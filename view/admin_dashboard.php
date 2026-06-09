<?php
if (session_status() === PHP_SESSION_NONE) { session_start(); }
// SECURITY GATE: If not logged in, kick them back to login page
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php?error=unauthorized");
    exit();
}

// Database connection logic
if (file_exists('logic/db.php')) { require_once('logic/db.php'); } 
elseif (file_exists('../logic/db.php')) { require_once('../logic/db.php'); } 
else { die("Error: Connection file missing."); }

// --- LOGIC: Fetch Analytics ---
$total_drugs = $conn->query("SELECT COUNT(*) as total FROM products")->fetch_assoc()['total'];
$low_stock = $conn->query("SELECT COUNT(*) as total FROM products WHERE quantity <= 10 AND quantity > 0")->fetch_assoc()['total'];
$out_of_stock = $conn->query("SELECT COUNT(*) as total FROM products WHERE quantity = 0")->fetch_assoc()['total'];

// Calculate Total Inventory Value (Price * Quantity)
$value_res = $conn->query("SELECT SUM(price * quantity) as total_val FROM products");
$inventory_value = $value_res->fetch_assoc()['total_val'] ?? 0;

// NEW: Build a direct lookup array of product counts per category from the DB
$category_counts = [];
$cat_stats_query = "SELECT category, COUNT(*) as count FROM products GROUP BY category";
$cat_stats_result = $conn->query($cat_stats_query);
if ($cat_stats_result) {
    while ($row = $cat_stats_result->fetch_assoc()) {
        $category_counts[$row['category']] = (int)$row['count'];
    }
}

