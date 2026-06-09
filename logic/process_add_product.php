<?php
session_start();

// 1. PLUG IN THE POWER (Central Database)
// We use ../ to go out of the 'logic' folder to find the file
require_once('../logic/db.php'); 

// 2. SECURITY CHECK
// Only Admins should be able to run this script
if (!isset($_SESSION['user_role']) || $_SESSION['user_role'] !== 'admin') {
    header("Location: /pharmacy_pj/index.php?msg=Unauthorized+Access");
    exit();
}

// 3. THE LOGIC
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    
    // Sanitize inputs to prevent SQL Injection
    $name     = $conn->real_escape_string($_POST['p_name']);
    $category = $conn->real_escape_string($_POST['p_category']);
    $price    = $_POST['p_price'];
    $stock    = $conn->real_escape_string($_POST['p_stock']);

    // 4. PREPARED STATEMENT (Security Standard)
    $stmt = $conn->prepare("INSERT INTO products (product_name, category, price, stock_status) VALUES (?, ?, ?, ?)");
    
    // "ssds" means: string, string, double (decimal), string
    $stmt->bind_param("ssds", $name, $category, $price, $stock);

    if ($stmt->execute()) {
        $stmt->close();
        
        // SUCCESS: Redirect back to the GATEKEEPER
        // We don't go to the view folder; we go to index.php
        header("Location: /pharmacy_pj/index.php?msg=Product+Added+Successfully");
        exit(); 
    } else {
        // ERROR: Redirect with an error message
        header("Location: /pharmacy_pj/index.php?msg=Error+Adding+Product");
        exit();
    }
}

// If someone tries to access this file without POST, send them home
header("Location: /pharmacy_pj/index.php");
exit();
?>