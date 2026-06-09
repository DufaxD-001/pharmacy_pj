<?php
// logic/process_checkout.php
if (session_status() === PHP_SESSION_NONE) { session_start(); }
require_once('db.php');

/**
 * 1. CAPTURE METHOD OF PAYMENT
 */
$payment_method = $_POST['payment_method'] ?? 'cash'; 

/**
 * 2. SESSION & ROLE VALIDATION
 * Fixed: All staff/admin routes point back to the template container index.php
 */
if (isset($_SESSION['client_id'])) {
    $user_id = $_SESSION['client_id'];
    $role = 'client';
    $origin_url = "../index.php"; 
} elseif (isset($_SESSION['staff_id'])) {
    $user_id = $_SESSION['staff_id'];
    $role = 'staff';
    $origin_url = "/pharmacy_pj/index.php?status=success";
} elseif (isset($_SESSION['user_id'])) { 
    $user_id = $_SESSION['user_id'];
    if (isset($_SESSION['user_role']) && in_array($_SESSION['user_role'], ['staff', 'pharmacist', 'admin'])) {
        $role = 'staff';
        $origin_url = "/pharmacy_pj/index.php?status=success";
    } else {
        $role = 'client';
        $origin_url = "../index.php"; 
    }
} else {
    header("Location: ../login.php?error=Session+Expired+Please+Login");
    exit();
}

/**
 * 3. IMMEDIATE LOCAL CHECKOUT (CASH, POS, BANK TRANSFER)
 * Processes database entries and routes immediately to the thermal receipt layout.
 */
if ($role === 'staff' && $payment_method !== 'paystack') {
    if (!empty($_SESSION['cart'])) {
        $conn->begin_transaction();

        try {
            // A. Calculate Grand Total from current session data
            $total_price = 0;
            foreach ($_SESSION['cart'] as $item) {
                $total_price += ($item['price'] * $item['qty']);
            }

            // B. Generate unique identification codes
            $reference = "TXN-" . strtoupper(substr(str_shuffle("0123456789abcdefghijklmnopqrstuvwxyz"), 0, 8));
            $invoice_no = "INV-" . date("Ymd") . "-" . rand(100, 999);
            $payment_status = "paid";

            // C. Insert master transaction log row into the 'orders' table
            $stmt_order = $conn->prepare("INSERT INTO orders (user_id, total_price, payment_method, payment_status, reference, invoice_no, order_date) VALUES (?, ?, ?, ?, ?, ?, NOW())");
            $stmt_order->bind_param("idssss", $user_id, $total_price, $payment_method, $payment_status, $reference, $invoice_no);
            $stmt_order->execute();
            
            // Capture the generated primary key row auto-increment ID
            $new_sale_id = $conn->insert_id;

            // D. Insert itemized item breakdowns, log sales ledger, and decrement current stock
            $stmt_item = $conn->prepare("INSERT INTO order_items (order_id, product_id, quantity, unit_price) VALUES (?, ?, ?, ?)");
            $update_stock = $conn->prepare("UPDATE products SET quantity = quantity - ? WHERE id = ?");
            
            // HEALTH CHECK ADDITION: Statement to log local cash/POS sales directly into the performance dashboard ledger
            $sale_sql = "INSERT INTO sales (product_id, sold_by, sale_price, amount_paid, change_amount, transaction_ref, payment_method, sale_date) 
                         VALUES (?, ?, ?, ?, ?, ?, ?, NOW())";
            $sale_stmt = $conn->prepare($sale_sql);

            foreach ($_SESSION['cart'] as $product_id => $item) {
                $purchased_qty = isset($item['qty']) ? (int)$item['qty'] : 1;
                $unit_price = $item['price'];
                $line_total = $unit_price * $purchased_qty;
                $change_amount = 0.00;

                // 1. Record item purchase line details
                $stmt_item->bind_param("iiid", $new_sale_id, $product_id, $purchased_qty, $unit_price);
                $stmt_item->execute();

                // 2. HEALTH CHECK SYNC: Update the central sales ledger so analytics charts capture local sales
                $sale_stmt->bind_param("iidddss", $product_id, $user_id, $unit_price, $line_total, $change_amount, $reference, $payment_method);
                $sale_stmt->execute();

                // 3. Reduce inventory items stock level quantities in real-time
                $update_stock->bind_param("ii", $purchased_qty, $product_id);
                $update_stock->execute();
                
                // 4. Automatically flag out-of-stock variations down to matching indicator standards
                $update_status = $conn->prepare("UPDATE products SET stock_status = 'Out of Stock' WHERE id = ? AND quantity <= 0");
                $update_status->bind_param("i", $product_id);
                $update_status->execute();
            }

            // Clear the transaction cache cart
            $_SESSION['cart'] = [];
            
            // Commit all changes simultaneously to protect integrity records
            $conn->commit();

            // SUCCESS ROUTE: Redirect straight to your custom receipt endpoint using the new ID
            header("Location: /pharmacy_pj/view/receipt.php?id=" . $new_sale_id);
            exit();

        } catch (Exception $e) {
            $conn->rollback();
            header("Location: /pharmacy_pj/index.php?error=" . urlencode("Transaction failed: " . $e->getMessage()));
            exit();
        }
    } else {
        header("Location: /pharmacy_pj/index.php?error=Cart+is+Empty");
        exit();
    }
}

