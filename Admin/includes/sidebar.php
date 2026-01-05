<div class="admin-sidebar">
    <div class="sidebar-header">
        <h2><i class="fas fa-couch"></i> Furnessence</h2>
        <p>Admin Panel</p>
    </div>
    
    <nav class="sidebar-nav">
        <a href="dashboard.php" class="nav-item <?php echo basename($_SERVER['PHP_SELF']) == 'dashboard.php' ? 'active' : ''; ?>">
            <i class="fas fa-home"></i>
            <span>Dashboard</span>
        </a>
        
        <a href="manage_products.php" class="nav-item <?php echo basename($_SERVER['PHP_SELF']) == 'manage_products.php' || basename($_SERVER['PHP_SELF']) == 'add_product.php' || basename($_SERVER['PHP_SELF']) == 'edit_product.php' ? 'active' : ''; ?>">
            <i class="fas fa-box"></i>
            <span>Products</span>
        </a>
        
        <a href="manage_categories.php" class="nav-item <?php echo basename($_SERVER['PHP_SELF']) == 'manage_categories.php' ? 'active' : ''; ?>">
            <i class="fas fa-tags"></i>
            <span>Categories</span>
        </a>
        
        <a href="manage_orders.php" class="nav-item <?php echo basename($_SERVER['PHP_SELF']) == 'manage_orders.php' ? 'active' : ''; ?>">
            <i class="fas fa-shopping-cart"></i>
            <span>Orders</span>
        </a>
        
        <a href="manage_users.php" class="nav-item <?php echo basename($_SERVER['PHP_SELF']) == 'manage_users.php' ? 'active' : ''; ?>">
            <i class="fas fa-users"></i>
            <span>Users</span>
        </a>
        
        <a href="../index.php" class="nav-item" target="_blank">
            <i class="fas fa-globe"></i>
            <span>View Website</span>
        </a>
        
        <a href="logout.php" class="nav-item">
            <i class="fas fa-sign-out-alt"></i>
            <span>Logout</span>
        </a>
    </nav>
</div>
