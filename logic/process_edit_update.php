<?php
session_start();

// 1. PLUG IN THE CENTRAL DATABASE
// We move up one level (../) to find the logic folder's db.php
require_once('../logic/db.php'); 

// 2. SECURITY CHECK
// Only allow admins to run this edit script
if (!isset($_SESSION['user_role']) || $_SESSION['user_role'] !== 'admin') {
    header("Location: /pharmacy_pj/index.php?msg=Unauthorized+Access");
    exit();
}

// 3. PROCESS THE PRODUCT EDIT
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    
    // Sanitize and prepare data
    $id    = intval($_POST['id']);
    $name  = $conn->real_escape_string($_POST['p_name']);
    $price = $_POST['p_price'];
    $stock = $conn->real_escape_string($_POST['p_stock']);

    // 4. PREPARED STATEMENT (Professional Security)
    // "sdsi" stands for: string (name), double (price), string (stock), integer (id)
    $stmt = $conn->prepare("UPDATE products SET product_name = ?, price = ?, stock_status = ? WHERE id = ?");
    $stmt->bind_param("sdsi", $name, $price, $stock, $id);

    if ($stmt->execute()) {
        $stmt->close();
        
        // SUCCESS: Redirect to index.php (The Gatekeeper)
        // This reloads the admin dashboard automatically
        header("Location: /pharmacy_pj/index.php?msg=Product+Updated+Successfully");
        exit(); 
    } else {
        // ERROR: Redirect with failure message
        header("Location: /pharmacy_pj/index.php?msg=Error+Updating+Product");
        exit();
    }
}

// Safety: If someone tries to access this file without a POST request
header("Location: /pharmacy_pj/index.php");
exit();
?>