<?php
/**
 * eSewa Payment Gateway Configuration
 * ARTNEPAL E-commerce Website
 * 
 * This file contains eSewa API configuration for sandbox/test environment
 */

// eSewa Sandbox/Test Environment Configuration
$esewa_env = getenv('ESEWA_ENV') ?: 'uat';
define('ESEWA_MERCHANT_CODE', getenv('ESEWA_MERCHANT_CODE') ?: 'EPAYTEST'); // Test merchant code provided by eSewa
define('ESEWA_SECRET', getenv('ESEWA_SECRET') ?: ($esewa_env === 'uat' ? '8gBm/:&EnhH.1/q' : ''));
define('ESEWA_SERVICE_URL', $esewa_env === 'uat' ? 'https://rc-epay.esewa.com.np/api/epay/main/v2/form' : 'https://epay.esewa.com.np/api/epay/main/v2/form'); // eSewa test URL
define('ESEWA_STATUS_CHECK_URL', $esewa_env === 'uat' ? 'https://rc.esewa.com.np/api/epay/transaction/status/' : 'https://epay.esewa.com.np/api/epay/transaction/status/');
$scheme = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? 'https' : 'http';
$host = $_SERVER['HTTP_HOST'] ?? 'localhost';
$script_dir = rtrim(str_replace('\\', '/', dirname($_SERVER['SCRIPT_NAME'] ?? '/')), '/');
$root_dir = (basename($script_dir) === 'esewa') ? dirname($script_dir) : $script_dir;
$root_dir = rtrim(str_replace('\\', '/', $root_dir), '/');
$base_url = $scheme . '://' . $host . ($root_dir ? $root_dir : '');
define('ESEWA_SUCCESS_URL', $base_url . '/esewa/success.php'); // Success callback URL
define('ESEWA_FAILURE_URL', $base_url . '/esewa/failure.php'); // Failure callback URL

// Payment Configuration
define('ESEWA_TAX_AMOUNT', 0); // Tax amount (can be 0 for now)
define('ESEWA_SERVICE_CHARGE', 0); // Service charge (can be 0 for now)
define('ESEWA_DELIVERY_CHARGE', 0); // Delivery charge (can be 0 for now)

// Security Configuration
define('ESEWA_SECRET_KEY', ''); // Secret key for verification (if provided by eSewa)

// Database Configuration
require_once __DIR__ . '/../config/database.php';

/**
 * Calculate total payment amount
 * @param float $amount - Base amount
 * @return float - Total amount including tax, service charge, and delivery charge
 */
function calculate_total_amount($amount) {
    $total = $amount + ESEWA_TAX_AMOUNT + ESEWA_SERVICE_CHARGE + ESEWA_DELIVERY_CHARGE;
    return round($total, 2);
}

/**
 * Generate unique transaction UUID
 * @return string - Unique transaction ID
 */
function generate_transaction_uuid() {
    $prefix = 'TXN-';
    $rand = function_exists('random_bytes') ? bin2hex(random_bytes(8)) : uniqid();
    $rand = preg_replace('/[^a-zA-Z0-9]/', '', (string)$rand);
    return $prefix . $rand . '-' . time();
}

/**
 * Store payment details in database
 * @param int $order_id - Order ID
 * @param string $customer_name - Customer name
 * @param string $transaction_uuid - Transaction UUID
 * @param float $amount - Payment amount
 * @param string $payment_method - Payment method
 * @param string $payment_status - Payment status
 * @return bool - Success/Failure
 */
