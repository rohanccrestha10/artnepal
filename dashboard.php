<?php
/**
 * User Dashboard Page
 * ARTNEPAL E-commerce Website
 * 
 * This page displays the user dashboard with product categories
 */

// Include header
require_once 'includes/header.php';

// Check if user is logged in
if (!is_logged_in()) {
    $_SESSION['error_message'] = 'Please login to access your dashboard.';
    redirect('login.php');
}

// Get user information
$user_id = $_SESSION['user_id'];
$stmt = prepared_query("SELECT full_name, email, phone_number, address FROM users WHERE user_id = ?", "i", [$user_id]);
$user = mysqli_fetch_assoc(mysqli_stmt_get_result($stmt));

// Get categories with product counts
$categories = [];
$stmt = prepared_query("
    SELECT c.*, COUNT(p.product_id) as product_count 
    FROM categories c 
    LEFT JOIN products p ON c.category_id = p.category_id 
    GROUP BY c.category_id 
    ORDER BY c.category_name
");
$result = mysqli_stmt_get_result($stmt);
while ($row = mysqli_fetch_assoc($result)) {
    $categories[] = $row;
}

// Get recent orders
$recent_orders = [];
$stmt = prepared_query("
    SELECT o.*, COUNT(oi.order_item_id) as item_count 
    FROM orders o 
    LEFT JOIN order_items oi ON o.order_id = oi.order_id 
    WHERE o.user_id = ? 
    GROUP BY o.order_id 
    ORDER BY o.created_at DESC 
    LIMIT 5
", "i", [$user_id]);
$result = mysqli_stmt_get_result($stmt);
while ($row = mysqli_fetch_assoc($result)) {
    $recent_orders[] = $row;
}

// Get cart summary
$cart_summary = ['total_items' => 0, 'total_amount' => 0];
$stmt = prepared_query("
    SELECT COUNT(*) as total_items, SUM(c.quantity * p.price) as total_amount 
    FROM cart c 
    JOIN products p ON c.product_id = p.product_id 
    WHERE c.user_id = ?
", "i", [$user_id]);
$result = mysqli_stmt_get_result($stmt);
if ($row = mysqli_fetch_assoc($result)) {
    $cart_summary = [
        'total_items' => $row['total_items'],
        'total_amount' => $row['total_amount'] ?? 0
    ];
}
?>

<div class="container">
    <div class="dashboard">
        <!-- Sidebar -->
        <aside class="sidebar">
            <div style="text-align: center; margin-bottom: 2rem;">
                <div style="width: 80px; height: 80px; background: linear-gradient(135deg, #8B4513, #D4AF37); border-radius: 50%; margin: 0 auto 1rem; display: flex; align-items: center; justify-content: center; color: white; font-size: 2rem;">
                    👤
                </div>
                <h3 style="color: #8B4513; margin-bottom: 0.5rem;"><?php echo htmlspecialchars($user['full_name']); ?></h3>
                <p style="color: #666; font-size: 0.9rem;"><?php echo htmlspecialchars($user['email']); ?></p>
            </div>
            
            <ul class="sidebar-menu">
                <li><a href="dashboard.php" class="active">📊 Dashboard</a></li>
                <li><a href="products.php">🛍️ Browse Products</a></li>
                <li><a href="cart.php">🛒 My Cart (<?php echo $cart_summary['total_items']; ?>)</a></li>
                <li><a href="orders.php">📦 My Orders</a></li>
                <li><a href="profile.php">⚙️ Profile Settings</a></li>
                <li><a href="logout.php">🚪 Logout</a></li>
            </ul>
        </aside>
        
        <!-- Main Content -->
        <main class="main-content">
            <h1 style="color: #8B4513; margin-bottom: 2rem;">Welcome to Your Dashboard</h1>
            
            <!-- Quick Stats -->
            <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 1.5rem; margin-bottom: 2rem;">
                <div class="stat-card">
                    <div class="stat-number"><?php echo count($recent_orders); ?></div>
                    <div class="stat-label">Total Orders</div>
                </div>
                
                <div class="stat-card">
                    <div class="stat-number"><?php echo $cart_summary['total_items']; ?></div>
                    <div class="stat-label">Cart Items</div>
                </div>
                
                <div class="stat-card">
                    <div class="stat-number"><?php echo format_price($cart_summary['total_amount']); ?></div>
                    <div class="stat-label">Cart Total</div>
                </div>
                
                <div class="stat-card">
                    <div class="stat-number"><?php echo count($categories); ?></div>
                    <div class="stat-label">Categories</div>
                </div>
            </div>
            
            <!-- Product Categories -->
            <section style="margin-bottom: 3rem;">
                <h2 style="color: #8B4513; margin-bottom: 1.5rem;">Browse by Category</h2>
                <div class="products-grid">
                    <?php foreach ($categories as $category): ?>
                        <div class="product-card" style="cursor: pointer;" onclick="window.location.href='products.php?category=<?php echo $category['category_id']; ?>'">
                            <div style="height: 150px; background: linear-gradient(135deg, #8B4513, #D4AF37); display: flex; align-items: center; justify-content: center; color: white; font-size: 2.5rem;">
                                <?php
                                // Display appropriate emoji for each category
                                $category_icons = [
                                    'Clay Products' => '🏺',
                                    'Thangka Paintings' => '🖼️',
                                    'Wooden Crafts' => '🪵',
                                    'Nepali Culture Arts' => '🎨',
                                    'Nepali Culture Masks' => '🎭',
                                    'God Statues' => '🗿'
                                ];
                                echo $category_icons[$category['category_name']] ?? '🎁';
                                ?>
                            </div>
                            <div class="product-info">
                                <h3 class="product-name"><?php echo htmlspecialchars($category['category_name']); ?></h3>
                                <p style="color: #666; font-size: 0.9rem; margin-bottom: 0.5rem;"><?php echo $category['product_count']; ?> products available</p>
                                <button class="btn btn-primary" style="width: 100%;">View Products</button>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            </section>
            
            <!-- Recent Orders -->
            <section style="margin-bottom: 3rem;">
                <h2 style="color: #8B4513; margin-bottom: 1.5rem;">Recent Orders</h2>
                <?php if (empty($recent_orders)): ?>
                    <div style="background: #FFF8DC; padding: 2rem; border-radius: 10px; text-align: center; border: 2px dashed #DEB887;">
                        <div style="font-size: 3rem; margin-bottom: 1rem;">📦</div>
                        <h3 style="color: #8B4513; margin-bottom: 1rem;">No Orders Yet</h3>
                        <p style="color: #666; margin-bottom: 1.5rem;">Start shopping to see your orders here!</p>
                        <a href="products.php" class="btn btn-primary">Start Shopping</a>
                    </div>
                <?php else: ?>
                    <div class="data-table-container">
                        <table class="data-table">
                            <thead>
                                <tr>
                                    <th>Order ID</th>
                                    <th>Date</th>
                                    <th>Items</th>
                                    <th>Total Amount</th>
                                    <th>Status</th>
                                    <th>Action</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($recent_orders as $order): ?>
                                    <tr>
                                        <td>#<?php echo str_pad($order['order_id'], 6, '0', STR_PAD_LEFT); ?></td>
                                        <td><?php echo date('M d, Y', strtotime($order['created_at'])); ?></td>
                                        <td><?php echo $order['item_count']; ?> items</td>
                                        <td><?php echo format_price($order['total_amount']); ?></td>
                                        <td>
                                            <span style="padding: 0.25rem 0.75rem; border-radius: 15px; font-size: 0.85rem; background-color: <?php
                                                switch($order['order_status']) {
                                                    case 'pending': echo '#FFF3CD; color: #856404;'; break;
                                                    case 'processing': echo '#CCE5FF; color: #004085;'; break;
                                                    case 'shipped': echo '#D4EDDA; color: #155724;'; break;
                                                    case 'delivered': echo '#D1ECF1; color: #0C5460;'; break;
                                                    case 'cancelled': echo '#F8D7DA; color: #721C24;'; break;
                                                    default: echo '#E2E3E5; color: #383D41;';
                                                }
                                            ?>;">
                                                <?php echo ucfirst($order['order_status']); ?>
                                            </span>
                                        </td>
                                        <td>
                                            <a href="order_details.php?id=<?php echo $order['order_id']; ?>" class="btn btn-secondary" style="padding: 0.25rem 0.75rem; font-size: 0.85rem;">
                                                View Details
                                            </a>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                    <div style="text-align: center; margin-top: 1.5rem;">
                        <a href="orders.php" class="btn btn-primary">View All Orders</a>
                    </div>
                <?php endif; ?>
            </section>
            
            <!-- Quick Actions -->
            <section>
                <h2 style="color: #8B4513; margin-bottom: 1.5rem;">Quick Actions</h2>
                <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 1rem;">
                    <a href="products.php" class="btn btn-primary" style="text-align: center; padding: 1.5rem;">
                        🛍️ Browse Products
                    </a>
                    <a href="cart.php" class="btn btn-secondary" style="text-align: center; padding: 1.5rem;">
                        🛒 View Cart
                    </a>
                    <a href="orders.php" class="btn btn-secondary" style="text-align: center; padding: 1.5rem;">
                        📦 Order History
                    </a>
                    <a href="profile.php" class="btn btn-secondary" style="text-align: center; padding: 1.5rem;">
                        ⚙️ Update Profile
                    </a>
                </div>
            </section>
        </main>
    </div>
</div>

<style>
.dashboard {
    display: grid;
    grid-template-columns: 250px 1fr;
    gap: 2rem;
    margin: 2rem 0;
}

.sidebar {
    background: white;
    padding: 1.5rem;
    border-radius: 10px;
    box-shadow: 0 5px 15px rgba(0,0,0,0.1);
    height: fit-content;
}

.sidebar-menu {
    list-style: none;
}

.sidebar-menu li {
    margin-bottom: 0.5rem;
}

.sidebar-menu a {
    display: block;
    padding: 0.75rem 1rem;
    color: #8B4513;
    text-decoration: none;
    border-radius: 5px;
    transition: background-color 0.3s ease;
}

.sidebar-menu a:hover,
.sidebar-menu a.active {
    background-color: #D4AF37;
    color: white;
}

.main-content {
    background: white;
    padding: 2rem;
    border-radius: 10px;
    box-shadow: 0 5px 15px rgba(0,0,0,0.1);
}

.stat-card {
    background: white;
    padding: 1.5rem;
    border-radius: 10px;
    box-shadow: 0 5px 15px rgba(0,0,0,0.1);
    text-align: center;
    border-left: 4px solid #8B4513;
}

.stat-number {
    font-size: 2rem;
    font-weight: bold;
    color: #8B4513;
}

.stat-label {
    color: #666;
    margin-top: 0.5rem;
}

.data-table-container {
    overflow-x: auto;
}

@media (max-width: 768px) {
    .dashboard {
        grid-template-columns: 1fr;
    }
    
    .sidebar {
        order: 2;
    }
    
    .main-content {
        order: 1;
    }
}
</style>

<?php
// Include footer
require_once 'includes/footer.php';
?>
