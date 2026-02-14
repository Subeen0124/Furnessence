<?php
require_once 'admin_config.php';
requireAdminLogin();

$admin = getAdminInfo();

// Handle order status update
if (isset($_POST['update_status'])) {
    $order_id = intval($_POST['order_id']);
    $status = $_POST['status'];
    $update_stmt = mysqli_prepare($conn, "UPDATE orders SET status = ? WHERE id = ?");
    mysqli_stmt_bind_param($update_stmt, "si", $status, $order_id);
    mysqli_stmt_execute($update_stmt);
    mysqli_stmt_close($update_stmt);
    $success = 'Order status updated!';
}

// Get filter
$status_filter = isset($_GET['status']) ? $_GET['status'] : 'all';
$valid_statuses = ['all', 'pending', 'processing', 'completed', 'cancelled'];
if (!in_array($status_filter, $valid_statuses)) {
    $status_filter = 'all';
}

if ($status_filter !== 'all') {
    $orders_stmt = mysqli_prepare($conn, "SELECT o.*, u.name as user_name, u.email as user_email 
        FROM orders o 
        LEFT JOIN users u ON o.user_id = u.id 
        WHERE o.status = ?
        ORDER BY o.id ASC");
    mysqli_stmt_bind_param($orders_stmt, "s", $status_filter);
    mysqli_stmt_execute($orders_stmt);
    $orders_result = mysqli_stmt_get_result($orders_stmt);
    mysqli_stmt_close($orders_stmt);
} else {
    $orders_query = "SELECT o.*, u.name as user_name, u.email as user_email 
        FROM orders o 
        LEFT JOIN users u ON o.user_id = u.id 
        ORDER BY o.id ASC";
    $orders_result = mysqli_query($conn, $orders_query);
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Orders - Furnessence Admin</title>
    <link rel="stylesheet" href="../assests/css/admin.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>
<body class="admin-body">
    <?php include 'includes/sidebar.php'; ?>
    
    <div class="admin-main">
        <?php include 'includes/header.php'; ?>
        
        <div class="admin-content">
            <div class="page-header">
                <h1>Manage Orders</h1>
            </div>
            
            <?php if (isset($success)): ?>
                <div class="alert alert-success"><?php echo $success; ?></div>
            <?php endif; ?>
            
            <div class="table-card">
                <div class="table-responsive">
                    <table class="admin-table">
                        <thead>
                            <tr>
                                <th>Order #</th>
                                <th>Customer</th>
                                <th>Amount</th>
                                <th>Payment</th>
                                <th>Pay Status</th>
                                <th>Status</th>
                                <th>Date</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (mysqli_num_rows($orders_result) > 0): ?>
                                <?php while($order = mysqli_fetch_assoc($orders_result)): ?>
                                    <tr>
                                        <td>#<?php echo htmlspecialchars($order['order_number']); ?></td>
                                        <td><?php echo htmlspecialchars($order['user_name']); ?></td>
                                        <td>Rs <?php echo number_format($order['total_amount'], 2); ?></td>
                                        <td>
                                            <?php 
                                            $pm = $order['payment_method'] ?? 'cod';
                                            $pm_labels = ['cod' => 'COD', 'khalti' => 'Khalti', 'esewa' => 'eSewa', 'bank' => 'Bank'];
                                            $pm_icons = ['cod' => 'fa-money-bill', 'khalti' => 'fa-wallet', 'esewa' => 'fa-mobile-screen', 'bank' => 'fa-building-columns'];
                                            ?>
                                            <span class="status-badge status-<?php echo $pm === 'khalti' ? 'processing' : ($pm === 'esewa' ? 'completed' : 'pending'); ?>" style="font-size: 0.78rem;">
                                                <i class="fas <?php echo $pm_icons[$pm] ?? 'fa-money-bill'; ?>"></i>
                                                <?php echo $pm_labels[$pm] ?? ucfirst($pm); ?>
                                            </span>
                                        </td>
                                        <td>
                                            <?php 
                                            $ps = $order['payment_status'] ?? 'pending';
                                            $ps_class = ['paid' => 'completed', 'pending' => 'pending', 'failed' => 'cancelled'];
                                            ?>
                                            <span class="status-badge status-<?php echo $ps_class[$ps] ?? 'pending'; ?>">
                                                <?php echo ucfirst($ps); ?>
                                            </span>
                                        </td>
                                        <td>
                                            <form method="POST" class="inline-form">
                                                <input type="hidden" name="order_id" value="<?php echo $order['id']; ?>">
                                                <select name="status" onchange="this.form.submit()" class="status-select">
                                                    <option value="pending" <?php echo $order['status'] == 'pending' ? 'selected' : ''; ?>>Pending</option>
                                                    <option value="processing" <?php echo $order['status'] == 'processing' ? 'selected' : ''; ?>>Processing</option>
                                                    <option value="completed" <?php echo $order['status'] == 'completed' ? 'selected' : ''; ?>>Completed</option>
                                                    <option value="cancelled" <?php echo $order['status'] == 'cancelled' ? 'selected' : ''; ?>>Cancelled</option>
                                                </select>
                                                <input type="hidden" name="update_status" value="1">
                                            </form>
                                        </td>
                                        <td><?php echo date('M d, Y', strtotime($order['created_at'])); ?></td>
                                        <td>
                                            <a href="view_order.php?id=<?php echo $order['id']; ?>" class="btn-action btn-view">
                                                <i class="fas fa-eye"></i>
                                            </a>
                                        </td>
                                    </tr>
                                <?php endwhile; ?>
                            <?php else: ?>
                                <tr>
                                    <td colspan="8" class="no-data">No orders found</td>
                                </tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</body>
</html>
