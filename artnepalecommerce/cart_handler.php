<?php
/**
 * Cart Handler Script
 * ARTNEPAL E-commerce Website
 * 
 * This script handles cart operations via AJAX requests
 */

// Start session
session_start();

// Include database configuration
require_once 'config/database.php';

// Check if user is logged in
if (!is_logged_in()) {
    header('Content-Type: application/json');
    echo json_encode(['success' => false, 'message' => 'Please login to manage your cart.']);
    exit();
}

// Get request data
$action = $_POST['action'] ?? '';
$product_id = isset($_POST['product_id']) ? (int)$_POST['product_id'] : 0;
$user_id = $_SESSION['user_id'];

// Validate product ID
if ($product_id <= 0) {
    header('Content-Type: application/json');
    echo json_encode(['success' => false, 'message' => 'Invalid product.']);
    exit();
}

// Check if product exists and get stock
$stmt = prepared_query("SELECT product_name, stock_quantity, price FROM products WHERE product_id = ?", "i", [$product_id]);
$result = mysqli_stmt_get_result($stmt);
$product = mysqli_fetch_assoc($result);

if (!$product) {
    header('Content-Type: application/json');
    echo json_encode(['success' => false, 'message' => 'Product not found.']);
    exit();
}

$response = ['success' => false, 'message' => '', 'cart_count' => 0];

switch ($action) {
    case 'add':
        // Check if product is in stock
        if ($product['stock_quantity'] <= 0) {
            $response['message'] = 'Product is out of stock.';
            break;
        }
        
        // Check if item already exists in cart
        $stmt = prepared_query("SELECT quantity FROM cart WHERE user_id = ? AND product_id = ?", "ii", [$user_id, $product_id]);
        $result = mysqli_stmt_get_result($stmt);
        $existing_item = mysqli_fetch_assoc($result);
        
        if ($existing_item) {
            // Update quantity
            $new_quantity = $existing_item['quantity'] + 1;
            
            // Check if enough stock
            if ($new_quantity > $product['stock_quantity']) {
                $response['message'] = 'Not enough stock available. Only ' . $product['stock_quantity'] . ' items available.';
                break;
            }
            
            $stmt = prepared_query("UPDATE cart SET quantity = ?, updated_at = CURRENT_TIMESTAMP WHERE user_id = ? AND product_id = ?", "iii", [$new_quantity, $user_id, $product_id]);
        } else {
            // Add new item to cart
            $stmt = prepared_query("INSERT INTO cart (user_id, product_id, quantity) VALUES (?, ?, 1)", "ii", [$user_id, $product_id]);
        }
        
        if ($stmt) {
            $response['success'] = true;
            $response['message'] = $product['product_name'] . ' added to cart successfully!';
        } else {
            $response['message'] = 'Failed to add product to cart.';
        }
        break;
        
    case 'increase':
        // Check if item exists in cart
        $stmt = prepared_query("SELECT quantity FROM cart WHERE user_id = ? AND product_id = ?", "ii", [$user_id, $product_id]);
        $result = mysqli_stmt_get_result($stmt);
        $existing_item = mysqli_fetch_assoc($result);
        
        if (!$existing_item) {
            $response['message'] = 'Item not found in cart.';
            break;
        }
        
        $new_quantity = $existing_item['quantity'] + 1;
        
        // Check if enough stock
        if ($new_quantity > $product['stock_quantity']) {
            $response['message'] = 'Not enough stock available. Only ' . $product['stock_quantity'] . ' items available.';
            break;
        }
        
        $stmt = prepared_query("UPDATE cart SET quantity = ?, updated_at = CURRENT_TIMESTAMP WHERE user_id = ? AND product_id = ?", "iii", [$new_quantity, $user_id, $product_id]);
        
        if ($stmt) {
            $response['success'] = true;
            $response['message'] = 'Quantity updated successfully!';
        } else {
            $response['message'] = 'Failed to update quantity.';
        }
        break;
        
    case 'decrease':
        // Check if item exists in cart
        $stmt = prepared_query("SELECT quantity FROM cart WHERE user_id = ? AND product_id = ?", "ii", [$user_id, $product_id]);
        $result = mysqli_stmt_get_result($stmt);
        $existing_item = mysqli_fetch_assoc($result);
        
        if (!$existing_item) {
            $response['message'] = 'Item not found in cart.';
            break;
        }
        
        $new_quantity = $existing_item['quantity'] - 1;
        
        if ($new_quantity <= 0) {
            // Remove item from cart
            $stmt = prepared_query("DELETE FROM cart WHERE user_id = ? AND product_id = ?", "ii", [$user_id, $product_id]);
            $response['message'] = 'Item removed from cart!';
        } else {
            // Update quantity
            $stmt = prepared_query("UPDATE cart SET quantity = ?, updated_at = CURRENT_TIMESTAMP WHERE user_id = ? AND product_id = ?", "iii", [$new_quantity, $user_id, $product_id]);
            $response['message'] = 'Quantity updated successfully!';
        }
        
        if ($stmt) {
            $response['success'] = true;
        } else {
            $response['message'] = 'Failed to update quantity.';
        }
        break;
        
    case 'remove':
        // Remove item from cart
        $stmt = prepared_query("DELETE FROM cart WHERE user_id = ? AND product_id = ?", "ii", [$user_id, $product_id]);
        
        if ($stmt) {
            $response['success'] = true;
            $response['message'] = 'Item removed from cart!';
        } else {
            $response['message'] = 'Failed to remove item from cart.';
        }
        break;
        
    default:
        $response['message'] = 'Invalid action.';
        break;
}

// Get updated cart count
if ($response['success']) {
    $stmt = prepared_query("SELECT SUM(quantity) as total FROM cart WHERE user_id = ?", "i", [$user_id]);
    $result = mysqli_stmt_get_result($stmt);
    $row = mysqli_fetch_assoc($result);
    $response['cart_count'] = $row['total'] ?? 0;
}

// Send JSON response
header('Content-Type: application/json');
echo json_encode($response);
exit();
?>
