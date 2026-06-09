<?php
session_start();
require_once('../logic/db.php');

// Define constants if not already defined
if(!defined('SITE_NAME')) define('SITE_NAME', 'Dufax Pharmacy');
if(!defined('CURRENCY_SYMBOL')) define('CURRENCY_SYMBOL', '₦');

$category_name = isset($_GET['cat']) ? $conn->real_escape_string($_GET['cat']) : 'All Categories';
$search_term = isset($_GET['query']) ? $conn->real_escape_string($_GET['query']) : '';

// Professional Query
$sql = "SELECT * FROM products WHERE category = '$category_name'";
if (!empty($search_term)) {
    $sql .= " AND (product_name LIKE '%$search_term%')";
}
$sql .= " ORDER BY product_name ASC";
$result = $conn->query($sql);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title><?php echo $category_name; ?> - <?php echo SITE_NAME; ?></title>
    <style>
        body { font-family: 'Segoe UI', Tahoma, sans-serif; background: #f8f9fa; padding: 40px; color: #333; }
        .container { max-width: 1100px; margin: auto; }
        .filter-bar { background: white; padding: 20px; border-radius: 12px; display: flex; gap: 10px; box-shadow: 0 4px 12px rgba(0,0,0,0.05); margin-bottom: 30px; }
        .grid { display: grid; grid-template-columns: repeat(auto-fill, minmax(280px, 1fr)); gap: 25px; }
        .product-card { background: white; padding: 25px; border-radius: 16px; border: 1px solid #eee; transition: 0.3s; display: flex; flex-direction: column; justify-content: space-between; }
        .product-card:hover { transform: translateY(-5px); box-shadow: 0 12px 25px rgba(0,0,0,0.08); }
        .price { color: #28a745; font-weight: 700; font-size: 22px; margin: 10px 0; }
        .status-badge { display: inline-block; padding: 4px 10px; border-radius: 20px; font-size: 11px; font-weight: 700; text-transform: uppercase; margin-bottom: 10px; }
        .status-available { background: #e7f3eb; color: #28a745; }
        .status-out { background: #fdeaea; color: #d9534f; }
        .back-btn { text-decoration: none; color: #666; font-size: 14px; margin-bottom: 20px; display: inline-block; transition: 0.2s; }
        .back-btn:hover { color: #28a745; }
        
        /* Form Styling */
        .add-form { margin-top: 15px; display: flex; flex-direction: column; gap: 10px; }
        .qty-wrapper { display: flex; align-items: center; justify-content: space-between; background: #f8f9fa; padding: 8px 12px; border-radius: 8px; }
        .qty-input { width: 60px; padding: 5px; border: 1px solid #ddd; border-radius: 4px; text-align: center; font-weight: 600; }
        .add-btn { width: 100%; padding: 12px; background: #1a1a1a; color: white; border: none; border-radius: 8px; cursor: pointer; font-weight: 600; transition: 0.3s; }
        .add-btn:hover:not(:disabled) { background: #28a745; }
        .add-btn:disabled { background: #ccc; cursor: not-allowed; }
    </style>
</head>
<body>

<div class="container">
    <a href="../index.php" class="back-btn">← Back to Dashboard</a>
    
    <div style="display: flex; justify-content: space-between; align-items: flex-end; margin-bottom: 15px;">
        <div>
            <h1 style="margin: 0; color: #1a1a1a; font-size: 32px;"><?php echo $category_name; ?></h1>
            <p style="margin: 5px 0 0 0; color: #888;">Browse our certified pharmaceutical collection</p>
        </div>
        <span style="background: #eee; padding: 5px 15px; border-radius: 20px; font-size: 13px; font-weight: 600;">
            <?php echo $result->num_rows; ?> Items
        </span>
    </div>

    <section class="filter-bar">
        <form method="GET" style="display: flex; width: 100%; gap: 10px;">
            <input type="hidden" name="cat" value="<?php echo htmlspecialchars($category_name); ?>">
            <input type="text" name="query" placeholder="Search within <?php echo htmlspecialchars($category_name); ?>..." 
                   value="<?php echo htmlspecialchars($search_term); ?>"
                   style="flex: 1; padding: 12px; border: 1.5px solid #eee; border-radius: 8px; outline: none; font-size: 15px;">
            <button type="submit" style="padding: 12px 25px; background: #28a745; color: white; border: none; border-radius: 8px; cursor: pointer; font-weight: 600;">
                Filter
            </button>
            <?php if(!empty($search_term)): ?>
                <a href="category_view.php?cat=<?php echo urlencode($category_name); ?>" 
                   style="display: flex; align-items: center; justify-content: center; width: 45px; background: #fdeaea; color: #d9534f; border-radius: 8px; text-decoration: none; font-weight: bold;">✕</a>
            <?php endif; ?>
        </form>
    </section>

    <div class="grid">
        <?php if ($result->num_rows > 0): ?>
            <?php while($row = $result->fetch_assoc()): ?>
                <div class="product-card">
                    <div>
                        <?php if ($row['quantity'] > 0): ?>
                            <span class="status-badge status-available">● Available</span>
                        <?php else: ?>
                            <span class="status-badge status-out">● Out of Stock</span>
                        <?php endif; ?>

                        <h3 style="margin: 5px 0 0 0; font-size: 18px; color: #1a1a1a;"><?php echo htmlspecialchars($row['product_name']); ?></h3>
                        <p style="font-size: 12px; color: #999; margin-bottom: 10px;"><?php echo htmlspecialchars($row['category']); ?></p>
                        
                        <div class="price"><?php echo CURRENCY_SYMBOL . number_format($row['price'], 2); ?></div>
                    </div>

           <form action="../logic/process_cart.php" method="POST" class="add-form">
    <input type="hidden" name="product_id" value="<?php echo $row['id']; ?>">
    <input type="hidden" name="action" value="add">
    
    <?php if ($row['quantity'] > 0): ?>
        <div class="qty-wrapper">
            <span style="font-size: 13px; font-weight: 600; color: #666;">Quantity</span>
            <input type="number" 
                   name="quantity" 
                   value="1" 
                   min="1" 
                   max="<?php echo $row['quantity']; ?>" 
                   class="qty-input"
                   oninput="validateQty(this, <?php echo $row['quantity']; ?>)">
        </div>
        <button type="submit" class="add-btn">Add to Cart</button>
    <?php else: ?>
        <button type="button" class="add-btn" disabled>Sold Out</button>
    <?php endif; ?>
</form>
                </div>
            <?php endwhile; ?>
        <?php else: ?>
            <div style="grid-column: 1/-1; text-align: center; padding: 60px; background: white; border-radius: 12px; border: 2px dashed #eee;">
                <span style="font-size: 40px;">🔍</span>
                <p style="color: #888; font-size: 16px; margin-top: 15px;">No medications found matching your request.</p>
                <a href="category_view.php?cat=<?php echo urlencode($category_name); ?>" style="color: #28a745; font-weight: 600;">Clear filters</a>
            </div>
        <?php endif; ?>
    </div>
</div>

<script>
function validateQty(input, maxAvailable) {
    const val = parseInt(input.value);
    
    if (val > maxAvailable) {
        alert("Sorry, we only have " + maxAvailable + " units of this item in stock.");
        input.value = maxAvailable;
    } else if (val < 1 || isNaN(val)) {
        input.value = 1;
    }
}
</script>

</body>
</html>