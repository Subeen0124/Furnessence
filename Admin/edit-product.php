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

// Get product ID from URL
$product_id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

if ($product_id <= 0) {
    header("location: manage-products.php");
    exit();
}

// Get product details
$product_sql = "SELECT * FROM products WHERE id = ?";
if ($stmt = mysqli_prepare($conn, $product_sql)) {
    mysqli_stmt_bind_param($stmt, "i", $product_id);
    mysqli_stmt_execute($stmt);
    $product_result = mysqli_stmt_get_result($stmt);
    $product = mysqli_fetch_assoc($product_result);
    mysqli_stmt_close($stmt);

    if (!$product) {
        header("location: manage-products.php");
        exit();
    }
}

// Get categories for dropdown
$categories_sql = "SELECT id, name FROM categories ORDER BY name";
$categories_result = mysqli_query($conn, $categories_sql);

// Define variables and initialize with product data
$name = $product['name'];
$description = $product['description'];
$price = $product['price'];
$stock_quantity = $product['stock_quantity'];
$category_id = $product['category_id'];
$image = $product['image'];
$status = $product['status'];

$name_err = $description_err = $price_err = $stock_err = $category_err = $image_err = "";

// Processing form data when form is submitted
if ($_SERVER["REQUEST_METHOD"] == "POST") {

    // Validate name
    if (empty(trim($_POST["name"]))) {
        $name_err = "Please enter product name.";
    } else {
        $name = trim($_POST["name"]);
    }

    // Validate description
    if (empty(trim($_POST["description"]))) {
        $description_err = "Please enter product description.";
    } else {
        $description = trim($_POST["description"]);
    }

    // Validate price
    if (empty(trim($_POST["price"]))) {
        $price_err = "Please enter product price.";
    } elseif (!is_numeric($_POST["price"]) || $_POST["price"] <= 0) {
        $price_err = "Please enter a valid price.";
    } else {
        $price = trim($_POST["price"]);
    }

    // Validate stock
    if (empty(trim($_POST["stock_quantity"]))) {
        $stock_err = "Please enter stock quantity.";
    } elseif (!is_numeric($_POST["stock_quantity"]) || $_POST["stock_quantity"] < 0) {
        $stock_err = "Please enter a valid stock quantity.";
    } else {
        $stock_quantity = trim($_POST["stock_quantity"]);
    }

    // Validate category
    if (empty(trim($_POST["category_id"]))) {
        $category_err = "Please select a category.";
    } else {
        $category_id = trim($_POST["category_id"]);
    }

    // Handle image upload (optional for edit)
    if (isset($_FILES["image"]) && $_FILES["image"]["error"] == 0) {
        $allowed = array("jpg" => "image/jpg", "jpeg" => "image/jpeg", "gif" => "image/gif", "png" => "image/png");
        $filename = $_FILES["image"]["name"];
        $filetype = $_FILES["image"]["type"];
        $filesize = $_FILES["image"]["size"];

        // Verify file extension
        $ext = pathinfo($filename, PATHINFO_EXTENSION);
        if (!array_key_exists($ext, $allowed)) {
            $image_err = "Please select a valid file format.";
        }

        // Verify file size - 5MB maximum
        $maxsize = 5 * 1024 * 1024;
        if ($filesize > $maxsize) {
            $image_err = "File size is larger than the allowed limit.";
        }

        // Verify MYME type of the file
        if (in_array($filetype, $allowed)) {
            // Check whether file exists before uploading it
            $upload_dir = "../assets/images/products/";
            if (!is_dir($upload_dir)) {
                mkdir($upload_dir, 0755, true);
            }

            $new_filename = uniqid() . "." . $ext;
            $upload_path = $upload_dir . $new_filename;

            if (move_uploaded_file($_FILES["image"]["tmp_name"], $upload_path)) {
                // Delete old image if exists
                if (!empty($product['image']) && file_exists("../" . $product['image'])) {
                    unlink("../" . $product['image']);
                }
                $image = "assets/images/products/" . $new_filename;
            } else {
                $image_err = "Your file was not uploaded.";
            }
        } else {
            $image_err = "Please select a valid file format.";
        }
    } else {
        // Keep existing image if no new image uploaded
        $image = $product['image'];
    }

    // Get status
    $status = isset($_POST['status']) ? trim($_POST['status']) : $product['status'];

    // Check input errors before updating in database
    if (empty($name_err) && empty($description_err) && empty($price_err) && empty($stock_err) && empty($category_err) && empty($image_err)) {

        // Prepare an update statement
        $sql = "UPDATE products SET name = ?, description = ?, price = ?, stock_quantity = ?, category_id = ?, image = ?, status = ? WHERE id = ?";

        if ($stmt = mysqli_prepare($conn, $sql)) {
            // Bind variables to the prepared statement as parameters
            mysqli_stmt_bind_param($stmt, "ssdissii", $param_name, $param_description, $param_price, $param_stock, $param_category, $param_image, $param_status, $param_id);

            // Set parameters
            $param_name = $name;
            $param_description = $description;
            $param_price = $price;
            $param_stock = $stock_quantity;
            $param_category = $category_id;
            $param_image = $image;
            $param_status = $status;
            $param_id = $product_id;

            // Attempt to execute the prepared statement
            if (mysqli_stmt_execute($stmt)) {
                // Records updated successfully. Redirect to landing page
                header("location: manage-products.php");
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
    <title>Edit Product - Furnessence Admin</title>
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

        .form-container {
            background-color: var(--white);
            padding: 30px;
            border-radius: 8px;
            box-shadow: var(--shadow);
            max-width: 800px;
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
        .form-group input[type="number"],
        .form-group textarea,
        .form-group select {
            width: 100%;
            padding: 12px;
            border: 1px solid var(--light-gray);
            border-radius: 4px;
            font-size: 1.6rem;
            transition: var(--transition-1);
        }

        .form-group input[type="text"]:focus,
        .form-group input[type="number"]:focus,
        .form-group textarea:focus,
        .form-group select:focus {
            outline: none;
            border-color: var(--tan-crayola);
            box-shadow: 0 0 0 2px rgba(210, 180, 140, 0.2);
        }

        .form-group textarea {
            resize: vertical;
            min-height: 100px;
        }

        .current-image {
            max-width: 200px;
            margin-top: 10px;
            border-radius: 4px;
        }

        .error-message {
            color: var(--red-orange-color-wheel);
            font-size: 1.4rem;
            margin-top: 5px;
        }

        .status-options {
            display: flex;
            gap: 20px;
            margin-top: 10px;
        }

        .status-option {
            display: flex;
            align-items: center;
        }

        .status-option input[type="radio"] {
            margin-right: 5px;
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
                <h1>Edit Product</h1>
            </div>

            <div class="form-container">
                <form action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]) . "?id=" . $product_id; ?>" method="post" enctype="multipart/form-data">
                    <div class="form-group">
                        <label>Product Name *</label>
                        <input type="text" name="name" class="form-control <?php echo (!empty($name_err)) ? 'is-invalid' : ''; ?>" value="<?php echo $name; ?>">
                        <span class="error-message"><?php echo $name_err; ?></span>
                    </div>

                    <div class="form-group">
                        <label>Description *</label>
                        <textarea name="description" class="form-control <?php echo (!empty($description_err)) ? 'is-invalid' : ''; ?>"><?php echo $description; ?></textarea>
                        <span class="error-message"><?php echo $description_err; ?></span>
                    </div>

                    <div class="form-group">
                        <label>Price ($) *</label>
                        <input type="number" name="price" step="0.01" min="0" class="form-control <?php echo (!empty($price_err)) ? 'is-invalid' : ''; ?>" value="<?php echo $price; ?>">
                        <span class="error-message"><?php echo $price_err; ?></span>
                    </div>

                    <div class="form-group">
                        <label>Stock Quantity *</label>
                        <input type="number" name="stock_quantity" min="0" class="form-control <?php echo (!empty($stock_err)) ? 'is-invalid' : ''; ?>" value="<?php echo $stock_quantity; ?>">
                        <span class="error-message"><?php echo $stock_err; ?></span>
                    </div>

                    <div class="form-group">
                        <label>Category *</label>
                        <select name="category_id" class="form-control <?php echo (!empty($category_err)) ? 'is-invalid' : ''; ?>">
                            <option value="">Select Category</option>
                            <?php while ($category = mysqli_fetch_assoc($categories_result)): ?>
                                <option value="<?php echo $category['id']; ?>" <?php echo ($category_id == $category['id']) ? 'selected' : ''; ?>>
                                    <?php echo htmlspecialchars($category['name']); ?>
                                </option>
                            <?php endwhile; ?>
                        </select>
                        <span class="error-message"><?php echo $category_err; ?></span>
                    </div>

                    <div class="form-group">
                        <label>Current Image</label>
                        <?php if (!empty($image)): ?>
                            <br>
                            <img src="../<?php echo $image; ?>" alt="Current product image" class="current-image">
                        <?php else: ?>
                            <p>No image uploaded</p>
                        <?php endif; ?>
                    </div>

                    <div class="form-group">
                        <label>Change Image (optional)</label>
                        <input type="file" name="image" accept="image/*" class="form-control">
                        <span class="error-message"><?php echo $image_err; ?></span>
                    </div>

                    <div class="form-group">
                        <label>Status</label>
                        <div class="status-options">
                            <div class="status-option">
                                <input type="radio" name="status" value="active" <?php echo ($status == 'active') ? 'checked' : ''; ?>>
                                <label>Active</label>
                            </div>
                            <div class="status-option">
                                <input type="radio" name="status" value="inactive" <?php echo ($status == 'inactive') ? 'checked' : ''; ?>>
                                <label>Inactive</label>
                            </div>
                        </div>
                    </div>

                    <div class="form-actions">
                        <button type="submit" class="btn-submit">Update Product</button>
                        <a href="manage-products.php" class="btn-cancel">Cancel</a>
                    </div>
                </form>
            </div>
        </div>
    </div>
</body>
</html>
