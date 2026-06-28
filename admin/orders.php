<?php
// Start session
session_start();
require_once '../config/database.php';

if (!is_admin_logged_in()) {
    redirect('login.php');
}

// Check if payment columns exist in orders table
$payment_columns_exist = false;
$result = mysqli_query($conn, "SHOW COLUMNS FROM orders LIKE 'payment_method'");
if (mysqli_num_rows($result) > 0) {
    $payment_columns_exist = true;
}

// Handle delete order
if (isset($_POST['action']) && $_POST['action'] === 'delete_order') {
    $order_id = (int)$_POST['order_id'];
    
    if ($order_id > 0) {
        // Start transaction
        mysqli_begin_transaction($conn);
        
        try {
            // Delete order items first
            $stmt = prepared_query("DELETE FROM order_items WHERE order_id = ?", "i", [$order_id]);
            
            // Delete the order
            $stmt = prepared_query("DELETE FROM orders WHERE order_id = ?", "i", [$order_id]);
            
            // Commit transaction
            mysqli_commit($conn);
            
            $_SESSION['success_message'] = 'Order deleted successfully!';
            redirect('orders.php');
            
        } catch (Exception $e) {
            // Rollback transaction
            mysqli_rollback($conn);
            $_SESSION['error_message'] = 'Failed to delete order. Please try again.';
        }
    }
}

// Handle update order status
if (isset($_POST['action']) && $_POST['action'] === 'update_status') {
    $order_id = (int)$_POST['order_id'];
    $new_status = sanitize_input($_POST['new_status']);
    
    if ($order_id > 0 && in_array($new_status, ['pending', 'processing', 'shipped', 'delivered', 'cancelled'])) {
        $stmt = prepared_query("UPDATE orders SET order_status = ? WHERE order_id = ?", "si", [$new_status, $order_id]);
        
        if ($stmt) {
            $_SESSION['success_message'] = 'Order status updated successfully!';
            redirect('orders.php');
        } else {
            $_SESSION['error_message'] = 'Failed to update order status.';
        }
    }
}

// Handle update payment status
if (isset($_POST['action']) && $_POST['action'] === 'update_payment_status' && $payment_columns_exist) {
    $order_id = (int)$_POST['order_id'];
    $new_payment_status = sanitize_input($_POST['new_payment_status']);
    
    if ($order_id > 0 && in_array($new_payment_status, ['pending', 'paid', 'failed'])) {
        $stmt = prepared_query("UPDATE orders SET payment_status = ? WHERE order_id = ?", "si", [$new_payment_status, $order_id]);
        
        if ($stmt) {
            $_SESSION['success_message'] = 'Payment status updated successfully!';
            redirect('orders.php');
        } else {
            $_SESSION['error_message'] = 'Failed to update payment status.';
        }
    }
}

