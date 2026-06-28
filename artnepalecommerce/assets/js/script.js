/**
 * ARTNEPAL E-commerce Website JavaScript
 * Handles form validation, password toggle, and interactive features
 */

// Document ready function
document.addEventListener('DOMContentLoaded', function() {
    // Initialize all features
    initializePasswordToggle();
    initializeFormValidation();
    initializeCartFunctions();
    initializeNotifications();
});

/**
 * Password Toggle Functionality
 */
function initializePasswordToggle() {
    const toggleButtons = document.querySelectorAll('.password-toggle-btn');
    
    toggleButtons.forEach(button => {
        button.addEventListener('click', function() {
            const passwordInput = this.previousElementSibling;
            const icon = this;
            
            if (passwordInput.type === 'password') {
                passwordInput.type = 'text';
                icon.textContent = '👁️';
                icon.title = 'Hide password';
            } else {
                passwordInput.type = 'password';
                icon.textContent = '👁️‍🗨️';
                icon.title = 'Show password';
            }
        });
    });
}

/**
 * Form Validation Functions
 */
function initializeFormValidation() {
    // Registration form validation
    const registerForm = document.getElementById('registerForm');
    if (registerForm) {
        registerForm.addEventListener('submit', validateRegistrationForm);
    }
    
    // Login form validation
    const loginForm = document.getElementById('loginForm');
    if (loginForm) {
        loginForm.addEventListener('submit', validateLoginForm);
    }
    
    // Checkout form validation
    const checkoutForm = document.getElementById('checkoutForm');
    if (checkoutForm) {
        checkoutForm.addEventListener('submit', validateCheckoutForm);
    }
    
    // Contact form validation
    const contactForm = document.getElementById('contactForm');
    if (contactForm) {
        contactForm.addEventListener('submit', validateContactForm);
    }
    
    // Real-time validation
    const inputs = document.querySelectorAll('.form-control');
    inputs.forEach(input => {
        input.addEventListener('blur', function() {
            validateField(this);
        });
        
        input.addEventListener('input', function() {
            clearFieldError(this);
        });
    });
}

/**
 * Validate Registration Form
 */
function validateRegistrationForm(e) {
    e.preventDefault();
    
    const form = e.target;
    let isValid = true;
    
    // Get form fields
    const fullName = form.querySelector('[name="full_name"]');
    const email = form.querySelector('[name="email"]');
    const phone = form.querySelector('[name="phone_number"]');
    const address = form.querySelector('[name="address"]');
    const password = form.querySelector('[name="password"]');
    const confirmPassword = form.querySelector('[name="confirm_password"]');
    
    // Validate full name (alphabets only)
    if (!validateName(fullName)) {
        isValid = false;
    }
    
    // Validate email
    if (!validateEmail(email)) {
        isValid = false;
    }
    
    // Validate phone (10 digits only)
    if (!validatePhone(phone)) {
        isValid = false;
    }
    
    // Validate address (alphabets and spaces only)
    if (!validateAddress(address)) {
        isValid = false;
    }
    
    // Validate password
    if (!validatePassword(password)) {
        isValid = false;
    }
    
    // Validate confirm password
    if (!validateConfirmPassword(password, confirmPassword)) {
        isValid = false;
    }
    
    if (isValid) {
        form.submit();
    }
}

/**
 * Validate Login Form
 */
function validateLoginForm(e) {
    e.preventDefault();
    
    const form = e.target;
    let isValid = true;
    
    const email = form.querySelector('[name="email"]');
    const password = form.querySelector('[name="password"]');
    
    if (!validateEmail(email)) {
        isValid = false;
    }
    
    if (password.value.trim() === '') {
        showFieldError(password, 'Password is required');
        isValid = false;
    }
    
    if (isValid) {
        form.submit();
    }
}

/**
 * Validate Checkout Form
 */
