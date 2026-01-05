<?php
require_once 'admin_config.php';
requireAdminLogin();

$admin = getAdminInfo();

// Handle category actions
if (isset($_POST['action'])) {
    if ($_POST['action'] == 'add') {
        $name = mysqli_real_escape_string($conn, trim($_POST['name']));
        $slug = strtolower(trim(preg_replace('/[^A-Za-z0-9-]+/', '-', $name)));
        $description = mysqli_real_escape_string($conn, trim($_POST['description']));
        
        $insert_query = "INSERT INTO categories (name, slug, description) VALUES ('$name', '$slug', '$description')";
        mysqli_query($conn, $insert_query);
        $success = 'Category added successfully!';
    } elseif ($_POST['action'] == 'delete' && isset($_POST['id'])) {
        $id = intval($_POST['id']);
        $delete_query = "DELETE FROM categories WHERE id = $id";
        mysqli_query($conn, $delete_query);
        $success = 'Category deleted successfully!';
    }
}

$categories_query = "SELECT c.*, COUNT(p.id) as product_count 
    FROM categories c 
    LEFT JOIN products p ON c.id = p.category_id 
    GROUP BY c.id 
    ORDER BY c.id ASC";
$categories_result = mysqli_query($conn, $categories_query);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Categories - Furnessence Admin</title>
    <link rel="stylesheet" href="../assests/css/admin.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>
<body class="admin-body">
    <?php include 'includes/sidebar.php'; ?>
    
    <div class="admin-main">
        <?php include 'includes/header.php'; ?>
        
        <div class="admin-content">
            <div class="page-header">
                <h1>Manage Categories</h1>
                <button onclick="showAddModal()" class="btn-primary">
                    <i class="fas fa-plus"></i> Add Category
                </button>
            </div>
            
            <?php if (isset($success)): ?>
                <div class="alert alert-success"><?php echo $success; ?></div>
            <?php endif; ?>
            
            <div class="table-card">
                <div class="table-responsive">
                    <table class="admin-table">
                        <thead>
                            <tr>
                                <th>ID</th>
                                <th>Name</th>
                                <th>Slug</th>
                                <th>Products</th>
                                <th>Created</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php while($cat = mysqli_fetch_assoc($categories_result)): ?>
                                <tr>
                                    <td><?php echo $cat['id']; ?></td>
                                    <td><?php echo htmlspecialchars($cat['name']); ?></td>
                                    <td><?php echo htmlspecialchars($cat['slug']); ?></td>
                                    <td><?php echo $cat['product_count']; ?></td>
                                    <td><?php echo date('M d, Y', strtotime($cat['created_at'])); ?></td>
                                    <td class="actions">
                                        <form method="POST" class="inline-form" data-confirm="Delete this category?">
                                            <input type="hidden" name="action" value="delete">
                                            <input type="hidden" name="id" value="<?php echo $cat['id']; ?>">
                                            <button type="submit" class="btn-action btn-delete">
                                                <i class="fas fa-trash"></i>
                                            </button>
                                        </form>
                                    </td>
                                </tr>
                            <?php endwhile; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Add Category Modal -->
    <div id="addModal" class="modal">
        <div class="modal-content">
            <span class="close" onclick="closeModal()">&times;</span>
            <h2>Add New Category</h2>
            <form method="POST">
                <input type="hidden" name="action" value="add">
                <div class="form-group">
                    <label>Name</label>
                    <input type="text" name="name" required>
                </div>
                <div class="form-group">
                    <label>Description</label>
                    <textarea name="description" rows="3"></textarea>
                </div>
                <button type="submit" class="btn-primary">Add Category</button>
            </form>
        </div>
    </div>
    
    <script src="js/admin.js"></script>
</body>
</html>
