<?php
session_start();
require_once('../logic/db.php');

// 1. Pagination Logic
$limit = 12; // Number of drugs per page
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$offset = ($page - 1) * $limit;

// 2. Fetch total count for pagination calculation
$total_results = $conn->query("SELECT COUNT(*) as id FROM products")->fetch_assoc()['id'];
$total_pages = ceil($total_results / $limit);

// 3. Fetch products for current page
$query = "SELECT * FROM products ORDER BY product_name ASC LIMIT $limit OFFSET $offset";
$result = $conn->query($query);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>All Medications - <?php echo SITE_NAME; ?></title>
    <style>
        body { font-family: 'Inter', 'Segoe UI', sans-serif; background: #f4f7f6; margin: 0; padding: 20px; }
        .shop-container { max-width: 1200px; margin: 0 auto; display: flex; gap: 30px; }
        
        /* Sidebar for Categories */
        .sidebar { width: 250px; background: white; padding: 20px; border-radius: 16px; height: fit-content; position: sticky; top: 20px; box-shadow: 0 4px 12px rgba(0,0,0,0.05); }
        .sidebar h3 { font-size: 16px; margin-bottom: 15px; color: #333; }
        .sidebar a { display: block; padding: 10px; color: #555; text-decoration: none; border-radius: 8px; font-size: 14px; margin-bottom: 5px; }
        .sidebar a:hover { background: #f0fdf4; color: #28a745; }

        /* Main Content Area */
        .main-content { flex: 1; }
        .product-grid { display: grid; grid-template-columns: repeat(auto-fill, minmax(220px, 1fr)); gap: 20px; }
        .card { background: white; padding: 15px; border-radius: 16px; border: 1px solid #eee; text-align: center; transition: 0.3s; }
        .card:hover { transform: translateY(-5px); box-shadow: 0 10px 20px rgba(0,0,0,0.08); }
        
        /* Pagination Styling */
        .pagination { margin-top: 40px; display: flex; justify-content: center; gap: 10px; }
        .page-link { padding: 10px 18px; background: white; border: 1px solid #ddd; border-radius: 8px; text-decoration: none; color: #333; font-weight: 600; }
        .page-link.active { background: #28a745; color: white; border-color: #28a745; }
    </style>
</head>
<body>

<div class="shop-container">
    <aside class="sidebar">
        <a href="../index.php" style="font-weight: bold; color: #28a745; margin-bottom: 20px;">← Home</a>
        <h3>Categories</h3>
        <a href="category_view.php?cat=Pain Relief">💊 Pain Relief</a>
        <a href="category_view.php?cat=Vitamins">🌿 Vitamins</a>
        <a href="category_view.php?cat=Baby Care">🍼 Baby Care</a>
        <a href="category_view.php?cat=First Aid">🩹 First Aid</a>
        <a href="category_view.php?cat=Skincare">🧴 Skincare</a>
    </aside>

    <main class="main-content">
        <h2 style="margin-bottom: 25px;">Browse All Medications</h2>

        <div class="product-grid">
            <?php while($row = $result->fetch_assoc()): ?>
                <div class="card">
                    <span style="font-size: 10px; color: #999; text-transform: uppercase; font-weight: bold;"><?php echo $row['category']; ?></span>
                    <h4 style="margin: 10px 0; font-size: 16px;"><?php echo htmlspecialchars($row['product_name']); ?></h4>
                    <div style="color: #28a745; font-size: 18px; font-weight: 800; margin-bottom: 15px;">
                        <?php echo CURRENCY_SYMBOL . number_format($row['price'], 2); ?>
                    </div>
                    
                    <form action="../logic/process_cart.php" method="POST">
                        <input type="hidden" name="product_id" value="<?php echo $row['id']; ?>">
                        <input type="hidden" name="action" value="add">
                        <input type="number" name="quantity" value="1" min="1" max="<?php echo $row['quantity']; ?>" 
                               style="width: 50px; padding: 5px; margin-bottom: 10px; border-radius: 4px; border: 1px solid #ddd;">
                        <button type="submit" style="width: 100%; padding: 10px; background: #1a1a1a; color: white; border: none; border-radius: 8px; cursor: pointer; font-weight: bold;">
                            Add to Cart
                        </button>
                    </form>
                </div>
            <?php endwhile; ?>
        </div>

        <div class="pagination">
            <?php for($i = 1; $i <= $total_pages; $i++): ?>
                <a href="?page=<?php echo $i; ?>" class="page-link <?php echo ($page == $i) ? 'active' : ''; ?>">
                    <?php echo $i; ?>
                </a>
            <?php endfor; ?>
        </div>
    </main>
</div>

</body>
</html>