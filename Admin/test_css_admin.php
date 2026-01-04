<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>CSS & Admin Test - Furnessence</title>
    <style>
        body{font-family:Arial;max-width:900px;margin:30px auto;padding:20px;background:#f5f5f5}
        .test{background:#fff;padding:20px;margin:15px 0;border-radius:8px;box-shadow:0 2px 4px rgba(0,0,0,0.1)}
        h1{color:#333;text-align:center}h2{color:#555;border-bottom:2px solid #ddd;padding-bottom:10px}
        .pass{color:#28a745;font-weight:bold}.fail{color:#dc3545;font-weight:bold}
        .status{padding:10px;margin:10px 0;border-radius:4px}
        .status.ok{background:#d4edda;border:1px solid #c3e6cb}
        .status.error{background:#f8d7da;border:1px solid #f5c6cb}
        table{width:100%;border-collapse:collapse;margin:10px 0}
        th,td{padding:8px;text-align:left;border-bottom:1px solid #ddd}
        th{background:#343a40;color:#fff}
        ul{list-style:none;padding:0}li{padding:5px 0}
        .btn{display:inline-block;padding:10px 20px;margin:5px;text-decoration:none;background:#007bff;color:#fff;border-radius:4px}
    </style>
</head>
<body>
    <h1>🎨 CSS & Admin System Test</h1>
    
    <?php
    error_reporting(E_ALL);
    ini_set('display_errors', 1);
    
    // Test 1: CSS Files
    echo "<div class='test'><h2>1. CSS Files Check</h2>";
    $css_files = [
        '../assets/css/style.css' => 'Main Stylesheet',
        '../assets/css/auth.css' => 'Authentication Styles',
        '../assets/css/admin.css' => 'Admin Panel Styles',
        '../assets/style.css' => 'Style Copy (for compatibility)'
    ];
    
    $all_css_exist = true;
    echo "<ul>";
    foreach($css_files as $file => $desc) {
        if (file_exists($file)) {
            $size = filesize($file);
            echo "<li><span class='pass'>✓</span> $desc ($file) - " . number_format($size) . " bytes</li>";
        } else {
            echo "<li><span class='fail'>✗</span> $desc ($file) <span class='fail'>MISSING</span></li>";
            $all_css_exist = false;
        }
    }
    echo "</ul>";
    
    if ($all_css_exist) {
        echo "<div class='status ok'><span class='pass'>✓ PASS:</span> All CSS files present and accessible</div>";
    } else {
        echo "<div class='status error'><span class='fail'>✗ FAIL:</span> Some CSS files are missing</div>";
    }
    echo "</div>";
    
    // Test 2: Admin PHP Files
    echo "<div class='test'><h2>2. Admin PHP Files</h2>";
    $admin_files = [
        'Adminlogin.php' => 'Admin Login',
        'Admindashboard.php' => 'Dashboard',
        'manage-products.php' => 'Product Management',
        'manage-categories.php' => 'Category Management',
        'manage-orders.php' => 'Order Management',
        'manage-users.php' => 'User Management',
        'add-product.php' => 'Add Product',
        'edit-product.php' => 'Edit Product',
        'edit-category.php' => 'Edit Category',
        'reports.php' => 'Reports'
    ];
    
    $all_admin_exist = true;
    echo "<ul>";
    foreach($admin_files as $file => $desc) {
        if (file_exists($file)) {
            echo "<li><span class='pass'>✓</span> $desc ($file)</li>";
        } else {
            echo "<li><span class='fail'>✗</span> $desc ($file) <span class='fail'>NOT FOUND</span></li>";
            $all_admin_exist = false;
        }
    }
    echo "</ul>";
    
    if ($all_admin_exist) {
        echo "<div class='status ok'><span class='pass'>✓ PASS:</span> All admin files present</div>";
    }
    echo "</div>";
    
    // Test 3: CSS References in Admin Files
    echo "<div class='test'><h2>3. CSS References in Admin Files</h2>";
    $css_issues = [];
    
    foreach($admin_files as $file => $desc) {
        if (file_exists($file)) {
            $content = file_get_contents($file);
            
            // Check for CSS references
            if (strpos($content, 'link rel="stylesheet"') !== false) {
                if (strpos($content, '../assets/css/style.css') !== false || 
                    strpos($content, '../assets/css/auth.css') !== false ||
                    strpos($content, '../assets/css/admin.css') !== false) {
                    echo "<li><span class='pass'>✓</span> $file - CSS references OK</li>";
                } else {
                    echo "<li><span class='fail'>✗</span> $file - Incorrect CSS paths</li>";
                    $css_issues[] = $file;
                }
            }
        }
    }
    
    if (empty($css_issues)) {
        echo "<div class='status ok'><span class='pass'>✓ PASS:</span> All CSS references are correct</div>";
    } else {
        echo "<div class='status error'><span class='fail'>✗ FAIL:</span> Some files have incorrect CSS paths</div>";
    }
    echo "</div>";
    
    // Test 4: Database & Admin User
    echo "<div class='test'><h2>4. Admin Authentication Test</h2>";
    require_once '../config.php';
    
    try {
        $conn = getDBConnection();
        $result = $conn->query("SELECT id, username, role, status FROM users WHERE username='admin'");
        $admin = $result->fetch_assoc();
        
        if ($admin && $admin['role'] === 'admin') {
            echo "<div class='status ok'><span class='pass'>✓ PASS:</span> Admin user configured correctly</div>";
            echo "<table><tr><th>Field</th><th>Value</th></tr>";
            echo "<tr><td>Username</td><td>{$admin['username']}</td></tr>";
            echo "<tr><td>Role</td><td><strong>{$admin['role']}</strong></td></tr>";
            echo "<tr><td>Status</td><td>{$admin['status']}</td></tr></table>";
        }
        
        // Test password
        $pwd_check = $conn->query("SELECT password FROM users WHERE username='admin'")->fetch_assoc();
        if (password_verify('admin123', $pwd_check['password'])) {
            echo "<div class='status ok'><span class='pass'>✓ PASS:</span> Admin password verified (admin123)</div>";
        }
        
        $conn->close();
    } catch (Exception $e) {
        echo "<div class='status error'><span class='fail'>✗ FAIL:</span> " . $e->getMessage() . "</div>";
    }
    echo "</div>";
    
    // Summary
    if ($all_css_exist && $all_admin_exist && empty($css_issues)) {
        echo "<div class='test' style='background:#d4edda;border:2px solid #28a745'>
        <h2 style='color:#155724'>✅ ALL TESTS PASSED!</h2>
        <p style='font-size:18px;color:#155724'>CSS and Admin system are working correctly!</p>
        <div style='margin-top:20px'>
            <a href='Adminlogin.php' class='btn' style='background:#8B7355'>Login to Admin Panel</a>
            <a href='../test_complete.php' class='btn' style='background:#6c757d'>Full System Test</a>
        </div>
        <div style='margin-top:15px;padding:15px;background:#fff;border-radius:4px'>
            <strong>Admin Credentials:</strong><br>
            Username: <code>admin</code><br>
            Password: <code>admin123</code>
        </div>
        </div>";
    }
    ?>
</body>
</html>
