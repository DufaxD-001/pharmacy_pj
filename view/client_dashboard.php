<?php
/** * NOTE: This file is strictly included by index.php.
 * $conn, $user_id, SITE_NAME, and CURRENCY_SYMBOL are provided by index.php.
 */

// 1. SECURITY GATEKEEPER: Prevent direct browser access to this file
if (!isset($conn)) {
    header("Location: ../index.php"); 
    exit();
}

// 2. FIXED: Live Cart Counter Logic for Multidimensional Arrays
$cart_count = 0;
if (isset($_SESSION['cart']) && is_array($_SESSION['cart'])) {
    foreach ($_SESSION['cart'] as $p_id => $item) {
        $cart_count += isset($item['qty']) ? (int)$item['qty'] : 0;
    }
}

// 3. Logic: Prepare the Marketplace search query
$search = "";
if (isset($_GET['search'])) {
    $search = $conn->real_escape_string($_GET['search']);
    $query = "SELECT * FROM products WHERE (product_name LIKE '%$search%' OR category LIKE '%$search%') AND quantity > 0 ORDER BY quantity DESC, product_name ASC";
} else {
    $query = "SELECT * FROM products WHERE quantity > 0 ORDER BY quantity DESC, product_name ASC";
}
$result = $conn->query($query);
?>

<header class="df-header">
    <div>
        <h1 class="df-logo">
            Dufax <span style="color: #28a745;">Care</span> Dashboard
        </h1>
        <p class="df-subtitle">
            Premium Pharmaceutical Services
        </p>
    </div>
    
    <div class="df-header-actions">
        <a href="view/cart_view.php" class="df-btn-cart">
            <span style="font-size: 18px;">🛒</span> 
            <span>My Cart</span>
            <?php if($cart_count > 0): ?>
                <span class="df-cart-badge">
                    <?php echo $cart_count; ?>
                </span>
            <?php endif; ?>
        </a>
        
        <a href="view/transactions.php" class="df-btn-history">
            <span style="font-size: 18px;">📜</span> 
            <span>Order History</span>
        </a>
    </div>
</header>

<?php if (isset($_GET['status']) && $_GET['status'] == 'added'): ?>
    <div class="df-alert df-alert-success">
        <div class="df-alert-content">
            <span style="font-size: 20px;">✅</span>
            <span>
                <strong>Successfully added!</strong> 
                You now have <?php echo isset($_GET['count']) ? intval($_GET['count']) : $cart_count; ?> product(s) in your cart.
            </span>
        </div>
        <a href="view/cart_view.php" class="df-alert-btn">
            Go to Cart →
        </a>
    </div>
<?php endif; ?>

<?php if (isset($_GET['error'])): ?>
    <div class="df-alert df-alert-error">
        <span style="font-size: 20px;">⚠️</span>
        <span><strong>Order Error:</strong> <?php echo htmlspecialchars($_GET['error']); ?></span>
    </div>
<?php endif; ?>

<section class="df-hero">
    <div class="df-hero-text">
        <h2>Your Health, Our Priority</h2>
        <p>
            Access authentic medications, wellness products, and professional pharmaceutical advice 24/7.
        </p>
        <div class="df-hero-badges">
            <div>✓ 100% Authentic</div>
            <div>✓ Fast Delivery</div>
        </div>
    </div>
    <div class="df-hero-img-container">
        <img src="https://images.unsplash.com/photo-1587854692152-cbe660feec90?auto=format&fit=crop&q=80&w=400" 
             alt="Pharmacy Support">
    </div>
</section>

<div style="margin-bottom: 30px;">
    <h3 style="color: #444; margin-bottom: 15px; font-size: 18px;">Shop by Category</h3>
    <div class="df-category-grid">
        <?php 
        $cats = ['Pain Relief' => '💊', 'Vitamins' => '🌿', 'Baby Care' => '🍼', 'First Aid' => '🩹', 'Skincare' => '🧴'];
        foreach($cats as $name => $icon): ?>
            <a href="view/category_view.php?cat=<?php echo urlencode($name); ?>" class="df-category-card">
                <div style="font-size: 30px; margin-bottom: 10px;"><?php echo $icon; ?></div>
                <div style="color: #333; font-weight: 600; font-size: 14px;"><?php echo $name; ?></div>
            </a>
        <?php endforeach; ?>
    </div>
