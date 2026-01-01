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

// Handle category deletion
if (isset($_GET['delete']) && is_numeric($_GET['delete'])) {
    $category_id = $_GET['delete'];

    // Check if category has products
    $check_sql = "SELECT COUNT(*) as product_count FROM products WHERE category_id = ?";
    if ($stmt = mysqli_prepare($conn, $check_sql)) {
        mysqli_stmt_bind_param($stmt, "i", $category_id);
        mysqli_stmt_execute($stmt);
        mysqli_stmt_bind_result($stmt, $product_count);
        mysqli_stmt_fetch($stmt);
        mysqli_stmt_close($stmt);

        if ($product_count > 0) {
            $error_message = "Cannot delete category. It contains " . $product_count . " product(s).";
        } else {
            $delete_sql = "DELETE FROM categories WHERE id = ?";
            if ($stmt = mysqli_prepare($conn, $delete_sql)) {
                mysqli_stmt_bind_param($stmt, "i", $category_id);
                if (mysqli_stmt_execute($stmt)) {
                    $success_message = "Category deleted successfully.";
                } else {
                    $error_message = "Error deleting category.";
                }
                mysqli_stmt_close($stmt);
            }
        }
    }

    header("location: manage-categories.php");
    exit();
}

// Handle category addition
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['add_category'])) {
    $category_name = trim($_POST['category_name']);
    $category_description = trim($_POST['category_description']);

    if (!empty($category_name)) {
        $insert_sql = "INSERT INTO categories (name, description) VALUES (?, ?)";
        if ($stmt = mysqli_prepare($conn, $insert_sql)) {
            mysqli_stmt_bind_param($stmt, "ss", $category_name, $category_description);
            if (mysqli_stmt_execute($stmt)) {
                $success_message = "Category added successfully.";
            } else {
                $error_message = "Error adding category.";
            }
            mysqli_stmt_close($stmt);
        }
    } else {
        $error_message = "Category name is required.";
    }

    header("location: manage-categories.php");
    exit();
}

// Get all categories
$categories_sql = "SELECT c.id, c.name, c.description, c.created_at, COUNT(p.id) as product_count
                   FROM categories c
                   LEFT JOIN products p ON c.id = p.category_id
                   GROUP BY c.id
                   ORDER BY c.name";
$categories_result = mysqli_query($conn, $categories_sql);

