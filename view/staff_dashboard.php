<?php
if (session_status() === PHP_SESSION_NONE) { session_start(); }

// 1. Security Check
$allowed_roles = ['staff', 'pharmacist', 'admin'];
if (!isset($_SESSION['user_id']) || !in_array($_SESSION['user_role'], $allowed_roles)) {
    header("Location: ../login.php?error=unauthorized_access");
    exit(); 
}

// 2. Database Connection
require_once __DIR__ . '/../logic/db.php';

if (!defined('CURRENCY_SYMBOL')) { define('CURRENCY_SYMBOL', '₦'); }

$current_user_id = $_SESSION['user_id'];
$user_stmt = $conn->prepare("SELECT username FROM users WHERE id = ?");
$user_stmt->bind_param("i", $current_user_id);
$user_stmt->execute();
$user_res = $user_stmt->get_result()->fetch_assoc();
$display_name = $user_res['username'] ?? 'Staff'; 

$search = $_GET['search'] ?? "";
$cat_filter = $_GET['category'] ?? "";

$query = "SELECT * FROM products WHERE quantity > 0";
$params = [];
$types = "";

if ($search !== "") {
    $query .= " AND (product_name LIKE ? OR category LIKE ? OR barcode = ?)";
    $search_param = "%$search%";
    
    $params[] = $search_param; 
    $params[] = $search_param; 
    $params[] = $search; // Exact match for barcode
    $types .= "sss";
}

if ($cat_filter !== "") {
    $query .= " AND category = ?";
    $params[] = $cat_filter;
    $types .= "s";
}

$query .= " ORDER BY product_name ASC";
$stmt = $conn->prepare($query);
if (!empty($params)) { $stmt->bind_param($types, ...$params); }
$stmt->execute();
$result = $stmt->get_result();

// Fetch other categories from database, excluding the hardcoded ones to prevent duplicates
$categories_res = $conn->query("SELECT DISTINCT category FROM products WHERE category != '' AND category NOT IN ('Skincare', 'Vitamin', 'Babycare', 'First Aid')");

