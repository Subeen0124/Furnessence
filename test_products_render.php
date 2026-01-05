<?php
require_once 'config.php';

echo "<!DOCTYPE html>";
echo "<html><head><title>Product Render Test</title></head><body>";
echo "<h1>Testing Product Rendering</h1>";

// Get products from database
$products_query = "SELECT p.*, c.name as category_name, c.slug as category_slug 
                  FROM products p 
                  LEFT JOIN categories c ON p.category_id = c.id 
                  WHERE p.is_active = 1 
                  ORDER BY p.created_at DESC 
                  LIMIT 20";
$products_result = mysqli_query($conn, $products_query);

echo "<h2>Query Result:</h2>";
if ($products_result) {
    echo "<p>Query successful! Rows: " . mysqli_num_rows($products_result) . "</p>";
    
    echo "<h2>Products HTML Output:</h2>";
    echo "<div style='border: 2px solid #333; padding: 10px; margin: 10px 0;'>";
    
    if (mysqli_num_rows($products_result) > 0) {
        while ($product = mysqli_fetch_assoc($products_result)) {
            $category_class = strtolower($product['category_slug'] ?? 'living-room');
            
            echo "<div style='background: #f0f0f0; padding: 10px; margin: 10px 0; border: 1px solid #ccc;'>";
            echo "<strong>Product ID:</strong> " . $product['id'] . "<br>";
            echo "<strong>Name:</strong> " . htmlspecialchars($product['name']) . "<br>";
            echo "<strong>Category:</strong> " . htmlspecialchars($product['category_name']) . "<br>";
            echo "<strong>Category Slug:</strong> " . htmlspecialchars($product['category_slug']) . "<br>";
            echo "<strong>Category Class:</strong> " . $category_class . "<br>";
            echo "<strong>data-filter Value:</strong> " . $category_class . "<br>";
            echo "<strong>Price:</strong> $" . number_format($product['price'], 2) . "<br>";
            echo "<strong>Stock:</strong> " . $product['stock_quantity'] . "<br>";
            echo "<strong>Active:</strong> " . ($product['is_active'] ? 'Yes' : 'No') . "<br>";
            echo "</div>";
        }
    } else {
        echo "<p style='color: red;'>No products found!</p>";
    }
    
    echo "</div>";
} else {
    echo "<p style='color: red;'>Query failed: " . mysqli_error($conn) . "</p>";
}

echo "<h2>Expected Filter Values:</h2>";
echo "<ul>";
echo "<li><strong>all</strong> - Shows all products</li>";
echo "<li><strong>living-room</strong> - Should show products with category_slug = 'living-room'</li>";
echo "<li><strong>bedroom</strong> - Should show products with category_slug = 'bedroom'</li>";
echo "<li><strong>office</strong> - Should show products with category_slug = 'office'</li>";
echo "<li><strong>dining</strong> - Should show products with category_slug = 'dining'</li>";
echo "</ul>";

mysqli_close($conn);
echo "</body></html>";
?>
