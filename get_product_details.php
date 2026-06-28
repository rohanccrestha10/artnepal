<?php
/**
 * Product Details API
 * ARTNEPAL E-commerce Website
 * 
 * This script returns product details in JSON format for AJAX requests
 */

// Start session
session_start();

// Include database configuration
require_once 'config/database.php';

// Check if user is logged in
if (!is_logged_in()) {
    header('Content-Type: application/json');
    echo json_encode(['success' => false, 'message' => 'Please login to view product details.']);
    exit();
}

// Get product ID
$product_id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

if ($product_id <= 0) {
    header('Content-Type: application/json');
    echo json_encode(['success' => false, 'message' => 'Invalid product ID.']);
    exit();
}

// Get product details
$stmt = prepared_query("
    SELECT p.*, c.category_name 
    FROM products p 
    JOIN categories c ON p.category_id = c.category_id 
    WHERE p.product_id = ?
", "i", [$product_id]);

$result = mysqli_stmt_get_result($stmt);
$product = mysqli_fetch_assoc($result);

if (!$product) {
    header('Content-Type: application/json');
    echo json_encode(['success' => false, 'message' => 'Product not found.']);
    exit();
}

// Format the response
$response = [
    'success' => true,
    'product' => [
        'product_id' => $product['product_id'],
        'product_name' => htmlspecialchars($product['product_name']),
        'description' => htmlspecialchars($product['description']),
        'price' => $product['price'],
        'formatted_price' => format_price($product['price']),
        'stock_quantity' => $product['stock_quantity'],
        'category_name' => htmlspecialchars($product['category_name']),
        'product_image' => htmlspecialchars($product['product_image']),
        'created_at' => $product['created_at']
    ]
];

// Send JSON response
header('Content-Type: application/json');
echo json_encode($response);
exit();
?>
