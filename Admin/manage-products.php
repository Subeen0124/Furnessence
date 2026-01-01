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

// Handle product deletion
if (isset($_GET['delete']) && is_numeric($_GET['delete'])) {
    $product_id = $_GET['delete'];

    // Get product image path before deletion
    $image_sql = "SELECT image FROM products WHERE id = ?";
    if ($stmt = mysqli_prepare($conn, $image_sql)) {
        mysqli_stmt_bind_param($stmt, "i", $product_id);
        mysqli_stmt_execute($stmt);
        mysqli_stmt_bind_result($stmt, $image_path);
        mysqli_stmt_fetch($stmt);
        mysqli_stmt_close($stmt);

        // Delete product from database
        $delete_sql = "DELETE FROM products WHERE id = ?";
        if ($stmt = mysqli_prepare($conn, $delete_sql)) {
            mysqli_stmt_bind_param($stmt, "i", $product_id);
            if (mysqli_stmt_execute($stmt)) {
                $success_message = "Product deleted successfully.";

                // Delete image file if exists
                if (!empty($image_path) && file_exists("../" . $image_path)) {
                    unlink("../" . $image_path);
                }
            } else {
                $error_message = "Error deleting product.";
            }
            mysqli_stmt_close($stmt);
        }
    }

    header("location: manage-products.php");
    exit();
}

// Handle product status update
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['update_status'])) {
    $product_id = (int)$_POST['product_id'];
    $new_status = trim($_POST['status']);

    $valid_statuses = ['active', 'inactive'];
    if (in_array($new_status, $valid_statuses)) {
        $update_sql = "UPDATE products SET status = ? WHERE id = ?";
        if ($stmt = mysqli_prepare($conn, $update_sql)) {
            mysqli_stmt_bind_param($stmt, "si", $new_status, $product_id);
            if (mysqli_stmt_execute($stmt)) {
                $success_message = "Product status updated successfully.";
            } else {
                $error_message = "Error updating product status.";
            }
            mysqli_stmt_close($stmt);
        }
    } else {
        $error_message = "Invalid status selected.";
    }

    header("location: manage-products.php");
    exit();
}

// Get all products with category information
$products_sql = "SELECT p.id, p.name, p.price, p.stock_quantity, p.status, p.image,
                        c.name as category_name
                 FROM products p
                 LEFT JOIN categories c ON p.category_id = c.id
                 ORDER BY p.id DESC";
$products_result = mysqli_query($conn, $products_sql);

