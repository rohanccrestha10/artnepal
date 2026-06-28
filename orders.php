<?php
/**
 * User Orders Page
 * ARTNEPAL E-commerce Website
 * 
 * This page displays all orders for the logged-in user
 */

// Include header
require_once 'includes/header.php';

// Check if user is logged in
if (!is_logged_in()) {
    $_SESSION['error_message'] = 'Please login to view your orders.';
    redirect('login.php');
}

// Get user information
$user_id = $_SESSION['user_id'];

// Get orders with product details
$orders = [];
$stmt = prepared_query("
    SELECT o.*, COUNT(oi.order_item_id) as item_count 
    FROM orders o 
    LEFT JOIN order_items oi ON o.order_id = oi.order_id 
    WHERE o.user_id = ? 
    GROUP BY o.order_id 
    ORDER BY o.created_at DESC
", "i", [$user_id]);
$result = mysqli_stmt_get_result($stmt);
while ($row = mysqli_fetch_assoc($result)) {
    $orders[] = $row;
}

// Get order items for each order
$order_items = [];
foreach ($orders as $order) {
    $stmt = prepared_query("
        SELECT oi.*, p.product_name, p.product_image 
        FROM order_items oi 
        JOIN products p ON oi.product_id = p.product_id 
        WHERE oi.order_id = ?
    ", "i", [$order['order_id']]);
    $result = mysqli_stmt_get_result($stmt);
    $items = [];
    while ($row = mysqli_fetch_assoc($result)) {
        $items[] = $row;
    }
    $order_items[$order['order_id']] = $items;
}
?>

<div class="container">
    <div style="max-width: 1200px; margin: 2rem auto;">
        <!-- Page Header -->
        <div style="text-align: center; margin-bottom: 2rem;">
            <h1 style="color: #8B4513; margin-bottom: 0.5rem;">My Orders</h1>
            <p style="color: #666;">View and track all your orders</p>
        </div>
        
        <!-- Success/Error Messages -->
        <?php if (isset($_SESSION['success_message'])): ?>
            <div style="background: #D4EDDA; border: 1px solid #C3E6CB; color: #155724; padding: 1rem; border-radius: 5px; margin-bottom: 2rem;">
                <h4 style="margin: 0 0 0.5rem 0;">✅ Success</h4>
                <p style="margin: 0;"><?php echo $_SESSION['success_message']; ?></p>
            </div>
            <?php unset($_SESSION['success_message']); ?>
        <?php endif; ?>
        
        <?php if (isset($_SESSION['error_message'])): ?>
            <div style="background: #F8D7DA; border: 1px solid #F5C6CB; color: #721C24; padding: 1rem; border-radius: 5px; margin-bottom: 2rem;">
                <h4 style="margin: 0 0 0.5rem 0;">❌ Error</h4>
                <p style="margin: 0;"><?php echo $_SESSION['error_message']; ?></p>
            </div>
            <?php unset($_SESSION['error_message']); ?>
        <?php endif; ?>
        
        <!-- Orders List -->
        <?php if (empty($orders)): ?>
            <div style="background: #FFF8DC; padding: 3rem; border-radius: 10px; text-align: center; border: 2px dashed #DEB887;">
                <div style="font-size: 4rem; margin-bottom: 1rem;">📦</div>
                <h2 style="color: #8B4513; margin-bottom: 1rem;">No Orders Yet</h2>
                <p style="color: #666; margin-bottom: 2rem;">You haven't placed any orders yet. Start shopping to see your orders here!</p>
                <a href="products.php" class="btn btn-primary" style="padding: 1rem 2rem;">
                    🛍️ Start Shopping
                </a>
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
                            <th>Products</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($orders as $order): ?>
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
                                    <div style="max-width: 200px;">
                                        <?php if (isset($order_items[$order['order_id']])): ?>
                                            <?php foreach (array_slice($order_items[$order['order_id']], 0, 2) as $item): ?>
                                                <div style="display: flex; align-items: center; margin-bottom: 0.5rem; padding: 0.5rem; background: #f8f9fa; border-radius: 5px;">
                                                    <?php if (!empty($item['product_image'])): ?>
                                                        <img src="assets/images/products/<?php echo htmlspecialchars($item['product_image']); ?>" 
                                                             alt="<?php echo htmlspecialchars($item['product_name']); ?>" 
                                                             style="width: 30px; height: 30px; object-fit: cover; border-radius: 3px; margin-right: 0.5rem;"
                                                             onerror="this.src='assets/images/placeholder.jpg';">
                                                    <?php else: ?>
                                                        <div style="width: 30px; height: 30px; background: #DEB887; border-radius: 3px; margin-right: 0.5rem; display: flex; align-items: center; justify-content: center; color: #8B4513; font-size: 0.8rem;">
                                                            📦
                                                        </div>
                                                    <?php endif; ?>
                                                    <div style="flex: 1;">
                                                        <div style="font-size: 0.8rem; color: #666;"><?php echo htmlspecialchars($item['product_name']); ?></div>
                                                        <div style="font-size: 0.7rem; color: #8B4513;">Qty: <?php echo $item['quantity']; ?></div>
                                                    </div>
                                                </div>
                                            <?php endforeach; ?>
                                            <?php if (count($order_items[$order['order_id']]) > 2): ?>
                                                <div style="text-align: center; color: #666; font-size: 0.8rem;">
                                                    +<?php echo count($order_items[$order['order_id']]) - 2; ?> more items
                                                </div>
                                            <?php endif; ?>
                                        <?php else: ?>
                                            <span style="color: #999;">No items</span>
                                        <?php endif; ?>
                                    </div>
                                </td>
                                <td>
                                    <a href="order_details.php?id=<?php echo $order['order_id']; ?>" class="btn btn-primary" style="padding: 0.25rem 0.75rem; font-size: 0.85rem;">
                                        View Details
                                    </a>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
            
            <!-- Back to Dashboard -->
            <div style="text-align: center; margin-top: 2rem;">
                <a href="dashboard.php" class="btn btn-secondary" style="padding: 1rem 2rem;">
                    🏠 Back to Dashboard
                </a>
            </div>
        <?php endif; ?>
    </div>
</div>

<style>
.data-table-container {
    background: white;
    border-radius: 10px;
    box-shadow: 0 5px 20px rgba(0,0,0,0.1);
    overflow: hidden;
    margin-bottom: 2rem;
}

.data-table {
    width: 100%;
    border-collapse: collapse;
    margin: 0;
}

.data-table th,
.data-table td {
    padding: 1rem;
    text-align: left;
    border-bottom: 1px solid #DEB887;
}

.data-table th {
    background-color: #8B4513;
    color: white;
    font-weight: bold;
}

.data-table tr:hover {
    background-color: #F8F9FA;
}

.btn {
    display: inline-block;
    padding: 0.75rem 1.5rem;
    border: none;
    border-radius: 5px;
    text-decoration: none;
    font-weight: bold;
    transition: all 0.3s ease;
}

.btn-primary {
    background-color: #8B4513;
    color: white;
}

.btn-primary:hover {
    background-color: #A0522D;
    transform: translateY(-2px);
}

.btn-secondary {
    background-color: #D4AF37;
    color: #8B4513;
}

.btn-secondary:hover {
    background-color: #FFD700;
    transform: translateY(-2px);
}

@media (max-width: 768px) {
    .container > div > div {
        padding: 1rem;
    }
    
    .data-table th,
    .data-table td {
        padding: 0.5rem;
        font-size: 0.8rem;
    }
}
</style>

<?php
// Include footer
require_once 'includes/footer.php';
?>
