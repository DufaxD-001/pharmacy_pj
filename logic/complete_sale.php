<?php
if (session_status() === PHP_SESSION_NONE) { session_start(); }
require_once __DIR__ . '/db.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && !empty($_SESSION['staff_cart'])) {
    
    $payment_method = $_POST['payment_method'];
    $amount_received = floatval($_POST['amount_paid'] ?? 0);
    $transaction_ref = $_POST['transaction_ref'] ?? null; // Get the ref
    $user_id = $_SESSION['user_id']; 
    $cart_total = 0;

    foreach ($_SESSION['staff_cart'] as $item) {
        $cart_total += $item['price'] * $item['qty'];
    }

    // Security check
    if ($payment_method === 'Cash' && $amount_received < $cart_total) {
        header("Location: ../view/staff_dashboard.php?error=" . urlencode("Amount paid is insufficient."));
        exit();
    }

    $conn->begin_transaction();

    try {
        $change = ($payment_method === 'Cash') ? ($amount_received - $cart_total) : 0;
        
        // --- MATCHING YOUR TABLE STRUCTURE EXACTLY ---
        // Columns: product_id (2), sold_by (3), sale_price (4), amount_paid (5), change_amount (6)
        // Note: product_id here is usually for the first item or left NULL if using sale_items table
        $first_product_id = array_key_first($_SESSION['staff_cart']); 

        $sql = "INSERT INTO sales (product_id, sold_by, sale_price, amount_paid, change_amount,transactional_ref, sale_date) 
                VALUES (?, ?, ?, ?, ?, NOW())";
        
        $stmt = $conn->prepare($sql);
        if (!$stmt) { throw new Exception("Prepare failed: " . $conn->error); }

        // i = product_id, i = sold_by, d = sale_price, d = amount_paid, d = change_amount
        $stmt->bind_param("iiddd", $first_product_id, $user_id, $cart_total, $amount_received, $change,$transactional_ref);
        
        if (!$stmt->execute()) {
            throw new Exception("Execute failed: " . $stmt->error);
        }
        
        $sale_id = $conn->insert_id;

        // Record individual items into sale_items table
        foreach ($_SESSION['staff_cart'] as $p_id => $item) {
            $stmt_item = $conn->prepare("INSERT INTO sale_items (sale_id, product_id, quantity, unit_price, subtotal) VALUES (?, ?, ?, ?, ?)");
            $sub = $item['qty'] * $item['price'];
            $stmt_item->bind_param("iiidd", $sale_id, $p_id, $item['qty'], $item['price'], $sub);
            $stmt_item->execute();

            // Update stock
            $upd = $conn->prepare("UPDATE products SET quantity = quantity - ? WHERE id = ?");
            $upd->bind_param("ii", $item['qty'], $p_id);
            $upd->execute();
        }

        $conn->commit();
        unset($_SESSION['staff_cart']);
        header("Location: ../view/receipt.php?id=" . $sale_id);
        exit();

    } catch (Exception $e) {
        $conn->rollback();
        // This will show you EXACTLY which column name is causing the problem
        header("Location: ../view/staff_dashboard.php?error=" . urlencode($e->getMessage()));
        exit();
    }
}