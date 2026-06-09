<?php
session_start();
require_once('../logic/db.php');

// Security Gatekeeper: Ensure user is logged in before accessing the cart
if (!isset($_SESSION['client_id']) && !isset($_SESSION['staff_id']) && !isset($_SESSION['user_id'])) {
    header("Location: ../login.php?error=Please+login+to+view+your+cart");
    exit();
}

if(!defined('SITE_NAME')) define('SITE_NAME', 'Dufax Pharmacy');
if(!defined('CURRENCY_SYMBOL')) define('CURRENCY_SYMBOL', '₦');

$total = 0;
$cart_items = [];

if (!empty($_SESSION['cart'])) {
    // Sanitize keys to ensure they are integers
    $cart_keys = array_map('intval', array_keys($_SESSION['cart']));
    
    if (!empty($cart_keys)) {
        // Create placeholders for the prepared statement (?,?,?)
        $placeholders = implode(',', array_fill(0, count($cart_keys), '?'));
        $types = str_repeat('i', count($cart_keys));
        
        $stmt = $conn->prepare("SELECT * FROM products WHERE id IN ($placeholders)");
        $stmt->bind_param($types, ...$cart_keys);
        $stmt->execute();
        $results = $stmt->get_result();

        if ($results) {
            while ($row = $results->fetch_assoc()) {
                $pid = $row['id'];
                if (isset($_SESSION['cart'][$pid])) {
                    $row['qty_in_cart'] = (int)$_SESSION['cart'][$pid];
                    $row['subtotal'] = $row['price'] * $row['qty_in_cart'];
                    $total += $row['subtotal'];
                    $cart_items[] = $row;
                }
            }
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Your Cart - <?php echo SITE_NAME; ?></title>
    <style>
        body { padding: 40px; background: #f4f7f6; font-family: 'Segoe UI', system-ui, sans-serif; color: #333; }
        .cart-container { max-width: 1100px; margin: 0 auto; display: grid; grid-template-columns: 1fr 380px; gap: 30px; }
        .cart-main { background: white; padding: 30px; border-radius: 16px; box-shadow: 0 4px 15px rgba(0,0,0,0.05); }
        table { width: 100%; border-collapse: collapse; margin-top: 20px; }
        th { text-align: left; background: #f8f9fa; color: #555; padding: 15px; border-bottom: 2px solid #eee; }
        td { padding: 15px; border-bottom: 1px solid #eee; }
        
        textarea#address_field {
            width: 100%;
            padding: 12px;
            border-radius: 10px;
            border: 1.5px solid #eee;
            background: #fdfdfd;
            font-family: inherit;
            resize: vertical;
            min-height: 80px;
            box-sizing: border-box;
        }
        
        @media (max-width: 900px) { 
            .cart-container { grid-template-columns: 1fr; }
            body { padding: 15px; }
        }
    </style>
</head>
<body>

<div class="cart-container">
    <div class="cart-main">
        <h2 style="margin-top:0;">🛒 Your Shopping Cart</h2>
        
        <?php if (empty($cart_items)): ?>
            <div style="text-align: center; padding: 40px;">
                <p style="color: #666; font-size: 18px;">Your cart is currently empty.</p>
                <a href="../index.php" style="color: #28a745; text-decoration: none; font-weight: bold;">← Back to Medications</a>
            </div>
        <?php else: ?>
            <table>
                <thead>
                    <tr>
                        <th>Product</th>
                        <th>Price</th>
                        <th>Qty</th>
                        <th>Subtotal</th>
                        <th>Action</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($cart_items as $item): ?>
                    <tr>
                        <td style="font-weight: 600;"><?php echo htmlspecialchars($item['product_name']); ?></td>
                        <td><?php echo CURRENCY_SYMBOL . number_format($item['price'], 2); ?></td>
                        <td><?php echo $item['qty_in_cart']; ?></td>
                        <td style="color: #28a745; font-weight: bold;"><?php echo CURRENCY_SYMBOL . number_format($item['subtotal'], 2); ?></td>
                        <td>
                            <form action="../logic/process_cart.php" method="POST" style="margin:0;">
                                <input type="hidden" name="product_id" value="<?php echo $item['id']; ?>">
                                <input type="hidden" name="action" value="remove">
                                <button type="submit" style="background: #fff0f0; color: #ff4d4d; border: 1px solid #ffcccc; padding: 5px 10px; border-radius: 6px; cursor: pointer; font-size: 12px; font-weight: 600;">Remove</button>
                            </form>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>

            <div style="margin-top: 30px; display: flex; flex-direction: column; gap: 15px; border-top: 1px solid #eee; padding-top: 20px;">
                <a href="../index.php" style="text-decoration: none; color: #28a745; font-weight: 600; font-size: 14px;">← Continue Shopping</a>
                <form action="../logic/process_cart.php" method="POST" onsubmit="return confirm('Are you sure you want to cancel this entire order?');" style="margin:0;">
                    <input type="hidden" name="action" value="empty">
                    <button type="submit" style="background: #fdeaea; color: #d9534f; border: 1px solid #d9534f; padding: 10px 20px; border-radius: 8px; font-weight: 700; cursor: pointer; font-size: 13px;">🗑️ Cancel Entire Order</button>
                </form>
            </div>
        <?php endif; ?>
    </div>

    <?php if (!empty($cart_items)): ?>
    <div style="height: fit-content;">
        <form action="../logic/process_checkout.php" method="POST" id="checkout-form" style="background: #fff; padding: 25px; border-radius: 16px; border: 1px solid #eee; box-shadow: 0 4px 12px rgba(0,0,0,0.03);">
            <h3 style="margin-top: 0; font-size: 18px; color: #1a1a1a;">Finalize Order</h3>

            <div style="background: #f8f9fa; padding: 15px; border-radius: 10px; margin-bottom: 20px;">
                <div style="display: flex; justify-content: space-between; margin-bottom: 8px; font-size: 14px;">
                    <span style="color: #666;">Items Subtotal</span>
                    <span style="font-weight: 600;"><?php echo CURRENCY_SYMBOL . number_format($total, 2); ?></span>
                </div>
                
                <div style="display: flex; justify-content: space-between; margin-bottom: 8px; font-size: 14px;">
                    <span style="color: #666;">Delivery Fee</span>
                    <span style="font-weight: 600; color: #28a745;">+ <?php echo CURRENCY_SYMBOL; ?><span id="display-delivery">0.00</span></span>
                </div>
                
                <hr style="border: 0; border-top: 1px solid #ddd; margin: 12px 0;">
                
                <div style="display: flex; justify-content: space-between; font-size: 18px; font-weight: 800;">
                    <span>Total</span>
                    <span style="color: #1a1a1a;"><?php echo CURRENCY_SYMBOL; ?><span id="display-total"><?php echo number_format($total, 2); ?></span></span>
                </div>
            </div>

            <div style="margin-bottom: 20px;">
                <label style="display: block; margin-bottom: 8px; font-size: 13px; font-weight: bold; color: #444;">Delivery Choice:</label>
                <select name="delivery_type" id="delivery_type" required onchange="updateCartTotal()" 
                        style="width: 100%; padding: 12px; border-radius: 10px; border: 1.5px solid #eee; background: #fff; font-size: 14px; cursor: pointer;">
                    <option value="pickup" data-fee="0">Pharmacy Pickup (Free)</option>
                    <option value="delivery" data-fee="500">Home Delivery (+₦500.00)</option>
                </select>
            </div>

            <div id="address_container" style="display: none; margin-bottom: 20px;">
                <label style="display: block; margin-bottom: 8px; font-size: 13px; font-weight: bold; color: #444;">Delivery Address:</label>
                <textarea name="address" id="address_field" placeholder="House number, Street name, City..."></textarea>
            </div>

            <input type="hidden" name="subtotal" value="<?php echo $total; ?>">
            <input type="hidden" name="final_total" id="hidden-final-total" value="<?php echo $total; ?>">

            <button type="submit" name="place_order" style="width: 100%; background: #1a1a1a; color: white; border: none; padding: 16px; border-radius: 12px; font-weight: 700; font-size: 15px; cursor: pointer; transition: 0.3s;"
                    onmouseover="this.style.background='#28a745'" onmouseout="this.style.background='#1a1a1a'">
                Confirm Order & Pay
            </button>
        </form>
    </div>
    <?php endif; ?>
</div>

<script>
function updateCartTotal() {
    const subtotal = <?php echo floatval($total); ?>;
    const deliverySelect = document.getElementById('delivery_type');
    const addressContainer = document.getElementById('address_container');
    const addressField = document.getElementById('address_field');
    
    if (!deliverySelect) return;

    const selectedOption = deliverySelect.options[deliverySelect.selectedIndex];
    const deliveryFee = parseInt(selectedOption.getAttribute('data-fee')) || 0;

    document.getElementById('display-delivery').innerText = deliveryFee.toFixed(2);
    
    const finalTotal = subtotal + deliveryFee;
    document.getElementById('display-total').innerText = finalTotal.toLocaleString(undefined, {minimumFractionDigits: 2});
    document.getElementById('hidden-final-total').value = finalTotal;

    if (deliveryFee > 0) {
        addressContainer.style.display = "block";
        addressField.required = true;
    } else {
        addressContainer.style.display = "none";
        addressField.required = false;
        addressField.value = ""; 
    }
}

document.addEventListener('DOMContentLoaded', updateCartTotal);
</script>

</body>
</html>