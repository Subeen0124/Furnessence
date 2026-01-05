<?php
require_once 'config.php';

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

$user_id = $_SESSION['user_id'];

// Get user details
$user_query = "SELECT * FROM users WHERE id = $user_id LIMIT 1";
$user_result = mysqli_query($conn, $user_query);
$user = mysqli_fetch_assoc($user_result);

// Get cart items
$cart_query = "SELECT * FROM cart WHERE user_id = $user_id ORDER BY created_at DESC";
$cart_result = mysqli_query($conn, $cart_query);

// Calculate totals
$cart_items = [];
$subtotal = 0;
while ($item = mysqli_fetch_assoc($cart_result)) {
    $cart_items[] = $item;
    $subtotal += $item['product_price'] * $item['quantity'];
}

$tax = $subtotal * 0.1;
$shipping = 0; // Free shipping
$total = $subtotal + $tax + $shipping;

$success = '';
$error = '';

// Handle order placement
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['place_order'])) {
    // Validate required fields
    $full_name = mysqli_real_escape_string($conn, trim($_POST['full_name']));
    $email = mysqli_real_escape_string($conn, trim($_POST['email']));
    $phone = mysqli_real_escape_string($conn, trim($_POST['phone']));
    $address = mysqli_real_escape_string($conn, trim($_POST['address']));
    $city = mysqli_real_escape_string($conn, trim($_POST['city']));
    $state = mysqli_real_escape_string($conn, trim($_POST['state']));
    $zip = mysqli_real_escape_string($conn, trim($_POST['zip']));
    $payment_method = mysqli_real_escape_string($conn, $_POST['payment_method']);
    $order_notes = isset($_POST['order_notes']) ? mysqli_real_escape_string($conn, trim($_POST['order_notes'])) : '';
    
    if (empty($full_name) || empty($email) || empty($phone) || empty($address) || empty($city) || empty($state) || empty($zip)) {
        $error = "Please fill in all required fields.";
    } elseif (count($cart_items) === 0) {
        $error = "Your cart is empty.";
    } else {
        // Start transaction
        mysqli_begin_transaction($conn);
        
        try {
            // Check stock availability for all items
            foreach ($cart_items as $item) {
                $stock_check = "SELECT stock_quantity FROM products WHERE id = {$item['product_id']} LIMIT 1";
                $stock_result = mysqli_query($conn, $stock_check);
                
                if (mysqli_num_rows($stock_result) > 0) {
                    $stock_data = mysqli_fetch_assoc($stock_result);
                    if ($stock_data['stock_quantity'] < $item['quantity']) {
                        throw new Exception("Insufficient stock for {$item['product_name']}. Only {$stock_data['stock_quantity']} available.");
                    }
                } else {
                    throw new Exception("Product {$item['product_name']} not found.");
                }
            }
            
            // Generate order number
            $order_number = 'ORD-' . strtoupper(uniqid());
            
            // Build shipping address
            $shipping_address = "$address, $city, $state $zip";
            
            // Insert order
            $insert_order = "INSERT INTO orders (user_id, order_number, total_amount, status, shipping_address) 
                VALUES ($user_id, '$order_number', $total, 'pending', '$shipping_address')";
            
            if (!mysqli_query($conn, $insert_order)) {
                throw new Exception("Failed to create order");
            }
            
            $order_id = mysqli_insert_id($conn);
            
            // Insert order items and update stock
            foreach ($cart_items as $item) {
                $product_id = $item['product_id'];
                $product_name = mysqli_real_escape_string($conn, $item['product_name']);
                $product_price = $item['product_price'];
                $quantity = $item['quantity'];
                $subtotal = $product_price * $quantity;
                
                // Insert order item
                $insert_item = "INSERT INTO order_items (order_id, product_id, product_name, product_price, quantity, subtotal) 
                    VALUES ($order_id, $product_id, '$product_name', $product_price, $quantity, $subtotal)";
                
                if (!mysqli_query($conn, $insert_item)) {
                    throw new Exception("Failed to add order items");
                }
                
                // Decrease stock
                $update_stock = "UPDATE products SET stock_quantity = stock_quantity - $quantity WHERE id = $product_id";
                
                if (!mysqli_query($conn, $update_stock)) {
                    throw new Exception("Failed to update stock");
                }
            }
            
            // Clear cart
            $clear_cart = "DELETE FROM cart WHERE user_id = $user_id";
            mysqli_query($conn, $clear_cart);
            
            // Commit transaction
            mysqli_commit($conn);
            
            $success = "Order placed successfully! Order ID: $order_number";
            
            // Redirect to success page after 2 seconds
            header("refresh:2;url=index.php?order_success=1&order_id=$order_number");
            
        } catch (Exception $e) {
            // Rollback transaction on error
            mysqli_rollback($conn);
            $error = $e->getMessage();
        }
    }
}

