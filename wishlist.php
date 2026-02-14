<?php
require_once 'config.php';

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}
$user_id = $_SESSION['user_id'];
$success = '';
$error = '';
// Handle remove from wishlist
if (isset($_GET['remove'])) {
    $wishlist_id = intval($_GET['remove']);
    $delete_stmt = mysqli_prepare($conn, "DELETE FROM wishlist WHERE id = ? AND user_id = ?");
    mysqli_stmt_bind_param($delete_stmt, "ii", $wishlist_id, $user_id);
    
    if (mysqli_stmt_execute($delete_stmt)) {
        $success = "Item removed from wishlist!";
    } else {
        $error = "Failed to remove item.";
    }
    mysqli_stmt_close($delete_stmt);
}

// Handle move to cart
if (isset($_GET['move_to_cart'])) {
    $wishlist_id = intval($_GET['move_to_cart']);
    
    // Get wishlist item
    $item_stmt = mysqli_prepare($conn, "SELECT * FROM wishlist WHERE id = ? AND user_id = ? LIMIT 1");
    mysqli_stmt_bind_param($item_stmt, "ii", $wishlist_id, $user_id);
    mysqli_stmt_execute($item_stmt);
    $item_result = mysqli_stmt_get_result($item_stmt);
    if ($item_result && mysqli_num_rows($item_result) > 0) {
        $item = mysqli_fetch_assoc($item_result);
        mysqli_stmt_close($item_stmt);
        
    // Check if already in cart
    $check_stmt = mysqli_prepare($conn, "SELECT id FROM cart WHERE user_id = ? AND product_id = ? LIMIT 1");
    mysqli_stmt_bind_param($check_stmt, "ii", $user_id, $item['product_id']);
    mysqli_stmt_execute($check_stmt);
    $cart_check = mysqli_stmt_get_result($check_stmt);
    mysqli_stmt_close($check_stmt);
        if (mysqli_num_rows($cart_check) > 0) {
            // Update quantity if already in cart
            $update_stmt = mysqli_prepare($conn, "UPDATE cart SET quantity = quantity + 1 WHERE user_id = ? AND product_id = ?");
            mysqli_stmt_bind_param($update_stmt, "ii", $user_id, $item['product_id']);
            mysqli_stmt_execute($update_stmt);
            mysqli_stmt_close($update_stmt);
        } else {
            // Add to cart
            $add_stmt = mysqli_prepare($conn, "INSERT INTO cart (user_id, product_id, product_name, product_price, product_image, quantity) VALUES (?, ?, ?, ?, ?, 1)");
            mysqli_stmt_bind_param($add_stmt, "iisds", $user_id, $item['product_id'], $item['product_name'], $item['product_price'], $item['product_image']);
            mysqli_stmt_execute($add_stmt);
            mysqli_stmt_close($add_stmt);
        }
    // Remove from wishlist
    $del_stmt = mysqli_prepare($conn, "DELETE FROM wishlist WHERE id = ?");
    mysqli_stmt_bind_param($del_stmt, "i", $wishlist_id);
    mysqli_stmt_execute($del_stmt);
    mysqli_stmt_close($del_stmt);
        $success = "Item moved to cart!";
    } else {
        mysqli_stmt_close($item_stmt);
    }
}

// Get all wishlist items
$wishlist_stmt = mysqli_prepare($conn, "SELECT * FROM wishlist WHERE user_id = ? ORDER BY created_at DESC");
mysqli_stmt_bind_param($wishlist_stmt, "i", $user_id);
mysqli_stmt_execute($wishlist_stmt);
$wishlist_result = mysqli_stmt_get_result($wishlist_stmt);

// Get cart count for header
$cart_stmt = mysqli_prepare($conn, "SELECT COUNT(*) as count FROM cart WHERE user_id = ?");
mysqli_stmt_bind_param($cart_stmt, "i", $user_id);
mysqli_stmt_execute($cart_stmt);
$cart_result = mysqli_stmt_get_result($cart_stmt);
$cart_count = mysqli_fetch_assoc($cart_result)['count'];
mysqli_stmt_close($cart_stmt);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>My Wishlist - Furnessence</title>
    
    <link rel="shortcut icon" href="./favicon.svg" type="image/svg+xml">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Jost:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="./assests/css/style.css">
    <link rel="stylesheet" href="./assests/css/wishlist.css?v=2.0">
    
    <script type="module" src="https://unpkg.com/ionicons@7.1.0/dist/ionicons/ionicons.esm.js"></script>
    <script nomodule src="https://unpkg.com/ionicons@7.1.0/dist/ionicons/ionicons.js"></script>
    </head>
<body>
    
    <div class="wishlist-container">
        <a href="index.php" class="back-link">
            <ion-icon name="arrow-back"></ion-icon>
            Back to Shop
    </a>
        
    <div class="page-header">
            <h1>My Wishlist</h1>
            <p><?php echo mysqli_num_rows($wishlist_result); ?> item(s) in your wishlist</p>
        </div>
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
        <?php if (mysqli_num_rows($wishlist_result) > 0): ?>
            <div class="wishlist-grid">
                <?php while ($item = mysqli_fetch_assoc($wishlist_result)): ?>
                    <div class="wishlist-item">
                        
                        <div class="item-image-container">
                       <img src="<?php echo htmlspecialchars($item['product_image']); ?>" 
                           alt="<?php echo htmlspecialchars($item['product_name']); ?>" 
                           class="item-image">
                            
                            <div class="item-actions">
                                <a href="?move_to_cart=<?php echo $item['id']; ?>" 
                                   class="action-btn btn-cart" 
                                   title="Add to Cart">
                                    <ion-icon name="bag-handle-outline"></ion-icon>
                                          </a>
                                          <a href="?remove=<?php echo $item['id']; ?>" 
                                              class="action-btn btn-remove" 
                                   onclick="return confirm('Remove this item from wishlist?')"
                                   title="Remove">
                                    <ion-icon name="trash-outline"></ion-icon>
                                </a>
                            </div>
                        </div>
                        <div class="item-details">
                            <h3><?php echo htmlspecialchars($item['product_name']); ?></h3>
                            <div class="item-price">Rs <?php echo number_format($item['product_price'], 2); ?></div>
                        </div>
                    </div>
                <?php endwhile; ?>
            </div>
        <?php else: ?>
            <div class="empty-wishlist">
                <ion-icon name="heart-dislike"></ion-icon>
                <h2>Your Wishlist is Empty</h2>
                <p>Start adding items you love to your wishlist!</p>
                <a href="index.php" class="shop-btn">Start Shopping</a>
            </div>
        <?php endif; ?>
    </div>
    