// Get orders with product details
$orders = [];
$stmt = prepared_query("
    SELECT o.*, u.full_name, u.email, u.phone_number, u.address,
           COUNT(oi.order_item_id) as item_count 
    FROM orders o 
    LEFT JOIN users u ON o.user_id = u.user_id 
    LEFT JOIN order_items oi ON o.order_id = oi.order_id 
    GROUP BY o.order_id 
    ORDER BY o.created_at DESC
");
$result = mysqli_stmt_get_result($stmt);
while ($row = mysqli_fetch_assoc($result)) {
    $orders[] = $row;
}

// Get order items for each order
$order_items = [];
foreach ($orders as $order) {
    $stmt = prepared_query("
        SELECT oi.*, p.product_name 
        FROM order_items oi 
        JOIN products p ON oi.product_id = p.product_id 
        WHERE oi.order_id = ?
    ", "i", [$order['order_id']]);
    
    $items = [];
    $result = mysqli_stmt_get_result($stmt);
    while ($row = mysqli_fetch_assoc($result)) {
        $items[] = $row;
    }
    $order_items[$order['order_id']] = $items;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Orders Management - ARTNEPAL Admin</title>
    <link rel="stylesheet" href="../assets/css/style.css">
    <style>
        .admin-layout { display: grid; grid-template-columns: 250px 1fr; min-height: 100vh; }
        .admin-sidebar { background: linear-gradient(135deg, #2F4F4F, #1C3A3A); color: white; padding: 2rem 1rem; }
        .admin-logo { font-size: 1.5rem; font-weight: bold; color: #D4AF37; margin-bottom: 2rem; text-align: center; }
        .admin-menu { list-style: none; }
        .admin-menu li { margin-bottom: 0.5rem; }
        .admin-menu a { display: block; padding: 0.75rem 1rem; color: white; text-decoration: none; border-radius: 5px; transition: background-color 0.3s ease; }
        .admin-menu a:hover, .admin-menu a.active { background-color: #D4AF37; color: #2F4F4F; }
        .admin-main { background: #F8F9FA; padding: 2rem; }
        .admin-header { background: white; padding: 1.5rem; border-radius: 10px; box-shadow: 0 2px 10px rgba(0,0,0,0.1); margin-bottom: 2rem; }
        .admin-content { background: white; padding: 2rem; border-radius: 10px; box-shadow: 0 5px 15px rgba(0,0,0,0.1); }
        .data-table { width: 100%; border-collapse: collapse; margin-top: 1rem; }
        .data-table th, .data-table td { padding: 0.75rem; text-align: left; border-bottom: 1px solid #DEB887; }
        .data-table th { background-color: #2F4F4F; color: white; font-weight: bold; }
        .data-table tr:hover { background-color: #F8F9FA; }
        .badge { padding: 0.25rem 0.75rem; border-radius: 15px; font-size: 0.75rem; font-weight: bold; }
        .badge-warning { background-color: #FFF3CD; color: #856404; }
        .badge-info { background-color: #CCE5FF; color: #004085; }
        .badge-success { background-color: #D4EDDA; color: #155724; }
        .badge-danger { background-color: #F8D7DA; color: #721C24; }
        .btn { padding: 0.25rem 0.75rem; border: none; border-radius: 5px; cursor: pointer; font-size: 0.8rem; transition: all 0.3s ease; }
        .btn-primary { background-color: #007BFF; color: white; }
        .btn-primary:hover { background-color: #0056B3; transform: translateY(-1px); }
        .btn-danger { background-color: #DC3545; color: white; }
        .btn-danger:hover { background-color: #C82333; transform: translateY(-1px); }
        .alert { padding: 1rem; border-radius: 5px; margin-bottom: 1rem; }
        .alert-success { background: #D4EDDA; border: 1px solid #C3E6CB; color: #155724; }
        .alert-danger { background: #F8D7DA; border: 1px solid #F5C6CB; color: #721C24; }
        .product-details { max-width: 250px; font-size: 0.85rem; }
        .product-item { margin-bottom: 0.5rem; padding-bottom: 0.5rem; border-bottom: 1px solid #F0F0F0; }
        .product-item:last-child { border-bottom: none; margin-bottom: 0; }
        .product-name { font-weight: bold; color: #333; }
        .product-price { color: #666; font-size: 0.8rem; }
        .status-select { cursor: pointer; transition: border-color 0.3s ease; }
        .status-select:hover { border-color: #8B4513; }
        .status-select:focus { outline: none; border-color: #8B4513; box-shadow: 0 0 3px rgba(139,69,19,0.3); }
        .customer-info { font-weight: bold; color: #333; }
        .customer-email { font-size: 0.85rem; color: #666; }
        .contact-info { font-size: 0.85rem; }
        .address-info { max-width: 150px; font-size: 0.85rem; word-wrap: break-word; }
    </style>
</head>
<body>
    <div class="admin-layout">
        <aside class="admin-sidebar">
            <div class="admin-logo">ARTNEPAL ADMIN</div>
            <ul class="admin-menu">
                <li><a href="dashboard.php">📊 Dashboard</a></li>
                <li><a href="products.php">🛍️ Products</a></li>
                <li><a href="categories.php">📁 Categories</a></li>
                <li><a href="orders.php" class="active">📦 Orders</a></li>
                <li><a href="users.php">👥 Users</a></li>
                <li><a href="messages.php">💬 Messages</a></li>
                <li><a href="logout.php">🚪 Logout</a></li>
            </ul>
        </aside>
        
        <main class="admin-main">
            <div class="admin-header">
                <h1 style="color: #2F4F4F; margin: 0;">Orders Management</h1>
                <p style="color: #666; margin: 0.5rem 0 0 0;">View and manage customer orders</p>
            </div>
            
            <div class="admin-content">
                <?php if (isset($_SESSION['success_message'])): ?>
                    <div class="alert alert-success" style="background: #D4EDDA; border: 1px solid #C3E6CB; color: #155724; padding: 1rem; border-radius: 5px; margin-bottom: 1rem;">
                        <?php echo $_SESSION['success_message']; unset($_SESSION['success_message']); ?>
                    </div>
                <?php endif; ?>
                
                <?php if (isset($_SESSION['error_message'])): ?>
                    <div class="alert alert-danger" style="background: #F8D7DA; border: 1px solid #F5C6CB; color: #721C24; padding: 1rem; border-radius: 5px; margin-bottom: 1rem;">
                        <?php echo $_SESSION['error_message']; unset($_SESSION['error_message']); ?>
                    </div>
                <?php endif; ?>
                
                <h2 style="color: #2F4F4F; margin-bottom: 1.5rem;">All Orders</h2>
                
                <?php if (empty($orders)): ?>
                    <p style="color: #666; text-align: center; padding: 2rem;">No orders found.</p>
                <?php else: ?>
                    <table class="data-table">
                        <thead>
                            <tr>
                                <th>Order ID</th>
                                <th>Customer</th>
                                <th>Contact</th>
                                <th>Delivery Address</th>
                                <th>Products</th>
                                <th>Total</th>
                                <?php if ($payment_columns_exist): ?>
                                    <th>Payment Method</th>
                                    <th>Payment Status</th>
                                <?php endif; ?>
                                <th>Order Status</th>
                                <th>Date</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($orders as $order): ?>
                                <tr>
                                    <td>#<?php echo str_pad($order['order_id'], 6, '0', STR_PAD_LEFT); ?></td>
                                    <td>
                                        <div class="customer-info"><?php echo htmlspecialchars($order['full_name'] ?? 'Guest'); ?></div>
                                        <div class="customer-email"><?php echo htmlspecialchars($order['email'] ?? 'N/A'); ?></div>
                                    </td>
                                    <td>
                                        <div class="contact-info"><?php echo htmlspecialchars($order['phone_number'] ?? 'N/A'); ?></div>
                                    </td>
                                    <td>
                                        <div class="address-info"><?php echo htmlspecialchars($order['address'] ?? 'N/A'); ?></div>
                                    </td>
                                    <td>
                                        <div class="product-details">
                                            <?php if (isset($order_items[$order['order_id']])): ?>
                                                <?php foreach ($order_items[$order['order_id']] as $item): ?>
                                                    <div class="product-item">
                                                        <div class="product-name"><?php echo htmlspecialchars($item['product_name']); ?></div>
                                                        <div class="product-price">
                                                            <?php echo $item['quantity']; ?> × <?php echo format_price($item['price_per_item']); ?> = <?php echo format_price($item['subtotal']); ?>
                                                        </div>
                                                    </div>
                                                <?php endforeach; ?>
                                            <?php else: ?>
                                                <span style="color: #999;">No items</span>
                                            <?php endif; ?>
                                        </div>
                                    </td>
                                    <td><?php echo format_price($order['total_amount']); ?></td>
                                    <?php if ($payment_columns_exist): ?>
                                        <td>
                                            <?php 
                                            $pm = $order['payment_method'] ?? 'N/A';
                                            if ($pm === 'esewa') {
                                                echo '<span class="badge badge-info">eSewa</span>';
                                            } elseif ($pm === 'cod') {
                                                echo '<span class="badge badge-success">Cash on Delivery</span>';
                                            } else {
                                                echo htmlspecialchars($pm);
                                            }
                                            ?>
                                        </td>
                                        <td>
                                            <form method="POST" style="display: inline;">
                                                <input type="hidden" name="action" value="update_payment_status">
                                                <input type="hidden" name="order_id" value="<?php echo $order['order_id']; ?>">
                                                <div style="display: flex; gap: 0.5rem; align-items: center;">
                                                    <select name="new_payment_status" class="status-select" style="padding: 0.25rem; border-radius: 3px; border: 1px solid #DEB887; font-size: 0.75rem;">
                                                        <option value="pending" <?php echo ($order['payment_status'] ?? 'pending') === 'pending' ? 'selected' : ''; ?>>Pending</option>
                                                        <option value="paid" <?php echo ($order['payment_status'] ?? '') === 'paid' ? 'selected' : ''; ?>>Paid</option>
                                                        <option value="failed" <?php echo ($order['payment_status'] ?? '') === 'failed' ? 'selected' : ''; ?>>Failed</option>
                                                    </select>
                                                    <button type="submit" class="btn btn-primary" style="padding: 0.25rem 0.75rem; font-size: 0.8rem;">
                                                        Update
                                                    </button>
                                                </div>
                                            </form>
                                        </td>
                                    <?php endif; ?>
                                    <td>
                                        <form method="POST" style="display: inline;">
                                            <input type="hidden" name="action" value="update_status">
                                            <input type="hidden" name="order_id" value="<?php echo $order['order_id']; ?>">
                                            <div style="display: flex; gap: 0.5rem; align-items: center;">
                                                <select name="new_status" class="status-select" style="padding: 0.25rem; border-radius: 3px; border: 1px solid #DEB887; font-size: 0.75rem;">
                                                    <option value="pending" <?php echo $order['order_status'] === 'pending' ? 'selected' : ''; ?>>Pending</option>
                                                    <option value="processing" <?php echo $order['order_status'] === 'processing' ? 'selected' : ''; ?>>Processing</option>
                                                    <option value="shipped" <?php echo $order['order_status'] === 'shipped' ? 'selected' : ''; ?>>Shipped</option>
                                                    <option value="delivered" <?php echo $order['order_status'] === 'delivered' ? 'selected' : ''; ?>>Delivered</option>
                                                    <option value="cancelled" <?php echo $order['order_status'] === 'cancelled' ? 'selected' : ''; ?>>Cancelled</option>
                                                </select>
                                                <button type="submit" class="btn btn-primary" style="padding: 0.25rem 0.75rem; font-size: 0.8rem;">
                                                    Update
                                                </button>
                                            </div>
                                        </form>
                                    </td>
                                    <td><?php echo date('M d, Y', strtotime($order['created_at'])); ?></td>
                                    <td>
                                        <form method="POST" style="display: inline;" onsubmit="return confirm('Are you sure you want to delete this order? This action cannot be undone.');">
                                            <input type="hidden" name="action" value="delete_order">
                                            <input type="hidden" name="order_id" value="<?php echo $order['order_id']; ?>">
                                            <button type="submit" class="btn btn-danger" style="padding: 0.25rem 0.75rem; font-size: 0.8rem;">
                                                Delete
                                            </button>
                                        </form>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                <?php endif; ?>
            </div>
        </main>
    </div>
</body>
</html>
