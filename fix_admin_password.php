<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Include config
require_once 'config.php';

echo "<h2>Role-Based Admin Login Setup</h2>";

// Create test accounts
echo "<h3>Setting up test accounts...</h3>";

// 1. Create/Update admin in admins table
$password = 'admin123';
$hash = password_hash($password, PASSWORD_DEFAULT);

$check_admin = mysqli_query($conn, "SELECT id FROM admins WHERE username = 'admin' LIMIT 1");
if (mysqli_num_rows($check_admin) > 0) {
    $update = "UPDATE admins SET password = '" . mysqli_real_escape_string($conn, $hash) . "' WHERE username = 'admin'";
    mysqli_query($conn, $update);
    echo "<p style='color: green;'>✓ Admin user password updated</p>";
} else {
    $insert = "INSERT INTO admins (username, email, password, full_name) VALUES ('admin', 'admin@furnessence.com', '$hash', 'Administrator')";
    mysqli_query($conn, $insert);
    echo "<p style='color: green;'>✓ Admin user created</p>";
}

// 2. Create test user with admin role
$check_user = mysqli_query($conn, "SELECT id FROM users WHERE email = 'testadmin@test.com' LIMIT 1");
if (mysqli_num_rows($check_user) > 0) {
    $update = "UPDATE users SET password = '" . mysqli_real_escape_string($conn, $hash) . "', role = 'admin' WHERE email = 'testadmin@test.com'";
    mysqli_query($conn, $update);
    echo "<p style='color: green;'>✓ Test admin user updated</p>";
} else {
    $insert = "INSERT INTO users (name, email, password, role) VALUES ('Test Admin', 'testadmin@test.com', '$hash', 'admin')";
    mysqli_query($conn, $insert);
    echo "<p style='color: green;'>✓ Test admin user created</p>";
}

echo "<hr>";
echo "<h3>Login Options:</h3>";

echo "<div style='background: #e7f3ff; padding: 20px; border-radius: 8px; margin: 20px 0;'>";
echo "<h4>Option 1: Admin Table Login</h4>";
echo "<strong>Username:</strong> admin<br>";
echo "<strong>Password:</strong> admin123<br>";
echo "</div>";

echo "<div style='background: #d4edda; padding: 20px; border-radius: 8px; margin: 20px 0;'>";
echo "<h4>Option 2: User with Admin Role</h4>";
echo "<strong>Email:</strong> testadmin@test.com<br>";
echo "<strong>Password:</strong> admin123<br>";
echo "</div>";

echo "<hr>";
echo "<h3>Verify Setup:</h3>";

// Test admin login
$result = mysqli_query($conn, "SELECT username, email FROM admins WHERE username = 'admin'");
if ($row = mysqli_fetch_assoc($result)) {
    echo "<p>✓ Admin exists: {$row['username']} ({$row['email']})</p>";
}

// Test user admin login
$result = mysqli_query($conn, "SELECT name, email, role FROM users WHERE role = 'admin'");
if (mysqli_num_rows($result) > 0) {
    echo "<p>✓ Users with admin role:</p><ul>";
    while ($row = mysqli_fetch_assoc($result)) {
        echo "<li>{$row['name']} ({$row['email']}) - Role: {$row['role']}</li>";
    }
    echo "</ul>";
}

echo "<br><a href='Admin/Adminlogin.php' style='padding: 12px 24px; background: #667eea; color: white; text-decoration: none; border-radius: 8px; display: inline-block;'>Go to Admin Login →</a>";
?>
