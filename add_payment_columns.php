<?php
require_once 'config/database.php';

echo "Adding payment columns to orders table...<br>";

// Add payment_method column
try {
    $stmt = mysqli_query($conn, "ALTER TABLE orders ADD COLUMN IF NOT EXISTS payment_method VARCHAR(50) NULL DEFAULT 'cod' AFTER total_amount");
    if ($stmt) {
        echo "✓ Payment method column added<br>";
    }
} catch (Exception $e) {
    echo "Note: " . $e->getMessage() . "<br>";
}

// Add payment_status column
try {
    $stmt = mysqli_query($conn, "ALTER TABLE orders ADD COLUMN IF NOT EXISTS payment_status ENUM('pending', 'paid', 'failed') NULL DEFAULT 'pending' AFTER payment_method");
    if ($stmt) {
        echo "✓ Payment status column added<br>";
    }
} catch (Exception $e) {
    echo "Note: " . $e->getMessage() . "<br>";
}

echo "<br>Done! You can delete this file now.";
?>
