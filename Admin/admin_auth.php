<?php
/**
 * Admin Authentication Check
 * Include this file at the top of all admin pages to ensure only admins can access
 */

// Start session if not already started
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Check if admin is logged in
if (!isset($_SESSION['admin_logged_in']) || $_SESSION['admin_logged_in'] !== true) {
    header("location: Adminlogin.php");
    exit();
}

// Verify admin role
if (!isset($_SESSION['admin_role']) || $_SESSION['admin_role'] !== 'admin') {
    // Invalid role - destroy session and redirect
    session_destroy();
    header("location: Adminlogin.php");
    exit();
}

// Optional: Check for session timeout (30 minutes of inactivity)
$timeout_duration = 1800; // 30 minutes in seconds
if (isset($_SESSION['admin_last_activity']) && (time() - $_SESSION['admin_last_activity']) > $timeout_duration) {
    session_unset();
    session_destroy();
    header("location: Adminlogin.php?timeout=1");
    exit();
}

// Update last activity time
$_SESSION['admin_last_activity'] = time();
?>
