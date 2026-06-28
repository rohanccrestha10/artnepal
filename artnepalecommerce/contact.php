<?php
/**
 * Contact Us Page
 * ARTNEPAL E-commerce Website
 * 
 * This page provides contact information and a contact form
 */

// Include header
require_once 'includes/header.php';

// Initialize variables
$name = $email = $message = '';
$errors = [];
$success = false;

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Get form data
    $name = sanitize_input($_POST['name'] ?? '');
    $email = sanitize_input($_POST['email'] ?? '');
    $message = sanitize_input($_POST['message'] ?? '');
    
    // Validation
    if (empty($name)) {
        $errors['name'] = 'Name is required';
    } elseif (!preg_match('/^[A-Za-z\s]+$/', $name)) {
        $errors['name'] = 'Name can only contain alphabets and spaces';
    } elseif (strlen($name) < 2) {
        $errors['name'] = 'Name must be at least 2 characters long';
    }
    
    if (empty($email)) {
        $errors['email'] = 'Email is required';
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errors['email'] = 'Please enter a valid email address';
    }
    
    if (empty($message)) {
        $errors['message'] = 'Message is required';
    } elseif (strlen($message) < 10) {
        $errors['message'] = 'Message must be at least 10 characters long';
    } elseif (strlen($message) > 1000) {
        $errors['message'] = 'Message cannot exceed 1000 characters';
    }
    
    // If no errors, save message
    if (empty($errors)) {
        $stmt = prepared_query(
            "INSERT INTO contact_messages (name, email, message) VALUES (?, ?, ?)",
            "sss",
            [$name, $email, $message]
        );
        
        if ($stmt) {
            $success = true;
            $name = $email = $message = '';
        } else {
            $errors['general'] = 'Failed to send message. Please try again.';
        }
    }
}
?>

<div class="container">
    <!-- Page Header -->
    <div style="text-align: center; margin: 2rem 0;">
        <h1 style="color: #8B4513; font-size: 2.5rem; margin-bottom: 1rem;">Contact Us</h1>
        <p style="color: #666; font-size: 1.1rem;">We'd love to hear from you! Get in touch with any questions about our products or services.</p>
    </div>
    
    <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 3rem; margin-bottom: 3rem;">
        <!-- Contact Information -->
        <div>
            <div style="background: white; padding: 2rem; border-radius: 10px; box-shadow: 0 5px 20px rgba(0,0,0,0.1);">
                <h2 style="color: #8B4513; margin-bottom: 1.5rem;">Contact Info</h2>
                
                <div style="margin-bottom: 2rem;">
                    <h3 style="color: #8B4513; margin-bottom: 1rem;">📍 Kathmandu, Nepal</h3>
                </div>
                
                <div style="margin-bottom: 2rem;">
                    <h3 style="color: #8B4513; margin-bottom: 1rem;">📧 artnepal921@gmail.com</h3>
                </div>
                
                <div style="margin-bottom: 2rem;">
                    <h3 style="color: #8B4513; margin-bottom: 1rem;">📞 +977-9860146269</h3>
                </div>
                
                <div style="margin-bottom: 2rem;">
                    <h3 style="color: #8B4513; margin-bottom: 1rem;">🕐 Sun-Fri: 10AM-6PM</h3>
                </div>
        </div>
        
        <!-- Contact Form -->
        <div>
            <div style="background: white; padding: 2rem; border-radius: 10px; box-shadow: 0 5px 20px rgba(0,0,0,0.1);">
                <h2 style="color: #8B4513; margin-bottom: 1.5rem;">Send us a Message</h2>
                
                <?php if ($success): ?>
                    <div style="background: #D4EDDA; border: 1px solid #C3E6CB; color: #155724; padding: 1rem; border-radius: 5px; margin-bottom: 1.5rem;">
                        <h4 style="margin: 0 0 0.5rem 0;">✅ Message Sent Successfully!</h4>
                        <p style="margin: 0;">Thank you for contacting us. We'll get back to you within 24 hours.</p>
                    </div>
                <?php endif; ?>
                
                <?php if (isset($errors['general'])): ?>
                    <div class="error-message" style="margin-bottom: 1rem;">
                        <?php echo $errors['general']; ?>
                    </div>
                <?php endif; ?>
                
                <form id="contactForm" method="POST" action="contact.php">
                    <div class="form-group">
                        <label for="name">Your Name *</label>
                        <input type="text" 
                               id="name" 
                               name="name" 
                               class="form-control" 
                               value="<?php echo htmlspecialchars($name); ?>"
                               placeholder="Enter your full name"
                               required>
                        <?php if (isset($errors['name'])): ?>
                            <span class="error-message"><?php echo $errors['name']; ?></span>
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
                        <label for="subject">Subject</label>
                        <select id="subject" name="subject" class="form-control">
                            <option value="">Select a subject</option>
                            <option value="product-inquiry">Product Inquiry</option>
                            <option value="order-status">Order Status</option>
                            <option value="partnership">Partnership Opportunity</option>
                            <option value="feedback">Feedback</option>
                            <option value="other">Other</option>
                        </select>
                    </div>
                    
                    <div class="form-group">
                        <label for="message">Message *</label>
                        <textarea id="message" 
                                  name="message" 
                                  class="form-control" 
                                  rows="5"
                                  placeholder="Tell us more about your inquiry..."
                                  required><?php echo htmlspecialchars($message); ?></textarea>
                        <?php if (isset($errors['message'])): ?>
                            <span class="error-message"><?php echo $errors['message']; ?></span>
                        <?php endif; ?>
                    </div>
                    
                    <button type="submit" class="btn btn-primary" style="width: 100%;">
                        Send Message
                    </button>
                </form>
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

.error-message {
    color: #DC143C;
    font-size: 0.875rem;
    margin-top: 0.25rem;
    display: block;
}

@media (max-width: 768px) {
    .container > div:first-child {
        grid-template-columns: 1fr;
        gap: 2rem;
    }
}
</style>

<?php
// Include footer
require_once 'includes/footer.php';
?>
