<?php
session_start();

// Check if admin is logged in
if (!isset($_SESSION['admin_logged_in']) || $_SESSION['admin_logged_in'] !== true) {
    header("location: Adminlogin.php");
    exit();
}

// Verify admin role
if (!isset($_SESSION['admin_role']) || $_SESSION['admin_role'] !== 'admin') {
    session_destroy();
    header("location: Adminlogin.php");
    exit();
}

// Include config file
require_once '../config.php';

// Establish database connection
$conn = getDBConnection();

// Handle order status update
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['update_status'])) {
    $order_id = (int)$_POST['order_id'];
    $new_status = trim($_POST['status']);

    $valid_statuses = ['pending', 'processing', 'shipped', 'delivered', 'cancelled'];
    if (in_array($new_status, $valid_statuses)) {
        $update_sql = "UPDATE orders SET status = ? WHERE id = ?";
        if ($stmt = mysqli_prepare($conn, $update_sql)) {
            mysqli_stmt_bind_param($stmt, "si", $new_status, $order_id);
            if (mysqli_stmt_execute($stmt)) {
                $success_message = "Order status updated successfully.";
            } else {
                $error_message = "Error updating order status.";
            }
            mysqli_stmt_close($stmt);
        }
    } else {
        $error_message = "Invalid status selected.";
    }

    header("location: manage-orders.php");
    exit();
}

// Get all orders with user details
$orders_sql = "SELECT o.id, o.total_amount, o.status, o.order_date, o.shipping_address,
                      u.username, u.email
               FROM orders o
               LEFT JOIN users u ON o.user_id = u.id
               ORDER BY o.order_date DESC";
$orders_result = mysqli_query($conn, $orders_sql);

