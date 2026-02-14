<?php
require_once 'config.php';
require_once 'khalti_config.php';

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

$user_id = $_SESSION['user_id'];

// Get user details
$user_stmt = mysqli_prepare($conn, "SELECT * FROM users WHERE id = ? LIMIT 1");
mysqli_stmt_bind_param($user_stmt, "i", $user_id);
mysqli_stmt_execute($user_stmt);
$user_result = mysqli_stmt_get_result($user_stmt);
$user = mysqli_fetch_assoc($user_result);
mysqli_stmt_close($user_stmt);

// Get cart items
$cart_stmt = mysqli_prepare($conn, "SELECT * FROM cart WHERE user_id = ? ORDER BY created_at DESC");
mysqli_stmt_bind_param($cart_stmt, "i", $user_id);
mysqli_stmt_execute($cart_stmt);
$cart_result = mysqli_stmt_get_result($cart_stmt);

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
    $full_name = trim($_POST['full_name']);
    $email = trim($_POST['email']);
    $phone = trim($_POST['phone']);
    $address = trim($_POST['address']);
    $city = trim($_POST['city']);
    $state = trim($_POST['state']);
    $zip = trim($_POST['zip']);
    $payment_method = $_POST['payment_method'];
    $order_notes = isset($_POST['order_notes']) ? trim($_POST['order_notes']) : '';
    
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
                $stock_stmt = mysqli_prepare($conn, "SELECT stock_quantity FROM products WHERE id = ? LIMIT 1");
                mysqli_stmt_bind_param($stock_stmt, "i", $item['product_id']);
                mysqli_stmt_execute($stock_stmt);
                $stock_result = mysqli_stmt_get_result($stock_stmt);
                
                if (mysqli_num_rows($stock_result) > 0) {
                    $stock_data = mysqli_fetch_assoc($stock_result);
                    if ($stock_data['stock_quantity'] < $item['quantity']) {
                        mysqli_stmt_close($stock_stmt);
                        throw new Exception("Insufficient stock for {$item['product_name']}. Only {$stock_data['stock_quantity']} available.");
                    }
                } else {
                    mysqli_stmt_close($stock_stmt);
                    throw new Exception("Product {$item['product_name']} not found.");
                }
                mysqli_stmt_close($stock_stmt);
            }
            
            // Generate order number
            $order_number = 'ORD-' . strtoupper(uniqid());
            
            // Build shipping address
            $shipping_address = "$address, $city, $state $zip";
            
            // Insert order
            $payment_status = ($payment_method === 'cod') ? 'unpaid' : 'unpaid';
            $order_stmt = mysqli_prepare($conn, "INSERT INTO orders (user_id, order_number, total_amount, status, shipping_address, payment_method, payment_status) VALUES (?, ?, ?, 'pending', ?, ?, ?)");
            mysqli_stmt_bind_param($order_stmt, "isdsss", $user_id, $order_number, $total, $shipping_address, $payment_method, $payment_status);
            
            if (!mysqli_stmt_execute($order_stmt)) {
                mysqli_stmt_close($order_stmt);
                throw new Exception("Failed to create order");
            }
            mysqli_stmt_close($order_stmt);
            
            $order_id = mysqli_insert_id($conn);
            
            // Insert order items and update stock
            foreach ($cart_items as $item) {
                $product_id = $item['product_id'];
                $product_name = $item['product_name'];
                $product_price = $item['product_price'];
                $quantity = $item['quantity'];
                $subtotal = $product_price * $quantity;
                
                // Insert order item
                $item_stmt = mysqli_prepare($conn, "INSERT INTO order_items (order_id, product_id, product_name, product_price, quantity, subtotal) VALUES (?, ?, ?, ?, ?, ?)");
                mysqli_stmt_bind_param($item_stmt, "iisdid", $order_id, $product_id, $product_name, $product_price, $quantity, $subtotal);
                
                if (!mysqli_stmt_execute($item_stmt)) {
                    mysqli_stmt_close($item_stmt);
                    throw new Exception("Failed to add order items");
                }
                mysqli_stmt_close($item_stmt);
                
                // Decrease stock
                $stock_update_stmt = mysqli_prepare($conn, "UPDATE products SET stock_quantity = stock_quantity - ? WHERE id = ?");
                mysqli_stmt_bind_param($stock_update_stmt, "ii", $quantity, $product_id);
                
                if (!mysqli_stmt_execute($stock_update_stmt)) {
                    mysqli_stmt_close($stock_update_stmt);
                    throw new Exception("Failed to update stock");
                }
                mysqli_stmt_close($stock_update_stmt);
            }
            
            // Clear cart
            $clear_stmt = mysqli_prepare($conn, "DELETE FROM cart WHERE user_id = ?");
            mysqli_stmt_bind_param($clear_stmt, "i", $user_id);
            mysqli_stmt_execute($clear_stmt);
            mysqli_stmt_close($clear_stmt);
            
            // Commit transaction
            mysqli_commit($conn);
            
            // Handle payment method routing
            if ($payment_method === 'khalti') {
                // Initiate Khalti Payment
                $khalti_payload = [
                    'return_url' => 'http://' . $_SERVER['HTTP_HOST'] . dirname($_SERVER['PHP_SELF']) . '/khalti_verify.php',
                    'website_url' => 'http://' . $_SERVER['HTTP_HOST'] . dirname($_SERVER['PHP_SELF']),
                    'amount' => intval($total * 100), // Khalti expects amount in paisa
                    'purchase_order_id' => $order_number,
                    'purchase_order_name' => 'Furnessence Order #' . $order_number,
                    'customer_info' => [
                        'name' => $full_name,
                        'email' => $email,
                        'phone' => $phone
                    ]
                ];
                
                $ch = curl_init();
                curl_setopt($ch, CURLOPT_URL, KHALTI_INITIATE_URL);
                curl_setopt($ch, CURLOPT_POST, true);
                curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($khalti_payload));
                curl_setopt($ch, CURLOPT_HTTPHEADER, [
                    'Authorization: key ' . getKhaltiSecretKey(),
                    'Content-Type: application/json'
                ]);
                curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
                curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
                
                $response = curl_exec($ch);
                $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
                curl_close($ch);
                
                $khalti_response = json_decode($response, true);
                
                if ($http_code === 200 && isset($khalti_response['payment_url'])) {
                    // Redirect user to Khalti payment page
                    header("Location: " . $khalti_response['payment_url']);
                    exit();
                } else {
                    $error = "Failed to initiate Khalti payment. Please try again or use another payment method.";
                }
            } elseif ($payment_method === 'esewa') {
                // eSewa integration placeholder
                $success = "Order placed successfully! Order ID: $order_number. Please complete eSewa payment.";
                header("refresh:2;url=index.php?order_success=1&order_id=$order_number");
            } else {
                // COD or Bank Transfer
                $success = "Order placed successfully! Order ID: $order_number";
                header("refresh:2;url=index.php?order_success=1&order_id=$order_number");
            }
            
        } catch (Exception $e) {
            // Rollback transaction on error
            mysqli_rollback($conn);
            $error = $e->getMessage();
        }
    }
}