/**
 * 4. PAYSTACK ROUTE (IF CHOSEN BY STAFF OR MANDATED BY CLIENT SITE FRONTEND)
 */
$stmt_user = $conn->prepare("SELECT email FROM users WHERE id = ?");
$stmt_user->bind_param("i", $user_id);
$stmt_user->execute();
$user_data = $stmt_user->get_result()->fetch_assoc();
$email = $user_data['email'] ?? 'customer@example.com';

$_SESSION['temp_delivery_type'] = $_POST['delivery_type'] ?? 'pickup';
$_SESSION['temp_address'] = isset($_POST['address']) ? trim(htmlspecialchars($_POST['address'])) : '';

$order_total = 0;
if (!empty($_SESSION['cart'])) {
    foreach ($_SESSION['cart'] as $p_id => $item) {
        $p_id = intval($p_id);
        $actual_qty = isset($item['qty']) ? intval($item['qty']) : 1;
        
        $res = $conn->query("SELECT price FROM products WHERE id = $p_id");
        $product = $res->fetch_assoc();
        if ($product) {
            $order_total += ($product['price'] * $actual_qty);
        }
    }
}

if ($_SESSION['temp_delivery_type'] === 'delivery') { $order_total += 500; }

if ($order_total <= 0) {
    header("Location: $origin_url?error=Invalid+Total+Amount");
    exit();
}

$reference = substr(str_shuffle("0123456789abcdefghijklmnopqrstuvwxyz"), 0, 10);
$amount_kobo = $order_total * 100;

$url = "https://api.paystack.co/transaction/initialize";
$fields = [
    'email'         => $email,
    'amount'        => $amount_kobo,
    'reference'     => $reference,
    'callback_url'  => "http://localhost/pharmacy_pj/logic/verify_payment.php"
];

$ch = curl_init();
curl_setopt($ch, CURLOPT_URL, $url);
curl_setopt($ch, CURLOPT_POST, true);
curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($fields));
curl_setopt($ch, CURLOPT_HTTPHEADER, [
    "Authorization: Bearer " . PSTK_SECRET_KEY,
    "Cache-Control: no-cache",
    "Content-Type: application/json"
]);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false); 

$result = curl_exec($ch);
$response = json_decode($result);

if ($response && $response->status) {
    header("Location: " . $response->data->authorization_url);
    exit();
} else {
    unset($_SESSION['temp_delivery_type']);
    unset($_SESSION['temp_address']);
    $error = $response->message ?? "Connection to Paystack failed.";
    header("Location: $origin_url?error=" . urlencode($error));
    exit();
}
?>