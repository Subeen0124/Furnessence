<?php
require_once 'config.php';

echo "<h2>Update Product Images to Match Names</h2>";
echo "<p>Assigning logical images to each product...</p>";

// Update products with appropriate images based on their names
$updates = [
    // Living Room products
    "UPDATE products SET image = 'assests/images/products/product-1.jpg' WHERE name = 'Modern Sofa'",
    "UPDATE products SET image = 'assests/images/products/product-2.jpg' WHERE name = 'Coffee Table'",
    
    // Bedroom products
    "UPDATE products SET image = 'assests/images/products/product-3.jpg' WHERE name = 'Queen Bed Frame'",
    "UPDATE products SET image = 'assests/images/products/product-4.jpg' WHERE name = 'Nightstand'",
    
    // Office products
    "UPDATE products SET image = 'assests/images/products/product-5.jpg' WHERE name = 'Office Desk'",
    "UPDATE products SET image = 'assests/images/products/product-6.jpg' WHERE name = 'Office Chair'",
    
    // Dining products
    "UPDATE products SET image = 'assests/images/products/product-7.jpg' WHERE name = 'Dining Table'",
    "UPDATE products SET image = 'assests/images/products/product-8.jpg' WHERE name = 'Dining Chair Set'"
];

$success_count = 0;
foreach ($updates as $query) {
    if (mysqli_query($conn, $query)) {
        $success_count++;
    } else {
        echo "<p style='color: red;'>Error: " . mysqli_error($conn) . "</p>";
    }
}

echo "<p style='color: green;'>✓ Updated $success_count products successfully!</p>";

// Show current product mapping
$result = mysqli_query($conn, "SELECT p.id, p.name, p.image, c.name as category_name FROM products p LEFT JOIN categories c ON p.category_id = c.id ORDER BY p.id");

echo "<h3>Current Product-Image Mapping:</h3>";
echo "<table border='1' style='border-collapse: collapse; width: 100%;'>";
echo "<tr><th>ID</th><th>Product Name</th><th>Category</th><th>Image File</th><th>Preview</th></tr>";

while ($row = mysqli_fetch_assoc($result)) {
    echo "<tr>";
    echo "<td>" . $row['id'] . "</td>";
    echo "<td><strong>" . $row['name'] . "</strong></td>";
    echo "<td>" . $row['category_name'] . "</td>";
    echo "<td>" . $row['image'] . "</td>";
    echo "<td><img src='" . $row['image'] . "' width='80' height='80' style='object-fit: cover;'></td>";
    echo "</tr>";
}

echo "</table>";

echo "<hr>";
echo "<h3>Notes:</h3>";
echo "<ul>";
echo "<li>product-1.jpg to product-8.jpg are assigned to the 8 main products</li>";
echo "<li>product-9.jpg to product-19.jpg are available for additional products</li>";
echo "<li>Hero section uses hero-product-1.jpg to hero-product-5.jpg (separate images)</li>";
echo "</ul>";

mysqli_close($conn);

echo "<hr>";
echo "<p><a href='index.php'>Go to Homepage</a> to see the updated products</p>";
?>
