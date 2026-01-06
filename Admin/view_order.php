<?php
require_once 'admin_config.php';
requireAdminLogin();

$admin = getAdminInfo();

// Get order ID
$order_id = isset($_GET['id']) ? intval($_GET['id']) : 0;

if ($order_id === 0) {
    header('Location: manage_orders.php');
    exit();
}

// Get order details
$order_query = "SELECT o.*, u.name as user_name, u.email as user_email 
    FROM orders o 
    LEFT JOIN users u ON o.user_id = u.id 
    WHERE o.id = $order_id 
    LIMIT 1";
$order_result = mysqli_query($conn, $order_query);

if (mysqli_num_rows($order_result) === 0) {
    header('Location: manage_orders.php');
    exit();
}

$order = mysqli_fetch_assoc($order_result);

// Get order items
$items_query = "SELECT * FROM order_items WHERE order_id = $order_id";
$items_result = mysqli_query($conn, $items_query);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>View Order #<?php echo $order['order_number']; ?> - Furnessence Admin</title>
    <link rel="stylesheet" href="../assests/css/admin.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        .order-details-grid {
            display: grid;
            grid-template-columns: 2fr 1fr;
            gap: 20px;
            margin-top: 20px;
        }
        
        .detail-row {
            display: flex;
            justify-content: space-between;
            padding: 12px 0;
            border-bottom: 1px solid var(--border-color);
        }
        
        .detail-row:last-child {
            border-bottom: none;
        }
        
        .detail-label {
            font-weight: 600;
            color: var(--dark-color);
        }
        
        .detail-value {
            color: var(--gray);
        }
        
        @media (max-width: 768px) {
            .order-details-grid {
                grid-template-columns: 1fr;
            }
        }
    </style>
</head>
<body class="admin-body">
    <?php include 'includes/sidebar.php'; ?>
    
    <div class="admin-main">
        <?php include 'includes/header.php'; ?>
        
        <div class="admin-content">
            <div class="page-header">
                <div>
                    <h1>Order #<?php echo htmlspecialchars($order['order_number']); ?></h1>
                    <p>Order placed on <?php echo date('F d, Y \a\t h:i A', strtotime($order['created_at'])); ?></p>
                </div>
                <a href="manage_orders.php" class="btn-secondary">
                    <i class="fas fa-arrow-left"></i> Back to Orders
                </a>
            </div>
            
            <div class="order-details-grid">
                <!-- Order Items -->
                <div class="table-card">
                    <div class="card-header">
                        <h2><i class="fas fa-box"></i> Order Items</h2>
                    </div>
                    <div class="table-responsive">
                        <table class="admin-table">
                            <thead>
                                <tr>
                                    <th>Product</th>
                                    <th>Price</th>
                                    <th>Quantity</th>
                                    <th>Subtotal</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php 
                                $total = 0;
                                while($item = mysqli_fetch_assoc($items_result)): 
                                    $total += $item['subtotal'];
                                ?>
                                    <tr>
                                        <td><?php echo htmlspecialchars($item['product_name']); ?></td>
                                        <td>Rs <?php echo number_format($item['product_price'], 2); ?></td>
                                        <td><?php echo $item['quantity']; ?></td>
                                        <td>Rs <?php echo number_format($item['subtotal'], 2); ?></td>
                                    </tr>
                                <?php endwhile; ?>
                                <tr class="order-total-row">
                                    <td colspan="3" class="total-label">Total:</td>
                                    <td>Rs <?php echo number_format($order['total_amount'], 2); ?></td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
                
                <!-- Order Info Sidebar -->
                <div>
                    <!-- Customer Information -->
                    <div class="table-card order-info-card">
                        <div class="card-header">
                            <h2><i class="fas fa-user"></i> Customer</h2>
                        </div>
                        <div class="card-content">
                            <div class="detail-row">
                                <span class="detail-label">Name:</span>
                                <span class="detail-value"><?php echo htmlspecialchars($order['user_name']); ?></span>
                            </div>
                            <div class="detail-row">
                                <span class="detail-label">Email:</span>
                                <span class="detail-value"><?php echo htmlspecialchars($order['user_email']); ?></span>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Order Status -->
                    <div class="table-card order-info-card">
                        <div class="card-header">
                            <h2><i class="fas fa-info-circle"></i> Status</h2>
                        </div>
                        <div class="card-content">
                            <form method="POST" action="manage_orders.php">
                                <input type="hidden" name="order_id" value="<?php echo $order['id']; ?>">
                                <input type="hidden" name="update_status" value="1">
                                <select name="status" class="form-group status-select">
                                    <option value="pending" <?php echo $order['status'] == 'pending' ? 'selected' : ''; ?>>Pending</option>
                                    <option value="processing" <?php echo $order['status'] == 'processing' ? 'selected' : ''; ?>>Processing</option>
                                    <option value="completed" <?php echo $order['status'] == 'completed' ? 'selected' : ''; ?>>Completed</option>
                                    <option value="cancelled" <?php echo $order['status'] == 'cancelled' ? 'selected' : ''; ?>>Cancelled</option>
                                </select>
                                <button type="submit" class="btn-primary btn-full-width">
                                    <i class="fas fa-save"></i> Update Status
                                </button>
                            </form>
                        </div>
                    </div>
                    
                    <!-- Shipping Address -->
                    <div class="table-card">
                        <div class="card-header">
                            <h2><i class="fas fa-map-marker-alt"></i> Shipping</h2>
                        </div>
                        <div class="card-content">
                            <p class="shipping-address">
                                <?php echo nl2br(htmlspecialchars($order['shipping_address'])); ?>
                            </p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</body>
</html>
