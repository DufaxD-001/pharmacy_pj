<?php
session_start();

// 1. PLUG IN THE CENTRAL DATABASE
// Use ../ to move out of the 'logic' folder to find db.php
require_once('../logic/db.php'); 

// 2. SECURITY CHECK
// Only Admins should be allowed to change site-wide settings
if (!isset($_SESSION['user_role']) || $_SESSION['user_role'] !== 'admin') {
    header("Location: /pharmacy_pj/index.php?msg=Unauthorized+Access");
    exit();
}

// 3. THE UPDATE LOGIC
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['announcement'])) {
    
    // Sanitize the input
    $new_info = $conn->real_escape_string($_POST['announcement']);
    $page_target = 'home';

    // 4. PREPARED STATEMENT (Professional Security)
    // This updates the specific row in your site_settings table
    $stmt = $conn->prepare("UPDATE site_settings SET content = ? WHERE page_name = ?");
    $stmt->bind_param("ss", $new_info, $page_target);

    if ($stmt->execute()) {
        $stmt->close();
        
        // SUCCESS: Redirect to index.php (The Gatekeeper)
        // Never redirect directly to the 'view' file anymore.
        header("Location: /pharmacy_pj/index.php?msg=Announcement+Updated+Successfully");
        exit(); 
    } else {
        // ERROR: Redirect with an error message
        header("Location: /pharmacy_pj/index.php?msg=Update+Failed");
        exit();
    }
}

// Safety: If accessed incorrectly, send back to dashboard
header("Location: /pharmacy_pj/index.php");
exit();
?>