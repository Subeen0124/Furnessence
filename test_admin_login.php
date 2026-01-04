<?php
require_once 'config.php';

$conn = getDBConnection();

// Check admin user
$result = $conn->query("SELECT id, username, email, password, role, status FROM users WHERE username = 'admin'");
$admin = $result->fetch_assoc();

echo "=== ADMIN USER TEST ===\n\n";

if ($admin) {
    echo "Admin user found:\n";
    echo "ID: " . $admin['id'] . "\n";
    echo "Username: " . $admin['username'] . "\n";
    echo "Email: " . $admin['email'] . "\n";
    echo "Role: " . $admin['role'] . "\n";
    echo "Status: " . $admin['status'] . "\n";
    echo "Password Hash: " . substr($admin['password'], 0, 20) . "...\n\n";
    
    // Test password
    echo "Testing password 'admin123':\n";
    if (password_verify('admin123', $admin['password'])) {
        echo "✓ Password VALID\n";
    } else {
        echo "✗ Password INVALID\n";
        echo "Generating new hash...\n";
        $new_hash = password_hash('admin123', PASSWORD_DEFAULT);
        echo "New hash: $new_hash\n\n";
        
        // Update password
        $stmt = $conn->prepare("UPDATE users SET password = ? WHERE username = 'admin'");
        $stmt->bind_param("s", $new_hash);
        if ($stmt->execute()) {
            echo "✓ Password updated successfully!\n";
        }
    }
} else {
    echo "✗ Admin user NOT FOUND\n";
    echo "Creating admin user...\n";
    
    $password = password_hash('admin123', PASSWORD_DEFAULT);
    $stmt = $conn->prepare("INSERT INTO users (username, email, password, role, status) VALUES ('admin', 'admin@furnessence.com', ?, 'admin', 'active')");
    $stmt->bind_param("s", $password);
    if ($stmt->execute()) {
        echo "✓ Admin user created successfully!\n";
    }
}

$conn->close();
?>
