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

// Get category ID from URL
$category_id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

if ($category_id <= 0) {
    header("location: manage-categories.php");
    exit();
}

// Get category details
$category_sql = "SELECT * FROM categories WHERE id = ?";
if ($stmt = mysqli_prepare($conn, $category_sql)) {
    mysqli_stmt_bind_param($stmt, "i", $category_id);
    mysqli_stmt_execute($stmt);
    $category_result = mysqli_stmt_get_result($stmt);
    $category = mysqli_fetch_assoc($category_result);
    mysqli_stmt_close($stmt);

    if (!$category) {
        header("location: manage-categories.php");
        exit();
    }
}

// Define variables and initialize with category data
$name = $category['name'];
$description = $category['description'];

$name_err = $description_err = "";

// Processing form data when form is submitted
if ($_SERVER["REQUEST_METHOD"] == "POST") {

    // Validate name
    if (empty(trim($_POST["name"]))) {
        $name_err = "Please enter category name.";
    } else {
        $name = trim($_POST["name"]);
    }

    // Validate description
    $description = trim($_POST["description"]);

    // Check input errors before updating in database
    if (empty($name_err)) {

        // Prepare an update statement
        $sql = "UPDATE categories SET name = ?, description = ? WHERE id = ?";

        if ($stmt = mysqli_prepare($conn, $sql)) {
            // Bind variables to the prepared statement as parameters
            mysqli_stmt_bind_param($stmt, "ssi", $param_name, $param_description, $param_id);

            // Set parameters
            $param_name = $name;
            $param_description = $description;
            $param_id = $category_id;

            // Attempt to execute the prepared statement
            if (mysqli_stmt_execute($stmt)) {
                // Records updated successfully. Redirect to landing page
                header("location: manage-categories.php");
                exit();
            } else {
                echo "Oops! Something went wrong. Please try again later.";
            }

            // Close statement
            mysqli_stmt_close($stmt);
        }
    }

    // Close connection
    mysqli_close($conn);
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Category - Furnessence Admin</title>
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

        .form-container {
            background-color: var(--white);
            padding: 30px;
            border-radius: 8px;
            box-shadow: var(--shadow);
            max-width: 600px;
        }

        .form-group {
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
            min-height: 100px;
        }

        .error-message {
            color: var(--red-orange-color-wheel);
            font-size: 1.4rem;
            margin-top: 5px;
        }

        .btn-submit {
            padding: 15px 30px;
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

        .btn-cancel {
            padding: 15px 30px;
            background-color: var(--granite-gray);
            color: var(--white);
            text-decoration: none;
            border-radius: 4px;
            font-size: 1.6rem;
            font-weight: var(--fw-500);
            margin-left: 10px;
            transition: var(--transition-1);
        }

        .btn-cancel:hover {
            background-color: var(--smokey-black);
        }

        .form-actions {
            margin-top: 30px;
            text-align: right;
        }

        .category-info {
            background-color: var(--cultured);
            padding: 15px;
            border-radius: 4px;
            margin-bottom: 20px;
        }

        .category-info p {
            margin: 5px 0;
            color: var(--granite-gray);
        }

        .category-info strong {
            color: var(--smokey-black);
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
                <h1>Edit Category</h1>
            </div>

            <div class="category-info">
                <p><strong>Category ID:</strong> <?php echo $category['id']; ?></p>
                <p><strong>Created:</strong> <?php echo date('M d, Y H:i', strtotime($category['created_at'])); ?></p>
            </div>

            <div class="form-container">
                <form action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]) . "?id=" . $category_id; ?>" method="post">
                    <div class="form-group">
                        <label>Category Name *</label>
                        <input type="text" name="name" class="form-control <?php echo (!empty($name_err)) ? 'is-invalid' : ''; ?>" value="<?php echo $name; ?>">
                        <span class="error-message"><?php echo $name_err; ?></span>
                    </div>

                    <div class="form-group">
                        <label>Description</label>
                        <textarea name="description" class="form-control"><?php echo $description; ?></textarea>
                        <span class="error-message"><?php echo $description_err; ?></span>
                    </div>

                    <div class="form-actions">
                        <button type="submit" class="btn-submit">Update Category</button>
                        <a href="manage-categories.php" class="btn-cancel">Cancel</a>
                    </div>
                </form>
            </div>
        </div>
    </div>
</body>
</html>