function validateCheckoutForm(e) {
    e.preventDefault();
    
    const form = e.target;
    let isValid = true;
    
    const fullName = form.querySelector('[name="full_name"]');
    const email = form.querySelector('[name="email"]');
    const phone = form.querySelector('[name="phone_number"]');
    const address = form.querySelector('[name="address"]');
    
    if (!validateName(fullName)) {
        isValid = false;
    }
    
    if (!validateEmail(email)) {
        isValid = false;
    }
    
    if (!validatePhone(phone)) {
        isValid = false;
    }
    
    if (!validateAddress(address)) {
        isValid = false;
    }
    
    if (isValid) {
        form.submit();
    }
}

/**
 * Validate Contact Form
 */
function validateContactForm(e) {
    e.preventDefault();
    
    const form = e.target;
    let isValid = true;
    
    const name = form.querySelector('[name="name"]');
    const email = form.querySelector('[name="email"]');
    const message = form.querySelector('[name="message"]');
    
    if (!validateName(name)) {
        isValid = false;
    }
    
    if (!validateEmail(email)) {
        isValid = false;
    }
    
    if (message.value.trim() === '') {
        showFieldError(message, 'Message is required');
        isValid = false;
    } else if (message.value.trim().length < 10) {
        showFieldError(message, 'Message must be at least 10 characters long');
        isValid = false;
    }
    
    if (isValid) {
        form.submit();
    }
}

/**
 * Individual Field Validation Functions
 */
function validateName(field) {
    const value = field.value.trim();
    
    if (value === '') {
        showFieldError(field, 'This field is required');
        return false;
    }
    
    if (!/^[A-Za-z\s]+$/.test(value)) {
        showFieldError(field, 'Only alphabets and spaces are allowed');
        return false;
    }
    
    if (value.length < 2) {
        showFieldError(field, 'Name must be at least 2 characters long');
        return false;
    }
    
    clearFieldError(field);
    return true;
}

function validateEmail(field) {
    const value = field.value.trim();
    
    if (value === '') {
        showFieldError(field, 'Email is required');
        return false;
    }
    
    const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
    if (!emailRegex.test(value)) {
        showFieldError(field, 'Please enter a valid email address');
        return false;
    }
    
    clearFieldError(field);
    return true;
}

function validatePhone(field) {
    const value = field.value.trim();
    
    if (value === '') {
        showFieldError(field, 'Phone number is required');
        return false;
    }
    
    if (!/^[0-9]{10}$/.test(value)) {
        showFieldError(field, 'Phone number must be exactly 10 digits');
        return false;
    }
    
    clearFieldError(field);
    return true;
}

