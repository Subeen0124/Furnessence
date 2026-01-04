<?php
require_once 'admin_config.php';
requireAdminLogin();

$admin = getAdminInfo();
$error = '';
$success = '';

// Get product ID
$product_id = isset($_GET['id']) ? intval($_GET['id']) : 0;

if ($product_id === 0) {
    header('Location: manage_products.php');
    exit();
}

// Get product details
$product_query = "SELECT * FROM products WHERE id = $product_id LIMIT 1";
$product_result = mysqli_query($conn, $product_query);

if (mysqli_num_rows($product_result) === 0) {
    header('Location: manage_products.php');
    exit();
}

$product = mysqli_fetch_assoc($product_result);

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = mysqli_real_escape_string($conn, trim($_POST['name']));
    $category_id = intval($_POST['category_id']);
    $description = mysqli_real_escape_string($conn, trim($_POST['description']));
    $price = floatval($_POST['price']);
    $stock_quantity = intval($_POST['stock_quantity']);
    $low_stock_threshold = intval($_POST['low_stock_threshold']);
    $is_active = isset($_POST['is_active']) ? 1 : 0;
    
    // Generate slug
    $slug = strtolower(trim(preg_replace('/[^A-Za-z0-9-]+/', '-', $name)));
    
    // Handle image upload
    $image_path = $product['image'];
    if (isset($_FILES['image']) && $_FILES['image']['error'] === UPLOAD_ERR_OK) {
        $upload_dir = '../assests/images/products/';
        
        if (!file_exists($upload_dir)) {
            mkdir($upload_dir, 0777, true);
        }
        
        $file_extension = strtolower(pathinfo($_FILES['image']['name'], PATHINFO_EXTENSION));
        $allowed_extensions = ['jpg', 'jpeg', 'png', 'gif', 'webp'];
        
        if (in_array($file_extension, $allowed_extensions)) {
            $new_filename = uniqid() . '_' . time() . '.' . $file_extension;
            $upload_path = $upload_dir . $new_filename;
            
            if (move_uploaded_file($_FILES['image']['tmp_name'], $upload_path)) {
                // Delete old image if exists
                if (!empty($product['image']) && file_exists('../' . $product['image'])) {
                    unlink('../' . $product['image']);
                }
                $image_path = 'assests/images/products/' . $new_filename;
            }
        }
    }
    
    if (empty($name) || empty($price)) {
        $error = 'Please fill in all required fields';
    } else {
        $update_query = "UPDATE products SET 
            category_id = $category_id,
            name = '$name',
            slug = '$slug',
            description = '$description',
            price = $price,
            stock_quantity = $stock_quantity,
            low_stock_threshold = $low_stock_threshold,
            image = '$image_path',
            is_active = $is_active
            WHERE id = $product_id";
        
        if (mysqli_query($conn, $update_query)) {
            $success = 'Product updated successfully!';
            // Refresh product data
            $product_result = mysqli_query($conn, $product_query);
            $product = mysqli_fetch_assoc($product_result);
        } else {
            $error = 'Failed to update product: ' . mysqli_error($conn);
        }
    }
}

// Get categories
$categories_query = "SELECT * FROM categories ORDER BY name ASC";
$categories_result = mysqli_query($conn, $categories_query);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Product - Furnessence Admin</title>
    <link rel="stylesheet" href="../assests/css/admin.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>