mysqli_close($conn);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Categories - Furnessence Admin</title>
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
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .btn-add-category {
            padding: 10px 20px;
            background-color: var(--tan-crayola);
            color: var(--white);
            text-decoration: none;
            border-radius: 4px;
            font-weight: var(--fw-500);
            transition: var(--transition-1);
        }

        .btn-add-category:hover {
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

        .categories-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(300px, 1fr));
            gap: 20px;
        }

        .category-card {
            background-color: var(--white);
            padding: 20px;
            border-radius: 8px;
            box-shadow: var(--shadow);
            position: relative;
        }

        .category-card h3 {
            margin-bottom: 10px;
            color: var(--smokey-black);
        }

        .category-card p {
            color: var(--granite-gray);
            margin-bottom: 15px;
            font-size: 1.4rem;
        }

        .category-stats {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 15px;
        }

        .product-count {
            background-color: var(--cultured);
            padding: 5px 10px;
            border-radius: 4px;
            font-size: 1.2rem;
            color: var(--smokey-black);
        }

        .category-actions {
            display: flex;
            gap: 10px;
        }

        .action-btn {
            padding: 8px 15px;
            border: none;
            border-radius: 4px;
            font-size: 1.4rem;
            font-weight: var(--fw-500);
            cursor: pointer;
            text-decoration: none;
            display: inline-block;
            transition: var(--transition-1);
        }

        .btn-edit {
            background-color: var(--tan-crayola);
            color: var(--white);
        }

        .btn-edit:hover {
            background-color: var(--smokey-black);
        }

        .btn-delete {
            background-color: var(--red-orange-color-wheel);
            color: var(--tan-crayola);
        }

        .btn-delete:hover {
            background-color: var(--smokey-black);
        }

        .add-category-form {
            background-color: var(--white);
            padding: 25px;
            border-radius: 8px;
            box-shadow: var(--shadow);
            margin-bottom: 30px;
        }

        .form-row {
            display: flex;
            gap: 20px;
        }

        .form-group {
            flex: 1;
            margin-bottom: 20px;
        }

        .form-group label {
            display: block;
            margin-bottom: 5px;
            font-weight: var(--fw-500);
            color: var(--smokey-black);
        }

        .form-group input[type="text"],
        .form-group textarea {
            width: 100%;
            padding: 12px;
            border: 1px solid var(--light-gray);
            border-radius: 4px;
            font-size: 1.6rem;
            transition: var(--transition-1);
        }

        .form-group input[type="text"]:focus,
        .form-group textarea:focus {
            outline: none;
            border-color: var(--tan-crayola);
            box-shadow: 0 0 0 2px rgba(210, 180, 140, 0.2);
        }

        .form-group textarea {
            resize: vertical;
            min-height: 80px;
        }

        .btn-submit {
            padding: 12px 25px;
            background-color: var(--tan-crayola);
            color: var(--white);
            border: none;
            border-radius: 4px;
            font-size: 1.6rem;
            font-weight: var(--fw-500);
            cursor: pointer;
            transition: var(--transition-1);
        }

        .btn-submit:hover {
            background-color: var(--smokey-black);
        }

        .no-categories {
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
                <li><a href="manage-users.php">Manage Users</a></li>
                <li><a href="manage-categories.php" class="active">Categories</a></li>
                <li><a href="reports.php">Reports</a></li>
            </ul>
        </div>

        <div class="admin-content">
            <div class="page-header">
                <h1>Manage Categories</h1>
                <button class="btn-add-category" onclick="toggleAddForm()">Add New Category</button>
            </div>

            <?php if (isset($success_message)): ?>
                <div class="message success"><?php echo $success_message; ?></div>
            <?php endif; ?>

            <?php if (isset($error_message)): ?>
                <div class="message error"><?php echo $error_message; ?></div>
            <?php endif; ?>

            <div class="add-category-form" id="addCategoryForm" style="display: none;">
                <h2>Add New Category</h2>
                <form action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>" method="post">
                    <div class="form-row">
                        <div class="form-group">
                            <label>Category Name *</label>
                            <input type="text" name="category_name" required>
                        </div>
                        <div class="form-group">
                            <label>Description</label>
                            <textarea name="category_description"></textarea>
                        </div>
                    </div>
                    <button type="submit" name="add_category" class="btn-submit">Add Category</button>
                </form>
            </div>

            <div class="categories-grid">
                <?php if (mysqli_num_rows($categories_result) > 0): ?>
                    <?php while ($category = mysqli_fetch_assoc($categories_result)): ?>
                        <div class="category-card">
                            <h3><?php echo htmlspecialchars($category['name']); ?></h3>
                            <p><?php echo htmlspecialchars($category['description'] ?? 'No description'); ?></p>
                            <div class="category-stats">
                                <span class="product-count"><?php echo $category['product_count']; ?> products</span>
                                <small>Created: <?php echo date('M d, Y', strtotime($category['created_at'])); ?></small>
                            </div>
                            <div class="category-actions">
                                <a href="edit-category.php?id=<?php echo $category['id']; ?>" class="action-btn btn-edit">Edit</a>
                                <a href="manage-categories.php?delete=<?php echo $category['id']; ?>" class="action-btn btn-delete" onclick="return confirm('Are you sure you want to delete this category?')">Delete</a>
                            </div>
                        </div>
                    <?php endwhile; ?>
                <?php else: ?>
                    <div class="no-categories">
                        No categories found.
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <script>
        function toggleAddForm() {
            const form = document.getElementById('addCategoryForm');
            form.style.display = form.style.display === 'none' ? 'block' : 'none';
        }
    </script>
</body>
</html>
