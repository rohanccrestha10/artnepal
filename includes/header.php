<?php
/**
 * Header Include File
 * ARTNEPAL E-commerce Website
 * 
 * This file contains the common header section
 * including navigation and session management
 */

// Start session if not already active
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

// Include database configuration
require_once 'config/database.php';

// Check if user is logged in
$user_logged_in = is_logged_in();
$admin_logged_in = is_admin_logged_in();

// Get cart item count if user is logged in
$cart_count = 0;
if ($user_logged_in) {
    $stmt = prepared_query("SELECT SUM(quantity) as total FROM cart WHERE user_id = ?", "i", [$_SESSION['user_id']]);
    $result = mysqli_stmt_get_result($stmt);
    if ($row = mysqli_fetch_assoc($result)) {
        $cart_count = $row['total'] ?? 0;
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>ARTNEPAL - Nepali Cultural Arts & Handicrafts</title>
    <link rel="stylesheet" href="assets/css/style.css">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Georgia:wght@400;700&display=swap" rel="stylesheet">
</head>
<body>
    <!-- Header -->
    <header class="header">
        <div class="container">
            <div class="header-content">
                <a href="index.php" class="logo">ARTNEPAL</a>
                <nav>
                    <ul class="nav-menu">
                        <li><a href="index.php">Home</a></li>
                        <li><a href="products.php">Products</a></li>
                        <li><a href="about.php">About Us</a></li>
                        <li><a href="contact.php">Contact Us</a></li>
                        <?php if ($user_logged_in): ?>
                            <li><a href="dashboard.php">Dashboard</a></li>
                            <li><a href="cart.php">Cart (<?php echo $cart_count; ?>)</a></li>
                            <li><a href="logout.php">Logout</a></li>
                        <?php elseif ($admin_logged_in): ?>
                            <li><a href="admin/dashboard.php">Admin Panel</a></li>
                            <li><a href="admin/logout.php">Logout</a></li>
                        <?php else: ?>
                            <li><a href="login.php">Login</a></li>
                            <li><a href="register.php">Register</a></li>
                        <?php endif; ?>
                    </ul>
                </nav>
            </div>
        </div>
    </header>

    <!-- Notification Display -->
    <?php
    if (isset($_SESSION['success_message'])) {
        echo '<div class="notification success">' . $_SESSION['success_message'] . '</div>';
        unset($_SESSION['success_message']);
    }
    
    if (isset($_SESSION['error_message'])) {
        echo '<div class="notification error">' . $_SESSION['error_message'] . '</div>';
        unset($_SESSION['error_message']);
    }
    ?>

    <!-- Main Content -->
    <main class="main-content">
