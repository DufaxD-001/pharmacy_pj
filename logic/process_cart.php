<?php
session_start();
require_once('db.php');

// 1. Updated check: product_id is now optional (required for add/remove, not for empty)
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['action'])) {
    
    $pid = isset($_POST['product_id']) ? intval($_POST['product_id']) : 0;
    $action = $_POST['action'];

    // Initialize cart if it doesn't exist
    if (!isset($_SESSION['cart'])) {
        $_SESSION['cart'] = [];
    }

    // --- ACTION: ADD TO CART ---
    if ($action == 'add') {
        $qty_to_add = isset($_POST['quantity']) ? intval($_POST['quantity']) : 1;
        if ($qty_to_add < 1) $qty_to_add = 1;

        if (isset($_SESSION['cart'][$pid])) {
            $_SESSION['cart'][$pid] += $qty_to_add;
        } else {
            $_SESSION['cart'][$pid] = $qty_to_add;
        }
        
        $total_items = array_sum($_SESSION['cart']);
        
        if (isset($_SERVER['HTTP_REFERER'])) {
            $separator = (parse_url($_SERVER['HTTP_REFERER'], PHP_URL_QUERY)) ? '&' : '?';
            header("Location: " . $_SERVER['HTTP_REFERER'] . $separator . "status=added&count=$total_items");
        } else {
            header("Location: ../index.php?status=added&count=$total_items");
        }
        exit();
    }
    
    // --- ACTION: REMOVE FROM CART ---
    if ($action == 'remove') {
        if (isset($_SESSION['cart'][$pid])) {
            unset($_SESSION['cart'][$pid]);
        }
        header("Location: ../view/cart_view.php?msg=Item+removed");
        exit();
    }

    // --- ACTION: UPDATE QUANTITY ---
    if ($action == 'update') {
        $new_qty = intval($_POST['quantity']);
        if ($new_qty > 0) {
            $_SESSION['cart'][$pid] = $new_qty;
        } else {
            unset($_SESSION['cart'][$pid]); 
        }
        header("Location: ../view/cart_view.php?msg=Cart+updated");
        exit();
    }

    // --- NEW ACTION: EMPTY ENTIRE CART ---
    if ($action == 'empty') {
        unset($_SESSION['cart']); 
        header("Location: ../view/cart_view.php?msg=Cart+cleared");
        exit();
    }
}

// Safety fallback
header("Location: ../index.php");
exit();