$total = 0;
if(!empty($_SESSION['cart'])) {
    foreach($_SESSION['cart'] as $item) {
        $total += ($item['price'] * $item['qty']);
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">
    <title>DufaxPlus POS | Terminal</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">
<style>
    :root {
        --primary-green: #2ecc71;
        --sidebar-bg: #1e2124;
        --content-bg: #f8f9fa;
        --text-main: #2d3436;
        --border: #dfe6e9;
        --shadow: 0 2px 15px rgba(0,0,0,0.05);
    }

    * { box-sizing: border-box; }

    html, body {
        min-height: 100vh;
        margin: 0;
        padding: 0;
        background: var(--content-bg);
    }

    body { 
        font-family: 'Inter', sans-serif; 
        display: flex;
        flex-direction: column;
    }

    /* Standardized Main Dashboard Header Layout */
    .df-header { 
        min-height: 60px;
        background: #fff; 
        padding: 10px 25px; 
        display: flex; 
        justify-content: space-between; 
        align-items: center; 
        border-bottom: 1px solid var(--border);
        flex-shrink: 0; 
        flex-wrap: wrap;
        gap: 15px;
        position: sticky;
        top: 0;
        z-index: 100;
        box-shadow: 0 2px 15px rgba(0,0,0,0.03);
    }

    .df-logo { margin: 0; font-size: 1.2rem; letter-spacing: -1px; font-weight: 800; color: var(--text-main); }
    .df-subtitle { margin: 2px 0 0 0; font-size: 11px; color: #7f8c8d; font-weight: 500; text-transform: uppercase; letter-spacing: 0.5px; }

    .main-wrapper {
        display: flex;
        flex: 1; 
        width: 100%;
        min-height: calc(100vh - 60px);
    }

    .sidebar {
        width: 240px;
        background: var(--sidebar-bg);
        color: #fff;
        display: flex;
        flex-direction: column;
        padding: 20px 12px;
        flex-shrink: 0;
        overflow-y: auto; 
    }

    .sidebar-label {
        font-size: 0.7rem;
        color: #636e72;
        font-weight: 800;
        text-transform: uppercase;
        padding: 0 15px;
        margin-bottom: 12px;
        margin-top: 15px;
        letter-spacing: 1px;
    }
    
    .sidebar-label:first-of-type { margin-top: 0; }

    .cat-link {
        color: #a4a9ad;
        text-decoration: none;
        padding: 12px 15px;
        border-radius: 8px;
        margin-bottom: 4px;
        font-size: 0.9rem;
        font-weight: 500;
        transition: 0.2s;
    }

    .cat-link:hover, .cat-link.active {
        background: rgba(255,255,255,0.1);
        color: #fff;
    }

    .product-area {
        flex: 1;
        background: var(--content-bg);
        padding: 20px;
        overflow-y: auto; 
    }

    .search-bar-container {
        position: sticky;
        top: 0;
        z-index: 10;
        background: var(--content-bg);
        padding: 10px 0 20px 0;
    }

    .search-bar-container input {
        width: 100%;
        padding: 14px;
        border-radius: 12px;
        border: 1px solid var(--border);
        box-shadow: var(--shadow);
        outline: none;
        font-size: 0.95rem;
    }

    .grid {
        display: grid;
        grid-template-columns: repeat(auto-fill, minmax(220px, 1fr));
        gap: 15px;
        padding-bottom: 40px; 
    }

    .product-card {
        background: #fff;
        border-radius: 14px;
        padding: 15px;
        border: 1px solid var(--border);
        transition: 0.3s ease;
        display: flex;
        flex-direction: column;
        justify-content: space-between;
        min-height: 180px;
    }

    .product-card:hover {
        transform: translateY(-3px);
        box-shadow: 0 10px 25px rgba(0,0,0,0.06);
    }

    .billing-terminal {
        width: 380px; 
        background: #fff;
        border-left: 1px solid var(--border);
        display: flex;
        flex-direction: column;
        flex-shrink: 0;
    }

    .bill-scroll {
        flex: 1;
        overflow-y: auto;
        padding: 15px;
        min-height: 200px;
    }

    .bill-item {
        background: var(--content-bg);
        border-radius: 8px;
        padding: 12px;
        margin-bottom: 8px;
        display: flex;
        justify-content: space-between;
        align-items: center;
    }

    .checkout-footer {
        padding: 20px; 
        border-top: 1px solid var(--border);
        background: #fff;
        margin-top: auto;
    }

    .btn-checkout {
        width: 100%;
        padding: 14px;
        background: var(--primary-green);
        color: white;
        border: none;
        border-radius: 10px;
        font-weight: 700;
        cursor: pointer;
        font-size: 1rem;
        transition: 0.2s;
    }

    .btn-checkout:hover { opacity: 0.9; }
    .btn-checkout:disabled { background: #dfe6e9; cursor: not-allowed; }

    .stock-tag {
        font-size: 0.7rem;
        padding: 4px 8px;
        border-radius: 6px;
        font-weight: 700;
    }
    .stock-low { background: #fff5f5; color: #e74c3c; }
    .stock-ok { background: #f0fff4; color: #27ae60; }

    .mobile-view-nav { display: none; }

    @media screen and (max-width: 1024px) {
        .main-wrapper { flex-direction: column; }
        .sidebar, .product-area, .billing-terminal { width: 100% !important; display: none; }
        body.show-cats .sidebar { display: flex; }
        body.show-products .product-area { display: block; }
        body.show-bill .billing-terminal { display: flex; }

        .mobile-view-nav {
            display: flex;
            position: fixed;
            bottom: 0;
            left: 0;
            width: 100%;
            background: #fff;
            border-top: 1px solid var(--border);
            height: 60px;
            z-index: 999;
        }
        .mobile-tab-btn {
            flex: 1;
            border: none;
            background: none;
            font-weight: 700;
            font-size: 0.8rem;
            color: #b2bec3;
            cursor: pointer;
            display: flex;
            flex-direction: column;
            justify-content: center;
            align-items: center;
            gap: 4px;
        }
        .mobile-tab-btn.active { color: var(--primary-green); }
        .grid { grid-template-columns: repeat(auto-fill, minmax(160px, 1fr)); }
    }
</style>
</head>
<body class="show-products"> 

<header class="df-header">
    <div>
        <h1 class="df-logo">
            Dufax <span style="color: #28a745;">Care</span> Dashboard
        </h1>
        <p class="df-subtitle">
            Premium Pharmaceutical Services
        </p>
    </div>
    <div style="display: flex; align-items: center; gap: 15px; flex-wrap: wrap;">
        <a href="/pharmacy_pj/view/sales_history.php" style="text-decoration: none; color: var(--text-main); font-weight: 600; font-size: 0.85rem;">HISTORY</a>
        <div style="background: #f1f2f6; padding: 6px 12px; border-radius: 20px; display: flex; align-items: center; gap: 6px;">
            <div style="width: 6px; height: 6px; background: var(--primary-green); border-radius: 50%;"></div>
            <span style="font-size: 0.75rem; font-weight: 700; color: var(--text-main);">
                <?php echo strtoupper(htmlspecialchars($display_name)); ?>
            </span>
        </div>
        <a href="../logout.php" style="color:#e74c3c; font-size: 0.85rem; text-decoration: none; font-weight: 800;">EXIT</a>
    </div>
</header>

<div class="main-wrapper">
    <aside class="sidebar">
        <div class="sidebar-label">Core Categories</div>
        <a href="/pharmacy_pj/view/staff_dashboard.php" class="cat-link <?php echo $cat_filter == '' ? 'active' : ''; ?>">
            📦 All Inventory
        </a>
        
        <a href="?category=Skincare" class="cat-link <?php echo $cat_filter == 'Skincare' ? 'active' : ''; ?>">
            🧴 Skincare
        </a>
        <a href="?category=Vitamin" class="cat-link <?php echo $cat_filter == 'Vitamin' ? 'active' : ''; ?>">
            💊 Vitamin
        </a>
        <a href="?category=Babycare" class="cat-link <?php echo $cat_filter == 'Babycare' ? 'active' : ''; ?>">
            🍼 Babycare
        </a>
        <a href="?category=First Aid" class="cat-link <?php echo $cat_filter == 'First Aid' ? 'active' : ''; ?>">
            🩹 First Aid
        </a>

        <?php if ($categories_res && $categories_res->num_rows > 0): ?>
            <div class="sidebar-label">Other Categories</div>
            <?php while($c = $categories_res->fetch_assoc()): ?>
                <a href="?category=<?php echo urlencode($c['category']); ?>" class="cat-link <?php echo $cat_filter == $c['category'] ? 'active' : ''; ?>">
                    📂 <?php echo htmlspecialchars($c['category']); ?>
                </a>
            <?php endwhile; ?>
        <?php endif; ?>
    </aside>

    <section class="product-area">
        <form class="search-bar-container" method="GET">
            <input type="text" name="search" placeholder="Search medication or scan barcode..." value="<?php echo htmlspecialchars($search); ?>" autofocus>
        </form>

        <?php if(isset($_GET['status'])): ?>
            <?php if($_GET['status'] === 'insufficient_stock'): ?>
                <div style="background:#fee2e2; color:#b91c1c; padding:12px; border-radius:10px; margin-bottom:15px; font-size: 0.85rem; font-weight: 600;">
                    ⚠️ Insufficient stock available for this quantity.
                </div>
            <?php elseif($_GET['status'] === 'product_not_found'): ?>
                <div style="background:#fef3c7; color:#92400e; padding:12px; border-radius:10px; margin-bottom:15px; font-size: 0.85rem; font-weight: 600;">
                    🔍 Product could not be found in inventory.
                </div>
            <?php endif; ?>
        <?php endif; ?>

        <?php if(isset($_GET['error'])): ?>
            <div style="background:#fee2e2; color:#b91c1c; padding:12px; border-radius:10px; margin-bottom:15px; font-size: 0.85rem; font-weight: 600;">
                ⚠️ <?php echo htmlspecialchars($_GET['error']); ?>
            </div>
        <?php endif; ?>

        <div class="grid">
            <?php while($row = $result->fetch_assoc()): ?>
                <div class="product-card">
                    <div>
                        <div style="display: flex; justify-content: space-between; align-items: flex-start; gap:4px;">
                            <span style="font-size: 0.65rem; color: #b2bec3; font-weight: 800; text-transform: uppercase; word-break: break-all;"><?php echo htmlspecialchars($row['category']); ?></span>
                            <span class="stock-tag <?php echo $row['quantity'] < 10 ? 'stock-low' : 'stock-ok'; ?>" style="white-space: nowrap;">
                                <?php echo $row['quantity']; ?> STK
                            </span>
                        </div>
                        <h3 style="font-size: 0.9rem; margin: 8px 0 4px 0; color: var(--text-main); font-weight: 700; line-height: 1.2;">
                            <?php echo htmlspecialchars($row['product_name']); ?>
                        </h3>
                        <div style="font-size: 1.1rem; font-weight: 800; color: var(--text-main); margin-bottom: 10px;">
                            <?php echo CURRENCY_SYMBOL . number_format($row['price'], 2); ?>
                        </div>
                    </div>

                    <form action="/pharmacy_pj/logic/pos_handler.php" method="POST">
                        <input type="hidden" name="product_id" value="<?php echo $row['id']; ?>">
                        <input type="hidden" name="action" value="add_to_bill">
                        <div style="display: flex; gap: 6px;">
                            <input type="number" name="qty" value="1" min="1" max="<?php echo $row['quantity']; ?>" style="width: 42px; padding: 6px; border-radius: 6px; border: 1px solid var(--border); font-weight: 700; text-align: center; font-size:0.85rem;">
                            <button type="submit" style="flex: 1; background: var(--text-main); color: #fff; border: none; border-radius: 6px; font-weight: 700; cursor: pointer; font-size: 0.75rem; padding: 6px;">+ ADD</button>
                        </div>
                    </form>
                </div>
            <?php endwhile; ?>
        </div>
    </section>

    <aside class="billing-terminal">
        <div style="padding: 15px 20px; border-bottom: 1px solid var(--border); display: flex; justify-content: space-between; align-items: center; flex-shrink: 0;">
            <h2 style="font-size: 1rem; margin: 0; font-weight: 800;">CURRENT BILL</h2>
            <span style="background: var(--content-bg); padding: 4px 10px; border-radius: 20px; font-size: 0.75rem; font-weight: 700;">
                <?php echo !empty($_SESSION['cart']) ? count($_SESSION['cart']) : 0; ?> ITEMS
            </span>
        </div>

        <div class="bill-scroll">
            <?php if(!empty($_SESSION['cart'])): 
                foreach($_SESSION['cart'] as $id => $item): 
                $sub = $item['price'] * $item['qty'];
            ?>
                <div class="bill-item">
                    <div style="flex: 1; padding-right: 10px;">
                        <div style="font-weight: 800; font-size: 0.8rem; color: var(--text-main);"><?php echo htmlspecialchars($item['name']); ?></div>
                        <div style="font-size: 0.75rem; color: #636e72; margin-top: 2px;">
                            <?php echo $item['qty']; ?> x <?php echo number_format($item['price'], 2); ?>
                        </div>
                    </div>
                    <div style="text-align: right; display: flex; flex-direction: column; justify-content: space-between; flex-shrink:0;">
                        <div style="font-weight: 800; color: var(--primary-green); font-size: 0.85rem;">₦<?php echo number_format($sub, 2); ?></div>
                        
                        <form action="/pharmacy_pj/logic/pos_handler.php" method="POST">
                            <input type="hidden" name="product_id" value="<?php echo $id; ?>">
                            <input type="hidden" name="action" value="remove_item">
                            <button type="submit" style="background: none; border: none; color: #e74c3c; font-size: 0.7rem; cursor: pointer; padding: 0; font-weight: 700; text-transform: uppercase;">Remove</button>
                        </form>
                    </div>
                </div>
            <?php endforeach; else: ?>
                <div style="text-align: center; color: #b2bec3; margin-top: 40px;">
                    <div style="font-size: 2rem; margin-bottom: 5px;">🛒</div>
                    <p style="font-weight: 600; font-size:0.85rem;">Bill is empty</p>
                </div>
            <?php endif; ?>
        </div>

        <div class="checkout-footer">
            <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 12px;">
                <span style="font-weight: 600; color: #636e72; font-size: 0.85rem;">Grand Total</span>
                <span style="font-weight: 900; font-size: 1.25rem; color: var(--text-main);">₦<?php echo number_format($total, 2); ?></span>
            </div>
            
            <form action="/pharmacy_pj/logic/process_checkout.php" method="POST">
                <div style="margin-bottom: 12px;">
                    <label for="payment_method" style="display:block; font-size:0.7rem; font-weight:700; color:#636e72; margin-bottom:4px; text-transform:uppercase;">Payment Method</label>
                    <select name="payment_method" id="payment_method" required style="width:100%; padding:10px; border-radius:6px; border:1px solid var(--border); font-weight:600; font-size:0.8rem; background:#fff; outline:none;">
                        <option value="cash">💵 Cash Payment</option>
                        <option value="pos">💳 POS Terminal Machine</option>
                        <option value="transfer">🏦 Direct Bank Transfer</option>
                        <option value="paystack">⚡ Online Paystack Link</option>
                    </select>
                </div>

                <button type="submit" class="btn-checkout" <?php echo empty($_SESSION['cart']) ? 'disabled' : ''; ?>>
                    COMPLETE SALE
                </button>
            </form>

            <?php if(!empty($_SESSION['cart'])): ?>
                <form action="/pharmacy_pj/logic/pos_handler.php" method="POST" style="margin-top:8px; text-align:center;">
                    <input type="hidden" name="action" value="clear_bill">
                    <button type="submit" style="background:none; border:none; color:#999; font-size:10px; cursor:pointer; font-weight: 600; letter-spacing: 0.5px;">VOID TRANSACTION</button>
                </form>
            <?php endif; ?>
        </div>
    </aside>
</div>

<nav class="mobile-view-nav">
    <button class="mobile-tab-btn" onclick="switchView('show-cats', this)">
        <span>📂</span>
        <span>Categories</span>
    </button>
    <button class="mobile-tab-btn active" onclick="switchView('show-products', this)">
        <span>💊</span>
        <span>Inventory</span>
    </button>
    <button class="mobile-tab-btn" onclick="switchView('show-bill', this)">
        <span>🛒</span>
        <span>Current Bill (<?php echo !empty($_SESSION['cart']) ? count($_SESSION['cart']) : 0; ?>)</span>
    </button>
</nav>

<script>
    function switchView(targetClass, element) {
        const body = document.body;
        body.classList.remove('show-cats', 'show-products', 'show-bill');
        body.classList.add(targetClass);
        
        document.querySelectorAll('.mobile-tab-btn').forEach(btn => {
            btn.classList.remove('active');
        });
        element.classList.add('active');
    }
</script>
</body>
</html>