<?php
require_once 'config.php';

$is_logged_in = isset($_SESSION['user_id']);
$user_name = isset($_SESSION['user_name']) ? $_SESSION['user_name'] : '';
$user_id = isset($_SESSION['user_id']) ? $_SESSION['user_id'] : 0;

// Get product ID from URL
$product_id = isset($_GET['id']) ? intval($_GET['id']) : 0;

if ($product_id === 0) {
    header("Location: index.php");
    exit();
}

// Fetch product details
$product_query = "SELECT p.*, c.name as category_name, c.slug as category_slug 
                  FROM products p 
                  LEFT JOIN categories c ON p.category_id = c.id 
                  WHERE p.id = $product_id AND p.is_active = 1 
                  LIMIT 1";
$product_result = mysqli_query($conn, $product_query);

if (!$product_result || mysqli_num_rows($product_result) === 0) {
    header("Location: index.php");
    exit();
}

$product = mysqli_fetch_assoc($product_result);

// Fetch related products (same category)
$related_query = "SELECT p.*, c.name as category_name 
                  FROM products p 
                  LEFT JOIN categories c ON p.category_id = c.id 
                  WHERE p.category_id = {$product['category_id']} 
                  AND p.id != $product_id 
                  AND p.is_active = 1 
                  ORDER BY RAND() 
                  LIMIT 4";
$related_result = mysqli_query($conn, $related_query);

// Get wishlist count
$wishlist_count = 0;
$cart_count = 0;

if ($is_logged_in) {
    $wishlist_query = "SELECT COUNT(*) as count FROM wishlist WHERE user_id = $user_id";
    $wishlist_result = mysqli_query($conn, $wishlist_query);
    if ($wishlist_result) {
        $wishlist_count = mysqli_fetch_assoc($wishlist_result)['count'];
    }
    
    $cart_query = "SELECT COUNT(*) as count FROM cart WHERE user_id = $user_id";
    $cart_result = mysqli_query($conn, $cart_query);
    if ($cart_result) {
        $cart_count = mysqli_fetch_assoc($cart_result)['count'];
    }
} else {
    if (isset($_SESSION['cart']) && !empty($_SESSION['cart'])) {
        $cart_count = count($_SESSION['cart']);
    }
}

$is_out_of_stock = $product['stock_quantity'] <= 0;
$is_low_stock = $product['stock_quantity'] > 0 && $product['stock_quantity'] <= $product['low_stock_threshold'];
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo htmlspecialchars($product['name']); ?> - Furnessence</title>
    
    <link rel="shortcut icon" href="./favicon.svg" type="image/svg+xml">
    <link rel="stylesheet" href="./assests/css/style.css">
    <link rel="stylesheet" href="./assests/css/autocomplete.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Mr+De+Haviland&family=Roboto:wght@400;500;700&display=swap" rel="stylesheet">
    
    <style>
        .product-details-container {
            max-width: 1200px;
            margin: 120px auto 60px;
            padding: 0 20px;
        }
        
        .breadcrumb {
            display: flex;
            gap: 10px;
            margin-bottom: 30px;
            font-size: 14px;
            color: #666;
        }
        
        .breadcrumb a {
            color: #666;
            text-decoration: none;
        }
        
        .breadcrumb a:hover {
            color: var(--bittersweet);
        }
        
        .product-details-grid {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 60px;
            margin-bottom: 80px;
        }
        
        .product-image-section {
            position: sticky;
            top: 100px;
            height: fit-content;
        }
        
        .product-main-image {
            width: 100%;
            aspect-ratio: 1;
            object-fit: cover;
            border-radius: 12px;
            border: 1px solid #eee;
        }
        
        .product-info-section {
            padding: 20px 0;
        }
        
        .product-category {
            color: hsl(174, 64%, 61%);
            font-size: 14px;
            font-weight: 500;
            text-transform: uppercase;
            letter-spacing: 1px;
            margin-bottom: 10px;
        }
        
        .product-title {
            font-size: 36px;
            font-weight: 700;
            margin-bottom: 20px;
            color: hsl(0, 0%, 13%);
            line-height: 1.3;
        }
        
        .product-price {
            font-size: 32px;
            font-weight: 700;
            color: hsl(12, 100%, 50%);
            margin-bottom: 20px;
        }
        
        .stock-status {
            display: inline-block;
            padding: 8px 16px;
            border-radius: 6px;
            font-size: 14px;
            font-weight: 600;
            margin-bottom: 30px;
        }
        
        .stock-status.in-stock {
            background: #d4edda;
            color: #155724;
        }
        
        .stock-status.low-stock {
            background: #fff3cd;
            color: #856404;
        }
        
        .stock-status.out-of-stock {
            background: #f8d7da;
            color: #721c24;
        }
        
        .product-description {
            line-height: 1.8;
            color: #555;
            margin-bottom: 30px;
            font-size: 16px;
        }
        
        .product-meta {
            border-top: 1px solid #eee;
            border-bottom: 1px solid #eee;
            padding: 20px 0;
            margin-bottom: 30px;
        }
        
        .meta-item {
            display: flex;
            justify-content: space-between;
            padding: 8px 0;
            font-size: 15px;
        }
        
        .meta-label {
            color: #666;
            font-weight: 500;
        }
        
        .meta-value {
            color: #333;
            font-weight: 600;
        }
        
        .product-actions {
            display: flex;
            gap: 15px;
            margin-bottom: 20px;
        }
        
        .btn-add-cart {
            flex: 1;
            padding: 16px 32px;
            background: hsl(174, 64%, 61%);
            color: white;
            border: none;
            border-radius: 8px;
            font-size: 16px;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 10px;
        }
        
        .btn-add-cart:hover:not(:disabled) {
            background: hsl(12, 100%, 50%);
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(255, 99, 71, 0.3);
        }
        
        .btn-add-cart:disabled {
            background: #ccc;
            cursor: not-allowed;
            color: #999;
        }
        
        .btn-wishlist {
            padding: 16px 24px;
            background: white;
            color: hsl(174, 64%, 61%);
            border: 2px solid hsl(174, 64%, 61%);
            border-radius: 8px;
            font-size: 20px;
            cursor: pointer;
            transition: all 0.3s;
        }
        
        .btn-wishlist:hover {
            background: hsl(174, 64%, 61%);
            color: white;
            transform: translateY(-2px);
        }
        
        .related-products-section {
            margin-top: 80px;
        }
        
        .section-title {
            text-align: center;
            font-size: 32px;
            font-weight: 700;
            margin-bottom: 50px;
            color: hsl(0, 0%, 13%);
        }
        
        .related-grid {
            display: grid;
            grid-template-columns: repeat(4, 1fr);
            gap: 30px;
        }
        
        @media (max-width: 992px) {
            .product-details-grid {
                grid-template-columns: 1fr;
                gap: 40px;
            }
            
            .product-image-section {
                position: static;
            }
            
            .related-grid {
                grid-template-columns: repeat(2, 1fr);
            }
        }
        
        @media (max-width: 576px) {
            .product-title {
                font-size: 28px;
            }
            
            .product-price {
                font-size: 24px;
            }
            
            .product-actions {
                flex-direction: column;
            }
            
            .related-grid {
                grid-template-columns: 1fr;
            }
        }
    </style>
