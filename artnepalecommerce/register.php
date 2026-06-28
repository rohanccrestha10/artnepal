<?php

/**

 * User Registration Page

 * ARTNEPAL E-commerce Website

 * 

 * This page handles user registration with strict validation

 */



// Include header

require_once 'includes/header.php';



// Initialize variables

$full_name = $email = $phone_number = $address = '';

$errors = [];



// Handle form submission

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    // Get form data

    $full_name = sanitize_input($_POST['full_name'] ?? '');

    $email = sanitize_input($_POST['email'] ?? '');

    $phone_number = sanitize_input($_POST['phone_number'] ?? '');

    $address = sanitize_input($_POST['address'] ?? '');

    $password = $_POST['password'] ?? '';

    $confirm_password = $_POST['confirm_password'] ?? '';

    

    // Validation rules (STRICT)

    

    // 1. Full Name: alphabets only (A-Z, a-z)

    if (empty($full_name)) {

        $errors['full_name'] = 'Full name is required';

    } elseif (!preg_match('/^[A-Za-z\s]+$/', $full_name)) {

        $errors['full_name'] = 'Full name can only contain alphabets and spaces';

    } elseif (strlen($full_name) < 2) {

        $errors['full_name'] = 'Full name must be at least 2 characters long';

    } elseif (strlen($full_name) > 100) {

        $errors['full_name'] = 'Full name cannot exceed 100 characters';

    }

    

    // 2. Email: valid email format with strict validation
    if (empty($email)) {
        $errors['email'] = 'Email is required';
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errors['email'] = 'Please enter a valid email address';
    } elseif (strlen($email) > 100) {
        $errors['email'] = 'Email cannot exceed 100 characters';
    } else {

        // Check if email already exists

        $stmt = prepared_query("SELECT user_id FROM users WHERE email = ?", "s", [$email]);

        $result = mysqli_stmt_get_result($stmt);

        if (mysqli_fetch_assoc($result)) {

            $errors['email'] = 'Email is already registered';

        }

    }

    

    // 3. Phone Number: digits only, exactly 10 digits

    if (empty($phone_number)) {

        $errors['phone_number'] = 'Phone number is required';

    } elseif (!preg_match('/^[0-9]{10}$/', $phone_number)) {

        $errors['phone_number'] = 'Phone number must contain exactly 10 digits';

    } else {

        // Check if phone number already exists

        $stmt = prepared_query("SELECT user_id FROM users WHERE phone_number = ?", "s", [$phone_number]);

        $result = mysqli_stmt_get_result($stmt);

        if (mysqli_fetch_assoc($result)) {

            $errors['phone_number'] = 'Phone number is already registered';

        }

    }

    

    // 4. Address: alphabets, digits and spaces
    if (empty($address)) {
        $errors['address'] = 'Address is required';
    } elseif (!preg_match('/^[A-Za-z0-9\s\.,#\-\/]+$/', $address)) {
        $errors['address'] = 'Address can only contain alphabets, digits, spaces, and basic punctuation (.,#-/)';
    } elseif (strlen($address) > 200) {

        $errors['address'] = 'Address cannot exceed 200 characters';

    }

    

    // 5. Password validation

    if (empty($password)) {

        $errors['password'] = 'Password is required';

    } elseif (strlen($password) < 8) {

        $errors['password'] = 'Password must be at least 8 characters long';

    } elseif (!preg_match('/^(?=.*[A-Za-z])(?=.*\d).+$/', $password)) {

        $errors['password'] = 'Password must contain at least one alphabet and one digit';

    } elseif (strlen($password) > 255) {

        $errors['password'] = 'Password cannot exceed 255 characters';

    }

    

    // 6. Confirm Password validation

    if (empty($confirm_password)) {

        $errors['confirm_password'] = 'Please confirm your password';

    } elseif ($password !== $confirm_password) {

        $errors['confirm_password'] = 'Passwords do not match';

    }

    

    // If no errors, register the user

    if (empty($errors)) {

        // Hash the password

        $hashed_password = password_hash($password, PASSWORD_DEFAULT);

        

        // Insert user into database

        $stmt = prepared_query(

            "INSERT INTO users (full_name, email, phone_number, address, password) VALUES (?, ?, ?, ?, ?)",

            "sssss",

            [$full_name, $email, $phone_number, $address, $hashed_password]

        );

        

        if ($stmt) {

            $_SESSION['success_message'] = 'Registration successful! Please login to continue.';

            redirect('login.php');

        } else {

            $errors['general'] = 'Registration failed. Please try again.';

        }

    }

}

?>



