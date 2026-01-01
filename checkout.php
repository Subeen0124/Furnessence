<?php
session_start();

// Include config file
require_once 'config.php';

// Check if user is logged in
if (!isset($_SESSION['loggedin']) || $_SESSION['loggedin'] !== true) {
    header("location: login.php");
    exit();
}

// Initialize cart if not exists
if (!isset($_SESSION['cart'])) {
    $_SESSION['cart'] = [];
}

// Handle cart updates
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['update_cart'])) {
    foreach ($_POST['quantity'] as $product_id => $quantity) {
        if ($quantity > 0) {
            $_SESSION['cart'][$product_id] = (int)$quantity;
        } else {
            unset($_SESSION['cart'][$product_id]);
        }
    }
    header("location: checkout.php");
    exit();
}

// Handle remove item
if (isset($_GET['remove']) && isset($_SESSION['cart'][$_GET['remove']])) {
    unset($_SESSION['cart'][$_GET['remove']]);
    header("location: checkout.php");
    exit();
}

// Sample product data (in a real app, this would come from database)
$products = [
    1 => ['name' => 'Animi Dolor Pariatur', 'price' => 10.00, 'image' => './assets/images/product-1.jpg'],
    2 => ['name' => 'Art Deco Home', 'price' => 30.00, 'image' => './assets/images/product-2.jpg'],
    3 => ['name' => 'Artificial potted plant', 'price' => 40.00, 'image' => './assets/images/product-3.jpg'],
    4 => ['name' => 'Dark Green Jug', 'price' => 17.10, 'image' => './assets/images/product-4.jpg'],
    5 => ['name' => 'Drinking Glasses', 'price' => 21.00, 'image' => './assets/images/product-5.jpg'],
    6 => ['name' => 'Helen Chair', 'price' => 69.50, 'image' => './assets/images/product-6.jpg'],
    7 => ['name' => 'High Quality Glass Bottle', 'price' => 30.10, 'image' => './assets/images/product-7.jpg'],
    8 => ['name' => 'Living Room & Bedroom Lights', 'price' => 45.00, 'image' => './assets/images/product-8.jpg'],
    9 => ['name' => 'Nancy Chair', 'price' => 90.00, 'image' => './assets/images/product-9.jpg'],
    10 => ['name' => 'Simple Chair', 'price' => 40.00, 'image' => './assets/images/product-10.jpg'],
    11 => ['name' => 'Smooth Disk', 'price' => 46.00, 'image' => './assets/images/product-11.jpg'],
    12 => ['name' => 'Table Black', 'price' => 67.00, 'image' => './assets/images/product-12.jpg'],
    13 => ['name' => 'Table Wood Pine', 'price' => 50.00, 'image' => './assets/images/product-13.jpg'],
    14 => ['name' => 'Teapot with black tea', 'price' => 25.00, 'image' => './assets/images/product-14.jpg'],
    15 => ['name' => 'Unique Decoration', 'price' => 15.00, 'image' => './assets/images/product-15.jpg'],
    16 => ['name' => 'Vase Of Flowers', 'price' => 77.00, 'image' => './assets/images/product-16.jpg'],
    17 => ['name' => 'Wood Eggs', 'price' => 19.00, 'image' => './assets/images/product-17.jpg'],
    18 => ['name' => 'Wooden Box', 'price' => 27.00, 'image' => './assets/images/product-18.jpg'],
    19 => ['name' => 'Wooden Cups', 'price' => 29.00, 'image' => './assets/images/product-19.jpg']
];

// Calculate cart total
$cart_total = 0;
foreach ($_SESSION['cart'] as $product_id => $quantity) {
    if (isset($products[$product_id])) {
        $cart_total += $products[$product_id]['price'] * $quantity;
    }
}

