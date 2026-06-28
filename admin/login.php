<?php
/**
 * Admin Login Page
 * ARTNEPAL E-commerce Website
 * 
 * This page handles admin authentication
 */

// Include header (simple version without navigation)
session_start();
require_once '../config/database.php';

// Redirect if already logged in
if (is_admin_logged_in()) {
    redirect('dashboard.php');
}

// Initialize variables
$username = '';
$errors = [];

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Get form data
    $username = sanitize_input($_POST['username'] ?? '');
    $password = $_POST['password'] ?? '';
    
    // Validation
    if (empty($username)) {
        $errors['username'] = 'Username is required';
    }
    
    if (empty($password)) {
        $errors['password'] = 'Password is required';
    }
    
    // If no validation errors, attempt login
    if (empty($errors)) {
        // Get admin from database
        $stmt = prepared_query("SELECT admin_id, username, email, password, role FROM admin WHERE username = ? OR email = ?", "ss", [$username, $username]);
        $result = mysqli_stmt_get_result($stmt);
        
        if ($admin = mysqli_fetch_assoc($result)) {
            // Verify password
            if (password_verify($password, $admin['password'])) {
                // Set session variables
                $_SESSION['admin_id'] = $admin['admin_id'];
                $_SESSION['admin_username'] = $admin['username'];
                $_SESSION['admin_email'] = $admin['email'];
                $_SESSION['admin_role'] = $admin['role'];
                $_SESSION['admin_login_time'] = time();
                
                // Regenerate session ID for security
                session_regenerate_id(true);
                
                $_SESSION['success_message'] = 'Welcome back, ' . $admin['username'] . '!';
                redirect('dashboard.php');
            } else {
                $errors['login'] = 'Invalid username or password';
            }
        } else {
            $errors['login'] = 'Invalid username or password';
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Login - ARTNEPAL</title>
    <link rel="stylesheet" href="../assets/css/style.css">
    <style>
        body {
            background: linear-gradient(135deg, #2F4F4F, #8B4513);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0;
        }
        
        .admin-login-container {
            background: white;
            padding: 3rem;
            border-radius: 15px;
            box-shadow: 0 15px 35px rgba(0,0,0,0.1);
            max-width: 400px;
            width: 90%;
        }
        
        .admin-header {
            text-align: center;
            margin-bottom: 2rem;
        }
        
        .admin-header h1 {
            color: #0c0c0cff;
            font-size: 2rem;
            margin-bottom: 0.5rem;
        }
        
        .admin-header p {
            color: #fcf7f7ff;
        }
        
        .form-group {
            margin-bottom: 1.5rem;
        }
        
        .form-group label {
            display: block;
            margin-bottom: 0.5rem;
            font-weight: bold;
            color: #2F4F4F;
        }
        
        .form-control {
            width: 100%;
            padding: 0.75rem;
            border: 2px solid #DEB887;
            border-radius: 5px;
            font-size: 1rem;
            transition: border-color 0.3s ease;
        }
        
        .form-control:focus {
            outline: none;
            border-color: #2F4F4F;
            box-shadow: 0 0 5px rgba(47,79,79,0.3);
        }
        
        .error-message {
            color: #DC143C;
            font-size: 0.875rem;
            margin-top: 0.25rem;
            display: block;
        }
        
        .btn {
            width: 100%;
            padding: 0.75rem;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            font-size: 1rem;
            transition: all 0.3s ease;
            text-decoration: none;
            display: inline-block;
            text-align: center;
        }
        
        .btn-primary {
            background-color: #2F4F4F;
            color: white;
        }
        
        .btn-primary:hover {
            background-color: #1C3A3A;
            transform: translateY(-2px);
            box-shadow: 0 4px 8px rgba(0,0,0,0.2);
        }
        
        .back-link {
            text-align: center;
            margin-top: 2rem;
            padding-top: 1rem;
            border-top: 1px solid #DEB887;
        }
        
        .back-link a {
            color: #2F4F4F;
            text-decoration: none;
        }
        
        .back-link a:hover {
            color: #8B4513;
        }
    </style>
</head>
<body>
    <div class="admin-login-container">
        <div class="admin-header">
            <h1>🔐 Admin Login</h1>
            <p>ARTNEPAL Administration Panel</p>
        </div>
        
        <?php if (isset($errors['login'])): ?>
            <div class="error-message" style="text-align: center; margin-bottom: 1rem; background: #F8D7DA; padding: 0.75rem; border-radius: 5px;">
                <?php echo $errors['login']; ?>
            </div>
        <?php endif; ?>
        
        <form method="POST" action="login.php">
            <div class="form-group">
                <label for="username">Username or Email *</label>
                <input type="text" 
                       id="username" 
                       name="username" 
                       class="form-control" 
                       value="<?php echo htmlspecialchars($username); ?>"
                       placeholder="Enter username or email"
                       required>
                <?php if (isset($errors['username'])): ?>
                    <span class="error-message"><?php echo $errors['username']; ?></span>
                <?php endif; ?>
            </div>
            
            <div class="form-group">
                <label for="password">Password *</label>
                <input type="password" 
                       id="password" 
                       name="password" 
                       class="form-control" 
                       placeholder="Enter password"
                       required>
                <?php if (isset($errors['password'])): ?>
                    <span class="error-message"><?php echo $errors['password']; ?></span>
                <?php endif; ?>
            </div>
            
            <button type="submit" class="btn btn-primary">
                Login to Admin Panel
            </button>
        </form>
        
        <div class="back-link">
            <p>Secure admin access for authorized personnel only</p>
            <hr style="margin: 1rem 0; border: none; border-top: 1px solid #DEB887;">
            <a href="../index.php">← Back to Website</a>
        </div>
    </div>
</body>
</html>
