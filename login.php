<?php
session_start();
require_once('logic/db.php'); 

if (isset($_SESSION['user_role'])) {
    header("Location: index.php");
    exit();
}

$error = "";

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $username = $conn->real_escape_string($_POST['username']);
    $password = $_POST['password'];

    // 1. Prepare statement to search for the user (UPDATED: Added 'status' to the query)
    $stmt = $conn->prepare("SELECT id, role, password, is_verified, status FROM users WHERE username = ?");
    $stmt->bind_param("s", $username);
    $stmt->execute();
    $result = $stmt->get_result();

    // Set a unified, secure generic error message
    $generic_error = "Invalid credentials";

    if ($user = $result->fetch_assoc()) {
        
        // NEW: Check if the account status is active before letting them step further
        if (isset($user['status']) && strtolower($user['status']) !== 'active') {
            $error = "Your account has been deactivated. Please contact your manager.";
        } 
        // 2. CHECK VERIFICATION STATUS
        elseif ($user['is_verified'] == 0) {
            $error = "Please verify your email before logging in.";
        } 
        else {
            // 3. PROCEED WITH PASSWORD CHECK
            if (password_verify($password, $user['password'])) {
                // Success with hashed password
                session_regenerate_id(true); // Added for extra session security
                $_SESSION['user_id'] = $user['id'];
                $_SESSION['user_role'] = $user['role'];
                header("Location: index.php");
                exit();
            } else {
                // Fallback check for old plain-text users
                if ($password === $user['password']) {
                     session_regenerate_id(true);
                     $_SESSION['user_id'] = $user['id'];
                     $_SESSION['user_role'] = $user['role'];
                     header("Location: index.php");
                     exit();
                }
                
                // Password failed (either hash or plain-text matches failed)
                $error = $generic_error;
            }
        }
    } else {
        // User does not exist in the database
        $error = $generic_error;
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Login - <?php echo SITE_NAME; ?></title>
 <style>
    :root { --primary: #2ecc71; --dark: #2c3e50; }
    body { font-family: 'Inter', sans-serif; background: #f4f7f6; display: flex; justify-content: center; align-items: center; height: 100vh; margin: 0; }
    
    .login-box { 
        background: white; padding: 40px; border-radius: 20px; 
        box-shadow: 0 15px 35px rgba(0,0,0,0.08); width: 380px; 
        border-top: 5px solid var(--primary);
    }
    
    h2 { text-align: center; color: var(--dark); letter-spacing: -1px; margin-bottom: 5px; }
    .subtitle { text-align: center; color: #7f8c8d; font-size: 13px; margin-bottom: 30px; }
    
    input { 
        width: 100%; padding: 14px; margin: 10px 0; 
        border: 2px solid #eee; border-radius: 12px; 
        box-sizing: border-box; transition: 0.3s;
    }
    input:focus { border-color: var(--primary); outline: none; }
    
    button { 
        width: 100%; padding: 14px; background: var(--dark); 
        color: white; border: none; border-radius: 12px; 
        cursor: pointer; font-weight: bold; font-size: 16px; margin-top: 10px;
    }
    button:hover { background: var(--primary); }
    
    .error { background: #fdeded; color: #e74c3c; padding: 12px; border-radius: 10px; font-size: 13px; text-align: center; margin-bottom: 20px; border: 1px solid #fadbd8; }
</style>
</head>
<body>
    <div class="login-box">
        <h2 style="text-align: center; margin-top: 0;"><?php echo SITE_NAME; ?></h2>
        
        <?php if(!empty($error)): ?>
            <div class="error"><?php echo $error; ?></div>
        <?php endif; ?>

        <?php if(isset($_GET['msg'])): ?>
            <div style="background: #d4edda; color: #155724; padding: 10px; border-radius: 5px; margin-bottom: 15px; font-size: 14px; text-align: center; border: 1px solid #c3e6cb;">
                <?php echo htmlspecialchars($_GET['msg']); ?>
            </div>
        <?php endif; ?>

        <form method="POST">
            <input type="text" name="username" placeholder="Username" required>
            <input type="password" name="password" placeholder="Password" required>
            <button type="submit">Access Dashboard</button>
        </form>

        <div style="margin-top: 25px; text-align: center; border-top: 1px solid #eee; padding-top: 15px; font-size: 14px; color: #555;">
            <p style="margin: 0;">New to the pharmacy?</p>
            <a href="logic/registration.php" style="color: #28a745; text-decoration: none; font-weight: bold;">Create an account here</a>
        </div>
    </div>
</body>
</html>