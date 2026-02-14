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
$user = mysqli_fetch_assoc(mysqli_stmt_get_result($user_stmt));
mysqli_stmt_close($user_stmt);

// Filter by status
$status_filter = isset($_GET['status']) ? $_GET['status'] : 'all';
$valid_statuses = ['all', 'pending', 'processing', 'completed', 'cancelled'];
if (!in_array($status_filter, $valid_statuses)) {
    $status_filter = 'all';
}

// Get orders
if ($status_filter === 'all') {
    $orders_stmt = mysqli_prepare($conn, "SELECT o.*, 
        (SELECT COUNT(*) FROM order_items WHERE order_id = o.id) as item_count 
        FROM orders o 
        WHERE o.user_id = ? 
        ORDER BY o.created_at DESC");
    mysqli_stmt_bind_param($orders_stmt, "i", $user_id);
} else {
    $orders_stmt = mysqli_prepare($conn, "SELECT o.*, 
        (SELECT COUNT(*) FROM order_items WHERE order_id = o.id) as item_count 
        FROM orders o 
        WHERE o.user_id = ? AND o.status = ?
        ORDER BY o.created_at DESC");
    mysqli_stmt_bind_param($orders_stmt, "is", $user_id, $status_filter);
}
mysqli_stmt_execute($orders_stmt);
$orders_result = mysqli_stmt_get_result($orders_stmt);
$total_filtered = mysqli_num_rows($orders_result);

// Get counts for each status
$count_stmt = mysqli_prepare($conn, "SELECT status, COUNT(*) as count FROM orders WHERE user_id = ? GROUP BY status");
mysqli_stmt_bind_param($count_stmt, "i", $user_id);
mysqli_stmt_execute($count_stmt);
$count_result = mysqli_stmt_get_result($count_stmt);
$status_counts = ['all' => 0, 'pending' => 0, 'processing' => 0, 'completed' => 0, 'cancelled' => 0];
while ($row = mysqli_fetch_assoc($count_result)) {
    $status_counts[$row['status']] = $row['count'];
    $status_counts['all'] += $row['count'];
}
mysqli_stmt_close($count_stmt);

// Get wishlist & cart count for sidebar
$wl_stmt = mysqli_prepare($conn, "SELECT COUNT(*) as count FROM wishlist WHERE user_id = ?");
mysqli_stmt_bind_param($wl_stmt, "i", $user_id);
mysqli_stmt_execute($wl_stmt);
$wishlist_count = mysqli_fetch_assoc(mysqli_stmt_get_result($wl_stmt))['count'];
mysqli_stmt_close($wl_stmt);

$ct_stmt = mysqli_prepare($conn, "SELECT COUNT(*) as count FROM cart WHERE user_id = ?");
mysqli_stmt_bind_param($ct_stmt, "i", $user_id);
mysqli_stmt_execute($ct_stmt);
$cart_count = mysqli_fetch_assoc(mysqli_stmt_get_result($ct_stmt))['count'];
mysqli_stmt_close($ct_stmt);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>My Orders - Furnessence</title>
    
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
                    <li>
                        <a href="dashboard.php">
                            <i class="fas fa-th-large"></i>
                            <span>Dashboard</span>
                        </a>
                    </li>
                    <li class="active">
                        <a href="my_orders.php">
                            <i class="fas fa-shopping-bag"></i>
                            <span>My Orders</span>
                            <?php if ($status_counts['all'] > 0): ?>
                            <span class="nav-badge"><?php echo $status_counts['all']; ?></span>
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
            
            <div class="page-title">
                <h1><i class="fas fa-shopping-bag"></i> My Orders</h1>
                <p>Track and manage your orders</p>
            </div>
            
            <!-- Status Filter Tabs -->
            <div class="filter-tabs">
                <a href="my_orders.php" class="filter-tab <?php echo $status_filter === 'all' ? 'active' : ''; ?>">
                    All <span class="tab-count"><?php echo $status_counts['all']; ?></span>
                </a>
                <a href="my_orders.php?status=pending" class="filter-tab <?php echo $status_filter === 'pending' ? 'active' : ''; ?>">
                    <span class="status-dot pending"></span> Pending <span class="tab-count"><?php echo $status_counts['pending']; ?></span>
                </a>
                <a href="my_orders.php?status=processing" class="filter-tab <?php echo $status_filter === 'processing' ? 'active' : ''; ?>">
                    <span class="status-dot processing"></span> Processing <span class="tab-count"><?php echo $status_counts['processing']; ?></span>
                </a>
                <a href="my_orders.php?status=completed" class="filter-tab <?php echo $status_filter === 'completed' ? 'active' : ''; ?>">
                    <span class="status-dot completed"></span> Completed <span class="tab-count"><?php echo $status_counts['completed']; ?></span>
                </a>
                <a href="my_orders.php?status=cancelled" class="filter-tab <?php echo $status_filter === 'cancelled' ? 'active' : ''; ?>">
                    <span class="status-dot cancelled"></span> Cancelled <span class="tab-count"><?php echo $status_counts['cancelled']; ?></span>
                </a>
            </div>
            
            <!-- Orders List -->
            <?php if ($total_filtered > 0): ?>
            <div class="orders-list">
                <?php while ($order = mysqli_fetch_assoc($orders_result)): ?>
                <?php
                    // Get order items
                    $items_stmt = mysqli_prepare($conn, "SELECT * FROM order_items WHERE order_id = ?");
                    mysqli_stmt_bind_param($items_stmt, "i", $order['id']);
                    mysqli_stmt_execute($items_stmt);
                    $items_result = mysqli_stmt_get_result($items_stmt);
                ?>
                <div class="order-card">
                    <div class="order-card-header">
                        <div class="order-meta">
                            <span class="order-number-label">Order #<?php echo htmlspecialchars($order['order_number']); ?></span>
                            <span class="order-date">
                                <i class="fas fa-calendar-alt"></i> 
                                <?php echo date('M d, Y - h:i A', strtotime($order['created_at'])); ?>
                            </span>
                        </div>
                        <span class="order-status status-<?php echo $order['status']; ?>">
                            <?php 
                            $status_icons = ['pending' => 'clock', 'processing' => 'spinner', 'completed' => 'check-circle', 'cancelled' => 'times-circle'];
                            ?>
                            <i class="fas fa-<?php echo $status_icons[$order['status']] ?? 'circle'; ?>"></i>
                            <?php echo ucfirst($order['status']); ?>
                        </span>
                    </div>
                    
                    <div class="order-card-body">
                        <div class="order-items-list">
                            <?php while ($item = mysqli_fetch_assoc($items_result)): ?>
                            <div class="order-item-row">
                                <div class="item-info">
                                    <span class="item-name"><?php echo htmlspecialchars($item['product_name']); ?></span>
                                    <span class="item-qty">x<?php echo $item['quantity']; ?></span>
                                </div>
                                <span class="item-price">Rs <?php echo number_format($item['subtotal'], 2); ?></span>
                            </div>
                            <?php endwhile; ?>
                            <?php mysqli_stmt_close($items_stmt); ?>
                        </div>
                    </div>
                    
                    <div class="order-card-footer">
                        <div class="order-footer-left">
                            <div class="order-address">
                                <i class="fas fa-map-marker-alt"></i>
                                <?php echo htmlspecialchars($order['shipping_address']); ?>
                            </div>
                            <div class="order-payment-info">
                                <?php 
                                $pm = $order['payment_method'] ?? 'cod';
                                $pm_labels = ['cod' => 'Cash on Delivery', 'khalti' => 'Khalti', 'esewa' => 'eSewa', 'bank' => 'Bank Transfer'];
                                $pm_icons = ['cod' => 'fa-money-bill', 'khalti' => 'fa-wallet', 'esewa' => 'fa-mobile-screen', 'bank' => 'fa-building-columns'];
                                $ps = $order['payment_status'] ?? 'pending';
                                ?>
                                <span class="payment-method-tag">
                                    <i class="fas <?php echo $pm_icons[$pm] ?? 'fa-money-bill'; ?>"></i>
                                    <?php echo $pm_labels[$pm] ?? ucfirst($pm); ?>
                                </span>
                                <span class="payment-status-tag <?php echo $ps; ?>">
                                    <?php echo ucfirst($ps); ?>
                                </span>
                            </div>
                        </div>
                        <div class="order-total">
                            Total: <strong>Rs <?php echo number_format($order['total_amount'], 2); ?></strong>
                        </div>
                    </div>
                </div>
                <?php endwhile; ?>
            </div>
            <?php else: ?>
            <div class="dashboard-card">
                <div class="card-body">
                    <div class="empty-state large">
                        <i class="fas fa-box-open"></i>
                        <h3>No <?php echo $status_filter !== 'all' ? $status_filter : ''; ?> orders found</h3>
                        <p>
                            <?php if ($status_filter !== 'all'): ?>
                                You don't have any <?php echo $status_filter; ?> orders. 
                                <a href="my_orders.php">View all orders</a>
                            <?php else: ?>
                                You haven't placed any orders yet. Start exploring our collection!
                            <?php endif; ?>
                        </p>
                        <a href="index.php" class="btn-shop">
                            <i class="fas fa-store"></i> Start Shopping
                        </a>
                    </div>
                </div>
            </div>
            <?php endif; ?>
            
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
