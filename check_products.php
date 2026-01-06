<?php
require_once 'config.php';

echo "=== Product Database Check ===\n\n";

// Check total products
$result = mysqli_query($conn, 'SELECT COUNT(*) as count FROM products');
$row = mysqli_fetch_assoc($result);
echo "Total products in database: " . $row['count'] . "\n";

// Check active products
$result2 = mysqli_query($conn, 'SELECT COUNT(*) as count FROM products WHERE is_active = 1');
$row2 = mysqli_fetch_assoc($result2);
echo "Active products: " . $row2['count'] . "\n\n";

// Show sample products
$result3 = mysqli_query($conn, 'SELECT id, name, is_active, stock_quantity, category_id FROM products LIMIT 10');
echo "Sample products:\n";
echo "---------------------------------------------------\n";
while($p = mysqli_fetch_assoc($result3)) {
    echo "ID: " . $p['id'] . " | ";
    echo "Name: " . $p['name'] . " | ";
    echo "Active: " . ($p['is_active'] ? 'Yes' : 'No') . " | ";
    echo "Stock: " . $p['stock_quantity'] . " | ";
    echo "Category ID: " . $p['category_id'] . "\n";
}

echo "\n=== Query Test ===\n\n";
// Test the exact query from index.php
$products_query = "SELECT p.*, c.name as category_name, c.slug as category_slug 
                  FROM products p 
                  LEFT JOIN categories c ON p.category_id = c.id 
                  WHERE p.is_active = 1 
                  ORDER BY p.created_at DESC 
                  LIMIT 20";
$products_result = mysqli_query($conn, $products_query);

if ($products_result) {
    $count = mysqli_num_rows($products_result);
    echo "Query returned: " . $count . " products\n\n";
    
    if ($count > 0) {
        echo "Products found:\n";
        echo "---------------------------------------------------\n";
        while ($product = mysqli_fetch_assoc($products_result)) {
            echo "- " . $product['name'] . " (Rs " . $product['price'] . ") - ";
            echo "Category: " . ($product['category_name'] ?? 'N/A') . " - ";
            echo "Stock: " . $product['stock_quantity'] . "\n";
        }
    } else {
        echo "No products returned by query!\n";
    }
} else {
    echo "Query error: " . mysqli_error($conn) . "\n";
}
?>
