<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="UTF-8">
  <meta http-equiv="X-UA-Compatible" content="IE=edge">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">

  <!-- 
    - primary meta tag
  -->
  <title>furnessence - Get Quality Furniture</title>
  <meta name="title" content="furnessence - Get Quality Furniture">
  <meta name="description" content="This is an eCommerce html template made by codewithsadee">

  <!-- 
    - favicon
  -->
  <link rel="shortcut icon" href="./favicon.svg" type="image/svg+xml">

  <!-- 
    - custom css link
  -->
  <link rel="stylesheet" href="./assests/css/style.css?v=14.0">

  <!-- 
    - font awesome
  -->
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">

  <!-- 
    - google font link
  -->
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link href="https://fonts.googleapis.com/css2?family=Mr+De+Haviland&family=Roboto:wght@400;500;700&display=swap"
    rel="stylesheet">

  <!-- 
    - preload images
  -->
  <link rel="preload" as="image" href="./assests/images/hero-product-1.jpg">
  <link rel="preload" as="image" href="./assests/images/hero-product-2.jpg">
  <link rel="preload" as="image" href="./assests/images/hero-product-3.jpg">
  <link rel="preload" as="image" href="./assests/images/hero-product-4.jpg">
  <link rel="preload" as="image" href="./assests/images/hero-product-5.jpg">

</head>

<body id="top">
<?php 
require_once 'config.php';
$is_logged_in = isset($_SESSION['user_id']);
$user_name = isset($_SESSION['user_name']) ? $_SESSION['user_name'] : '';
$user_id = isset($_SESSION['user_id']) ? $_SESSION['user_id'] : 0;

// Get products from database
$products_query = "SELECT p.*, c.name as category_name, c.slug as category_slug 
                  FROM products p 
                  LEFT JOIN categories c ON p.category_id = c.id 
                  WHERE p.is_active = 1 
                  ORDER BY p.created_at DESC 
                  LIMIT 20";
