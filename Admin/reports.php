<?php
session_start();

// Check if admin is logged in
if (!isset($_SESSION['admin_logged_in']) || $_SESSION['admin_logged_in'] !== true) {
    header("location: AdminLogin.php");
    exit();
}

// Include config file
require_once '../config.php';

// Establish database connection
$conn = getDBConnection();

// Get sales statistics
// Total revenue
$revenue_sql = "SELECT SUM(total_amount) as total_revenue FROM orders WHERE status != 'cancelled'";
$revenue_result = mysqli_query($conn, $revenue_sql);
$total_revenue = mysqli_fetch_assoc($revenue_result)['total_revenue'] ?? 0;

// Total orders
$total_orders_sql = "SELECT COUNT(*) as total_orders FROM orders";
$total_orders_result = mysqli_query($conn, $total_orders_sql);
$total_orders = mysqli_fetch_assoc($total_orders_result)['total_orders'];

// Orders by status
$status_sql = "SELECT status, COUNT(*) as count FROM orders GROUP BY status";
$status_result = mysqli_query($conn, $status_sql);
$status_counts = [];
while ($row = mysqli_fetch_assoc($status_result)) {
    $status_counts[$row['status']] = $row['count'];
}

// Top selling products
$top_products_sql = "SELECT p.name, SUM(oi.quantity) as total_sold, SUM(oi.quantity * oi.price) as total_revenue
                     FROM order_items oi
                     JOIN products p ON oi.product_id = p.id
                     GROUP BY p.id, p.name
                     ORDER BY total_sold DESC
                     LIMIT 10";
$top_products_result = mysqli_query($conn, $top_products_sql);

// Daily sales for the last 30 days
$daily_sales_sql = "SELECT DATE(order_date) as date, SUM(total_amount) as daily_revenue, COUNT(*) as daily_orders
                    FROM orders
                    WHERE order_date >= DATE_SUB(CURDATE(), INTERVAL 30 DAY) AND status != 'cancelled'
                    GROUP BY DATE(order_date)
                    ORDER BY date DESC";
$daily_sales_result = mysqli_query($conn, $daily_sales_sql);

mysqli_close($conn);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Reports - Furnessence Admin</title>
    <link rel="stylesheet" href="../style.css">
    <style>
        .admin-dashboard {
            display: flex;
            min-height: 100vh;
        }

        .admin-sidebar {
            width: 250px;
            background-color: var(--smokey-black);
            color: var(--white);
            padding: 20px;
        }

        .admin-sidebar h2 {
            margin-bottom: 30px;
            color: var(--tan-crayola);
        }

        .admin-sidebar ul {
            list-style: none;
            padding: 0;
        }

        .admin-sidebar li {
            margin-bottom: 10px;
        }

        .admin-sidebar a {
            color: var(--white);
            text-decoration: none;
            display: block;
            padding: 10px;
            border-radius: 4px;
            transition: var(--transition-1);
        }

        .admin-sidebar a:hover,
        .admin-sidebar a.active {
            background-color: var(--tan-crayola);
        }

        .admin-content {
            flex: 1;
            padding: 30px;
        }

        .page-header {
            margin-bottom: 30px;
        }

        .stats-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 20px;
            margin-bottom: 40px;
        }

        .stat-card {
            background-color: var(--white);
            padding: 25px;
            border-radius: 8px;
            box-shadow: var(--shadow);
            text-align: center;
        }

        .stat-card h3 {
            font-size: 3rem;
            color: var(--tan-crayola);
            margin-bottom: 10px;
        }

        .stat-card p {
            color: var(--granite-gray);
            font-size: 1.6rem;
        }

        .reports-section {
            background-color: var(--white);
            padding: 25px;
            border-radius: 8px;
            box-shadow: var(--shadow);
            margin-bottom: 30px;
        }

        .reports-section h2 {
            margin-bottom: 20px;
            color: var(--smokey-black);
        }

        .status-distribution {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(150px, 1fr));
            gap: 15px;
            margin-bottom: 30px;
        }

        .status-item {
            background-color: var(--cultured);
            padding: 15px;
            border-radius: 4px;
            text-align: center;
        }

        .status-item h4 {
            margin-bottom: 5px;
            color: var(--smokey-black);
        }

        .status-item .count {
            font-size: 2rem;
            font-weight: var(--fw-500);
            color: var(--tan-crayola);
        }

        .reports-table {
            width: 100%;
            border-collapse: collapse;
        }

        .reports-table th,
        .reports-table td {
            padding: 12px;
            text-align: left;
            border-bottom: 1px solid var(--light-gray);
        }

        .reports-table th {
            background-color: var(--cultured);
            font-weight: var(--fw-500);
            color: var(--smokey-black);
        }

        .product-name {
            font-weight: var(--fw-500);
            color: var(--smokey-black);
        }

        .revenue-amount {
            font-weight: var(--fw-500);
            color: var(--tan-crayola);
        }

        .no-data {
            text-align: center;
            padding: 40px;
            color: var(--granite-gray);
            font-size: 1.6rem;
        }
    </style>
