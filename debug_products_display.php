<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

require_once 'config.php';

echo "<!DOCTYPE html><html><head><title>Debug Product Display</title></head><body>";
echo "<h1>Complete Product Display Debugging</h1>";

// Test 1: Database Connection
echo "<h2>1. Database Connection</h2>";
if ($conn) {
    echo "<p style='color: green;'>✓ Database connected successfully</p>";
} else {
    echo "<p style='color: red;'>✗ Database connection failed: " . mysqli_connect_error() . "</p>";
    exit();
}

// Test 2: Products Query
echo "<h2>2. Products Query</h2>";
$products_query = "SELECT p.*, c.name as category_name, c.slug as category_slug 
                  FROM products p 
                  LEFT JOIN categories c ON p.category_id = c.id 
                  WHERE p.is_active = 1 
                  ORDER BY p.created_at DESC 
                  LIMIT 20";
echo "<pre>Query: $products_query</pre>";
$products_result = mysqli_query($conn, $products_query);

if ($products_result) {
    $count = mysqli_num_rows($products_result);
    echo "<p style='color: green;'>✓ Query executed successfully. Found $count products</p>";
} else {
    echo "<p style='color: red;'>✗ Query failed: " . mysqli_error($conn) . "</p>";
    exit();
}

// Test 3: Generate HTML for each product
echo "<h2>3. Product HTML Generation</h2>";
echo "<div style='border: 2px solid #333; padding: 20px; margin: 20px 0; background: #f5f5f5;'>";

if (mysqli_num_rows($products_result) > 0) {
    $product_count = 0;
    while ($product = mysqli_fetch_assoc($products_result)) {
        $product_count++;
        $is_out_of_stock = $product['stock_quantity'] == 0;
        $is_low_stock = $product['stock_quantity'] > 0 && $product['stock_quantity'] <= $product['low_stock_threshold'];
        $category_class = strtolower($product['category_slug'] ?? 'living-room');
        $product_image = !empty($product['image']) ? './' . $product['image'] : './assests/images/products/product-1.jpg';
        
        echo "<div style='background: white; border: 1px solid #ddd; padding: 15px; margin: 10px 0;'>";
        echo "<h3>Product #$product_count: " . htmlspecialchars($product['name']) . "</h3>";
        echo "<table border='1' style='width: 100%; border-collapse: collapse;'>";
        echo "<tr><th>Field</th><th>Value</th></tr>";
        echo "<tr><td>ID</td><td>" . $product['id'] . "</td></tr>";
        echo "<tr><td>Name</td><td>" . htmlspecialchars($product['name']) . "</td></tr>";
        echo "<tr><td>Price</td><td>$" . number_format($product['price'], 2) . "</td></tr>";
        echo "<tr><td>Stock</td><td>" . $product['stock_quantity'] . "</td></tr>";
        echo "<tr><td>Category Name</td><td>" . htmlspecialchars($product['category_name']) . "</td></tr>";
        echo "<tr><td>Category Slug</td><td>" . htmlspecialchars($product['category_slug']) . "</td></tr>";
        echo "<tr><td>Category Class</td><td>" . $category_class . "</td></tr>";
        echo "<tr><td>data-filter</td><td>" . $category_class . "</td></tr>";
        echo "<tr><td>Image Path (DB)</td><td>" . htmlspecialchars($product['image']) . "</td></tr>";
        echo "<tr><td>Image Path (Used)</td><td>" . htmlspecialchars($product_image) . "</td></tr>";
        echo "<tr><td>Out of Stock?</td><td>" . ($is_out_of_stock ? 'Yes' : 'No') . "</td></tr>";
        echo "<tr><td>Low Stock?</td><td>" . ($is_low_stock ? 'Yes' : 'No') . "</td></tr>";
        echo "</table>";
        
        // Check if image file exists
        $file_check = str_replace('./', '', $product_image);
        $full_path = "c:/xampp/htdocs/Furnessence/" . $file_check;
        $exists = file_exists($full_path);
        echo "<p><strong>Image File Check:</strong> " . ($exists ? "<span style='color: green;'>✓ Exists</span>" : "<span style='color: red;'>✗ Not Found</span>") . " ($full_path)</p>";
        
        if ($exists) {
            echo "<p><strong>Image Preview:</strong><br><img src='$product_image' width='200' style='border: 1px solid #ccc;'></p>";
        }
        
        echo "</div>";
    }
    
    echo "<p style='color: green; font-size: 18px; font-weight: bold;'>✓ Total products generated: $product_count</p>";
} else {
    echo "<p style='color: red;'>✗ No products found in query result</p>";
}

echo "</div>";

// Test 4: Check if JavaScript/CSS files exist
echo "<h2>4. Asset Files Check</h2>";
$files_to_check = [
    'assests/css/style.css',
    'assests/js/script.js'
];

foreach ($files_to_check as $file) {
    $full_path = "c:/xampp/htdocs/Furnessence/" . $file;
    $exists = file_exists($full_path);
    $status = $exists ? "<span style='color: green;'>✓ Exists</span>" : "<span style='color: red;'>✗ Missing</span>";
    echo "<p>$file - $status</p>";
}

// Test 5: Check filter button values
echo "<h2>5. Expected Filter Button Values</h2>";
echo "<ul>";
echo "<li><strong>all</strong> - Show all products</li>";
echo "<li><strong>living-room</strong> - Show Living Room category</li>";
echo "<li><strong>bedroom</strong> - Show Bedroom category</li>";
echo "<li><strong>office</strong> - Show Office category</li>";
echo "<li><strong>dining</strong> - Show Dining category</li>";
echo "</ul>";

// Test 6: Generate actual HTML that would appear on page
echo "<h2>6. Sample HTML Output (First Product)</h2>";
mysqli_data_seek($products_result, 0); // Reset to first row
$sample_product = mysqli_fetch_assoc($products_result);

if ($sample_product) {
    $category_class = strtolower($sample_product['category_slug'] ?? 'living-room');
    $product_image = !empty($sample_product['image']) ? './' . $sample_product['image'] : './assests/images/products/product-1.jpg';
    
    echo "<pre style='background: #f0f0f0; padding: 15px; border: 1px solid #ccc; overflow-x: auto;'>";
    echo htmlspecialchars('
<li class="' . $category_class . '" data-filter="' . $category_class . '">
  <div class="product-card">
    <a href="#" class="card-banner img-holder has-before" style="--width: 300; --height: 300;">
      <img src="' . $product_image . '" width="300" height="300" loading="lazy" alt="' . $sample_product['name'] . '" class="img-cover">
      <button class="card-action-btn add-to-cart-btn" 
              data-product-id="' . $sample_product['id'] . '"
              data-product-name="' . $sample_product['name'] . '"
              data-product-price="' . $sample_product['price'] . '"
              data-product-image="' . $sample_product['image'] . '">
        Add to Cart
      </button>
    </a>
  </div>
</li>');
    echo "</pre>";
}

mysqli_close($conn);

echo "<hr>";
echo "<h2>Instructions:</h2>";
echo "<ol>";
echo "<li>If all tests show green checkmarks, the backend is working correctly</li>";
echo "<li>If products still don't show on homepage, check browser console (F12) for JavaScript errors</li>";
echo "<li>Make sure to hard refresh (Ctrl+Shift+R) the homepage</li>";
echo "<li>Check if CSS is hiding products (inspect element on the page)</li>";
echo "</ol>";

echo "<p><a href='index.php' style='background: #4CAF50; color: white; padding: 10px 20px; text-decoration: none; border-radius: 5px;'>Go to Homepage</a></p>";

echo "</body></html>";
?>
