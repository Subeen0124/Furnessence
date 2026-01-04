<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Furnessence - Installation Checker</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 20px;
        }
        
        .container {
            background: white;
            border-radius: 15px;
            box-shadow: 0 20px 60px rgba(0, 0, 0, 0.3);
            max-width: 700px;
            width: 100%;
            padding: 40px;
        }
        
        h1 {
            color: #2c3e50;
            margin-bottom: 10px;
            font-size: 28px;
        }
        
        .subtitle {
            color: #7f8c8d;
            margin-bottom: 30px;
        }
        
        .check-item {
            display: flex;
            align-items: center;
            padding: 15px;
            margin-bottom: 10px;
            border-radius: 8px;
            background: #f8f9fa;
            border-left: 4px solid #ddd;
        }
        
        .check-item.success {
            background: #d4edda;
            border-left-color: #28a745;
        }
        
        .check-item.error {
            background: #f8d7da;
            border-left-color: #dc3545;
        }
        
        .check-item .icon {
            font-size: 24px;
            margin-right: 15px;
            width: 30px;
        }
        
        .check-item.success .icon {
            color: #28a745;
        }
        
        .check-item.error .icon {
            color: #dc3545;
        }
        
        .check-item .text {
            flex: 1;
        }
        
        .check-item .label {
            font-weight: 600;
            color: #2c3e50;
            margin-bottom: 3px;
        }
        
        .check-item .detail {
            font-size: 13px;
            color: #6c757d;
        }
        
        .actions {
            margin-top: 30px;
            display: flex;
            gap: 10px;
            flex-wrap: wrap;
        }
        
        .btn {
            padding: 12px 24px;
            border-radius: 8px;
            text-decoration: none;
            font-weight: 600;
            display: inline-block;
            transition: all 0.3s;
        }
        
        .btn-primary {
            background: #667eea;
            color: white;
        }
        
        .btn-primary:hover {
            background: #5568d3;
            transform: translateY(-2px);
        }
        
        .btn-secondary {
            background: #6c757d;
            color: white;
        }
        
        .btn-secondary:hover {
            background: #5a6268;
        }
        
        .section {
            margin: 30px 0;
        }
        
        .section-title {
            font-size: 18px;
            font-weight: 600;
            color: #2c3e50;
            margin-bottom: 15px;
            padding-bottom: 10px;
            border-bottom: 2px solid #e9ecef;
        }
        
        .info-box {
            background: #e7f3ff;
            border: 1px solid #b3d9ff;
            border-radius: 8px;
            padding: 15px;
            margin: 20px 0;
        }
        
        .info-box h3 {
            color: #004085;
            margin-bottom: 10px;
            font-size: 16px;
        }
        
        .info-box p {
            color: #004085;
            line-height: 1.6;
            margin-bottom: 8px;
        }
        
        .info-box code {
            background: white;
            padding: 2px 6px;
            border-radius: 3px;
            font-family: 'Courier New', monospace;
            color: #e83e8c;
        }
    </style>
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
            <div style="display: flex; flex-direction: column; gap: 10px; margin-top: 15px;">
                <a href="ADMIN_QUICK_START.md" style="color: #667eea; text-decoration: none;">📖 Quick Start Guide</a>
                <a href="Admin/README.md" style="color: #667eea; text-decoration: none;">📚 Admin Documentation</a>
                <a href="PROJECT_COMPLETE.md" style="color: #667eea; text-decoration: none;">✨ Project Summary</a>
            </div>
        </div>
    </div>
</body>
</html>
