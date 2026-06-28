<?php
/**
 * Payment Success Page
 * ARTNEPAL E-commerce Website
 * 
 * This page displays payment success message after verification
 */

// Start session
session_start();

// Include header
require_once 'includes/header.php';

// Get payment response parameters from session or URL
$oid = $_GET['oid'] ?? $_SESSION['esewa_oid'] ?? null;
$amt = $_GET['amt'] ?? $_SESSION['esewa_amt'] ?? null;
$refId = $_GET['refId'] ?? $_SESSION['esewa_refId'] ?? null;
$pidx = $_GET['pidx'] ?? $_SESSION['khalti_pidx'] ?? null;
$khalti_amount = $_GET['amount'] ?? $_SESSION['khalti_amount'] ?? null;

// Get order details from session or database
$order_id = $_SESSION['order_id'] ?? null;

if (!$order_id) {
    // Try to get order ID from payment record
    require_once 'esewa/config.php';
    $payment = get_payment_details($oid);
    if ($payment) {
        $order_id = $payment['order_id'];
    }
}

if (!$order_id) {
    $_SESSION['error_message'] = 'Order not found. Please contact support.';
    redirect('index.php');
}

// Get order details
$stmt = prepared_query("
    SELECT o.*, u.full_name, u.email 
    FROM orders o 
    JOIN users u ON o.user_id = u.user_id 
    WHERE o.order_id = ?
", "i", [$order_id]);

$order = mysqli_fetch_assoc(mysqli_stmt_get_result($stmt));

if (!$order) {
    $_SESSION['error_message'] = 'Order not found. Please contact support.';
    redirect('index.php');
}
?>

<div class="container">
    <div style="max-width: 800px; margin: 2rem auto;">
        <!-- Success Message -->
        <div style="text-align: center; margin-bottom: 3rem;">
            <div style="font-size: 4rem; color: #28A745; margin-bottom: 1rem;">✅</div>
            <h1 style="color: #28A745; margin-bottom: 1rem;">Payment Successful!</h1>
            <p style="color: #666; font-size: 1.1rem;">Thank you for your payment. Your order has been placed successfully.</p>
        </div>
        
        <!-- Payment Details -->
        <div style="background: white; padding: 2rem; border-radius: 10px; box-shadow: 0 5px 20px rgba(0,0,0,0.1); margin-bottom: 2rem;">
            <h2 style="color: #8B4513; margin-bottom: 1.5rem;">Payment Details</h2>
            
            <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 1.5rem; margin-bottom: 2rem;">
                <div>
                    <div style="color: #666; font-size: 0.9rem; margin-bottom: 0.25rem;">Order Number</div>
                    <div style="font-weight: bold; color: #333;">#<?php echo str_pad($order['order_id'], 6, '0', STR_PAD_LEFT); ?></div>
                </div>
                
                <?php if ($oid): ?>
                <div>
                    <div style="color: #666; font-size: 0.9rem; margin-bottom: 0.25rem;">Transaction ID (eSewa)</div>
                    <div style="font-weight: bold; color: #333;"><?php echo htmlspecialchars($oid); ?></div>
                </div>
                <?php endif; ?>
                
                <?php if ($pidx): ?>
                <div>
                    <div style="color: #666; font-size: 0.9rem; margin-bottom: 0.25rem;">Payment Index (Khalti)</div>
                    <div style="font-weight: bold; color: #333;"><?php echo htmlspecialchars($pidx); ?></div>
                </div>
                <?php endif; ?>
                
                <?php if ($refId): ?>
                <div>
                    <div style="color: #666; font-size: 0.9rem; margin-bottom: 0.25rem;">Reference ID (eSewa)</div>
                    <div style="font-weight: bold; color: #333;"><?php echo htmlspecialchars($refId); ?></div>
                </div>
                <?php endif; ?>
                
                <div>
                    <div style="color: #666; font-size: 0.9rem; margin-bottom: 0.25rem;">Amount Paid</div>
                    <div style="font-weight: bold; color: #DC143C; font-size: 1.1rem;"><?php echo format_price($khalti_amount ?? $amt ?? $order['total_amount']); ?></div>
                </div>
            </div>
            
            <!-- Order Details -->
            <div style="background: #F8F9FA; padding: 1.5rem; border-radius: 5px; margin-bottom: 2rem;">
                <h3 style="color: #8B4513; margin-bottom: 1rem;">📦 Order Information</h3>
                <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 1rem;">
                    <div>
                        <div style="color: #666; font-size: 0.9rem; margin-bottom: 0.25rem;">Customer Name</div>
                        <div style="font-weight: bold;"><?php echo htmlspecialchars($order['full_name']); ?></div>
                    </div>
                    
                    <div>
                        <div style="color: #666; font-size: 0.9rem; margin-bottom: 0.25rem;">Email</div>
                        <div style="font-weight: bold;"><?php echo htmlspecialchars($order['email']); ?></div>
                    </div>
                    
                    <div>
                        <div style="color: #666; font-size: 0.9rem; margin-bottom: 0.25rem;">Order Status</div>
                        <div style="font-weight: bold; color: #28A745;">Processing</div>
                    </div>
                </div>
            </div>
            
            <!-- Contact Information -->
            <div style="background: #FFF8DC; padding: 1.5rem; border-radius: 5px; margin-bottom: 2rem;">
                <h3 style="color: #8B4513; margin-bottom: 1rem;">📞 Need Help?</h3>
                <p style="color: #666; margin-bottom: 1rem;">If you have any questions about your order, feel free to contact us:</p>
                <div style="display: flex; justify-content: center; gap: 2rem; flex-wrap: wrap;">
                    <div>
                        <strong>📧 Email:</strong> artnepal921@gmail.com
                    </div>
                    <div>
                        <strong>📞 Phone:</strong> +977-9860146269
                    </div>
                </div>
            </div>
        </div>
        
        <!-- Action Buttons -->
        <div style="display: flex; gap: 1rem; justify-content: center; flex-wrap: wrap;">
            <a href="order_details.php?id=<?php echo $order['order_id']; ?>" class="btn btn-primary" style="padding: 1rem 2rem;">
                View Order Details
            </a>
            <a href="dashboard.php" class="btn btn-secondary" style="padding: 1rem 2rem;">
                Go to Dashboard
            </a>
            <a href="products.php" class="btn btn-secondary" style="padding: 1rem 2rem;">
                Continue Shopping
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
    cursor: pointer;
    font-family: 'Georgia', serif;
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
}
</style>

<?php
// Clear session variables
unset($_SESSION['payment_success']);
unset($_SESSION['order_id']);
unset($_SESSION['esewa_oid']);
unset($_SESSION['esewa_amt']);
unset($_SESSION['esewa_refId']);

// Include footer
require_once 'includes/footer.php';
?>
