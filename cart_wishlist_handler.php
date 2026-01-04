<?php
require_once 'config.php';

header('Content-Type: application/json');

$is_logged_in = isset($_SESSION['user_id']);
$user_id = $is_logged_in ? $_SESSION['user_id'] : 0;

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['success' => false, 'message' => 'Invalid request method']);
    exit();
}

$action = isset($_POST['action']) ? $_POST['action'] : '';
$product_id = isset($_POST['product_id']) ? intval($_POST['product_id']) : 0;
$product_name = isset($_POST['product_name']) ? mysqli_real_escape_string($conn, $_POST['product_name']) : '';
$product_price = isset($_POST['product_price']) ? floatval($_POST['product_price']) : 0;
$product_image = isset($_POST['product_image']) ? mysqli_real_escape_string($conn, $_POST['product_image']) : '';

if ($product_id === 0 || empty($product_name) || $product_price === 0) {
    echo json_encode(['success' => false, 'message' => 'Invalid product data']);
    exit();
}

if ($action === 'add_to_wishlist') {
    // Wishlist requires login
    if (!$is_logged_in) {
        echo json_encode(['success' => false, 'message' => 'Please login to add items to wishlist']);
        exit();
    }
    
    // Check if already in wishlist
    $check_query = "SELECT id FROM wishlist WHERE user_id = $user_id AND product_id = $product_id LIMIT 1";
    $check_result = mysqli_query($conn, $check_query);
    
    if (mysqli_num_rows($check_result) > 0) {
        echo json_encode(['success' => false, 'message' => 'Already in wishlist']);
        exit();
    }
    
    // Add to wishlist
    $insert_query = "INSERT INTO wishlist (user_id, product_id, product_name, product_price, product_image) VALUES ($user_id, $product_id, '$product_name', $product_price, '$product_image')";
    
    if (mysqli_query($conn, $insert_query)) {
        // Get new count
        $count_query = "SELECT COUNT(*) as count FROM wishlist WHERE user_id = $user_id";
        $count_result = mysqli_query($conn, $count_query);
        $wishlist_count = mysqli_fetch_assoc($count_result)['count'];
        
        echo json_encode(['success' => true, 'message' => 'Added to wishlist!', 'wishlist_count' => $wishlist_count]);
    } else {
        echo json_encode(['success' => false, 'message' => 'Failed to add to wishlist']);
    }
    
} elseif ($action === 'add_to_cart') {
    if ($is_logged_in) {
        // For logged-in users, use database
        $check_query = "SELECT id, quantity FROM cart WHERE user_id = $user_id AND product_id = $product_id LIMIT 1";
        $check_result = mysqli_query($conn, $check_query);
        
        if (mysqli_num_rows($check_result) > 0) {
            // Update quantity
            $update_query = "UPDATE cart SET quantity = quantity + 1 WHERE user_id = $user_id AND product_id = $product_id";
            mysqli_query($conn, $update_query);
            $message = 'Quantity updated in cart!';
        } else {
            // Add to cart
            $insert_query = "INSERT INTO cart (user_id, product_id, product_name, product_price, product_image, quantity) VALUES ($user_id, $product_id, '$product_name', $product_price, '$product_image', 1)";
            mysqli_query($conn, $insert_query);
            $message = 'Added to cart!';
        }
        
        // Get new count
        $count_query = "SELECT COUNT(*) as count FROM cart WHERE user_id = $user_id";
        $count_result = mysqli_query($conn, $count_query);
        $cart_count = mysqli_fetch_assoc($count_result)['count'];
    } else {
        // For guest users, use session
        if (!isset($_SESSION['cart'])) {
            $_SESSION['cart'] = [];
        }
        
        // Check if product already in session cart
        $found = false;
        foreach ($_SESSION['cart'] as &$item) {
            if ($item['product_id'] == $product_id) {
                $item['quantity']++;
                $found = true;
                $message = 'Quantity updated in cart!';
                break;
            }
        }
        
        if (!$found) {
            // Add new item to session cart
            $_SESSION['cart'][] = [
                'product_id' => $product_id,
                'product_name' => $product_name,
                'product_price' => $product_price,
                'product_image' => $product_image,
                'quantity' => 1
            ];
            $message = 'Added to cart!';
        }
        
        $cart_count = count($_SESSION['cart']);
    }
    
    echo json_encode(['success' => true, 'message' => $message, 'cart_count' => $cart_count]);
    
} else {
    echo json_encode(['success' => false, 'message' => 'Invalid action']);
}
?>