// Get wishlist count for header
$wl_stmt = mysqli_prepare($conn, "SELECT COUNT(*) as count FROM wishlist WHERE user_id = ?");
mysqli_stmt_bind_param($wl_stmt, "i", $user_id);
mysqli_stmt_execute($wl_stmt);
$wishlist_result = mysqli_stmt_get_result($wl_stmt);
$wishlist_count = mysqli_fetch_assoc($wishlist_result)['count'];
mysqli_stmt_close($wl_stmt);
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
                                <div class="payment-option active" onclick="selectPayment(this, 'cod')">
                                    <input type="radio" id="cod" name="payment_method" value="cod" checked>
                                    <label for="cod">
                                        <ion-icon name="cash-outline"></ion-icon>
                                        <div>
                                            <span>Cash on Delivery</span>
                                            <small>Pay when you receive</small>
                                        </div>
                                    </label>
                                </div>
                                
                                <div class="payment-option" onclick="selectPayment(this, 'khalti')">
                                    <input type="radio" id="khalti" name="payment_method" value="khalti">
                                    <label for="khalti">
                                        <ion-icon name="wallet-outline"></ion-icon>
                                        <div>
                                            <span>Khalti</span>
                                            <small>Pay securely via Khalti wallet</small>
                                        </div>
                                    </label>
                                </div>
                                
                                <div class="payment-option" onclick="selectPayment(this, 'esewa')">
                                    <input type="radio" id="esewa" name="payment_method" value="esewa">
                                    <label for="esewa">
                                        <ion-icon name="phone-portrait-outline"></ion-icon>
                                        <div>
                                            <span>eSewa</span>
                                            <small>Pay with eSewa mobile wallet</small>
                                        </div>
                                    </label>
                                </div>
                                
                                <div class="payment-option" onclick="selectPayment(this, 'bank')">
                                    <input type="radio" id="bank" name="payment_method" value="bank">
                                    <label for="bank">
                                        <ion-icon name="card-outline"></ion-icon>
                                        <div>
                                            <span>Bank Transfer</span>
                                            <small>Direct bank payment</small>
                                        </div>
                                    </label>
                                </div>
                            </div>
                            
                            <div id="khalti-info" class="payment-info" style="display:none;">
                                <ion-icon name="information-circle-outline"></ion-icon>
                                <p>You will be redirected to Khalti's secure payment page to complete your payment.</p>
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
                            <span>Rs <?php echo number_format($subtotal, 2); ?></span>
                        </div>
                        
                        <div class="summary-row">
                            <span>Shipping:</span>
                            <span><?php echo $shipping > 0 ? 'Rs ' . number_format($shipping, 2) : 'Free'; ?></span>
                        </div>
                        
                        <div class="summary-row">
                            <span>Tax (10%):</span>
                            <span>Rs <?php echo number_format($tax, 2); ?></span>
                        </div>
                        
                        <div class="summary-row total">
                            <span>Total:</span>
                            <span>Rs <?php echo number_format($total, 2); ?></span>
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
