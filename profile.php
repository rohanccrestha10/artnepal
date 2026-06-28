<?php
/**
 * User Profile Page
 * ARTNEPAL E-commerce Website
 * 
 * This page allows users to view and update their profile information
 */

// Include header
require_once 'includes/header.php';

// Check if user is logged in
if (!is_logged_in()) {
    $_SESSION['error_message'] = 'Please login to access your profile.';
    redirect('login.php');
}

// Get user information
$user_id = $_SESSION['user_id'];
$stmt = prepared_query("SELECT full_name, email, phone_number, address FROM users WHERE user_id = ?", "i", [$user_id]);
$result = mysqli_stmt_get_result($stmt);
$user = mysqli_fetch_assoc($result);

// Initialize variables
$full_name = $user['full_name'] ?? '';
$email = $user['email'] ?? '';
$phone_number = $user['phone_number'] ?? '';
$address = $user['address'] ?? '';
$errors = [];

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Get form data
    $full_name = sanitize_input($_POST['full_name'] ?? '');
    $email = sanitize_input($_POST['email'] ?? '');
    $phone_number = sanitize_input($_POST['phone_number'] ?? '');
    $address = sanitize_input($_POST['address'] ?? '');
    
    // Validation
    if (empty($full_name)) {
        $errors['full_name'] = 'Full name is required';
    } elseif (!preg_match('/^[A-Za-z\s]+$/', $full_name)) {
        $errors['full_name'] = 'Full name can only contain alphabets and spaces';
    } elseif (strlen($full_name) < 2) {
        $errors['full_name'] = 'Full name must be at least 2 characters long';
    } elseif (strlen($full_name) > 100) {
        $errors['full_name'] = 'Full name cannot exceed 100 characters';
    }
    
    if (empty($email)) {
        $errors['email'] = 'Email is required';
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errors['email'] = 'Please enter a valid email address';
    } elseif (strlen($email) > 100) {
        $errors['email'] = 'Email cannot exceed 100 characters';
    }
    
    if (empty($phone_number)) {
        $errors['phone_number'] = 'Phone number is required';
    } elseif (!preg_match('/^[0-9]{10}$/', $phone_number)) {
        $errors['phone_number'] = 'Phone number must contain exactly 10 digits';
    }
    
    if (empty($address)) {
        $errors['address'] = 'Address is required';
    } elseif (!preg_match('/^[A-Za-z\s\.,#\-\/]+$/', $address)) {
        $errors['address'] = 'Address can only contain alphabets, spaces, and basic punctuation (.,#-/)';
    } elseif (strlen($address) > 200) {
        $errors['address'] = 'Address cannot exceed 200 characters';
    }
    
    // If no errors, update user profile
    if (empty($errors)) {
        $stmt = prepared_query(
            "UPDATE users SET full_name = ?, email = ?, phone_number = ?, address = ? WHERE user_id = ?",
            "sssss",
            [$full_name, $email, $phone_number, $address, $user_id]
        );
        
        if ($stmt) {
            $_SESSION['success_message'] = 'Profile updated successfully!';
            // Update session variables
            $_SESSION['user_full_name'] = $full_name;
            $_SESSION['user_email'] = $email;
            $_SESSION['user_phone'] = $phone_number;
            $_SESSION['user_address'] = $address;
            redirect('dashboard.php');
        } else {
            $_SESSION['error_message'] = 'Failed to update profile. Please try again.';
        }
    }
}
?>