mysqli_close($conn);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Orders - Furnessence Admin</title>
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

        .page-header {
            margin-bottom: 30px;
        }

        .message {
            padding: 15px;
            border-radius: 4px;
            margin-bottom: 20px;
            font-weight: var(--fw-500);
        }

        .message.success {
            background-color: var(--green);
            color: var(--white);
        }

        .message.error {
            background-color: var(--red-orange-color-wheel);
            color: var(--white);
        }

        .orders-table {
            width: 100%;
            background-color: var(--white);
            border-radius: 8px;
            box-shadow: var(--shadow);
            overflow: hidden;
        }

        .orders-table table {
            width: 100%;
            border-collapse: collapse;
        }

        .orders-table th,
        .orders-table td {
            padding: 15px;
            text-align: left;
            border-bottom: 1px solid var(--light-gray);
        }

        .orders-table th {
            background-color: var(--cultured);
            font-weight: var(--fw-500);
            color: var(--smokey-black);
        }

        .orders-table tbody tr:hover {
            background-color: var(--cultured);
        }

        .order-id {
            font-weight: var(--fw-500);
            color: var(--tan-crayola);
        }

        .customer-info {
            display: flex;
            flex-direction: column;
        }

        .customer-name {
            font-weight: var(--fw-500);
            color: var(--smokey-black);
        }

        .customer-email {
            color: var(--granite-gray);
            font-size: 1.4rem;
        }

        .order-amount {
            font-weight: var(--fw-500);
            color: var(--smokey-black);
            font-size: 1.6rem;
        }

        .status-badge {
            padding: 5px 12px;
            border-radius: 20px;
            font-size: 1.2rem;
            font-weight: var(--fw-500);
            text-transform: uppercase;
        }

        .status-pending {
            background-color: var(--yellow);
            color: var(--smokey-black);
        }

        .status-processing {
            background-color: var(--tan-crayola);
            color: var(--white);
        }

        .status-shipped {
            background-color: var(--blue);
            color: var(--white);
        }

        .status-delivered {
            background-color: var(--green);
            color: var(--white);
        }

        .status-cancelled {
            background-color: var(--red-orange-color-wheel);
            color: var(--white);
        }

        .order-date {
            color: var(--granite-gray);
            font-size: 1.4rem;
        }

        .actions-cell {
            min-width: 200px;
        }

        .status-select {
            padding: 8px 12px;
            border: 1px solid var(--light-gray);
            border-radius: 4px;
            font-size: 1.4rem;
            margin-right: 10px;
        }

        .btn-update-status {
            padding: 8px 15px;
            background-color: var(--tan-crayola);
            color: var(--white);
            border: none;
            border-radius: 4px;
            font-size: 1.4rem;
            font-weight: var(--fw-500);
            cursor: pointer;
            transition: var(--transition-1);
        }

        .btn-update-status:hover {
            background-color: var(--smokey-black);
        }

        .btn-view-details {
            padding: 8px 15px;
            background-color: var(--granite-gray);
            color: var(--white);
            text-decoration: none;
            border-radius: 4px;
            font-size: 1.4rem;
            font-weight: var(--fw-500);
            margin-left: 10px;
            transition: var(--transition-1);
        }

        .btn-view-details:hover {
            background-color: var(--smokey-black);
        }

        .no-orders {
            text-align: center;
            padding: 40px;
            color: var(--granite-gray);
            font-size: 1.8rem;
        }

        .modal {
            display: none;
            position: fixed;
            z-index: 1000;
            left: 0;
            top: 0;
            width: 100%;
            height: 100%;
            background-color: rgba(0, 0, 0, 0.5);
        }

        .modal-content {
            background-color: var(--white);
            margin: 5% auto;
            padding: 30px;
            border-radius: 8px;
            width: 80%;
            max-width: 600px;
            max-height: 80vh;
            overflow-y: auto;
        }

        .close {
            color: var(--granite-gray);
            float: right;
            font-size: 28px;
            font-weight: bold;
            cursor: pointer;
        }

        .close:hover {
            color: var(--smokey-black);
        }

        .order-details h3 {
            margin-bottom: 20px;
            color: var(--smokey-black);
        }

        .order-details-grid {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 20px;
            margin-bottom: 20px;
        }

        .detail-item {
            margin-bottom: 10px;
        }

        .detail-label {
            font-weight: var(--fw-500);
            color: var(--smokey-black);
        }

        .detail-value {
            color: var(--granite-gray);
        }

        .order-items {
            margin-top: 20px;
        }

        .order-items h4 {
            margin-bottom: 15px;
            color: var(--smokey-black);
        }

        .items-table {
            width: 100%;
            border-collapse: collapse;
        }

        .items-table th,
        .items-table td {
            padding: 10px;
            text-align: left;
            border-bottom: 1px solid var(--light-gray);
        }

        .items-table th {
            background-color: var(--cultured);
            font-weight: var(--fw-500);
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
                <li><a href="manage-orders.php" class="active">Manage Orders</a></li>
                <li><a href="manage-users.php">Manage Users</a></li>
                <li><a href="manage-categories.php">Categories</a></li>
                <li><a href="reports.php">Reports</a></li>
            </ul>
        </div>

        <div class="admin-content">
            <div class="page-header">
                <h1>Manage Orders</h1>
            </div>

            <?php if (isset($success_message)): ?>
                <div class="message success"><?php echo $success_message; ?></div>
            <?php endif; ?>

            <?php if (isset($error_message)): ?>
                <div class="message error"><?php echo $error_message; ?></div>
            <?php endif; ?>

            <div class="orders-table">
                <table>
                    <thead>
                        <tr>
                            <th>Order ID</th>
                            <th>Customer</th>
                            <th>Amount</th>
                            <th>Status</th>
                            <th>Date</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (mysqli_num_rows($orders_result) > 0): ?>
                            <?php while ($order = mysqli_fetch_assoc($orders_result)): ?>
                                <tr>
                                    <td>
                                        <span class="order-id">#<?php echo $order['id']; ?></span>
                                    </td>
                                    <td>
                                        <div class="customer-info">
                                            <span class="customer-name"><?php echo htmlspecialchars($order['username'] ?? 'Guest'); ?></span>
                                            <span class="customer-email"><?php echo htmlspecialchars($order['email'] ?? 'N/A'); ?></span>
                                        </div>
                                    </td>
                                    <td>
                                        <span class="order-amount">$<?php echo number_format($order['total_amount'], 2); ?></span>
                                    </td>
                                    <td>
                                        <span class="status-badge status-<?php echo strtolower($order['status']); ?>">
                                            <?php echo ucfirst($order['status']); ?>
                                        </span>
                                    </td>
                                    <td>
                                        <span class="order-date"><?php echo date('M d, Y H:i', strtotime($order['created_at'])); ?></span>
                                    </td>
                                    <td class="actions-cell">
                                        <form method="post" style="display: inline;">
                                            <input type="hidden" name="order_id" value="<?php echo $order['id']; ?>">
                                            <select name="status" class="status-select">
                                                <option value="pending" <?php echo ($order['status'] == 'pending') ? 'selected' : ''; ?>>Pending</option>
                                                <option value="processing" <?php echo ($order['status'] == 'processing') ? 'selected' : ''; ?>>Processing</option>
                                                <option value="shipped" <?php echo ($order['status'] == 'shipped') ? 'selected' : ''; ?>>Shipped</option>
                                                <option value="delivered" <?php echo ($order['status'] == 'delivered') ? 'selected' : ''; ?>>Delivered</option>
                                                <option value="cancelled" <?php echo ($order['status'] == 'cancelled') ? 'selected' : ''; ?>>Cancelled</option>
                                            </select>
                                            <button type="submit" name="update_status" class="btn-update-status">Update</button>
                                        </form>
                                        <a href="#" class="btn-view-details" onclick="viewOrderDetails(<?php echo $order['id']; ?>)">View Details</a>
                                    </td>
                                </tr>
                            <?php endwhile; ?>
                        <?php else: ?>
                            <tr>
                                <td colspan="6" class="no-orders">No orders found.</td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <!-- Order Details Modal -->
    <div id="orderDetailsModal" class="modal">
        <div class="modal-content">
            <span class="close" onclick="closeModal()">&times;</span>
            <div id="orderDetailsContent">
                <!-- Order details will be loaded here via AJAX -->
            </div>
        </div>
    </div>

    <script>
        function viewOrderDetails(orderId) {
            // For now, just show a placeholder. In a real application, you'd make an AJAX call
            const modal = document.getElementById('orderDetailsModal');
            const content = document.getElementById('orderDetailsContent');

            content.innerHTML = `
                <div class="order-details">
                    <h3>Order #${orderId} Details</h3>
                    <p>Order details functionality would be implemented here with AJAX to load order items, shipping info, etc.</p>
                </div>
            `;

            modal.style.display = 'block';
        }

        function closeModal() {
            document.getElementById('orderDetailsModal').style.display = 'none';
        }

        // Close modal when clicking outside
        window.onclick = function(event) {
            const modal = document.getElementById('orderDetailsModal');
            if (event.target == modal) {
                modal.style.display = 'none';
            }
        }
    </script>
</body>
</html>
