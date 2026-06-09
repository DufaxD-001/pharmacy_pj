<?php
session_start();
if (!isset($_GET['invoice'])) {
    header("Location: ../index.php");
    exit();
}
$invoice_no = htmlspecialchars($_GET['invoice']);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Order Confirmed - Dufax Pharmacy</title>
    <style>
        body { font-family: 'Segoe UI', sans-serif; background: #f4f7f6; display: flex; align-items: center; justify-content: center; height: 100vh; margin: 0; }
        .card { background: white; padding: 40px; border-radius: 20px; box-shadow: 0 10px 30px rgba(0,0,0,0.05); text-align: center; max-width: 400px; width: 90%; }
        .icon { width: 70px; height: 70px; background: #28a745; color: white; border-radius: 50%; display: flex; align-items: center; justify-content: center; font-size: 35px; margin: 0 auto 20px; }
        h1 { color: #1a1a1a; margin: 0 0 10px 0; font-size: 24px; }
        p { color: #666; font-size: 15px; margin-bottom: 25px; line-height: 1.5; }
        .invoice-tag { background: #f8f9fa; color: #1a1a1a; padding: 10px; border-radius: 8px; font-weight: 700; font-family: monospace; display: block; margin-bottom: 30px; border: 1px solid #eee; }
        .btn-home { background: #1a1a1a; color: white; text-decoration: none; padding: 15px 30px; border-radius: 12px; font-weight: 700; display: block; transition: 0.3s; }
        .btn-home:hover { background: #28a745; transform: translateY(-2px); }
    </style>
</head>
<body>
    <div class="card">
        <div class="icon">✓</div>
        <h1>Payment Successful</h1>
        <p>Your order has been received and is now being prepared for delivery.</p>
        <span class="invoice-tag">Invoice: <?php echo $invoice_no; ?></span>
        <a href="../index.php" class="btn-home">Go to Homepage</a>
    </div>
</body>
</html>