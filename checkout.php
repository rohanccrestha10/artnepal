<?php
/**
 * Checkout Page
 * ARTNEPAL E-commerce Website
 * 
 * This page handles the checkout process and order placement
 */

// Include header
require_once 'includes/header.php';

// Check if user is logged in
if (!is_logged_in()) {
    $_SESSION['error_message'] = 'Please login to proceed with checkout.';
    redirect('login.php');
}

// Get user ID
$user_id = $_SESSION['user_id'];

// Get user information
$stmt = prepared_query("SELECT full_name, email, phone_number, address FROM users WHERE user_id = ?", "i", [$user_id]);
$user = mysqli_fetch_assoc(mysqli_stmt_get_result($stmt));

// Get cart items
$cart_items = [];
$total_amount = 0;
$total_items = 0;

$stmt = prepared_query("
    SELECT c.*, p.product_name, p.price, p.stock_quantity,
           (c.quantity * p.price) as subtotal
    FROM cart c
    JOIN products p ON c.product_id = p.product_id
    WHERE c.user_id = ?
", "i", [$user_id]);

$result = mysqli_stmt_get_result($stmt);
while ($row = mysqli_fetch_assoc($result)) {
    $cart_items[] = $row;
    $total_amount += $row['subtotal'];
    $total_items += $row['quantity'];
}

// Redirect to cart if empty
if (empty($cart_items)) {
    $_SESSION['error_message'] = 'Your cart is empty. Please add items before checkout.';
    redirect('cart.php');
}

// Check stock availability
$stock_issues = [];
foreach ($cart_items as $item) {
    if ($item['quantity'] > $item['stock_quantity']) {
        $stock_issues[] = $item['product_name'] . ' - Only ' . $item['stock_quantity'] . ' available (you have ' . $item['quantity'] . ' in cart)';
    }
}

// Initialize variables
$full_name = $user['full_name'];
$email = $user['email'];
$phone_number = $user['phone_number'];
$address = $user['address'];
$errors = [];

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Get form data
    $full_name = sanitize_input($_POST['full_name'] ?? '');
    $email = sanitize_input($_POST['email'] ?? '');
    $phone_number = sanitize_input($_POST['phone_number'] ?? '');
    $address = sanitize_input($_POST['address'] ?? '');
    $payment_method = sanitize_input($_POST['payment_method'] ?? 'cod');
    
    // Validation
    if (empty($full_name)) {
        $errors['full_name'] = 'Full name is required';
    } elseif (!preg_match('/^[A-Za-z\s]+$/', $full_name)) {
        $errors['full_name'] = 'Full name can only contain alphabets and spaces';
    } elseif (strlen($full_name) < 2) {
        $errors['full_name'] = 'Full name must be at least 2 characters long';
    }
    
    if (empty($email)) {
        $errors['email'] = 'Email is required';
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errors['email'] = 'Please enter a valid email address';
    }
    
    if (empty($phone_number)) {
        $errors['phone_number'] = 'Phone number is required';
    } elseif (!preg_match('/^[0-9]{10}$/', $phone_number)) {
        $errors['phone_number'] = 'Phone number must contain exactly 10 digits';
    }
    
    if (empty($address)) {
        $errors['address'] = 'Address is required';
    } elseif (!preg_match('/^[A-Za-z\s\.,#\-\/]+$/', $address)) {
        $errors['address'] = 'Address can only contain alphabets, spaces, and basic punctuation (.,#-/)';
    }
    
    // Check stock again before placing order
    if (empty($errors)) {
        $current_stock_issues = [];
        foreach ($cart_items as $item) {
            $stmt = prepared_query("SELECT stock_quantity FROM products WHERE product_id = ?", "i", [$item['product_id']]);
            $result = mysqli_stmt_get_result($stmt);
            $product = mysqli_fetch_assoc($result);
            
            if ($item['quantity'] > $product['stock_quantity']) {
                $current_stock_issues[] = $item['product_name'] . ' - Only ' . $product['stock_quantity'] . ' available';
            }
        }
        
        if (!empty($current_stock_issues)) {
            $errors['stock'] = 'Some items are no longer available in the requested quantity: ' . implode(', ', $current_stock_issues);
        }
    }
    
    // If no errors, place the order
    if (empty($errors)) {
        // Start transaction
        mysqli_begin_transaction($conn);
        
        try {
            // Determine order status based on payment method
            $order_status = ($payment_method === 'esewa') ? 'pending' : 'pending';
            
            // Try inserting with payment columns first, fall back to without
            $stmt = @prepared_query("
                INSERT INTO orders (user_id, full_name, email, phone_number, address, total_amount, order_status, payment_method, payment_status) 
                VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)
            ", "issssdsss", [$user_id, $full_name, $email, $phone_number, $address, $total_amount, $order_status, $payment_method, 'pending']);
            
            if (!$stmt) {
                // Fallback: insert without payment columns if they don't exist yet
                $stmt = prepared_query("
                    INSERT INTO orders (user_id, full_name, email, phone_number, address, total_amount, order_status) 
                    VALUES (?, ?, ?, ?, ?, ?, ?)
                ", "issssds", [$user_id, $full_name, $email, $phone_number, $address, $total_amount, $order_status]);
            }
            
            $order_id = mysqli_insert_id($conn);
            
            // Add order items
            foreach ($cart_items as $item) {
                $stmt = prepared_query("
                    INSERT INTO order_items (order_id, product_id, quantity, price_per_item, subtotal) 
                    VALUES (?, ?, ?, ?, ?)
                ", "iiiid", [$order_id, $item['product_id'], $item['quantity'], $item['price'], $item['subtotal']]);
                
                // Update product stock
                $stmt = prepared_query("
                    UPDATE products SET stock_quantity = stock_quantity - ? WHERE product_id = ?
                ", "ii", [$item['quantity'], $item['product_id']]);
            }
            
            // Commit transaction
            mysqli_commit($conn);
            
            // Handle payment method
            if ($payment_method === 'esewa') {
                // Redirect to eSewa payment
                redirect('esewa/payment.php?order_id=' . $order_id);
            } else {
                // COD - Clear cart and redirect to success
                $stmt = prepared_query("DELETE FROM cart WHERE user_id = ?", "i", [$user_id]);
                
                // Store COD payment record
                require_once 'khalti/config.php';
                $transaction_uuid = generate_khalti_transaction_uuid();
                store_khalti_payment_details($order_id, $full_name, $transaction_uuid, $total_amount, 'cod', 'success');
                
                $_SESSION['success_message'] = 'Order placed successfully! Your order ID is #' . str_pad($order_id, 6, '0', STR_PAD_LEFT);
                redirect('order_success.php?id=' . $order_id);
            }
            
        } catch (Exception $e) {
            // Rollback transaction
            mysqli_rollback($conn);
            $errors['general'] = 'Failed to place order: ' . $e->getMessage();
        }
    }
}
?>

<div class="container">
    <div class="checkout-container">
        <h1 style="color: #8B4513; margin-bottom: 2rem; text-align: center;">Checkout</h1>
        
        <?php if (!empty($stock_issues)): ?>
            <div style="background: #FFF3CD; border: 1px solid #FFEAA7; color: #856404; padding: 1rem; border-radius: 5px; margin-bottom: 2rem;">
                <h4 style="margin: 0 0 0.5rem 0;">⚠️ Stock Availability Issues</h4>
                <ul style="margin: 0; padding-left: 1.5rem;">
                    <?php foreach ($stock_issues as $issue): ?>
                        <li><?php echo htmlspecialchars($issue); ?></li>
                    <?php endforeach; ?>
                </ul>
                <p style="margin: 1rem 0 0 0;">
                    <a href="cart.php" class="btn btn-secondary" style="font-size: 0.9rem;">Update Cart Quantities</a>
                </p>
            </div>
        <?php endif; ?>
        
        <div style="display: grid; grid-template-columns: 1fr 350px; gap: 2rem;">
            <!-- Checkout Form -->
            <div>
                <form id="checkoutForm" method="POST" action="checkout.php">
                    <h2 style="color: #8B4513; margin-bottom: 1.5rem;">Shipping Information</h2>
                    
                    <?php if (isset($errors['general'])): ?>
                        <div class="error-message" style="margin-bottom: 1rem;">
                            <?php echo $errors['general']; ?>
                        </div>
                    <?php endif; ?>
                    
                    <?php if (isset($errors['stock'])): ?>
                        <div class="error-message" style="margin-bottom: 1rem;">
                            <?php echo $errors['stock']; ?>
                        </div>
                    <?php endif; ?>
                    
                    <div class="form-group">
                        <label for="full_name">Full Name *</label>
                        <input type="text" 
                               id="full_name" 
                               name="full_name" 
                               class="form-control" 
                               value="<?php echo htmlspecialchars($full_name); ?>"
                               placeholder="Enter your full name"
                               required>
                        <?php if (isset($errors['full_name'])): ?>
                            <span class="error-message"><?php echo $errors['full_name']; ?></span>
                        <?php endif; ?>
                    </div>
                    
                    <div class="form-group">
                        <label for="email">Email Address *</label>
                        <input type="email" 
                               id="email" 
                               name="email" 
                               class="form-control" 
                               value="<?php echo htmlspecialchars($email); ?>"
                               placeholder="Enter your email address"
                               required>
                        <?php if (isset($errors['email'])): ?>
                            <span class="error-message"><?php echo $errors['email']; ?></span>
                        <?php endif; ?>
                    </div>
                    
                    <div class="form-group">
                        <label for="phone_number">Phone Number *</label>
                        <input type="tel" 
                               id="phone_number" 
                               name="phone_number" 
                               class="form-control" 
                               value="<?php echo htmlspecialchars($phone_number); ?>"
                               placeholder="Enter 10-digit phone number"
                               maxlength="10"
                               required>
                        <?php if (isset($errors['phone_number'])): ?>
                            <span class="error-message"><?php echo $errors['phone_number']; ?></span>
                        <?php endif; ?>
                    </div>
                    
                    <div class="form-group">
                        <label for="address">Delivery Address *</label>
                        <textarea id="address" 
                                  name="address" 
                                  class="form-control" 
                                  rows="3"
                                  placeholder="Enter your complete delivery address (alphabets only)"
                                  required><?php echo htmlspecialchars($address); ?></textarea>
                        <?php if (isset($errors['address'])): ?>
                            <span class="error-message"><?php echo $errors['address']; ?></span>
                        <?php endif; ?>
                    </div>
                    
                    <div style="background: #F8F9FA; padding: 1.5rem; border-radius: 5px; margin-bottom: 2rem;">
                        <h3 style="color: #8B4513; margin-bottom: 1rem;">Order Notes</h3>
                        <textarea name="order_notes" 
                                  class="form-control" 
                                  rows="3"
                                  placeholder="Any special instructions for delivery (optional)"></textarea>
                    </div>
                    
                    <div style="background: #F8F9FA; padding: 1.5rem; border-radius: 5px; margin-bottom: 2rem;">
                        <h3 style="color: #8B4513; margin-bottom: 1rem;">💳 Payment Method</h3>
                        
                        <div class="form-group">
                            <label style="display: block; margin-bottom: 1rem; font-weight: bold; color: #8B4513;">Select Payment Method *</label>
                            
                            <div style="margin-bottom: 1rem;">
                                <input type="radio" 
                                       id="payment_esewa" 
                                       name="payment_method" 
                                       value="esewa" 
                                       checked
                                       required>
                                <label for="payment_esewa" style="display: inline; margin-left: 0.5rem; font-weight: normal; cursor: pointer;">
                                    <span style="color: #60BB46; font-weight: bold;">eSewa</span> - Pay securely with eSewa
                                </label>
                            </div>
                            
                            <div style="margin-bottom: 1rem;">
                                <input type="radio" 
                                       id="payment_cod" 
                                       name="payment_method" 
                                       value="cod"
                                       required>
                                <label for="payment_cod" style="display: inline; margin-left: 0.5rem; font-weight: normal; cursor: pointer;">
                                    Cash on Delivery (COD) - Pay when you receive your order
                                </label>
                            </div>
                        </div>
                    </div>
                    
                    <div style="display: flex; gap: 1rem;">
                        <a href="cart.php" class="btn btn-secondary" style="flex: 1;">
                            ← Back to Cart
                        </a>
                        <button type="submit" class="btn btn-primary" style="flex: 1;">
                            Place Order →
                        </button>
                    </div>
                </form>
            </div>
            
            <!-- Order Summary -->
            <div>
                <div style="background: white; padding: 1.5rem; border-radius: 10px; box-shadow: 0 5px 15px rgba(0,0,0,0.1); position: sticky; top: 100px;">
                    <h3 style="color: #8B4513; margin-bottom: 1rem;">Order Summary</h3>
                    
                    <!-- Order Items -->
                    <div style="margin-bottom: 1rem;">
                        <?php foreach ($cart_items as $item): ?>
                            <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 0.5rem; padding-bottom: 0.5rem; border-bottom: 1px solid #F0F0F0;">
                                <div style="flex: 1;">
                                    <div style="font-weight: bold; color: #333; font-size: 0.9rem;"><?php echo htmlspecialchars($item['product_name']); ?></div>
                                    <div style="color: #666; font-size: 0.8rem;"><?php echo $item['quantity']; ?> × <?php echo format_price($item['price']); ?></div>
                                </div>
                                <div style="color: #DC143C; font-weight: bold;">
                                    <?php echo format_price($item['subtotal']); ?>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                    
                    <!-- Price Breakdown -->
                    <div style="border-top: 1px solid #DEB887; padding-top: 1rem;">
                        <div class="summary-row">
                            <span>Subtotal (<?php echo $total_items; ?> items):</span>
                            <span><?php echo format_price($total_amount); ?></span>
                        </div>
                        
                        <div class="summary-row">
                            <span>Shipping:</span>
                            <span style="color: #28A745;">Free</span>
                        </div>
                        
                        <div class="summary-row">
                            <span>Tax:</span>
                            <span>Included</span>
                        </div>
                        
                        <div class="summary-row total">
                            <span>Total:</span>
                            <span><?php echo format_price($total_amount); ?></span>
                        </div>
                    </div>
                    
                    <!-- Delivery Info -->
                    <div style="background: #FFF8DC; padding: 1rem; border-radius: 5px; margin-top: 1rem;">
                        <h4 style="color: #8B4513; margin-bottom: 0.5rem; font-size: 0.9rem;">📦 Delivery Information</h4>
                        <p style="color: #666; font-size: 0.8rem; margin: 0;">
                            Estimated delivery: 3-5 business days within Kathmandu Valley, 5-7 business days outside valley.
                        </p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<style>
.checkout-container {
    max-width: 1000px;
    margin: 2rem auto;
    padding: 0 1rem;
}

.form-group {
    margin-bottom: 1.5rem;
}

.form-group label {
    display: block;
    margin-bottom: 0.5rem;
    font-weight: bold;
    color: #8B4513;
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
    border-color: #8B4513;
    box-shadow: 0 0 5px rgba(139,69,19,0.3);
}

.form-control.error {
    border-color: #DC143C;
    box-shadow: 0 0 5px rgba(220,20,60,0.3);
}

.error-message {
    color: #DC143C;
    font-size: 0.875rem;
    margin-top: 0.25rem;
    display: block;
}

.summary-row {
    display: flex;
    justify-content: space-between;
    margin-bottom: 0.75rem;
    color: #666;
    font-size: 0.9rem;
}

.summary-row.total {
    font-size: 1.1rem;
    font-weight: bold;
    color: #8B4513;
    border-top: 1px solid #DEB887;
    padding-top: 0.75rem;
    margin-top: 0.75rem;
}

@media (max-width: 768px) {
    .checkout-container > div {
        grid-template-columns: 1fr;
    }
    
    .checkout-container > div > div:last-child {
        order: -1;
    }
}
</style>

<script>
// Initialize form validation when DOM is loaded
document.addEventListener('DOMContentLoaded', function() {
    const checkoutForm = document.getElementById('checkoutForm');
    if (checkoutForm) {
        checkoutForm.addEventListener('submit', validateCheckoutForm);
    }
});

function validateCheckoutForm(e) {
    e.preventDefault();
    
    const form = e.target;
    let isValid = true;
    
    // Clear previous errors
    const errorMessages = form.querySelectorAll('.error-message');
    errorMessages.forEach(msg => msg.remove());
    
    // Get form fields
    const fullName = form.querySelector('[name="full_name"]');
    const email = form.querySelector('[name="email"]');
    const phone = form.querySelector('[name="phone_number"]');
    const address = form.querySelector('[name="address"]');
    
    // Validate full name
    if (!validateName(fullName)) {
        isValid = false;
    }
    
    // Validate email
    if (!validateEmail(email)) {
        isValid = false;
    }
    
    // Validate phone
    if (!validatePhone(phone)) {
        isValid = false;
    }
    
    // Validate address
    if (!validateAddress(address)) {
        isValid = false;
    }
    
    if (isValid) {
        form.submit();
    }
}

function validateName(field) {
    const value = field.value.trim();
    
    if (value === '') {
        showFieldError(field, 'Full name is required');
        return false;
    }
    
    if (!/^[A-Za-z\s]+$/.test(value)) {
        showFieldError(field, 'Full name can only contain alphabets and spaces');
        return false;
    }
    
    if (value.length < 2) {
        showFieldError(field, 'Full name must be at least 2 characters long');
        return false;
    }
    
    clearFieldError(field);
    return true;
}

function validateEmail(field) {
    const value = field.value.trim();
    
    if (value === '') {
        showFieldError(field, 'Email is required');
        return false;
    }
    
    const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
    if (!emailRegex.test(value)) {
        showFieldError(field, 'Please enter a valid email address');
        return false;
    }
    
    clearFieldError(field);
    return true;
}

function validatePhone(field) {
    const value = field.value.trim();
    
    if (value === '') {
        showFieldError(field, 'Phone number is required');
        return false;
    }
    
    if (!/^[0-9]{10}$/.test(value)) {
        showFieldError(field, 'Phone number must be exactly 10 digits');
        return false;
    }
    
    clearFieldError(field);
    return true;
}

function validateAddress(field) {
    const value = field.value.trim();
    
    if (value === '') {
        showFieldError(field, 'Address is required');
        return false;
    }
    
    if (!/^[A-Za-z\s\.,#\-\/]+$/.test(value)) {
        showFieldError(field, 'Address can only contain alphabets, spaces, and basic punctuation');
        return false;
    }
    
    clearFieldError(field);
    return true;
}

function showFieldError(field, message) {
    clearFieldError(field);
    
    field.classList.add('error');
    
    const errorDiv = document.createElement('div');
    errorDiv.className = 'error-message';
    errorDiv.textContent = message;
    errorDiv.style.color = '#DC143C';
    errorDiv.style.fontSize = '0.875rem';
    errorDiv.style.marginTop = '0.25rem';
    errorDiv.style.display = 'block';
    
    field.parentNode.appendChild(errorDiv);
}

function clearFieldError(field) {
    field.classList.remove('error');
    
    const errorDiv = field.parentNode.querySelector('.error-message');
    if (errorDiv) {
        errorDiv.remove();
    }
}
</script>

<?php
// Include footer
require_once 'includes/footer.php';
?>
