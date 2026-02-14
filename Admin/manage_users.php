<?php
require_once 'admin_config.php';
requireAdminLogin();

$admin = getAdminInfo();

// Handle status toggle
if (isset($_POST['toggle_status']) && isset($_POST['user_id'])) {
    $uid = intval($_POST['user_id']);
    $new_status = $_POST['new_status'] === 'active' ? 'active' : 'inactive';
    $toggle_stmt = mysqli_prepare($conn, "UPDATE users SET status = ? WHERE id = ?");
    mysqli_stmt_bind_param($toggle_stmt, "si", $new_status, $uid);
    if (mysqli_stmt_execute($toggle_stmt)) {
        $success = 'User status updated to ' . $new_status . '!';
    } else {
        $error = 'Failed to update user status.';
    }
    mysqli_stmt_close($toggle_stmt);
}

// Filter
$status_filter = isset($_GET['status']) ? $_GET['status'] : 'all';
$valid = ['all', 'active', 'inactive'];
if (!in_array($status_filter, $valid)) $status_filter = 'all';

if ($status_filter !== 'all') {
    $users_stmt = mysqli_prepare($conn, "SELECT * FROM users WHERE status = ? ORDER BY id ASC");
    mysqli_stmt_bind_param($users_stmt, "s", $status_filter);
    mysqli_stmt_execute($users_stmt);
    $users_result = mysqli_stmt_get_result($users_stmt);
    mysqli_stmt_close($users_stmt);
} else {
    $users_result = mysqli_query($conn, "SELECT * FROM users ORDER BY id ASC");
}

// Get counts
$count_result = mysqli_query($conn, "SELECT status, COUNT(*) as count FROM users GROUP BY status");
$status_counts = ['all' => 0, 'active' => 0, 'inactive' => 0];
while ($row = mysqli_fetch_assoc($count_result)) {
    $status_counts[$row['status']] = $row['count'];
    $status_counts['all'] += $row['count'];
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Users - Furnessence Admin</title>
    <link rel="stylesheet" href="../assests/css/admin.css?v=<?php echo time(); ?>">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>
<body class="admin-body">
    <?php include 'includes/sidebar.php'; ?>
    
    <div class="admin-main">
        <?php include 'includes/header.php'; ?>
        
        <div class="admin-content">
            <div class="page-header">
                <h1>Manage Users</h1>
                <p><?php echo $status_counts['all']; ?> total users</p>
            </div>

            <?php if (isset($success)): ?>
                <div class="alert alert-success"><i class="fas fa-check-circle"></i> <?php echo $success; ?></div>
            <?php endif; ?>
            <?php if (isset($error)): ?>
                <div class="alert alert-danger"><i class="fas fa-exclamation-circle"></i> <?php echo $error; ?></div>
            <?php endif; ?>

            <!-- Status Filter -->
            <div class="filters-card" style="margin-bottom: 20px;">
                <div class="filter-group" style="display:flex; gap:8px; padding:15px;">
                    <a href="manage_users.php" class="btn-filter <?php echo $status_filter === 'all' ? 'active' : ''; ?>">
                        All (<?php echo $status_counts['all']; ?>)
                    </a>
                    <a href="manage_users.php?status=active" class="btn-filter <?php echo $status_filter === 'active' ? 'active' : ''; ?>">
                        <i class="fas fa-check-circle" style="color:#27ae60;"></i> Active (<?php echo $status_counts['active']; ?>)
                    </a>
                    <a href="manage_users.php?status=inactive" class="btn-filter <?php echo $status_filter === 'inactive' ? 'active' : ''; ?>">
                        <i class="fas fa-ban" style="color:#e74c3c;"></i> Inactive (<?php echo $status_counts['inactive']; ?>)
                    </a>
                </div>
            </div>
            
            <div class="table-card">
                <div class="table-responsive">
                    <table class="admin-table">
                        <thead>
                            <tr>
                                <th>ID</th>
                                <th>Name</th>
                                <th>Email</th>
                                <th>Status</th>
                                <th>Registered</th>
                                <th>Orders</th>
                                <th>Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (mysqli_num_rows($users_result) > 0): ?>
                                <?php while($user = mysqli_fetch_assoc($users_result)): 
                                    $oc_stmt = mysqli_prepare($conn, "SELECT COUNT(*) as count FROM orders WHERE user_id = ?");
                                    mysqli_stmt_bind_param($oc_stmt, "i", $user['id']);
                                    mysqli_stmt_execute($oc_stmt);
                                    $order_count_result = mysqli_stmt_get_result($oc_stmt);
                                    $order_count = mysqli_fetch_assoc($order_count_result)['count'];
                                    mysqli_stmt_close($oc_stmt);
                                    $user_status = isset($user['status']) ? $user['status'] : 'active';
                                ?>
                                    <tr>
                                        <td><?php echo $user['id']; ?></td>
                                        <td><?php echo htmlspecialchars($user['name']); ?></td>
                                        <td><?php echo htmlspecialchars($user['email']); ?></td>
                                        <td>
                                            <span class="status-badge status-<?php echo $user_status === 'active' ? 'completed' : 'cancelled'; ?>">
                                                <i class="fas fa-<?php echo $user_status === 'active' ? 'check-circle' : 'ban'; ?>"></i>
                                                <?php echo ucfirst($user_status); ?>
                                            </span>
                                        </td>
                                        <td><?php echo date('M d, Y', strtotime($user['created_at'])); ?></td>
                                        <td><?php echo $order_count; ?></td>
                                        <td>
                                            <form method="POST" style="display:inline;" onsubmit="return confirm('Are you sure you want to <?php echo $user_status === 'active' ? 'deactivate' : 'activate'; ?> this user?');">
                                                <input type="hidden" name="user_id" value="<?php echo $user['id']; ?>">
                                                <input type="hidden" name="new_status" value="<?php echo $user_status === 'active' ? 'inactive' : 'active'; ?>">
                                                <?php if ($user_status === 'active'): ?>
                                                    <button type="submit" name="toggle_status" class="btn-action btn-delete" title="Deactivate user">
                                                        <i class="fas fa-user-slash"></i>
                                                    </button>
                                                <?php else: ?>
                                                    <button type="submit" name="toggle_status" class="btn-action btn-edit" title="Activate user">
                                                        <i class="fas fa-user-check"></i>
                                                    </button>
                                                <?php endif; ?>
                                            </form>
                                        </td>
                                    </tr>
                                <?php endwhile; ?>
                            <?php else: ?>
                                <tr>
                                    <td colspan="7" class="no-data">No users found</td>
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