<div class="container">

    <div class="form-container">

        <h2 style="text-align: center; color: #8B4513; margin-bottom: 2rem;">Register - ARTNEPAL</h2>

        

        <?php if (isset($errors['general'])): ?>

            <div class="error-message" style="text-align: center; margin-bottom: 1rem;">

                <?php echo $errors['general']; ?>

            </div>

        <?php endif; ?>

        

        <form id="registerForm" method="POST" action="register.php" novalidate>

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

                       placeholder="Enter 10-digit phone number"

                       maxlength="10"

                       required>

                <?php if (isset($errors['phone_number'])): ?>

                    <span class="error-message"><?php echo $errors['phone_number']; ?></span>

                <?php endif; ?>

            </div>

            

            <div class="form-group">

                <label for="address">Address *</label>

                <input type="text" 

                       id="address" 

                       name="address" 

                       class="form-control" 

                       value="<?php echo htmlspecialchars($address); ?>"

                       placeholder="Enter your address (alphabets and spaces only)"

                       required>

                <?php if (isset($errors['address'])): ?>

                    <span class="error-message"><?php echo $errors['address']; ?></span>

                <?php endif; ?>

            </div>

            

            <div class="form-group">

                <label for="password">Password *</label>

                <div class="password-toggle">

                    <input type="password" 

                           id="password" 

                           name="password" 

                           class="form-control" 

                           placeholder="Enter password (min 8 chars, 1 alphabet, 1 digit)"

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

                <label for="confirm_password">Confirm Password *</label>

                <div class="password-toggle">

                    <input type="password" 

                           id="confirm_password" 

                           name="confirm_password" 

                           class="form-control" 

                           placeholder="Confirm your password"

                           required>

                    <button type="button" class="password-toggle-btn" title="Show password">

                        👁️‍🗨️

                    </button>

                </div>

                <?php if (isset($errors['confirm_password'])): ?>

                    <span class="error-message"><?php echo $errors['confirm_password']; ?></span>

                <?php endif; ?>

            </div>

            

            <div class="form-group">

                <button type="submit" class="btn btn-primary" style="width: 100%;">

                    Register Account

                </button>

            </div>

            

            <div style="text-align: center; margin-top: 1rem;">

                <p>Already have an account? <a href="login.php" style="color: #8B4513;">Login here</a></p>

            </div>

        </form>

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



@media (max-width: 768px) {

    .form-container {

        margin: 1rem;

        padding: 1.5rem;

    }

}

</style>



<script>

document.addEventListener('DOMContentLoaded', function() {

    const form = document.getElementById('registerForm');

    const emailInput = document.getElementById('email');

    

    // Email validation function

    function validateEmail(email) {

        // Basic email format check

        const emailRegex = /^[a-zA-Z0-9._%+-]+@[a-zA-Z0-9.-]+\.(com|net|org|edu|gov|mil|int|biz|info|mobi|name|aero|jobs|museum|co|io|me|tv|us|uk|ca|au|in|np|pk|bd|lk|mm|th|vn|ph|sg|my|id|kh|la|bn|bt|mv)$/;

        if (!emailRegex.test(email)) {

            return false;

        }

        

        // Check for common typos in domain extensions

        const commonTypos = {

            'con': 'com',

            'co': 'com',

            'comm': 'com',

            'comn': 'com',

            'cmo': 'com',

            'gamil': 'gmail',

            'gmial': 'gmail',

            'gmaill': 'gmail',

            'yahooo': 'yahoo',

            'yaho': 'yahoo',

            'hotmial': 'hotmail',

            'hotmai': 'hotmail'

        };

        

        const [localPart, domain] = email.split('@');

        const [domainName, extension] = domain.split('.');

        

        // Check for common typos

        if (commonTypos[extension]) {

            return {

                valid: false,

                message: `Did you mean ${localPart}@${domainName}.${commonTypos[extension]}?`

            };

        }

        

        // Check for common email provider typos

        if (commonTypos[domainName]) {

            return {

                valid: false,

                message: `Did you mean ${localPart}@${commonTypos[domainName]}.${extension}?`

            };

        }

        

        return { valid: true };

    }

    

    // Real-time email validation

    emailInput.addEventListener('blur', function() {

        const email = this.value.trim();

        const errorElement = this.parentElement.querySelector('.error-message');

        

        if (email && !validateEmail(email).valid) {

            const validation = validateEmail(email);

            this.classList.add('error');

            

            // Remove existing error message if any

            if (errorElement) {

                errorElement.remove();

            }

            

            // Add new error message

            const errorSpan = document.createElement('span');

            errorSpan.className = 'error-message';

            errorSpan.textContent = validation.message || 'Please enter a valid email address';

            this.parentElement.appendChild(errorSpan);

        } else if (email && validateEmail(email).valid) {

            this.classList.remove('error');

            if (errorElement) {

                errorElement.remove();

            }

        }

    });

    

    // Form submission validation

    form.addEventListener('submit', function(e) {

        const email = emailInput.value.trim();

        

        if (email) {

            const validation = validateEmail(email);

            if (!validation.valid) {

                e.preventDefault();

                

                // Focus on email field

                emailInput.focus();

                emailInput.classList.add('error');

                

                // Show error message

                const errorElement = emailInput.parentElement.querySelector('.error-message');

                if (errorElement) {

                    errorElement.remove();

                }

                

                const errorSpan = document.createElement('span');

                errorSpan.className = 'error-message';

                errorSpan.textContent = validation.message || 'Please enter a valid email address';

                emailInput.parentElement.appendChild(errorSpan);

                

                return false;

            }

        }

    });

});

</script>



<?php

// Include footer

require_once 'includes/footer.php';

?>

