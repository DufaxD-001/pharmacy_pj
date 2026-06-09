<?php
session_start();
require_once('../logic/db.php');

// 1. Get Search Term
$search = $_GET['search'] ?? '';

// 2. Prepare Query (Filters by Invoice, Reference, or Username)
$query = "SELECT o.*, u.username 
          FROM orders o 
          JOIN users u ON o.user_id = u.id";

if (!empty($search)) {
    $query .= " WHERE o.invoice_no LIKE ? OR o.reference LIKE ? OR u.username LIKE ?";
}

$query .= " ORDER BY o.created_at DESC";

$stmt = $conn->prepare($query);

if (!empty($search)) {
    $searchTerm = "%$search%";
    $stmt->bind_param("sss", $searchTerm, $searchTerm, $searchTerm);
}

$stmt->execute();
$result = $stmt->get_result();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Transaction History - Dufax Pharmacy</title>
    <style>
        body { font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; background: #f0f2f5; padding: 20px; }
        .container { max-width: 1000px; margin: auto; background: white; padding: 25px; border-radius: 12px; box-shadow: 0 4px 20px rgba(0,0,0,0.08); }
        .header-flex { display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px; }
        
        /* Search Bar Styling */
        .search-box { display: flex; gap: 10px; margin-bottom: 20px; }
        .search-box input { flex: 1; padding: 10px; border: 1px solid #ddd; border-radius: 5px; }
        .search-box button { padding: 10px 20px; background: #007bff; color: white; border: none; border-radius: 5px; cursor: pointer; }
        
        table { width: 100%; border-collapse: collapse; }
        th, td { padding: 15px; border-bottom: 1px solid #eee; text-align: left; }
        th { background: #f8f9fa; color: #333; }
        .btn-print { background: #28a745; color: white; padding: 6px 12px; text-decoration: none; border-radius: 4px; font-weight: bold; }
        .btn-print:hover { background: #218838; }
        .back-link { text-decoration: none; color: #666; font-size: 14px; }
    </style>
</head>
<body>

<div class="container">
    <div class="header-flex">
        <h2>📜 Transaction History</h2>
        <a href="../index.php" class="back-link">⬅ Back to Dashboard</a>
    </div>

    <!-- Search Form -->
    <form class="search-box" method="GET" action="">
        <input type="text" name="search" placeholder="Search by Invoice #, Reference, or Staff Name..." value="<?php echo htmlspecialchars($search); ?>">
        <button type="submit">Search</button>
        <?php if(!empty($search)): ?>
            <a href="sales_history.php" style="padding: 10px; text-decoration:none; color: red;">Clear</a>
        <?php endif; ?>
    </form>
    
    <table>
        <thead>
            <tr>
                <th>Date</th>
                <th>Invoice No</th>
                <th>Reference</th>
                <th>Total</th>
                <th>Staff</th>
                <th>Action</th>
            </tr>
        </thead>
        <tbody>
            <?php if ($result->num_rows > 0): ?>
                <?php while($row = $result->fetch_assoc()): ?>
                <tr>
                    <td><?php echo date('d-M-y H:i', strtotime($row['created_at'])); ?></td>
                    <td><strong><?php echo $row['invoice_no']; ?></strong></td>
                    <td><small style="color:#777;"><?php echo $row['reference']; ?></small></td>
                    <td><strong>₦<?php echo number_format($row['total_price'], 2); ?></strong></td>
                    <td><?php echo htmlspecialchars($row['username']); ?></td>
                    <td>
                        <a href="receipt.php?id=<?php echo $row['id']; ?>" target="_blank" class="btn-print">
                            Print 🖨️
                        </a>
                    </td>
                </tr>
                <?php endwhile; ?>
            <?php else: ?>
                <tr><td colspan="6" style="text-align:center; padding: 40px; color: #999;">No matching transactions found.</td></tr>
            <?php endif; ?>
        </tbody>
    </table>
</div>

</body>
</html>