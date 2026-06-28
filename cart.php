<?php
/**
 * Shopping Cart Page
 * ARTNEPAL E-commerce Website
 * 
 * This page displays and manages the user's shopping cart
 */

// Include header
require_once 'includes/header.php';

// Check if user is logged in
if (!is_logged_in()) {
    $_SESSION['error_message'] = 'Please login to view your cart.';
    redirect('login.php');
}

// Get user ID
$user_id = $_SESSION['user_id'];

// Get cart items
$cart_items = [];
$total_amount = 0;
$total_items = 0;

$stmt = prepared_query("
    SELECT c.*, p.product_name, p.price, p.product_image, p.stock_quantity, p.category_id,
           cat.category_name,
           (c.quantity * p.price) as subtotal
    FROM cart c
    JOIN products p ON c.product_id = p.product_id
    JOIN categories cat ON p.category_id = cat.category_id
    WHERE c.user_id = ?
    ORDER BY c.created_at DESC
", "i", [$user_id]);

$result = mysqli_stmt_get_result($stmt);
while ($row = mysqli_fetch_assoc($result)) {
    $cart_items[] = $row;
    $total_amount += $row['subtotal'];
    $total_items += $row['quantity'];
}

// Handle cart update from POST requests
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_cart'])) {
    foreach ($_POST['quantities'] as $product_id => $quantity) {
        $product_id = (int)$product_id;
        $quantity = (int)$quantity;
        
        if ($quantity <= 0) {
            // Remove item from cart
            prepared_query("DELETE FROM cart WHERE user_id = ? AND product_id = ?", "ii", [$user_id, $product_id]);
        } else {
            // Check stock availability
            $stmt = prepared_query("SELECT stock_quantity FROM products WHERE product_id = ?", "i", [$product_id]);
            $result = mysqli_stmt_get_result($stmt);
            $product = mysqli_fetch_assoc($result);
            
            if ($product && $quantity <= $product['stock_quantity']) {
                // Update quantity
                prepared_query("UPDATE cart SET quantity = ?, updated_at = CURRENT_TIMESTAMP WHERE user_id = ? AND product_id = ?", "iii", [$quantity, $user_id, $product_id]);
            } else {
                $_SESSION['error_message'] = 'Not enough stock for some items. Quantity has been adjusted.';
            }
        }
    }
    
    $_SESSION['success_message'] = 'Cart updated successfully!';
    redirect('cart.php');
}
?>

<div class="container">
    <div class="cart-container">
        <h1 style="color: #8B4513; margin-bottom: 2rem; text-align: center;">Shopping Cart</h1>
        
        <?php if (empty($cart_items)): ?>
            <!-- Empty Cart -->
            <div style="text-align: center; padding: 3rem;">
                <div style="font-size: 4rem; margin-bottom: 1rem;">🛒</div>
                <h2 style="color: #8B4513; margin-bottom: 1rem;">Your cart is empty</h2>
                <p style="color: #666; margin-bottom: 2rem;">Start shopping to add items to your cart!</p>
                <a href="products.php" class="btn btn-primary" style="font-size: 1.1rem; padding: 1rem 2rem;">
                    Continue Shopping
                </a>
            </div>
        <?php else: ?>
            <!-- Cart with Items -->
            <form method="POST" action="cart.php" id="cartForm">
                <div style="margin-bottom: 2rem;">
                    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 1rem;">
                        <h3 style="color: #8B4513; margin: 0;">Cart Items (<?php echo $total_items; ?>)</h3>
                        <button type="submit" name="update_cart" class="btn btn-secondary">
                            🔄 Update Cart
                        </button>
                    </div>
                </div>
                
                <!-- Cart Items List -->
                <div style="margin-bottom: 2rem;">
                    <?php foreach ($cart_items as $item): ?>
                        <div class="cart-item">
                            <img src="assets/images/products/<?php echo htmlspecialchars($item['product_image']); ?>" 
                                 alt="<?php echo htmlspecialchars($item['product_name']); ?>" 
                                 class="cart-item-image"
                                 onerror="this.src='assets/images/placeholder.jpg';">
                            
                            <div class="cart-item-details">
                                <div style="display: flex; justify-content: space-between; align-items: start; margin-bottom: 0.5rem;">
                                    <h4 style="color: #8B4513; margin: 0;"><?php echo htmlspecialchars($item['product_name']); ?></h4>
                                    <button type="button" 
                                            class="btn btn-danger remove-from-cart" 
                                            data-product-id="<?php echo $item['product_id']; ?>"
                                            style="padding: 0.25rem 0.5rem; font-size: 0.8rem;">
                                        🗑️ Remove
                                    </button>
                                </div>
                                
                                <p style="color: #666; font-size: 0.9rem; margin-bottom: 0.5rem;">
                                    Category: <?php echo htmlspecialchars($item['category_name']); ?>
                                </p>
                                
                                <div style="display: flex; justify-content: space-between; align-items: center;">
                                    <div class="quantity-controls">
                                        <button type="button" 
                                                class="quantity-btn" 
                                                data-action="decrease"
                                                data-product-id="<?php echo $item['product_id']; ?>">
                                            -
                                        </button>
                                        <input type="number" 
                                               name="quantities[<?php echo $item['product_id']; ?>]" 
                                               value="<?php echo $item['quantity']; ?>" 
                                               min="1" 
                                               max="<?php echo $item['stock_quantity']; ?>"
                                               class="form-control" 
                                               style="width: 60px; text-align: center; margin: 0 0.5rem;">
                                        <button type="button" 
                                                class="quantity-btn" 
                                                data-action="increase"
                                                data-product-id="<?php echo $item['product_id']; ?>">
                                            +
                                        </button>
                                    </div>
                                    
                                    <div style="text-align: right;">
                                        <div style="color: #666; font-size: 0.9rem;">
                                            <?php echo format_price($item['price']); ?> × <?php echo $item['quantity']; ?>
                                        </div>
                                        <div class="cart-item-price">
                                            <?php echo format_price($item['subtotal']); ?>
                                        </div>
                                    </div>
                                </div>
                                
                                <?php if ($item['quantity'] > $item['stock_quantity']): ?>
                                    <div style="color: #DC143C; font-size: 0.85rem; margin-top: 0.5rem;">
                                        ⚠️ Only <?php echo $item['stock_quantity']; ?> available in stock
                                    </div>
                                <?php endif; ?>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
                
                <!-- Cart Summary -->
                <div class="cart-summary">
                    <h3 style="color: #8B4513; margin-bottom: 1rem;">Order Summary</h3>
                    
                    <div class="summary-row">
                        <span>Subtotal (<?php echo $total_items; ?> items):</span>
                        <span><?php echo format_price($total_amount); ?></span>
                    </div>
                    
                    <div class="summary-row">
                        <span>Shipping:</span>
                        <span>Free</span>
                    </div>
                    
                    <div class="summary-row">
                        <span>Tax:</span>
                        <span>Included</span>
                    </div>
                    
                    <div class="summary-row total">
                        <span>Total:</span>
                        <span><?php echo format_price($total_amount); ?></span>
                    </div>
                    
                    <div style="margin-top: 2rem; display: flex; gap: 1rem;">
                        <a href="products.php" class="btn btn-secondary" style="flex: 1;">
                            🛍️ Continue Shopping
                        </a>
                        <a href="checkout.php" class="btn btn-primary" style="flex: 1;">
                            💳 Proceed to Checkout
                        </a>
                    </div>
                </div>
            </form>
        <?php endif; ?>
    </div>
