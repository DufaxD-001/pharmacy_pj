<?php
// logic/verify_payment.php
if (session_status() === PHP_SESSION_NONE) { session_start(); }
require_once('db.php'); 

/**
 * DETERMINISTIC ROLE FALLBACK
 * Safely find who is logged in right away to manage error redirect destinations.
 */
if (isset($_SESSION['staff_id'])) {
    $user_id = $_SESSION['staff_id'];
    $role = 'staff';
    $fallback_url = "/pharmacy_pj/index.php"; // Central routing index
} elseif (isset($_SESSION['client_id']) || isset($_SESSION['user_id'])) {
    $user_id = $_SESSION['client_id'] ?? $_SESSION['user_id'];
    $role = 'client';
    $fallback_url = "/pharmacy_pj/index.php";
} else {
    header("Location: /pharmacy_pj/login.php?error=Session+Expired+Please+Login");
    exit();
}

// 1. Validation: Ensure reference exists
if (!isset($_GET['reference'])) {
    header("Location: {$fallback_url}?error=" . urlencode("No payment reference supplied."));
    exit();
}
$reference = $_GET['reference'];

// 2. Verify Transaction with Paystack API
$curl = curl_init();
curl_setopt_array($curl, array(
    CURLOPT_URL => "https://api.paystack.co/transaction/verify/" . rawurlencode($reference),
    CURLOPT_RETURNTRANSFER => true,
    CURLOPT_HTTPHEADER => [
        "accept: application/json",
        "authorization: Bearer " . PSTK_SECRET_KEY,
        "cache-control: no-cache"
    ],
    CURLOPT_SSL_VERIFYPEER => false 
));

$response = curl_exec($curl);
$tranx = json_decode($response);
curl_close($curl);

if (!$tranx || !$tranx->status || $tranx->data->status !== 'success') {
    $gateway_err = $tranx->data->gateway_response ?? "Invalid Transaction Context";
    header("Location: {$fallback_url}?error=" . urlencode("Payment Verification Failed: " . $gateway_err));
    exit();
}

$total_paid = $tranx->data->amount / 100; // Convert Kobo to Naira
$invoice_no = "PH-" . date("Y") . "-" . str_pad(rand(1, 9999), 4, '0', STR_PAD_LEFT);

// Retrieve delivery info stored during checkout initialization
$delivery_type = $_SESSION['temp_delivery_type'] ?? 'pickup';
$delivery_address = $_SESSION['temp_address'] ?? null;

// 4. Begin Database Transaction to maintain strict data integrity
$conn->begin_transaction();

try {
    // A. Insert into Orders Table
    $sql_order = "INSERT INTO orders (user_id, total_price, payment_method, payment_status, reference, invoice_no, delivery_type, delivery_address, created_at, order_date) 
                  VALUES (?, ?, ?, 'Paid', ?, ?, ?, ?, NOW(), NOW())";
    
    $stmt_order = $conn->prepare($sql_order);
    if (!$stmt_order) {
        throw new Exception("Prepare failed: " . $conn->error);
    }
    
    $p_method = "paystack";
    $stmt_order->bind_param("idsssss", $user_id, $total_paid, $p_method, $reference, $invoice_no, $delivery_type, $delivery_address);
    $stmt_order->execute();
    $order_id = $conn->insert_id;

    // B. Process Items, Sales Ledger, and Stock Levels
    if (isset($_SESSION['cart']) && !empty($_SESSION['cart'])) {
        
        foreach ($_SESSION['cart'] as $p_id => $item) {
            $p_id = (int)$p_id;
            $purchased_qty = isset($item['qty']) ? (int)$item['qty'] : 1;

            // Fetch live base item pricing metrics
            $stmt_p = $conn->prepare("SELECT price FROM products WHERE id = ?");
            $stmt_p->bind_param("i", $p_id);
            $stmt_p->execute();
            $res_p = $stmt_p->get_result()->fetch_assoc();
            
            if (!$res_p) {
                throw new Exception("Product ID $p_id no longer exists in database registry.");
            }
            
            $unit_price = (float)$res_p['price'];
            $line_total = $unit_price * $purchased_qty;

            // 1. Record sub-item mapping in order_items
            $item_stmt = $conn->prepare("INSERT INTO order_items (order_id, product_id, quantity, unit_price) VALUES (?, ?, ?, ?)");
            $item_stmt->bind_param("iiid", $order_id, $p_id, $purchased_qty, $unit_price);
            $item_stmt->execute();

            // 2. Log financial performance metrics inside sales ledger table
            $sale_sql = "INSERT INTO sales (product_id, sold_by, sale_price, amount_paid, change_amount, transaction_ref, payment_method, sale_date) 
                         VALUES (?, ?, ?, ?, ?, ?, ?, NOW())";
            $sale_stmt = $conn->prepare($sale_sql);
            $change_amount = 0.00; 
            $p_method_label = "Paystack";
            
            $sale_stmt->bind_param("iidddss", $p_id, $user_id, $unit_price, $line_total, $change_amount, $reference, $p_method_label);
            $sale_stmt->execute();

            // 3. DEDUCT STOCK: Safe decrement implementation
            $update_stock = $conn->prepare("UPDATE products SET quantity = quantity - ? WHERE id = ?");
            $update_stock->bind_param("ii", $purchased_qty, $p_id);
            $update_stock->execute();
            
            // 4. Sync stock indicators based on inventory metrics
            $update_status = $conn->prepare("UPDATE products SET stock_status = 'Out of Stock' WHERE id = ? AND quantity <= 0");
            $update_status->bind_param("i", $p_id);
            $update_status->execute();
        }
        
        // 5. Finalize Transaction: Save changes safely
        $conn->commit();
        
        // Cleanup active cart context definitions
        $_SESSION['cart'] = []; 
        unset($_SESSION['checkout_return_to']); 
        unset($_SESSION['temp_delivery_type']);
        unset($_SESSION['temp_address']);

        // 6. DYNAMIC TARGETED REDIRECTS
        if ($role === 'staff') {
            header("Location: /pharmacy_pj/view/receipt.php?id=" . $order_id);
        } else {
            header("Location: /pharmacy_pj/index.php?status=success&order_id=" . $order_id);
        }
        exit();

    } else {
        throw new Exception("Your session cart was empty.");
    }

} catch (Exception $e) {
    // If anything fails above, cancel database changes completely
    $conn->rollback();
    header("Location: {$fallback_url}?error=" . urlencode("Database Transaction Blocked: " . $e->getMessage()));
    exit();
}
?>