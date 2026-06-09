<?php
if (session_status() === PHP_SESSION_NONE) { session_start(); }

// Smart Path for DB
if (file_exists('logic/db.php')) { require_once('logic/db.php'); } 
else { require_once('../logic/db.php'); }

$message = "";

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $name = $conn->real_escape_string($_POST['product_name']);
    $price = (float)$_POST['price'];
    $qty = (int)$_POST['quantity'];
    $cat = $conn->real_escape_string($_POST['category']); // Will strictly be one of your synchronized categories

    $sql = "INSERT INTO products (product_name, price, quantity, category) VALUES ('$name', '$price', '$qty', '$cat')";
    if ($conn->query($sql)) {
        header("Location: ../index.php?status=success");
        exit();
    } else {
        $message = "Error: " . $conn->error;
    }
}

// FIXED: Added 'Antibiotics' and 'Supplements' to perfectly mirror your admin dashboard array
$allowed_categories = ['Pain Relief', 'Antibiotics', 'Vitamins', 'Supplements', 'Baby Care', 'First Aid', 'Skincare'];
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Add Medication | Dufax Pharmacy</title>
    <style>
        :root { --primary: #2ecc71; --dark: #2c3e50; }
        body { font-family: 'Inter', sans-serif; background: #f4f7f6; padding: 40px; }
        .form-card { 
            background: white; padding: 40px; border-radius: 20px; 
            max-width: 500px; margin: auto; 
            box-shadow: 0 10px 30px rgba(0,0,0,0.08); 
        }
        h2 { margin-top: 0; color: var(--dark); letter-spacing: -1px; }
        label { display: block; margin-top: 15px; font-weight: 600; font-size: 14px; color: #555; }
        
        /* Styled select element to match your input text fields */
        input, select { 
            width: 100%; padding: 12px; margin-top: 5px; 
            border: 2px solid #eee; border-radius: 10px; 
            box-sizing: border-box; outline: none; transition: 0.3s;
            background: white; font-family: inherit; font-size: 14px;
        }
        input:focus, select:focus { border-color: var(--primary); }
        
        .btn-container { margin-top: 25px; }
        
        button { 
            background: var(--dark); color: white; border: none; 
            padding: 15px; width: 100%; border-radius: 12px; 
            cursor: pointer; font-weight: bold; font-size: 16px; 
            transition: 0.3s; 
        }
        button:hover { background: var(--primary); transform: translateY(-2px); }

        .btn-cancel { 
            display: block; text-align: center; margin-top: 15px; 
            color: #95a5a6; text-decoration: none; font-size: 14px; 
            font-weight: 600; transition: 0.2s;
        }
        .btn-cancel:hover { color: #e74c3c; }
    </style>
</head>
<body>
    <div class="form-card">
        <h2>➕ Add New <span style="color:var(--primary)">Medication</span></h2>
        
        <?php if($message): ?>
            <p style="color:#e74c3c; background:#fdeded; padding:10px; border-radius:8px; font-size:14px;">
                <?php echo $message; ?>
            </p>
        <?php endif; ?>
        
        <form method="POST">
            <label>Drug Name</label>
            <input type="text" name="product_name" required placeholder="e.g. Paracetamol 500mg">
            
            <label>Category</label>
            <select name="category" required>
                <option value="" disabled selected>-- Select Storefront Category --</option>
                <?php foreach($allowed_categories as $cat_name): ?>
                    <option value="<?php echo htmlspecialchars($cat_name); ?>">
                        <?php echo htmlspecialchars($cat_name); ?>
                    </option>
                <?php endforeach; ?>
            </select>

            <label>Price (₦)</label>
            <input type="number" step="0.01" name="price" required placeholder="0.00">

            <label>Initial Stock Quantity</label>
            <input type="number" name="quantity" required placeholder="0">

            <div class="btn-container">
                <button type="submit">Save to Inventory</button>
                <a href="../index.php" class="btn-cancel">✕ Cancel and Go Back</a>
            </div>
        </form>
    </div>
</body>
</html>