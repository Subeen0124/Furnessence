<?php
// This file should be included after config.php and after setting up user variables
// Make sure these variables are set before including this file:
// $is_logged_in, $user_name, $user_id, $wishlist_count, $cart_count

if (!isset($is_logged_in)) {
    $is_logged_in = isset($_SESSION['user_id']);
}

if (!isset($user_name)) {
    $user_name = isset($_SESSION['user_name']) ? $_SESSION['user_name'] : '';
}

if (!isset($user_id)) {
    $user_id = isset($_SESSION['user_id']) ? $_SESSION['user_id'] : 0;
}

// Get counts if not already set
if (!isset($wishlist_count) && $is_logged_in) {
    $wishlist_query = "SELECT COUNT(*) as count FROM wishlist WHERE user_id = $user_id";
    $wishlist_result = mysqli_query($conn, $wishlist_query);
    $wishlist_count = $wishlist_result ? mysqli_fetch_assoc($wishlist_result)['count'] : 0;
} elseif (!isset($wishlist_count)) {
    $wishlist_count = 0;
}

if (!isset($cart_count)) {
    if ($is_logged_in) {
        $cart_query = "SELECT COUNT(*) as count FROM cart WHERE user_id = $user_id";
        $cart_result = mysqli_query($conn, $cart_query);
        $cart_count = $cart_result ? mysqli_fetch_assoc($cart_result)['count'] : 0;
    } else {
        $cart_count = isset($_SESSION['cart']) ? count($_SESSION['cart']) : 0;
    }
}
?>

<!-- HEADER -->
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
