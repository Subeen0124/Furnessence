<?php
session_start();

// Include config file
require_once 'config.php';

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
    header("location: product-cart.php");
    exit();
}

// Handle remove item
if (isset($_GET['remove']) && isset($_SESSION['cart'][$_GET['remove']])) {
    unset($_SESSION['cart'][$_GET['remove']]);
    header("location: product-cart.php");
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
    19 => ['name' => 'Nancy Chair', 'price' => 29.00, 'image' => './assets/images/product-19.jpg']
];

// Calculate cart total
$cart_total = 0;
foreach ($_SESSION['cart'] as $product_id => $quantity) {
    if (isset($products[$product_id])) {
        $cart_total += $products[$product_id]['price'] * $quantity;
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Shopping Cart - Furnessence</title>
    <link rel="stylesheet" href="style.css">
    <style>
        .cart-container {
            max-width: 1200px;
            margin: 50px auto;
            padding: 20px;
        }

        .cart-header {
            text-align: center;
            margin-bottom: 40px;
        }

        .cart-header h1 {
            font-size: var(--fs-3);
            color: var(--tan-crayola);
            margin-bottom: 10px;
        }

        .cart-empty {
            text-align: center;
            padding: 60px 20px;
            background-color: var(--cultured);
            border-radius: 8px;
        }

        .cart-empty h2 {
            color: var(--granite-gray);
            margin-bottom: 20px;
        }

        .cart-empty a {
            display: inline-block;
            padding: 12px 30px;
            background-color: var(--tan-crayola);
            color: var(--white);
            text-decoration: none;
            border-radius: 4px;
            transition: var(--transition-1);
        }

        .cart-empty a:hover {
            background-color: var(--smokey-black);
        }

        .cart-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 40px;
            background-color: var(--white);
            box-shadow: var(--shadow);
            border-radius: 8px;
            overflow: hidden;
        }

        .cart-table th,
        .cart-table td {
            padding: 20px;
            text-align: left;
            border-bottom: 1px solid var(--light-gray);
        }

        .cart-table th {
            background-color: var(--cultured);
            font-weight: var(--fw-500);
            color: var(--smokey-black);
        }

        .cart-item-image {
            width: 80px;
            height: 80px;
            object-fit: cover;
            border-radius: 4px;
        }

        .cart-item-name {
            font-weight: var(--fw-500);
            margin-bottom: 5px;
        }

        .cart-item-price {
            color: var(--tan-crayola);
            font-weight: var(--fw-500);
        }

        .quantity-input {
            width: 60px;
            padding: 8px;
            text-align: center;
            border: 1px solid var(--light-gray);
            border-radius: 4px;
            font-size: 1.4rem;
        }

        .remove-btn {
            color: var(--red-orange-color-wheel);
            text-decoration: none;
            font-size: 1.4rem;
            padding: 5px 10px;
            border: 1px solid var(--red-orange-color-wheel);
            border-radius: 4px;
            transition: var(--transition-1);
        }

        .remove-btn:hover {
            background-color: var(--red-orange-color-wheel);
            color: var(--white);
        }

        .cart-total {
            background-color: var(--cultured);
            padding: 30px;
            border-radius: 8px;
            margin-bottom: 30px;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .cart-total h2 {
            font-size: var(--fs-4);
            margin: 0;
        }

        .cart-total .total-price {
            font-size: var(--fs-4);
            color: var(--tan-crayola);
            font-weight: var(--fw-700);
        }

        .cart-actions {
            display: flex;
            gap: 20px;
            justify-content: space-between;
            align-items: center;
        }

        .btn {
            padding: 15px 30px;
            border: none;
            border-radius: 4px;
            font-size: 1.6rem;
            font-weight: var(--fw-500);
            cursor: pointer;
            text-decoration: none;
            display: inline-block;
            transition: var(--transition-1);
        }

        .btn-primary {
            background-color: var(--tan-crayola);
            color: var(--white);
        }

        .btn-primary:hover {
            background-color: var(--smokey-black);
        }

        .btn-secondary {
            background-color: var(--granite-gray);
            color: var(--white);
        }

        .btn-secondary:hover {
            background-color: var(--smokey-black);
        }

        .continue-shopping {
            text-align: center;
            margin-top: 40px;
        }

        .continue-shopping a {
            color: var(--tan-crayola);
            text-decoration: none;
            font-weight: var(--fw-500);
        }

        .continue-shopping a:hover {
            text-decoration: underline;
        }

        @media (max-width: 768px) {
            .cart-table {
                font-size: 1.4rem;
            }

            .cart-table th,
            .cart-table td {
                padding: 15px 10px;
            }

            .cart-item-image {
                width: 60px;
                height: 60px;
            }

            .cart-actions {
                flex-direction: column;
                gap: 15px;
            }

            .btn {
                width: 100%;
                text-align: center;
            }
        }
    </style>
</head>
<body>
    <div class="cart-container">
        <div class="cart-header">
            <h1>Shopping Cart</h1>
            <p>Review your items and proceed to checkout</p>
        </div>

        <?php if (empty($_SESSION['cart'])): ?>
            <div class="cart-empty">
                <h2>Your cart is empty</h2>
                <p>Add some products to your cart to get started!</p>
                <a href="index.php">Continue Shopping</a>
            </div>
        <?php else: ?>
            <form method="post" action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>">
                <input type="hidden" name="update_cart" value="1">

                <table class="cart-table">
                    <thead>
                        <tr>
                            <th>Product</th>
                            <th>Price</th>
                            <th>Quantity</th>
                            <th>Total</th>
                            <th>Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($_SESSION['cart'] as $product_id => $quantity): ?>
                            <?php if (isset($products[$product_id])): ?>
                                <tr>
                                    <td>
                                        <div style="display: flex; align-items: center; gap: 15px;">
                                            <img src="<?php echo htmlspecialchars($products[$product_id]['image']); ?>" alt="<?php echo htmlspecialchars($products[$product_id]['name']); ?>" class="cart-item-image">
                                            <div>
                                                <div class="cart-item-name"><?php echo htmlspecialchars($products[$product_id]['name']); ?></div>
                                            </div>
                                        </div>
                                    </td>
                                    <td class="cart-item-price">$<?php echo number_format($products[$product_id]['price'], 2); ?></td>
                                    <td>
                                        <input type="number" name="quantity[<?php echo $product_id; ?>]" value="<?php echo $quantity; ?>" min="1" class="quantity-input">
                                    </td>
                                    <td class="cart-item-price">$<?php echo number_format($products[$product_id]['price'] * $quantity, 2); ?></td>
                                    <td>
                                        <a href="?remove=<?php echo $product_id; ?>" class="remove-btn">Remove</a>
                                    </td>
                                </tr>
                            <?php endif; ?>
                        <?php endforeach; ?>
                    </tbody>
                </table>

                <div class="cart-total">
                    <h2>Cart Total</h2>
                    <div class="total-price">$<?php echo number_format($cart_total, 2); ?></div>
                </div>

                <div class="cart-actions">
                    <button type="submit" class="btn btn-secondary">Update Cart</button>
                    <a href="checkout.php" class="btn btn-primary">Proceed to Checkout</a>
                </div>
            </form>

            <div class="continue-shopping">
                <a href="index.php">← Continue Shopping</a>
            </div>
        <?php endif; ?>
    </div>
</body>
</html>
