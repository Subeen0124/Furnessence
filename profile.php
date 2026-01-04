<?php
require_once 'config.php';

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

$user_id = $_SESSION['user_id'];

// Get user details
$query = "SELECT * FROM users WHERE id = $user_id LIMIT 1";
$result = mysqli_query($conn, $query);
$user = mysqli_fetch_assoc($result);

$success = '';
$error = '';

// Handle profile update
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['update_profile'])) {
    $name = mysqli_real_escape_string($conn, trim($_POST['name']));
    $email = mysqli_real_escape_string($conn, trim($_POST['email']));
    
    if (empty($name) || empty($email)) {
        $error = "Name and email are required.";
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error = "Please enter a valid email address.";
    } else {
        // Check if email is already taken by another user
        $check_email = "SELECT id FROM users WHERE email = '$email' AND id != $user_id LIMIT 1";
        $email_result = mysqli_query($conn, $check_email);
        
        if (mysqli_num_rows($email_result) > 0) {
            $error = "Email already taken by another user.";
        } else {
            $update_query = "UPDATE users SET name = '$name', email = '$email' WHERE id = $user_id";
            
            if (mysqli_query($conn, $update_query)) {
                $_SESSION['user_name'] = $name;
                $_SESSION['user_email'] = $email;
                $success = "Profile updated successfully!";
                $user['name'] = $name;
                $user['email'] = $email;
            } else {
                $error = "Failed to update profile.";
            }
        }
    }
}

// Handle password change
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['change_password'])) {
    $current_password = $_POST['current_password'];
    $new_password = $_POST['new_password'];
    $confirm_password = $_POST['confirm_password'];
    
    if (empty($current_password) || empty($new_password) || empty($confirm_password)) {
        $error = "All password fields are required.";
    } elseif (!password_verify($current_password, $user['password'])) {
        $error = "Current password is incorrect.";
    } elseif (strlen($new_password) < 6) {
        $error = "New password must be at least 6 characters.";
    } elseif ($new_password !== $confirm_password) {
        $error = "New passwords do not match.";
    } else {
        $hashed_password = password_hash($new_password, PASSWORD_DEFAULT);
        $update_pass = "UPDATE users SET password = '$hashed_password' WHERE id = $user_id";
        
        if (mysqli_query($conn, $update_pass)) {
            $success = "Password changed successfully!";
        } else {
            $error = "Failed to change password.";
        }
    }
}

// Get wishlist count
$wishlist_query = "SELECT COUNT(*) as count FROM wishlist WHERE user_id = $user_id";
$wishlist_result = mysqli_query($conn, $wishlist_query);
$wishlist_count = mysqli_fetch_assoc($wishlist_result)['count'];

// Get cart count
$cart_query = "SELECT COUNT(*) as count FROM cart WHERE user_id = $user_id";
$cart_result = mysqli_query($conn, $cart_query);
$cart_count = mysqli_fetch_assoc($cart_result)['count'];
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>My Profile - Furnessence</title>
    
    <link rel="shortcut icon" href="./favicon.svg" type="image/svg+xml">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Jost:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="./assests/css/style.css">
    <link rel="stylesheet" href="./assests/css/auth.css">
    <link rel="stylesheet" href="./assests/css/profile.css">
    
    <script type="module" src="https://unpkg.com/ionicons@7.1.0/dist/ionicons/ionicons.esm.js"></script>
    <script nomodule src="https://unpkg.com/ionicons@7.1.0/dist/ionicons/ionicons.js"></script>
</head>
<body>
    
    <div class="profile-container">
        
        <div class="profile-header">
            <h1>Welcome, <?php echo htmlspecialchars($user['name']); ?>!</h1>
            <p><?php echo htmlspecialchars($user['email']); ?></p>
            
            <div class="profile-stats">
                <div class="stat-item">
                    <div class="stat-number"><?php echo $wishlist_count; ?></div>
                    <div class="stat-label">Wishlist Items</div>
                </div>
                <div class="stat-item">
                    <div class="stat-number"><?php echo $cart_count; ?></div>
                    <div class="stat-label">Cart Items</div>
                </div>
            </div>
        </div>
        
        <?php if (!empty($error)): ?>
            <div class="alert alert-error" style="margin-bottom: 20px;">
                <ion-icon name="alert-circle"></ion-icon>
                <span><?php echo $error; ?></span>
            </div>
        <?php endif; ?>
        
        <?php if (!empty($success)): ?>
            <div class="alert alert-success" style="margin-bottom: 20px;">
                <ion-icon name="checkmark-circle"></ion-icon>
                <span><?php echo $success; ?></span>
            </div>
        <?php endif; ?>
        
        <div class="profile-content">
            
            <div class="profile-card">
                <h2>Quick Links</h2>
                <div class="quick-links">
                    <a href="index.php" class="quick-link-btn">
                        <ion-icon name="home"></ion-icon>
                        <span>Home</span>
                    </a>
                    <a href="wishlist.php" class="quick-link-btn">
                        <ion-icon name="heart"></ion-icon>
                        <span>Wishlist</span>
                    </a>
                    <a href="cart.php" class="quick-link-btn">
                        <ion-icon name="cart"></ion-icon>
                        <span>Cart</span>
                    </a>
                    <a href="logout.php" class="quick-link-btn">
                        <ion-icon name="log-out"></ion-icon>
                        <span>Logout</span>
                    </a>
                </div>
            </div>
            
            <div class="profile-card">
                <h2>Update Profile</h2>
                <form action="" method="POST" class="auth-form">
                    <div class="form-group">
                        <label for="name">Full Name</label>
                        <div class="input-wrapper">
                            <input type="text" id="name" name="name" class="form-input" value="<?php echo htmlspecialchars($user['name']); ?>" required>
                            <ion-icon name="person-outline"></ion-icon>
                        </div>
                    </div>
                    
                    <div class="form-group">
                        <label for="email">Email Address</label>
                        <div class="input-wrapper">
                            <input type="email" id="email" name="email" class="form-input" value="<?php echo htmlspecialchars($user['email']); ?>" required>
                            <ion-icon name="mail-outline"></ion-icon>
                        </div>
                    </div>
                    
                    <button type="submit" name="update_profile" class="btn-submit">Update Profile</button>
                </form>
            </div>
            
            <div class="profile-card">
                <h2>Change Password</h2>
                <form action="" method="POST" class="auth-form">
                    <div class="form-group">
                        <label for="current_password">Current Password</label>
                        <div class="input-wrapper">
                            <input type="password" id="current_password" name="current_password" class="form-input" required>
                            <ion-icon name="lock-closed-outline"></ion-icon>
                        </div>
                    </div>
                    
                    <div class="form-group">
                        <label for="new_password">New Password</label>
                        <div class="input-wrapper">
                            <input type="password" id="new_password" name="new_password" class="form-input" required>
                            <ion-icon name="lock-closed-outline"></ion-icon>
                        </div>
                    </div>
                    
                    <div class="form-group">
                        <label for="confirm_password">Confirm New Password</label>
                        <div class="input-wrapper">
                            <input type="password" id="confirm_password" name="confirm_password" class="form-input" required>
                            <ion-icon name="lock-closed-outline"></ion-icon>
                        </div>
                    </div>
                    
                    <button type="submit" name="change_password" class="btn-submit">Change Password</button>
                </form>
            </div>
            
        </div>
        
    </div>
    
</body>
</html>
