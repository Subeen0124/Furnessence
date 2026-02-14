<?php
/**
 * Admin Configuration and Authentication Helper
 */

require_once dirname(__DIR__) . '/config.php';

// Check if admin is logged in
function isAdminLoggedIn() {
    return isset($_SESSION['admin_id']) && isset($_SESSION['admin_username']);
}

// Require admin login
function requireAdminLogin() {
    if (!isAdminLoggedIn()) {
        header('Location: Adminlogin.php');
        exit();
    }
}

// Get admin info
function getAdminInfo() {
    if (!isAdminLoggedIn()) {
        return null;
    }
    
    global $conn;
    $admin_id = $_SESSION['admin_id'];
    $stmt = mysqli_prepare($conn, "SELECT id, username, email, full_name FROM admins WHERE id = ? LIMIT 1");
    mysqli_stmt_bind_param($stmt, "i", $admin_id);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);
    
    if ($result && mysqli_num_rows($result) > 0) {
        $admin = mysqli_fetch_assoc($result);
        mysqli_stmt_close($stmt);
        return $admin;
    }
    
    mysqli_stmt_close($stmt);
    return null;
}

// Admin logout
function adminLogout() {
    unset($_SESSION['admin_id']);
    unset($_SESSION['admin_username']);
    session_destroy();
    header('Location: Adminlogin.php');
    exit();
}
?>
