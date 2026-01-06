<?php
require_once 'config.php';

// Redirect if already logged in
if (isset($_SESSION['user_id'])) {
    header("Location: index.php");
    exit();
}

$error = '';
$success = '';

// Handle registration form submission
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $name = mysqli_real_escape_string($conn, trim($_POST['name']));
    $email = mysqli_real_escape_string($conn, trim($_POST['email']));
    $password = $_POST['password'];
    $confirm_password = $_POST['confirm_password'];
    $agree_terms = isset($_POST['agree_terms']);
    
    // Validation
    if (empty($name) || empty($email) || empty($password) || empty($confirm_password)) {
        $error = "Please fill in all fields.";
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error = "Please enter a valid email address.";
    } elseif (strlen($password) < 6) {
        $error = "Password must be at least 6 characters long.";
    } elseif ($password !== $confirm_password) {
        $error = "Passwords do not match.";
    } elseif (!$agree_terms) {
        $error = "Please agree to the terms and conditions.";
    } else {
        // Check if email already exists
        $check_query = "SELECT id FROM users WHERE email = '$email' LIMIT 1";
        $check_result = mysqli_query($conn, $check_query);
        
        if (mysqli_num_rows($check_result) > 0) {
            $error = "Email already registered. Please <a href='login.php'>login</a> instead.";
        } else {
            // Hash password using PHP's built-in password_hash
            $hashed_password = password_hash($password, PASSWORD_DEFAULT);
            
            // Insert user into database
            $insert_query = "INSERT INTO users (name, email, password, created_at) VALUES ('$name', '$email', '$hashed_password', NOW())";
            
            if (mysqli_query($conn, $insert_query)) {
                $success = "Registration successful! Redirecting to login...";
                
                // Redirect after 2 seconds
                header("refresh:2;url=login.php");
            } else {
                $error = "Registration failed. Please try again.";
            }
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Register - Furnessence</title>
    
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
            <h1>Create Account</h1>
            <p>Join us and start shopping!</p>
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
                
                <!-- Full Name -->
                <div class="form-group">
                    <label for="name">
                        Full Name
                        <span class="required">*</span>
                    </label>
                    <div class="input-wrapper">
                        <input 
                            type="text" 
                            id="name" 
                            name="name" 
                            class="form-input" 
                            placeholder="Enter your full name"
                            value="<?php echo isset($_POST['name']) ? htmlspecialchars($_POST['name']) : ''; ?>"
                            required
                        >
                        <ion-icon name="person-outline"></ion-icon>
                    </div>
                </div>
                
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
                            value="<?php echo isset($_POST['email']) ? htmlspecialchars($_POST['email']) : ''; ?>"
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
                            placeholder="Create a password (min. 6 characters)"
                            required
                        >
                        <ion-icon name="lock-closed-outline"></ion-icon>
                        <button type="button" class="toggle-password" onclick="togglePassword('password', 'toggle-icon-1')">
                            <ion-icon name="eye-outline" id="toggle-icon-1"></ion-icon>
                        </button>
                    </div>
                </div>
                
                <!-- Confirm Password -->
                <div class="form-group">
                    <label for="confirm_password">
                        Confirm Password
                        <span class="required">*</span>
                    </label>
                    <div class="input-wrapper">
                        <input 
                            type="password" 
                            id="confirm_password" 
                            name="confirm_password" 
                            class="form-input" 
                            placeholder="Confirm your password"
                            required
                        >
                        <ion-icon name="lock-closed-outline"></ion-icon>
                        <button type="button" class="toggle-password" onclick="togglePassword('confirm_password', 'toggle-icon-2')">
                            <ion-icon name="eye-outline" id="toggle-icon-2"></ion-icon>
                        </button>
                    </div>
                </div>
                
                <!-- Terms & Conditions -->
                <div class="checkbox-group">
                    <input type="checkbox" id="agree_terms" name="agree_terms" required>
                    <label for="agree_terms">
                        I agree to the <a href="#" style="color: var(--medium-turquoise);">Terms & Conditions</a>
                    </label>
                </div>
                
                <!-- Submit Button -->
                <button type="submit" class="btn-submit">
                    Create Account
                </button>
                
            </form>
            
            <!-- Divider -->
            <div class="divider">
                <span>or sign up with</span>
            </div>
            
            <!-- Social Login -->
            <div class="social-login">
                <button type="button" class="social-btn google" onclick="alert('Google signup coming soon!')">
                    <ion-icon name="logo-google"></ion-icon>
                    Google
                </button>
                <button type="button" class="social-btn facebook" onclick="alert('Facebook signup coming soon!')">
                    <ion-icon name="logo-facebook"></ion-icon>
                    Facebook
                </button>
            </div>
            
        </div>
        
        <!-- Auth Footer -->
        <div class="auth-footer">
            <p>Already have an account? <a href="login.php">Login here</a></p>
        </div>
        
    </div>
    
    <script src="./assests/js/auth.js"></script>
    
</body>
</html>
