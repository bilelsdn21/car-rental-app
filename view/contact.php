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
                        <p>Rue Lamine Abassi</p>
                        <p>Sousse,Hammam Sousse 4011</p>
                        <p>Tunisia</p>
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

                    <form class="form">
                        <div class="flex">
                            <label>
                                <input required="" placeholder="" type="text" class="input">
                                <span>first name</span>
                            </label>
                    
                            <label>
                                <input required="" placeholder="" type="text" class="input">
                                <span>last name</span>
                            </label>
                        </div>  
                                
                        <label>
                            <input required="" placeholder="" type="email" class="input">
                            <span>email</span>
                        </label> 
                            
                        <label>
                            <input required="" type="tel" placeholder="" class="input">
                            <span>contact number</span>
                        </label>
                        <label>
                            <textarea required="" rows="3" placeholder="" class="input01"></textarea>
                            <span>message</span>
                        </label>
                        
                        <button class="fancy" href="#">
                        <span class="top-key"></span>
                        <span class="text">submit</span>
                        <span class="bottom-key-1"></span>
                        <span class="bottom-key-2"></span>
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </section>

    <section class="map-section">
        <div class="container">
            <div class="map-container">
                <!-- Replace with your actual Google Maps embed code -->
                <iframe src="https://www.google.com/maps/embed?pb=!1m14!1m8!1m3!1d7691.820705000249!2d10.600841816381335!3d35.848703687632614!3m2!1i1024!2i768!4f13.1!3m3!1m2!1s0x12fd8a6826eca57b%3A0xf698bd059d8e2d13!2s%C3%89cole%20sup%C3%A9rieure%20des%20sciences%20et%20de%20la%20technologie%20de%20Hammam%20Sousse!5e0!3m2!1sen!2stn!4v1745340900462!5m2!1sen!2stn" 
                    width="100%" 
                    height="450" 
                    style="border:0;" 
                    allowfullscreen="" 
                    loading="lazy" 
                    referrerpolicy="no-referrer-when-downgrade">
                </iframe>
            </div>
        </div>
    </section>

    <?php include 'includes/footer.php'; ?>
</body>
</html> 