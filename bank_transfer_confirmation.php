<?php
/**
 * Bank Transfer Confirmation Page
 * ARTNEPAL E-commerce Website
 * 
 * This page shows bank transfer details and asks user to confirm payment
 */

// Start session
session_start();

// Include header
require_once 'includes/header.php';

// Check if user is logged in
if (!is_logged_in()) {
    $_SESSION['error_message'] = 'Please login to confirm payment.';
    redirect('login.php');
}

// Get order ID from URL
$order_id = $_GET['order_id'] ?? null;

if (!$order_id) {
    $_SESSION['error_message'] = 'Invalid order. Please try again.';
    redirect('checkout.php');
}

// Get order details
$stmt = prepared_query("
    SELECT o.*, u.full_name, u.email, u.phone_number 
    FROM orders o 
    JOIN users u ON o.user_id = u.user_id 
    WHERE o.order_id = ? AND o.user_id = ?
", "ii", [$order_id, $_SESSION['user_id']]);

$order = mysqli_fetch_assoc(mysqli_stmt_get_result($stmt));

if (!$order) {
    $_SESSION['error_message'] = 'Order not found. Please try again.';
    redirect('checkout.php');
}

// Get payment details
require_once 'khalti/config.php';
$payment = get_khalti_payment_details_by_order_id($order_id);

// Handle payment confirmation
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $transaction_ref = sanitize_input($_POST['transaction_ref'] ?? '');
    
    if (empty($transaction_ref)) {
        $errors['transaction_ref'] = 'Transaction reference number is required';
    }
    
    if (empty($errors)) {
        // Update payment with transaction reference
        $stmt = prepared_query("
            UPDATE payments 
            SET reference_id = ?, payment_status = 'pending_verification'
            WHERE order_id = ?
        ", "si", [$transaction_ref, $order_id]);
        
        // Clear cart
        $stmt = prepared_query("DELETE FROM cart WHERE user_id = ?", "i", [$_SESSION['user_id']]);
        
        $_SESSION['success_message'] = 'Payment confirmation submitted! Your order #' . str_pad($order_id, 6, '0', STR_PAD_LEFT) . ' is pending verification.';
        redirect('order_success.php?id=' . $order_id);
    }
}

// Helper function to get payment by order ID
function get_khalti_payment_details_by_order_id($order_id) {
    $stmt = prepared_query("
        SELECT * FROM payments 
        WHERE order_id = ?
    ", "i", [$order_id]);
    
    $result = mysqli_stmt_get_result($stmt);
    return mysqli_fetch_assoc($result);
}
?>

<div class="container">
    <div style="max-width: 900px; margin: 2rem auto;">
        <!-- Header -->
        <div style="text-align: center; margin-bottom: 2rem;">
            <h1 style="color: #8B4513; margin-bottom: 0.5rem;">🏦 Bank Transfer Payment</h1>
            <p style="color: #666;">Complete your payment by transferring to our bank account</p>
        </div>
        
        <!-- Order Summary -->
        <div style="background: white; padding: 2rem; border-radius: 10px; box-shadow: 0 5px 20px rgba(0,0,0,0.1); margin-bottom: 2rem;">
            <h2 style="color: #8B4513; margin-bottom: 1.5rem;">📦 Order Summary</h2>
            
            <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 1.5rem; margin-bottom: 2rem;">
                <div>
                    <div style="color: #666; font-size: 0.9rem; margin-bottom: 0.25rem;">Order Number</div>
                    <div style="font-weight: bold; color: #333;">#<?php echo str_pad($order['order_id'], 6, '0', STR_PAD_LEFT); ?></div>
                </div>
                
                <div>
                    <div style="color: #666; font-size: 0.9rem; margin-bottom: 0.25rem;">Customer Name</div>
                    <div style="font-weight: bold;"><?php echo htmlspecialchars($order['full_name']); ?></div>
                </div>
                
                <div>
                    <div style="color: #666; font-size: 0.9rem; margin-bottom: 0.25rem;">Total Amount</div>
                    <div style="font-weight: bold; color: #DC143C; font-size: 1.2rem;"><?php echo format_price($order['total_amount']); ?></div>
                </div>
            </div>
        </div>
        
        <!-- Bank Account Details -->
        <div style="background: #E3F2FD; padding: 2rem; border-radius: 10px; box-shadow: 0 5px 20px rgba(0,0,0,0.1); margin-bottom: 2rem;">
            <h2 style="color: #007bff; margin-bottom: 1.5rem;">🏦 Bank Account Details</h2>
            
            <div style="background: white; padding: 1.5rem; border-radius: 5px; margin-bottom: 1.5rem;">
                <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(250px, 1fr)); gap: 1rem;">
                    <div>
                        <div style="color: #666; font-size: 0.9rem; margin-bottom: 0.25rem;">Bank Name</div>
                        <div style="font-weight: bold; color: #333; font-size: 1.1rem;">Nepal Investment Bank Ltd.</div>
                    </div>
                    
                    <div>
                        <div style="color: #666; font-size: 0.9rem; margin-bottom: 0.25rem;">Account Name</div>
                        <div style="font-weight: bold; color: #333; font-size: 1.1rem;">Art Nepal</div>
                    </div>
                    
                    <div>
                        <div style="color: #666; font-size: 0.9rem; margin-bottom: 0.25rem;">Account Number</div>
                        <div style="font-weight: bold; color: #333; font-size: 1.1rem;">0123456789012345</div>
                    </div>
                    
                    <div>
                        <div style="color: #666; font-size: 0.9rem; margin-bottom: 0.25rem;">Account Type</div>
                        <div style="font-weight: bold; color: #333; font-size: 1.1rem;">Savings</div>
                    </div>
                    
                    <div>
                        <div style="color: #666; font-size: 0.9rem; margin-bottom: 0.25rem;">Branch</div>
                        <div style="font-weight: bold; color: #333; font-size: 1.1rem;">Kathmandu Main Branch</div>
                    </div>
                </div>
            </div>
            
            <div style="background: #FFF3CD; padding: 1rem; border-radius: 5px; border-left: 4px solid #FFC107;">
                <h4 style="color: #856404; margin: 0 0 0.5rem 0;">⚠️ Important Instructions:</h4>
                <ul style="color: #856404; margin: 0; padding-left: 1.5rem; line-height: 1.8;">
                    <li>Transfer the exact amount: <?php echo format_price($order['total_amount']); ?></li>
                    <li>Include your Order Number (#<?php echo str_pad($order['order_id'], 6, '0', STR_PAD_LEFT); ?>) in the transfer description</li>
                    <li>Save your transaction receipt/reference number</li>
                    <li>Complete the confirmation form below after transfer</li>
                    <li>Your order will be processed after payment verification</li>
                </ul>
            </div>
        </div>
        
        <!-- Payment Confirmation Form -->
        <div style="background: white; padding: 2rem; border-radius: 10px; box-shadow: 0 5px 20px rgba(0,0,0,0.1); margin-bottom: 2rem;">
            <h2 style="color: #8B4513; margin-bottom: 1.5rem;">✅ Confirm Payment</h2>
            
            <form method="POST" action="">
                <div class="form-group">
                    <label for="transaction_ref" style="display: block; margin-bottom: 0.5rem; font-weight: bold; color: #8B4513;">
                        Transaction Reference Number *
                    </label>
                    <input type="text" 
                           id="transaction_ref" 
                           name="transaction_ref" 
                           class="form-control" 
                           placeholder="Enter your bank transaction reference number"
                           required
                           style="width: 100%; padding: 0.75rem; border: 1px solid #ddd; border-radius: 5px; font-size: 1rem;">
                    <p style="color: #666; font-size: 0.85rem; margin-top: 0.5rem;">
                        This is the reference number from your bank transfer receipt
                    </p>
                    <?php if (isset($errors['transaction_ref'])): ?>
                        <span class="error-message" style="color: #DC3545; font-size: 0.85rem;"><?php echo $errors['transaction_ref']; ?></span>
                    <?php endif; ?>
                </div>
                
                <div style="background: #F8F9FA; padding: 1rem; border-radius: 5px; margin-bottom: 1.5rem;">
                    <p style="color: #666; margin: 0; font-size: 0.9rem;">
                        <strong>Note:</strong> After submitting, your order will be marked as "Pending Verification". We will verify your payment and process your order within 24-48 hours.
                    </p>
                </div>
                
                <div style="display: flex; gap: 1rem;">
                    <button type="submit" class="btn btn-primary" style="flex: 1; padding: 1rem 2rem;">
                        ✅ I Have Made the Payment
                    </button>
                    <a href="dashboard.php" class="btn btn-secondary" style="flex: 1; padding: 1rem 2rem; text-align: center;">
                        Cancel
                    </a>
                </div>
            </form>
        </div>
        
        <!-- Contact Information -->
        <div style="background: #FFF8DC; padding: 1.5rem; border-radius: 10px; box-shadow: 0 5px 20px rgba(0,0,0,0.1);">
            <h3 style="color: #8B4513; margin-bottom: 1rem;">📞 Need Help?</h3>
            <p style="color: #666; margin-bottom: 1rem;">If you have any questions about the payment process:</p>
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
    text-align: center;
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

.form-control {
    width: 100%;
    padding: 0.75rem;
    border: 1px solid #ddd;
    border-radius: 5px;
    font-size: 1rem;
    font-family: 'Georgia', serif;
}

.error-message {
    color: #DC3545;
    font-size: 0.85rem;
    margin-top: 0.25rem;
}

@media (max-width: 768px) {
    .container > div > div {
        padding: 1rem;
    }
}
</style>

<?php
// Include footer
require_once 'includes/footer.php';
?>
