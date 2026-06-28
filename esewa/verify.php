<?php
/**
 * eSewa Payment Verification
 * ARTNEPAL E-commerce Website
 * 
 * This file verifies payment from eSewa and updates database
 */

// Start session
session_start();

// Include eSewa configuration
require_once 'config.php';

function decode_esewa_base64_data($data) {
    $data = (string)$data;
    $data = trim($data);
    $data = urldecode($data);

    $normalized = strtr($data, '-_', '+/');
    $normalized = str_replace(' ', '+', $normalized);
    $normalized = preg_replace('/\s+/', '', $normalized);
    $pad = strlen($normalized) % 4;
    if ($pad) {
        $normalized .= str_repeat('=', 4 - $pad);
    }

    $decoded = base64_decode($normalized, true);
    if ($decoded === false) {
        $decoded = base64_decode($data, true);
    }

    return $decoded === false ? null : $decoded;
}

$raw_data = $_GET['data'] ?? $_POST['data'] ?? null;
$response_data = null;

// Log raw verification data
file_put_contents(__DIR__ . '/esewa_log.txt', "[" . date('Y-m-d H:i:s') . "] Verification Raw Data: " . $raw_data . "\n\n", FILE_APPEND);

if ($raw_data) {
    $decoded = decode_esewa_base64_data($raw_data);
    file_put_contents(__DIR__ . '/esewa_log.txt', "[" . date('Y-m-d H:i:s') . "] Verification Decoded Data: " . $decoded . "\n\n", FILE_APPEND);
    if ($decoded) {
        $json = json_decode($decoded, true);
        if (is_array($json)) {
            $response_data = $json;
        }
    }
}

// Get payment response from eSewa
$oid = $response_data['transaction_uuid'] ?? ($_GET['oid'] ?? $_GET['pid'] ?? null);
$amt = $response_data['total_amount'] ?? ($_GET['amt'] ?? null);
$refId = $response_data['transaction_code'] ?? ($_GET['refId'] ?? $_GET['refid'] ?? null);

// Validate required parameters
if (!$oid || !$amt || !$refId) {
    $_SESSION['error_message'] = 'Invalid payment response. Please contact support.';
    redirect('../failure.php');
}

if ($response_data) {
    if (!verify_esewa_response_signature($response_data)) {
        $_SESSION['error_message'] = 'Payment verification failed. Please contact support.';
        redirect('../failure.php');
    }

    $status = strtoupper((string)($response_data['status'] ?? ''));
    if ($status !== 'COMPLETE') {
        $_SESSION['error_message'] = 'Payment failed or pending. Please try again.';
        redirect('../failure.php');
    }

    $product_code = (string)($response_data['product_code'] ?? '');
    if ($product_code !== (string)ESEWA_MERCHANT_CODE) {
        $_SESSION['error_message'] = 'Payment verification failed. Please contact support.';
        redirect('../failure.php');
    }
}

// Get payment details from database
$payment = get_payment_details($oid);

if (!$payment) {
    $_SESSION['error_message'] = 'Payment record not found. Please contact support.';
    redirect('../failure.php');
}

if (($payment['payment_status'] ?? '') === 'success') {
    $_SESSION['success_message'] = 'Payment successful! Your order #' . str_pad($payment['order_id'], 6, '0', STR_PAD_LEFT) . ' has been placed.';
    $_SESSION['payment_success'] = true;
    $_SESSION['order_id'] = $payment['order_id'];
    $_SESSION['esewa_oid'] = $oid;
    $_SESSION['esewa_amt'] = $amt;
    $_SESSION['esewa_refId'] = $refId;
    redirect('../success.php?oid=' . urlencode($oid) . '&amt=' . urlencode($amt) . '&refId=' . urlencode($refId));
}

// Verify amount matches
if (abs($payment['amount'] - $amt) > 0.01) {
    $_SESSION['error_message'] = 'Payment amount mismatch. Please contact support.';
    redirect('../failure.php');
}

if (!$response_data) {
    $status_check = esewa_status_check($oid, $amt, ESEWA_MERCHANT_CODE);
    if (is_array($status_check) && (int)($status_check['code'] ?? 1) === 0) {
        $_SESSION['error_message'] = (string)($status_check['error_message'] ?? 'Payment verification temporarily unavailable. Please try again.');
        redirect('../order_details.php?id=' . urlencode($payment['order_id']));
    }
    if (!is_array($status_check) || strtoupper((string)($status_check['status'] ?? '')) !== 'COMPLETE') {
        $_SESSION['error_message'] = 'Payment verification pending. Please check your order status after a moment.';
        redirect('../order_details.php?id=' . urlencode($payment['order_id']));
    }
    if (!empty($status_check['ref_id'])) {
        $refId = $status_check['ref_id'];
    }
}

// Update payment status with reference ID
$update_payment = update_payment_status($oid, 'success');

if (!$update_payment) {
    $_SESSION['error_message'] = 'Failed to update payment status. Please contact support.';
    redirect('../failure.php');
}

// Update payment with reference ID
$stmt = prepared_query("
    UPDATE payments 
    SET reference_id = ? 
    WHERE transaction_uuid = ?
", "ss", [$refId, $oid]);

// Check if payment columns exist first
$payment_columns_exist = false;
$result = mysqli_query($conn, "SHOW COLUMNS FROM orders LIKE 'payment_method'");
if (mysqli_num_rows($result) > 0) {
    $payment_columns_exist = true;
}

// Update order status (and payment status if columns exist)
if ($payment_columns_exist) {
    $stmt = prepared_query("UPDATE orders SET payment_status = 'paid', order_status = 'processing' WHERE order_id = ?", "i", [$payment['order_id']]);
    $update_order = $stmt;
} else {
    $update_order = update_order_status($payment['order_id'], 'processing');
}

if (!$update_order) {
    $_SESSION['error_message'] = 'Payment successful but failed to update order. Please contact support.';
    redirect('../failure.php');
}

// Clear cart for this order
$stmt = prepared_query("SELECT user_id FROM orders WHERE order_id = ?", "i", [$payment['order_id']]);
$order_row = $stmt ? mysqli_fetch_assoc(mysqli_stmt_get_result($stmt)) : null;
$user_id = $order_row['user_id'] ?? null;
if ($user_id) {
    $stmt = prepared_query("DELETE FROM cart WHERE user_id = ?", "i", [$user_id]);
}

// Store success message in session
$_SESSION['success_message'] = 'Payment successful! Your order #' . str_pad($payment['order_id'], 6, '0', STR_PAD_LEFT) . ' has been placed.';
$_SESSION['payment_success'] = true;
$_SESSION['order_id'] = $payment['order_id'];
$_SESSION['esewa_oid'] = $oid;
$_SESSION['esewa_amt'] = $amt;
$_SESSION['esewa_refId'] = $refId;

// Redirect to success page
redirect('../success.php?oid=' . urlencode($oid) . '&amt=' . urlencode($amt) . '&refId=' . urlencode($refId));
?>
