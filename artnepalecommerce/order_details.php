<?php
/**
 * Order Details Page
 * ARTNEPAL E-commerce Website
 * 
 * This page displays detailed order information
 */

// Include header
require_once 'includes/header.php';

// Check if user is logged in
if (!is_logged_in()) {
    $_SESSION['error_message'] = 'Please login to view your order details.';
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
    SELECT o.*, u.phone_number, u.address
    FROM orders o 
    LEFT JOIN users u ON o.user_id = u.user_id
    WHERE o.order_id = ? AND o.user_id = ?
", "ii", [$order_id, $_SESSION['user_id']]);

$result = mysqli_stmt_get_result($stmt);
$order = mysqli_fetch_assoc($result);

if (!$order) {
    $_SESSION['error_message'] = 'Order not found or you do not have permission to view this order.';
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
    <div style="max-width: 900px; margin: 2rem auto;">
        <!-- Page Header -->
        <div style="text-align: center; margin-bottom: 2rem;">
            <h1 style="color: #8B4513; margin-bottom: 0.5rem;">Order Details</h1>
            <p style="color: #666;">View complete information about your order</p>
        </div>
        
        <!-- Order Information -->
        <div style="background: white; padding: 2rem; border-radius: 10px; box-shadow: 0 5px 20px rgba(0,0,0,0.1); margin-bottom: 2rem;">
            <h2 style="color: #8B4513; margin-bottom: 1.5rem;">Order Information</h2>
            
            <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 1.5rem; margin-bottom: 2rem;">
                <div>
                    <div style="color: #666; font-size: 0.9rem; margin-bottom: 0.25rem;">Order Number</div>
                    <div style="font-weight: bold; color: #333; font-size: 1.1rem;">#<?php echo str_pad($order['order_id'], 6, '0', STR_PAD_LEFT); ?></div>
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
        </div>
        
        <!-- Customer Information -->
        <div style="background: white; padding: 2rem; border-radius: 10px; box-shadow: 0 5px 20px rgba(0,0,0,0.1); margin-bottom: 2rem;">
            <h2 style="color: #8B4513; margin-bottom: 1.5rem;">👤 Customer Information</h2>
            
            <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(250px, 1fr)); gap: 1.5rem;">
                <div>
                    <div style="color: #666; font-size: 0.9rem; margin-bottom: 0.25rem;">Full Name</div>
                    <div style="font-weight: bold;"><?php echo htmlspecialchars($order['full_name']); ?></div>
                </div>
                
                <div>
                    <div style="color: #666; font-size: 0.9rem; margin-bottom: 0.25rem;">Email Address</div>
                    <div style="font-weight: bold;"><?php echo htmlspecialchars($order['email']); ?></div>
                </div>
                
                <div>
                    <div style="color: #666; font-size: 0.9rem; margin-bottom: 0.25rem;">Phone Number</div>
                    <div style="font-weight: bold;"><?php echo htmlspecialchars($order['phone_number']); ?></div>
                </div>
            </div>
            
            <div style="margin-top: 1.5rem;">
                <div style="color: #666; font-size: 0.9rem; margin-bottom: 0.25rem;">Delivery Address</div>
                <div style="font-weight: bold; background: #F8F9FA; padding: 1rem; border-radius: 5px;"><?php echo htmlspecialchars($order['address']); ?></div>
            </div>
        </div>
        
        <!-- Order Items -->
        <div style="background: white; padding: 2rem; border-radius: 10px; box-shadow: 0 5px 20px rgba(0,0,0,0.1); margin-bottom: 2rem;">
            <h2 style="color: #8B4513; margin-bottom: 1.5rem;">🛍️ Order Items (<?php echo count($order_items); ?> items)</h2>
            
            <div style="border: 1px solid #DEB887; border-radius: 5px; overflow: hidden;">
                <?php foreach ($order_items as $item): ?>
                    <div style="display: flex; align-items: center; padding: 1.5rem; border-bottom: 1px solid #F0F0F0;">
                        <img src="assets/images/products/<?php echo htmlspecialchars($item['product_image']); ?>" 
                             alt="<?php echo htmlspecialchars($item['product_name']); ?>" 
                             style="width: 80px; height: 80px; object-fit: cover; border-radius: 5px; margin-right: 1.5rem;"
                             onerror="this.src='assets/images/placeholder.svg';">
                        
                        <div style="flex: 1;">
                            <div style="font-weight: bold; color: #333; font-size: 1.1rem; margin-bottom: 0.5rem;"><?php echo htmlspecialchars($item['product_name']); ?></div>
                            <div style="color: #666; font-size: 0.9rem;">
                                Quantity: <?php echo $item['quantity']; ?> | Price: <?php echo format_price($item['price_per_item']); ?>
                            </div>
                        </div>
                        
                        <div style="text-align: right;">
                            <div style="color: #666; font-size: 0.9rem; margin-bottom: 0.25rem;">Subtotal</div>
                            <div style="color: #DC143C; font-weight: bold; font-size: 1.1rem;"><?php echo format_price($item['subtotal']); ?></div>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
            
            <!-- Order Summary -->
            <div style="border-top: 1px solid #DEB887; padding-top: 1.5rem; margin-top: 1.5rem;">
                <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 0.5rem;">
                    <span style="color: #666;">Subtotal (<?php echo count($order_items); ?> items)</span>
                    <span><?php echo format_price($order['total_amount']); ?></span>
                </div>
                <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 0.5rem;">
                    <span style="color: #666;">Shipping</span>
                    <span style="color: #28A745;">Free</span>
                </div>
                <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 0.5rem;">
                    <span style="color: #666;">Tax</span>
                    <span>Included</span>
                </div>
                <div style="display: flex; justify-content: space-between; align-items: center; padding-top: 1rem; border-top: 2px solid #DEB887;">
                    <span style="font-weight: bold; font-size: 1.1rem; color: #333;">Total</span>
                    <span style="font-weight: bold; font-size: 1.2rem; color: #DC143C;"><?php echo format_price($order['total_amount']); ?></span>
                </div>
            </div>
        </div>
        
        <!-- Delivery Information -->
        <div style="background: #FFF8DC; padding: 2rem; border-radius: 10px; margin-bottom: 2rem;">
            <h3 style="color: #8B4513; margin-bottom: 1rem;">📦 Delivery Information</h3>
            <div style="color: #666; line-height: 1.6;">
                <p style="margin-bottom: 1rem;"><strong>Estimated Delivery:</strong> 3-5 business days within Kathmandu Valley, 5-7 business days outside valley.</p>
                <p style="margin-bottom: 1rem;"><strong>Payment Method:</strong> Cash on Delivery (COD)</p>
                <p><strong>Delivery Contact:</strong> <?php echo htmlspecialchars($order['phone_number']); ?></p>
            </div>
        </div>
        
        <!-- Action Buttons -->
        <div style="display: flex; gap: 1rem; justify-content: center; flex-wrap: wrap;">
            <a href="dashboard.php" class="btn btn-primary" style="padding: 1rem 2rem;">
                🏠 Back to Dashboard
            </a>
            <a href="products.php" class="btn btn-secondary" style="padding: 1rem 2rem;">
                🛍️ Continue Shopping
            </a>
            <a href="contact.php" class="btn btn-secondary" style="padding: 1rem 2rem;">
                📧 Contact Support
            </a>
        </div>
    </div>
</div>

<style>
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
    
    .container > div > div > div[style*="grid"] {
        grid-template-columns: 1fr;
        gap: 1rem;
    }
}
</style>

<?php
// Include footer
require_once 'includes/footer.php';
?>