// Handle order placement
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['place_order'])) {
    $conn = getDBConnection();

    // Get form data
    $shipping_name = trim($_POST['shipping_name']);
    $shipping_email = trim($_POST['shipping_email']);
    $shipping_address = trim($_POST['shipping_address']);
    $shipping_city = trim($_POST['shipping_city']);
    $shipping_zip = trim($_POST['shipping_zip']);
    $payment_method = $_POST['payment_method'];

    // Validate form data
    $errors = [];
    if (empty($shipping_name)) $errors[] = "Shipping name is required.";
    if (empty($shipping_email) || !filter_var($shipping_email, FILTER_VALIDATE_EMAIL)) $errors[] = "Valid shipping email is required.";
    if (empty($shipping_address)) $errors[] = "Shipping address is required.";
    if (empty($shipping_city)) $errors[] = "Shipping city is required.";
    if (empty($shipping_zip)) $errors[] = "Shipping ZIP code is required.";
    if (empty($payment_method)) $errors[] = "Payment method is required.";
    if (empty($_SESSION['cart'])) $errors[] = "Your cart is empty.";

    if (empty($errors)) {
        // Insert order
        $user_id = $_SESSION['id'];
        $total_amount = $cart_total;
        $order_date = date('Y-m-d H:i:s');

        $sql = "INSERT INTO orders (user_id, total_amount, shipping_name, shipping_email, shipping_address, shipping_city, shipping_zip, payment_method, order_date) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)";

        if ($stmt = $conn->prepare($sql)) {
            $stmt->bind_param("idsssssss", $user_id, $total_amount, $shipping_name, $shipping_email, $shipping_address, $shipping_city, $shipping_zip, $payment_method, $order_date);

            if ($stmt->execute()) {
                $order_id = $conn->insert_id;

                // Insert order items
                foreach ($_SESSION['cart'] as $product_id => $quantity) {
                    if (isset($products[$product_id])) {
                        $product_name = $products[$product_id]['name'];
                        $price = $products[$product_id]['price'];

                        $item_sql = "INSERT INTO order_items (order_id, product_id, product_name, quantity, price) VALUES (?, ?, ?, ?, ?)";
                        if ($item_stmt = $conn->prepare($item_sql)) {
                            $item_stmt->bind_param("iisid", $order_id, $product_id, $product_name, $quantity, $price);
                            $item_stmt->execute();
                            $item_stmt->close();
                        }
                    }
                }

                // Clear cart
                $_SESSION['cart'] = [];

                // Redirect to order confirmation
                header("location: checkout.php?order_id=" . $order_id);
                exit();
            } else {
                $errors[] = "Something went wrong. Please try again.";
            }
            $stmt->close();
        }
    }

    $conn->close();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Checkout - Furnessence</title>
    <link rel="stylesheet" href="style.css">
    <style>
        .checkout-container {
            max-width: 1200px;
            margin: 50px auto;
            padding: 20px;
            display: grid;
            grid-template-columns: 1fr 400px;
            gap: 40px;
        }

        .cart-section, .checkout-form {
            background-color: var(--white);
            padding: 30px;
            border-radius: 8px;
            box-shadow: var(--shadow);
        }

        .cart-item {
            display: flex;
            align-items: center;
            gap: 20px;
            padding: 20px 0;
            border-bottom: 1px solid var(--light-gray);
        }

        .cart-item img {
            width: 80px;
            height: 80px;
            object-fit: cover;
            border-radius: 4px;
        }

        .cart-item-details {
            flex: 1;
        }

        .cart-item-title {
            font-weight: var(--fw-500);
            margin-bottom: 5px;
        }

        .cart-item-price {
            color: var(--tan-crayola);
            font-weight: var(--fw-500);
        }

        .quantity-input {
            width: 60px;
            padding: 5px;
            text-align: center;
            border: 1px solid var(--light-gray);
            border-radius: 4px;
        }

        .remove-btn {
            color: var(--red-orange-color-wheel);
            text-decoration: none;
            font-size: var(--fs-5);
        }

        .cart-total {
            margin-top: 20px;
            padding-top: 20px;
            border-top: 2px solid var(--light-gray);
            text-align: right;
        }

        .cart-total h3 {
            font-size: var(--fs-4);
            margin-bottom: 10px;
        }

        .form-group {
            margin-bottom: 20px;
        }

        .form-group label {
            display: block;
            margin-bottom: 5px;
            font-weight: var(--fw-500);
        }

        .form-group input,
        .form-group select {
            width: 100%;
            padding: 12px;
            border: 1px solid var(--light-gray);
            border-radius: 4px;
            font-size: 1.6rem;
        }

        .form-group input:focus,
        .form-group select:focus {
            outline: none;
            border-color: var(--tan-crayola);
        }

        .btn {
            width: 100%;
            padding: 15px;
            background-color: var(--tan-crayola);
            color: var(--white);
            border: none;
            border-radius: 4px;
            font-size: 1.8rem;
            font-weight: var(--fw-500);
            cursor: pointer;
            transition: var(--transition-1);
        }

        .btn:hover {
            background-color: var(--smokey-black);
        }

        .update-cart-btn {
            background-color: var(--granite-gray);
            margin-top: 20px;
        }

        .update-cart-btn:hover {
            background-color: var(--smokey-black);
        }

        .error {
            color: var(--red-orange-color-wheel);
            font-size: 1.4rem;
            margin-bottom: 10px;
        }

        .success {
            color: green;
            font-size: 1.6rem;
            text-align: center;
            margin: 20px 0;
        }

        .order-confirmation {
            text-align: center;
            padding: 40px;
        }

        .order-confirmation h2 {
            color: var(--tan-crayola);
            margin-bottom: 20px;
        }

        @media (max-width: 768px) {
            .checkout-container {
                grid-template-columns: 1fr;
                gap: 20px;
            }
        }
    </style>
</head>
<body>
    <div class="checkout-container">
        <?php if (isset($_GET['order_id'])): ?>
            <!-- Order Confirmation -->
            <div class="order-confirmation" style="grid-column: span 2;">
                <h2>Order Placed Successfully!</h2>
                <p>Thank you for your order. Your order ID is: <strong><?php echo htmlspecialchars($_GET['order_id']); ?></strong></p>
                <p>You will receive an email confirmation shortly.</p>
                <a href="index.php" class="btn" style="width: auto; padding: 12px 30px; margin-top: 20px;">Continue Shopping</a>
            </div>
        <?php else: ?>
            <!-- Cart Section -->
            <div class="cart-section">
                <h2>Your Cart</h2>

                <?php if (empty($_SESSION['cart'])): ?>
                    <p>Your cart is empty. <a href="index.php">Continue shopping</a></p>
                <?php else: ?>
                    <form method="post" action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>">
                        <input type="hidden" name="update_cart" value="1">
                        <?php foreach ($_SESSION['cart'] as $product_id => $quantity): ?>
                            <?php if (isset($products[$product_id])): ?>
                                <div class="cart-item">
                                    <img src="<?php echo htmlspecialchars($products[$product_id]['image']); ?>" alt="<?php echo htmlspecialchars($products[$product_id]['name']); ?>">
                                    <div class="cart-item-details">
                                        <h3 class="cart-item-title"><?php echo htmlspecialchars($products[$product_id]['name']); ?></h3>
                                        <p class="cart-item-price">$<?php echo number_format($products[$product_id]['price'], 2); ?> each</p>
                                    </div>
                                    <input type="number" name="quantity[<?php echo $product_id; ?>]" value="<?php echo $quantity; ?>" min="1" class="quantity-input">
                                    <a href="?remove=<?php echo $product_id; ?>" class="remove-btn">Remove</a>
                                </div>
                            <?php endif; ?>
                        <?php endforeach; ?>

                        <div class="cart-total">
                            <h3>Total: $<?php echo number_format($cart_total, 2); ?></h3>
                        </div>

                        <button type="submit" class="btn update-cart-btn">Update Cart</button>
                    </form>
                <?php endif; ?>
            </div>

            <!-- Checkout Form -->
            <div class="checkout-form">
                <h2>Shipping & Payment</h2>

                <?php if (!empty($errors)): ?>
                    <?php foreach ($errors as $error): ?>
                        <div class="error"><?php echo $error; ?></div>
                    <?php endforeach; ?>
                <?php endif; ?>

                <form method="post" action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>">
                    <input type="hidden" name="place_order" value="1">

                    <div class="form-group">
                        <label for="shipping_name">Full Name</label>
                        <input type="text" id="shipping_name" name="shipping_name" value="<?php echo htmlspecialchars($_SESSION['name'] ?? ''); ?>" required>
                    </div>

                    <div class="form-group">
                        <label for="shipping_email">Email Address</label>
                        <input type="email" id="shipping_email" name="shipping_email" value="<?php echo htmlspecialchars($_SESSION['email'] ?? ''); ?>" required>
                    </div>

                    <div class="form-group">
                        <label for="shipping_address">Shipping Address</label>
                        <input type="text" id="shipping_address" name="shipping_address" required>
                    </div>

                    <div class="form-group">
                        <label for="shipping_city">City</label>
                        <input type="text" id="shipping_city" name="shipping_city" required>
                    </div>

                    <div class="form-group">
                        <label for="shipping_zip">ZIP Code</label>
                        <input type="text" id="shipping_zip" name="shipping_zip" required>
                    </div>

                    <div class="form-group">
                        <label for="payment_method">Payment Method</label>
                        <select id="payment_method" name="payment_method" required>
                            <option value="">Select Payment Method</option>
                            <option value="credit_card">Credit Card</option>
                            <option value="debit_card">Debit Card</option>
                            <option value="paypal">PayPal</option>
                            <option value="cash_on_delivery">Cash on Delivery</option>
                        </select>
                    </div>

                    <button type="submit" class="btn">Place Order</button>
                </form>
            </div>
        <?php endif; ?>
    </div>
</body>
</html>