</div>

<section class="df-search-container">
    <form action="index.php" method="GET" class="df-search-form">
        <input type="text" name="search" placeholder="Search medications, vitamins, or brands..." value="<?php echo htmlspecialchars($search); ?>">
        <button type="submit">Search</button>
    </form>
</section>

<section>
    <div class="df-section-title">
        <h3 style="color: #444; margin: 0;">Featured Medications</h3>
        <a href="view/all_products.php">View All Items →</a>
    </div>
    <div class="df-products-grid">
        <?php 
        while($row = $result->fetch_assoc()): 
            $max_stock = $row['quantity'];
        ?>
            <div class="df-product-card">
                <div>
                    <span class="df-stock-badge">Available</span>
                    <strong class="df-product-name"><?php echo htmlspecialchars($row['product_name']); ?></strong>
                    <span class="df-product-price"><?php echo CURRENCY_SYMBOL . number_format($row['price'], 2); ?></span>
                </div>

                <form action="logic/process_cart.php" method="POST" style="margin-top: 15px;">
                    <input type="hidden" name="product_id" value="<?php echo $row['id']; ?>">
                    <input type="hidden" name="action" value="add">
                    
                    <div class="df-qty-container">
                        <span>Qty:</span>
                        <input type="number" name="quantity" value="1" min="1" max="<?php echo $max_stock; ?>">
                    </div>

                    <button type="submit" class="df-btn-add">
                        Add to Cart
                    </button>
                </form>
            </div>
        <?php endwhile; ?>
    </div>
</section>

<section class="df-warning-notice">
    <span style="font-size: 35px;">⚠️</span>
    <div>
        <h4>Prescription Safety & Usage Notice</h4>
        <p>Consult a physician before starting any new medication. Check seal and expiry dates.</p>
    </div>
</section>

<footer class="df-footer">
    <div class="df-footer-content">
        <div>
            <h3 style="color: #28a745; margin-bottom: 20px; font-size: 20px;">Dufax <span style="color: #fff;">Care</span></h3>
            <p style="color: #aaa; font-size: 14px; line-height: 1.6;">Your trusted digital pharmacy partner in Ilorin.</p>
        </div>
    </div>
    <div class="df-footer-bottom">
        <p>&copy; <?php echo date('Y'); ?> <strong>DufaxPlus Web Solutions</strong>.</p>
    </div>
</footer>

