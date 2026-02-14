<?php
require_once 'config.php';

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

// Get total orders count
$orders_stmt = mysqli_prepare($conn, "SELECT COUNT(*) as count FROM orders WHERE user_id = ?");
mysqli_stmt_bind_param($orders_stmt, "i", $user_id);
mysqli_stmt_execute($orders_stmt);
$orders_count = mysqli_fetch_assoc(mysqli_stmt_get_result($orders_stmt))['count'];
mysqli_stmt_close($orders_stmt);

// Get total amount spent
$spent_stmt = mysqli_prepare($conn, "SELECT COALESCE(SUM(total_amount), 0) as total FROM orders WHERE user_id = ? AND status != 'cancelled'");
mysqli_stmt_bind_param($spent_stmt, "i", $user_id);
mysqli_stmt_execute($spent_stmt);
$total_spent = mysqli_fetch_assoc(mysqli_stmt_get_result($spent_stmt))['total'];
mysqli_stmt_close($spent_stmt);

// Get wishlist count
$wl_stmt = mysqli_prepare($conn, "SELECT COUNT(*) as count FROM wishlist WHERE user_id = ?");
mysqli_stmt_bind_param($wl_stmt, "i", $user_id);
mysqli_stmt_execute($wl_stmt);
$wishlist_count = mysqli_fetch_assoc(mysqli_stmt_get_result($wl_stmt))['count'];
mysqli_stmt_close($wl_stmt);

// Get cart count and cart total
$cart_stmt = mysqli_prepare($conn, "SELECT COUNT(*) as count, COALESCE(SUM(product_price * quantity), 0) as total FROM cart WHERE user_id = ?");
mysqli_stmt_bind_param($cart_stmt, "i", $user_id);
mysqli_stmt_execute($cart_stmt);
$cart_data = mysqli_fetch_assoc(mysqli_stmt_get_result($cart_stmt));
$cart_count = $cart_data['count'];
$cart_total = $cart_data['total'];
mysqli_stmt_close($cart_stmt);

// Get order status counts
$status_stmt = mysqli_prepare($conn, "SELECT status, COUNT(*) as count FROM orders WHERE user_id = ? GROUP BY status");
mysqli_stmt_bind_param($status_stmt, "i", $user_id);
mysqli_stmt_execute($status_stmt);
$status_result = mysqli_stmt_get_result($status_stmt);
$order_statuses = ['pending' => 0, 'processing' => 0, 'completed' => 0, 'cancelled' => 0];
while ($row = mysqli_fetch_assoc($status_result)) {
    $order_statuses[$row['status']] = $row['count'];
}
mysqli_stmt_close($status_stmt);

