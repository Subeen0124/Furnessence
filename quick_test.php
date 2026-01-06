<!DOCTYPE html>
<html>
<head>
    <title>Quick Product Display Test</title>
    <style>
        body { font-family: Arial; padding: 20px; }
        .test-grid { display: grid; grid-template-columns: repeat(4, 1fr); gap: 20px; margin: 20px 0; }
        .test-card { border: 1px solid #ddd; padding: 15px; text-align: center; }
        .test-card img { width: 100%; height: 200px; object-fit: cover; }
        h1 { color: #333; }
        .success { color: green; font-weight: bold; }
        .error { color: red; font-weight: bold; }
    </style>
</head>
<body>

<h1>Quick Product Display Test</h1>

<?php
require_once 'config.php';

$query = "SELECT p.*, c.name as category_name, c.slug as category_slug 
          FROM products p 
          LEFT JOIN categories c ON p.category_id = c.id 
          WHERE p.is_active = 1 
          ORDER BY p.created_at DESC";
$result = mysqli_query($conn, $query);

if ($result && mysqli_num_rows($result) > 0) {
    echo "<p class='success'>✓ Found " . mysqli_num_rows($result) . " products</p>";
    echo "<div class='test-grid'>";
    
    while ($product = mysqli_fetch_assoc($result)) {
        $image = !empty($product['image']) ? $product['image'] : 'assests/images/products/product-1.jpg';
        echo "<div class='test-card'>";
        echo "<img src='$image' alt='" . htmlspecialchars($product['name']) . "'>";
        echo "<h3>" . htmlspecialchars($product['name']) . "</h3>";
        echo "<p>Rs " . number_format($product['price'], 2) . "</p>";
        echo "<p>Category: " . htmlspecialchars($product['category_name']) . "</p>";
        echo "<p>Stock: " . $product['stock_quantity'] . "</p>";
        echo "</div>";
    }
    
    echo "</div>";
} else {
    echo "<p class='error'>✗ No products found or query failed</p>";
}

mysqli_close($conn);
?>

<hr>
<h2>Troubleshooting Steps:</h2>
<ol>
    <li>If you see products above, the database is working ✓</li>
    <li>If products show here but not on homepage, it's a CSS/JavaScript issue</li>
    <li>Try opening homepage and pressing F12 → Console tab to check for errors</li>
    <li>Try viewing page source (Ctrl+U) on homepage to see if HTML is generated</li>
</ol>

<p><a href="index.php" style="background: #4CAF50; color: white; padding: 10px 20px; text-decoration: none; border-radius: 5px;">Go to Homepage</a></p>

</body>
</html>
