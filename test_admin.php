<?php
// Test script for admin panel functionality

echo "<h1>Furnessence Admin Panel Test</h1>";

// Include config
require_once 'config.php';

$conn = getDBConnection();

// Test database connection
if ($conn) {
    echo "<p style='color: green;'>✓ Database connection successful</p>";
} else {
    echo "<p style='color: red;'>✗ Database connection failed</p>";
    exit();
}

// Check if tables exist
$tables = ['users', 'products', 'categories', 'orders', 'order_items'];
$tables_exist = true;

foreach ($tables as $table) {
    $result = $conn->query("SHOW TABLES LIKE '$table'");
    if ($result->num_rows == 0) {
        echo "<p style='color: red;'>✗ Table '$table' does not exist</p>";
        $tables_exist = false;
    } else {
        echo "<p style='color: green;'>✓ Table '$table' exists</p>";
    }
}

if (!$tables_exist) {
    echo "<p>Please run setup.php first to create the database tables.</p>";
    $conn->close();
    exit();
}

// Check admin user
$admin_check = $conn->prepare("SELECT id, username, email, status FROM users WHERE username = ?");
$admin_check->bind_param("s", $admin_username = "admin");
$admin_check->execute();
$admin_result = $admin_check->get_result();

if ($admin_result->num_rows > 0) {
    $admin = $admin_result->fetch_assoc();
    echo "<p style='color: green;'>✓ Admin user exists: {$admin['username']} ({$admin['email']}) - Status: {$admin['status']}</p>";
} else {
    echo "<p style='color: red;'>✗ Admin user does not exist</p>";
}
$admin_check->close();

// Check sample data
$product_count = $conn->query("SELECT COUNT(*) as count FROM products")->fetch_assoc()['count'];
echo "<p style='color: green;'>✓ Products in database: $product_count</p>";

$category_count = $conn->query("SELECT COUNT(*) as count FROM categories")->fetch_assoc()['count'];
echo "<p style='color: green;'>✓ Categories in database: $category_count</p>";

$user_count = $conn->query("SELECT COUNT(*) as count FROM users")->fetch_assoc()['count'];
echo "<p style='color: green;'>✓ Users in database: $user_count</p>";

$conn->close();

echo "<h2>Admin Panel Features Test</h2>";
echo "<p>Admin login credentials: admin / admin123</p>";
echo "<p>Admin panel URL: http://localhost/Admin/AdminLogin.php</p>";

echo "<h3>Features to Test:</h3>";
echo "<ul>";
echo "<li>Admin Login - Use admin/admin123</li>";
echo "<li>Dashboard - View statistics</li>";
echo "<li>Manage Products - Add, edit, delete products</li>";
echo "<li>Manage Orders - View and update order status</li>";
echo "<li>Manage Users - View and update user status</li>";
echo "<li>Manage Categories - Add, edit, delete categories</li>";
echo "<li>Reports - View sales statistics</li>";
echo "<li>Logout - Session termination</li>";
echo "</ul>";

echo "<p><strong>Note:</strong> Make sure XAMPP Apache and MySQL are running before testing.</p>";
echo "<p>Run setup.php first if tables don't exist: http://localhost/setup.php</p>";
?>
