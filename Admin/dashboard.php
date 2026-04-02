<?php
require_once 'admin_config.php';
requireAdminLogin();

$admin = getAdminInfo();

// Get statistics
$stats_query = "SELECT 
    (SELECT COUNT(*) FROM products) as total_products,
    (SELECT COUNT(*) FROM products WHERE stock_quantity <= low_stock_threshold) as low_stock_products,
    (SELECT COUNT(*) FROM products WHERE stock_quantity = 0) as out_of_stock,
    (SELECT COUNT(*) FROM users) as total_users,
    (SELECT COUNT(*) FROM orders) as total_orders,
    (SELECT SUM(total_amount) FROM orders WHERE status = 'completed') as total_revenue";
$stats_result = mysqli_query($conn, $stats_query);
$stats = mysqli_fetch_assoc($stats_result);

// Get recent orders
$orders_query = "SELECT o.*, u.name as user_name 
    FROM orders o 
    LEFT JOIN users u ON o.user_id = u.id 
    ORDER BY o.created_at DESC 
    LIMIT 5";
$orders_result = mysqli_query($conn, $orders_query);

// Get low stock products
$low_stock_query = "SELECT * FROM products 
    WHERE stock_quantity <= low_stock_threshold 
    ORDER BY stock_quantity ASC 
    LIMIT 5";
$low_stock_result = mysqli_query($conn, $low_stock_query);

// ===== CHART DATA =====

// Revenue Trend Filter
$revenue_filter_options = [
    '7d' => 'Last 7 Days',
    '30d' => 'Last 30 Days',
    '6m' => 'Last 6 Months',
    '12m' => 'Last 12 Months',
    'all' => 'All Time'
];
$revenue_range = $_GET['revenue_range'] ?? '12m';
if (!array_key_exists($revenue_range, $revenue_filter_options)) {
    $revenue_range = '12m';
}

$revenue_group_expression = "DATE_FORMAT(created_at, '%Y-%m')";
$revenue_date_condition = "AND created_at >= DATE_SUB(NOW(), INTERVAL 12 MONTH)";
$revenue_is_daily = false;

switch ($revenue_range) {
    case '7d':
        $revenue_group_expression = "DATE(created_at)";
        $revenue_date_condition = "AND created_at >= DATE_SUB(NOW(), INTERVAL 7 DAY)";
        $revenue_is_daily = true;
        break;
    case '30d':
        $revenue_group_expression = "DATE(created_at)";
        $revenue_date_condition = "AND created_at >= DATE_SUB(NOW(), INTERVAL 30 DAY)";
        $revenue_is_daily = true;
        break;
    case '6m':
        $revenue_group_expression = "DATE_FORMAT(created_at, '%Y-%m')";
        $revenue_date_condition = "AND created_at >= DATE_SUB(NOW(), INTERVAL 6 MONTH)";
        break;
    case 'all':
        $revenue_group_expression = "DATE_FORMAT(created_at, '%Y-%m')";
        $revenue_date_condition = "";
        break;
    default:
        $revenue_group_expression = "DATE_FORMAT(created_at, '%Y-%m')";
        $revenue_date_condition = "AND created_at >= DATE_SUB(NOW(), INTERVAL 12 MONTH)";
        break;
}

$revenue_badge_text = $revenue_filter_options[$revenue_range];

$revenue_query = "SELECT 
    {$revenue_group_expression} as period,
    SUM(total_amount) as revenue,
    COUNT(*) as order_count
    FROM orders 
    WHERE status != 'cancelled' {$revenue_date_condition}
    GROUP BY {$revenue_group_expression}
    ORDER BY period ASC";
$revenue_result = mysqli_query($conn, $revenue_query);
$monthly_labels = [];
$monthly_revenue = [];
$monthly_orders = [];
while ($row = mysqli_fetch_assoc($revenue_result)) {
    $monthly_labels[] = $revenue_is_daily
        ? date('M d', strtotime($row['period']))
        : date('M Y', strtotime($row['period'] . '-01'));
    $monthly_revenue[] = round($row['revenue'], 2);
    $monthly_orders[] = $row['order_count'];
}

// Order Status Distribution
$status_query = "SELECT status, COUNT(*) as count FROM orders GROUP BY status";
$status_result = mysqli_query($conn, $status_query);
$status_labels = [];
$status_data = [];
while ($row = mysqli_fetch_assoc($status_result)) {
    $status_labels[] = ucfirst($row['status']);
    $status_data[] = $row['count'];
}

