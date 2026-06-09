<?php
session_start();
require_once('db.php');
require_once('mail_helper.php'); // Imported to make sendOTP() available

if (!isset($_SESSION['temp_email'])) {
    header("Location: registration.php");
    exit();
}

$email = $_SESSION['temp_email'];
$message = "";

// Helper function to handle creating and emailing a brand new OTP
function resendNewOTP($conn, $email) {
    $new_otp = rand(100000, 999999);
    
    // Set expiration threshold context timestamp to 60 seconds from right now
    $_SESSION['otp_expires_at'] = time() + 60;

    // Save the new OTP code to the database
    $stmt = $conn->prepare("UPDATE users SET otp_code = ? WHERE email = ?");
    $stmt->bind_param("ss", $new_otp, $email);
    $stmt->execute();

    // Calls your mail_helper function to actually send the email to the user
    if (sendOTP($email, $new_otp)) {
        return "A new verification code has been sent! Valid for 60 seconds.";
    } else {
        return "Code generated in database, but email delivery failed. Check phpMyAdmin.";
    }
}

// Handle OTP Resend action
if (isset($_POST['action']) && $_POST['action'] === 'resend') {
    if (time() > ($_SESSION['otp_expires_at'] ?? 0)) {
        $success_msg = resendNewOTP($conn, $email);
        $message = "<p style='color:green; font-weight:bold;'>{$success_msg}</p>";
    } else {
        $seconds_left = $_SESSION['otp_expires_at'] - time();
        $message = "<p style='color:orange;'>Please wait {$seconds_left}s before requesting a new code.</p>";
    }
}

// Handle standard validation processing form entry
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['otp'])) {
    $user_otp = trim($_POST['otp']);

    // Check if OTP session window has run out
    if (isset($_SESSION['otp_expires_at']) && time() > $_SESSION['otp_expires_at']) {
        $message = "<p style='color:red; font-weight:bold;'>This OTP code has expired. Please request a new one below.</p>";
    } else {
        // Query to see if the entry combination exists
        $stmt = $conn->prepare("SELECT id FROM users WHERE email = ? AND otp_code = ?");
        $stmt->bind_param("ss", $email, $user_otp);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result->num_rows > 0) {
            // Success! Securely update verification status
            $update_stmt = $conn->prepare("UPDATE users SET is_verified = 1, otp_code = NULL WHERE email = ?");
            $update_stmt->bind_param("s", $email);
            $update_stmt->execute();
            
            // Clear temporary routing variables
            unset($_SESSION['temp_email']);
            unset($_SESSION['otp_expires_at']);
            
            header("Location: ../login.php?msg=Email+Verified!+You+can+now+login.");
            exit();
        } else {
            $message = "<p style='color:red;'>Invalid OTP code. Please try again.</p>";
        }
    }
}

// Pass backend countdown metrics directly into client-side JS tracking parameters safely
$time_now = time();
$expiry_time = $_SESSION['otp_expires_at'] ?? ($time_now + 60); 
$remaining_seconds = max(0, $expiry_time - $time_now);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Verify OTP</title>
    <style>
        body { font-family: 'Segoe UI', sans-serif; background-color: #f4f7f6; display: flex; justify-content: center; align-items: center; height: 100vh; margin: 0; }
        .otp-container { background: white; padding: 30px; border-radius: 12px; box-shadow: 0px 10px 25px rgba(0,0,0,0.1); width: 350px; text-align: center; }
        input { width: 100%; padding: 12px; margin: 20px 0; border: 1px solid #ddd; border-radius: 6px; text-align: center; font-size: 20px; letter-spacing: 5px; box-sizing: border-box; }
        button { width: 100%; padding: 12px; background-color: #007bff; color: white; border: none; border-radius: 6px; cursor: pointer; font-weight: bold; font-size: 15px; }
        button:hover { background-color: #0056b3; }
        .timer-info { margin-top: 15px; font-size: 14px; color: #555; }
        .resend-link-btn { background: none; border: none; color: #28a745; text-decoration: underline; font-weight: bold; cursor: pointer; font-size: 14px; display: none; margin: 15px auto 0 auto; padding: 0; }
    </style>
</head>
<body>
    <div class="otp-container">
        <h2>Verify Email</h2>
        <p>Enter the 6-digit code sent to your email.</p>
        
        <?php echo $message; ?>
        
        <!-- Main Form -->
        <form method="POST" id="otpForm">
            <input type="text" name="otp" maxlength="6" placeholder="000000" required autocomplete="off">
            <button type="submit" id="verifyBtn">Verify Account</button>
        </form>

        <!-- Live client countdown interface container -->
        <div class="timer-info" id="timerDisplay">
            Code expires in: <span id="clockCount"><?php echo $remaining_seconds; ?></span>s
        </div>

        <!-- Hidden alternative post back framework for clean token lifecycle refreshes -->
        <form method="POST" id="resendForm">
            <input type="hidden" name="action" value="resend">
            <button type="submit" class="resend-link-btn" id="resendBtn">Resend Code</button>
        </form>
    </div>

    <script>
        let countdownTime = <?php echo $remaining_seconds; ?>;
        const clockDisplay = document.getElementById('clockCount');
        const textWrapper = document.getElementById('timerDisplay');
        const resendAction = document.getElementById('resendBtn');
        const verifyAction = document.getElementById('verifyBtn');

        if (countdownTime <= 0) {
            triggerExpirationUI();
        } else {
            const trackingTimer = setInterval(() => {
                countdownTime--;
                clockDisplay.textContent = countdownTime;
                
                if (countdownTime <= 0) {
                    clearInterval(trackingTimer);
                    triggerExpirationUI();
                }
            }, 1000);
        }

        function triggerExpirationUI() {
            textWrapper.textContent = "Your verification token window has run out.";
            textWrapper.style.color = '#e74c3c';
            verifyAction.disabled = true;
            verifyAction.style.backgroundColor = '#cccccc';
            verifyAction.style.cursor = 'not-allowed';
            resendAction.style.display = 'block';
        }
    </script>
</body>
</html>