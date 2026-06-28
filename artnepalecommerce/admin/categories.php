<?php
/**
 * Admin Categories Management
 * ARTNEPAL E-commerce Website
 * 
 * This page handles category management for admin
 */

// Start session
session_start();

// Include database configuration
require_once '../config/database.php';

// Check if admin is logged in
if (!is_admin_logged_in()) {
    $_SESSION['error_message'] = 'Please login to access admin panel.';
    redirect('login.php');
}

// Handle form submissions
$message = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';
    
    if ($action === 'add_category') {
        $category_name = sanitize_input($_POST['category_name'] ?? '');
        $description = sanitize_input($_POST['description'] ?? '');
        
        if (!empty($category_name)) {
            $stmt = prepared_query(
                "INSERT INTO categories (category_name, description) VALUES (?, ?)",
                "ss",
                [$category_name, $description]
            );
            
            if ($stmt) {
                $message = 'Category added successfully!';
            } else {
                $message = 'Failed to add category.';
            }
        }
    } elseif ($action === 'edit_category') {
        $category_id = (int)($_POST['category_id'] ?? 0);
        $category_name = sanitize_input($_POST['category_name'] ?? '');
        $description = sanitize_input($_POST['description'] ?? '');
        
        if ($category_id > 0 && !empty($category_name)) {
            $stmt = prepared_query(
                "UPDATE categories SET category_name = ?, description = ? WHERE category_id = ?",
                "ssi",
                [$category_name, $description, $category_id]
            );
            
            if ($stmt) {
                $message = 'Category updated successfully!';
            } else {
                $message = 'Failed to update category.';
            }
        }
    } elseif ($action === 'delete_category') {
        $category_id = (int)($_POST['category_id'] ?? 0);
        
        if ($category_id > 0) {
            // Check if category has products
            $stmt = prepared_query("SELECT COUNT(*) as count FROM products WHERE category_id = ?", "i", [$category_id]);
            $result = mysqli_stmt_get_result($stmt);
            $product_count = mysqli_fetch_assoc($result)['count'];
            
            if ($product_count > 0) {
                $message = 'Cannot delete category. It contains products.';
            } else {
                $stmt = prepared_query("DELETE FROM categories WHERE category_id = ?", "i", [$category_id]);
                if ($stmt) {
                    $message = 'Category deleted successfully!';
                } else {
                    $message = 'Failed to delete category.';
                }
            }
        }
    }
}

