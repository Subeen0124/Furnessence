<?php
require_once 'config.php';

echo "<!DOCTYPE html><html><head>";
echo "<title>Product Image Checker</title>";
echo "<style>
body { font-family: Arial, sans-serif; padding: 20px; }
table { border-collapse: collapse; width: 100%; margin: 20px 0; }
th, td { border: 1px solid #ddd; padding: 12px; text-align: left; }
th { background-color: #4CAF50; color: white; }
tr:nth-child(even) { background-color: #f2f2f2; }
.preview { width: 150px; height: 150px; object-fit: cover; }
.update-form { background: #f9f9f9; padding: 20px; margin: 20px 0; border-radius: 5px; }
.success { color: green; font-weight: bold; }
.error { color: red; font-weight: bold; }
</style>";
echo "</head><body>";

echo "<h1>Product Image Checker & Updater</h1>";

// Check current product images
$query = "SELECT p.id, p.name, p.image, p.stock_quantity, c.name as category_name 
          FROM products p 
          LEFT JOIN categories c ON p.category_id = c.id 
          ORDER BY p.id";
$result = mysqli_query($conn, $query);

echo "<h2>Current Product Images:</h2>";
echo "<table>";
echo "<tr><th>ID</th><th>Product Name</th><th>Category</th><th>Current Image Path</th><th>File Exists?</th><th>Preview</th></tr>";

$products = [];
while ($row = mysqli_fetch_assoc($result)) {
    $products[] = $row;
    $file_path = "c:/xampp/htdocs/Furnessence/" . $row['image'];
    $exists = file_exists($file_path);
    $status_color = $exists ? 'green' : 'red';
    $status_text = $exists ? '✓ Yes' : '✗ No';
    
    echo "<tr>";
    echo "<td>" . $row['id'] . "</td>";
    echo "<td><strong>" . htmlspecialchars($row['name']) . "</strong></td>";
    echo "<td>" . htmlspecialchars($row['category_name']) . "</td>";
    echo "<td>" . htmlspecialchars($row['image']) . "</td>";
    echo "<td style='color: $status_color;'>$status_text</td>";
    echo "<td>";
    if ($exists) {
        echo "<img src='" . $row['image'] . "' class='preview' alt='" . htmlspecialchars($row['name']) . "'>";
    } else {
        echo "No preview";
    }
    echo "</td>";
    echo "</tr>";
}
echo "</table>";

// Show available images
echo "<h2>Available Product Images:</h2>";
$image_dir = "c:/xampp/htdocs/Furnessence/assests/images/products/";
$available_images = [];

if (is_dir($image_dir)) {
    $files = scandir($image_dir);
    echo "<div style='display: grid; grid-template-columns: repeat(4, 1fr); gap: 15px; margin: 20px 0;'>";
    
    foreach ($files as $file) {
        if (preg_match('/^product-\d+\.jpg$/', $file)) {
            $available_images[] = $file;
            $web_path = "assests/images/products/" . $file;
            echo "<div style='border: 1px solid #ddd; padding: 10px; text-align: center;'>";
            echo "<img src='$web_path' style='width: 100%; height: 150px; object-fit: cover;'>";
            echo "<p><strong>$file</strong></p>";
            echo "</div>";
        }
    }
    
    echo "</div>";
}

// Suggested mapping
echo "<h2>Suggested Image Mapping:</h2>";
echo "<p>Based on typical furniture e-commerce image organization:</p>";

$suggested_mapping = [
    1 => 'product-1.jpg',  // Modern Sofa
    2 => 'product-2.jpg',  // Coffee Table
    3 => 'product-3.jpg',  // Queen Bed Frame
    4 => 'product-4.jpg',  // Nightstand
    5 => 'product-5.jpg',  // Office Desk
    6 => 'product-6.jpg',  // Office Chair
    7 => 'product-7.jpg',  // Dining Table
    8 => 'product-8.jpg'   // Dining Chair Set
];

echo "<table>";
echo "<tr><th>Product ID</th><th>Product Name</th><th>Category</th><th>Suggested Image</th><th>Preview</th></tr>";

foreach ($products as $product) {
    $suggested_image = isset($suggested_mapping[$product['id']]) ? $suggested_mapping[$product['id']] : 'product-1.jpg';
    $suggested_path = "assests/images/products/" . $suggested_image;
    
    echo "<tr>";
    echo "<td>" . $product['id'] . "</td>";
    echo "<td><strong>" . htmlspecialchars($product['name']) . "</strong></td>";
    echo "<td>" . htmlspecialchars($product['category_name']) . "</td>";
    echo "<td>" . $suggested_image . "</td>";
    echo "<td><img src='$suggested_path' class='preview'></td>";
    echo "</tr>";
}

echo "</table>";

echo "<div class='update-form'>";
echo "<h3>Apply Suggested Mapping?</h3>";
echo "<p>This will update the database to use the suggested image assignments (product-1.jpg for product ID 1, product-2.jpg for product ID 2, etc.)</p>";
echo "<form method='post'>";
echo "<button type='submit' name='apply_mapping' style='background: #4CAF50; color: white; padding: 10px 20px; border: none; border-radius: 5px; cursor: pointer; font-size: 16px;'>Apply Mapping</button>";
echo "</form>";
echo "</div>";

// Handle form submission
if (isset($_POST['apply_mapping'])) {
    echo "<h3>Updating Database...</h3>";
    $success_count = 0;
    
    foreach ($suggested_mapping as $product_id => $image_file) {
        $image_path = "assests/images/products/" . $image_file;
        $update_query = "UPDATE products SET image = '$image_path' WHERE id = $product_id";
        
        if (mysqli_query($conn, $update_query)) {
            echo "<p class='success'>✓ Updated Product ID $product_id to $image_file</p>";
            $success_count++;
        } else {
            echo "<p class='error'>✗ Failed to update Product ID $product_id: " . mysqli_error($conn) . "</p>";
        }
    }
    
    echo "<p class='success'>Successfully updated $success_count products!</p>";
    echo "<p><a href='check_product_images.php'>Refresh Page</a> | <a href='index.php'>Go to Homepage</a></p>";
}

mysqli_close($conn);

echo "</body></html>";
?>
