<?php
require_once 'admin_config.php';
requireAdminLogin();

$admin = getAdminInfo();

// Handle delete action
if (isset($_GET['delete']) && isset($_GET['id'])) {
    $id = intval($_GET['id']);
    $delete_query = "DELETE FROM products WHERE id = $id";
    if (mysqli_query($conn, $delete_query)) {
        $success = 'Product deleted successfully!';
    } else {
        $error = 'Failed to delete product';
    }
}

// Get filter and search parameters
$search = isset($_GET['search']) ? mysqli_real_escape_string($conn, $_GET['search']) : '';
$category_filter = isset($_GET['category']) ? intval($_GET['category']) : 0;
$stock_filter = isset($_GET['stock']) ? $_GET['stock'] : 'all';

// Build query
$where = [];
if (!empty($search)) {
    $where[] = "(p.name LIKE '%$search%' OR p.description LIKE '%$search%')";
}
if ($category_filter > 0) {
    $where[] = "p.category_id = $category_filter";
}
if ($stock_filter == 'low') {
    $where[] = "stock_quantity <= low_stock_threshold AND stock_quantity > 0";
} elseif ($stock_filter == 'out') {
    $where[] = "stock_quantity = 0";
}

$where_clause = !empty($where) ? 'WHERE ' . implode(' AND ', $where) : '';

$products_query = "SELECT p.*, c.name as category_name 
    FROM products p 
    LEFT JOIN categories c ON p.category_id = c.id 
    $where_clause 
    ORDER BY p.id ASC";
$products_result = mysqli_query($conn, $products_query);

// Get categories for filter
$categories_query = "SELECT * FROM categories ORDER BY name ASC";
$categories_result = mysqli_query($conn, $categories_query);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Products - Furnessence Admin</title>
    <link rel="stylesheet" href="../assests/css/admin.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>
<body class="admin-body">
    <?php include 'includes/sidebar.php'; ?>
    
    <div class="admin-main">
        <?php include 'includes/header.php'; ?>
        
        <div class="admin-content">
            <div class="page-header">
                <h1>Manage Products</h1>
                <a href="add_product.php" class="btn-primary">
                    <i class="fas fa-plus"></i> Add New Product
                </a>
            </div>
            
            <?php if (isset($success)): ?>
                <div class="alert alert-success">
                    <i class="fas fa-check-circle"></i>
                    <?php echo $success; ?>
                </div>
            <?php endif; ?>
            
            <?php if (isset($error)): ?>
                <div class="alert alert-error">
                    <i class="fas fa-exclamation-circle"></i>
                    <?php echo $error; ?>
                </div>
            <?php endif; ?>
            
            <!-- Filters -->
            <div class="filters-card">
                <form method="GET" class="filters-form">
                    <div class="filter-group">
                        <input 
                            type="text" 
                            name="search" 
                            placeholder="Search products..." 
                            value="<?php echo htmlspecialchars($search); ?>"
                        >
                    </div>
                    
                    <div class="filter-group">
                        <select name="category">
                            <option value="">All Categories</option>
                            <?php 
                            mysqli_data_seek($categories_result, 0);
                            while($cat = mysqli_fetch_assoc($categories_result)): 
                            ?>
                                <option value="<?php echo $cat['id']; ?>" <?php echo $category_filter == $cat['id'] ? 'selected' : ''; ?>>
                                    <?php echo htmlspecialchars($cat['name']); ?>
                                </option>
                            <?php endwhile; ?>
                        </select>
                    </div>
                    
                    <div class="filter-group">
                        <select name="stock">
                            <option value="all" <?php echo $stock_filter == 'all' ? 'selected' : ''; ?>>All Stock</option>
                            <option value="low" <?php echo $stock_filter == 'low' ? 'selected' : ''; ?>>Low Stock</option>
                            <option value="out" <?php echo $stock_filter == 'out' ? 'selected' : ''; ?>>Out of Stock</option>
                        </select>
                    </div>
                    
                    <button type="submit" class="btn-filter">
                        <i class="fas fa-filter"></i> Filter
                    </button>
                    
                    <a href="manage_products.php" class="btn-reset">
                        <i class="fas fa-redo"></i> Reset
                    </a>
                </form>
            </div>
            
            <!-- Products Table -->
            <div class="table-card">
                <div class="table-responsive">
                    <table class="admin-table">
                        <thead>
                            <tr>
                                <th>ID</th>
                                <th>Image</th>
                                <th>Name</th>
                                <th>Category</th>
                                <th>Price</th>
                                <th>Stock</th>
                                <th>Status</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (mysqli_num_rows($products_result) > 0): ?>
                                <?php while($product = mysqli_fetch_assoc($products_result)): ?>
                                    <tr>
                                        <td><?php echo $product['id']; ?></td>
                                        <td>
                                            <?php if (!empty($product['image'])): ?>
                                                <img src="../<?php echo htmlspecialchars($product['image']); ?>" alt="Product" class="product-thumb">
                                            <?php else: ?>
                                                <div class="product-thumb-placeholder">
                                                    <i class="fas fa-image"></i>
                                                </div>
                                            <?php endif; ?>
                                        </td>
                                        <td><?php echo htmlspecialchars($product['name']); ?></td>
                                        <td><?php echo htmlspecialchars($product['category_name'] ?? 'Uncategorized'); ?></td>
                                        <td>$<?php echo number_format($product['price'], 2); ?></td>
                                        <td><?php echo $product['stock_quantity']; ?></td>
                                        <td>
                                            <?php if ($product['stock_quantity'] == 0): ?>
                                                <span class="stock-badge out-of-stock">Out of Stock</span>
                                            <?php elseif ($product['stock_quantity'] <= $product['low_stock_threshold']): ?>
                                                <span class="stock-badge low-stock">Low Stock</span>
                                            <?php else: ?>
                                                <span class="stock-badge in-stock">In Stock</span>
                                            <?php endif; ?>
                                        </td>
                                        <td class="actions">
                                            <a href="edit_product.php?id=<?php echo $product['id']; ?>" class="btn-action btn-edit" title="Edit">
                                                <i class="fas fa-edit"></i>
                                            </a>
                                            <a href="?delete=1&id=<?php echo $product['id']; ?>" 
                                               class="btn-action btn-delete" 
                                               title="Delete"
                                               onclick="return confirm('Are you sure you want to delete this product?')">
                                                <i class="fas fa-trash"></i>
                                            </a>
                                        </td>
                                    </tr>
                                <?php endwhile; ?>
                            <?php else: ?>
                                <tr>
                                    <td colspan="8" class="no-data">No products found</td>
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