<style>
    /* Global layout optimization */
    body {
        margin: 0;
        padding: 10px; /* Slight buffer for tight screens */
    }

    /* Header Breakdowns */
    .df-header {
        margin-bottom: 30px; 
        display: flex; 
        flex-direction: row;
        justify-content: space-between; 
        align-items: center; 
        background: #fff; 
        padding: 20px 30px; 
        border-radius: 16px; 
        box-shadow: 0 4px 20px rgba(0,0,0,0.08); 
        border: 1px solid #f0f0f0;
    }
    .df-logo { margin: 0; font-size: 22px; color: #1a1a1a; letter-spacing: -0.5px; }
    .df-subtitle { margin: 3px 0 0 0; color: #888; font-size: 13px; font-weight: 500; }
    .df-header-actions { display: flex; gap: 12px; align-items: center; }
    
    .df-btn-cart {
        text-decoration: none; color: #444; font-weight: 600; font-size: 14px; padding: 10px 18px; 
        background: #f8f9fa; border-radius: 10px; border: 1.5px solid #eee; display: flex; align-items: center; gap: 10px; position: relative;
    }
    .df-cart-badge {
        background: #28a745; color: white; font-size: 11px; padding: 2px 8px; border-radius: 20px; margin-left: 5px; box-shadow: 0 2px 5px rgba(40,167,69,0.3);
    }
    .df-btn-history {
        text-decoration: none; background: #1a1a1a; color: white; padding: 10px 22px; border-radius: 10px; 
        font-weight: 600; font-size: 14px; display: flex; align-items: center; gap: 10px; box-shadow: 0 4px 12px rgba(0,0,0,0.15);
    }

    /* Alerts */
    .df-alert {
        padding: 16px 24px; border-radius: 12px; margin-bottom: 25px; display: flex; justify-content: space-between; align-items: center; animation: slideIn 0.5s ease-out;
    }
    .df-alert-success { background: #e7f3eb; color: #1e7e34; border: 1px solid #c3e6cb; }
    .df-alert-error { background: #fdf2f2; color: #b91c1c; border: 1px solid #fecaca; justify-content: flex-start; gap: 12px; }
    .df-alert-content { display: flex; align-items: center; gap: 12px; }
    .df-alert-btn { background: #28a745; color: white; text-decoration: none; padding: 8px 16px; border-radius: 8px; font-size: 13px; font-weight: 700; white-space: nowrap; }

    /* Hero Unit */
    .df-hero {
        display: flex; gap: 30px; align-items: center; background: linear-gradient(135deg, #28a745 0%, #1e7e34 100%); 
        padding: 40px; border-radius: 20px; color: white; margin-bottom: 30px; overflow: hidden;
    }
    .df-hero-text { flex: 1.5; z-index: 2; }
    .df-hero-text h2 { font-size: 28px; margin-top: 0; margin-bottom: 10px; font-weight: 700; }
    .df-hero-text p { font-size: 16px; opacity: 0.9; line-height: 1.6; margin-bottom: 20px; }
    .df-hero-badges { display: flex; gap: 15px; }
    .df-hero-badges div { background: rgba(255,255,255,0.2); padding: 10px 15px; border-radius: 8px; font-size: 13px; font-weight: 600; }
    .df-hero-img-container { flex: 1; display: flex; justify-content: center; }
    .df-hero-img-container img { width: 100%; max-width: 300px; height: auto; border-radius: 15px; box-shadow: 0 10px 30px rgba(0,0,0,0.2); }

    /* Category Layout */
    .df-category-grid { display: grid; grid-template-columns: repeat(auto-fit, minmax(130px, 1fr)); gap: 15px; }
    .df-category-card {
        text-decoration: none; background: #fff; padding: 20px; border-radius: 12px; text-align: center; border: 1px solid #eee; 
        box-shadow: 0 2px 4px rgba(0,0,0,0.02); display: block; transition: transform 0.2s;
    }
    .df-category-card:hover { transform: translateY(-5px); }

    /* Search engine UI */
    .df-search-container { background: #fff; padding: 20px; border-radius: 12px; box-shadow: 0 4px 6px rgba(0,0,0,0.05); margin-bottom: 30px; }
    .df-search-form { display: flex; gap: 10px; align-items: center; }
    .df-search-form input { flex: 1; padding: 12px; border: 2px solid #eee; border-radius: 8px; font-size: 15px; outline: none; }
    .df-search-form button { padding: 12px 25px; background: #333; color: white; border: none; border-radius: 8px; cursor: pointer; font-weight: bold; }

    /* Featured Products Engine */
    .df-section-title { display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px; }
    .df-section-title a { color: #28a745; text-decoration: none; font-weight: 600; font-size: 14px; }
    .df-products-grid { display: grid; grid-template-columns: repeat(auto-fit, minmax(220px, 1fr)); gap: 20px; }
    
    .df-product-card {
        background: #fff; padding: 20px; border-radius: 15px; border: 1px solid #eee; text-align: center; 
        display: flex; flex-direction: column; justify-content: space-between; transition: 0.3s;
    }
    .df-product-card:hover { box-shadow: 0 8px 15px rgba(0,0,0,0.05); }
    .df-stock-badge { font-size: 10px; color: #28a745; font-weight: 700; text-transform: uppercase; background: #e7f3eb; padding: 3px 8px; border-radius: 10px; }
    .df-product-name { display: block; margin: 10px 0 5px 0; font-size: 16px; color: #1a1a1a; }
    .df-product-price { color: #28a745; font-weight: 800; font-size: 18px; }
    .df-qty-container { display: flex; align-items: center; justify-content: center; gap: 8px; margin-bottom: 10px; background: #f8f9fa; padding: 5px; border-radius: 8px; }
    .df-qty-container span { font-size: 11px; font-weight: 600; color: #666; }
    .df-qty-container input { width: 50px; border: 1px solid #ddd; border-radius: 4px; text-align: center; font-size: 13px; font-weight: bold; }
    .df-btn-add { width: 100%; background: #1a1a1a; color: white; border: none; padding: 10px; border-radius: 8px; cursor: pointer; font-size: 13px; font-weight: 700; }

    /* Warnings */
    .df-warning-notice { background: #fff9e6; border-left: 5px solid #ffcc00; padding: 25px; border-radius: 12px; margin-top: 40px; display: flex; align-items: center; gap: 20px; }
    .df-warning-notice h4 { margin: 0; color: #856404; font-size: 16px; font-weight: 700; }
    .df-warning-notice p { margin: 5px 0 0 0; font-size: 13px; color: #856404; line-height: 1.6; }

    /* Footer structure */
    .df-footer { background: #1a1a1a; color: #fff; padding: 60px 30px 30px 30px; border-radius: 20px 20px 0 0; margin-top: 60px; }
    .df-footer-content { max-width: 1200px; margin: 0 auto; display: grid; grid-template-columns: repeat(auto-fit, minmax(250px, 1fr)); gap: 40px; }
    .df-footer-bottom { border-top: 1px solid #333; margin-top: 50px; padding-top: 30px; text-align: center; color: #666; font-size: 13px; }

    @keyframes slideIn { from { opacity: 0; transform: translateY(-10px); } to { opacity: 1; transform: translateY(0); } }

    /* ==========================================================================
       MOBILE RESPONSIVE MEDIA QUERIES (Handheld devices & small displays)
       ========================================================================== */
    @media screen and (max-width: 680px) {
        /* Header stacks elements vertically */
        .df-header {
            flex-direction: column;
            gap: 15px;
            text-align: center;
            padding: 20px;
        }
        .df-header-actions {
            width: 100%;
            justify-content: center;
        }
        .df-btn-cart, .df-btn-history {
            flex: 1;
            justify-content: center;
            padding: 10px 12px;
            font-size: 13px;
        }

        /* Success alerts adjust cleanly */
        .df-alert {
            flex-direction: column;
            gap: 15px;
            text-align: center;
        }
        .df-alert-content {
            flex-direction: column;
        }
        .df-alert-btn {
            width: 100%;
            text-align: center;
        }

        /* Hero component adjusts layout flow */
        .df-hero {
            flex-direction: column-reverse;
            padding: 25px 20px;
            text-align: center;
        }
        .df-hero-text h2 { font-size: 22px; }
        .df-hero-text p { font-size: 14px; }
        .df-hero-badges { justify-content: center; }
        .df-hero-img-container img { max-width: 180px; }

        /* Categorization scaling */
        .df-category-grid {
            grid-template-columns: repeat(auto-fit, minmax(100px, 1fr));
            gap: 10px;
        }
        .df-category-card { padding: 15px 10px; }
        .df-category-card div { font-size: 12px !important; }

        /* Search input optimization */
        .df-search-form {
            flex-direction: column;
        }
        .df-search-form input, .df-search-form button {
            width: 100%;
            box-sizing: border-box;
        }

        /* Product matrix goes full width if screens get tiny */
        .df-products-grid {
            grid-template-columns: repeat(auto-fit, minmax(160px, 1fr));
            gap: 15px;
        }
        
        /* Warning element adjustments */
        .df-warning-notice {
            flex-direction: column;
            text-align: center;
            gap: 10px;
        }
    }
</style>