$products_result = mysqli_query($conn, $products_query);

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
    // For guest users, count from session
    if (isset($_SESSION['cart']) && !empty($_SESSION['cart'])) {
        $cart_count = count($_SESSION['cart']);
    }
}
?>

  <!-- 
    - #HEADER
  -->

  <header class="header" data-header>
    <div class="container">

      <a href="index.php" class="logo"><i class="fas fa-couch"></i> Furnessence</a>

      <div class="input-wrapper">
        <form action="search.php" method="GET" class="search-form">
          <input type="search" name="q" placeholder="Search Anything..." class="input-field" required>
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





  <!-- 
    - #SIDEBAR
  -->

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
          <a href="#home" class="navbar-link" data-nav-link>Home</a>
        </li>

        <li class="navbar-item">
          <a href="#about" class="navbar-link" data-nav-link>About</a>
        </li>

        <li class="navbar-item">
          <a href="#product" class="navbar-link" data-nav-link>Product</a>
        </li>

        <li class="navbar-item">
          <a href="#blog" class="navbar-link" data-nav-link>Blogs</a>
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
        <a href="sajiblama11@gmail.com" class="contact-link">support.center@furnessence.co</a>
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
            <ion-icon name="logo-tumblr"></ion-icon>
          </a>
        </li>

      </ul>

    </div>

  </div>

  <div class="overlay" data-overlay data-nav-toggler></div>





  <main>
    <article>

      <!-- 
        - #HERO
      -->

      <section class="section hero" id="home" aria-label="home">
        <div class="container">

          <ul class="hero-list">

            <li>
              <div class="hero-card">

                <figure class="card-banner img-holder hero-card-1">
                  <img src="./assests/images/products/product-1.jpg" width="285" height="396" alt="Modern Sofa"
                    class="img-cover">
                </figure>

                <div class="card-content">
                  <h3>
                    <a href="#" class="card-title">Modern Sofa</a>
                  </h3>

                  <p class="card-text">Living Room</p>
                </div>

              </div>
            </li>

            <li class="colspan-2">
              <div class="hero-card">

                <figure class="card-banner img-holder hero-card-2">
                  <img src="./assests/images/products/product-6.jpg" width="568" height="389" alt="Office Chair"
                    class="img-cover">
                </figure>

                <div class="card-content">
                  <h3>
                    <a href="#" class="card-title">Office Chair</a>
                  </h3>

                  <p class="card-text">Office</p>
                </div>

              </div>
            </li>

            <li>
              <div class="hero-card">

                <figure class="card-banner img-holder hero-card-3">
                  <img src="./assests/images/products/product-3.jpg" width="285" height="396" alt="Queen Bed Frame"
                    class="img-cover">
                </figure>

                <div class="card-content">
                  <h3>
                    <a href="#" class="card-title">Queen Bed Frame</a>
                  </h3>

                  <p class="card-text">Bedroom</p>
                </div>

              </div>
            </li>

            <li class="colspan-2">
              <div class="hero-card">

                <figure class="card-banner img-holder hero-card-4">
                  <img src="./assests/images/products/product-7.jpg" width="580" height="213" alt="Dining Table"
                    class="img-cover">
                </figure>

                <div class="card-content">
                  <h3>
                    <a href="#" class="card-title">Dining Table</a>
                  </h3>

                  <p class="card-text">Dining</p>
                </div>

              </div>
            </li>

            <li class="colspan-2">
              <div class="hero-card">

                <figure class="card-banner img-holder hero-card-5">
                  <img src="./assests/images/products/product-5.jpg" width="580" height="213" alt="Office Desk"
                    class="img-cover">
                </figure>

                <div class="card-content">
                  <h3>
                    <a href="#" class="card-title">Office Desk</a>
                  </h3>

                  <p class="card-text">Office</p>
                </div>

              </div>
            </li>

          </ul>

        </div>
      </section>





      <!-- 
        - #ABOUT
      -->

      <section class="section about" id="about" aria-label="about">
        <div class="container">

          <h2 class="section-title">Furnessence</h2>

          <p class="section-text">
            When you start with a portrait and search for a pure form, a clear volume, through successive eliminations,
            you arrive
            inevitably at the egg. Likewise, starting with the egg and following the same process in reverse, one
            finishes with the
            portrait.
          </p>

          <div class="about-card">
            <figure class="card-banner img-holder about-banner">
              <img src="./assests/images/about-banner.jpg" width="1170" height="450" loading="lazy" alt="Furnessence promo"
                class="img-cover">
            </figure>

            <button class="play-btn" aria-label="play video">
              <ion-icon name="play-circle-outline" aria-hidden="true"></ion-icon>
            </button>
          </div>

        </div>
      </section>





      <!-- 
        - #PRODUCTS
      -->

      <section class="section product" id="product" aria-label="product">
        <div class="container">

          <div class="title-wrapper">
            <h2 class="h2 section-title">Popular Products</h2>

            <ul class="filter-btn-list">

              <li class="filter-btn-item">
                <button class="filter-btn active" data-filter-btn="all">All Products</button>
              </li>

              <li class="filter-btn-item">
                <button class="filter-btn" data-filter-btn="living-room">Living Room</button>
              </li>

              <li class="filter-btn-item">
                <button class="filter-btn" data-filter-btn="bedroom">Bedroom</button>
              </li>

              <li class="filter-btn-item">
                <button class="filter-btn" data-filter-btn="office">Office</button>
              </li>

              <li class="filter-btn-item">
                <button class="filter-btn" data-filter-btn="dining">Dining</button>
              </li>

            </ul>
          </div>

          <ul class="grid-list product-list">

            <?php 
            if ($products_result && mysqli_num_rows($products_result) > 0) {
                while ($product = mysqli_fetch_assoc($products_result)) {
                    $is_out_of_stock = $product['stock_quantity'] == 0;
                    $is_low_stock = $product['stock_quantity'] > 0 && $product['stock_quantity'] <= $product['low_stock_threshold'];
                    $category_class = strtolower($product['category_slug'] ?? 'living-room');
                    // Check if image path already contains 'assests/' to avoid double path
                    $product_image = !empty($product['image']) ? './' . $product['image'] : './assests/images/products/product-1.jpg';
            ?>
            <li class="<?php echo $category_class; ?>" data-filter="<?php echo $category_class; ?>">
              <div class="product-card">

                <a href="#" class="card-banner img-holder has-before" style="--width: 300; --height: 300;">
                  <img src="<?php echo htmlspecialchars($product_image); ?>" width="300" height="300" loading="lazy"
                    alt="<?php echo htmlspecialchars($product['name']); ?>" class="img-cover">

                  <ul class="card-action-list">

                    <li>
                      <button class="card-action-btn add-to-cart-btn" 
                              data-product-id="<?php echo $product['id']; ?>"
                              data-product-name="<?php echo htmlspecialchars($product['name']); ?>"
                              data-product-price="<?php echo $product['price']; ?>"
                              data-product-image="<?php echo htmlspecialchars($product['image']); ?>"
                              aria-label="add to cart" title="Add to cart"
                              <?php echo $is_out_of_stock ? 'disabled' : ''; ?>>
                        <ion-icon name="bag-handle-outline" aria-hidden="true"></ion-icon>
                      </button>
                    </li>

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

                  </ul>

                  <?php if ($is_out_of_stock): ?>
                    <div class="card-badge">Out of Stock</div>
                  <?php elseif ($is_low_stock): ?>
                    <div class="card-badge orange">Low Stock</div>
                  <?php endif; ?>
                </a>

                <div class="card-content">
                  <h3 class="h3">
                    <a href="#" class="card-title"><?php echo htmlspecialchars($product['name']); ?></a>
                  </h3>

                  <div class="card-price">
                    <data class="price" value="<?php echo $product['price']; ?>">$<?php echo number_format($product['price'], 2); ?></data>
                  </div>
                  
                  <?php if ($is_low_stock && !$is_out_of_stock): ?>
                    <div class="stock-info stock-warning">
                      Only <?php echo $product['stock_quantity']; ?> left in stock
                    </div>
                  <?php endif; ?>
                </div>

              </div>
            </li>

            <?php 
                }
            } else {
                echo '<li class="no-products"><p>No products available at the moment. <br><br>Admin can add products from the <a href="Admin/Adminlogin.php" class="admin-link">admin panel</a>.</p></li>';
            }
            ?>

          </ul>

        </div>
      </section>





      <!-- 
        - #BLOG
      -->

      <section class="section blog" id="blog" aria-label="blog">
        <div class="container">

          <div class="title-wrapper">
            <h2 class="h2 section-title">Explore our blog</h2>

            <a href="#" class="btn-link">
              <span class="span">View All</span>

              <ion-icon name="arrow-forward" aria-hidden="true"></ion-icon>
            </a>
          </div>

          <ul class="grid-list">

            <li>
              <div class="blog-card">

                <div class="card-banner img-holder" style="--width: 374; --height: 243;">
                  <img src="./assests/images/blog-1.jpg" width="374" height="243" loading="lazy"
                    alt="Unique products that will impress your home in 2022." class="img-cover">

                  <a href="#" class="card-btn">
                    <span class="span">Read more</span>

                    <ion-icon name="add-outline" aria-hidden="true"></ion-icon>
                  </a>
                </div>

                <div class="card-content">

                  <h3 class="h3">
                    <a href="#" class="card-title">Unique products that will impress your home in 2025.</a>
                  </h3>

                  <ul class="card-meta-list">

                    <li class="card-meta-item">
                      <time class="card-meta-text" datetime="2022-09-27">November 27, 2025</time>
                    </li>

                    <li class="card-meta-item">
                      <a href="#" class="card-meta-text">Admin</a>
                    </li>

                    <li class="card-meta-item">
                      in <a href="#" class="card-meta-text">deco</a>
                    </li>

                  </ul>

                </div>

              </div>
            </li>

            <li>
              <div class="blog-card">

                <div class="card-banner img-holder" style="--width: 374; --height: 243;">
                  <img src="./assests/images/blog-2.jpg" width="374" height="243" loading="lazy"
                    alt="Navy Blue & White Striped Area Rugs" class="img-cover">

                  <a href="#" class="card-btn">
                    <span class="span">Read more</span>

                    <ion-icon name="add-outline" aria-hidden="true"></ion-icon>
                  </a>
                </div>

                <div class="card-content">

                  <h3 class="h3">
                    <a href="#" class="card-title">Navy Blue & White Striped Area Rugs</a>
                  </h3>

                  <ul class="card-meta-list">

                    <li class="card-meta-item">
                      <time class="card-meta-text" datetime="2022-09-25">November 25, 2025</time>
                    </li>

                    <li class="card-meta-item">
                      <a href="#" class="card-meta-text">Admin</a>
                    </li>

                    <li class="card-meta-item">
                      in <a href="#" class="card-meta-text">deco</a>
                    </li>

                  </ul>

                </div>

              </div>
            </li>

            <li>
              <div class="blog-card">

                <div class="card-banner img-holder" style="--width: 374; --height: 243;">
                  <img src="./assests/images/blog-3.jpg" width="374" height="243" loading="lazy"
                    alt="Woodex White Coated Staircase Floating" class="img-cover">

                  <a href="#" class="card-btn">
                    <span class="span">Read more</span>

                    <ion-icon name="add-outline" aria-hidden="true"></ion-icon>
                  </a>
                </div>

                <div class="card-content">

                  <h3 class="h3">
                    <a href="#" class="card-title">Woodex White Coated Staircase Floating</a>
                  </h3>

                  <ul class="card-meta-list">

                    <li class="card-meta-item">
                      <time class="card-meta-text" datetime="2025-09-18">November 18, 2025</time>
                    </li>

                    <li class="card-meta-item">
                      <a href="#" class="card-meta-text">Admin</a>
                    </li>

                    <li class="card-meta-item">
                      in <a href="#" class="card-meta-text">deco</a>
                    </li>

                  </ul>

                </div>

              </div>
            </li>

          </ul>

        </div>
      </section>





      <!-- 
        - #NEWSLETTER
      -->

      <section class="section newsletter" aria-label="newsletter">
        <div class="container">

          <div class="newsletter-card">

            <div class="card-content">
              <h2 class="h2 section-title">Our Newsletter</h2>

              <p class="section-text">
                Subscribe our newsletter and get discount 50% off
              </p>
            </div>

            <form action="" class="card-form">
              <input type="email" name="email_address" placeholder="Your email address" required class="email-field">

              <button type="submit" class="newsletter-btn" aria-label="subscribe">
                <ion-icon name="arrow-forward" aria-hidden="true"></ion-icon>
              </button>
            </form>

          </div>

        </div>
      </section>

    </article>
  </main>





  <!-- 
    - #FOOTER
  -->

  <footer class="footer">
    <div class="container">

      <div class="footer-top section">

        <div class="footer-brand">

          <a href="#" class="logo">Furnessence</a>

          <ul>

            <li class="footer-list-item">
              <ion-icon name="location-sharp" aria-hidden="true"></ion-icon>

              <address class="address">
                Furnessence, bharatpur-10, Nepal 2025
              </address>
            </li>

            <li class="footer-list-item">
              <ion-icon name="call-sharp" aria-hidden="true"></ion-icon>

              <a href="tel:+9779829294061" class="footer-link">+9779829294061</a>
            </li>

            <li>
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
                    <ion-icon name="logo-tumblr"></ion-icon>
                  </a>
                </li>

              </ul>
            </li>

          </ul>

        </div>

        <ul class="footer-list">

          <li>
            <p class="footer-list-title">Help & Information</p>
          </li>

          <li>
            <a href="#" class="footer-link">Help & Contact Us</a>
          </li>

          <li>
            <a href="#" class="footer-link">Returns & Refunds</a>
          </li>

          <li>
            <a href="#" class="footer-link">Online Stores</a>
          </li>

          <li>
            <a href="#" class="footer-link">Terms & Conditions</a>
          </li>

        </ul>

        <ul class="footer-list">

          <li>
            <p class="footer-list-title">About Us</p>
          </li>

          <li>
            <a href="#" class="footer-link">About Us</a>
          </li>

          <li>
            <a href="#" class="footer-link">What We Do</a>
          </li>

          <li>
            <a href="#" class="footer-link">FAQ Page</a>
          </li>

          <li>
            <a href="#" class="footer-link">Contact Us</a>
          </li>

        </ul>

      </div>

      <div class="footer-bottom">

        <p class="copyright">
          &copy; 2025 All Rights Reserved by <a href="#" class="copyright-link">sajib&subin</a>.
        </p>

      </div>

    </div>
  </footer>





  <!-- 
    - #BACK TO TOP
  -->

  <a href="#top" class="back-top-btn" aria-label="back to top" data-back-top-btn>
    <ion-icon name="arrow-up" aria-hidden="true"></ion-icon>
  </a>





  <!-- 
    - custom js link
  -->
  <script src="./assests/js/script.js?v=4.0" defer></script>

  <!-- 
    - ionicon link
  -->
  <script type="module" src="https://unpkg.com/ionicons@5.5.2/dist/ionicons/ionicons.esm.js"></script>
  <script nomodule src="https://unpkg.com/ionicons@5.5.2/dist/ionicons/ionicons.js"></script>

</body>

</html>
