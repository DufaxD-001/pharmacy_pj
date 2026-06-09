<?php
session_start();
require_once('../logic/db.php');

// 1. Security Check: Is the user logged in?
if (!isset($_SESSION['user_id'])) {
    header("Location: ../login.php");
    exit();
}

// 2. Get and Validate Order ID
$order_id = isset($_GET['id']) ? intval($_GET['id']) : 0;
$user_id = $_SESSION['user_id'];
$role = $_SESSION['user_role'];

// 3. Fetch Order Details
$query = "SELECT orders.*, users.username, users.email 
          FROM orders 
          JOIN users ON orders.user_id = users.id 
          WHERE orders.id = $order_id";

if ($role !== 'admin') {
    $query .= " AND orders.user_id = $user_id";
}

$order_res = $conn->query($query);

if (!$order_res || $order_res->num_rows == 0) {
    // UPDATED: Back link to transactions.php if not found
    die("<div style='padding:50px; text-align:center;'><h2>Invoice not found.</h2><a href='transactions.php'>Back to History</a></div>");
}

$order = $order_res->fetch_assoc();

// 4. Fetch Items in this Order
$items_query = "SELECT order_items.*, products.product_name 
                FROM order_items 
                JOIN products ON order_items.product_id = products.id 
                WHERE order_items.order_id = $order_id";
$items_res = $conn->query($items_query);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Invoice - <?php echo $order['invoice_no']; ?></title>
    <style>
        body { font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; background: #f4f7f6; color: #333; }
        .invoice-box { max-width: 800px; margin: 30px auto; padding: 40px; background: #fff; border-radius: 10px; box-shadow: 0 0 20px rgba(0,0,0,0.1); }
        .header { display: flex; justify-content: space-between; border-bottom: 2px solid #eee; padding-bottom: 20px; }
        .status-paid { color: #28a745; font-weight: bold; text-transform: uppercase; }
        table { width: 100%; line-height: inherit; text-align: left; border-collapse: collapse; margin-top: 30px; }
        table th { background: #f8f9fa; padding: 12px; border-bottom: 2px solid #eee; }
        table td { padding: 12px; border-bottom: 1px solid #eee; }
        .total-row { font-size: 20px; font-weight: bold; color: #28a745; }
        .no-print-btn { background: #007bff; color: #fff; padding: 10px 20px; border: none; border-radius: 5px; cursor: pointer; text-decoration: none; display: inline-block; }
        @media print { .no-print { display: none; } .invoice-box { box-shadow: none; margin: 0; width: 100%; } }
    </style>
</head>
<body>

<div class="no-print" style="max-width: 800px; margin: 20px auto; display: flex; justify-content: space-between;">
    <a href="transactions.php" class="no-print-btn" style="background: #666;">← Back to History</a>
    
    <button onclick="window.print()" class="no-print-btn">Print Invoice</button>
</div>

<div class="invoice-box">
    <div class="header">
        <div>
            <h1 style="margin: 0; color: #007bff;"><?php echo SITE_NAME; ?></h1>
            <p>Official Pharmacy Receipt</p>
        </div>
        <div style="text-align: right;">
            <h3 style="margin: 0;">INVOICE</h3>
            <p>#<?php echo $order['invoice_no']; ?></p>
            <p>Date: <?php echo date('d M, Y', strtotime($order['created_at'])); ?></p>
        </div>
    </div>
<div>
    <strong>Billed To:</strong><br>
    <span style="font-size: 16px; font-weight: 600;">
        <?php echo htmlspecialchars($order['username']); ?>
    </span><br>
    
    <?php if (!empty($order['email'])): ?>
        <span style="color: #666;"><?php echo htmlspecialchars($order['email']); ?></span>
    <?php else: ?>
        <span style="color: #999; font-style: italic; font-size: 12px;">No email on file</span>
    <?php endif; ?>
</div>
        <div style="text-align: right;">
            <strong>Payment Info:</strong><br>
            Status: <span class="status-paid"><?php echo $order['payment_status']; ?></span><br>
            Ref: <?php echo $order['reference']; ?>
        </div>
    </div>

    <table>
        <thead>
            <tr>
                <th>Item Description</th>
                <th>Qty</th>
                <th>Unit Price</th>
                <th>Subtotal</th>
            </tr>
        </thead>
        <tbody>
            <?php while($item = $items_res->fetch_assoc()): ?>
            <tr>
                <td><?php echo htmlspecialchars($item['product_name']); ?></td>
                <td><?php echo $item['quantity']; ?></td>
                <td><?php echo CURRENCY_SYMBOL . number_format($item['unit_price'], 2); ?></td>
                <td><?php echo CURRENCY_SYMBOL . number_format($item['quantity'] * $item['unit_price'], 2); ?></td>
            </tr>
            <?php endwhile; ?>
        </tbody>
        <tfoot>
            <tr>
                <td colspan="2"></td>
                <td style="text-align: right; padding-top: 20px;"><strong>Grand Total:</strong></td>
                <td class="total-row" style="padding-top: 20px;"><?php echo CURRENCY_SYMBOL . number_format($order['total_price'], 2); ?></td>
            </tr>
        </tfoot>
    </table>

    <div style="margin-top: 50px; text-align: center; color: #999; font-size: 12px;">
        <p>Thank you for choosing <?php echo SITE_NAME; ?>!</p>
        <p>This is a computer-generated receipt and requires no signature.</p>
    </div>
</div>

</body>
</html>