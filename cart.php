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
        $delete_stmt = mysqli_prepare($conn, "DELETE FROM cart WHERE id = ? AND user_id = ?");
        mysqli_stmt_bind_param($delete_stmt, "ii", $cart_id, $user_id);
        
        if (mysqli_stmt_execute($delete_stmt)) {
            $success = "Item removed from cart!";
        } else {
            $error = "Failed to remove item.";
        }
        mysqli_stmt_close($delete_stmt);
    }

    // Handle update quantity
    if (isset($_POST['update_quantity'])) {
        $cart_id = intval($_POST['cart_id']);
        $quantity = intval($_POST['quantity']);
        if ($quantity > 0) {
            // Get product stock
            $stock_stmt = mysqli_prepare($conn, "SELECT stock_quantity FROM products WHERE id = (SELECT product_id FROM cart WHERE id = ? LIMIT 1) LIMIT 1");
            mysqli_stmt_bind_param($stock_stmt, "i", $cart_id);
            mysqli_stmt_execute($stock_stmt);
            $stock_result = mysqli_stmt_get_result($stock_stmt);
            if ($stock_result && mysqli_num_rows($stock_result) > 0) {
                $stock_data = mysqli_fetch_assoc($stock_result);
                $available_stock = $stock_data['stock_quantity'];
                if ($quantity > $available_stock) {
                    $error = "Only $available_stock items in stock.";
                } elseif ($quantity == $available_stock) {
                    $update_stmt = mysqli_prepare($conn, "UPDATE cart SET quantity = ? WHERE id = ? AND user_id = ?");
                    mysqli_stmt_bind_param($update_stmt, "iii", $quantity, $cart_id, $user_id);
                    mysqli_stmt_execute($update_stmt);
                    mysqli_stmt_close($update_stmt);
                    $error = "Already at maximum stock in cart.";
                } else {
                    $update_stmt = mysqli_prepare($conn, "UPDATE cart SET quantity = ? WHERE id = ? AND user_id = ?");
                    mysqli_stmt_bind_param($update_stmt, "iii", $quantity, $cart_id, $user_id);
                    mysqli_stmt_execute($update_stmt);
                    mysqli_stmt_close($update_stmt);
                }
            }
            mysqli_stmt_close($stock_stmt);
        }
    }

    // Get all cart items from database
    $cart_stmt = mysqli_prepare($conn, "SELECT * FROM cart WHERE user_id = ? ORDER BY created_at DESC");
    mysqli_stmt_bind_param($cart_stmt, "i", $user_id);
    mysqli_stmt_execute($cart_stmt);
    $cart_result = mysqli_stmt_get_result($cart_stmt);

    // Calculate total and remove out-of-stock items
    $total = 0;
    $cart_items = [];
    $removed_out_of_stock = false;
    while ($item = mysqli_fetch_assoc($cart_result)) {
        // Check stock for each item
        $item_stock_stmt = mysqli_prepare($conn, "SELECT stock_quantity FROM products WHERE id = ? LIMIT 1");
        mysqli_stmt_bind_param($item_stock_stmt, "i", $item['product_id']);
        mysqli_stmt_execute($item_stock_stmt);
        $stock_result = mysqli_stmt_get_result($item_stock_stmt);
        $stock_data = ($stock_result && mysqli_num_rows($stock_result) > 0) ? mysqli_fetch_assoc($stock_result) : null;
        mysqli_stmt_close($item_stock_stmt);
        if ($stock_data && $stock_data['stock_quantity'] == 0) {
            // Remove from cart if out of stock
            $del_stmt = mysqli_prepare($conn, "DELETE FROM cart WHERE id = ? AND user_id = ?");
            mysqli_stmt_bind_param($del_stmt, "ii", $item['id'], $user_id);
            mysqli_stmt_execute($del_stmt);
            mysqli_stmt_close($del_stmt);
            $removed_out_of_stock = true;
            continue;
        }
        $cart_items[] = $item;
        $total += $item['product_price'] * $item['quantity'];
    }
    if ($removed_out_of_stock) {
        $error = 'Some items were removed because they are out of stock.';
    }

    // Get wishlist count for header
    $wl_stmt = mysqli_prepare($conn, "SELECT COUNT(*) as count FROM wishlist WHERE user_id = ?");
    mysqli_stmt_bind_param($wl_stmt, "i", $user_id);
    mysqli_stmt_execute($wl_stmt);
    $wishlist_result = mysqli_stmt_get_result($wl_stmt);
    $wishlist_count = mysqli_fetch_assoc($wishlist_result)['count'];
    mysqli_stmt_close($wl_stmt);
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
                                <?php
                                // Get current stock for this product
                                $tpl_stock_stmt = mysqli_prepare($conn, "SELECT stock_quantity, low_stock_threshold FROM products WHERE id = ? LIMIT 1");
                                mysqli_stmt_bind_param($tpl_stock_stmt, "i", $item['product_id']);
                                mysqli_stmt_execute($tpl_stock_stmt);
                                $stock_result = mysqli_stmt_get_result($tpl_stock_stmt);
                                $stock_data = ($stock_result && mysqli_num_rows($stock_result) > 0) ? mysqli_fetch_assoc($stock_result) : null;
                                mysqli_stmt_close($tpl_stock_stmt);
                                $is_out_of_stock = $stock_data && $stock_data['stock_quantity'] == 0;
                                $is_low_stock = $stock_data && $stock_data['stock_quantity'] > 0 && $stock_data['stock_quantity'] <= $stock_data['low_stock_threshold'];
                                ?>
                                <?php if ($is_out_of_stock): ?>
                                    <div class="stock-status out-of-stock"><i class="fas fa-times-circle"></i> Out of Stock</div>
                                <?php elseif ($is_low_stock): ?>
                                    <div class="stock-status low-stock"><i class="fas fa-exclamation-triangle"></i> Only <?php echo $stock_data['stock_quantity']; ?> left</div>
                                <?php else: ?>
                                    <div class="stock-status in-stock"><i class="fas fa-check-circle"></i> Full Stock (<?php echo $stock_data['stock_quantity']; ?> available)</div>
                                <?php endif; ?>
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
                                        <button type="submit" name="update_quantity" class="qty-btn" <?php echo ($stock_data && $item['quantity'] >= $stock_data['stock_quantity']) ? 'disabled' : ''; ?>>
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
