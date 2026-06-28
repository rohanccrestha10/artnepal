<?php
/**
 * eSewa Payment Failure Page
 * ARTNEPAL E-commerce Website
 * 
 * This page displays payment failure message
 */

// Start session
session_start();

// Include header
require_once '../includes/header.php';

// Get error message from session
$error_message = $_SESSION['error_message'] ?? 'Payment failed. Please try again.';

// Get payment response parameters (if available)
$oid = $_GET['oid'] ?? null;
$amt = $_GET['amt'] ?? null;

// Clear error message from session
unset($_SESSION['error_message']);
?>

<div class="container">
    <div style="max-width: 800px; margin: 2rem auto;">
        <!-- Failure Message -->
        <div style="text-align: center; margin-bottom: 3rem;">
            <div style="font-size: 4rem; color: #DC3545; margin-bottom: 1rem;">❌</div>
            <h1 style="color: #DC3545; margin-bottom: 1rem;">Payment Failed</h1>
            <p style="color: #666; font-size: 1.1rem;"><?php echo htmlspecialchars($error_message); ?></p>
        </div>
        
        <!-- Payment Details (if available) -->
        <?php if ($oid && $amt): ?>
        <div style="background: white; padding: 2rem; border-radius: 10px; box-shadow: 0 5px 20px rgba(0,0,0,0.1); margin-bottom: 2rem;">
            <h2 style="color: #8B4513; margin-bottom: 1.5rem;">Payment Details</h2>
            
            <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 1.5rem; margin-bottom: 2rem;">
                <div>
                    <div style="color: #666; font-size: 0.9rem; margin-bottom: 0.25rem;">Transaction ID</div>
                    <div style="font-weight: bold; color: #333;"><?php echo htmlspecialchars($oid); ?></div>
                </div>
                
                <div>
                    <div style="color: #666; font-size: 0.9rem; margin-bottom: 0.25rem;">Amount</div>
                    <div style="font-weight: bold; color: #DC143C; font-size: 1.1rem;"><?php echo format_price($amt); ?></div>
                </div>
            </div>
        </div>
        <?php endif; ?>
        
        <!-- Help Information -->
        <div style="background: #FFF8DC; padding: 2rem; border-radius: 10px; box-shadow: 0 5px 20px rgba(0,0,0,0.1); margin-bottom: 2rem;">
            <h2 style="color: #8B4513; margin-bottom: 1.5rem;">What to do next?</h2>
            
            <ul style="color: #666; line-height: 1.8; margin-bottom: 2rem;">
                <li>Check if you have sufficient balance in your eSewa account</li>
                <li>Ensure your internet connection is stable</li>
                <li>Try the payment again</li>
                <li>If the problem persists, contact our support team</li>
            </ul>
            
            <div style="background: #F8F9FA; padding: 1.5rem; border-radius: 5px; margin-bottom: 2rem;">
                <h3 style="color: #8B4513; margin-bottom: 1rem;">📞 Contact Support</h3>
                <p style="color: #666; margin-bottom: 1rem;">If you continue to face issues, please contact us:</p>
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
            <a href="../checkout.php" class="btn btn-primary" style="padding: 1rem 2rem;">
                Try Payment Again
            </a>
            <a href="../cart.php" class="btn btn-secondary" style="padding: 1rem 2rem;">
                View Cart
            </a>
            <a href="../dashboard.php" class="btn btn-secondary" style="padding: 1rem 2rem;">
                Go to Dashboard
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
// Include footer
require_once '../includes/footer.php';
?>
