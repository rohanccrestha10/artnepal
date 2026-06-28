<?php
/**
 * User Login Page
 * ARTNEPAL E-commerce Website
 * 
 * This page handles user authentication with session management
 */

// Include header
require_once 'includes/header.php';

// Redirect if already logged in
if (is_logged_in()) {
    redirect('dashboard.php');
}

// Initialize variables
$email = '';
$errors = [];

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Get form data
    $email = sanitize_input($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';
    
    // Validation
    if (empty($email)) {
        $errors['email'] = 'Email is required';
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errors['email'] = 'Please enter a valid email address';
    }
    
    if (empty($password)) {
        $errors['password'] = 'Password is required';
    }
    
    // If no validation errors, attempt login
    if (empty($errors)) {
        // Get user from database
        $stmt = prepared_query("SELECT user_id, full_name, email, password FROM users WHERE email = ?", "s", [$email]);
        $result = mysqli_stmt_get_result($stmt);
        
        if ($user = mysqli_fetch_assoc($result)) {
            // Verify password
            if (password_verify($password, $user['password'])) {
                // Set session variables
                $_SESSION['user_id'] = $user['user_id'];
                $_SESSION['full_name'] = $user['full_name'];
                $_SESSION['email'] = $user['email'];
                $_SESSION['login_time'] = time();
                
                // Regenerate session ID for security
                session_regenerate_id(true);
                
                $_SESSION['success_message'] = 'Welcome back, ' . $user['full_name'] . '!';
                redirect('dashboard.php');
            } else {
                $errors['login'] = 'Invalid email or password';
            }
        } else {
            $errors['login'] = 'Invalid email or password';
        }
    }
}
?>

<div class="container">
    <div class="form-container">
        <h2 style="text-align: center; color: #8B4513; margin-bottom: 2rem;">Login - ARTNEPAL</h2>
        
        <?php if (isset($errors['login'])): ?>
            <div class="error-message" style="text-align: center; margin-bottom: 1rem;">
                <?php echo $errors['login']; ?>
            </div>
        <?php endif; ?>
        
        <form id="loginForm" method="POST" action="login.php" novalidate>
            <div class="form-group">
                <label for="email">Email Address *</label>
                <input type="email" 
                       id="email" 
                       name="email" 
                       class="form-control" 
                       value="<?php echo htmlspecialchars($email); ?>"
                       placeholder="Enter your email address"
                       required>
                <?php if (isset($errors['email'])): ?>
                    <span class="error-message"><?php echo $errors['email']; ?></span>
                <?php endif; ?>
            </div>
            
            <div class="form-group">
                <label for="password">Password *</label>
                <div class="password-toggle">
                    <input type="password" 
                           id="password" 
                           name="password" 
                           class="form-control" 
                           placeholder="Enter your password"
                           required>
                    <button type="button" class="password-toggle-btn" title="Show password">
                        👁️‍🗨️
                    </button>
                </div>
                <?php if (isset($errors['password'])): ?>
                    <span class="error-message"><?php echo $errors['password']; ?></span>
                <?php endif; ?>
            </div>
            
            <div class="form-group">
                <button type="submit" class="btn btn-primary" style="width: 100%;">
                    Login
                </button>
            </div>
            
            <div style="text-align: center; margin-top: 1rem;">
                <p>Don't have an account? <a href="register.php" style="color: #8B4513;">Register here</a></p>
                 <p>Are you an Admin? <a href="admin/login.php" style="color: #8B4513;">Admin login</a></p>
            </div>
        </form>
        
        <div style="text-align: center; margin-top: 2rem; padding-top: 1rem; border-top: 1px solid #DEB887;">
            <h4 style="color: #8B4513; margin-bottom: 1rem;">New to ARTNEPAL?</h4>
            <p style="color: #666; font-size: 0.9rem;">Join us to explore authentic Nepali arts and handicrafts</p>
            <a href="register.php" class="btn btn-secondary" style="margin-top: 1rem;">
                Create Account
            </a>
        </div>
    </div>
</div>

<style>
.form-container {
    background: white;
    padding: 2rem;
    border-radius: 10px;
    box-shadow: 0 5px 20px rgba(0,0,0,0.1);
    margin: 2rem auto;
    max-width: 500px;
}

.form-group {
    margin-bottom: 1.5rem;
}

.form-group label {
    display: block;
    margin-bottom: 0.5rem;
    font-weight: bold;
    color: #8B4513;
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
    border-color: #8B4513;
    box-shadow: 0 0 5px rgba(139,69,19,0.3);
}

.form-control.error {
    border-color: #DC143C;
}

.error-message {
    color: #DC143C;
    font-size: 0.875rem;
    margin-top: 0.25rem;
    display: block;
}

.password-toggle {
    position: relative;
}

.password-toggle-btn {
    position: absolute;
    right: 10px;
    top: 50%;
    transform: translateY(-50%);
    background: none;
    border: none;
    cursor: pointer;
    color: #8B4513;
    font-size: 1.2rem;
}

.btn {
    padding: 0.75rem 1.5rem;
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
    background-color: #8B4513;
    color: white;
}

.btn-primary:hover {
    background-color: #A0522D;
    transform: translateY(-2px);
    box-shadow: 0 4px 8px rgba(0,0,0,0.2);
}

.btn-secondary {
    background-color: #D4AF37;
    color: #8B4513;
}

.btn-secondary:hover {
    background-color: #FFD700;
    transform: translateY(-2px);
}

@media (max-width: 768px) {
    .form-container {
        margin: 1rem;
        padding: 1.5rem;
    }
}
</style>

<?php
// Include footer
require_once 'includes/footer.php';
?>