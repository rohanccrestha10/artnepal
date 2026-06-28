<?php
/**
 * Admin Products Management
 * ARTNEPAL E-commerce Website
 * 
 * This page handles product management for admin
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
    
    if ($action === 'add_product') {
        $category_id = (int)($_POST['category_id'] ?? 0);
        $product_name = sanitize_input($_POST['product_name'] ?? '');
        $description = sanitize_input($_POST['description'] ?? '');
        $price = (float)($_POST['price'] ?? 0);
        $stock_quantity = (int)($_POST['stock_quantity'] ?? 0);
        
        if ($category_id > 0 && !empty($product_name) && $price > 0 && $stock_quantity >= 0) {
            $stmt = prepared_query(
                "INSERT INTO products (category_id, product_name, description, price, stock_quantity) VALUES (?, ?, ?, ?, ?)",
                "issdi",
                [$category_id, $product_name, $description, $price, $stock_quantity]
            );
            
            if ($stmt) {
                $message = 'Product added successfully!';
            } else {
                $message = 'Failed to add product.';
            }
        } else {
            $message = 'Please fill all required fields correctly.';
        }
    } elseif ($action === 'edit_product') {
        $product_id = (int)($_POST['product_id'] ?? 0);
        $category_id = (int)($_POST['category_id'] ?? 0);
        $product_name = sanitize_input($_POST['product_name'] ?? '');
        $description = sanitize_input($_POST['description'] ?? '');
        $price = (float)($_POST['price'] ?? 0);
        $stock_quantity = (int)($_POST['stock_quantity'] ?? 0);
        
        if ($product_id > 0 && $category_id > 0 && !empty($product_name) && $price > 0 && $stock_quantity >= 0) {
            $stmt = prepared_query(
                "UPDATE products SET category_id = ?, product_name = ?, description = ?, price = ?, stock_quantity = ? WHERE product_id = ?",
                "issdii",
                [$category_id, $product_name, $description, $price, $stock_quantity, $product_id]
            );
            
            if ($stmt) {
                $message = 'Product updated successfully!';
            } else {
                $message = 'Failed to update product.';
            }
        } else {
            $message = 'Please fill all required fields correctly.';
        }
    } elseif ($action === 'delete_product') {
        $product_id = (int)($_POST['product_id'] ?? 0);
        
        if ($product_id > 0) {
            // Check if product is in any orders
            $stmt = prepared_query("SELECT COUNT(*) as count FROM order_items WHERE product_id = ?", "i", [$product_id]);
            $result = mysqli_stmt_get_result($stmt);
            $order_count = mysqli_fetch_assoc($result)['count'];
            
            if ($order_count > 0) {
                $message = 'Cannot delete product. It is associated with orders.';
            } else {
                $stmt = prepared_query("DELETE FROM products WHERE product_id = ?", "i", [$product_id]);
                if ($stmt) {
                    $message = 'Product deleted successfully!';
                } else {
                    $message = 'Failed to delete product.';
                }
            }
        }
    }
}

// Get all products with category names
$products = [];
$stmt = prepared_query("
    SELECT p.*, c.category_name 
    FROM products p 
    JOIN categories c ON p.category_id = c.category_id 
    ORDER BY p.created_at DESC
");
$result = mysqli_stmt_get_result($stmt);
while ($row = mysqli_fetch_assoc($result)) {
    $products[] = $row;
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
    <title>Products Management - ARTNEPAL Admin</title>
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
            padding: 0.75rem;
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
            gap: 1.5rem;
        }
        
        .stock-low {
            color: #DC3545;
            font-weight: bold;
        }
        
        .stock-ok {
            color: #28A745;
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
                <li><a href="products.php" class="active">🛍️ Products</a></li>
                <li><a href="categories.php">📁 Categories</a></li>
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
                <h1 style="color: #2F4F4F; margin: 0;">Products Management</h1>
                <p style="color: #666; margin: 0.5rem 0 0 0;">Manage your product catalog</p>
            </div>
            
            <!-- Content -->
            <div class="admin-content">
                <?php if (!empty($message)): ?>
                    <div class="alert alert-<?php echo strpos($message, 'success') !== false ? 'success' : 'danger'; ?>">
                        <?php echo $message; ?>
                    </div>
                <?php endif; ?>
                
                <!-- Add Product Form -->
                <div style="margin-bottom: 3rem;">
                    <h2 style="color: #2F4F4F; margin-bottom: 1.5rem;">Add New Product</h2>
                    <form method="POST" action="products.php">
                        <input type="hidden" name="action" value="add_product">
                        <div class="form-grid">
                            <div class="form-group">
                                <label for="product_name">Product Name *</label>
                                <input type="text" 
                                       id="product_name" 
                                       name="product_name" 
                                       class="form-control" 
                                       placeholder="Enter product name"
                                       required>
                            </div>
                            
                            <div class="form-group">
                                <label for="category_id">Category *</label>
                                <select id="category_id" name="category_id" class="form-control" required>
                                    <option value="">Select a category</option>
                                    <?php foreach ($categories as $category): ?>
                                        <option value="<?php echo $category['category_id']; ?>">
                                            <?php echo htmlspecialchars($category['category_name']); ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                        </div>
                        
                        <div class="form-group">
                            <label for="description">Description</label>
                            <textarea id="description" 
                                      name="description" 
                                      class="form-control" 
                                      rows="3"
                                      placeholder="Enter product description"></textarea>
                        </div>
                        
                        <div class="form-grid">
                            <div class="form-group">
                                <label for="price">Price (NPR) *</label>
                                <input type="number" 
                                       id="price" 
                                       name="price" 
                                       class="form-control" 
                                       placeholder="0.00"
                                       step="0.01"
                                       min="0"
                                       required>
                            </div>
                            
                            <div class="form-group">
                                <label for="stock_quantity">Stock Quantity *</label>
                                <input type="number" 
                                       id="stock_quantity" 
                                       name="stock_quantity" 
                                       class="form-control" 
                                       placeholder="0"
                                       min="0"
                                       required>
                            </div>
                        </div>
                        
                        <button type="submit" class="btn btn-primary">Add Product</button>
                    </form>
                </div>
                
                <!-- Products Table -->
                <div>
                    <h2 style="color: #2F4F4F; margin-bottom: 1.5rem;">Existing Products</h2>
                    
                    <?php if (empty($products)): ?>
                        <p style="color: #666; text-align: center; padding: 2rem;">No products found.</p>
                    <?php else: ?>
                        <table class="data-table">
                            <thead>
                                <tr>
                                    <th>Product ID</th>
                                    <th>Product Name</th>
                                    <th>Category</th>
                                    <th>Price</th>
                                    <th>Stock</th>
                                    <th>Created</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($products as $product): ?>
                                    <tr>
                                        <td><?php echo $product['product_id']; ?></td>
                                        <td><?php echo htmlspecialchars($product['product_name']); ?></td>
                                        <td><?php echo htmlspecialchars($product['category_name']); ?></td>
                                        <td><?php echo format_price($product['price']); ?></td>
                                        <td class="<?php echo $product['stock_quantity'] <= 5 ? 'stock-low' : 'stock-ok'; ?>">
                                            <?php echo $product['stock_quantity']; ?>
                                            <?php if ($product['stock_quantity'] <= 5): ?>
                                                (Low Stock)
                                            <?php endif; ?>
                                        </td>
                                        <td><?php echo date('M d, Y', strtotime($product['created_at'])); ?></td>
                                        <td>
                                            <button class="btn btn-secondary" onclick="editProduct(<?php echo $product['product_id']; ?>)" style="padding: 0.25rem 0.75rem; font-size: 0.85rem;">
                                                Edit
                                            </button>
                                            <button class="btn btn-danger" onclick="deleteProduct(<?php echo $product['product_id']; ?>, '<?php echo htmlspecialchars($product['product_name']); ?>')" style="padding: 0.25rem 0.75rem; font-size: 0.85rem;">
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
    
    <!-- Edit Product Modal -->
    <div id="editModal" style="display: none; position: fixed; top: 0; left: 0; width: 100%; height: 100%; background: rgba(0,0,0,0.5); z-index: 9999; align-items: center; justify-content: center;">
        <div style="background: white; padding: 2rem; border-radius: 10px; max-width: 600px; width: 90%; position: relative; max-height: 90vh; overflow-y: auto;">
            <button onclick="closeEditModal()" style="position: absolute; top: 1rem; right: 1rem; background: none; border: none; font-size: 1.5rem; cursor: pointer; color: #666;">×</button>
            
            <h3 style="color: #2F4F4F; margin-bottom: 1.5rem;">Edit Product</h3>
            
            <form method="POST" action="products.php">
                <input type="hidden" name="action" value="edit_product">
                <input type="hidden" name="product_id" id="edit_product_id">
                
                <div class="form-grid">
                    <div class="form-group">
                        <label for="edit_product_name">Product Name *</label>
                        <input type="text" 
                               id="edit_product_name" 
                               name="product_name" 
                               class="form-control" 
                               required>
                    </div>
                    
                    <div class="form-group">
                        <label for="edit_category_id">Category *</label>
                        <select id="edit_category_id" name="category_id" class="form-control" required>
                            <?php foreach ($categories as $category): ?>
                                <option value="<?php echo $category['category_id']; ?>">
                                    <?php echo htmlspecialchars($category['category_name']); ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                </div>
                
                <div class="form-group">
                    <label for="edit_description">Description</label>
                    <textarea id="edit_description" 
                              name="description" 
                              class="form-control" 
                              rows="3"></textarea>
                </div>
                
                <div class="form-grid">
                    <div class="form-group">
                        <label for="edit_price">Price (NPR) *</label>
                        <input type="number" 
                               id="edit_price" 
                               name="price" 
                               class="form-control" 
                               step="0.01"
                               min="0"
                               required>
                    </div>
                    
                    <div class="form-group">
                        <label for="edit_stock_quantity">Stock Quantity *</label>
                        <input type="number" 
                               id="edit_stock_quantity" 
                               name="stock_quantity" 
                               class="form-control" 
                               min="0"
                               required>
                    </div>
                </div>
                
                <div style="display: flex; gap: 1rem;">
                    <button type="submit" class="btn btn-primary">Update Product</button>
                    <button type="button" class="btn btn-secondary" onclick="closeEditModal()">Cancel</button>
                </div>
            </form>
        </div>
    </div>
    
    <!-- Delete Confirmation Modal -->
    <div id="deleteModal" style="display: none; position: fixed; top: 0; left: 0; width: 100%; height: 100%; background: rgba(0,0,0,0.5); z-index: 9999; align-items: center; justify-content: center;">
        <div style="background: white; padding: 2rem; border-radius: 10px; max-width: 400px; width: 90%; text-align: center;">
            <h3 style="color: #DC3545; margin-bottom: 1rem;">Confirm Delete</h3>
            <p style="margin-bottom: 1.5rem;">Are you sure you want to delete the product "<span id="delete_product_name"></span>"?</p>
            
            <form method="POST" action="products.php" style="display: inline;">
                <input type="hidden" name="action" value="delete_product">
                <input type="hidden" name="product_id" id="delete_product_id">
                <button type="submit" class="btn btn-danger">Delete</button>
                <button type="button" class="btn btn-secondary" onclick="closeDeleteModal()">Cancel</button>
            </form>
        </div>
    </div>
    
    <script>
        function editProduct(productId) {
            // Fetch product data
            fetch('get_product_data.php?id=' + productId)
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        document.getElementById('edit_product_id').value = data.product.product_id;
                        document.getElementById('edit_product_name').value = data.product.product_name;
                        document.getElementById('edit_category_id').value = data.product.category_id;
                        document.getElementById('edit_description').value = data.product.description;
                        document.getElementById('edit_price').value = data.product.price;
                        document.getElementById('edit_stock_quantity').value = data.product.stock_quantity;
                        document.getElementById('editModal').style.display = 'flex';
                    } else {
                        alert('Error loading product data');
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                    alert('Error loading product data');
                });
        }
        
        function closeEditModal() {
            document.getElementById('editModal').style.display = 'none';
        }
        
        function deleteProduct(productId, productName) {
            document.getElementById('delete_product_id').value = productId;
            document.getElementById('delete_product_name').textContent = productName;
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
