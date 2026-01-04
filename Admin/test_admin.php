<?php
/**
 * Admin System Test and Verification
 * Tests admin authentication and access control
 */

error_reporting(E_ALL);
ini_set('display_errors', 1);

require_once '../config.php';

echo "<!DOCTYPE html>
<html lang='en'>
<head>
    <meta charset='UTF-8'>
    <meta name='viewport' content='width=device-width, initial-scale=1.0'>
    <title>Admin System Test - Furnessence</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            max-width: 900px;
            margin: 30px auto;
            padding: 20px;
            background-color: #f5f5f5;
        }
        .test-section {
            background: white;
            padding: 20px;
            margin-bottom: 20px;
            border-radius: 8px;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
        }
        .success { color: #28a745; font-weight: bold; }
        .error { color: #dc3545; font-weight: bold; }
        .warning { color: #ffc107; font-weight: bold; }
        .info { color: #17a2b8; }
        h1 { color: #333; text-align: center; }
        h2 { color: #555; border-bottom: 2px solid #ddd; padding-bottom: 10px; }
        .status { padding: 10px; margin: 10px 0; border-radius: 4px; }
        .status.pass { background: #d4edda; border: 1px solid #c3e6cb; }
        .status.fail { background: #f8d7da; border: 1px solid #f5c6cb; }
        .status.warn { background: #fff3cd; border: 1px solid #ffeaa7; }
        table { width: 100%; border-collapse: collapse; margin-top: 10px; }
        th, td { padding: 10px; text-align: left; border-bottom: 1px solid #ddd; }
        th { background-color: #343a40; color: white; }
        .file-list { list-style: none; padding: 0; }
        .file-list li { padding: 5px 0; }
        .btn { display: inline-block; padding: 10px 20px; margin: 5px; text-decoration: none; 
               background: #007bff; color: white; border-radius: 4px; }
        .btn:hover { background: #0056b3; }
    </style>
</head>
<body>
    <h1>🔐 Admin System Test & Verification</h1>";

// Test 1: Database Connection
echo "<div class='test-section'>
    <h2>1. Database Connection</h2>";
try {
    $conn = getDBConnection();
    echo "<div class='status pass'><span class='success'>✓ PASS:</span> Database connected successfully</div>";
} catch (Exception $e) {
    echo "<div class='status fail'><span class='error'>✗ FAIL:</span> " . $e->getMessage() . "</div>";
    die("</div></body></html>");
}
echo "</div>";

// Test 2: Admin User Verification
echo "<div class='test-section'>
    <h2>2. Admin User Verification</h2>";

$admin_sql = "SELECT id, username, email, role, status FROM users WHERE username = 'admin'";
$admin_result = $conn->query($admin_sql);

if ($admin_result && $admin_result->num_rows > 0) {
    $admin = $admin_result->fetch_assoc();
    
    if ($admin['role'] === 'admin') {
        echo "<div class='status pass'><span class='success'>✓ PASS:</span> Admin user exists with correct role</div>";
    } else {
        echo "<div class='status fail'><span class='error'>✗ FAIL:</span> Admin user exists but role is '{$admin['role']}' instead of 'admin'</div>";
    }
    
    if ($admin['status'] === 'active') {
        echo "<div class='status pass'><span class='success'>✓ PASS:</span> Admin account is active</div>";
    } else {
        echo "<div class='status warn'><span class='warning'>⚠ WARNING:</span> Admin account status is '{$admin['status']}'</div>";
    }
    
    echo "<table>
        <tr><th>Field</th><th>Value</th></tr>
        <tr><td>ID</td><td>{$admin['id']}</td></tr>
        <tr><td>Username</td><td>{$admin['username']}</td></tr>
        <tr><td>Email</td><td>{$admin['email']}</td></tr>
        <tr><td>Role</td><td><strong>{$admin['role']}</strong></td></tr>
        <tr><td>Status</td><td>{$admin['status']}</td></tr>
        <tr><td>Default Password</td><td><code>admin123</code></td></tr>
    </table>";
} else {
    echo "<div class='status fail'><span class='error'>✗ FAIL:</span> Admin user not found in database</div>";
}
echo "</div>";

// Test 3: Users Table Structure
echo "<div class='test-section'>
    <h2>3. Users Table Structure</h2>";

$structure_sql = "SHOW COLUMNS FROM users";
$structure_result = $conn->query($structure_sql);

$required_columns = ['id', 'username', 'email', 'password', 'role', 'status', 'created_at'];
$existing_columns = [];

echo "<table>
    <tr><th>Column</th><th>Type</th><th>Status</th></tr>";

while ($column = $structure_result->fetch_assoc()) {
    $existing_columns[] = $column['Field'];
    $is_required = in_array($column['Field'], $required_columns);
    $status_class = $is_required ? 'success' : 'info';
    $status_icon = $is_required ? '✓ Required' : '○ Optional';
    
    echo "<tr>
        <td><strong>{$column['Field']}</strong></td>
        <td>{$column['Type']}</td>
        <td><span class='$status_class'>$status_icon</span></td>
    </tr>";
}
echo "</table>";

// Check for missing required columns
$missing_columns = array_diff($required_columns, $existing_columns);
if (empty($missing_columns)) {
    echo "<div class='status pass'><span class='success'>✓ PASS:</span> All required columns present</div>";
} else {
    echo "<div class='status fail'><span class='error'>✗ FAIL:</span> Missing columns: " . implode(', ', $missing_columns) . "</div>";
}
echo "</div>";

// Test 4: Admin Files Check
echo "<div class='test-section'>
    <h2>4. Admin Files Verification</h2>";

$admin_files = [
    'Adminlogin.php' => 'Admin login page',
    'Adminlogout.php' => 'Admin logout handler',
    'Admindashboard.php' => 'Admin dashboard',
    'manage-products.php' => 'Product management',
    'manage-categories.php' => 'Category management',
    'manage-orders.php' => 'Order management',
    'manage-users.php' => 'User management',
    'add-product.php' => 'Add product form',
    'edit-product.php' => 'Edit product form',
    'edit-category.php' => 'Edit category form',
    'reports.php' => 'Reports page',
    'admin_auth.php' => 'Authentication helper'
];

$all_files_exist = true;

echo "<ul class='file-list'>";
foreach ($admin_files as $file => $description) {
    $file_path = __DIR__ . '/' . $file;
    if (file_exists($file_path)) {
        echo "<li><span class='success'>✓</span> <strong>$file</strong> - $description</li>";
    } else {
        echo "<li><span class='error'>✗</span> <strong>$file</strong> - $description <span class='error'>(NOT FOUND)</span></li>";
        $all_files_exist = false;
    }
}
echo "</ul>";

if ($all_files_exist) {
    echo "<div class='status pass'><span class='success'>✓ PASS:</span> All admin files present</div>";
} else {
    echo "<div class='status fail'><span class='error'>✗ FAIL:</span> Some admin files are missing</div>";
}
echo "</div>";

// Test 5: Security Features
echo "<div class='test-section'>
    <h2>5. Security Features Check</h2>";

// Check if admin login file has role checking
$login_content = file_get_contents(__DIR__ . '/Adminlogin.php');
$has_role_check = strpos($login_content, "role = 'admin'") !== false || strpos($login_content, 'role = "admin"') !== false;

if ($has_role_check) {
    echo "<div class='status pass'><span class='success'>✓ PASS:</span> Admin login includes role verification</div>";
} else {
    echo "<div class='status warn'><span class='warning'>⚠ WARNING:</span> Admin login may not check for admin role</div>";
}

// Check if dashboard has authentication
$dashboard_content = file_get_contents(__DIR__ . '/Admindashboard.php');
$has_auth_check = strpos($dashboard_content, 'admin_logged_in') !== false;

if ($has_auth_check) {
    echo "<div class='status pass'><span class='success'>✓ PASS:</span> Dashboard has authentication check</div>";
} else {
    echo "<div class='status fail'><span class='error'>✗ FAIL:</span> Dashboard missing authentication check</div>";
}

echo "</div>";

// Test 6: User Accounts Summary
echo "<div class='test-section'>
    <h2>6. User Accounts Summary</h2>";

$users_sql = "SELECT role, status, COUNT(*) as count FROM users GROUP BY role, status ORDER BY role, status";
$users_result = $conn->query($users_sql);

echo "<table>
    <tr><th>Role</th><th>Status</th><th>Count</th></tr>";

$total_admins = 0;
$active_admins = 0;

while ($row = $users_result->fetch_assoc()) {
    echo "<tr>
        <td><strong>{$row['role']}</strong></td>
        <td>{$row['status']}</td>
        <td>{$row['count']}</td>
    </tr>";
    
    if ($row['role'] === 'admin') {
        $total_admins += $row['count'];
        if ($row['status'] === 'active') {
            $active_admins += $row['count'];
        }
    }
}
echo "</table>";

if ($active_admins > 0) {
    echo "<div class='status pass'><span class='success'>✓ PASS:</span> {$active_admins} active admin account(s) found</div>";
} else {
    echo "<div class='status fail'><span class='error'>✗ FAIL:</span> No active admin accounts found</div>";
}
echo "</div>";

// Final Summary
echo "<div class='test-section' style='background: #d4edda; border: 2px solid #28a745;'>
    <h2 style='color: #155724;'>✅ Admin System Status</h2>";

if ($active_admins > 0 && $all_files_exist) {
    echo "<p style='color: #155724; font-size: 18px; font-weight: bold;'>
        Admin system is OPERATIONAL and ready to use!
    </p>
    <div style='margin-top: 20px;'>
        <strong>Login Credentials:</strong>
        <table style='margin-top: 10px;'>
            <tr><th>Username</th><td>admin</td></tr>
            <tr><th>Password</th><td>admin123</td></tr>
        </table>
    </div>
    <div style='margin-top: 20px;'>
        <a href='Adminlogin.php' class='btn'>→ Login to Admin Panel</a>
        <a href='../test_connection.php' class='btn' style='background: #6c757d;'>← Back to System Test</a>
    </div>";
} else {
    echo "<p style='color: #721c24; font-size: 16px;'>
        <strong>Issues detected:</strong> Please review the test results above.
    </p>";
}

echo "</div>";

$conn->close();

echo "</body></html>";
?>
