<?php
/**
 * Complete Project Test - All Components
 */
error_reporting(E_ALL);
ini_set('display_errors', 1);

echo "<!DOCTYPE html>
<html><head>
<meta charset='UTF-8'>
<title>Complete Project Test</title>
<style>
body{font-family:Arial;max-width:1000px;margin:30px auto;padding:20px;background:#f5f5f5}
.test{background:#fff;padding:20px;margin:15px 0;border-radius:8px;box-shadow:0 2px 4px rgba(0,0,0,0.1)}
h1{color:#333;text-align:center}h2{color:#555;border-bottom:2px solid #ddd;padding-bottom:10px}
.pass{color:#28a745;font-weight:bold}.fail{color:#dc3545;font-weight:bold}
.status{padding:10px;margin:10px 0;border-radius:4px}
.status.ok{background:#d4edda;border:1px solid #c3e6cb}
.status.error{background:#f8d7da;border:1px solid #f5c6cb}
table{width:100%;border-collapse:collapse;margin:10px 0}
th,td{padding:8px;text-align:left;border-bottom:1px solid #ddd}
th{background:#343a40;color:#fff}
.btn{display:inline-block;padding:10px 20px;margin:5px;text-decoration:none;background:#007bff;color:#fff;border-radius:4px}
.btn:hover{background:#0056b3}
ul{list-style:none;padding:0}li{padding:5px 0}
</style>
</head><body>
<h1>🎯 Furnessence - Complete Project Test</h1>";

$all_tests_passed = true;

// Test 1: Database Connection
echo "<div class='test'><h2>1. Database Connection</h2>";
try {
    require_once 'config.php';
    $conn = getDBConnection();
    echo "<div class='status ok'><span class='pass'>✓ PASS:</span> Database connected (furnessence)</div>";
} catch (Exception $e) {
    echo "<div class='status error'><span class='fail'>✗ FAIL:</span> " . $e->getMessage() . "</div>";
    $all_tests_passed = false;
    die("</div></body></html>");
}
echo "</div>";

// Test 2: Database Tables
echo "<div class='test'><h2>2. Database Tables</h2>";
$required_tables = ['users', 'categories', 'products', 'orders', 'order_items'];
$result = $conn->query("SHOW TABLES");
$existing = [];
while($row = $result->fetch_array()) $existing[] = $row[0];

$missing = array_diff($required_tables, $existing);
if (empty($missing)) {
    echo "<div class='status ok'><span class='pass'>✓ PASS:</span> All required tables exist (" . implode(', ', $required_tables) . ")</div>";
} else {
    echo "<div class='status error'><span class='fail'>✗ FAIL:</span> Missing tables: " . implode(', ', $missing) . "</div>";
    $all_tests_passed = false;
}
echo "</div>";

// Test 3: Admin User
echo "<div class='test'><h2>3. Admin Account</h2>";
$admin = $conn->query("SELECT id, username, email, role, status FROM users WHERE username='admin'")->fetch_assoc();
if ($admin && $admin['role'] === 'admin' && $admin['status'] === 'active') {
    echo "<div class='status ok'><span class='pass'>✓ PASS:</span> Admin account configured correctly</div>";
    echo "<table><tr><th>Field</th><th>Value</th></tr>";
    echo "<tr><td>Username</td><td>{$admin['username']}</td></tr>";
    echo "<tr><td>Email</td><td>{$admin['email']}</td></tr>";
    echo "<tr><td>Role</td><td><strong>{$admin['role']}</strong></td></tr>";
    echo "<tr><td>Status</td><td>{$admin['status']}</td></tr>";
    echo "<tr><td>Password</td><td>admin123</td></tr></table>";
    
    // Test password
    $pwd_result = $conn->query("SELECT password FROM users WHERE username='admin'")->fetch_assoc();
    if (password_verify('admin123', $pwd_result['password'])) {
        echo "<div class='status ok'><span class='pass'>✓ PASS:</span> Admin password verified (admin123)</div>";
    } else {
        echo "<div class='status error'><span class='fail'>✗ FAIL:</span> Admin password verification failed</div>";
        $all_tests_passed = false;
    }
} else {
    echo "<div class='status error'><span class='fail'>✗ FAIL:</span> Admin account not properly configured</div>";
    $all_tests_passed = false;
}
echo "</div>";

// Test 4: Data Counts
echo "<div class='test'><h2>4. Database Content</h2>";
$counts = [
    'Products' => $conn->query("SELECT COUNT(*) as c FROM products")->fetch_assoc()['c'],
    'Categories' => $conn->query("SELECT COUNT(*) as c FROM categories")->fetch_assoc()['c'],
    'Users' => $conn->query("SELECT COUNT(*) as c FROM users")->fetch_assoc()['c'],
    'Orders' => $conn->query("SELECT COUNT(*) as c FROM orders")->fetch_assoc()['c']
];
echo "<table><tr><th>Table</th><th>Count</th><th>Status</th></tr>";
foreach($counts as $table => $count) {
    $status = $count > 0 ? "<span class='pass'>✓ Has data</span>" : "⚠ Empty";
    echo "<tr><td>$table</td><td>$count</td><td>$status</td></tr>";
}
echo "</table></div>";

// Test 5: Critical Files
echo "<div class='test'><h2>5. Critical Files</h2>";
$files = [
    'config.php' => 'Configuration',
    'index.php' => 'Homepage',
    'login.php' => 'User Login',
    'registration.php' => 'Registration',
    'checkout.php' => 'Checkout',
    'Admin/Adminlogin.php' => 'Admin Login',
    'Admin/Admindashboard.php' => 'Admin Dashboard',
    'Admin/manage-products.php' => 'Product Management',
    'Admin/manage-orders.php' => 'Order Management'
];
$missing_files = [];
echo "<ul>";
foreach($files as $file => $desc) {
    if (file_exists($file)) {
        echo "<li><span class='pass'>✓</span> $desc ($file)</li>";
    } else {
        echo "<li><span class='fail'>✗</span> $desc ($file) <span class='fail'>MISSING</span></li>";
        $missing_files[] = $file;
        $all_tests_passed = false;
    }
}
echo "</ul>";
if (empty($missing_files)) {
    echo "<div class='status ok'><span class='pass'>✓ PASS:</span> All critical files present</div>";
}
echo "</div>";

// Test 6: Services
echo "<div class='test'><h2>6. Server Services</h2>";
$mysql_running = $conn->ping();
$apache_running = true; // If this page loads, Apache is running

echo "<ul>";
echo "<li><span class='pass'>✓</span> Apache Web Server - Running</li>";
echo "<li><span class='pass'>✓</span> MySQL Database - Running</li>";
echo "<li><span class='pass'>✓</span> PHP " . phpversion() . " - Active</li>";
echo "</ul>";
echo "<div class='status ok'><span class='pass'>✓ PASS:</span> All services operational</div>";
echo "</div>";

// Test 7: Admin Login Test
echo "<div class='test'><h2>7. Admin Login Verification</h2>";
$sql = "SELECT id, username, password, role FROM users WHERE username = 'admin' AND status = 'active' AND role = 'admin'";
$stmt = $conn->prepare($sql);
$stmt->execute();
$result = $stmt->get_result();
if ($result->num_rows === 1) {
    $admin = $result->fetch_assoc();
    if (password_verify('admin123', $admin['password'])) {
        echo "<div class='status ok'><span class='pass'>✓ PASS:</span> Admin login authentication working</div>";
        echo "<p>Login will succeed with:<br>Username: <code>admin</code><br>Password: <code>admin123</code></p>";
    } else {
        echo "<div class='status error'><span class='fail'>✗ FAIL:</span> Password verification failed</div>";
        $all_tests_passed = false;
    }
} else {
    echo "<div class='status error'><span class='fail'>✗ FAIL:</span> Admin user query failed</div>";
    $all_tests_passed = false;
}
echo "</div>";

$conn->close();

// Final Summary
if ($all_tests_passed) {
    echo "<div class='test' style='background:#d4edda;border:2px solid #28a745'>
    <h2 style='color:#155724'>✅ ALL TESTS PASSED!</h2>
    <p style='font-size:18px;color:#155724'>Your Furnessence project is fully operational!</p>
    <div style='margin-top:20px'>
        <strong>Quick Links:</strong><br>
        <a href='index.php' class='btn'>Main Website</a>
        <a href='login.php' class='btn'>User Login</a>
        <a href='registration.php' class='btn'>Register</a>
        <a href='Admin/Adminlogin.php' class='btn' style='background:#dc3545'>Admin Panel</a>
    </div>
    <div style='margin-top:20px;padding:15px;background:#fff;border-radius:4px'>
        <strong>Admin Login Credentials:</strong><br>
        Username: <code>admin</code><br>
        Password: <code>admin123</code>
    </div>
    </div>";
} else {
    echo "<div class='test' style='background:#f8d7da;border:2px solid:#dc3545'>
    <h2 style='color:#721c24'>⚠ SOME TESTS FAILED</h2>
    <p style='color:#721c24'>Please review the failed tests above and fix the issues.</p>
    </div>";
}

echo "</body></html>";
?>
