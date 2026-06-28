<?php
/**
 * eSewa Payment Processing
 * ARTNEPAL E-commerce Website
 * 
 * This file handles payment initiation and redirects to eSewa payment page
 */

// Start session
session_start();

// Include eSewa configuration
require_once 'config.php';

// Check if user is logged in
if (!is_logged_in()) {
    $_SESSION['error_message'] = 'Please login to proceed with payment.';
    redirect('../login.php');
}

// Get order ID from POST or session
$order_id = $_POST['order_id'] ?? $_GET['order_id'] ?? null;

if (!$order_id) {
    $_SESSION['error_message'] = 'Invalid order. Please try again.';
    redirect('../checkout.php');
}

// Get order details
$stmt = prepared_query("
    SELECT o.*, u.full_name, u.email 
    FROM orders o 
    JOIN users u ON o.user_id = u.user_id 
    WHERE o.order_id = ? AND o.user_id = ?
", "ii", [$order_id, $_SESSION['user_id']]);

$order = mysqli_fetch_assoc(mysqli_stmt_get_result($stmt));

if (!$order) {
    $_SESSION['error_message'] = 'Order not found. Please try again.';
    redirect('../checkout.php');
}

// Check if order is already paid
if ($order['order_status'] === 'paid' || $order['order_status'] === 'processing' || $order['order_status'] === 'shipped' || $order['order_status'] === 'delivered') {
    $_SESSION['error_message'] = 'This order has already been paid.';
    redirect('../order_details.php?id=' . $order_id);
}

// Calculate payment amounts
$amount = $order['total_amount'];
$tax_amount = ESEWA_TAX_AMOUNT;
$service_charge = ESEWA_SERVICE_CHARGE;
$delivery_charge = ESEWA_DELIVERY_CHARGE;
$total_amount = calculate_total_amount($amount);

// Format amounts for eSewa v2 (requires 1 decimal place if it's a whole number)
$formatted_total_amount = number_format($total_amount, 1, '.', '');

// Generate transaction UUID
$transaction_uuid = generate_transaction_uuid();
$signed_field_names = 'total_amount,transaction_uuid,product_code';
$signature = generate_esewa_signature($formatted_total_amount, $transaction_uuid, ESEWA_MERCHANT_CODE);

// Store payment details in database
$payment_stored = store_payment_details(
    $order_id,
    $order['full_name'],
    $transaction_uuid,
    $total_amount,
    'esewa',
    'pending'
);

if (!$payment_stored) {
    global $conn;
    $db_error = mysqli_error($conn);
    file_put_contents(__DIR__ . '/esewa_log.txt', "[" . date('Y-m-d H:i:s') . "] Database Error: " . $db_error . "\n\n", FILE_APPEND);
    $_SESSION['error_message'] = 'Failed to initiate payment. Please ensure the payments table exists in your database.';
    redirect('../checkout.php');
}

// Store transaction details in session for verification
$_SESSION['esewa_transaction'] = [
    'order_id' => $order_id,
    'transaction_uuid' => $transaction_uuid,
    'amount' => $total_amount
];

// Log initiation for debugging
$log_data = [
    'order_id' => $order_id,
    'amount' => $amount,
    'total_amount' => $total_amount,
    'transaction_uuid' => $transaction_uuid,
    'product_code' => ESEWA_MERCHANT_CODE,
    'signature' => $signature
];
file_put_contents(__DIR__ . '/esewa_log.txt', "[" . date('Y-m-d H:i:s') . "] Initiation Payload: " . json_encode($log_data) . "\n\n", FILE_APPEND);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Redirecting to eSewa Payment - ARTNEPAL</title>
    <style>
        body {
            font-family: 'Georgia', serif;
            background-color: #FFF8DC;
            display: flex;
            justify-content: center;
            align-items: center;
            min-height: 100vh;
            margin: 0;
            padding: 20px;
        }
        .payment-container {
            background: white;
            padding: 2rem;
            border-radius: 10px;
            box-shadow: 0 5px 20px rgba(0,0,0,0.1);
            max-width: 500px;
            width: 100%;
            text-align: center;
        }
        .payment-container h1 {
            color: #8B4513;
            margin-bottom: 1rem;
        }
        .payment-container p {
            color: #666;
            margin-bottom: 1.5rem;
        }
        .order-details {
            background: #F8F9FA;
            padding: 1rem;
            border-radius: 5px;
            margin-bottom: 1.5rem;
            text-align: left;
        }
        .order-details div {
            margin-bottom: 0.5rem;
        }
        .order-details strong {
            color: #8B4513;
        }
        .esewa-button {
            background: #60BB46;
            color: white;
            padding: 1rem 2rem;
            border: none;
            border-radius: 5px;
            font-size: 1.1rem;
            font-weight: bold;
            cursor: pointer;
            width: 100%;
            text-decoration: none;
            display: inline-block;
            transition: background 0.3s ease;
        }
        .esewa-button:hover {
            background: #4A9A36;
        }
    </style>
</head>
<body>
    <div class="payment-container">
        <h1>🛒 eSewa Payment</h1>
        <p>You will be redirected to eSewa payment gateway to complete your payment.</p>
        
        <div class="order-details">
            <div><strong>Order ID:</strong> #<?php echo str_pad($order_id, 6, '0', STR_PAD_LEFT); ?></div>
            <div><strong>Amount:</strong> <?php echo format_price($amount); ?></div>
            <div><strong>Tax:</strong> <?php echo format_price($tax_amount); ?></div>
            <div><strong>Service Charge:</strong> <?php echo format_price($service_charge); ?></div>
            <div><strong>Delivery Charge:</strong> <?php echo format_price($delivery_charge); ?></div>
            <div><strong>Total Amount:</strong> <?php echo format_price($total_amount); ?></div>
        </div>
        
        <form action="<?php echo ESEWA_SERVICE_URL; ?>" method="POST">
            <input type="hidden" name="amount" value="<?php echo number_format($amount, 1, '.', ''); ?>">
            <input type="hidden" name="tax_amount" value="<?php echo number_format($tax_amount, 1, '.', ''); ?>">
            <input type="hidden" name="total_amount" value="<?php echo $formatted_total_amount; ?>">
            <input type="hidden" name="transaction_uuid" value="<?php echo $transaction_uuid; ?>">
            <input type="hidden" name="product_code" value="<?php echo ESEWA_MERCHANT_CODE; ?>">
            <input type="hidden" name="product_service_charge" value="<?php echo $service_charge; ?>">
            <input type="hidden" name="product_delivery_charge" value="<?php echo $delivery_charge; ?>">
            <input type="hidden" name="success_url" value="<?php echo ESEWA_SUCCESS_URL; ?>">
            <input type="hidden" name="failure_url" value="<?php echo ESEWA_FAILURE_URL; ?>">
            <input type="hidden" name="signed_field_names" value="<?php echo $signed_field_names; ?>">
            <input type="hidden" name="signature" value="<?php echo $signature; ?>">
            
            <button type="submit" class="esewa-button">
                Proceed to eSewa Payment
            </button>
        </form>
    </div>
</body>
</html>
