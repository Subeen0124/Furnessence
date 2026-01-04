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
    $query = "SELECT id, username, email, full_name FROM admins WHERE id = $admin_id LIMIT 1";
    $result = mysqli_query($conn, $query);
    
    if ($result && mysqli_num_rows($result) > 0) {
        return mysqli_fetch_assoc($result);
    }
    
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
