<?php
session_start();

// 1. PLUG IN THE CENTRAL DATABASE
require_once('../logic/db.php'); 

// 2. SECURITY: Only Staff or Admin can process sales
if (!isset($_SESSION['user_role']) || ($_SESSION['user_role'] !== 'staff' && $_SESSION['user_role'] !== 'admin')) {
    header("Location: /pharmacy_pj/index.php?msg=Unauthorized+Access");
    exit();
}

// 3. HANDLE "MARK OUT OF STOCK" (Manual GET request)
if (isset($_GET['out_of_stock'])) {
    $id = intval($_GET['out_of_stock']);
    
    $stmt = $conn->prepare("UPDATE products SET stock_status = 'Out of Stock', quantity = 0 WHERE id = ?");
    $stmt->bind_param("i", $id);
    
    if ($stmt->execute()) {
        header("Location: /pharmacy_pj/index.php?msg=Product+marked+out+of+stock");
    } else {
        header("Location: /pharmacy_pj/index.php?msg=Error+updating+stock");
    }
    exit();
}

// 4. HANDLE "RECORD SALE" (POST request)
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['product_id'])) {
    $id = intval($_POST['product_id']);
    
    // First, check if there is actually stock to sell
    $check = $conn->query("SELECT quantity FROM products WHERE id = $id");
    $product = $check->fetch_assoc();

    if ($product && $product['quantity'] > 0) {
        // Subtract 1 from quantity
        $conn->query("UPDATE products SET quantity = quantity - 1 WHERE id = $id");

        // If quantity hits zero, automatically flip status to 'Out of Stock'
        $conn->query("UPDATE products SET stock_status = 'Out of Stock' WHERE id = $id AND quantity <= 0");
        
        header("Location: /pharmacy_pj/index.php?msg=Sale+recorded+successfully");
    } else {
        // If someone clicks 'Sell' but quantity is already 0
        header("Location: /pharmacy_pj/index.php?msg=Error:+Item+is+already+empty");
    }
    exit();
}

// Safety: If accessed incorrectly
header("Location: /pharmacy_pj/index.php");
exit();
?>