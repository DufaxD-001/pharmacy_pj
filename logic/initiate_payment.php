<?php
session_start();

// 1. Correct the path to db.php (it is in the same folder 'logic')
require_once('db.php'); 

// 2. Security: Recalculate the total from the Session
// Never trust the 'total' sent from the browser/form!
$total_naira = 0;
if (!empty($_SESSION['cart'])) {
    foreach ($_SESSION['cart'] as $id => $qty) {
        $res = $conn->query("SELECT price FROM products WHERE id = " . intval($id));
        $product = $res->fetch_assoc();
        $total_naira += ($product['price'] * $qty);
    }
} else {
    die("Your cart is empty.");
}

// Paystack needs the amount in KOBO
$amount_in_kobo = $total_naira * 100;

// 3. Get User Email (Use a fallback if session is empty for testing)
$email = $_SESSION['user_email'] ?? "arowoduyefawaz20@gmail.com";

$url = "https://api.paystack.co/transaction/initialize";

$fields = [
    'email' => $email,
    'amount' => $amount_in_kobo,
    'callback_url' => "http://localhost/pharmacy_pj/logic/payment_success.php",
    'metadata' => [
        'user_id' => $_SESSION['user_id'] ?? 0,
        'cart_summary' => json_encode($_SESSION['cart'])
    ]
];

$ch = curl_init();
curl_setopt($ch, CURLOPT_URL, $url);
curl_setopt($ch, CURLOPT_POST, true);
curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($fields));
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_HTTPHEADER, array(
    "Authorization: Bearer " . PSTK_SECRET_KEY, // Using the constant from db.php
    "Content-Type: application/json",
    "Cache-Control: no-cache"
));

$response = curl_exec($ch);
$err = curl_error($ch);
curl_close($ch);

// 4. Detailed Error Handling
if ($err) {
    die("CURL Error: " . $err);
}

$result = json_decode($response);

if ($result && $result->status) {
    // Redirect to Paystack Checkout Page
    header("Location: " . $result->data->authorization_url);
    exit;
} else {
    // This will now show you the ACTUAL reason from Paystack
    $error_message = isset($result->message) ? $result->message : "Unknown Error";
    die("Paystack API Error: " . $error_message);
}