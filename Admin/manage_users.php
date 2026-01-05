<?php
require_once 'admin_config.php';
requireAdminLogin();

$admin = getAdminInfo();

$users_query = "SELECT * FROM users ORDER BY id ASC";
$users_result = mysqli_query($conn, $users_query);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Users - Furnessence Admin</title>
    <link rel="stylesheet" href="../assests/css/admin.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>
<body class="admin-body">
    <?php include 'includes/sidebar.php'; ?>
    
    <div class="admin-main">
        <?php include 'includes/header.php'; ?>
        
        <div class="admin-content">
            <div class="page-header">
                <h1>Manage Users</h1>
            </div>
            
            <div class="table-card">
                <div class="table-responsive">
                    <table class="admin-table">
                        <thead>
                            <tr>
                                <th>ID</th>
                                <th>Name</th>
                                <th>Email</th>
                                <th>Registered</th>
                                <th>Orders</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (mysqli_num_rows($users_result) > 0): ?>
                                <?php while($user = mysqli_fetch_assoc($users_result)): 
                                    $order_count_query = "SELECT COUNT(*) as count FROM orders WHERE user_id = {$user['id']}";
                                    $order_count_result = mysqli_query($conn, $order_count_query);
                                    $order_count = mysqli_fetch_assoc($order_count_result)['count'];
                                ?>
                                    <tr>
                                        <td><?php echo $user['id']; ?></td>
                                        <td><?php echo htmlspecialchars($user['name']); ?></td>
                                        <td><?php echo htmlspecialchars($user['email']); ?></td>
                                        <td><?php echo date('M d, Y', strtotime($user['created_at'])); ?></td>
                                        <td><?php echo $order_count; ?></td>
                                    </tr>
                                <?php endwhile; ?>
                            <?php else: ?>
                                <tr>
                                    <td colspan="5" class="no-data">No users found</td>
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