// EXACT MATCH: Mirroring the array directly from your client dashboard
$synchronized_categories = [
    'Pain Relief' => '💊',
    'Antibiotics'  => '🧬',
    'Supplements'  => '🧪',
    'Vitamins'    => '🌿', 
    'Baby Care'   => '🍼', 
    'First Aid'   => '🩹', 
    'Skincare'    => '🧴'
];
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Dufax Pharmacy | Admin Panel</title>
    <style>
        :root { 
            --primary: #2ecc71; --primary-dark: #27ae60;
            --danger: #e74c3c; --warning: #f1c40f;
            --dark: #2c3e50; --light-bg: #f8f9fa; 
        }
        body { font-family: 'Inter', 'Segoe UI', sans-serif; background: var(--light-bg); margin: 0; color: #333; }
        
        /* Updated Dashboard Header Layout */
        .df-header { 
            background: rgba(255, 255, 255, 0.95); padding: 15px 40px; 
            display: flex; justify-content: space-between; align-items: center; 
            box-shadow: 0 2px 15px rgba(0,0,0,0.05); position: sticky; top: 0; z-index: 100;
            backdrop-filter: blur(10px);
        }
        .df-logo { margin: 0; font-size: 20px; letter-spacing: -1px; font-weight: 700; color: var(--dark); }
        .df-subtitle { margin: 2px 0 0 0; font-size: 11px; color: #7f8c8d; font-weight: 500; text-transform: uppercase; letter-spacing: 0.5px; }

        .container { max-width: 1300px; margin: 30px auto; padding: 0 25px; }

        /* Modern Stats Grid */
        .stats-grid { display: grid; grid-template-columns: repeat(auto-fit, minmax(220px, 1fr)); gap: 20px; margin-bottom: 40px; }
        .stat-card { 
            background: white; padding: 20px; border-radius: 15px; 
            box-shadow: 0 5px 15px rgba(0,0,0,0.02); border-left: 6px solid var(--primary);
            transition: transform 0.3s ease;
        }
        .stat-card:hover { transform: translateY(-5px); }
        .stat-card h3 { margin: 0; color: #7f8c8d; font-size: 12px; text-transform: uppercase; letter-spacing: 1px; }
        .stat-card .value { font-size: 24px; font-weight: 700; margin-top: 8px; color: var(--dark); }

        /* Category Cards */
        .section-header { display: flex; align-items: center; justify-content: space-between; margin-bottom: 20px; }
        .section-title { font-size: 20px; font-weight: 700; color: var(--dark); display: flex; align-items: center; gap: 10px; }
        
        .category-grid { display: grid; grid-template-columns: repeat(auto-fill, minmax(180px, 1fr)); gap: 20px; }
        .category-card { 
            background: white; padding: 25px; border-radius: 20px; text-decoration: none; color: inherit;
            border: 1px solid rgba(0,0,0,0.05); text-align: center; transition: all 0.3s ease;
        }
        .category-card:hover { background: var(--primary); color: white; border-color: var(--primary); }
        .category-card .icon { font-size: 28px; margin-bottom: 10px; }
        .category-card .count { font-size: 11px; opacity: 0.8; margin-top: 5px; font-weight: 600; }

        /* Search and Buttons */
        .search-box { background: #eee; padding: 10px 20px; border-radius: 12px; width: 300px; display: flex; align-items: center; }
        .search-box input { border: none; background: transparent; outline: none; margin-left: 10px; width: 100%; }
        .btn-add { background: var(--dark); color: white; padding: 12px 20px; border-radius: 12px; text-decoration: none; font-weight: 600; font-size: 14px; }

        /* Stock Progress Bar */
        .stock-bar-bg { background: #eee; border-radius: 10px; height: 8px; width: 100px; margin-top: 5px; overflow: hidden; }
        .stock-bar-fill { height: 100%; border-radius: 10px; }

        /* Table Styling */
        .results-container { 
            background: white; padding: 30px; border-radius: 20px; 
            box-shadow: 0 10px 40px rgba(0,0,0,0.04); margin-top: 30px; border: 1px solid #eee;
        }
        table { width: 100%; border-collapse: collapse; }
        th { text-align: left; padding: 15px; color: #95a5a6; font-size: 11px; text-transform: uppercase; border-bottom: 1px solid #f1f1f1; }
        td { padding: 15px; border-bottom: 1px solid #f9f9f9; font-size: 14px; }
        
        .badge { padding: 4px 10px; border-radius: 6px; font-size: 10px; font-weight: 700; text-transform: uppercase; }
        .badge-low { background: #fff3cd; color: #856404; }
        .badge-out { background: #f8d7da; color: #721c24; }
        .badge-ok { background: #d4edda; color: #155724; }
    </style>
</head>
<body>

<header class="df-header">
    <div>
        <h1 class="df-logo">
            Dufax <span style="color: #28a745;">Care</span> Dashboard
        </h1>
        <p class="df-subtitle">
            Premium Pharmaceutical Services
        </p>
    </div>
    <div style="display: flex; gap: 15px; align-items: center;">
        <form action="index.php" method="GET" class="search-box">
            <span>🔍</span>
            <input type="text" name="search" placeholder="Search inventory..." value="<?php echo htmlspecialchars($_GET['search'] ?? ''); ?>">
        </form>
        <form action="import_csv.php" method="POST" enctype="multipart/form-data" class="upload-form-inline" style="display: flex; align-items: center; background: #fff; padding: 6px 12px; border-radius: 12px; border: 1px solid #ddd; gap: 8px;">
            <span title="Bulk upload data via CSV file">📊</span>
            <input type="file" name="csv_file" accept=".csv" required style="font-size: 12px; max-width: 170px;">
            <button type="submit" name="import_submit" style="background: var(--primary); color: white; border: none; padding: 8px 14px; border-radius: 8px; font-weight: 600; font-size: 12px; cursor: pointer;">Bulk Import</button>
        </form>
        <a href="view/add_product.php" class="btn-add">+ Add Product</a>
    </div>
</header>

<div class="container">
    
    <?php if (isset($_GET['import_success'])): ?>
        <div style="padding: 15px 20px; border-radius: 12px; margin-bottom: 25px; font-size: 14px; font-weight: 600; background: #d4edda; color: #155724; border: 1px solid #c3e6cb;">
            🎉 Bulk Inventory Updated! Imported <strong><?php echo intval($_GET['inserted']); ?></strong> new products and updated <strong><?php echo intval($_GET['updated']); ?></strong> items.
        </div>
    <?php endif; ?>

    <?php if (isset($_GET['import_error'])): ?>
        <div style="padding: 15px 20px; border-radius: 12px; margin-bottom: 25px; font-size: 14px; font-weight: 600; background: #f8d7da; color: #721c24; border: 1px solid #f5c6cb;">
            ⚠️ Import Failure: <?php echo htmlspecialchars($_GET['import_error']); ?>. Check your layout columns and try again.
        </div>
    <?php endif; ?>
    
    <div class="stats-grid">
        <div class="stat-card" style="border-color: #3498db;">
            <h3>Total Inventory</h3>
            <div class="value"><?php echo number_format($total_drugs); ?> <span style="font-size: 14px; color: #999;">SKUs</span></div>
        </div>
        <div class="stat-card" style="border-color: var(--primary);">
            <h3>Asset Value</h3>
            <div class="value">₦<?php echo number_format($inventory_value, 2); ?></div>
        </div>
        <div class="stat-card" style="border-color: var(--warning);">
            <h3>Low Stock</h3>
            <div class="value"><?php echo $low_stock; ?> <span style="font-size: 14px; color: #999;">Items</span></div>
        </div>
        <div class="stat-card" style="border-color: var(--danger);">
            <h3>Out of Stock</h3>
            <div class="value" style="color: var(--danger);"><?php echo $out_of_stock; ?></div>
        </div>
    </div>

    <div class="section-header">
        <div class="section-title">📂 Departmental View</div>
        <a href="index.php?view_all=1#results" style="color: var(--primary); text-decoration: none; font-size: 13px; font-weight: 600;">View All Inventory →</a>
    </div>

    <div class="category-grid">
        <?php foreach ($synchronized_categories as $name => $icon): 
            // Fallback to 0 if no products exist for this category yet
            $count = isset($category_counts[$name]) ? $category_counts[$name] : 0; 
        ?>
            <a href="index.php?cat=<?php echo urlencode($name); ?>#results" class="category-card">
                <div class="icon"><?php echo $icon; ?></div>
                <div class="name" style="font-weight: 600; font-size: 14px;"><?php echo htmlspecialchars($name); ?></div>
                <div class="count"><?php echo $count; ?> Products</div>
            </a>
        <?php endforeach; ?>
    </div>

    <?php if(isset($_GET['cat']) || isset($_GET['search']) || isset($_GET['view_all'])): ?>
        <div class="results-container" id="results">
            <div style="display:flex; justify-content: space-between; align-items:center; margin-bottom: 25px;">
                <h3 style="margin:0;">📦 <?php echo htmlspecialchars($_GET['cat'] ?? ($_GET['search'] ?? 'Master Inventory')); ?></h3>
                <a href="index.php" style="text-decoration:none; color: #95a5a6; font-size: 13px; font-weight: 600;">✕ Close View</a>
            </div>
            
            <table>
                <thead>
                    <tr>
                        <th>Product Details</th>
                        <th>Category</th>
                        <th>Unit Price</th>
                        <th>Inventory Level</th>
                        <th>Management</th>
                    </tr>
                </thead>
                <tbody>
                    <?php
                    $filter = "";
                    if(isset($_GET['cat'])) {
                        $c = $conn->real_escape_string($_GET['cat']);
                        $filter = "WHERE category = '$c'";
                    } elseif(isset($_GET['search'])) {
                        $s = $conn->real_escape_string($_GET['search']);
                        $filter = "WHERE product_name LIKE '%$s%' OR category LIKE '%$s%'";
                    }

                    $list_res = $conn->query("SELECT * FROM products $filter ORDER BY product_name ASC");

                    if($list_res && $list_res->num_rows > 0):
                        while($item = $list_res->fetch_assoc()): 
                            // Calculate Stock Percentage for the bar (Assuming 100 is "Full")
                            $percent = min(($item['quantity'] / 100) * 100, 100);
                            $bar_color = ($item['quantity'] <= 0) ? 'var(--danger)' : (($item['quantity'] <= 10) ? 'var(--warning)' : 'var(--primary)');
                        ?>
                        <tr>
                            <td><div style="font-weight: 700; color: var(--dark);"><?php echo htmlspecialchars($item['product_name']); ?></div></td>
                            <td><span style="color: #7f8c8d;"><?php echo htmlspecialchars($item['category']); ?></span></td>
                            <td style="font-family: monospace; font-weight: 600;">₦<?php echo number_format($item['price'], 2); ?></td>
                            <td>
                                <div style="display: flex; align-items: center; gap: 10px;">
                                    <div style="font-weight: 700; width: 30px;"><?php echo $item['quantity']; ?></div>
                                    <div class="stock-bar-bg">
                                        <div class="stock-bar-fill" style="width: <?php echo $percent; ?>%; background: <?php echo $bar_color; ?>;"></div>
                                    </div>
                                    <?php if($item['quantity'] == 0): ?>
                                        <span class="badge badge-out">Empty</span>
                                    <?php elseif($item['quantity'] <= 10): ?>
                                        <span class="badge badge-low">Low</span>
                                    <?php else: ?>
                                        <span class="badge badge-ok">Good</span>
                                    <?php endif; ?>
                                </div>
                            </td>
                            <td>
                                <a href="view/edit_product.php?id=<?php echo $item['id']; ?>" style="color: #3498db; text-decoration: none; font-weight: 700; font-size: 12px;">Manage →</a>
                            </td>
                        </tr>
                        <?php endwhile; else: ?>
                        <tr><td colspan="5" style="padding: 40px; text-align: center; color: #bdc3c7;">No matching records found.</td></tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    <?php endif; ?>
</div>

</body>
</html>