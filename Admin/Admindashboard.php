<?php
session_start();

// Check if admin is logged in and has admin role
if (!isset($_SESSION['admin_logged_in']) || $_SESSION['admin_logged_in'] !== true) {
    header("location: Adminlogin.php");
    exit();
}

// Additional security: verify admin role
if (!isset($_SESSION['admin_role']) || $_SESSION['admin_role'] !== 'admin') {
    session_destroy();
    header("location: Adminlogin.php");
    exit();
}

// Include config file
require_once '../config.php';

// Establish database connection
$conn = getDBConnection();

// Get dashboard statistics
// Total products
$products_sql = "SELECT COUNT(*) as total_products FROM products";
$products_result = mysqli_query($conn, $products_sql);
$products_count = mysqli_fetch_assoc($products_result)['total_products'];

// Total orders
$orders_sql = "SELECT COUNT(*) as total_orders FROM orders";
$orders_result = mysqli_query($conn, $orders_sql);
$orders_count = mysqli_fetch_assoc($orders_result)['total_orders'];

// Total users
$users_sql = "SELECT COUNT(*) as total_users FROM users";
$users_result = mysqli_query($conn, $users_sql);
$users_count = mysqli_fetch_assoc($users_result)['total_users'];

// Total revenue
$revenue_sql = "SELECT SUM(total_amount) as total_revenue FROM orders WHERE status != 'cancelled'";
$revenue_result = mysqli_query($conn, $revenue_sql);
$total_revenue = mysqli_fetch_assoc($revenue_result)['total_revenue'] ?? 0;

// Recent orders
$recent_orders_sql = "SELECT o.id, o.total_amount, o.status, o.order_date, u.username
                     FROM orders o
                     LEFT JOIN users u ON o.user_id = u.id
                     ORDER BY o.order_date DESC LIMIT 5";
$recent_orders_result = mysqli_query($conn, $recent_orders_sql);

mysqli_close($conn);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard - Furnessence</title>
    <link rel="stylesheet" href="../assets/css/style.css">
    <link rel="stylesheet" href="../assets/css/admin.css">
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

        .dashboard-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 30px;
        }

        .dashboard-header h1 {
            color: var(--smokey-black);
        }

        .logout-btn {
            padding: 10px 20px;
            background-color: var(--red-orange-color-wheel);
            color: var(--white);
            text-decoration: none;
            border-radius: 4px;
            transition: var(--transition-1);
        }

        .logout-btn:hover {
            background-color: var(--smokey-black);
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

        .recent-orders {
            background-color: var(--white);
            padding: 25px;
            border-radius: 8px;
            box-shadow: var(--shadow);
        }

        .recent-orders h2 {
            margin-bottom: 20px;
            color: var(--smokey-black);
        }

        .orders-table {
            width: 100%;
            border-collapse: collapse;
        }

        .orders-table th,
        .orders-table td {
            padding: 12px;
            text-align: left;
            border-bottom: 1px solid var(--light-gray);
        }

        .orders-table th {
            background-color: var(--cultured);
            font-weight: var(--fw-500);
        }

        .status-badge {
            padding: 5px 10px;
            border-radius: 4px;
            font-size: 1.2rem;
            font-weight: var(--fw-500);
        }

        .status-pending {
            background-color: var(--yellow);
            color: var(--smokey-black);
        }

        .status-processing {
            background-color: var(--tan-crayola);
            color: var(--white);
        }

        .status-completed {
            background-color: var(--green);
            color: var(--white);
        }

        .status-cancelled {
            background-color: var(--red-orange-color-wheel);
            color: var(--white);
        }
    </style>
</head>
<body>
    <div class="admin-dashboard">
        <div class="admin-sidebar">
            <h2>Admin Panel</h2>
            <ul>
                <li><a href="Admindashboard.php" class="active">Dashboard</a></li>
                <li><a href="manage-products.php">Manage Products</a></li>
                <li><a href="manage-orders.php">Manage Orders</a></li>
                <li><a href="manage-users.php">Manage Users</a></li>
                <li><a href="manage-categories.php">Categories</a></li>
                <li><a href="reports.php">Reports</a></li>
            </ul>
        </div>

        <div class="admin-content">
            <div class="dashboard-header">
                <h1>Dashboard</h1>
                <a href="Adminlogout.php" class="logout-btn">Logout</a>
            </div>

            <div class="stats-grid">
                <div class="stat-card">
                    <h3><?php echo $products_count; ?></h3>
                    <p>Total Products</p>
                </div>
                <div class="stat-card">
                    <h3><?php echo $orders_count; ?></h3>
                    <p>Total Orders</p>
                </div>
                <div class="stat-card">
                    <h3><?php echo $users_count; ?></h3>
                    <p>Total Users</p>
                </div>
                <div class="stat-card">
                    <h3>$<?php echo number_format($total_revenue ?? 0, 2); ?></h3>
                    <p>Total Revenue</p>
                </div>
            </div>

            <div class="recent-orders">
                <h2>Recent Orders</h2>
                <table class="orders-table">
                    <thead>
                        <tr>
                            <th>Order ID</th>
                            <th>Customer</th>
                            <th>Amount</th>
                            <th>Status</th>
                            <th>Date</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php while ($order = mysqli_fetch_assoc($recent_orders_result)): ?>
                            <tr>
                                <td>#<?php echo $order['id']; ?></td>
                                <td><?php echo htmlspecialchars($order['username'] ?? 'Guest'); ?></td>
                                <td>$<?php echo number_format($order['total_amount'], 2); ?></td>
                                <td>
                                    <span class="status-badge status-<?php echo strtolower($order['status']); ?>">
                                        <?php echo ucfirst($order['status']); ?>
                                    </span>
                                </td>
                                <td><?php echo date('M d, Y', strtotime($order['order_date'])); ?></td>
                            </tr>
                        <?php endwhile; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</body>
</html>
