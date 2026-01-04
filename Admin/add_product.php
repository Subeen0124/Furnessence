<?php
require_once 'admin_config.php';
requireAdminLogin();

$admin = getAdminInfo();
$error = '';
$success = '';

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
    $image_path = '';
    if (isset($_FILES['image']) && $_FILES['image']['error'] === UPLOAD_ERR_OK) {
        $upload_dir = '../assests/images/products/';
        
        // Create directory if it doesn't exist
        if (!file_exists($upload_dir)) {
            mkdir($upload_dir, 0777, true);
        }
        
        $file_extension = strtolower(pathinfo($_FILES['image']['name'], PATHINFO_EXTENSION));
        $allowed_extensions = ['jpg', 'jpeg', 'png', 'gif', 'webp'];
        
        if (in_array($file_extension, $allowed_extensions)) {
            $new_filename = uniqid() . '_' . time() . '.' . $file_extension;
            $upload_path = $upload_dir . $new_filename;
            
            if (move_uploaded_file($_FILES['image']['tmp_name'], $upload_path)) {
                $image_path = 'assests/images/products/' . $new_filename;
            } else {
                $error = 'Failed to upload image';
            }
        } else {
            $error = 'Invalid image format. Allowed: JPG, PNG, GIF, WEBP';
        }
    }
    
    if (empty($error)) {
        if (empty($name) || empty($price)) {
            $error = 'Please fill in all required fields';
        } else {
            $insert_query = "INSERT INTO products (category_id, name, slug, description, price, stock_quantity, low_stock_threshold, image, is_active) 
                VALUES ($category_id, '$name', '$slug', '$description', $price, $stock_quantity, $low_stock_threshold, '$image_path', $is_active)";
            
            if (mysqli_query($conn, $insert_query)) {
                $success = 'Product added successfully!';
                // Clear form
                $_POST = [];
            } else {
                $error = 'Failed to add product: ' . mysqli_error($conn);
            }
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
    <title>Add Product - Furnessence Admin</title>
    <link rel="stylesheet" href="../assests/css/admin.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>
<body class="admin-body">
    <?php include 'includes/sidebar.php'; ?>
    
    <div class="admin-main">
        <?php include 'includes/header.php'; ?>
        
        <div class="admin-content">
            <div class="page-header">
                <h1>Add New Product</h1>
                <a href="manage_products.php" class="btn-secondary">
                    <i class="fas fa-arrow-left"></i> Back to Products
                </a>
            </div>
            
            <?php if ($success): ?>
                <div class="alert alert-success">
                    <i class="fas fa-check-circle"></i>
                    <?php echo $success; ?>
                    <a href="manage_products.php">View Products</a>
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
                                placeholder="Enter product name"
                                value="<?php echo isset($_POST['name']) ? htmlspecialchars($_POST['name']) : ''; ?>"
                            >
                        </div>
                        
                        <div class="form-group">
                            <label for="category_id">Category</label>
                            <select id="category_id" name="category_id">
                                <option value="0">No Category</option>
                                <?php while($cat = mysqli_fetch_assoc($categories_result)): ?>
                                    <option value="<?php echo $cat['id']; ?>" 
                                        <?php echo (isset($_POST['category_id']) && $_POST['category_id'] == $cat['id']) ? 'selected' : ''; ?>>
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
                                placeholder="0.00"
                                value="<?php echo isset($_POST['price']) ? htmlspecialchars($_POST['price']) : ''; ?>"
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
                                placeholder="0"
                                value="<?php echo isset($_POST['stock_quantity']) ? htmlspecialchars($_POST['stock_quantity']) : '0'; ?>"
                            >
                        </div>
                        
                        <div class="form-group">
                            <label for="low_stock_threshold">Low Stock Threshold</label>
                            <input 
                                type="number" 
                                id="low_stock_threshold" 
                                name="low_stock_threshold" 
                                min="0"
                                placeholder="10"
                                value="<?php echo isset($_POST['low_stock_threshold']) ? htmlspecialchars($_POST['low_stock_threshold']) : '10'; ?>"
                            >
                            <small>Alert when stock falls below this number</small>
                        </div>
                        
                        <div class="form-group">
                            <label for="image">Product Image</label>
                            <input 
                                type="file" 
                                id="image" 
                                name="image" 
                                accept="image/*"
                                onchange="previewImage(this)"
                            >
                            <small>Allowed formats: JPG, PNG, GIF, WEBP</small>
                            <div id="image-preview" class="image-preview"></div>
                        </div>
                    </div>
                    
                    <div class="form-group">
                        <label for="description">Description</label>
                        <textarea 
                            id="description" 
                            name="description" 
                            rows="5"
                            placeholder="Enter product description"
                        ><?php echo isset($_POST['description']) ? htmlspecialchars($_POST['description']) : ''; ?></textarea>
                    </div>
                    
                    <div class="form-group">
                        <label class="checkbox-label">
                            <input 
                                type="checkbox" 
                                name="is_active" 
                                <?php echo (!isset($_POST['is_active']) || isset($_POST['is_active'])) ? 'checked' : ''; ?>
                            >
                            <span>Active (Show on website)</span>
                        </label>
                    </div>
                    
                    <div class="form-actions">
                        <button type="submit" class="btn-primary">
                            <i class="fas fa-save"></i> Add Product
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
