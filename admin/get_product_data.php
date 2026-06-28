<?php
/**
 * Get Product Data API
 * ARTNEPAL E-commerce Website
 * 
 * This script returns product data for admin editing
 */

// Start session
session_start();

// Include database configuration
require_once '../config/database.php';

// Check if admin is logged in
if (!is_admin_logged_in()) {
    header('Content-Type: application/json');
    echo json_encode(['success' => false, 'message' => 'Unauthorized access.']);
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
$stmt = prepared_query("SELECT * FROM products WHERE product_id = ?", "i", [$product_id]);
$result = mysqli_stmt_get_result($stmt);
$product = mysqli_fetch_assoc($result);

if (!$product) {
    header('Content-Type: application/json');
    echo json_encode(['success' => false, 'message' => 'Product not found.']);
    exit();
}

// Format response
$response = [
    'success' => true,
    'product' => [
        'product_id' => $product['product_id'],
        'category_id' => $product['category_id'],
        'product_name' => htmlspecialchars($product['product_name']),
        'description' => htmlspecialchars($product['description']),
        'price' => $product['price'],
        'stock_quantity' => $product['stock_quantity'],
        'created_at' => $product['created_at']
    ]
];

// Send JSON response
header('Content-Type: application/json');
echo json_encode($response);
exit();
?>