// Get wishlist count for header
$wishlist_query = "SELECT COUNT(*) as count FROM wishlist WHERE user_id = $user_id";
$wishlist_result = mysqli_query($conn, $wishlist_query);
$wishlist_count = mysqli_fetch_assoc($wishlist_result)['count'];
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Checkout - Furnessence</title>
    
    <link rel="shortcut icon" href="./favicon.svg" type="image/svg+xml">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Mr+De+Haviland&family=Roboto:wght@400;500;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="./assests/css/style.css">
    <link rel="stylesheet" href="./assests/css/checkout.css">
    
    <script type="module" src="https://unpkg.com/ionicons@7.1.0/dist/ionicons/ionicons.esm.js"></script>
    <script nomodule src="https://unpkg.com/ionicons@7.1.0/dist/ionicons/ionicons.js"></script>
</head>
<body>
    
    <div class="checkout-container">
        
        <a href="cart.php" class="back-link">
            <ion-icon name="arrow-back"></ion-icon>
            Back to Cart
        </a>
        
        <div class="page-header">
            <h1>Checkout</h1>
            <p>Complete your order</p>
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
        
        <?php if (count($cart_items) > 0): ?>
            <form action="" method="POST" id="checkoutForm">
                <div class="checkout-content">
                    
                    <!-- Checkout Form -->
                    <div class="checkout-form-section">
                        
                        <!-- Billing Information -->
                        <div class="form-card">
                            <h2>Billing Information</h2>
                            
                            <div class="form-group">
                                <label for="full_name">Full Name <span class="required">*</span></label>
                                <input type="text" id="full_name" name="full_name" class="form-input" value="<?php echo htmlspecialchars($user['name']); ?>" required>
                            </div>
                            
                            <div class="form-row">
                                <div class="form-group">
                                    <label for="email">Email Address <span class="required">*</span></label>
                                    <input type="email" id="email" name="email" class="form-input" value="<?php echo htmlspecialchars($user['email']); ?>" required>
                                </div>
                                
                                <div class="form-group">
                                    <label for="phone">Phone Number <span class="required">*</span></label>
                                    <input type="tel" id="phone" name="phone" class="form-input" placeholder="+977-9800000000" required>
                                </div>
                            </div>
                            
                            <div class="form-group">
                                <label for="address">Street Address <span class="required">*</span></label>
                                <input type="text" id="address" name="address" class="form-input" placeholder="House number and street name" required>
                            </div>
                            
                            <div class="form-row">
                                <div class="form-group">
                                    <label for="city">City <span class="required">*</span></label>
                                    <input type="text" id="city" name="city" class="form-input" required>
                                </div>
                                
                                <div class="form-group">
                                    <label for="state">State/Province <span class="required">*</span></label>
                                    <input type="text" id="state" name="state" class="form-input" required>
                                </div>
                            </div>
                            
                            <div class="form-row">
                                <div class="form-group">
                                    <label for="zip">ZIP/Postal Code <span class="required">*</span></label>
                                    <input type="text" id="zip" name="zip" class="form-input" required>
                                </div>
                                
                                <div class="form-group">
                                    <label for="country">Country</label>
                                    <input type="text" id="country" name="country" class="form-input" value="Nepal" readonly>
                                </div>
                            </div>
                            
                            <div class="form-group">
                                <label for="order_notes">Order Notes (Optional)</label>
                                <textarea id="order_notes" name="order_notes" class="form-textarea" placeholder="Notes about your order, e.g. special notes for delivery."></textarea>
                            </div>
                        </div>
                        
                        <!-- Payment Method -->
                        <div class="form-card">
                            <h2>Payment Method</h2>
                            
                            <div class="payment-methods">
                                <div class="payment-option" onclick="selectPayment(this, 'cod')">
                                    <input type="radio" id="cod" name="payment_method" value="cod" checked>
                                    <label for="cod">
                                        <ion-icon name="cash-outline"></ion-icon>
                                        <span>Cash on Delivery</span>
                                    </label>
                                </div>
                                
                                <div class="payment-option" onclick="selectPayment(this, 'bank')">
                                    <input type="radio" id="bank" name="payment_method" value="bank">
                                    <label for="bank">
                                        <ion-icon name="card-outline"></ion-icon>
                                        <span>Bank Transfer</span>
                                    </label>
                                </div>
                                
                                <div class="payment-option" onclick="selectPayment(this, 'esewa')">
                                    <input type="radio" id="esewa" name="payment_method" value="esewa">
                                    <label for="esewa">
                                        <ion-icon name="wallet-outline"></ion-icon>
                                        <span>eSewa / Khalti</span>
                                    </label>
                                </div>
                            </div>
                        </div>
                        
                    </div>
                    
                    <!-- Order Summary -->
                    <div class="order-summary">
                        <h2>Order Summary</h2>
                        
                        <div class="summary-items">
                            <?php foreach ($cart_items as $item): ?>
                                <div class="summary-item">
                                    <img src="<?php echo htmlspecialchars($item['product_image']); ?>" alt="<?php echo htmlspecialchars($item['product_name']); ?>" class="item-image">
                                    
                                    <div class="item-info">
                                        <h4><?php echo htmlspecialchars($item['product_name']); ?></h4>
                                        <p class="item-quantity">Qty: <?php echo $item['quantity']; ?></p>
                                    </div>
                                    
                                    <div class="item-price">
                                        $<?php echo number_format($item['product_price'] * $item['quantity'], 2); ?>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        </div>
                        
                        <div class="summary-row">
                            <span>Subtotal:</span>
                            <span>$<?php echo number_format($subtotal, 2); ?></span>
                        </div>
                        
                        <div class="summary-row">
                            <span>Shipping:</span>
                            <span><?php echo $shipping > 0 ? '$' . number_format($shipping, 2) : 'Free'; ?></span>
                        </div>
                        
                        <div class="summary-row">
                            <span>Tax (10%):</span>
                            <span>$<?php echo number_format($tax, 2); ?></span>
                        </div>
                        
                        <div class="summary-row total">
                            <span>Total:</span>
                            <span>$<?php echo number_format($total, 2); ?></span>
                        </div>
                        
                        <button type="submit" name="place_order" class="place-order-btn">
                            <ion-icon name="checkmark-circle"></ion-icon>
                            Place Order
                        </button>
                        
                        <div class="secure-checkout">
                            <ion-icon name="shield-checkmark"></ion-icon>
                            <span>Secure Checkout</span>
                        </div>
                    </div>
                    
                </div>
            </form>
        <?php else: ?>
            <div class="checkout-content">
                <div class="empty-cart">
                    <ion-icon name="cart-outline"></ion-icon>
                    <h2>Your Cart is Empty</h2>
                    <p>Add items to your cart before checking out</p>
                    <a href="index.php" class="shop-btn">Start Shopping</a>
                </div>
            </div>
        <?php endif; ?>
        
    </div>
    
    <script src="./assests/js/checkout.js"></script>
    
</body>
</html>
