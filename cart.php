<?php
require_once 'config.php';

$is_logged_in = isset($_SESSION['user_id']);
$success = '';
$error = '';

if ($is_logged_in) {
    $user_id = $_SESSION['user_id'];
    
    // Handle remove from cart
    if (isset($_GET['remove'])) {
        $cart_id = intval($_GET['remove']);
        $delete_query = "DELETE FROM cart WHERE id = $cart_id AND user_id = $user_id";
        
        if (mysqli_query($conn, $delete_query)) {
            $success = "Item removed from cart!";
        } else {
            $error = "Failed to remove item.";
        }
    }

    // Handle update quantity
    if (isset($_POST['update_quantity'])) {
        $cart_id = intval($_POST['cart_id']);
        $quantity = intval($_POST['quantity']);
        
        if ($quantity > 0) {
            $update_query = "UPDATE cart SET quantity = $quantity WHERE id = $cart_id AND user_id = $user_id";
            mysqli_query($conn, $update_query);
        }
    }

    // Get all cart items from database
    $cart_query = "SELECT * FROM cart WHERE user_id = $user_id ORDER BY created_at DESC";
    $cart_result = mysqli_query($conn, $cart_query);

    // Calculate total
    $total = 0;
    $cart_items = [];
    while ($item = mysqli_fetch_assoc($cart_result)) {
        $cart_items[] = $item;
        $total += $item['product_price'] * $item['quantity'];
    }

    // Get wishlist count for header
    $wishlist_query = "SELECT COUNT(*) as count FROM wishlist WHERE user_id = $user_id";
    $wishlist_result = mysqli_query($conn, $wishlist_query);
    $wishlist_count = mysqli_fetch_assoc($wishlist_result)['count'];
} else {
    // Handle session cart for guest users
    $cart_items = [];
    $total = 0;
    $wishlist_count = 0;
    
    // Handle remove from cart
    if (isset($_GET['remove'])) {
        $product_id = intval($_GET['remove']);
        if (isset($_SESSION['cart'])) {
            foreach ($_SESSION['cart'] as $key => $item) {
                if ($item['product_id'] == $product_id) {
                    unset($_SESSION['cart'][$key]);
                    $_SESSION['cart'] = array_values($_SESSION['cart']); // Re-index array
                    $success = "Item removed from cart!";
                    break;
                }
            }
        }
    }
    
    // Handle update quantity
    if (isset($_POST['update_quantity'])) {
        $product_id = intval($_POST['product_id']);
        $quantity = intval($_POST['quantity']);
        
        if (isset($_SESSION['cart']) && $quantity > 0) {
            foreach ($_SESSION['cart'] as &$item) {
                if ($item['product_id'] == $product_id) {
                    $item['quantity'] = $quantity;
                    break;
                }
            }
        }
    }
    
    // Get cart items from session
    if (isset($_SESSION['cart']) && !empty($_SESSION['cart'])) {
        $cart_items = $_SESSION['cart'];
        foreach ($cart_items as $item) {
            $total += $item['product_price'] * $item['quantity'];
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Shopping Cart - Furnessence</title>
    
    <link rel="shortcut icon" href="./favicon.svg" type="image/svg+xml">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Jost:wght@400;500;600;700&display=swap" rel="stylesheet">
    <title>Shopping Cart - Furnessence</title>
    
    <link rel="shortcut icon" href="./favicon.svg" type="image/svg+xml">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Jost:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="./assests/css/style.css">
    <link rel="stylesheet" href="./assests/css/cart.css">
    
    <script type="module" src="https://unpkg.com/ionicons@7.1.0/dist/ionicons/ionicons.esm.js"></script>
    <script nomodule src="https://unpkg.com/ionicons@7.1.0/dist/ionicons/ionicons.js"></script>
</head>
<body>
    
    <div class="cart-container">
        
        <a href="index.php" class="back-link">
            <ion-icon name="arrow-back"></ion-icon>
            Continue Shopping
        </a>
        
        <div class="page-header">
            <h1>Shopping Cart</h1>
            <p><?php echo count($cart_items); ?> item(s) in your cart</p>
        </div>
        
        <?php if (!empty($error)): ?>
            <div class="alert alert-error">
                <span><?php echo $error; ?></span>
            </div>
        <?php endif; ?>
        
        <?php if (!empty($success)): ?>
            <div class="alert alert-success">
                <span><?php echo $success; ?></span>
            </div>
        <?php endif; ?>
        
        <?php if (count($cart_items) > 0): ?>
            <div class="cart-content">
                <div class="cart-items">
                    <?php foreach ($cart_items as $item): ?>
                        <div class="cart-item">
                            <img src="<?php echo htmlspecialchars($item['product_image']); ?>" alt="<?php echo htmlspecialchars($item['product_name']); ?>" class="item-image">
                            
                            <div class="item-details">
                                <h3><?php echo htmlspecialchars($item['product_name']); ?></h3>
                                <div class="item-price">Rs <?php echo number_format($item['product_price'], 2); ?></div>
                                
                                <div class="quantity-controls">
                                    <form method="POST" class="qty-form">
                                        <?php if ($is_logged_in): ?>
                                            <input type="hidden" name="cart_id" value="<?php echo $item['id']; ?>">
                                        <?php else: ?>
                                            <input type="hidden" name="product_id" value="<?php echo $item['product_id']; ?>">
                                        <?php endif; ?>
                                        <input type="hidden" name="quantity" value="<?php echo max(1, $item['quantity'] - 1); ?>">
                                        <button type="submit" name="update_quantity" class="qty-btn">
                                            <ion-icon name="remove"></ion-icon>
                                        </button>
                                    </form>
                                    
                                    <input type="text" value="<?php echo $item['quantity']; ?>" class="qty-input" readonly>
                                    
                                    <form method="POST" class="qty-form">
                                        <?php if ($is_logged_in): ?>
                                            <input type="hidden" name="cart_id" value="<?php echo $item['id']; ?>">
                                        <?php else: ?>
                                            <input type="hidden" name="product_id" value="<?php echo $item['product_id']; ?>">
                                        <?php endif; ?>
                                        <input type="hidden" name="quantity" value="<?php echo $item['quantity'] + 1; ?>">
                                        <button type="submit" name="update_quantity" class="qty-btn">
                                            <ion-icon name="add"></ion-icon>
                                        </button>
                                    </form>
                                    
                                    <span class="item-subtotal">
                                        Subtotal: Rs <?php echo number_format($item['product_price'] * $item['quantity'], 2); ?>
                                    </span>
                                </div>
                                
                                <div class="item-actions">
                                    <?php if ($is_logged_in): ?>
                                        <a href="?remove=<?php echo $item['id']; ?>" class="remove-btn" onclick="return confirm('Remove this item from cart?')">
                                    <?php else: ?>
                                        <a href="?remove=<?php echo $item['product_id']; ?>" class="remove-btn" onclick="return confirm('Remove this item from cart?')">
                                    <?php endif; ?>
                                        <ion-icon name="trash"></ion-icon>
                                        Remove
                                    </a>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
                
                <div class="cart-summary">
                    <h2>Order Summary</h2>
                    
                    <div class="summary-row">
                        <span>Subtotal:</span>
                        <span>Rs <?php echo number_format($total, 2); ?></span>
                    </div>
                    
                    <div class="summary-row">
                        <span>Shipping:</span>
                        <span>Free</span>
                    </div>
                    
                    <div class="summary-row">
                        <span>Tax:</span>
                        <span>Rs <?php echo number_format($total * 0.1, 2); ?></span>
                    </div>
                    
                    <div class="summary-row total">
                        <span>Total:</span>
                        <span>Rs <?php echo number_format($total * 1.1, 2); ?></span>
                    </div>
                    
                    <?php if ($is_logged_in): ?>
                        <button class="checkout-btn" onclick="window.location.href='checkout.php'">
                            Proceed to Checkout
                        </button>
                    <?php else: ?>
                        <button class="checkout-btn" onclick="if(confirm('Please login to proceed with checkout')) window.location.href='login.php'">
                            Proceed to Checkout
                        </button>
                    <?php endif; ?>
                </div>
            </div>
        <?php else: ?>
            <div class="cart-content">
                <div class="empty-cart">
                    <ion-icon name="cart-outline"></ion-icon>
                    <h2>Your Cart is Empty</h2>
                    <p>Add some amazing furniture to your cart!</p>
                    <a href="index.php" class="shop-btn">Start Shopping</a>
                </div>
            </div>
        <?php endif; ?>
        
    </div>
    
</body>
</html>
