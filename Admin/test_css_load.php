<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>CSS Load Test</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            padding: 20px;
            background: #f5f5f5;
        }
        .test-section {
            background: white;
            padding: 20px;
            margin: 10px 0;
            border-radius: 8px;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
        }
        .success { color: green; font-weight: bold; }
        .error { color: red; font-weight: bold; }
        pre {
            background: #f0f0f0;
            padding: 10px;
            border-radius: 4px;
            overflow-x: auto;
        }
    </style>
</head>
<body>
    <h1>CSS Loading Diagnostic Test</h1>
    
    <div class="test-section">
        <h2>1. CSS File Paths Test</h2>
        <?php
        $cssFiles = [
            '../assets/css/style.css',
            '../assets/css/auth.css',
            '../assets/css/admin.css'
        ];
        
        foreach ($cssFiles as $file) {
            $fullPath = __DIR__ . '/' . $file;
            if (file_exists($fullPath)) {
                $size = filesize($fullPath);
                echo "<p class='success'>✓ $file exists ($size bytes)</p>";
            } else {
                echo "<p class='error'>✗ $file NOT FOUND</p>";
            }
        }
        ?>
    </div>
    
    <div class="test-section">
        <h2>2. HTTP Access Test</h2>
        <p>Open browser console and check for CSS 404 errors:</p>
        <link rel="stylesheet" href="../assets/css/style.css">
        <link rel="stylesheet" href="../assets/css/auth.css">
        <link rel="stylesheet" href="../assets/css/admin.css">
        <p><strong>Expected behavior:</strong> No 404 errors in console</p>
    </div>
    
    <div class="test-section">
        <h2>3. CSS Sample from auth.css</h2>
        <?php
        $authCssPath = __DIR__ . '/../assets/css/auth.css';
        if (file_exists($authCssPath)) {
            $content = file_get_contents($authCssPath);
            $lines = explode("\n", $content);
            $sample = array_slice($lines, 0, 20);
            echo "<pre>" . htmlspecialchars(implode("\n", $sample)) . "</pre>";
        }
        ?>
    </div>
    
    <div class="test-section">
        <h2>4. Test Styled Elements (from auth.css)</h2>
        <div style="max-width: 400px; margin: 0 auto;">
            <div class="auth-container">
                <h2>Sample Login Form</h2>
                <form class="auth-form">
                    <div class="form-group">
                        <label>Username</label>
                        <input type="text" class="form-control" placeholder="Enter username">
                    </div>
                    <div class="form-group">
                        <label>Password</label>
                        <input type="password" class="form-control" placeholder="Enter password">
                    </div>
                    <button type="submit" class="btn btn-primary btn-block">Login</button>
                </form>
            </div>
        </div>
        <p><strong>Expected:</strong> If CSS is working, form should be styled with proper colors and spacing</p>
    </div>
    
    <div class="test-section">
        <h2>5. Direct CSS Links</h2>
        <p>Click these links to verify CSS loads directly:</p>
        <ul>
            <li><a href="../assets/css/style.css" target="_blank">style.css</a></li>
            <li><a href="../assets/css/auth.css" target="_blank">auth.css</a></li>
            <li><a href="../assets/css/admin.css" target="_blank">admin.css</a></li>
        </ul>
    </div>
    
    <div class="test-section">
        <h2>6. Browser Console Check</h2>
        <p>Press <strong>F12</strong> to open Developer Tools and check:</p>
        <ul>
            <li><strong>Console Tab:</strong> Look for CSS loading errors (red text)</li>
            <li><strong>Network Tab:</strong> Check if CSS files return 200 (success) or 404 (not found)</li>
            <li><strong>Elements Tab:</strong> Inspect elements to see if CSS rules are applied</li>
        </ul>
    </div>
    
    <script>
        // JavaScript to check CSS loading
        console.log('=== CSS Load Test ===');
        const links = document.querySelectorAll('link[rel="stylesheet"]');
        links.forEach(link => {
            link.onload = () => console.log('✓ Loaded:', link.href);
            link.onerror = () => console.error('✗ Failed to load:', link.href);
        });
    </script>
</body>
</html>
