<?php
require_once 'config.php';

echo "<h2>Fixing Product Image Paths</h2>";

// Update all product image paths to use the correct directory
$update_query = "UPDATE products SET 
    image = REPLACE(image, 'assests/images/product-', 'assests/images/products/product-')
    WHERE image LIKE 'assests/images/product-%'";

if (mysqli_query($conn, $update_query)) {
    echo "<p style='color: green;'>✓ Successfully updated image paths!</p>";
    
    // Show updated products
    $check_query = "SELECT id, name, image FROM products";
    $result = mysqli_query($conn, $check_query);
    
    echo "<h3>Updated Products:</h3>";
    echo "<table border='1' style='border-collapse: collapse;'>";
    echo "<tr><th>ID</th><th>Name</th><th>Image Path</th><th>File Exists?</th></tr>";
    
    while ($row = mysqli_fetch_assoc($result)) {
        $file_path = "c:/xampp/htdocs/Furnessence/" . $row['image'];
        $exists = file_exists($file_path) ? "✓ Yes" : "✗ No";
        $color = file_exists($file_path) ? "green" : "red";
        
        echo "<tr>";
        echo "<td>" . $row['id'] . "</td>";
        echo "<td>" . $row['name'] . "</td>";
        echo "<td>" . $row['image'] . "</td>";
        echo "<td style='color: $color;'>" . $exists . "</td>";
        echo "</tr>";
    }
    
    echo "</table>";
    
} else {
    echo "<p style='color: red;'>✗ Error updating: " . mysqli_error($conn) . "</p>";
}

mysqli_close($conn);

echo "<hr>";
echo "<p><a href='index.php'>Go to Homepage</a> to see the changes</p>";
?>
