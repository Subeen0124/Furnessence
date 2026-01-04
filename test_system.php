<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

echo "<h1>Furnessence System Test</h1>";

// Test 1: PHP Version
echo "<h2>1. PHP Version</h2>";
echo "PHP Version: " . phpversion() . " ✓<br><br>";

// Test 2: Database Connection
echo "<h2>2. Database Connection</h2>";
define('DB_HOST', 'localhost');
define('DB_USER', 'root');
define('DB_PASS', '');
define('DB_NAME', 'furnessence_db');

$conn = @mysqli_connect(DB_HOST, DB_USER, DB_PASS, DB_NAME);

if ($conn) {
    echo "✓ Connected to database: " . DB_NAME . "<br><br>";
    
    // Test 3: Check Tables
    echo "<h2>3. Database Tables</h2>";
    $tables = ['admins', 'products', 'categories', 'users', 'orders', 'cart', 'wishlist', 'order_items'];
    foreach ($tables as $table) {
        $result = @mysqli_query($conn, "SHOW TABLES LIKE '$table'");
        if ($result && mysqli_num_rows($result) > 0) {
            echo "✓ Table '$table' exists<br>";
        } else {
            echo "✗ Table '$table' MISSING - Import database.sql!<br>";
        }
    }
    
    // Test 4: Check Admin User
    echo "<h2>4. Admin User</h2>";
    $result = @mysqli_query($conn, "SELECT * FROM admins LIMIT 1");
    if ($result && mysqli_num_rows($result) > 0) {
        $admin = mysqli_fetch_assoc($result);
        echo "✓ Admin user exists: " . $admin['username'] . "<br>";
        echo "Default password: admin123<br><br>";
    } else {
        echo "✗ No admin user found - Import database.sql!<br><br>";
    }
    
    mysqli_close($conn);
} else {
    echo "✗ Database connection FAILED<br>";
    echo "Error: " . mysqli_connect_error() . "<br>";
    echo "<br><strong>Action needed:</strong><br>";
    echo "1. Make sure XAMPP MySQL is running<br>";
    echo "2. Create database 'furnessence_db' in phpMyAdmin<br>";
    echo "3. Import database.sql file<br><br>";
}

// Test 5: File Paths
echo "<h2>5. File Paths</h2>";
$files = [
    'config.php' => 'config.php',
    'Admin login' => 'Admin/Adminlogin.php',
    'Admin config' => 'Admin/admin_config.php',
    'CSS file' => 'assests/css/admin.css',
    'Upload folder' => 'assests/images/products'
];

foreach ($files as $name => $path) {
    if (file_exists($path)) {
        echo "✓ $name: $path<br>";
    } else {
        echo "✗ $name NOT FOUND: $path<br>";
    }
}

echo "<br><h2>6. Session Test</h2>";
session_start();
echo "✓ Session started successfully<br>";
echo "Session ID: " . session_id() . "<br><br>";

echo "<h2>Next Steps:</h2>";
echo "<ol>";
echo "<li>If database connection failed: Start MySQL in XAMPP Control Panel</li>";
echo "<li>If tables missing: Import database.sql via phpMyAdmin</li>";
echo "<li>If everything is ✓: <a href='Admin/Adminlogin.php'>Go to Admin Login</a></li>";
echo "</ol>";

echo "<br><strong>Files:</strong><br>";
echo "<a href='install_check.php'>Run Full Installation Check</a><br>";
echo "<a href='Admin/Adminlogin.php'>Admin Login</a><br>";
echo "<a href='index.php'>Main Website</a>";
?>
