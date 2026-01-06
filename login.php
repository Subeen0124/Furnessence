<?php
require_once 'config.php';

// Redirect if already logged in
if (isset($_SESSION['user_id'])) {
    header("Location: index.php");
    exit();
}

$error = '';
$success = '';

// Handle login form submission
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $email = mysqli_real_escape_string($conn, trim($_POST['email']));
    $password = $_POST['password'];
    $remember = isset($_POST['remember']);
    
    if (empty($email) || empty($password)) {
        $error = "Please fill in all fields.";
    } else {
        // Check if user exists
        $query = "SELECT * FROM users WHERE email = '$email' LIMIT 1";
        $result = mysqli_query($conn, $query);
        
        if ($result && mysqli_num_rows($result) > 0) {
            $user = mysqli_fetch_assoc($result);
            
            // Verify password using PHP's built-in password_verify
            if (password_verify($password, $user['password'])) {
                // Set session variables
                $_SESSION['user_id'] = $user['id'];
                $_SESSION['user_name'] = $user['name'];
                $_SESSION['user_email'] = $user['email'];
                
                // Merge session cart with database cart
                if (isset($_SESSION['cart']) && !empty($_SESSION['cart'])) {
                    $user_id = $user['id'];
                    
                    foreach ($_SESSION['cart'] as $item) {
                        $product_id = intval($item['product_id']);
                        $product_name = mysqli_real_escape_string($conn, $item['product_name']);
                        $product_price = floatval($item['product_price']);
                        $product_image = mysqli_real_escape_string($conn, $item['product_image']);
                        $quantity = intval($item['quantity']);
                        
                        // Check if product already in database cart
                        $check_query = "SELECT id, quantity FROM cart WHERE user_id = $user_id AND product_id = $product_id LIMIT 1";
                        $check_result = mysqli_query($conn, $check_query);
                        
                        if (mysqli_num_rows($check_result) > 0) {
                            // Update quantity
                            $existing = mysqli_fetch_assoc($check_result);
                            $new_quantity = $existing['quantity'] + $quantity;
                            $update_query = "UPDATE cart SET quantity = $new_quantity WHERE id = {$existing['id']}";
                            mysqli_query($conn, $update_query);
                        } else {
                            // Insert new item
                            $insert_query = "INSERT INTO cart (user_id, product_id, product_name, product_price, product_image, quantity) VALUES ($user_id, $product_id, '$product_name', $product_price, '$product_image', $quantity)";
                            mysqli_query($conn, $insert_query);
                        }
                    }
                    
                    // Clear session cart after merging
                    unset($_SESSION['cart']);
                }
                
                // Set remember me cookie if checked
                if ($remember) {
                    setcookie('user_email', $email, time() + (86400 * 30), "/"); // 30 days
                }
                
                // Redirect to home page
                header("Location: index.php");
                exit();
            } else {
                $error = "Invalid email or password.";
            }
        } else {
            $error = "Invalid email or password.";
        }
    }
}

// Pre-fill email if remember me cookie exists
$remembered_email = isset($_COOKIE['user_email']) ? $_COOKIE['user_email'] : '';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - Furnessence</title>
    
    <!-- Favicon -->
    <link rel="shortcut icon" href="./favicon.svg" type="image/svg+xml">
    
    <!-- Google Font -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Jost:wght@400;500;600;700&display=swap" rel="stylesheet">
    
    <!-- Auth CSS -->
    <link rel="stylesheet" href="./assests/css/auth.css">
    
    <!-- Ionicons -->
    <script type="module" src="https://unpkg.com/ionicons@7.1.0/dist/ionicons/ionicons.esm.js"></script>
    <script nomodule src="https://unpkg.com/ionicons@7.1.0/dist/ionicons/ionicons.js"></script>
</head>
<body>
    
    <div class="auth-container">
        
        <!-- Auth Header -->
        <div class="auth-header">
            <a href="index.php" class="logo">Furnessence</a>
            <h1>Welcome Back!</h1>
            <p>Login to access your account</p>
        </div>
        
        <!-- Auth Body -->
        <div class="auth-body">
            
            <?php if (!empty($error)): ?>
                <div class="alert alert-error">
                    <ion-icon name="alert-circle"></ion-icon>
                    <span><?php echo $error; ?></span>
                </div>
            <?php endif; ?>
            
            <?php if (!empty($success)): ?>
                <div class="alert alert-success">
                    <ion-icon name="checkmark-circle"></ion-icon>
                    <span><?php echo $success; ?></span>
                </div>
            <?php endif; ?>
            
            <form action="" method="POST" class="auth-form">
                
                <!-- Email -->
                <div class="form-group">
                    <label for="email">
                        Email Address
                        <span class="required">*</span>
                    </label>
                    <div class="input-wrapper">
                        <input 
                            type="email" 
                            id="email" 
                            name="email" 
                            class="form-input" 
                            placeholder="Enter your email"
                            value="<?php echo htmlspecialchars($remembered_email); ?>"
                            required
                        >
                        <ion-icon name="mail-outline"></ion-icon>
                    </div>
                </div>
                
                <!-- Password -->
                <div class="form-group">
                    <label for="password">
                        Password
                        <span class="required">*</span>
                    </label>
                    <div class="input-wrapper">
                        <input 
                            type="password" 
                            id="password" 
                            name="password" 
                            class="form-input" 
                            placeholder="Enter your password"
                            required
                        >
                        <ion-icon name="lock-closed-outline"></ion-icon>
                        <button type="button" class="toggle-password" onclick="togglePassword()">
                            <ion-icon name="eye-outline" id="toggle-icon"></ion-icon>
                        </button>
                    </div>
                </div>
                
                <!-- Remember Me & Forgot Password -->
                <div style="display: flex; justify-content: space-between; align-items: center;">
                    <div class="checkbox-group">
                        <input type="checkbox" id="remember" name="remember">
                        <label for="remember">Remember me</label>
                    </div>
                    <div class="forgot-password">
                        <a href="#" onclick="alert('Password reset functionality coming soon!'); return false;">Forgot Password?</a>
                    </div>
                </div>
                
                <!-- Submit Button -->
                <button type="submit" class="btn-submit">
                    Login
                </button>
                
            </form>
            
            <!-- Divider -->
            <div class="divider">
                <span>or continue with</span>
            </div>
            
            <!-- Social Login -->
            <div class="social-login">
                <button type="button" class="social-btn google" onclick="alert('Google login coming soon!')">
                    <ion-icon name="logo-google"></ion-icon>
                    Google
                </button>
                <button type="button" class="social-btn facebook" onclick="alert('Facebook login coming soon!')">
                    <ion-icon name="logo-facebook"></ion-icon>
                    Facebook
                </button>
            </div>
            
        </div>
        
        <!-- Auth Footer -->
        <div class="auth-footer">
            <p>Don't have an account? <a href="registration.php">Sign up here</a></p>
        </div>
        
    </div>
    
    <script src="./assests/js/auth.js"></script>
    
</body>
</html>
