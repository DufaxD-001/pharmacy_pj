<?php
// 1. SESSION & SECURITY
if (session_status() === PHP_SESSION_NONE) { session_start(); }

// Admin only check
if (!isset($_SESSION['user_role']) || $_SESSION['user_role'] !== 'admin') {
    header("Location: ../login.php"); 
    exit();
}

// 2. SMART PATH FOR DATABASE
if (file_exists('logic/db.php')) { require_once('logic/db.php'); } 
else { require_once('../logic/db.php'); }

// 3. FETCH PRODUCT DATA
$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
$message = "";

$res = $conn->query("SELECT * FROM products WHERE id = $id");
$product = $res->fetch_assoc();

if (!$product) {
    // FIXED: Redirect back to the main index controller
    header("Location: ../index.php?msg=notfound");
    exit();
}

// 4. UPDATE LOGIC (When "Save Changes" is clicked)
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $name = $conn->real_escape_string($_POST['product_name']);
    $price = (float)$_POST['price'];
    $qty = (int)$_POST['quantity'];
    $cat = $conn->real_escape_string($_POST['category']);

    // Update the database
    $sql = "UPDATE products SET 
            product_name='$name', 
            price='$price', 
            quantity='$qty', 
            category='$cat' 
            WHERE id=$id";
    
    if ($conn->query($sql)) {
        // FIXED: Redirect back to the main index controller with success status
        header("Location: ../index.php?status=updated");
        exit();
    } else {
        $message = "Update failed: " . $conn->error;
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Edit - <?php echo htmlspecialchars($product['product_name']); ?></title>
    <style>
        body { background: #f4f7f6; font-family: 'Segoe UI', sans-serif; padding: 40px; }
        .edit-card { max-width: 500px; margin: auto; background: white; padding: 35px; border-radius: 16px; box-shadow: 0 10px 30px rgba(0,0,0,0.08); border: 1px solid #f0f0f0; }
        label { display:block; margin-bottom: 8px; font-weight: 600; font-size: 14px; color: #444; }
        input { width: 100%; padding: 12px; border: 2px solid #eee; border-radius: 10px; font-size: 15px; outline: none; margin-bottom: 20px; box-sizing: border-box; }
        input:focus { border-color: #28a745; }
        .btn-save { background: #1a1a1a; color: white; padding: 14px; border: none; border-radius: 10px; cursor: pointer; font-weight: 600; font-size: 14px; width: 100%; transition: 0.2s; }
        .btn-save:hover { background: #28a745; }
        .btn-cancel { display: block; text-align: center; margin-top: 15px; color: #888; text-decoration: none; font-size: 14px; font-weight: 600; }
        .btn-cancel:hover { color: #d9534f; }
    </style>
</head>
<body>

    <div class="edit-card">
        <h2 style="margin-top:0; color: #1a1a1a; letter-spacing: -0.5px;">
            Edit <span style="color: #28a745;">Medication</span>
        </h2>
        
        <?php if($message): ?>
            <p style="color: #d9534f; background: #fdeaea; padding: 10px; border-radius: 8px; font-size: 14px;"><?php echo $message; ?></p>
        <?php endif; ?>

        <form method="POST">
            <label>Product Name</label>
            <input type="text" name="product_name" value="<?php echo htmlspecialchars($product['product_name']); ?>" required>

            <label>Category</label>
            <input type="text" name="category" value="<?php echo htmlspecialchars($product['category'] ?? ''); ?>" placeholder="e.g. Antibiotics" required>

            <label>Price (₦)</label>
            <input type="number" step="0.01" name="price" value="<?php echo $product['price']; ?>" required>

            <label>Stock Quantity</label>
            <input type="number" name="quantity" value="<?php echo $product['quantity']; ?>" required>

            <button type="submit" class="btn-save">Save Changes</button>
            
            <!-- FIXED: href points to the parent directory index.php -->
            <a href="../index.php" class="btn-cancel">✕ Cancel and Go Back</a>
        </form>
    </div>

</body>
</html>