mysqli_close($conn);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Products - Furnessence Admin</title>
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
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 30px;
        }

        .btn-add-product {
            padding: 10px 20px;
            background-color: var(--tan-crayola);
            color: var(--white);
            text-decoration: none;
            border-radius: 4px;
            font-weight: var(--fw-500);
            transition: var(--transition-1);
        }

        .btn-add-product:hover {
            background-color: var(--smokey-black);
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

        .products-table {
            width: 100%;
            background-color: var(--white);
            border-radius: 8px;
            box-shadow: var(--shadow);
            overflow: hidden;
        }

        .products-table table {
            width: 100%;
            border-collapse: collapse;
        }

        .products-table th,
        .products-table td {
            padding: 15px;
            text-align: left;
            border-bottom: 1px solid var(--light-gray);
        }

        .products-table th {
            background-color: var(--cultured);
            font-weight: var(--fw-500);
            color: var(--smokey-black);
        }

        .products-table tbody tr:hover {
            background-color: var(--cultured);
        }

        .product-image {
            width: 60px;
            height: 60px;
            object-fit: cover;
            border-radius: 4px;
        }

        .product-name {
            font-weight: var(--fw-500);
            color: var(--smokey-black);
            margin-bottom: 5px;
        }

        .product-category {
            color: var(--granite-gray);
            font-size: 1.4rem;
        }

        .product-price {
            font-weight: var(--fw-500);
            color: var(--tan-crayola);
            font-size: 1.6rem;
        }

        .stock-status {
            padding: 5px 10px;
            border-radius: 4px;
            font-size: 1.2rem;
            font-weight: var(--fw-500);
        }

        .stock-in {
            background-color: var(--green);
            color: var(--white);
        }

        .stock-low {
            background-color: var(--yellow);
            color: var(--smokey-black);
        }

        .stock-out {
            background-color: var(--red-orange-color-wheel);
            color: var(--white);
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
            color: var(--white);
        }

        .status-inactive {
            background-color: var(--red-orange-color-wheel);
            color: var(--white);
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

        .btn-edit {
            padding: 8px 15px;
            background-color: var(--tan-crayola);
            color: var(--white);
            text-decoration: none;
            border-radius: 4px;
            font-size: 1.4rem;
            font-weight: var(--fw-500);
            margin-right: 10px;
            transition: var(--transition-1);
        }

        .btn-edit:hover {
            background-color: var(--smokey-black);
        }

        .btn-delete {
            padding: 8px 15px;
            background-color: var(--red-orange-color-wheel);
            color: var(--white);
            text-decoration: none;
            border-radius: 4px;
            font-size: 1.4rem;
            font-weight: var(--fw-500);
            transition: var(--transition-1);
        }

        .btn-delete:hover {
            background-color: var(--smokey-black);
        }

        .no-products {
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
                <li><a href="manage-products.php" class="active">Manage Products</a></li>
                <li><a href="manage-orders.php">Manage Orders</a></li>
                <li><a href="manage-users.php">Manage Users</a></li>
                <li><a href="manage-categories.php">Categories</a></li>
                <li><a href="reports.php">Reports</a></li>
            </ul>
        </div>

        <div class="admin-content">
            <div class="page-header">
                <h1>Manage Products</h1>
                <a href="add-product.php" class="btn-add-product">Add New Product</a>
            </div>

            <?php if (isset($success_message)): ?>
                <div class="message success"><?php echo $success_message; ?></div>
            <?php endif; ?>

            <?php if (isset($error_message)): ?>
                <div class="message error"><?php echo $error_message; ?></div>
            <?php endif; ?>

            <div class="products-table">
                <table>
                    <thead>
                        <tr>
                            <th>Image</th>
                            <th>Product</th>
                            <th>Price</th>
                            <th>Stock</th>
                            <th>Status</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (mysqli_num_rows($products_result) > 0): ?>
                            <?php while ($product = mysqli_fetch_assoc($products_result)): ?>
                                <tr>
                                    <td>
                                        <?php if (!empty($product['image'])): ?>
                                            <img src="../<?php echo $product['image']; ?>" alt="<?php echo htmlspecialchars($product['name']); ?>" class="product-image">
                                        <?php else: ?>
                                            <div class="product-image" style="background-color: var(--light-gray); display: flex; align-items: center; justify-content: center; color: var(--granite-gray);">No Image</div>
                                        <?php endif; ?>
                                    </td>
                                    <td>
                                        <div class="product-name"><?php echo htmlspecialchars($product['name']); ?></div>
                                        <div class="product-category"><?php echo htmlspecialchars($product['category_name'] ?? 'No Category'); ?></div>
                                    </td>
                                    <td>
                                        <span class="product-price">$<?php echo number_format($product['price'], 2); ?></span>
                                    </td>
                                    <td>
                                        <span class="stock-status <?php
                                            if ($product['stock_quantity'] == 0) echo 'stock-out';
                                            elseif ($product['stock_quantity'] <= 5) echo 'stock-low';
                                            else echo 'stock-in';
                                        ?>">
                                            <?php echo $product['stock_quantity']; ?> in stock
                                        </span>
                                    </td>
                                    <td>
                                        <span class="status-badge status-<?php echo strtolower($product['status']); ?>">
                                            <?php echo ucfirst($product['status']); ?>
                                        </span>
                                    </td>
                                    <td class="actions-cell">
                                        <form method="post" style="display: inline;">
                                            <input type="hidden" name="product_id" value="<?php echo $product['id']; ?>">
                                            <select name="status" class="status-select">
                                                <option value="active" <?php echo ($product['status'] == 'active') ? 'selected' : ''; ?>>Active</option>
                                                <option value="inactive" <?php echo ($product['status'] == 'inactive') ? 'selected' : ''; ?>>Inactive</option>
                                            </select>
                                            <button type="submit" name="update_status" class="btn-update-status">Update</button>
                                        </form>
                                        <a href="edit-product.php?id=<?php echo $product['id']; ?>" class="btn-edit">Edit</a>
                                        <a href="manage-products.php?delete=<?php echo $product['id']; ?>" class="btn-delete" onclick="return confirm('Are you sure you want to delete this product?')">Delete</a>
                                    </td>
                                </tr>
                            <?php endwhile; ?>
                        <?php else: ?>
                            <tr>
                                <td colspan="6" class="no-products">No products found.</td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</body>
</html>