// Get all categories
$categories = [];
$stmt = prepared_query("SELECT * FROM categories ORDER BY category_name");
$result = mysqli_stmt_get_result($stmt);
while ($row = mysqli_fetch_assoc($result)) {
    $categories[] = $row;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Categories Management - ARTNEPAL Admin</title>
    <link rel="stylesheet" href="../assets/css/style.css">
    <style>
        .admin-layout {
            display: grid;
            grid-template-columns: 250px 1fr;
            min-height: 100vh;
        }
        
        .admin-sidebar {
            background: linear-gradient(135deg, #2F4F4F, #1C3A3A);
            color: white;
            padding: 2rem 1rem;
        }
        
        .admin-logo {
            font-size: 1.5rem;
            font-weight: bold;
            color: #D4AF37;
            margin-bottom: 2rem;
            text-align: center;
        }
        
        .admin-menu {
            list-style: none;
        }
        
        .admin-menu li {
            margin-bottom: 0.5rem;
        }
        
        .admin-menu a {
            display: block;
            padding: 0.75rem 1rem;
            color: white;
            text-decoration: none;
            border-radius: 5px;
            transition: background-color 0.3s ease;
        }
        
        .admin-menu a:hover,
        .admin-menu a.active {
            background-color: #D4AF37;
            color: #2F4F4F;
        }
        
        .admin-main {
            background: #F8F9FA;
            padding: 2rem;
        }
        
        .admin-header {
            background: white;
            padding: 1.5rem;
            border-radius: 10px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
            margin-bottom: 2rem;
        }
        
        .admin-content {
            background: white;
            padding: 2rem;
            border-radius: 10px;
            box-shadow: 0 5px 15px rgba(0,0,0,0.1);
        }
        
        .form-group {
            margin-bottom: 1.5rem;
        }
        
        .form-group label {
            display: block;
            margin-bottom: 0.5rem;
            font-weight: bold;
            color: #2F4F4F;
        }
        
        .form-control {
            width: 100%;
            padding: 0.75rem;
            border: 2px solid #DEB887;
            border-radius: 5px;
            font-size: 1rem;
            transition: border-color 0.3s ease;
        }
        
        .form-control:focus {
            outline: none;
            border-color: #2F4F4F;
            box-shadow: 0 0 5px rgba(47,79,79,0.3);
        }
        
        .btn {
            padding: 0.75rem 1.5rem;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            font-size: 1rem;
            transition: all 0.3s ease;
            text-decoration: none;
            display: inline-block;
        }
        
        .btn-primary {
            background-color: #2F4F4F;
            color: white;
        }
        
        .btn-secondary {
            background-color: #6C757D;
            color: white;
        }
        
        .btn-danger {
            background-color: #DC3545;
            color: white;
        }
        
        .data-table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 2rem;
        }
        
        .data-table th,
        .data-table td {
            padding: 1rem;
            text-align: left;
            border-bottom: 1px solid #DEB887;
        }
        
        .data-table th {
            background-color: #2F4F4F;
            color: white;
            font-weight: bold;
        }
        
        .data-table tr:hover {
            background-color: #F8F9FA;
        }
        
        .alert {
            padding: 1rem;
            border-radius: 5px;
            margin-bottom: 1rem;
        }
        
        .alert-success {
            background-color: #D4EDDA;
            border: 1px solid #C3E6CB;
            color: #155724;
        }
        
        .alert-danger {
            background-color: #F8D7DA;
            border: 1px solid #F5C6CB;
            color: #721C24;
        }
        
        .form-grid {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 2rem;
            margin-bottom: 2rem;
        }
        
        @media (max-width: 768px) {
            .admin-layout {
                grid-template-columns: 1fr;
            }
            
            .admin-sidebar {
                order: 2;
            }
            
            .admin-main {
                order: 1;
            }
            
            .form-grid {
                grid-template-columns: 1fr;
            }
        }
    </style>
</head>
<body>
    <div class="admin-layout">
        <!-- Sidebar -->
        <aside class="admin-sidebar">
            <div class="admin-logo">ARTNEPAL ADMIN</div>
            <ul class="admin-menu">
                <li><a href="dashboard.php">📊 Dashboard</a></li>
                <li><a href="products.php">🛍️ Products</a></li>
                <li><a href="categories.php" class="active">📁 Categories</a></li>
                <li><a href="orders.php">📦 Orders</a></li>
                <li><a href="users.php">👥 Users</a></li>
                <li><a href="messages.php">💬 Messages</a></li>
                <li><a href="logout.php">🚪 Logout</a></li>
            </ul>
        </aside>
        
        <!-- Main Content -->
        <main class="admin-main">
            <!-- Header -->
            <div class="admin-header">
                <h1 style="color: #2F4F4F; margin: 0;">Categories Management</h1>
                <p style="color: #666; margin: 0.5rem 0 0 0;">Manage product categories</p>
            </div>
            
            <!-- Content -->
            <div class="admin-content">
                <?php if (!empty($message)): ?>
                    <div class="alert alert-<?php echo strpos($message, 'success') !== false ? 'success' : 'danger'; ?>">
                        <?php echo $message; ?>
                    </div>
                <?php endif; ?>
                
                <!-- Add Category Form -->
                <div style="margin-bottom: 3rem;">
                    <h2 style="color: #2F4F4F; margin-bottom: 1.5rem;">Add New Category</h2>
                    <form method="POST" action="categories.php">
                        <input type="hidden" name="action" value="add_category">
                        <div class="form-grid">
                            <div class="form-group">
                                <label for="category_name">Category Name *</label>
                                <input type="text" 
                                       id="category_name" 
                                       name="category_name" 
                                       class="form-control" 
                                       placeholder="Enter category name"
                                       required>
                            </div>
                            
                            <div class="form-group">
                                <label for="description">Description</label>
                                <textarea id="description" 
                                          name="description" 
                                          class="form-control" 
                                          rows="3"
                                          placeholder="Enter category description"></textarea>
                            </div>
                        </div>
                        
                        <button type="submit" class="btn btn-primary">Add Category</button>
                    </form>
                </div>
                
                <!-- Categories Table -->
                <div>
                    <h2 style="color: #2F4F4F; margin-bottom: 1.5rem;">Existing Categories</h2>
                    
                    <?php if (empty($categories)): ?>
                        <p style="color: #666; text-align: center; padding: 2rem;">No categories found.</p>
                    <?php else: ?>
                        <table class="data-table">
                            <thead>
                                <tr>
                                    <th>Category ID</th>
                                    <th>Category Name</th>
                                    <th>Description</th>
                                    <th>Created At</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($categories as $category): ?>
                                    <tr>
                                        <td><?php echo $category['category_id']; ?></td>
                                        <td><?php echo htmlspecialchars($category['category_name']); ?></td>
                                        <td><?php echo htmlspecialchars($category['description'] ?? 'No description'); ?></td>
                                        <td><?php echo date('M d, Y', strtotime($category['created_at'])); ?></td>
                                        <td>
                                            <button class="btn btn-secondary" onclick="editCategory(<?php echo $category['category_id']; ?>, '<?php echo htmlspecialchars($category['category_name']); ?>', '<?php echo htmlspecialchars($category['description'] ?? ''); ?>')" style="padding: 0.25rem 0.75rem; font-size: 0.85rem;">
                                                Edit
                                            </button>
                                            <button class="btn btn-danger" onclick="deleteCategory(<?php echo $category['category_id']; ?>, '<?php echo htmlspecialchars($category['category_name']); ?>')" style="padding: 0.25rem 0.75rem; font-size: 0.85rem;">
                                                Delete
                                            </button>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    <?php endif; ?>
                </div>
            </div>
        </main>
    </div>
    
    <!-- Edit Category Modal -->
    <div id="editModal" style="display: none; position: fixed; top: 0; left: 0; width: 100%; height: 100%; background: rgba(0,0,0,0.5); z-index: 9999; align-items: center; justify-content: center;">
        <div style="background: white; padding: 2rem; border-radius: 10px; max-width: 500px; width: 90%; position: relative;">
            <button onclick="closeEditModal()" style="position: absolute; top: 1rem; right: 1rem; background: none; border: none; font-size: 1.5rem; cursor: pointer; color: #666;">×</button>
            
            <h3 style="color: #2F4F4F; margin-bottom: 1.5rem;">Edit Category</h3>
            
            <form method="POST" action="categories.php">
                <input type="hidden" name="action" value="edit_category">
                <input type="hidden" name="category_id" id="edit_category_id">
                
                <div class="form-group">
                    <label for="edit_category_name">Category Name *</label>
                    <input type="text" 
                           id="edit_category_name" 
                           name="category_name" 
                           class="form-control" 
                           required>
                </div>
                
                <div class="form-group">
                    <label for="edit_description">Description</label>
                    <textarea id="edit_description" 
                              name="description" 
                              class="form-control" 
                              rows="3"></textarea>
                </div>
                
                <div style="display: flex; gap: 1rem;">
                    <button type="submit" class="btn btn-primary">Update Category</button>
                    <button type="button" class="btn btn-secondary" onclick="closeEditModal()">Cancel</button>
                </div>
            </form>
        </div>
    </div>
    
    <!-- Delete Confirmation Modal -->
    <div id="deleteModal" style="display: none; position: fixed; top: 0; left: 0; width: 100%; height: 100%; background: rgba(0,0,0,0.5); z-index: 9999; align-items: center; justify-content: center;">
        <div style="background: white; padding: 2rem; border-radius: 10px; max-width: 400px; width: 90%; text-align: center;">
            <h3 style="color: #DC3545; margin-bottom: 1rem;">Confirm Delete</h3>
            <p style="margin-bottom: 1.5rem;">Are you sure you want to delete the category "<span id="delete_category_name"></span>"?</p>
            
            <form method="POST" action="categories.php" style="display: inline;">
                <input type="hidden" name="action" value="delete_category">
                <input type="hidden" name="category_id" id="delete_category_id">
                <button type="submit" class="btn btn-danger">Delete</button>
                <button type="button" class="btn btn-secondary" onclick="closeDeleteModal()">Cancel</button>
            </form>
        </div>
    </div>
    
    <script>
        function editCategory(id, name, description) {
            document.getElementById('edit_category_id').value = id;
            document.getElementById('edit_category_name').value = name;
            document.getElementById('edit_description').value = description;
            document.getElementById('editModal').style.display = 'flex';
        }
        
        function closeEditModal() {
            document.getElementById('editModal').style.display = 'none';
        }
        
        function deleteCategory(id, name) {
            document.getElementById('delete_category_id').value = id;
            document.getElementById('delete_category_name').textContent = name;
            document.getElementById('deleteModal').style.display = 'flex';
        }
        
        function closeDeleteModal() {
            document.getElementById('deleteModal').style.display = 'none';
        }
        
        // Close modals when clicking outside
        window.onclick = function(event) {
            if (event.target.id === 'editModal') {
                closeEditModal();
            }
            if (event.target.id === 'deleteModal') {
                closeDeleteModal();
            }
        }
    </script>
</body>
</html>
