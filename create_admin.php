<?php
require_once 'config.php';

// Delete old admin
mysqli_query($conn, "DELETE FROM admins WHERE username = 'admin'");

// Create new admin with correct password
$password = 'admin123';
$hash = password_hash($password, PASSWORD_DEFAULT);
$username = 'admin';
$email = 'admin@furnessence.com';
$full_name = 'Administrator';

$query = "INSERT INTO admins (username, email, password, full_name) VALUES (?, ?, ?, ?)";
$stmt = mysqli_prepare($conn, $query);
mysqli_stmt_bind_param($stmt, "ssss", $username, $email, $hash, $full_name);
mysqli_stmt_execute($stmt);

if (mysqli_stmt_affected_rows($stmt) > 0) {
    echo "✓ Admin user created successfully!\n\n";
    echo "Login credentials:\n";
    echo "Username: admin\n";
    echo "Password: admin123\n\n";
    
    // Verify
    $result = mysqli_query($conn, "SELECT username, email, LENGTH(password) as pwd_len FROM admins WHERE username = 'admin'");
    $admin = mysqli_fetch_assoc($result);
    echo "Verified:\n";
    echo "Username: " . $admin['username'] . "\n";
    echo "Email: " . $admin['email'] . "\n";
    echo "Password length: " . $admin['pwd_len'] . " (should be 60)\n";
    
    if ($admin['pwd_len'] == 60) {
        echo "\n✓✓✓ SUCCESS! Password hash is correct!\n";
        echo "\nGo to: http://localhost/Furnessence/Admin/Adminlogin.php\n";
    } else {
        echo "\n✗ ERROR: Password hash is still wrong\n";
    }
} else {
    echo "✗ Failed to create admin user\n";
    echo "Error: " . mysqli_error($conn) . "\n";
}
?>