function validateAddress(field) {
    const value = field.value.trim();
    
    if (value === '') {
        showFieldError(field, 'Address is required');
        return false;
    }
    
    if (!/^[A-Za-z0-9\s\.,#\-\/]+$/.test(value)) {
        showFieldError(field, 'Address can only contain alphabets, digits, spaces, and basic punctuation');
        return false;
    }
    
    clearFieldError(field);
    return true;
}

function validatePassword(field) {
    const value = field.value;
    
    if (value === '') {
        showFieldError(field, 'Password is required');
        return false;
    }
    
    if (value.length < 8) {
        showFieldError(field, 'Password must be at least 8 characters long');
        return false;
    }
    
    if (!/(?=.*[A-Za-z])(?=.*\d)/.test(value)) {
        showFieldError(field, 'Password must contain at least one alphabet and one digit');
        return false;
    }
    
    clearFieldError(field);
    return true;
}

function validateConfirmPassword(passwordField, confirmField) {
    const password = passwordField.value;
    const confirmPassword = confirmField.value;
    
    if (confirmPassword === '') {
        showFieldError(confirmField, 'Please confirm your password');
        return false;
    }
    
    if (password !== confirmPassword) {
        showFieldError(confirmField, 'Passwords do not match');
        return false;
    }
    
    clearFieldError(confirmField);
    return true;
}

/**
 * Field Error Handling Functions
 */
function showFieldError(field, message) {
    clearFieldError(field);
    
    field.classList.add('error');
    
    const errorDiv = document.createElement('div');
    errorDiv.className = 'error-message';
    errorDiv.textContent = message;
    
    field.parentNode.appendChild(errorDiv);
}

function clearFieldError(field) {
    field.classList.remove('error');
    
    const errorDiv = field.parentNode.querySelector('.error-message');
    if (errorDiv) {
        errorDiv.remove();
    }
}

function validateField(field) {
    const name = field.name;
    
    switch(name) {
        case 'full_name':
        case 'name':
            validateName(field);
            break;
        case 'email':
            validateEmail(field);
            break;
        case 'phone_number':
            validatePhone(field);
            break;
        case 'address':
            validateAddress(field);
            break;
        case 'password':
            validatePassword(field);
            break;
        case 'confirm_password':
            const passwordField = document.querySelector('[name="password"]');
            validateConfirmPassword(passwordField, field);
            break;
    }
}

/**
 * Cart Functions
 */
function initializeCartFunctions() {
    // Add to cart buttons
    const addToCartButtons = document.querySelectorAll('.add-to-cart-btn');
    addToCartButtons.forEach(button => {
        button.addEventListener('click', function() {
            const productId = this.dataset.productId;
            addToCart(productId);
        });
    });
    
    // Quantity controls
    const quantityButtons = document.querySelectorAll('.quantity-btn');
    quantityButtons.forEach(button => {
        button.addEventListener('click', function() {
            const action = this.dataset.action;
            const productId = this.dataset.productId;
            updateCartQuantity(productId, action);
        });
    });
    
    // Remove from cart buttons
    const removeButtons = document.querySelectorAll('.remove-from-cart');
    removeButtons.forEach(button => {
        button.addEventListener('click', function() {
            const productId = this.dataset.productId;
            removeFromCart(productId);
        });
    });
}

/**
 * Add to Cart Function
 */
function addToCart(productId) {
    const formData = new FormData();
    formData.append('product_id', productId);
    formData.append('action', 'add');
    
    fetch('cart_handler.php', {
        method: 'POST',
        body: formData
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            showNotification('Product added to cart successfully!', 'success');
            updateCartCount(data.cart_count);
        } else {
            showNotification(data.message, 'error');
        }
    })
    .catch(error => {
        showNotification('Error adding product to cart', 'error');
    });
}

/**
 * Update Cart Quantity
 */
function updateCartQuantity(productId, action) {
    const formData = new FormData();
    formData.append('product_id', productId);
    formData.append('action', action);
    
    fetch('cart_handler.php', {
        method: 'POST',
        body: formData
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            location.reload(); // Reload to update cart display
        } else {
            showNotification(data.message, 'error');
        }
    })
    .catch(error => {
        showNotification('Error updating cart', 'error');
    });
}

/**
 * Remove from Cart Function
 */
function removeFromCart(productId) {
    if (confirm('Are you sure you want to remove this item from cart?')) {
        const formData = new FormData();
        formData.append('product_id', productId);
        formData.append('action', 'remove');
        
        fetch('cart_handler.php', {
            method: 'POST',
            body: formData
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                location.reload(); // Reload to update cart display
            } else {
                showNotification(data.message, 'error');
            }
        })
        .catch(error => {
            showNotification('Error removing item from cart', 'error');
        });
    }
}

/**
 * Update Cart Count in Header
 */
function updateCartCount(count) {
    const cartLink = document.querySelector('a[href="cart.php"]');
    if (cartLink) {
        cartLink.innerHTML = `Cart (${count})`;
    }
}

/**
 * Notification System
 */
function initializeNotifications() {
    // Auto-hide notifications after 5 seconds
    const notifications = document.querySelectorAll('.notification');
    notifications.forEach(notification => {
        setTimeout(() => {
            notification.remove();
        }, 5000);
    });
}

function showNotification(message, type = 'info') {
    const notification = document.createElement('div');
    notification.className = `notification ${type}`;
    notification.textContent = message;
    
    document.body.appendChild(notification);
    
    // Auto-hide after 5 seconds
    setTimeout(() => {
        notification.remove();
    }, 5000);
}

/**
 * Utility Functions
 */
function formatPrice(price) {
    return 'NPR ' + parseFloat(price).toFixed(2);
}

function confirmAction(message) {
    return confirm(message);
}

function loadingState(element, show = true) {
    if (show) {
        element.disabled = true;
        element.innerHTML = '<span class="loading"></span> Loading...';
    } else {
        element.disabled = false;
        element.innerHTML = element.dataset.originalText || element.textContent;
    }
}