// Get recent orders (last 5)
$recent_stmt = mysqli_prepare($conn, "SELECT o.*, 
    (SELECT COUNT(*) FROM order_items WHERE order_id = o.id) as item_count 
    FROM orders o 
    WHERE o.user_id = ? 
    ORDER BY o.created_at DESC 
    LIMIT 5");
mysqli_stmt_bind_param($recent_stmt, "i", $user_id);
mysqli_stmt_execute($recent_stmt);
$recent_orders = mysqli_stmt_get_result($recent_stmt);
mysqli_stmt_close($recent_stmt);

// Get wishlist items (last 4)
$wl_items_stmt = mysqli_prepare($conn, "SELECT * FROM wishlist WHERE user_id = ? ORDER BY created_at DESC LIMIT 4");
mysqli_stmt_bind_param($wl_items_stmt, "i", $user_id);
mysqli_stmt_execute($wl_items_stmt);
$wishlist_items = mysqli_stmt_get_result($wl_items_stmt);
mysqli_stmt_close($wl_items_stmt);

// Calculate member since
$member_since = date('F Y', strtotime($user['created_at']));
$days_member = floor((time() - strtotime($user['created_at'])) / 86400);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>My Dashboard - Furnessence</title>
    
    <link rel="shortcut icon" href="./favicon.svg" type="image/svg+xml">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Jost:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="./assests/css/style.css">
    <link rel="stylesheet" href="./assests/css/dashboard.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    
    <script type="module" src="https://unpkg.com/ionicons@7.1.0/dist/ionicons/ionicons.esm.js"></script>
    <script nomodule src="https://unpkg.com/ionicons@7.1.0/dist/ionicons/ionicons.js"></script>
</head>
<body>
    
    <div class="dashboard-container">
        
        <!-- Sidebar Navigation -->
        <aside class="dashboard-sidebar">
            <div class="sidebar-brand">
                <a href="index.php" class="logo"><i class="fas fa-couch"></i> Furnessence</a>
            </div>
            
            <div class="sidebar-user">
                <div class="user-avatar">
                    <i class="fas fa-user-circle"></i>
                </div>
                <div class="user-info">
                    <h3><?php echo htmlspecialchars($user['name']); ?></h3>
                    <p><?php echo htmlspecialchars($user['email']); ?></p>
                </div>
            </div>
            
            <nav class="sidebar-nav">
                <ul>
                    <li class="active">
                        <a href="dashboard.php">
                            <i class="fas fa-th-large"></i>
                            <span>Dashboard</span>
                        </a>
                    </li>
                    <li>
                        <a href="my_orders.php">
                            <i class="fas fa-shopping-bag"></i>
                            <span>My Orders</span>
                            <?php if ($orders_count > 0): ?>
                            <span class="nav-badge"><?php echo $orders_count; ?></span>
                            <?php endif; ?>
                        </a>
                    </li>
                    <li>
                        <a href="wishlist.php">
                            <i class="fas fa-heart"></i>
                            <span>Wishlist</span>
                            <?php if ($wishlist_count > 0): ?>
                            <span class="nav-badge"><?php echo $wishlist_count; ?></span>
                            <?php endif; ?>
                        </a>
                    </li>
                    <li>
                        <a href="cart.php">
                            <i class="fas fa-shopping-cart"></i>
                            <span>Cart</span>
                            <?php if ($cart_count > 0): ?>
                            <span class="nav-badge"><?php echo $cart_count; ?></span>
                            <?php endif; ?>
                        </a>
                    </li>
                    <li>
                        <a href="profile.php">
                            <i class="fas fa-user-edit"></i>
                            <span>Edit Profile</span>
                        </a>
                    </li>
                    <li>
                        <a href="index.php">
                            <i class="fas fa-store"></i>
                            <span>Back to Shop</span>
                        </a>
                    </li>
                    <li class="nav-divider"></li>
                    <li>
                        <a href="logout.php" class="logout-link">
                            <i class="fas fa-sign-out-alt"></i>
                            <span>Logout</span>
                        </a>
                    </li>
                </ul>
            </nav>
        </aside>
        
        <!-- Mobile Header -->
        <div class="dashboard-mobile-header">
            <button class="mobile-menu-btn" id="mobileMenuBtn">
                <i class="fas fa-bars"></i>
            </button>
            <a href="index.php" class="logo"><i class="fas fa-couch"></i> Furnessence</a>
            <a href="profile.php" class="mobile-profile-btn">
                <i class="fas fa-user-circle"></i>
            </a>
        </div>
        
        <!-- Mobile Sidebar Overlay -->
        <div class="sidebar-overlay" id="sidebarOverlay"></div>
        
        <!-- Main Content -->
        <main class="dashboard-main">
            
            <!-- Welcome Banner -->
            <div class="welcome-banner">
                <div class="welcome-text">
                    <h1>Welcome back, <?php echo htmlspecialchars($user['name']); ?>! 👋</h1>
                    <p>Member since <?php echo $member_since; ?> &bull; <?php echo $days_member; ?> days with us</p>
                </div>
                <div class="welcome-actions">
                    <a href="index.php" class="btn-shop">
                        <i class="fas fa-store"></i> Continue Shopping
                    </a>
                </div>
            </div>
            
            <!-- Stats Cards -->
            <div class="stats-grid">
                <div class="stat-card stat-orders">
                    <div class="stat-icon">
                        <i class="fas fa-shopping-bag"></i>
                    </div>
                    <div class="stat-details">
                        <h3><?php echo $orders_count; ?></h3>
                        <p>Total Orders</p>
                    </div>
                </div>
                
                <div class="stat-card stat-spent">
                    <div class="stat-icon">
                        <i class="fas fa-wallet"></i>
                    </div>
                    <div class="stat-details">
                        <h3>Rs <?php echo number_format($total_spent, 0); ?></h3>
                        <p>Total Spent</p>
                    </div>
                </div>
                
                <div class="stat-card stat-wishlist">
                    <div class="stat-icon">
                        <i class="fas fa-heart"></i>
                    </div>
                    <div class="stat-details">
                        <h3><?php echo $wishlist_count; ?></h3>
                        <p>Wishlist Items</p>
                    </div>
                </div>
                
                <div class="stat-card stat-cart">
                    <div class="stat-icon">
                        <i class="fas fa-shopping-cart"></i>
                    </div>
                    <div class="stat-details">
                        <h3><?php echo $cart_count; ?></h3>
                        <p>Cart Items</p>
                        <?php if ($cart_total > 0): ?>
                        <span class="stat-sub">Rs <?php echo number_format($cart_total, 0); ?></span>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
            
            <!-- Order Status Overview -->
            <div class="content-grid">
                
                <!-- Order Status Breakdown -->
                <div class="dashboard-card order-status-card">
                    <div class="card-header">
                        <h2><i class="fas fa-chart-pie"></i> Order Status Overview</h2>
                    </div>
                    <div class="card-body">
                        <?php if ($orders_count > 0): ?>
                        <div class="status-bars">
                            <div class="status-item">
                                <div class="status-info">
                                    <span class="status-dot pending"></span>
                                    <span class="status-label">Pending</span>
                                    <span class="status-count"><?php echo $order_statuses['pending']; ?></span>
                                </div>
                                <div class="status-bar">
                                    <div class="status-fill pending" style="width: <?php echo $orders_count > 0 ? ($order_statuses['pending'] / $orders_count * 100) : 0; ?>%"></div>
                                </div>
                            </div>
                            
                            <div class="status-item">
                                <div class="status-info">
                                    <span class="status-dot processing"></span>
                                    <span class="status-label">Processing</span>
                                    <span class="status-count"><?php echo $order_statuses['processing']; ?></span>
                                </div>
                                <div class="status-bar">
                                    <div class="status-fill processing" style="width: <?php echo $orders_count > 0 ? ($order_statuses['processing'] / $orders_count * 100) : 0; ?>%"></div>
                                </div>
                            </div>
                            
                            <div class="status-item">
                                <div class="status-info">
                                    <span class="status-dot completed"></span>
                                    <span class="status-label">Completed</span>
                                    <span class="status-count"><?php echo $order_statuses['completed']; ?></span>
                                </div>
                                <div class="status-bar">
                                    <div class="status-fill completed" style="width: <?php echo $orders_count > 0 ? ($order_statuses['completed'] / $orders_count * 100) : 0; ?>%"></div>
                                </div>
                            </div>
                            
                            <div class="status-item">
                                <div class="status-info">
                                    <span class="status-dot cancelled"></span>
                                    <span class="status-label">Cancelled</span>
                                    <span class="status-count"><?php echo $order_statuses['cancelled']; ?></span>
                                </div>
                                <div class="status-bar">
                                    <div class="status-fill cancelled" style="width: <?php echo $orders_count > 0 ? ($order_statuses['cancelled'] / $orders_count * 100) : 0; ?>%"></div>
                                </div>
                            </div>
                        </div>
                        <?php else: ?>
                        <div class="empty-state">
                            <i class="fas fa-box-open"></i>
                            <p>No orders yet</p>
                            <a href="index.php" class="btn-link">Start Shopping</a>
                        </div>
                        <?php endif; ?>
                    </div>
                </div>
                
                <!-- Quick Actions -->
                <div class="dashboard-card quick-actions-card">
                    <div class="card-header">
                        <h2><i class="fas fa-bolt"></i> Quick Actions</h2>
                    </div>
                    <div class="card-body">
                        <div class="actions-grid">
                            <a href="index.php" class="action-item">
                                <div class="action-icon browse"><i class="fas fa-search"></i></div>
                                <span>Browse Products</span>
                            </a>
                            <a href="cart.php" class="action-item">
                                <div class="action-icon cart"><i class="fas fa-shopping-cart"></i></div>
                                <span>View Cart</span>
                            </a>
                            <a href="wishlist.php" class="action-item">
                                <div class="action-icon wishlist"><i class="fas fa-heart"></i></div>
                                <span>My Wishlist</span>
                            </a>
                            <a href="profile.php" class="action-item">
                                <div class="action-icon profile"><i class="fas fa-user-cog"></i></div>
                                <span>Edit Profile</span>
                            </a>
                            <?php if ($cart_count > 0): ?>
                            <a href="checkout.php" class="action-item">
                                <div class="action-icon checkout"><i class="fas fa-credit-card"></i></div>
                                <span>Checkout</span>
                            </a>
                            <?php endif; ?>
                            <a href="my_orders.php" class="action-item">
                                <div class="action-icon orders"><i class="fas fa-clipboard-list"></i></div>
                                <span>All Orders</span>
                            </a>
                        </div>
                    </div>
                </div>
            </div>
            
            <!-- Recent Orders & Wishlist -->
            <div class="content-grid">
                
                <!-- Recent Orders Table -->
                <div class="dashboard-card recent-orders-card">
                    <div class="card-header">
                        <h2><i class="fas fa-clock"></i> Recent Orders</h2>
                        <?php if ($orders_count > 0): ?>
                        <a href="my_orders.php" class="view-all-link">View All <i class="fas fa-arrow-right"></i></a>
                        <?php endif; ?>
                    </div>
                    <div class="card-body">
                        <?php if (mysqli_num_rows($recent_orders) > 0): ?>
                        <div class="orders-table-wrapper">
                            <table class="orders-table">
                                <thead>
                                    <tr>
                                        <th>Order #</th>
                                        <th>Date</th>
                                        <th>Items</th>
                                        <th>Total</th>
                                        <th>Status</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php while ($order = mysqli_fetch_assoc($recent_orders)): ?>
                                    <tr>
                                        <td class="order-number"><?php echo htmlspecialchars($order['order_number']); ?></td>
                                        <td class="order-date"><?php echo date('M d, Y', strtotime($order['created_at'])); ?></td>
                                        <td class="order-items"><?php echo $order['item_count']; ?> item(s)</td>
                                        <td class="order-total">Rs <?php echo number_format($order['total_amount'], 2); ?></td>
                                        <td>
                                            <span class="order-status status-<?php echo $order['status']; ?>">
                                                <?php echo ucfirst($order['status']); ?>
                                            </span>
                                        </td>
                                    </tr>
                                    <?php endwhile; ?>
                                </tbody>
                            </table>
                        </div>
                        <?php else: ?>
                        <div class="empty-state">
                            <i class="fas fa-receipt"></i>
                            <p>No orders placed yet</p>
                            <a href="index.php" class="btn-link">Browse Products</a>
                        </div>
                        <?php endif; ?>
                    </div>
                </div>
                
                <!-- Wishlist Preview -->
                <div class="dashboard-card wishlist-preview-card">
                    <div class="card-header">
                        <h2><i class="fas fa-heart"></i> Wishlist</h2>
                        <?php if ($wishlist_count > 0): ?>
                        <a href="wishlist.php" class="view-all-link">View All <i class="fas fa-arrow-right"></i></a>
                        <?php endif; ?>
                    </div>
                    <div class="card-body">
                        <?php if (mysqli_num_rows($wishlist_items) > 0): ?>
                        <div class="wishlist-mini-grid">
                            <?php while ($item = mysqli_fetch_assoc($wishlist_items)): ?>
                            <div class="wishlist-mini-item">
                                <div class="mini-item-image">
                                    <img src="<?php echo htmlspecialchars($item['product_image']); ?>" 
                                         alt="<?php echo htmlspecialchars($item['product_name']); ?>">
                                </div>
                                <div class="mini-item-info">
                                    <h4><?php echo htmlspecialchars($item['product_name']); ?></h4>
                                    <p class="mini-item-price">Rs <?php echo number_format($item['product_price'], 2); ?></p>
                                </div>
                            </div>
                            <?php endwhile; ?>
                        </div>
                        <?php else: ?>
                        <div class="empty-state">
                            <i class="fas fa-heart-broken"></i>
                            <p>Your wishlist is empty</p>
                            <a href="index.php" class="btn-link">Discover Products</a>
                        </div>
                        <?php endif; ?>
                    </div>
                </div>
                
            </div>
            
            <!-- Account Info Card -->
            <div class="dashboard-card account-card">
                <div class="card-header">
                    <h2><i class="fas fa-id-card"></i> Account Information</h2>
                    <a href="profile.php" class="view-all-link">Edit <i class="fas fa-pencil-alt"></i></a>
                </div>
                <div class="card-body">
                    <div class="account-info-grid">
                        <div class="account-info-item">
                            <div class="info-icon"><i class="fas fa-user"></i></div>
                            <div class="info-details">
                                <span class="info-label">Full Name</span>
                                <span class="info-value"><?php echo htmlspecialchars($user['name']); ?></span>
                            </div>
                        </div>
                        <div class="account-info-item">
                            <div class="info-icon"><i class="fas fa-envelope"></i></div>
                            <div class="info-details">
                                <span class="info-label">Email Address</span>
                                <span class="info-value"><?php echo htmlspecialchars($user['email']); ?></span>
                            </div>
                        </div>
                        <div class="account-info-item">
                            <div class="info-icon"><i class="fas fa-calendar-alt"></i></div>
                            <div class="info-details">
                                <span class="info-label">Member Since</span>
                                <span class="info-value"><?php echo date('F d, Y', strtotime($user['created_at'])); ?></span>
                            </div>
                        </div>
                        <div class="account-info-item">
                            <div class="info-icon"><i class="fas fa-shield-alt"></i></div>
                            <div class="info-details">
                                <span class="info-label">Account Status</span>
                                <span class="info-value status-active">Active</span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            
        </main>
    </div>
    
    <script>
    // Mobile sidebar toggle
    const mobileMenuBtn = document.getElementById('mobileMenuBtn');
    const sidebar = document.querySelector('.dashboard-sidebar');
    const overlay = document.getElementById('sidebarOverlay');
    
    mobileMenuBtn.addEventListener('click', () => {
        sidebar.classList.toggle('open');
        overlay.classList.toggle('active');
    });
    
    overlay.addEventListener('click', () => {
        sidebar.classList.remove('open');
        overlay.classList.remove('active');
    });
    </script>
    
</body>
</html>
