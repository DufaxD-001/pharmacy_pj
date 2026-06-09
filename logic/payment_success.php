<?php
session_start();
require_once('db.php'); 

// 1. Check if the constant exists
if (!defined('PSTK_SECRET_KEY')) {
    die("Error: PSTK_SECRET_KEY is not defined in db.php.");
}

$reference = isset($_GET['reference']) ? $_GET['reference'] : '';
if (!$reference) {
    die("No reference found.");
}

// 2. The Verification Request
$curl = curl_init();
curl_setopt_array($curl, array(
    CURLOPT_URL => "https://api.paystack.co/transaction/verify/" . rawurlencode($reference),
    CURLOPT_RETURNTRANSFER => true,
    CURLOPT_HTTPHEADER => array(
        "accept: application/json",
        "authorization: Bearer " . PSTK_SECRET_KEY,
        "cache-control: no-cache"
    ),
    CURLOPT_SSL_VERIFYPEER => false, 
    CURLOPT_SSL_VERIFYHOST => false,
));

$response = curl_exec($curl);
$err = curl_error($curl);
curl_close($curl);

if ($err) {
    die("Curl Error: " . $err);
}

$tranx = json_decode($response);

if (!$tranx || !$tranx->status) {
    header("Location: ../index.php?msg=Verification+Error");
    exit();
}

if ('success' === $tranx->data->status) {
    if (!isset($_SESSION['user_id'])) {
        die("Payment Success, but User Session was lost.");
    }

    $user_id = $_SESSION['user_id'];
    $total_paid = $tranx->data->amount / 100;

    // 3. DATABASE SAVING
    $stmt = $conn->prepare("INSERT INTO orders (user_id, total_price, payment_status, reference) VALUES (?, ?, 'Paid', ?)");
    $stmt->bind_param("ids", $user_id, $total_paid, $reference);
    
    if ($stmt->execute()) {
        $order_id = $conn->insert_id;

        // --- NEW: INVOICE NUMBER LOGIC ---
        // This generates a professional ID like PH-2026-0001
        $invoice_no = "PH-" . date("Y") . "-" . str_pad($order_id, 4, "0", STR_PAD_LEFT);
        
        // We use @ to suppress the error just in case the column doesn't exist yet
        @$conn->query("UPDATE orders SET invoice_no = '$invoice_no' WHERE id = $order_id");

        // Process items and stock...
        if (!empty($_SESSION['cart'])) {
            foreach ($_SESSION['cart'] as $p_id => $qty) {
                // Get unit price from DB
                $p_res = $conn->query("SELECT price FROM products WHERE id = " . intval($p_id));
                $p_data = $p_res->fetch_assoc();
                $u_price = $p_data['price'];

                // Save to order_items
                $i_stmt = $conn->prepare("INSERT INTO order_items (order_id, product_id, quantity, unit_price) VALUES (?, ?, ?, ?)");
                $i_stmt->bind_param("iiid", $order_id, $p_id, $qty, $u_price);
                $i_stmt->execute();

                // Update product quantity
                $conn->query("UPDATE products SET quantity = quantity - " . intval($qty) . " WHERE id = " . intval($p_id));
            }
            unset($_SESSION['cart']); // CLEAR THE CART AFTER SUCCESS
        }
        
       // Replace the old header("Location: ../index.php?msg=...") with this:
header("Location: ../view/order_confirmation.php?invoice=" . urlencode($invoice_no));
exit();
    }
} else {
    header("Location: ../index.php?msg=Payment+Declined:+" . ($tranx->data->gateway_response ?? "Unknown Reason"));
    exit();
}