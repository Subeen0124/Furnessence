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

// Handle user status update
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['update_status'])) {
    $user_id = (int)$_POST['user_id'];
    $new_status = trim($_POST['status']);

    $valid_statuses = ['active', 'inactive'];
    if (in_array($new_status, $valid_statuses)) {
        $update_sql = "UPDATE users SET status = ? WHERE id = ?";
        if ($stmt = mysqli_prepare($conn, $update_sql)) {
            mysqli_stmt_bind_param($stmt, "si", $new_status, $user_id);
            if (mysqli_stmt_execute($stmt)) {
                $success_message = "User status updated successfully.";
            } else {
                $error_message = "Error updating user status.";
            }
            mysqli_stmt_close($stmt);
        }
    } else {
        $error_message = "Invalid status selected.";
    }

    header("location: manage-users.php");
    exit();
}

// Get all users
$users_sql = "SELECT id, username, email, status, created_at FROM users ORDER BY created_at DESC";
$users_result = mysqli_query($conn, $users_sql);

mysqli_close($conn);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Users - Furnessence Admin</title>
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

        .users-table {
            width: 100%;
            background-color: var(--white);
            border-radius: 8px;
            box-shadow: var(--shadow);
            overflow: hidden;
        }

        .users-table table {
            width: 100%;
            border-collapse: collapse;
        }

        .users-table th,
        .users-table td {
            padding: 15px;
            text-align: left;
            border-bottom: 1px solid var(--light-gray);
        }

        .users-table th {
            background-color: var(--cultured);
            font-weight: var(--fw-500);
            color: var(--smokey-black);
        }

        .users-table tbody tr:hover {
            background-color: var(--cultured);
        }

        .user-info {
            display: flex;
            flex-direction: column;
        }

        .user-name {
            font-weight: var(--fw-500);
            color: var(--smokey-black);
            margin-bottom: 5px;
        }

        .user-email {
            color: var(--granite-gray);
            font-size: 1.4rem;
        }

        .status-badge {
            padding: 5px 12px;
            border-radius: 20px;
            font-size: 1.2rem;
            font-weight: var(--fw-500);
            text-transform: uppercase;
        }

        .status-active {
            background-color: var(--green);
            color: var(--smokey-black);
        }

        .status-inactive {
            background-color: var(--red-orange-color-wheel);
            color: var(--blue-lagoon);
        }

        .registration-date {
            color: var(--granite-gray);
            font-size: 1.4rem;
        }

        .actions-cell {
            min-width: 150px;
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

        .no-users {
            text-align: center;
            padding: 40px;
            color: var(--granite-gray);
            font-size: 1.8rem;
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
                <li><a href="manage-users.php" class="active">Manage Users</a></li>
                <li><a href="manage-categories.php">Categories</a></li>
                <li><a href="reports.php">Reports</a></li>
            </ul>
        </div>

        <div class="admin-content">
            <div class="page-header">
                <h1>Manage Users</h1>
            </div>

            <?php if (isset($success_message)): ?>
                <div class="message success"><?php echo $success_message; ?></div>
            <?php endif; ?>

            <?php if (isset($error_message)): ?>
                <div class="message error"><?php echo $error_message; ?></div>
            <?php endif; ?>

            <div class="users-table">
                <table>
                    <thead>
                        <tr>
                            <th>User</th>
                            <th>Status</th>
                            <th>Registration Date</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (mysqli_num_rows($users_result) > 0): ?>
                            <?php while ($user = mysqli_fetch_assoc($users_result)): ?>
                                <tr>
                                    <td>
                                        <div class="user-info">
                                            <span class="user-name"><?php echo htmlspecialchars($user['username']); ?></span>
                                            <span class="user-email"><?php echo htmlspecialchars($user['email']); ?></span>
                                        </div>
                                    </td>
                                    <td>
                                        <span class="status-badge status-<?php echo strtolower($user['status']); ?>">
                                            <?php echo ucfirst($user['status']); ?>
                                        </span>
                                    </td>
                                    <td>
                                        <span class="registration-date"><?php echo date('M d, Y', strtotime($user['created_at'])); ?></span>
                                    </td>
                                    <td class="actions-cell">
                                        <form method="post" style="display: inline;">
                                            <input type="hidden" name="user_id" value="<?php echo $user['id']; ?>">
                                            <select name="status" class="status-select">
                                                <option value="active" <?php echo ($user['status'] == 'active') ? 'selected' : ''; ?>>Active</option>
                                                <option value="inactive" <?php echo ($user['status'] == 'inactive') ? 'selected' : ''; ?>>Inactive</option>
                                            </select>
                                            <button type="submit" name="update_status" class="btn-update-status">Update</button>
                                        </form>
                                    </td>
                                </tr>
                            <?php endwhile; ?>
                        <?php else: ?>
                            <tr>
                                <td colspan="4" class="no-users">No users found.</td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</body>
</html>