<div class="container">
    <div style="max-width: 800px; margin: 2rem auto;">
        <!-- Page Header -->
        <div style="text-align: center; margin-bottom: 2rem;">
            <h1 style="color: #8B4513; margin-bottom: 0.5rem;">My Profile</h1>
            <p style="color: #666;">View and update your personal information</p>
        </div>
        
        <!-- Profile Form -->
        <div style="background: white; padding: 2rem; border-radius: 10px; box-shadow: 0 5px 20px rgba(0,0,0,0.1); margin-bottom: 2rem;">
            <h2 style="color: #8B4513; margin-bottom: 1.5rem;">Personal Information</h2>
            
            <?php if (isset($_SESSION['error_message'])): ?>
                <div style="background: #F8D7DA; border: 1px solid #F5C6CB; color: #721C24; padding: 1rem; border-radius: 5px; margin-bottom: 1.5rem;">
                    <h4 style="margin: 0 0 0.5rem 0;">❌ Error</h4>
                    <p style="margin: 0;"><?php echo $_SESSION['error_message']; ?></p>
                </div>
                <?php unset($_SESSION['error_message']); ?>
            <?php endif; ?>
            
            <?php if (isset($_SESSION['success_message'])): ?>
                <div style="background: #D4EDDA; border: 1px solid #C3E6CB; color: #155724; padding: 1rem; border-radius: 5px; margin-bottom: 1.5rem;">
                    <h4 style="margin: 0 0 0.5rem 0;">✅ Success</h4>
                    <p style="margin: 0;"><?php echo $_SESSION['success_message']; ?></p>
                </div>
                <?php unset($_SESSION['success_message']); ?>
            <?php endif; ?>
            
            <form method="POST" style="margin-bottom: 2rem;">
                <div class="form-group">
                    <label for="full_name">Full Name *</label>
                    <input type="text" 
                           id="full_name" 
                           name="full_name" 
                           class="form-control" 
                           value="<?php echo htmlspecialchars($full_name); ?>"
                           placeholder="Enter your full name"
                           required>
                    <?php if (isset($errors['full_name'])): ?>
                        <span class="error-message"><?php echo $errors['full_name']; ?></span>
                    <?php endif; ?>
                </div>
                
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
                    <label for="phone_number">Phone Number *</label>
                    <input type="tel" 
                           id="phone_number" 
                           name="phone_number" 
                           class="form-control" 
                           value="<?php echo htmlspecialchars($phone_number); ?>"
                           placeholder="Enter your 10-digit phone number"
                           maxlength="10"
                           required>
                    <?php if (isset($errors['phone_number'])): ?>
                        <span class="error-message"><?php echo $errors['phone_number']; ?></span>
                    <?php endif; ?>
                </div>
                
                <div class="form-group">
                    <label for="address">Delivery Address *</label>
                    <textarea id="address" 
                              name="address" 
                              class="form-control" 
                              rows="3"
                              placeholder="Enter your delivery address (alphabets and basic punctuation only)"
                              required><?php echo htmlspecialchars($address); ?></textarea>
                    <?php if (isset($errors['address'])): ?>
                        <span class="error-message"><?php echo $errors['address']; ?></span>
                    <?php endif; ?>
                </div>
                
                <div style="display: flex; gap: 1rem; margin-top: 2rem;">
                    <button type="submit" class="btn btn-primary" style="flex: 1;">
                        Update Profile
                    </button>
                    <a href="dashboard.php" class="btn btn-secondary" style="flex: 1;">
                        Cancel
                    </a>
                </div>
            </form>
        </div>
        
        <!-- Action Buttons -->
        <div style="display: flex; gap: 1rem; justify-content: center; flex-wrap: wrap;">
            <a href="dashboard.php" class="btn btn-primary" style="padding: 1rem 2rem;">
                🏠 Back to Dashboard
            </a>
            <a href="products.php" class="btn btn-secondary" style="padding: 1rem 2rem;">
                🛍️ Continue Shopping
            </a>
        </div>
    </div>
</div>

<style>
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
    box-shadow: 0 0 5px rgba(220,20,60,0.3);
}

.error-message {
    color: #DC143C;
    font-size: 0.875rem;
    margin-top: 0.25rem;
    display: block;
}

.btn {
    display: inline-block;
    padding: 0.75rem 1.5rem;
    border: none;
    border-radius: 5px;
    text-decoration: none;
    font-weight: bold;
    transition: all 0.3s ease;
}

.btn-primary {
    background-color: #8B4513;
    color: white;
}

.btn-primary:hover {
    background-color: #A0522D;
    transform: translateY(-2px);
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
    .container > div > div {
        padding: 1rem;
    }
}
</style>

<?php
// Include footer
require_once 'includes/footer.php';
?>
