<?php
session_start();
require_once('../logic/db.php');

if (!isset($_SESSION['user_id'])) {
    header("Location: ../login.php");
    exit();
}

$user_id = $_SESSION['user_id'];
$query = "SELECT * FROM orders WHERE user_id = $user_id ORDER BY created_at DESC";
$result = $conn->query($query);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>My Orders - <?php echo SITE_NAME; ?></title>
    <style>
        body { background: #f4f7f6; padding: 40px; font-family: 'Segoe UI', sans-serif; }
        .container { max-width: 1000px; margin: 0 auto; }
        .order-card { background: white; padding: 20px; border-radius: 12px; box-shadow: 0 4px 6px rgba(0,0,0,0.05); }
        table { width: 100%; border-collapse: collapse; margin-top: 20px; }
        th { text-align: left; color: #888; padding: 15px; border-bottom: 2px solid #eee; }
        td { padding: 15px; border-bottom: 1px solid #eee; }
        .btn-view { background: #007bff; color: white; text-decoration: none; padding: 8px 15px; border-radius: 6px; font-size: 13px; }
    </style>
</head>
<body>
    <div class="container">
        <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 30px;">
            <h2>Your Order History</h2>
            <a href="../index.php" style="color: #666; text-decoration: none; font-weight: bold;">← Back to Shopping</a>
        </div>

        <div class="order-card">
            <table>
                <thead>
                    <tr>
                        <th>Date</th>
                        <th>Invoice #</th>
                        <th>Amount</th>
                        <th>Status</th>
                        <th>Action</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if ($result->num_rows > 0): ?>
                        <?php while($row = $result->fetch_assoc()): ?>
                        <tr>
                            <td><?php echo date('M d, Y', strtotime($row['created_at'])); ?></td>
                            <td><strong><?php echo $row['invoice_no']; ?></strong></td>
                            <td style="font-weight: bold; color: #28a745;"><?php echo CURRENCY_SYMBOL . number_format($row['total_price'], 2); ?></td>
                            <td><span style="background: #e8f5e9; color: #2e7d32; padding: 4px 8px; border-radius: 4px; font-size: 12px; font-weight: bold;"><?php echo strtoupper($row['payment_status']); ?></span></td>
                            <td><a href="view_invoice.php?id=<?php echo $row['id']; ?>" class="btn-view">View Invoice</a></td>
                        </tr>
                        <?php endwhile; ?>
                    <?php else: ?>
                        <tr><td colspan="5" style="text-align: center; padding: 50px; color: #999;">No orders found.</td></tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</body>
</html>