// Top 5 Selling Products
$top_products_query = "SELECT product_name, SUM(quantity) as total_sold, SUM(subtotal) as total_revenue
    FROM order_items 
    GROUP BY product_name 
    ORDER BY total_sold DESC 
    LIMIT 5";
$top_result = mysqli_query($conn, $top_products_query);
$top_product_labels = [];
$top_product_sold = [];
$top_product_revenue = [];
while ($row = mysqli_fetch_assoc($top_result)) {
    $top_product_labels[] = $row['product_name'];
    $top_product_sold[] = $row['total_sold'];
    $top_product_revenue[] = round($row['total_revenue'], 2);
}

// Daily Orders (last 30 days)
$daily_query = "SELECT DATE(created_at) as order_date, COUNT(*) as count, SUM(total_amount) as revenue
    FROM orders 
    WHERE created_at >= DATE_SUB(NOW(), INTERVAL 30 DAY)
    GROUP BY DATE(created_at)
    ORDER BY order_date ASC";
$daily_result = mysqli_query($conn, $daily_query);
$daily_labels = [];
$daily_orders_data = [];
$daily_revenue_data = [];
while ($row = mysqli_fetch_assoc($daily_result)) {
    $daily_labels[] = date('M d', strtotime($row['order_date']));
    $daily_orders_data[] = $row['count'];
    $daily_revenue_data[] = round($row['revenue'], 2);
}

// User Registration Trend (last 6 months)
$users_trend_query = "SELECT DATE_FORMAT(created_at, '%Y-%m') as month, COUNT(*) as count
    FROM users 
    WHERE created_at >= DATE_SUB(NOW(), INTERVAL 6 MONTH)
    GROUP BY DATE_FORMAT(created_at, '%Y-%m')
    ORDER BY month ASC";
$users_trend_result = mysqli_query($conn, $users_trend_query);
$users_trend_labels = [];
$users_trend_data = [];
while ($row = mysqli_fetch_assoc($users_trend_result)) {
    $users_trend_labels[] = date('M Y', strtotime($row['month'] . '-01'));
    $users_trend_data[] = $row['count'];
}

// Category-wise Sales
$category_query = "SELECT c.name, SUM(oi.quantity) as total_sold, SUM(oi.subtotal) as total_revenue
    FROM order_items oi
    JOIN products p ON oi.product_id = p.id
    JOIN categories c ON p.category_id = c.id
    GROUP BY c.name
    ORDER BY total_revenue DESC";