</head>
<body>

<?php include 'includes/header.php'; ?>

<main>
    <div class="product-details-container">
        <!-- Breadcrumb -->
        <nav class="breadcrumb">
            <a href="index.php">Home</a>
            <span>/</span>
            <a href="index.php#product">Products</a>
            <span>/</span>
            <span><?php echo htmlspecialchars($product['name']); ?></span>
        </nav>
        
        <!-- Product Details Grid -->
        <div class="product-details-grid">
            <!-- Product Image -->
            <div class="product-image-section">
                <?php 
                $product_image = !empty($product['image']) ? htmlspecialchars($product['image']) : 'assests/images/products/product-1.jpg';
                ?>
                <img src="<?php echo $product_image; ?>" 
                     alt="<?php echo htmlspecialchars($product['name']); ?>" 
                     class="product-main-image">
            </div>
            
            <!-- Product Info -->
            <div class="product-info-section">
                <div class="product-category">
                    <?php echo htmlspecialchars($product['category_name'] ?? 'Uncategorized'); ?>
                </div>
                
                <h1 class="product-title">
                    <?php echo htmlspecialchars($product['name']); ?>
                </h1>
                
                <div class="product-price">
                    Rs <?php echo number_format($product['price'], 2); ?>
                </div>
                
                <!-- Stock Status -->
                <?php if ($is_out_of_stock): ?>
                    <div class="stock-status out-of-stock">
                        <i class="fas fa-times-circle"></i> Out of Stock
                    </div>
                <?php elseif ($is_low_stock): ?>
                    <div class="stock-status low-stock">
                        <i class="fas fa-exclamation-triangle"></i> Only <?php echo $product['stock_quantity']; ?> left
                    </div>
                <?php else: ?>
                    <div class="stock-status in-stock">
                        <i class="fas fa-check-circle"></i> In Stock (<?php echo $product['stock_quantity']; ?> available)
                    </div>
                <?php endif; ?>
                
                <!-- Description -->
                <div class="product-description">
                    <?php 
                    if (!empty($product['description'])) {
                        echo nl2br(htmlspecialchars($product['description']));
                    } else {
                        echo "Premium quality furniture piece that combines style and functionality. Perfect for modern homes.";
                    }
                    ?>
                </div>
                
                <!-- Product Meta -->
                <div class="product-meta">
                    <div class="meta-item">
                        <span class="meta-label">SKU:</span>
                        <span class="meta-value">#<?php echo str_pad($product['id'], 5, '0', STR_PAD_LEFT); ?></span>
                    </div>
                    <div class="meta-item">
                        <span class="meta-label">Category:</span>
                        <span class="meta-value"><?php echo htmlspecialchars($product['category_name'] ?? 'Uncategorized'); ?></span>
                    </div>
                    <div class="meta-item">
                        <span class="meta-label">Availability:</span>
                        <span class="meta-value">
                            <?php echo $is_out_of_stock ? 'Out of Stock' : 'In Stock'; ?>
                        </span>
                    </div>
                </div>
                
                <!-- Add to Cart & Wishlist -->
                <div class="product-actions">
                    <button class="btn-add-cart add-to-cart-btn" 
                            data-product-id="<?php echo $product['id']; ?>"
                            data-product-name="<?php echo htmlspecialchars($product['name']); ?>"
                            data-product-price="<?php echo $product['price']; ?>"
                            data-product-image="<?php echo htmlspecialchars($product['image']); ?>"
                            <?php echo $is_out_of_stock ? 'disabled' : ''; ?>>
                        <i class="fas fa-shopping-cart"></i>
                        <?php echo $is_out_of_stock ? 'Out of Stock' : 'Add to Cart'; ?>
                    </button>
                    
                    <button class="btn-wishlist add-to-wishlist-btn"
                            data-product-id="<?php echo $product['id']; ?>"
                            data-product-name="<?php echo htmlspecialchars($product['name']); ?>"
                            data-product-price="<?php echo $product['price']; ?>"
                            data-product-image="<?php echo htmlspecialchars($product['image']); ?>"
                            title="Add to Wishlist">
                        <i class="fas fa-heart"></i>
                    </button>
                </div>
            </div>
        </div>
        
        <!-- Related Products -->
        <?php if (mysqli_num_rows($related_result) > 0): ?>
        <div class="related-products-section">
            <h2 class="section-title">Related Products</h2>
            
            <div class="related-grid">
                <?php while ($related = mysqli_fetch_assoc($related_result)): 
                    $related_image = !empty($related['image']) ? htmlspecialchars($related['image']) : 'assests/images/products/product-1.jpg';
                    $related_out_of_stock = $related['stock_quantity'] <= 0;
                ?>
                <div class="product-card">
                    <a href="product_details.php?id=<?php echo $related['id']; ?>" class="card-banner img-holder" style="--width: 300; --height: 300;">
                        <img src="<?php echo $related_image; ?>" 
                             width="300" height="300" loading="lazy"
                             alt="<?php echo htmlspecialchars($related['name']); ?>" 
                             class="img-cover">
                        
                        <?php if ($related_out_of_stock): ?>
                            <div class="card-badge">Out of Stock</div>
                        <?php endif; ?>
                    </a>
                    
                    <div class="card-content">
                        <h3 class="h3">
                            <a href="product_details.php?id=<?php echo $related['id']; ?>" class="card-title">
                                <?php echo htmlspecialchars($related['name']); ?>
                            </a>
                        </h3>
                        <div class="card-price">
                            <data class="price" value="<?php echo $related['price']; ?>">
                                Rs <?php echo number_format($related['price'], 2); ?>
                            </data>
                        </div>
                    </div>
                </div>
                <?php endwhile; ?>
            </div>
        </div>
        <?php endif; ?>
    </div>