<body class="admin-body">
    <?php include 'includes/sidebar.php'; ?>
    
    <div class="admin-main">
        <?php include 'includes/header.php'; ?>
        
        <div class="admin-content">
            <div class="page-header">
                <h1>Edit Product</h1>
                <a href="manage_products.php" class="btn-secondary">
                    <i class="fas fa-arrow-left"></i> Back to Products
                </a>
            </div>
            
            <?php if ($success): ?>
                <div class="alert alert-success">
                    <i class="fas fa-check-circle"></i>
                    <?php echo $success; ?>
                </div>
            <?php endif; ?>
            
            <?php if ($error): ?>
                <div class="alert alert-error">
                    <i class="fas fa-exclamation-circle"></i>
                    <?php echo $error; ?>
                </div>
            <?php endif; ?>
            
            <div class="form-card">
                <form method="POST" enctype="multipart/form-data" class="admin-form">
                    <div class="form-grid">
                        <div class="form-group">
                            <label for="name">Product Name <span class="required">*</span></label>
                            <input 
                                type="text" 
                                id="name" 
                                name="name" 
                                required 
                                value="<?php echo htmlspecialchars($product['name']); ?>"
                            >
                        </div>
                        
                        <div class="form-group">
                            <label for="category_id">Category</label>
                            <select id="category_id" name="category_id">
                                <option value="0">No Category</option>
                                <?php while($cat = mysqli_fetch_assoc($categories_result)): ?>
                                    <option value="<?php echo $cat['id']; ?>" 
                                        <?php echo ($product['category_id'] == $cat['id']) ? 'selected' : ''; ?>>
                                        <?php echo htmlspecialchars($cat['name']); ?>
                                    </option>
                                <?php endwhile; ?>
                            </select>
                        </div>
                        
                        <div class="form-group">
                            <label for="price">Price <span class="required">*</span></label>
                            <input 
                                type="number" 
                                id="price" 
                                name="price" 
                                required 
                                step="0.01" 
                                min="0"
                                value="<?php echo $product['price']; ?>"
                            >
                        </div>
                        
                        <div class="form-group">
                            <label for="stock_quantity">Stock Quantity <span class="required">*</span></label>
                            <input 
                                type="number" 
                                id="stock_quantity" 
                                name="stock_quantity" 
                                required 
                                min="0"
                                value="<?php echo $product['stock_quantity']; ?>"
                            >
                        </div>
                        
                        <div class="form-group">
                            <label for="low_stock_threshold">Low Stock Threshold</label>
                            <input 
                                type="number" 
                                id="low_stock_threshold" 
                                name="low_stock_threshold" 
                                min="0"
                                value="<?php echo $product['low_stock_threshold']; ?>"
                            >
                        </div>
                        
                        <div class="form-group">
                            <label for="image">Product Image</label>
                            <?php if (!empty($product['image'])): ?>
                                <div class="current-image">
                                    <img src="../<?php echo htmlspecialchars($product['image']); ?>" alt="Current">
                                    <p>Current Image</p>
                                </div>
                            <?php endif; ?>
                            <input 
                                type="file" 
                                id="image" 
                                name="image" 
                                accept="image/*"
                                onchange="previewImage(this)"
                            >
                            <small>Leave empty to keep current image</small>
                            <div id="image-preview" class="image-preview"></div>
                        </div>
                    </div>
                    
                    <div class="form-group">
                        <label for="description">Description</label>
                        <textarea 
                            id="description" 
                            name="description" 
                            rows="5"
                        ><?php echo htmlspecialchars($product['description']); ?></textarea>
                    </div>
                    
                    <div class="form-group">
                        <label class="checkbox-label">
                            <input 
                                type="checkbox" 
                                name="is_active" 
                                <?php echo $product['is_active'] ? 'checked' : ''; ?>
                            >
                            <span>Active (Show on website)</span>
                        </label>
                    </div>
                    
                    <div class="form-actions">
                        <button type="submit" class="btn-primary">
                            <i class="fas fa-save"></i> Update Product
                        </button>
                        <a href="manage_products.php" class="btn-secondary">
                            <i class="fas fa-times"></i> Cancel
                        </a>
                    </div>
                </form>
            </div>
        </div>
    </div>
    
    <script>
    function previewImage(input) {
        const preview = document.getElementById('image-preview');
        preview.innerHTML = '';
        
        if (input.files && input.files[0]) {
            const reader = new FileReader();
            reader.onload = function(e) {
                const img = document.createElement('img');
                img.src = e.target.result;
                preview.appendChild(img);
            }
            reader.readAsDataURL(input.files[0]);
        }
    }
    </script>
</body>
</html>
