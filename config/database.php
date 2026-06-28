<?php
/**
 * Database Configuration File
 * ARTNEPAL E-commerce Website
 * 
 * This file contains database connection settings
 * and establishes connection to MySQL database
 */

// Start output buffering to handle redirects
if (session_status() === PHP_SESSION_NONE) {
    ob_start();
}

// Database connection parameters
define('DB_HOST', '127.0.0.1');
define('DB_USER', 'root');
define('DB_PASS', '');
define('DB_NAME', 'artnepal_db');

// Error reporting for debugging
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Create database connection
$conn = mysqli_connect(DB_HOST, DB_USER, DB_PASS, DB_NAME);

// Check connection
if (!$conn) {
    die("Connection failed: " . mysqli_connect_error() . "<br>Please check your database configuration in config/database.php");
}

// Set charset to support Nepali characters
mysqli_set_charset($conn, "utf8");

/**
 * Function to execute prepared statements
 * @param string $sql - SQL query with placeholders
 * @param string $types - Type definitions (i=integer, s=string, d=double)
 * @param array $params - Array of parameters
 * @return mysqli_stmt|false
 */
function prepared_query($sql, $types = "", $params = []) {
    global $conn;
    
    $stmt = mysqli_prepare($conn, $sql);
    if (!$stmt) {
        return false;
    }
    
    if (!empty($types) && !empty($params)) {
        mysqli_stmt_bind_param($stmt, $types, ...$params);
    }
    
    mysqli_stmt_execute($stmt);
    return $stmt;
}

/**
 * Function to sanitize input data
 * @param string $data - Input data to sanitize
 * @return string - Sanitized data
 */
function sanitize_input($data) {
    global $conn;
    $data = trim($data);
    $data = stripslashes($data);
    $data = htmlspecialchars($data);
    $data = mysqli_real_escape_string($conn, $data);
    return $data;
}

/**
 * Function to check if user is logged in
 * @return boolean - True if logged in, false otherwise
 */
function is_logged_in() {
    return isset($_SESSION['user_id']) && !empty($_SESSION['user_id']);
}

/**
 * Function to check if admin is logged in
 * @return boolean - True if admin logged in, false otherwise
 */
function is_admin_logged_in() {
    return isset($_SESSION['admin_id']) && !empty($_SESSION['admin_id']);
}

/**
 * Function to redirect to specific page
 * @param string $url - URL to redirect to
 */
function redirect($url) {
    // Ensure we have a proper URL
    if (!preg_match('/^http/', $url)) {
        $url = rtrim(dirname($_SERVER['PHP_SELF']), '/\\') . '/' . ltrim($url, '/');
    }
    
    // Clear any output buffers
    while (ob_get_level()) {
        ob_end_clean();
    }
    
    header("Location: $url");
    exit();
}

/**
 * Function to display error message
 * @param string $message - Error message to display
 */
function display_error($message) {
    return "<div class='error-message'>$message</div>";
}

/**
 * Function to display success message
 * @param string $message - Success message to display
 */
function display_success($message) {
    return "<div class='success-message'>$message</div>";
}

/**
 * Function to format price in NPR format
 * @param float $price - Price to format
 * @return string - Formatted price
 */
function format_price($price) {
    return 'NPR ' . number_format($price, 2);
}

/**
 * Function to get category name by ID
 * @param int $category_id - Category ID
 * @return string - Category name
 */
function get_category_name($category_id) {
    global $conn;
    
    $stmt = prepared_query("SELECT category_name FROM categories WHERE category_id = ?", "i", [$category_id]);
    $result = mysqli_stmt_get_result($stmt);
    
    if ($row = mysqli_fetch_assoc($result)) {
        return $row['category_name'];
    }
    
    return "Unknown Category";
}

/**
 * Function to get product details by ID
 * @param int $product_id - Product ID
 * @return array|false - Product details or false if not found
 */
function get_product_details($product_id) {
    global $conn;
    
    $stmt = prepared_query("SELECT p.*, c.category_name FROM products p JOIN categories c ON p.category_id = c.category_id WHERE p.product_id = ?", "i", [$product_id]);
    $result = mysqli_stmt_get_result($stmt);
    
    return mysqli_fetch_assoc($result);
}

/**
 * Function to update product stock
 * @param int $product_id - Product ID
 * @param int $quantity - Quantity to subtract
 * @return boolean - True if successful, false otherwise
 */
function update_product_stock($product_id, $quantity) {
    $stmt = prepared_query("UPDATE products SET stock_quantity = stock_quantity - ? WHERE product_id = ? AND stock_quantity >= ?", "iii", [$quantity, $product_id, $quantity]);
    
    return mysqli_stmt_affected_rows($stmt) > 0;
}

?>
