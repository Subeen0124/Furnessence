<?php
// Simulate admin login test
require_once __DIR__ . '/../config.php';
session_start();

$conn = getDBConnection();

// Test credentials
$username = 'admin';
$password = 'admin123';

echo "=== ADMIN LOGIN SIMULATION ===\n\n";

$sql = "SELECT id, username, password, role FROM users WHERE username = ? AND status = 'active' AND role = 'admin'";

if ($stmt = mysqli_prepare($conn, $sql)) {
    mysqli_stmt_bind_param($stmt, "s", $username);
    
    if (mysqli_stmt_execute($stmt)) {
        mysqli_stmt_store_result($stmt);
        
        if (mysqli_stmt_num_rows($stmt) == 1) {
            mysqli_stmt_bind_result($stmt, $id, $db_username, $hashed_password, $role);
            
            if (mysqli_stmt_fetch($stmt)) {
                echo "User found in database:\n";
                echo "ID: $id\n";
                echo "Username: $db_username\n";
                echo "Role: $role\n\n";
                
                if (password_verify($password, $hashed_password)) {
                    echo "✓ Password verification: SUCCESS\n";
                    echo "✓ Login would be successful!\n\n";
                    
                    echo "Session would contain:\n";
                    echo "  - admin_logged_in: true\n";
                    echo "  - admin_id: $id\n";
                    echo "  - admin_username: $db_username\n";
                    echo "  - admin_role: $role\n\n";
                    
                    echo "Would redirect to: Admindashboard.php\n";
                } else {
                    echo "✗ Password verification: FAILED\n";
                }
            }
        } else {
            echo "✗ No admin user found with username '$username'\n";
        }
    }
    mysqli_stmt_close($stmt);
}

mysqli_close($conn);
?>