</head>
<body>
    <div class="admin-dashboard">
        <div class="admin-sidebar">
            <h2>Admin Panel</h2>
            <ul>
                <li><a href="Admindashboard.php">Dashboard</a></li>
                <li><a href="manage-products.php">Manage Products</a></li>
                <li><a href="manage-orders.php">Manage Orders</a></li>
                <li><a href="manage-users.php">Manage Users</a></li>
                <li><a href="manage-categories.php">Categories</a></li>
                <li><a href="reports.php" class="active">Reports</a></li>
            </ul>
        </div>

        <div class="admin-content">
            <div class="page-header">
                <h1>Sales Reports</h1>
            </div>

            <div class="stats-grid">
                <div class="stat-card">
                    <h3>$<?php echo number_format($total_revenue, 2); ?></h3>
                    <p>Total Revenue</p>
                </div>
                <div class="stat-card">
                    <h3><?php echo $total_orders; ?></h3>
                    <p>Total Orders</p>
                </div>
                <div class="stat-card">
                    <h3><?php echo number_format($total_revenue / max($total_orders, 1), 2); ?></h3>
                    <p>Average Order Value</p>
                </div>
                <div class="stat-card">
                    <h3><?php echo isset($status_counts['completed']) ? $status_counts['completed'] : 0; ?></h3>
                    <p>Completed Orders</p>
                </div>
            </div>

            <div class="reports-section">
                <h2>Order Status Distribution</h2>
                <div class="status-distribution">
                    <?php
                    $statuses = ['pending', 'processing', 'shipped', 'delivered', 'cancelled'];
                    foreach ($statuses as $status) {
                        $count = isset($status_counts[$status]) ? $status_counts[$status] : 0;
                        echo "<div class='status-item'>
                                <h4>" . ucfirst($status) . "</h4>
                                <div class='count'>$count</div>
                              </div>";
                    }
                    ?>
                </div>
            </div>

            <div class="reports-section">
                <h2>Top Selling Products</h2>
                <table class="reports-table">
                    <thead>
                        <tr>
                            <th>Product Name</th>
                            <th>Units Sold</th>
                            <th>Revenue</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (mysqli_num_rows($top_products_result) > 0): ?>
                            <?php while ($product = mysqli_fetch_assoc($top_products_result)): ?>
                                <tr>
                                    <td><span class="product-name"><?php echo htmlspecialchars($product['name']); ?></span></td>
                                    <td><?php echo $product['total_sold']; ?></td>
                                    <td><span class="revenue-amount">$<?php echo number_format($product['total_revenue'], 2); ?></span></td>
                                </tr>
                            <?php endwhile; ?>
                        <?php else: ?>
                            <tr>
                                <td colspan="3" class="no-data">No sales data available.</td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>

            <div class="reports-section">
                <h2>Daily Sales (Last 30 Days)</h2>
                <table class="reports-table">
                    <thead>
                        <tr>
                            <th>Date</th>
                            <th>Orders</th>
                            <th>Revenue</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (mysqli_num_rows($daily_sales_result) > 0): ?>
                            <?php while ($day = mysqli_fetch_assoc($daily_sales_result)): ?>
                                <tr>
                                    <td><?php echo date('M d, Y', strtotime($day['date'])); ?></td>
                                    <td><?php echo $day['daily_orders']; ?></td>
                                    <td><span class="revenue-amount">$<?php echo number_format($day['daily_revenue'], 2); ?></span></td>
                                </tr>
                            <?php endwhile; ?>
                        <?php else: ?>
                            <tr>
                                <td colspan="3" class="no-data">No sales data available for the last 30 days.</td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</body>
</html>
