<?php
session_start();
require_once('db.php');
require_once('mail_helper.php');

$message = "";

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $username = $conn->real_escape_string($_POST['username']);
    $email = $conn->real_escape_string($_POST['email']);
    $password = $_POST['password'];
    $confirm_password = $_POST['confirm_password'];
    $role = 'client'; 

    if ($password !== $confirm_password) {
        $message = "<p style='color:red;'>Passwords do not match!</p>";
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $message = "<p style='color:red;'>Invalid email address.</p>";
    } else {
        $checkUser = $conn->query("SELECT id FROM users WHERE username = '$username' OR email = '$email'");
        
        if ($checkUser->num_rows > 0) {
            $message = "<p style='color:red;'>Username or Email already taken.</p>";
        } else {
            // 1. Generate 6-digit OTP
            $otp_code = rand(100000, 999999);
            $hashed_password = password_hash($password, PASSWORD_DEFAULT);

            // 2. Updated INSERT to include otp_code (is_verified defaults to 0)
            $stmt = $conn->prepare("INSERT INTO users (username, email, password, role, otp_code) VALUES (?, ?, ?, ?, ?)");
            $stmt->bind_param("sssss", $username, $email, $hashed_password, $role, $otp_code);

            if ($stmt->execute()) {
                $_SESSION['temp_email'] = $email;
                
                // Set the session expiration timestamp for the countdown clock!
                $_SESSION['otp_expires_at'] = time() + 60; // 60 seconds from right now
                
                // Call the helper function to send the email
                if (sendOTP($email, $otp_code)) {
                    header("Location: verify_otp.php");
                } else {
                    // If the mail fails, we still go to verify page 
                    // so you can check the code in phpMyAdmin manually
                    header("Location: verify_otp.php?error=mail_failed");
                }
                exit();
            } else {
                $message = "<p style='color:red;'>Error: " . $conn->error . "</p>";
            }
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Registration - <?php echo SITE_NAME; ?></title>
    <style>
        body { font-family: 'Segoe UI', sans-serif; background-color: #f4f7f6; display: flex; justify-content: center; align-items: center; height: 100vh; margin: 0; }
        .reg-container { background: white; padding: 30px; border-radius: 12px; box-shadow: 0px 10px 25px rgba(0,0,0,0.1); width: 380px; }
        label { font-size: 14px; font-weight: bold; color: #555; }
        input { width: 100%; padding: 12px; margin: 8px 0 18px 0; border: 1px solid #ddd; border-radius: 6px; box-sizing: border-box; }
        button { width: 100%; padding: 12px; background-color: #28a745; color: white; border: none; border-radius: 6px; cursor: pointer; font-weight: bold; }
    </style>
</head>
<body>

<div class="reg-container">
    <h2 style="text-align: center;">Create Account</h2>
    <p style="text-align:center; font-size: 13px; color: #666;">Step 1: Account Details</p>
    <?php echo $message; ?>

    <form method="POST">
        <label>Username</label>
        <input type="text" name="username" required>
        <label>Email Address</label>
        <input type="email" name="email" required>
        <label>Password</label>
        <input type="password" name="password" required>
        <label>Confirm Password</label>
        <input type="password" name="confirm_password" required>
        <button type="submit">Register & Get OTP</button>
    </form>
    <p style="text-align:center;">Already have an account? <a href="../login.php">Login</a></p>
</div>

</body>
</html>