<?php
require_once 'config.php';

$is_logged_in = isset($_SESSION['user_id']);
$user_name = isset($_SESSION['user_name']) ? $_SESSION['user_name'] : '';
$user_id = isset($_SESSION['user_id']) ? $_SESSION['user_id'] : 0;

// Get search query
$search_query = isset($_GET['q']) ? trim($_GET['q']) : '';
$search_term = mysqli_real_escape_string($conn, $search_query);

// Get counts for header
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

// Search products
$products = [];
$total_results = 0;

if (!empty($search_term)) {
    $search_sql = "SELECT p.*, c.name as category_name, c.slug as category_slug 
                   FROM products p 
                   LEFT JOIN categories c ON p.category_id = c.id 
                   WHERE p.is_active = 1 
                   AND (
                       p.name LIKE '%$search_term%' 
                       OR p.description LIKE '%$search_term%'
                       OR c.name LIKE '%$search_term%'
                   )
                   ORDER BY p.created_at DESC";
    
    $search_result = mysqli_query($conn, $search_sql);
    
    if ($search_result) {
        $total_results = mysqli_num_rows($search_result);
        while ($row = mysqli_fetch_assoc($search_result)) {
            $products[] = $row;
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Search Results - Furnessence</title>
    
    <link rel="shortcut icon" href="./favicon.svg" type="image/svg+xml">
    <link rel="stylesheet" href="./assests/css/style.css?v=14.0">
    <link rel="stylesheet" href="./assests/css/search.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Mr+De+Haviland&family=Roboto:wght@400;500;700&display=swap" rel="stylesheet">
    
    <script type="module" src="https://unpkg.com/ionicons@5.5.2/dist/ionicons/ionicons.esm.js"></script>
    <script nomodule src="https://unpkg.com/ionicons@5.5.2/dist/ionicons/ionicons.js"></script>
</head>
<body>
    
    <!-- HEADER -->
    <header class="header" data-header>
        <div class="container">
            <a href="index.php" class="logo"><i class="fas fa-couch"></i> Furnessence</a>

            <div class="input-wrapper">
                <form action="search.php" method="GET" class="search-form">
                    <input type="search" name="q" placeholder="Search Anything..." class="input-field" value="<?php echo htmlspecialchars($search_query); ?>" required>
                    <button type="submit" class="search-btn" aria-label="Search">
                        <ion-icon name="search-outline"></ion-icon>
                    </button>
                </form>
            </div>

            <div class="header-action">
                <?php if ($is_logged_in): ?>
                    <div class="user-dropdown-wrapper">
                        <button class="header-action-btn user-icon-btn" aria-label="user" aria-expanded="false" data-user-dropdown-btn>
                            <ion-icon name="person-outline" aria-hidden="true"></ion-icon>
                        </button>
                        <div class="user-dropdown-menu" data-user-dropdown>
                            <div class="user-dropdown-header">
                                <div class="user-avatar">
                                    <ion-icon name="person-circle-outline"></ion-icon>
                                </div>
                                <div class="user-info">
                                    <p class="user-name"><?php echo htmlspecialchars($user_name); ?></p>
                                    <p class="user-email"><?php echo htmlspecialchars($_SESSION['user_email']); ?></p>
                                </div>
                            </div>
                            <ul class="user-dropdown-list">
                                <li>
                                    <a href="profile.php" class="user-dropdown-link">
                                        <ion-icon name="person-outline"></ion-icon>
                                        <span>My Profile</span>
                                    </a>
                                </li>
                                <li>
                                    <a href="wishlist.php" class="user-dropdown-link">
                                        <ion-icon name="heart-outline"></ion-icon>
                                        <span>Wishlist</span>
                                        <?php if ($wishlist_count > 0): ?>
                                        <span class="dropdown-badge"><?php echo $wishlist_count; ?></span>
                                        <?php endif; ?>
                                    </a>
                                </li>
                                <li>
                                    <a href="cart.php" class="user-dropdown-link">
                                        <ion-icon name="bag-handle-outline"></ion-icon>
                                        <span>My Cart</span>
                                        <?php if ($cart_count > 0): ?>
                                        <span class="dropdown-badge"><?php echo $cart_count; ?></span>
                                        <?php endif; ?>
                                    </a>
                                </li>
                            </ul>
                            <div class="user-dropdown-footer">
                                <a href="logout.php" class="user-dropdown-logout">
                                    <ion-icon name="log-out-outline"></ion-icon>
                                    <span>Logout</span>
                                </a>
                            </div>
                        </div>
                    </div>
                <?php else: ?>
                    <button class="header-action-btn" aria-label="login" title="Login" onclick="window.location.href='login.php'">
                        <ion-icon name="log-in-outline" aria-hidden="true"></ion-icon>
                    </button>
                <?php endif; ?>

                <button class="header-action-btn" aria-label="cart" title="Shopping Cart" onclick="window.location.href='cart.php'">
                    <ion-icon name="bag-handle-outline" aria-hidden="true"></ion-icon>
                    <?php if ($cart_count > 0): ?>
                    <span class="btn-badge"><?php echo $cart_count; ?></span>
                    <?php endif; ?>
                </button>

                <button class="header-action-btn nav-toggle-btn" aria-label="open menu" data-nav-toggler>
                    <ion-icon name="menu-outline" aria-hidden="true"></ion-icon>
                </button>
            </div>
        </div>
    </header>

    <!-- SIDEBAR -->
    <div class="sidebar" data-navbar>
        <button class="nav-close-btn" aria-label="close menu" data-nav-toggler>
            <ion-icon name="close-outline" aria-hidden="true"></ion-icon>
        </button>

        <div class="wrapper">
            <ul class="sidebar-list">
                <li>
                    <p class="sidebar-list-title">Language</p>
                </li>
                <li>
                    <a href="#" class="sidebar-link">English</a>
                </li>
                <li>
                    <a href="#" class="sidebar-link">French</a>
                </li>
                <li>
                    <a href="#" class="sidebar-link">Arabric</a>
                </li>
            </ul>

            <ul class="sidebar-list">
                <li>
                    <p class="sidebar-list-title">Currency</p>
                </li>
                <li>
                    <a href="#" class="sidebar-link">USD - US Dollar</a>
                </li>
                <li>
                    <a href="#" class="sidebar-link">Euro</a>
                </li>
                <li>
                    <a href="#" class="sidebar-link">Pound</a>
                </li>
            </ul>
        </div>

        <nav class="navbar">
            <ul class="navbar-list">
                <li class="navbar-item">
                    <a href="index.php" class="navbar-link">Home</a>
                </li>
                <li class="navbar-item">
                    <a href="index.php#about" class="navbar-link">About</a>
                </li>
                <li class="navbar-item">
                    <a href="index.php#product" class="navbar-link">Product</a>
                </li>
                <li class="navbar-item">
                    <a href="index.php#blog" class="navbar-link">Blogs</a>
                </li>
            </ul>
        </nav>

        <ul class="contact-list">
            <li>
                <p class="contact-list-title">Contact Us</p>
            </li>
            <li class="contact-item">
                <address class="address">
                    bharatpur-10,chitwan in bishal chowk
                </address>
            </li>
            <li class="contact-item">
                <a href="mailto:sajiblama11@gmail.com" class="contact-link">support.center@furnessence.co</a>
            </li>
            <li class="contact-item">
                <a href="tel:9829294061" class="contact-link"> +9779829294061</a>
            </li>
        </ul>

        <div class="social-wrapper">
            <p class="social-list-title">Follow US On Socials</p>
            <ul class="social-list">
                <li>
                    <a href="#" class="social-link">
                        <ion-icon name="logo-facebook"></ion-icon>
                    </a>
                </li>
                <li>
                    <a href="#" class="social-link">
                        <ion-icon name="logo-twitter"></ion-icon>
                    </a>
                </li>
                <li>
                    <a href="#" class="social-link">
                        <ion-icon name="logo-instagram"></ion-icon>
                    </a>
                </li>
                <li>
                    <a href="#" class="social-link">
                        <ion-icon name="logo-youtube"></ion-icon>
                    </a>
                </li>
            </ul>
        </div>
    </div>

    <!-- OVERLAY -->
    <div class="overlay" data-overlay data-nav-toggler></div>
    
    <!-- SEARCH RESULTS -->
    <section class="search-page">
        <div class="container">
            <div class="search-header">
                <?php if (!empty($search_query)): ?>
                    <h1>Search Results</h1>
                    <p class="search-info">
                        Found <strong><?php echo $total_results; ?></strong> result(s) for 
                        <span class="search-query">"<?php echo htmlspecialchars($search_query); ?>"</span>
                    </p>
                <?php else: ?>
                    <h1>Search Products</h1>
                    <p class="search-info">Enter a search term to find products</p>
                <?php endif; ?>
            </div>
            
            <?php if (!empty($search_query)): ?>
                <?php if ($total_results > 0): ?>
                    <ul class="grid-list product-list search-results">
                        <?php foreach ($products as $product): ?>
                            <li>
                                <div class="product-card">
                                    <a href="#" class="card-banner img-holder has-before" style="--width: 300; --height: 300;">
                                        <img src="<?php echo htmlspecialchars($product['image']); ?>" 
                                             width="300" height="300" loading="lazy"
                                             alt="<?php echo htmlspecialchars($product['name']); ?>" 
                                             class="img-cover"
                                             onerror="this.src='./assests/images/products/placeholder.jpg'">
                                        
                                        <ul class="card-action-list">
                                            <li>
                                                <button class="card-action-btn add-to-cart-btn" 
                                                        data-product-id="<?php echo $product['id']; ?>"
                                                        data-product-name="<?php echo htmlspecialchars($product['name']); ?>"
                                                        data-product-price="<?php echo $product['price']; ?>"
                                                        data-product-image="<?php echo htmlspecialchars($product['image']); ?>"
                                                        aria-label="add to cart" title="Add to cart">
                                                    <ion-icon name="bag-handle-outline" aria-hidden="true"></ion-icon>
                                                </button>
                                            </li>
                                            
                                            <?php if ($is_logged_in): ?>
                                            <li>
                                                <button class="card-action-btn add-to-wishlist-btn" 
                                                        data-product-id="<?php echo $product['id']; ?>"
                                                        data-product-name="<?php echo htmlspecialchars($product['name']); ?>"
                                                        data-product-price="<?php echo $product['price']; ?>"
                                                        data-product-image="<?php echo htmlspecialchars($product['image']); ?>"
                                                        aria-label="add to wishlist" title="Add to wishlist">
                                                    <ion-icon name="heart-outline" aria-hidden="true"></ion-icon>
                                                </button>
                                            </li>
                                            <?php endif; ?>
                                        </ul>
                                        
                                        <?php if (!empty($product['category_name'])): ?>
                                            <div class="card-badge"><?php echo htmlspecialchars($product['category_name']); ?></div>
                                        <?php endif; ?>
                                    </a>
                                    
                                    <div class="card-content">
                                        <h3 class="h3">
                                            <a href="#" class="card-title"><?php echo htmlspecialchars($product['name']); ?></a>
                                        </h3>
                                        
                                        <div class="card-price">
                                            <data class="price" value="<?php echo $product['price']; ?>">$<?php echo number_format($product['price'], 2); ?></data>
                                        </div>
                                    </div>
                                </div>
                            </li>
                        <?php endforeach; ?>
                    </ul>
                <?php else: ?>
                    <div class="no-results">
                        <ion-icon name="search-outline"></ion-icon>
                        <h2>No Results Found</h2>
                        <p>Sorry, we couldn't find any products matching "<strong><?php echo htmlspecialchars($search_query); ?></strong>"</p>
                        <p>Try different keywords or browse our categories</p>
                        <a href="index.php" class="back-btn">
                            <ion-icon name="arrow-back-outline"></ion-icon>
                            Back to Home
                        </a>
                    </div>
                <?php endif; ?>
            <?php endif; ?>
        </div>
    </section>
    
    <script src="./assests/js/script.js?v=4.0" defer></script>
    
    <!-- ionicon link -->
    <script type="module" src="https://unpkg.com/ionicons@5.5.2/dist/ionicons/ionicons.esm.js"></script>
    <script nomodule src="https://unpkg.com/ionicons@5.5.2/dist/ionicons/ionicons.js"></script>
    
    <!-- Reinitialize product cards for search page -->
    <script>
    document.addEventListener('DOMContentLoaded', function() {
        console.log('Search page loaded');
        
        // Wait a bit for ionicons to load
        setTimeout(function() {
            if (typeof initializeProductCards === 'function') {
                initializeProductCards();
                console.log('Product cards reinitialized for search page');
            }
        }, 500);
    });
    </script>
</body>
</html>
