<?php
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

// This replaces the three manual require lines. 
// It automatically finds all PHPMailer files for you.
require __DIR__ . '/../vendor/autoload.php'; 

function sendOTP($toEmail, $otp) {
    $mail = new PHPMailer(true);
    try {
        // Server settings using constants from db.php
        $mail->isSMTP();
        $mail->Host       = MAIL_HOST; 
        $mail->SMTPAuth   = true;
        $mail->Username   = MAIL_USERNAME; 
        $mail->Password   = MAIL_PASSWORD; 
        $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
        $mail->Port       = MAIL_PORT;

        // Recipients
        $mail->setFrom(MAIL_USERNAME, SITE_NAME);
        $mail->addAddress($toEmail);

        // Content
        $mail->isHTML(true);
        $mail->Subject = 'Verify Your Account - ' . SITE_NAME;
        $mail->Body    = "
            <div style='font-family: sans-serif; padding: 20px; border: 1px solid #eee; border-radius: 10px;'>
                <h2 style='color: #28a745;'>Account Verification</h2>
                <p>Please use the code below to activate your account:</p>
                <div style='font-size: 30px; font-weight: bold; background: #f4f4f4; padding: 10px; text-align: center;'>$otp</div>
                <p>Sent from " . SITE_NAME . "</p>
            </div>";

        $mail->send();
        return true;
} catch (Exception $e) {
        // Remove the slashes below to see the REAL error if it still fails
        // die("Mailer Error: " . $mail->ErrorInfo); 
        return false;
    }
}