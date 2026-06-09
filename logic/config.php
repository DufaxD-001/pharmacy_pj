<?php
// 1. Database Connection Settings
$host = "localhost";
$user = "USERNAME";
$pass = "PASSWORD";
$dbname = "pharmacy_pj";

$conn = new mysqli($host, $user, $pass, $dbname);

if ($conn->connect_error) {
    die("Database Connection Failed: " . $conn->connect_error);
}

// Set character set to handle symbols like ₦
$conn->set_charset("utf8");

// 2. Paystack API Keys
define("PSTK_PUBLIC_KEY", "pk_test_YOUR_PAYSTACK_PUBLIC_KEY");
define("PSTK_SECRET_KEY", "sk_test_YOUR_PAYSTACK_SECRET_KEY");

// 3. Site-Wide Settings
define("SITE_NAME", "Dufax Pharmacy");
define("CURRENCY_SYMBOL", "₦");
define("CONTACT_EMAIL", "info@dufaxplus.com");

// 4. Mail Server Settings
define("MAIL_HOST", "smtp.gmail.com");
define("MAIL_USERNAME", "YOUR_EMAIL_ADDRESS"); 
define("MAIL_PASSWORD", "YOUR_GMAIL_APP_PASSWORD"); 
define("MAIL_PORT", 587);

// 5. Timezone
date_default_timezone_set("Africa/Lagos");

/** * CRITICAL ADDITION: 
 * Since you used Composer, we must point to the autoload file.
 * This assumes your 'vendor' folder is in the root pharmacy_pj folder.
 */
require_once __DIR__ . '/../vendor/autoload.php'; 
?>