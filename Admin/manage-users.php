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
            position: sticky;
            top: 0;
            height: 100vh;
            overflow-y: auto;
        }

        .admin-sidebar h2 {
            margin-bottom: 30px;
            color: var(--tan-crayola);
            font-size: 2.4rem;
        }

        .admin-sidebar ul {
            list-style: none;
            padding: 0;
        }

        .admin-sidebar li {
            margin-bottom: 8px;
        }

        .admin-sidebar a {
            color: var(--white);
            text-decoration: none;
            display: block;
            padding: 12px 15px;
            border-radius: 6px;
            transition: var(--transition-1);
            font-size: 1.5rem;
        }

        .admin-sidebar a:hover {
            background-color: rgba(210, 180, 140, 0.2);
            transform: translateX(5px);
        }

        .admin-sidebar a.active {
            background-color: var(--tan-crayola);
            font-weight: var(--fw-500);
        }

        .admin-content {
            flex: 1;
            padding: 40px;
            background-color: #f5f5f5;
            min-height: 100vh;
        }

        .page-header {
            margin-bottom: 30px;
        }

        .page-header h1 {
            color: var(--smokey-black);
            font-size: 3rem;
            font-weight: var(--fw-700);
        }

        .message {
            padding: 15px 20px;
            border-radius: 6px;
            margin-bottom: 25px;
            font-weight: var(--fw-500);
            font-size: 1.5rem;
        }

        .message.success {
            background-color: #28a745;
            color: var(--white);
        }

        .message.error {
            background-color: var(--red-orange-color-wheel);
            color: var(--white);
        }

        .users-table {
            width: 100%;
            background-color: var(--white);
            border-radius: 12px;
            box-shadow: 0 2px 12px rgba(0, 0, 0, 0.1);
            overflow: hidden;
            overflow-x: auto;
        }

        .users-table table {
            width: 100%;
            border-collapse: collapse;
            min-width: 700px;
        }

        .users-table th,
        .users-table td {
            padding: 15px 12px;
            text-align: left;
            border-bottom: 1px solid var(--light-gray);
            vertical-align: middle;
        }

        .users-table th {
            background-color: var(--smokey-black);
            font-weight: var(--fw-700);
            color: var(--white);
            text-transform: uppercase;
            font-size: 1.4rem;
            letter-spacing: 0.5px;
        }

        .users-table tbody tr:hover {
            background-color: var(--cultured);
            transition: var(--transition-1);
        }

        .user-info {
            display: flex;
            flex-direction: column;
            gap: 5px;
        }

        .user-name {
            font-weight: var(--fw-500);
            color: var(--smokey-black);
            font-size: 1.5rem;
        }

        .user-email {
            color: var(--granite-gray);
            font-size: 1.3rem;
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

        .status-select {
            padding: 6px 10px;
            border: 1px solid var(--light-gray);
            border-radius: 4px;
            font-size: 1.3rem;
            min-width: 95px;
            cursor: pointer;
            background-color: var(--white);
            font-weight: var(--fw-500);
            transition: var(--transition-1);
        }

        .status-select.status-active {
            background-color: #28a745;
            color: var(--white);
            border-color: #28a745;
        }

        .status-select.status-inactive {
            background-color: var(--granite-gray);
            color: var(--white);
            border-color: var(--granite-gray);
        }

        .status-select:focus {
            outline: 2px solid var(--tan-crayola);
            border-color: var(--tan-crayola);
        }

        .action-btn {
            padding: 8px 15px;
            margin-bottom: 10px;
            gap: 10px;
            border: none;
            border-radius: 4px;
            font-size: 1.4rem;
            font-weight: var(--fw-500);
            cursor: pointer;
            text-decoration: none;
            transition: var(--transition-1);
        }

        .btn-update-status {
            padding: 6px 12px;
            background-color: var(--middle-blue-green);
            color: var(--smokey-black);
            border: none;
            border-radius: 4px;
            font-size: 1.3rem;
            font-weight: var(--fw-500);
            cursor: pointer;
            transition: var(--transition-1);
            white-space: nowrap;
        }

        .btn-update-status:hover {
            background-color: var(--tan-crayola);
            color: var(--white);
            transform: translateY(-2px);
        }

        .btn-cancel {
            background-color: var(--light-gray);
            color: var(--smokey-black);
        }

        .btn-cancel:hover {
            background-color: var(--granite-gray);
            color: var(--white);
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
                <li><a href="Adminlogout.php">Logout</a></li>
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
                                        <form method="post" style="display: inline-flex; align-items: center; gap: 8px;">
                                            <input type="hidden" name="user_id" value="<?php echo $user['id']; ?>">
                                            <select name="status" class="status-select status-<?php echo strtolower($user['status']); ?>">
                                                <option value="active" <?php echo ($user['status'] == 'active') ? 'selected' : ''; ?>>Active</option>
                                                <option value="inactive" <?php echo ($user['status'] == 'inactive') ? 'selected' : ''; ?>>Inactive</option>
                                            </select>
                                            <button type="submit" name="update_status" class="btn-update-status">Update</button>
                                        </form>
                                    </td>
                                    <td>
                                        <span class="registration-date"><?php echo date('M d, Y', strtotime($user['created_at'])); ?></span>
                                    </td>
                                </tr>
                            <?php endwhile; ?>
                        <?php else: ?>
                            <tr>
                                <td colspan="3" class="no-users">No users found.</td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <script>
        // Handle status select color change
        document.addEventListener('DOMContentLoaded', function() {
            const statusSelects = document.querySelectorAll('.status-select');
            
            statusSelects.forEach(select => {
                select.addEventListener('change', function() {
                    // Remove existing status classes
                    this.classList.remove('status-active', 'status-inactive');
                    
                    // Add new class based on selected value
                    const selectedValue = this.value;
                    this.classList.add('status-' + selectedValue);
                });
            });
        });
    </script>
</body>
</html>
