<?php
if (session_status() === PHP_SESSION_NONE) { session_start(); }
require_once('../logic/db.php');

// 1. Get the ID from the URL safely
$sale_id = intval($_GET['id'] ?? 0);

if ($sale_id <= 0) {
    die("<div style='font-family:sans-serif; text-align:center; margin-top:50px;'><h3>⚠️ Invalid Sale ID.</h3><a href='staff_dashboard.php'>Back to Dashboard</a></div>");
}

// 2. Determine the Back button destination based on the session variable
if (isset($_SESSION['client_id'])) {
    // Points back to the root application container
    $back_to = "/pharmacy_pj/index.php";
} else {
    // Points back to the root app container and lets the dashboard know it came from a successful sale
    $back_to = "/pharmacy_pj/index.php?status=success";
}

// 3. Fetch from 'orders' table (Including the user who checked it out)
$stmt = $conn->prepare("SELECT o.*, u.username 
                        FROM orders o 
                        JOIN users u ON o.user_id = u.id 
                        WHERE o.id = ?");
$stmt->bind_param("i", $sale_id);
$stmt->execute();
$sale = $stmt->get_result()->fetch_assoc();

if (!$sale) { 
    die("<div style='font-family:sans-serif; text-align:center; margin-top:50px;'><h3>⚠️ Order record #$sale_id not found in the database.</h3><a href='$back_to'>Back to Dashboard</a></div>"); 
}

// 4. Fetch Items from 'order_items'
$items_stmt = $conn->prepare("SELECT oi.*, p.product_name 
                              FROM order_items oi 
                              JOIN products p ON oi.product_id = p.id 
                              WHERE oi.order_id = ?");
$items_stmt->bind_param("i", $sale_id);
$items_stmt->execute();
$items = $items_stmt->get_result();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Receipt #<?php echo htmlspecialchars($sale['invoice_no'] ?? $sale['id']); ?></title>
    <style>
        body { font-family: 'Courier New', Courier, monospace; width: 300px; margin: 20px auto; font-size: 12px; color: #000; background: #fff; }
        .text-center { text-align: center; }
        .divider { border-top: 1px dashed #000; margin: 10px 0; }
        table { width: 100%; border-collapse: collapse; }
        th { text-align: left; border-bottom: 1px solid #000; padding-bottom: 3px; }
        td { padding: 4px 0; vertical-align: top; }
        .footer { margin-top: 20px; font-size: 10px; }
        
        @media print { 
            .no-print { display: none !important; } 
            body { margin: 0; width: 100%; }
        }
    </style>
</head>
<body>

    <div class="text-center">
        <h2 style="margin:0; font-weight: 800; letter-spacing: -0.5px;">DUFAXPLUS PHARMACY</h2>
        <p style="margin: 4px 0;">Agba-Dam Area, Ilorin, Kwara State<br>Tel: +234 (0) 800 DUFAX POS</p>
        <p style="margin: 5px 0 0 0;"><strong>Receipt: #<?php echo htmlspecialchars($sale['invoice_no'] ?? $sale['id']); ?></strong></p>
    </div>

    <div class="divider"></div>
    <p style="margin: 0; line-height: 1.4;">
        <strong>Ref: <?php echo htmlspecialchars($sale['reference']); ?></strong><br>
        Date: <?php echo date('d-M-Y H:i', strtotime($sale['order_date'])); ?><br>
        Processed by: <?php echo htmlspecialchars($sale['username']); ?>
    </p>
    <div class="divider"></div>

    <table>
        <thead>
            <tr>
                <th>Item</th>
                <th style="width: 40px; text-align: center;">Qty</th>
                <th style="text-align: right; width: 80px;">Total</th>
            </tr>
        </thead>
        <tbody>
            <?php while($row = $items->fetch_assoc()): ?>
            <tr>
                <td><?php echo htmlspecialchars($row['product_name']); ?></td>
                <td style="text-align: center;"><?php echo $row['quantity']; ?></td>
                <td style="text-align: right;">
                    ₦<?php echo number_format($row['quantity'] * $row['unit_price'], 2); ?>
                </td>
            </tr>
            <?php endwhile; ?>
        </tbody>
    </table>

    <div class="divider"></div>
    
    <div style="display:flex; justify-content:space-between; font-weight:bold; font-size:13px; margin-bottom: 4px;">
        <span>TOTAL PAID</span>
        <span>₦<?php echo number_format($sale['total_price'], 2); ?></span>
    </div>
    
    <div style="display:flex; justify-content:space-between; margin-bottom: 2px;">
        <span>Payment Method:</span>
        <span style="font-weight: 600; text-transform: uppercase;">
            <?php echo htmlspecialchars($sale['payment_method'] ?? 'Counter Sale'); ?>
        </span>
    </div>

    <div style="display:flex; justify-content:space-between;">
        <span>Status:</span>
        <span style="font-weight: 600; text-transform: uppercase;"><?php echo htmlspecialchars($sale['payment_status']); ?></span>
    </div>

    <div class="divider"></div>
    <div class="footer text-center">
        <p style="margin: 0 0 15px 0; font-weight: 600;">Thank you for your patronage!<br>Wishing you a quick recovery.</p>
        
        <div class="no-print">
            <button onclick="window.print()" style="padding:12px; width:100%; cursor:pointer; font-weight:bold; background:#2ecc71; color:#fff; border:none; border-radius:6px; margin-bottom:10px; font-size: 12px; transition: 0.2s;">
                Print Receipt 🖨️
            </button>
            
            <a href="<?php echo $back_to; ?>" style="display:block; text-align:center; text-decoration:none; color:#1e2124; font-weight:bold; padding:11px; border:1px solid #dfe6e9; border-radius:6px; background: #f8f9fa; font-size: 11px;">
                Back to Dashboard
            </a>
        </div>
    </div>

    <script>
        // Smooth print execution trigger on load completion
        window.onload = function() {
            setTimeout(function() {
                window.print();
            }, 400);
        };
    </script>

</body>
</html>