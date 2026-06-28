<?php
/**
 * Order Success Page
 * ARTNEPAL E-commerce Website
 * 
 * This page displays order confirmation after successful checkout
 */

// Include header
require_once 'includes/header.php';

// Check if user is logged in
if (!is_logged_in()) {
    $_SESSION['error_message'] = 'Please login to view your order.';
    redirect('login.php');
}

// Get order ID from URL
$order_id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

if ($order_id <= 0) {
    $_SESSION['error_message'] = 'Invalid order ID.';
    redirect('dashboard.php');
}

// Get order details
$stmt = prepared_query("
    SELECT o.*, COUNT(oi.order_item_id) as item_count 
    FROM orders o 
    LEFT JOIN order_items oi ON o.order_id = oi.order_id 
    WHERE o.order_id = ? AND o.user_id = ?
", "ii", [$order_id, $_SESSION['user_id']]);

$result = mysqli_stmt_get_result($stmt);
$order = mysqli_fetch_assoc($result);

if (!$order) {
    $_SESSION['error_message'] = 'Order not found.';
    redirect('dashboard.php');
}

// Get order items
$order_items = [];
$stmt = prepared_query("
    SELECT oi.*, p.product_name, p.product_image
    FROM order_items oi
    JOIN products p ON oi.product_id = p.product_id
    WHERE oi.order_id = ?
", "i", [$order_id]);

$result = mysqli_stmt_get_result($stmt);
while ($row = mysqli_fetch_assoc($result)) {
    $order_items[] = $row;
}
?>

<div class="container">
    <div style="max-width: 800px; margin: 2rem auto;">
        <!-- Success Message -->
        <div style="text-align: center; margin-bottom: 3rem;">
            <div style="font-size: 4rem; color: #28A745; margin-bottom: 1rem;">✅</div>
            <h1 style="color: #28A745; margin-bottom: 1rem;">Order Placed Successfully!</h1>
            <p style="color: #666; font-size: 1.1rem;">Thank you for your order. We've received your order and will process it shortly.</p>
        </div>
        
        <!-- Order Details -->
        <div style="background: white; padding: 2rem; border-radius: 10px; box-shadow: 0 5px 20px rgba(0,0,0,0.1); margin-bottom: 2rem;">
            <h2 style="color: #8B4513; margin-bottom: 1.5rem;">Order Details</h2>
            
            <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 1.5rem; margin-bottom: 2rem;">
                <div>
                    <div style="color: #666; font-size: 0.9rem; margin-bottom: 0.25rem;">Order Number</div>
                    <div style="font-weight: bold; color: #333;">#<?php echo str_pad($order['order_id'], 6, '0', STR_PAD_LEFT); ?></div>
                </div>
                
                <div>
                    <div style="color: #666; font-size: 0.9rem; margin-bottom: 0.25rem;">Order Date</div>
                    <div style="font-weight: bold; color: #333;"><?php echo date('M d, Y h:i A', strtotime($order['created_at'])); ?></div>
                </div>
                
                <div>
                    <div style="color: #666; font-size: 0.9rem; margin-bottom: 0.25rem;">Order Status</div>
                    <div style="font-weight: bold;">
                        <span style="padding: 0.25rem 0.75rem; border-radius: 15px; font-size: 0.85rem; background-color: #FFF3CD; color: #856404;">
                            <?php echo ucfirst($order['order_status']); ?>
                        </span>
                    </div>
                </div>
                
                <div>
                    <div style="color: #666; font-size: 0.9rem; margin-bottom: 0.25rem;">Total Amount</div>
                    <div style="font-weight: bold; color: #DC143C; font-size: 1.1rem;"><?php echo format_price($order['total_amount']); ?></div>
                </div>
            </div>
            
            <!-- Shipping Information -->
            <div style="background: #F8F9FA; padding: 1.5rem; border-radius: 5px; margin-bottom: 2rem;">
                <h3 style="color: #8B4513; margin-bottom: 1rem;">📦 Shipping Information</h3>
                <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 1rem;">
                    <div>
                        <div style="color: #666; font-size: 0.9rem; margin-bottom: 0.25rem;">Recipient Name</div>
                        <div style="font-weight: bold;"><?php echo htmlspecialchars($order['full_name']); ?></div>
                    </div>
                    
                    <div>
                        <div style="color: #666; font-size: 0.9rem; margin-bottom: 0.25rem;">Email</div>
                        <div style="font-weight: bold;"><?php echo htmlspecialchars($order['email']); ?></div>
                    </div>
                    
                    <div>
                        <div style="color: #666; font-size: 0.9rem; margin-bottom: 0.25rem;">Phone</div>
                        <div style="font-weight: bold;"><?php echo htmlspecialchars($order['phone_number']); ?></div>
                    </div>
                </div>
                
                <div style="margin-top: 1rem;">
                    <div style="color: #666; font-size: 0.9rem; margin-bottom: 0.25rem;">Delivery Address</div>
                    <div style="font-weight: bold;"><?php echo htmlspecialchars($order['address']); ?></div>
                </div>
            </div>
            
            <!-- Order Items -->
            <div>
                <h3 style="color: #8B4513; margin-bottom: 1rem;">🛍️ Order Items (<?php echo $order['item_count']; ?> items)</h3>
                <div style="border: 1px solid #DEB887; border-radius: 5px; overflow: hidden;">
                    <?php foreach ($order_items as $item): ?>
                        <div style="display: flex; align-items: center; padding: 1rem; border-bottom: 1px solid #F0F0F0;">
                            <img src="assets/images/products/<?php echo htmlspecialchars($item['product_image']); ?>" 
                                 alt="<?php echo htmlspecialchars($item['product_name']); ?>" 
                                 style="width: 60px; height: 60px; object-fit: cover; border-radius: 5px; margin-right: 1rem;"
                                 onerror="this.src='assets/images/placeholder.jpg';">
                            
                            <div style="flex: 1;">
                                <div style="font-weight: bold; color: #333;"><?php echo htmlspecialchars($item['product_name']); ?></div>
                                <div style="color: #666; font-size: 0.9rem;">
                                    <?php echo $item['quantity']; ?> × <?php echo format_price($item['price_per_item']); ?>
                                </div>
                            </div>
                            
                            <div style="color: #DC143C; font-weight: bold;">
                                <?php echo format_price($item['subtotal']); ?>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>
        </div>
        
        <!-- Next Steps -->
        <div style="background: #D1ECF1; border: 1px solid #BEE5EB; color: #0C5460; padding: 1.5rem; border-radius: 5px; margin-bottom: 2rem;">
            <h3 style="margin-bottom: 1rem;">📋 What's Next?</h3>
            <ol style="margin: 0; padding-left: 1.5rem;">
                <li style="margin-bottom: 0.5rem;">You will receive an order confirmation email shortly</li>
                <li style="margin-bottom: 0.5rem;">Our team will process your order within 24 hours</li>
                <li style="margin-bottom: 0.5rem;">You'll receive shipping updates via email and phone</li>
                <li>Estimated delivery: 3-5 business days within Kathmandu Valley</li>
            </ol>
        </div>
        
        <!-- Action Buttons -->
        <div style="display: flex; gap: 1rem; justify-content: center; flex-wrap: wrap;">
            <a href="dashboard.php" class="btn btn-primary" style="padding: 1rem 2rem;">
                🏠 Go to Dashboard
            </a>
            <a href="products.php" class="btn btn-secondary" style="padding: 1rem 2rem;">
                🛍️ Continue Shopping
            </a>
            <a href="order_details.php?id=<?php echo $order['order_id']; ?>" class="btn btn-secondary" style="padding: 1rem 2rem;">
                📄 View Order Details
            </a>
        </div>
        
        <!-- Contact Info -->
        <div style="text-align: center; margin-top: 3rem; padding: 2rem; background: #FFF8DC; border-radius: 10px;">
            <h3 style="color: #8B4513; margin-bottom: 1rem;">Need Help?</h3>
            <p style="color: #666; margin-bottom: 1rem;">If you have any questions about your order, feel free to contact us:</p>
            <div style="display: flex; justify-content: center; gap: 2rem; flex-wrap: wrap;">
                <div>
                    <strong>📧 Email:</strong> info@artnepal.com
                </div>
                <div>
                    <strong>📞 Phone:</strong> +977-9860146269
                </div>
                <div>
                    <strong>🕐 Hours:</strong> Mon-Sat, 10AM-6PM
                </div>
            </div>
        </div>
    </div>
</div>

<?php
// Include footer
require_once 'includes/footer.php';
?>
