<?php
session_start();

// 1. PLUG IN THE CENTRAL DATABASE
// We use ../ to go out of the 'logic' folder to find db.php
require_once('../logic/db.php'); 

// 2. SECURITY CHECK: Only Admins can delete items
if (!isset($_SESSION['user_role']) || $_SESSION['user_role'] !== 'admin') {
    header("Location: /pharmacy_pj/index.php?msg=Unauthorized+Access");
    exit();
}

// 3. CHECK IF AN ID WAS SENT
if (isset($_GET['id'])) {
    $product_id = intval($_GET['id']); // Security: Convert to integer to prevent SQL injection
    
    // 4. PREPARED STATEMENT
    $stmt = $conn->prepare("DELETE FROM products WHERE id = ?");
    $stmt->bind_param("i", $product_id);

    if ($stmt->execute()) {
        $stmt->close();
        
        // SUCCESS: Redirect back to the Gatekeeper (index.php)
        // This ensures the Admin Dashboard refreshes within the correct layout
        header("Location: /pharmacy_pj/index.php?msg=Product+Deleted+Successfully");
        exit(); 
    } else {
        // ERROR: Redirect with a failure message
        header("Location: /pharmacy_pj/index.php?msg=Error+Deleting+Product");
        exit();
    }

} else {
    // Safety: If no ID is provided, just go back home
    header("Location: /pharmacy_pj/index.php");
    exit();
}
?>