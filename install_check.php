<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Furnessence - Installation Checker</title>
    <link rel="stylesheet" href="./assests/css/install_check.css">
</head>
<body>
    <div class="container">
        <h1>🎉 Furnessence Installation</h1>
        <p class="subtitle">System Requirements Checker</p>
        
        <div class="section">
            <div class="section-title">Environment Check</div>
            
            <?php
            // Check PHP Version
            $php_version = phpversion();
            $php_ok = version_compare($php_version, '7.4', '>=');
            ?>
            <div class="check-item <?php echo $php_ok ? 'success' : 'error'; ?>">
                <div class="icon"><?php echo $php_ok ? '✓' : '✗'; ?></div>
                <div class="text">
                    <div class="label">PHP Version</div>
                    <div class="detail">Current: <?php echo $php_version; ?> (Required: 7.4+)</div>
                </div>
            </div>
            
            <?php
            // Check MySQLi Extension
            $mysqli_ok = extension_loaded('mysqli');
            ?>
            <div class="check-item <?php echo $mysqli_ok ? 'success' : 'error'; ?>">
                <div class="icon"><?php echo $mysqli_ok ? '✓' : '✗'; ?></div>
                <div class="text">
                    <div class="label">MySQLi Extension</div>
                    <div class="detail"><?php echo $mysqli_ok ? 'Enabled' : 'Not found - Please enable in php.ini'; ?></div>
                </div>
            </div>
            
            <?php
            // Check Database Connection
            require_once 'config.php';
            $db_ok = isset($conn) && $conn !== false;
            ?>
            <div class="check-item <?php echo $db_ok ? 'success' : 'error'; ?>">
                <div class="icon"><?php echo $db_ok ? '✓' : '✗'; ?></div>
                <div class="text">
                    <div class="label">Database Connection</div>
                    <div class="detail"><?php echo $db_ok ? 'Connected to: ' . DB_NAME : 'Failed - Check config.php settings'; ?></div>
                </div>
            </div>
            
            <?php
            // Check if tables exist
            $tables_ok = false;
            if ($db_ok) {
                $required_tables = ['admins', 'products', 'categories', 'users', 'orders', 'cart', 'wishlist'];
                $tables_found = 0;
                foreach ($required_tables as $table) {
                    $result = mysqli_query($conn, "SHOW TABLES LIKE '$table'");
                    if ($result && mysqli_num_rows($result) > 0) {
                        $tables_found++;
                    }
                }
                $tables_ok = ($tables_found === count($required_tables));
            }
            ?>
            <div class="check-item <?php echo $tables_ok ? 'success' : 'error'; ?>">
                <div class="icon"><?php echo $tables_ok ? '✓' : '✗'; ?></div>
                <div class="text">
                    <div class="label">Database Tables</div>
                    <div class="detail"><?php echo $tables_ok ? 'All tables created' : 'Missing tables - Import database.sql'; ?></div>
                </div>
            </div>
            
            <?php
            // Check upload directory
            $upload_dir = 'assests/images/products/';
            $dir_exists = is_dir($upload_dir);
            $dir_writable = $dir_exists && is_writable($upload_dir);
            ?>
            <div class="check-item <?php echo $dir_writable ? 'success' : 'error'; ?>">
                <div class="icon"><?php echo $dir_writable ? '✓' : '✗'; ?></div>
                <div class="text">
                    <div class="label">Upload Directory</div>
                    <div class="detail"><?php echo $dir_writable ? 'Ready: ' . $upload_dir : 'Not writable - Check permissions'; ?></div>
                </div>
            </div>
            
            <?php
            // Check admin user
            $admin_ok = false;
            if ($db_ok && $tables_ok) {
                $result = mysqli_query($conn, "SELECT COUNT(*) as count FROM admins");
                if ($result) {
                    $row = mysqli_fetch_assoc($result);
                    $admin_ok = $row['count'] > 0;
                }
            }
            ?>
            <div class="check-item <?php echo $admin_ok ? 'success' : 'error'; ?>">
                <div class="icon"><?php echo $admin_ok ? '✓' : '✗'; ?></div>
                <div class="text">
                    <div class="label">Admin Account</div>
                    <div class="detail"><?php echo $admin_ok ? 'Admin user exists' : 'No admin user - Import database.sql'; ?></div>
                </div>
            </div>
        </div>
        
        <?php if ($db_ok && $tables_ok && $admin_ok): ?>
        <div class="info-box">
            <h3>✅ Installation Complete!</h3>
            <p><strong>Default Admin Credentials:</strong></p>
            <p>Username: <code>admin</code></p>
            <p>Password: <code>admin123</code></p>
            <p><strong>⚠️ Important:</strong> Change the default password after first login for security!</p>
        </div>
        <?php else: ?>
        <div class="info-box">
            <h3>⚙️ Setup Instructions</h3>
            <p>1. Open phpMyAdmin: <code>http://localhost/phpmyadmin</code></p>
            <p>2. Create or select database: <code><?php echo DB_NAME; ?></code></p>
            <p>3. Import file: <code>database.sql</code></p>
            <p>4. Refresh this page to verify</p>
        </div>
        <?php endif; ?>
        
        <div class="actions">
            <?php if ($db_ok && $tables_ok && $admin_ok): ?>
                <a href="Admin/Adminlogin.php" class="btn btn-primary">Go to Admin Panel →</a>
                <a href="index.php" class="btn btn-secondary">View Website →</a>
            <?php endif; ?>
            <a href="javascript:location.reload()" class="btn btn-secondary">Refresh Check</a>
        </div>
        
        <div class="section">
            <div class="section-title">Quick Links</div>
            <div class="quick-links">
                <a href="ADMIN_QUICK_START.md">📖 Quick Start Guide</a>
                <a href="Admin/README.md">📚 Admin Documentation</a>
                <a href="PROJECT_COMPLETE.md">✨ Project Summary</a>
            </div>
        </div>
    </div>
</body>
</html>