$category_result = mysqli_query($conn, $category_query);
$cat_labels = [];
$cat_revenue = [];
while ($row = mysqli_fetch_assoc($category_result)) {
    $cat_labels[] = $row['name'];
    $cat_revenue[] = round($row['total_revenue'], 2);
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard - Furnessence</title>
    <link rel="stylesheet" href="../assests/css/admin.css?v=<?php echo time(); ?>">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.1/dist/chart.umd.min.js"></script>
</head>
<body class="admin-body">
    <?php include 'includes/sidebar.php'; ?>
    
    <div class="admin-main">
        <?php include 'includes/header.php'; ?>
        
        <div class="admin-content">
            <div class="page-header">
                <h1>Dashboard</h1>
                <p>Welcome back, <?php echo htmlspecialchars($admin['full_name']); ?>!</p>
            </div>
            
            <!-- Statistics Cards -->
            <div class="stats-grid">
                <a href="manage_products.php" class="stat-card-link">
                    <div class="stat-card">
                        <div class="stat-icon blue">
                            <i class="fas fa-box"></i>
                        </div>
                        <div class="stat-info">
                            <h3><?php echo $stats['total_products']; ?></h3>
                            <p>Total Products</p>
                        </div>
                    </div>
                </a>
                
                <a href="manage_products.php?stock=low" class="stat-card-link">
                    <div class="stat-card">
                        <div class="stat-icon orange">
                            <i class="fas fa-exclamation-triangle"></i>
                        </div>
                        <div class="stat-info">
                            <h3><?php echo $stats['low_stock_products']; ?></h3>
                            <p>Low Stock Items</p>
                        </div>
                    </div>
                </a>
                
                <a href="manage_products.php?stock=out" class="stat-card-link">
                    <div class="stat-card">
                        <div class="stat-icon red">
                            <i class="fas fa-times-circle"></i>
                        </div>
                        <div class="stat-info">
                            <h3><?php echo $stats['out_of_stock']; ?></h3>
                            <p>Out of Stock</p>
                        </div>
                    </div>
                </a>
                
                <a href="manage_users.php" class="stat-card-link">
                    <div class="stat-card">
                        <div class="stat-icon purple">
                            <i class="fas fa-users"></i>
                        </div>
                        <div class="stat-info">
                            <h3><?php echo $stats['total_users']; ?></h3>
                            <p>Total Users</p>
                        </div>
                    </div>
                </a>
                
                <a href="manage_orders.php" class="stat-card-link">
                    <div class="stat-card">
                        <div class="stat-icon green">
                            <i class="fas fa-shopping-cart"></i>
                        </div>
                        <div class="stat-info">
                            <h3><?php echo $stats['total_orders']; ?></h3>
                            <p>Total Orders</p>
                        </div>
                    </div>
                </a>
                
                <a href="manage_orders.php?status=completed" class="stat-card-link">
                    <div class="stat-card">
                        <div class="stat-icon teal">
                            <i class="fas fa-coins"></i>
                        </div>
                        <div class="stat-info">
                            <h3>Rs <?php echo number_format($stats['total_revenue'] ?? 0, 2); ?></h3>
                            <p>Total Revenue</p>
                        </div>
                    </div>
                </a>
            </div>
            
            <!-- Charts Section -->
            <div class="charts-grid">
                <!-- Revenue Trend Chart -->
                <div class="dashboard-card chart-card">
                    <div class="card-header">
                        <h2><i class="fas fa-chart-line"></i> Revenue Trend</h2>
                        <div class="chart-header-actions">
                            <select id="revenueRangeFilter" class="chart-filter-select" aria-label="Filter revenue chart range">
                                <?php foreach ($revenue_filter_options as $value => $label): ?>
                                    <option value="<?php echo $value; ?>" <?php echo $revenue_range === $value ? 'selected' : ''; ?>>
                                        <?php echo htmlspecialchars($label); ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                            <span class="chart-badge"><?php echo htmlspecialchars($revenue_badge_text); ?></span>
                        </div>
                    </div>
                    <div class="chart-body">
                        <canvas id="revenueChart"></canvas>
                    </div>
                </div>
                
                <!-- Order Status Chart -->
                <div class="dashboard-card chart-card">
                    <div class="card-header">
                        <h2><i class="fas fa-chart-pie"></i> Order Status</h2>
                        <span class="chart-badge">All Time</span>
                    </div>
                    <div class="chart-body">
                        <canvas id="orderStatusChart"></canvas>
                    </div>
                </div>
                
                <!-- Daily Orders Chart -->
                <div class="dashboard-card chart-card">
                    <div class="card-header">
                        <h2><i class="fas fa-chart-area"></i> Daily Orders</h2>
                        <span class="chart-badge">Last 30 Days</span>
                    </div>
                    <div class="chart-body">
                        <canvas id="dailyOrdersChart"></canvas>
                    </div>
                </div>
                
                <!-- Top Selling Products -->
                <div class="dashboard-card chart-card">
                    <div class="card-header">
                        <h2><i class="fas fa-trophy"></i> Top Selling Products</h2>
                        <span class="chart-badge">By Quantity</span>
                    </div>
                    <div class="chart-body">
                        <canvas id="topProductsChart"></canvas>
                    </div>
                </div>
                
                <!-- Category Revenue -->
                <div class="dashboard-card chart-card">
                    <div class="card-header">
                        <h2><i class="fas fa-tags"></i> Sales by Category</h2>
                        <span class="chart-badge">Revenue</span>
                    </div>
                    <div class="chart-body">
                        <canvas id="categoryChart"></canvas>
                    </div>
                </div>
                
                <!-- User Registration Trend -->
                <div class="dashboard-card chart-card">
                    <div class="card-header">
                        <h2><i class="fas fa-user-plus"></i> New Users</h2>
                        <span class="chart-badge">Last 6 Months</span>
                    </div>
                    <div class="chart-body">
                        <canvas id="usersChart"></canvas>
                    </div>
                </div>
            </div>
            
            <!-- Recent Orders and Low Stock -->
            <div class="dashboard-tables">
                <div class="dashboard-card">
                    <div class="card-header">
                        <h2><i class="fas fa-shopping-bag"></i> Recent Orders</h2>
                        <a href="manage_orders.php" class="btn-view-all">View All</a>
                    </div>
                    <div class="table-responsive">
                        <table class="admin-table">
                            <thead>
                                <tr>
                                    <th>Order #</th>
                                    <th>Customer</th>
                                    <th>Amount</th>
                                    <th>Status</th>
                                    <th>Date</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if (mysqli_num_rows($orders_result) > 0): ?>
                                    <?php while($order = mysqli_fetch_assoc($orders_result)): ?>
                                        <tr>
                                            <td>#<?php echo htmlspecialchars($order['order_number']); ?></td>
                                            <td><?php echo htmlspecialchars($order['user_name']); ?></td>
                                            <td>Rs <?php echo number_format($order['total_amount'], 2); ?></td>
                                            <td><span class="status-badge status-<?php echo $order['status']; ?>"><?php echo ucfirst($order['status']); ?></span></td>
                                            <td><?php echo date('M d, Y', strtotime($order['created_at'])); ?></td>
                                        </tr>
                                    <?php endwhile; ?>
                                <?php else: ?>
                                    <tr>
                                        <td colspan="5" class="no-data">No orders yet</td>
                                    </tr>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
                
                <div class="dashboard-card">
                    <div class="card-header">
                        <h2><i class="fas fa-exclamation-circle"></i> Low Stock Alert</h2>
                        <a href="manage_products.php" class="btn-view-all">View All</a>
                    </div>
                    <div class="table-responsive">
                        <table class="admin-table">
                            <thead>
                                <tr>
                                    <th>Product</th>
                                    <th>Stock</th>
                                    <th>Status</th>
                                    <th>Action</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if (mysqli_num_rows($low_stock_result) > 0): ?>
                                    <?php while($product = mysqli_fetch_assoc($low_stock_result)): ?>
                                        <tr>
                                            <td><?php echo htmlspecialchars($product['name']); ?></td>
                                            <td><?php echo $product['stock_quantity']; ?></td>
                                            <td>
                                                <?php if ($product['stock_quantity'] == 0): ?>
                                                    <span class="stock-badge out-of-stock">Out of Stock</span>
                                                <?php else: ?>
                                                    <span class="stock-badge low-stock">Low Stock</span>
                                                <?php endif; ?>
                                            </td>
                                            <td>
                                                <a href="edit_product.php?id=<?php echo $product['id']; ?>" class="btn-action btn-edit">
                                                    <i class="fas fa-edit"></i>
                                                </a>
                                            </td>
                                        </tr>
                                    <?php endwhile; ?>
                                <?php else: ?>
                                    <tr>
                                        <td colspan="4" class="no-data">All products are well stocked!</td>
                                    </tr>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script>
    // Chart.js default settings
    Chart.defaults.font.family = "'Roboto', 'Segoe UI', sans-serif";
    Chart.defaults.font.size = 12;
    Chart.defaults.plugins.legend.labels.usePointStyle = true;

    const turquoise = '#1abc9c';
    const blue = '#3498db';
    const purple = '#9b59b6';
    const orange = '#f39c12';
    const red = '#e74c3c';
    const green = '#27ae60';
    const darkBlue = '#2c3e50';

    // 1. Revenue Trend (Line Chart)
    new Chart(document.getElementById('revenueChart'), {
        type: 'line',
        data: {
            labels: <?php echo json_encode($monthly_labels); ?>,
            datasets: [{
                label: 'Revenue (Rs)',
                data: <?php echo json_encode($monthly_revenue); ?>,
                borderColor: turquoise,
                backgroundColor: 'rgba(26, 188, 156, 0.1)',
                borderWidth: 3,
                fill: true,
                tension: 0.4,
                pointBackgroundColor: turquoise,
                pointRadius: 5,
                pointHoverRadius: 8
            }, {
                label: 'Orders',
                data: <?php echo json_encode($monthly_orders); ?>,
                borderColor: blue,
                backgroundColor: 'rgba(52, 152, 219, 0.1)',
                borderWidth: 2,
                fill: false,
                tension: 0.4,
                pointBackgroundColor: blue,
                pointRadius: 4,
                yAxisID: 'y1'
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            interaction: { mode: 'index', intersect: false },
            plugins: {
                tooltip: {
                    callbacks: {
                        label: function(ctx) {
                            if (ctx.dataset.label.includes('Revenue'))
                                return 'Revenue: Rs ' + ctx.parsed.y.toLocaleString();
                            return 'Orders: ' + ctx.parsed.y;
                        }
                    }
                }
            },
            scales: {
                y: { beginAtZero: true, title: { display: true, text: 'Revenue (Rs)' } },
                y1: { beginAtZero: true, position: 'right', grid: { drawOnChartArea: false }, title: { display: true, text: 'Orders' } }
            }
        }
    });

    // 2. Order Status (Doughnut Chart)
    new Chart(document.getElementById('orderStatusChart'), {
        type: 'doughnut',
        data: {
            labels: <?php echo json_encode($status_labels); ?>,
            datasets: [{
                data: <?php echo json_encode($status_data); ?>,
                backgroundColor: [orange, blue, green, red],
                borderWidth: 3,
                borderColor: '#fff',
                hoverOffset: 8
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            cutout: '65%',
            plugins: {
                legend: { position: 'bottom', labels: { padding: 20 } }
            }
        }
    });

    // 3. Daily Orders (Area Chart)
    new Chart(document.getElementById('dailyOrdersChart'), {
        type: 'line',
        data: {
            labels: <?php echo json_encode($daily_labels); ?>,
            datasets: [{
                label: 'Orders',
                data: <?php echo json_encode($daily_orders_data); ?>,
                borderColor: purple,
                backgroundColor: 'rgba(155, 89, 182, 0.15)',
                borderWidth: 2,
                fill: true,
                tension: 0.3,
                pointRadius: 3
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: { legend: { display: false } },
            scales: {
                y: { beginAtZero: true, ticks: { stepSize: 1 } },
                x: { ticks: { maxTicksLimit: 10 } }
            }
        }
    });

    // 4. Top Products (Horizontal Bar)
    new Chart(document.getElementById('topProductsChart'), {
        type: 'bar',
        data: {
            labels: <?php echo json_encode($top_product_labels); ?>,
            datasets: [{
                label: 'Units Sold',
                data: <?php echo json_encode($top_product_sold); ?>,
                backgroundColor: [turquoise, blue, purple, orange, green],
                borderRadius: 6,
                barThickness: 25
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            indexAxis: 'y',
            plugins: { legend: { display: false } },
            scales: {
                x: { beginAtZero: true, ticks: { stepSize: 1 } }
            }
        }
    });

    // 5. Category Revenue (Polar Area)
    new Chart(document.getElementById('categoryChart'), {
        type: 'polarArea',
        data: {
            labels: <?php echo json_encode($cat_labels); ?>,
            datasets: [{
                data: <?php echo json_encode($cat_revenue); ?>,
                backgroundColor: [
                    'rgba(26, 188, 156, 0.7)',
                    'rgba(52, 152, 219, 0.7)',
                    'rgba(155, 89, 182, 0.7)',
                    'rgba(243, 156, 18, 0.7)',
                    'rgba(231, 76, 60, 0.7)'
                ],
                borderWidth: 2
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: { position: 'bottom', labels: { padding: 15 } },
                tooltip: {
                    callbacks: {
                        label: function(ctx) {
                            return ctx.label + ': Rs ' + ctx.parsed.r.toLocaleString();
                        }
                    }
                }
            }
        }
    });

    // 6. User Registration Trend (Bar Chart)
    new Chart(document.getElementById('usersChart'), {
        type: 'bar',
        data: {
            labels: <?php echo json_encode($users_trend_labels); ?>,
            datasets: [{
                label: 'New Users',
                data: <?php echo json_encode($users_trend_data); ?>,
                backgroundColor: 'rgba(52, 152, 219, 0.7)',
                borderColor: blue,
                borderWidth: 1,
                borderRadius: 8,
                barThickness: 35
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: { legend: { display: false } },
            scales: {
                y: { beginAtZero: true, ticks: { stepSize: 1 } }
            }
        }
    });

    // Revenue range filter handler
    const revenueRangeFilter = document.getElementById('revenueRangeFilter');
    if (revenueRangeFilter) {
        revenueRangeFilter.addEventListener('change', function() {
            const params = new URLSearchParams(window.location.search);
            params.set('revenue_range', this.value);
            window.location.search = params.toString();
        });
    }
    </script>
</body>
</html>
