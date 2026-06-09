<?php
if (session_status() === PHP_SESSION_NONE) { session_start(); }

// 1. Security Check: Block unauthorized users
if (!isset($_SESSION['user_id'])) {
    header("Location: logic/registration.php");
    exit();
}

// 2. Load the Engine (Since file is in root, path is direct)
if (file_exists('logic/db.php')) { 
    require_once('logic/db.php'); 
} else { 
    die("Error: Connection file missing."); 
}

if (isset($_POST['import_submit'])) {
    // Check if file uploaded cleanly
    if (isset($_FILES['csv_file']) && $_FILES['csv_file']['error'] == 0) {
        
        $file_name = $_FILES['csv_file']['tmp_name'];
        
        if (($handle = fopen($file_name, "r")) !== FALSE) {
            
            // Skip the first row (the column header row)
            fgetcsv($handle, 1000, ",");
            
            $inserted = 0;
            $updated = 0;
            
            // Read through the file row by row
            while (($data = fgetcsv($handle, 1000, ",")) !== FALSE) {
                // $data[0] = Name, $data[1] = Category, $data[2] = Price, $data[3] = Quantity
                if (empty($data[0])) continue; // Skip empty rows
                
                $product_name = $conn->real_escape_string($data[0]);
                $category     = $conn->real_escape_string($data[1]);
                $price        = floatval($data[2]);
                $quantity     = intval($data[3]);
                
                // Duplicate check
                $check_query = "SELECT id FROM products WHERE product_name = '$product_name'";
                $check_result = $conn->query($check_query);
                
                if ($check_result && $check_result->num_rows > 0) {
                    // Update matching stock metrics
                    $update_query = "UPDATE products SET price = $price, quantity = quantity + $quantity, category = '$category' WHERE product_name = '$product_name'";
                    $conn->query($update_query);
                    $updated++;
                } else {
                    // Brand new stock registration
                    $insert_query = "INSERT INTO products (product_name, category, price, quantity) VALUES ('$product_name', '$category', $price, $quantity)";
                    $conn->query($insert_query);
                    $inserted++;
                }
            }
            
            fclose($handle);
            // Redirect smoothly back to the root routing layout index file
            header("Location: index.php?import_success=1&inserted=$inserted&updated=$updated");
            exit();
        } else {
            header("Location: index.php?import_error=Failed to open CSV file");
            exit();
        }
    } else {
        header("Location: index.php?import_error=Error uploading file");
        exit();
    }
} else {
    header("Location: index.php");
    exit();
}