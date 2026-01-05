<?php
require_once 'config.php';

echo "<h2>Debug: Categories in Database</h2>";

$cat_query = "SELECT * FROM categories";
$cat_result = mysqli_query($conn, $cat_query);

echo "<table border='1'>";
echo "<tr><th>ID</th><th>Name</th><th>Slug</th></tr>";
while ($cat = mysqli_fetch_assoc($cat_result)) {
    echo "<tr>";
    echo "<td>" . $cat['id'] . "</td>";
    echo "<td>" . $cat['name'] . "</td>";
    echo "<td>" . $cat['slug'] . "</td>";
    echo "</tr>";
}
echo "</table>";

echo "<h2>Debug: Products with Categories</h2>";

$prod_query = "SELECT p.id, p.name, p.stock_quantity, p.is_active, c.name as category_name, c.slug as category_slug 
               FROM products p 
               LEFT JOIN categories c ON p.category_id = c.id 
               WHERE p.is_active = 1";
$prod_result = mysqli_query($conn, $prod_query);

echo "<table border='1'>";
echo "<tr><th>ID</th><th>Product Name</th><th>Stock</th><th>Active</th><th>Category Name</th><th>Category Slug</th></tr>";
while ($prod = mysqli_fetch_assoc($prod_result)) {
    echo "<tr>";
    echo "<td>" . $prod['id'] . "</td>";
    echo "<td>" . $prod['name'] . "</td>";
    echo "<td>" . $prod['stock_quantity'] . "</td>";
    echo "<td>" . $prod['is_active'] . "</td>";
    echo "<td>" . ($prod['category_name'] ?? 'NULL') . "</td>";
    echo "<td>" . ($prod['category_slug'] ?? 'NULL') . "</td>";
    echo "</tr>";
}
echo "</table>";

echo "<h2>Expected Filter Button Values:</h2>";
echo "<pre>";
echo "all - Show all products\n";
echo "living-room - Living Room products\n";
echo "bedroom - Bedroom products\n";
echo "office - Office products\n";
echo "dining - Dining products\n";
echo "</pre>";

mysqli_close($conn);
?>
