<?php
session_start();
require_once 'db.php';

$success = $error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = $_POST['name'] ?? '';
    $email = $_POST['email'] ?? '';
    $subject = $_POST['subject'] ?? '';
    $message = $_POST['message'] ?? '';
    
    // Basic validation
    if (empty($name) || empty($email) || empty($subject) || empty($message)) {
        $error = "All fields are required";
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error = "Invalid email format";
    } else {
        // In a real application, you would send an email here
        // For now, we'll just show a success message
        $success = "Thank you for your message. We'll get back to you soon!";
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Contact Us - RCar</title>
    <link rel="stylesheet" href="assets/style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
</head>
<body>
    <?php include 'includes/navbar.php'; ?>

    <section class="contact-header">
        <div class="container">
            <h1>Contact Us</h1>
            <p>We're here to help and answer any questions you might have</p>
        </div>
    </section>

    <section class="contact-content">
        <div class="container">
            <div class="contact-grid">
                <div class="contact-info">
                    <div class="info-card">
                        <i class="fas fa-map-marker-alt"></i>
                        <h3>Visit Us</h3>
                        <p>123 Car Street</p>
                        <p>City, State 12345</p>
                        <p>Country</p>
                    </div>
                    
                    <div class="info-card">
                        <i class="fas fa-phone"></i>
                        <h3>Call Us</h3>
                        <p>+1 234 567 890</p>
                        <p>Mon - Fri, 9am - 6pm</p>
                    </div>
                    
                    <div class="info-card">
                        <i class="fas fa-envelope"></i>
                        <h3>Email Us</h3>
                        <p>info@rcar.com</p>
                        <p>support@rcar.com</p>
                    </div>
                    
                    <div class="info-card">
                        <i class="fas fa-clock"></i>
                        <h3>Working Hours</h3>
                        <p>Monday - Friday: 9:00 AM - 6:00 PM</p>
                        <p>Saturday: 10:00 AM - 4:00 PM</p>
                        <p>Sunday: Closed</p>
                    </div>
                </div>

                <div class="contact-form-container">
                    <?php if ($success): ?>
                        <div class="success"><?php echo htmlspecialchars($success); ?></div>
                    <?php endif; ?>
                    
                    <?php if ($error): ?>
                        <div class="error"><?php echo htmlspecialchars($error); ?></div>
                    <?php endif; ?>

                    <form class="contact-form" method="POST" action="">
                        <div class="form-group">
                            <label for="name">Full Name</label>
                            <div class="input-group">
                                <i class="fas fa-user"></i>
                                <input type="text" id="name" name="name" required>
                            </div>
                        </div>
                        
                        <div class="form-group">
                            <label for="email">Email Address</label>
                            <div class="input-group">
                                <i class="fas fa-envelope"></i>
                                <input type="email" id="email" name="email" required>
                            </div>
                        </div>
                        
                        <div class="form-group">
                            <label for="subject">Subject</label>
                            <div class="input-group">
                                <i class="fas fa-tag"></i>
                                <input type="text" id="subject" name="subject" required>
                            </div>
                        </div>
                        
                        <div class="form-group">
                            <label for="message">Message</label>
                            <textarea id="message" name="message" rows="5" required></textarea>
                        </div>
                        
                        <button type="submit" class="submit-btn">Send Message</button>
                    </form>
                </div>
            </div>
        </div>
    </section>

    <section class="map-section">
        <div class="container">
            <div class="map-container">
                <!-- Replace with your actual Google Maps embed code -->
                <iframe 
                    src="https://www.google.com/maps/embed?pb=!1m18!1m12!1m3!1d387193.305935303!2d-74.25986548248684!3d40.69714941932609!2m3!1f0!2f0!3f0!3m2!1i1024!2i768!4f13.1!3m3!1m2!1s0x89c24fa5d33f083b%3A0xc80b8f06e177fe62!2sNew%20York%2C%20NY%2C%20USA!5e0!3m2!1sen!2s!4v1645454332403!5m2!1sen!2s" 
                    width="100%" 
                    height="450" 
                    style="border:0;" 
                    allowfullscreen="" 
                    loading="lazy">
                </iframe>
            </div>
        </div>
    </section>

    <?php include 'includes/footer.php'; ?>
</body>
</html> 