<?php
require_once 'admin_config.php';

// Perform logout
if (isAdminLoggedIn()) {
    adminLogout();
}

// If not logged in, redirect to login
header('Location: Adminlogin.php');
exit();
?>
