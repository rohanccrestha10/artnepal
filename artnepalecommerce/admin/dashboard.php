<?php
/**
 * Admin Dashboard
 * ARTNEPAL E-commerce Website
 * 
 * This page displays the admin dashboard with statistics and management options
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

// Get dashboard statistics
$stats = [];

// Total users
$stmt = prepared_query("SELECT COUNT(*) as total FROM users");
$result = mysqli_stmt_get_result($stmt);
$stats['total_users'] = mysqli_fetch_assoc($result)['total'];

// Total products
$stmt = prepared_query("SELECT COUNT(*) as total FROM products");
$result = mysqli_stmt_get_result($stmt);
$stats['total_products'] = mysqli_fetch_assoc($result)['total'];

// Total orders
$stmt = prepared_query("SELECT COUNT(*) as total FROM orders");
$result = mysqli_stmt_get_result($stmt);
$stats['total_orders'] = mysqli_fetch_assoc($result)['total'];

// Total revenue
$stmt = prepared_query("SELECT SUM(total_amount) as total FROM orders WHERE order_status != 'cancelled'");
$result = mysqli_stmt_get_result($stmt);
$stats['total_revenue'] = mysqli_fetch_assoc($result)['total'] ?? 0;

// Recent orders
$recent_orders = [];
$stmt = prepared_query("
    SELECT o.*, u.full_name, u.email,
           COUNT(oi.order_item_id) as item_count 
    FROM orders o 
    LEFT JOIN users u ON o.user_id = u.user_id 
    LEFT JOIN order_items oi ON o.order_id = oi.order_id 
    GROUP BY o.order_id 
    ORDER BY o.created_at DESC 
    LIMIT 10
");
$result = mysqli_stmt_get_result($stmt);
while ($row = mysqli_fetch_assoc($result)) {
    $recent_orders[] = $row;
}

// Low stock products
$low_stock_products = [];
$stmt = prepared_query("
    SELECT p.*, c.category_name 
    FROM products p 
    JOIN categories c ON p.category_id = c.category_id 
    WHERE p.stock_quantity <= 5 
    ORDER BY p.stock_quantity ASC 
    LIMIT 10
");
$result = mysqli_stmt_get_result($stmt);
while ($row = mysqli_fetch_assoc($result)) {
    $low_stock_products[] = $row;
}

// Recent users
$recent_users = [];
$stmt = prepared_query("
    SELECT user_id, full_name, email, phone_number, created_at 
    FROM users 
    ORDER BY created_at DESC 
    LIMIT 10
");
$result = mysqli_stmt_get_result($stmt);
while ($row = mysqli_fetch_assoc($result)) {
    $recent_users[] = $row;
}

// Contact messages
$contact_messages = [];
$stmt = prepared_query("
    SELECT message_id, name, email, message, created_at, is_read 
    FROM contact_messages 
    ORDER BY created_at DESC 
    LIMIT 5
");
$result = mysqli_stmt_get_result($stmt);
while ($row = mysqli_fetch_assoc($result)) {
    $contact_messages[] = $row;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard - ARTNEPAL</title>
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
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
        
        .admin-header h1 {
            color: #2F4F4F;
            margin: 0;
        }
        
        .admin-user {
            color: #666;
        }
        
        .admin-stats {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 1.5rem;
            margin-bottom: 2rem;
        }
        
        .stat-card {
            background: white;
            padding: 1.5rem;
            border-radius: 10px;
            box-shadow: 0 5px 15px rgba(0,0,0,0.1);
            text-align: center;
            border-left: 4px solid #2F4F4F;
        }
        
        .stat-number {
            font-size: 2rem;
            font-weight: bold;
            color: #2F4F4F;
        }
        
        .stat-label {
            color: #666;
            margin-top: 0.5rem;
        }
        
        .admin-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(400px, 1fr));
            gap: 2rem;
        }
        
        .admin-card {
            background: white;
            padding: 1.5rem;
            border-radius: 10px;
            box-shadow: 0 5px 15px rgba(0,0,0,0.1);
        }
        
        .admin-card h3 {
            color: #2F4F4F;
            margin-bottom: 1rem;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
        
        .data-table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 1rem;
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
        
        .badge {
            padding: 0.25rem 0.75rem;
            border-radius: 15px;
            font-size: 0.75rem;
            font-weight: bold;
        }
        
        .badge-success {
            background-color: #D4EDDA;
            color: #155724;
        }
        
        .badge-warning {
            background-color: #FFF3CD;
            color: #856404;
        }
        
        .badge-danger {
            background-color: #F8D7DA;
            color: #721C24;
        }
        
        .badge-info {
            background-color: #CCE5FF;
            color: #004085;
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
            
            .admin-grid {
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
                <li><a href="dashboard.php" class="active">📊 Dashboard</a></li>
                <li><a href="products.php">🛍️ Products</a></li>
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
                <div>
                    <h1>Admin Dashboard</h1>
                    <p class="admin-user">Welcome back, <?php echo htmlspecialchars($_SESSION['admin_username']); ?>!</p>
                </div>
                <div>
                    <span style="color: #666;">Last login: <?php echo date('M d, Y h:i A', $_SESSION['admin_login_time']); ?></span>
                </div>
            </div>
            
            <!-- Statistics -->
            <div class="admin-stats">
                <div class="stat-card">
                    <div class="stat-number"><?php echo number_format($stats['total_users']); ?></div>
                    <div class="stat-label">Total Users</div>
                </div>
                
                <div class="stat-card">
                    <div class="stat-number"><?php echo number_format($stats['total_products']); ?></div>
                    <div class="stat-label">Total Products</div>
                </div>
                
                <div class="stat-card">
                    <div class="stat-number"><?php echo number_format($stats['total_orders']); ?></div>
                    <div class="stat-label">Total Orders</div>
                </div>
                
                <div class="stat-card">
                    <div class="stat-number"><?php echo format_price($stats['total_revenue']); ?></div>
                    <div class="stat-label">Total Revenue</div>
                </div>
            </div>
            
            <!-- Dashboard Grid -->
            <div class="admin-grid">
                <!-- Recent Orders -->
                <div class="admin-card">
                    <h3>
                        Recent Orders
                        <a href="orders.php" class="btn btn-secondary" style="padding: 0.25rem 0.75rem; font-size: 0.8rem;">View All</a>
                    </h3>
                    <?php if (empty($recent_orders)): ?>
                        <p style="color: #666; text-align: center; padding: 2rem;">No orders yet.</p>
                    <?php else: ?>
                        <table class="data-table">
                            <thead>
                                <tr>
                                    <th>Order ID</th>
                                    <th>Customer</th>
                                    <th>Amount</th>
                                    <th>Status</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach (array_slice($recent_orders, 0, 5) as $order): ?>
                                    <tr>
                                        <td>#<?php echo str_pad($order['order_id'], 6, '0', STR_PAD_LEFT); ?></td>
                                        <td><?php echo htmlspecialchars($order['full_name'] ?? 'Guest'); ?></td>
                                        <td><?php echo format_price($order['total_amount']); ?></td>
                                        <td>
                                            <span class="badge <?php
                                                switch($order['order_status']) {
                                                    case 'pending': echo 'badge-warning'; break;
                                                    case 'processing': echo 'badge-info'; break;
                                                    case 'shipped': echo 'badge-success'; break;
                                                    case 'delivered': echo 'badge-success'; break;
                                                    case 'cancelled': echo 'badge-danger'; break;
                                                    default: echo 'badge-secondary';
                                                }
                                            ?>">
                                                <?php echo ucfirst($order['order_status']); ?>
                                            </span>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    <?php endif; ?>
                </div>
                
                <!-- Orders Section -->
            <div class="admin-card">
                <h3>
                    Orders Management
                    <a href="orders.php" class="btn btn-secondary" style="padding: 0.25rem 0.75rem; font-size: 0.8rem;">Manage Orders</a>
                </h3>
                <p style="color: #666; text-align: center; margin-top: 1rem;">Click to manage all orders, view details, and update order status.</p>
            </div>
                <div class="admin-card">
                    <h3>
                        Low Stock Alert
                        <a href="products.php" class="btn btn-secondary" style="padding: 0.25rem 0.75rem; font-size: 0.8rem;">Manage</a>
                    </h3>
                    <?php if (empty($low_stock_products)): ?>
                        <p style="color: #28A745; text-align: center; padding: 2rem;">✅ All products have sufficient stock.</p>
                    <?php else: ?>
                        <table class="data-table">
                            <thead>
                                <tr>
                                    <th>Product</th>
                                    <th>Category</th>
                                    <th>Stock</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($low_stock_products as $product): ?>
                                    <tr>
                                        <td><?php echo htmlspecialchars($product['product_name']); ?></td>
                                        <td><?php echo htmlspecialchars($product['category_name']); ?></td>
                                        <td>
                                            <span class="badge badge-danger">
                                                <?php echo $product['stock_quantity']; ?> left
                                            </span>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    <?php endif; ?>
                </div>
                
                <!-- Recent Users -->
                <div class="admin-card">
                    <h3>
                        Recent Users
                        <a href="users.php" class="btn btn-secondary" style="padding: 0.25rem 0.75rem; font-size: 0.8rem;">View All</a>
                    </h3>
                    <?php if (empty($recent_users)): ?>
                        <p style="color: #666; text-align: center; padding: 2rem;">No users registered yet.</p>
                    <?php else: ?>
                        <table class="data-table">
                            <thead>
                                <tr>
                                    <th>Name</th>
                                    <th>Email</th>
                                    <th>Joined</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach (array_slice($recent_users, 0, 5) as $user): ?>
                                    <tr>
                                        <td><?php echo htmlspecialchars($user['full_name']); ?></td>
                                        <td><?php echo htmlspecialchars($user['email']); ?></td>
                                        <td><?php echo date('M d, Y', strtotime($user['created_at'])); ?></td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    <?php endif; ?>
                </div>
                
                <!-- Contact Messages -->
                <div class="admin-card">
                    <h3>
                        Recent Messages
                        <a href="messages.php" class="btn btn-secondary" style="padding: 0.25rem 0.75rem; font-size: 0.8rem;">View All</a>
                    </h3>
                    <?php if (empty($contact_messages)): ?>
                        <p style="color: #666; text-align: center; padding: 2rem;">No messages received.</p>
                    <?php else: ?>
                        <table class="data-table">
                            <thead>
                                <tr>
                                    <th>Name</th>
                                    <th>Email</th>
                                    <th>Status</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($contact_messages as $message): ?>
                                    <tr>
                                        <td><?php echo htmlspecialchars($message['name']); ?></td>
                                        <td><?php echo htmlspecialchars($message['email']); ?></td>
                                        <td>
                                            <span class="badge <?php echo $message['is_read'] ? 'badge-success' : 'badge-warning'; ?>">
                                                <?php echo $message['is_read'] ? 'Read' : 'Unread'; ?>
                                            </span>
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
</body>
</html>
