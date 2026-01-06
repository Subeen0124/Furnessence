<?php
require_once 'config.php';
require_once 'includes/algorithms/ProductSearch.php';

echo "<h1>Search Algorithm Debug</h1>";

// Check database connection
echo "<h2>1. Database Connection</h2>";
if ($conn) {
    echo "✓ Connected to database<br>";
} else {
    echo "✗ Database connection failed<br>";
    exit;
}

// Check if products exist
echo "<h2>2. Products in Database</h2>";
$check_query = "SELECT COUNT(*) as total, 
                SUM(CASE WHEN is_active = 1 THEN 1 ELSE 0 END) as active 
                FROM products";
$result = mysqli_query($conn, $check_query);
if ($result) {
    $counts = mysqli_fetch_assoc($result);
    echo "Total products: " . $counts['total'] . "<br>";
    echo "Active products: " . $counts['active'] . "<br>";
    
    if ($counts['active'] == 0) {
        echo "<strong style='color: red;'>⚠️ No active products found! This is why search returns no results.</strong><br>";
    }
} else {
    echo "Error checking products: " . mysqli_error($conn) . "<br>";
}

// Test the algorithm
echo "<h2>3. Test Search Algorithm</h2>";
$test_query = "sofa";
echo "Searching for: '<strong>$test_query</strong>'<br><br>";

$searchEngine = new ProductSearch($conn, $test_query);
$products = $searchEngine->search();

echo "Results found: <strong>" . count($products) . "</strong><br><br>";

if (count($products) > 0) {
    echo "<h3>Products:</h3>";
    echo "<table border='1' cellpadding='5'>";
    echo "<tr><th>ID</th><th>Name</th><th>Category</th><th>Price</th><th>Active</th></tr>";
    foreach ($products as $product) {
        echo "<tr>";
        echo "<td>" . $product['id'] . "</td>";
        echo "<td>" . htmlspecialchars($product['name']) . "</td>";
        echo "<td>" . htmlspecialchars($product['category_name']) . "</td>";
        echo "<td>Rs " . number_format($product['price'], 2) . "</td>";
        echo "<td>" . ($product['is_active'] ? 'Yes' : 'No') . "</td>";
        echo "</tr>";
    }
    echo "</table>";
} else {
    echo "<p style='color: red;'>No products found. Check:</p>";
    echo "<ul>";
    echo "<li>Do you have products in the database?</li>";
    echo "<li>Are they marked as active (is_active = 1)?</li>";
    echo "<li>Does the product name/description match your search?</li>";
    echo "</ul>";
}

// Show sample products
echo "<h2>4. Sample Products from Database</h2>";
$sample_query = "SELECT id, name, category_id, is_active FROM products LIMIT 5";
$sample_result = mysqli_query($conn, $sample_query);
if ($sample_result && mysqli_num_rows($sample_result) > 0) {
    echo "<table border='1' cellpadding='5'>";
    echo "<tr><th>ID</th><th>Name</th><th>Category ID</th><th>Active</th></tr>";
    while ($row = mysqli_fetch_assoc($sample_result)) {
        echo "<tr>";
        echo "<td>" . $row['id'] . "</td>";
        echo "<td>" . htmlspecialchars($row['name']) . "</td>";
        echo "<td>" . $row['category_id'] . "</td>";
        echo "<td>" . ($row['is_active'] ? 'Yes' : 'No') . "</td>";
        echo "</tr>";
    }
    echo "</table>";
} else {
    echo "<p style='color: red;'>No products found in database!</p>";
}

?>