function store_payment_details($order_id, $customer_name, $transaction_uuid, $amount, $payment_method, $payment_status) {
    $stmt = prepared_query("
        INSERT INTO payments (order_id, customer_name, transaction_uuid, amount, payment_method, payment_status, payment_date)
        VALUES (?, ?, ?, ?, ?, ?, NOW())
    ", "issdss", [$order_id, $customer_name, $transaction_uuid, $amount, $payment_method, $payment_status]);
    
    return $stmt !== false;
}

/**
 * Update payment status
 * @param string $transaction_uuid - Transaction UUID
 * @param string $payment_status - New payment status
 * @return bool - Success/Failure
 */
function update_payment_status($transaction_uuid, $payment_status) {
    $stmt = prepared_query("
        UPDATE payments 
        SET payment_status = ? 
        WHERE transaction_uuid = ?
    ", "ss", [$payment_status, $transaction_uuid]);
    
    return $stmt !== false;
}

/**
 * Get payment details by transaction UUID
 * @param string $transaction_uuid - Transaction UUID
 * @return array|false - Payment details or false
 */
function get_payment_details($transaction_uuid) {
    $stmt = prepared_query("
        SELECT * FROM payments 
        WHERE transaction_uuid = ?
    ", "s", [$transaction_uuid]);
    
    $result = mysqli_stmt_get_result($stmt);
    return mysqli_fetch_assoc($result);
}

/**
 * Update order status after successful payment
 * @param int $order_id - Order ID
 * @param string $status - New order status
 * @return bool - Success/Failure
 */
function update_order_status($order_id, $status) {
    $stmt = prepared_query("
        UPDATE orders 
        SET order_status = ? 
        WHERE order_id = ?
    ", "si", [$status, $order_id]);
    
    return $stmt !== false;
}

function generate_esewa_signature($total_amount, $transaction_uuid, $product_code) {
    if (!ESEWA_SECRET) {
        return '';
    }

    $message = 'total_amount=' . $total_amount . ',transaction_uuid=' . $transaction_uuid . ',product_code=' . $product_code;
    return base64_encode(hash_hmac('sha256', $message, ESEWA_SECRET, true));
}

function verify_esewa_response_signature($response_data) {
    if (!is_array($response_data)) {
        return false;
    }

    $signature = $response_data['signature'] ?? null;
    $signed_field_names = $response_data['signed_field_names'] ?? null;
    if (!$signature || !$signed_field_names || !ESEWA_SECRET) {
        return false;
    }

    $fields = array_map('trim', explode(',', (string)$signed_field_names));
    $parts = [];
    foreach ($fields as $field) {
        if ($field === '') {
            continue;
        }
        if (!array_key_exists($field, $response_data)) {
            return false;
        }
        $parts[] = $field . '=' . $response_data[$field];
    }

    $message = implode(',', $parts);
    $expected = base64_encode(hash_hmac('sha256', $message, ESEWA_SECRET, true));
    return hash_equals($expected, (string)$signature);
}

function esewa_status_check($transaction_uuid, $total_amount, $product_code) {
    $url = ESEWA_STATUS_CHECK_URL . '?product_code=' . urlencode($product_code) . '&total_amount=' . urlencode($total_amount) . '&transaction_uuid=' . urlencode($transaction_uuid);

    if (function_exists('curl_init')) {
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 10);
        curl_setopt($ch, CURLOPT_TIMEOUT, 20);
        $response = curl_exec($ch);
        $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $curl_error = curl_error($ch);
        curl_close($ch);

        // Log status check for debugging
        file_put_contents(__DIR__ . '/esewa_log.txt', "[" . date('Y-m-d H:i:s') . "] Status Check URL: " . $url . "\nResponse: " . $response . " (HTTP $http_code)\n\n", FILE_APPEND);

        if ($http_code !== 200 || $response === false) {
            return false;
        }
    } else {
        $response = file_get_contents($url);
        file_put_contents(__DIR__ . '/esewa_log.txt', "[" . date('Y-m-d H:i:s') . "] Status Check URL: " . $url . " (file_get_contents)\nResponse: " . $response . "\n\n", FILE_APPEND);
        if ($response === false) {
            return false;
        }
    }

    $data = json_decode($response, true);
    return is_array($data) ? $data : false;
}
?>