</main>

<script type="module" src="https://unpkg.com/ionicons@5.5.2/dist/ionicons/ionicons.esm.js"></script>
<script nomodule src="https://unpkg.com/ionicons@5.5.2/dist/ionicons/ionicons.js"></script>

<script src="./assests/js/script.js?v=6.0"></script>
<script src="./assests/js/autocomplete.js"></script>

<script>
// Debug: Check if buttons exist
document.addEventListener('DOMContentLoaded', function() {
    console.log('Product Details Page Loaded');
    
    const cartBtn = document.querySelector('.btn-add-cart');
    const wishlistBtn = document.querySelector('.btn-wishlist');
    
    console.log('Cart button found:', cartBtn);
    console.log('Wishlist button found:', wishlistBtn);
    
    if (cartBtn) {
        console.log('Cart button data:', {
            id: cartBtn.getAttribute('data-product-id'),
            name: cartBtn.getAttribute('data-product-name'),
            price: cartBtn.getAttribute('data-product-price'),
            image: cartBtn.getAttribute('data-product-image')
        });
    }
    
    // Re-initialize if needed
    if (typeof initializeProductDetailsButtons === 'function') {
        console.log('Re-initializing product details buttons');
        initializeProductDetailsButtons();
    }
});
</script>
</body>
</html>
