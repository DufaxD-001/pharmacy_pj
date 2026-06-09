<?php
session_start();

// 1. Security Check: Redirect to registration inside logic folder
if (!isset($_SESSION['user_id'])) {
    header("Location: logic/registration.php");
    exit();
}

// 2. Load the Engine
require_once('logic/db.php'); 

$role = $_SESSION['user_role'] ?? 'client';
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title><?php echo SITE_NAME; ?> Portal</title> 
</head>
<body style="background: #f4f7f6; margin: 0; font-family: 'Segoe UI', sans-serif;">

   <div style="display: flex; justify-content: center; align-items: center; padding: 20px 0; background: #f4f7f6;">
    <a href="index.php">
        <img src="assets/20260203_061958.png" alt="Logo" style="max-height: 80px; width: auto;">
    </a>
</div>

    <nav style="background: #333; color: white; padding: 15px 40px; display: flex; justify-content: space-between; align-items: center; box-shadow: 0 2px 5px rgba(0,0,0,0.1);">
        <span style="font-weight: bold; letter-spacing: 1px;"><?php echo strtoupper(SITE_NAME); ?> PORTAL</span>
        <div style="display: flex; gap: 20px; align-items: center;">
            <span style="font-size: 13px; color: #ccc;">Role: <?php echo ucfirst($role); ?></span>
            <a href="logout.php" style="color: #ff4d4d; text-decoration: none; font-weight: bold; font-size: 14px;">Logout ⏻</a>
        </div>
    </nav>

    <?php if(isset($_GET['msg'])): ?>
        <div style="padding: 15px; background-color: #d4edda; color: #155724; margin: 20px 40px; border-radius: 8px; border-left: 5px solid #28a745;">
            <?php echo htmlspecialchars($_GET['msg']); ?>
        </div>
    <?php endif; ?>

    <div class="container" style="padding: 20px 40px;">
        <?php 
        switch ($role) {
            case 'admin': include('view/admin_dashboard.php'); break;
            case 'staff': include('view/staff_dashboard.php'); break;
            case 'client': include('view/client_dashboard.php'); break;
            default: echo "<h1>Error: Role not recognized.</h1>";
        }
        ?>
    </div>
</body>
</html>