</div>

<style>
.cart-container {
    background: white;
    padding: 2rem;
    border-radius: 10px;
    box-shadow: 0 5px 20px rgba(0,0,0,0.1);
    margin: 2rem auto;
    max-width: 1000px;
}

.cart-item {
    display: flex;
    align-items: flex-start;
    padding: 1.5rem;
    border-bottom: 1px solid #DEB887;
    gap: 1rem;
}

.cart-item:last-child {
    border-bottom: none;
}

.cart-item-image {
    width: 100px;
    height: 100px;
    object-fit: cover;
    border-radius: 5px;
    flex-shrink: 0;
}

.cart-item-details {
    flex: 1;
    min-width: 0;
}

.cart-item-name {
    font-weight: bold;
    color: #8B4513;
    margin-bottom: 0.5rem;
}

.cart-item-price {
    color: #DC143C;
    font-weight: bold;
    font-size: 1.1rem;
}

.quantity-controls {
    display: flex;
    align-items: center;
    gap: 0.5rem;
}

.quantity-btn {
    width: 30px;
    height: 30px;
    border: 1px solid #8B4513;
    background: white;
    color: #8B4513;
    cursor: pointer;
    border-radius: 3px;
    font-weight: bold;
}

.quantity-btn:hover {
    background: #8B4513;
    color: white;
}

.cart-summary {
    margin-top: 2rem;
    padding-top: 2rem;
    border-top: 2px solid #8B4513;
}

.summary-row {
    display: flex;
    justify-content: space-between;
    margin-bottom: 0.75rem;
    color: #666;
}

.summary-row.total {
    font-size: 1.2rem;
    font-weight: bold;
    color: #8B4513;
    border-top: 1px solid #DEB887;
    padding-top: 0.75rem;
    margin-top: 1rem;
}

@media (max-width: 768px) {
    .cart-container {
        margin: 1rem;
        padding: 1rem;
    }
    
    .cart-item {
        flex-direction: column;
        text-align: center;
    }
    
    .cart-item-image {
        margin: 0 auto 1rem;
    }
    
    .quantity-controls {
        justify-content: center;
        margin: 1rem 0;
    }
    
    .cart-item-details > div:first-child {
        flex-direction: column;
        gap: 1rem;
    }
}

@media (max-width: 480px) {
    .cart-summary > div:last-child {
        flex-direction: column;
    }
}
</style>

<?php
// Include footer
require_once 'includes/footer.php';
?>
