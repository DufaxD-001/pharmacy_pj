<?php
// 1. Start session and enable error reporting for debugging
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
error_reporting(E_ALL);
ini_set('display_errors', 1);

// 2. Database Connection
require_once 'db.php'; 

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';
    $product_id = intval($_POST['product_id'] ?? 0);
    $qty = intval($_POST['qty'] ?? 1);

    // Ensure the cart exists
    if (!isset($_SESSION['cart'])) {
        $_SESSION['cart'] = [];
    }

    $msg = "done";

    // --- ACTION: ADD TO BILL ---
    if ($action === 'add_to_bill' && $product_id > 0) {
        $stmt = $conn->prepare("SELECT product_name, price, quantity FROM products WHERE id = ?");
        $stmt->bind_param("i", $product_id);
        $stmt->execute();
        $product = $stmt->get_result()->fetch_assoc();

        if ($product) {
            $current_in_cart = isset($_SESSION['cart'][$product_id]) ? $_SESSION['cart'][$product_id]['qty'] : 0;
            $new_total_qty = $current_in_cart + $qty;

            // Check if we have enough stock
            if ($new_total_qty <= $product['quantity']) {
                $_SESSION['cart'][$product_id] = [
                    'name'  => $product['product_name'],
                    'price' => $product['price'],
                    'qty'   => $new_total_qty
                ];
                $msg = "success";
            } else {
                $msg = "insufficient_stock";
            }
        } else {
            $msg = "product_not_found";
        }
    }

    // --- ACTION: REMOVE ITEM ---
    if ($action === 'remove_item') {
        if (isset($_SESSION['cart'][$product_id])) {
            unset($_SESSION['cart'][$product_id]);
        }
        $msg = "item_removed";
    }

    // --- ACTION: CLEAR BILL ---
    if ($action === 'clear_bill') {
        unset($_SESSION['cart']);
        $msg = "bill_cleared";
    }

    // 3. Redirect back safely
    header("Location: ../index.php?status=" . urlencode($msg));
    exit();
} else {
    header("Location: /pharmacy_pj/index.php");
    exit();
}
?>