<?php
/**
 * Admin Logout Script
 * ARTNEPAL E-commerce Website
 * 
 * This script handles admin logout and session destruction
 */

// Start session
session_start();

// Include database configuration
require_once '../config/database.php';

// Destroy all session variables
$_SESSION = array();

// Delete the session cookie
if (ini_get("session.use_cookies")) {
    $params = session_get_cookie_params();
    setcookie(session_name(), '', time() - 42000,
        $params["path"], $params["domain"],
        $params["secure"], $params["httponly"]
    );
}

// Destroy the session
session_destroy();

// Redirect to admin login page with success message
session_start();
$_SESSION['success_message'] = 'You have been logged out successfully.';
header('Location: login.php');
exit();
?>
