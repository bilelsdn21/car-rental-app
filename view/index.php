<?php
session_start();
require_once 'db.php';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>RCar - Premium Car Rental Service</title>
    <link rel="stylesheet" href="assets/style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
</head>
<body>
    <?php include 'includes/navbar.php'; ?>

    <section class="hero">
        <div class="hero-content">
            <h1>Find Your Perfect Ride</h1>
            <p>Choose from our wide selection of premium vehicles for any occasion</p>
            <div class="search-form">
                <form class="search-grid" action="catalog.php" method="GET">
                    <div class="search-group">
                        <label for="location">Location</label>
                        <input type="text" id="location" name="location" placeholder="Where do you want to rent?">
                    </div>
                    <div class="search-group">
                        <label for="start_date">Pick-up Date</label>
                        <input type="date" id="start_date" name="start_date" min="<?php echo date('Y-m-d'); ?>">
                    </div>
                    <div class="search-group">
                        <label for="end_date">Return Date</label>
                        <input type="date" id="end_date" name="end_date" min="<?php echo date('Y-m-d'); ?>">
                    </div>
                    <button type="submit" class="search-btn">
                        <i class="fas fa-search"></i>
                    </button>
                </form>
            </div>
        </div>
    </section>

    <section class="features">
        <div class="container">
            <h2>Why Choose Us</h2>
            <div class="features-grid">
                <div class="feature-card">
                    <i class="fas fa-car"></i>
                    <h3>Wide Selection</h3>
                    <p>Choose from our extensive fleet of premium vehicles for any occasion.</p>
                </div>
                <div class="feature-card">
                    <i class="fas fa-shield-alt"></i>
                    <h3>Safe & Secure</h3>
                    <p>All our vehicles are regularly maintained and fully insured.</p>
                </div>
                <div class="feature-card">
                    <i class="fas fa-dollar-sign"></i>
                    <h3>Best Prices</h3>
                    <p>Competitive rates and special offers for both short and long-term rentals.</p>
                </div>
                <div class="feature-card">
                    <i class="fas fa-headset"></i>
                    <h3>24/7 Support</h3>
                    <p>Our customer service team is always ready to assist you.</p>
                </div>
            </div>
        </div>
    </section>

    <section class="featured-cars">
        <div class="container">
            <h2>Featured Vehicles</h2>
            <div class="car-grid">
                <?php
                // Get featured cars
                $query = "SELECT c.*, u.name as agency_name 
                         FROM cars c 
                         JOIN users u ON c.posted_by = u.id 
                         WHERE c.status = 'available' 
                         ORDER BY c.created_at DESC 
                         LIMIT 6";
                
                $result = $conn->query($query);
                if (!$result) {
                    error_log("Database query failed: " . $conn->error);
                    error_log("Query: " . $query);
                    // Show a friendly error message
                    echo "<div class='error-message'>
                        <i class='fas fa-exclamation-circle'></i>
                        <p>Sorry, we couldn't load the featured vehicles at this time.</p>
                    </div>";
                } else {
                    while ($car = $result->fetch_assoc()):
                ?>
                <div class="car-card">
                    <img src="<?php echo htmlspecialchars($car['image_url']); ?>" alt="<?php echo htmlspecialchars($car['make'] . ' ' . $car['model']); ?>">
                    <div class="car-card-content">
                        <h3><?php echo htmlspecialchars($car['make'] . ' ' . $car['model']); ?></h3>
                        <p class="agency">By <?php echo htmlspecialchars($car['agency_name']); ?></p>
                        <p class="price">$<?php echo number_format($car['price_per_day'], 2); ?> / day</p>
                        <a href="car.php?id=<?php echo $car['id']; ?>" class="view-details-btn">View Details</a>
                    </div>
                </div>
                <?php endwhile; 
                } // Close the else from the database query check
                ?>
            </div>
            <div class="text-center" style="margin-top: 2rem;">
                <a href="catalog.php" class="btn-primary">View All Cars</a>
            </div>
        </div>
    </section>

    <footer>
        <div class="container">
            <div class="footer-content">
                <div class="footer-section">
                    <h3>Quick Links</h3>
                    <a href="catalog.php">Browse Cars</a>
                    <a href="about.php">About Us</a>
                    <a href="contact.php">Contact</a>
                    <?php if (!isset($_SESSION['user_id'])): ?>
                        <a href="register.php?type=agency">List Your Cars</a>
                    <?php endif; ?>
                </div>
                <div class="footer-section">
                    <h3>For Renters</h3>
                    <a href="how_it_works.php">How it Works</a>
                    <a href="register.php?type=user">Create Account</a>
                    <?php if (isset($_SESSION['user_id'])): ?>
                        <a href="bookings.php">My Bookings</a>
                    <?php endif; ?>
                </div>
                <div class="footer-section">
                    <h3>For Agencies</h3>
                    <a href="register.php?type=agency">Register Agency</a>
                    <?php if (isset($_SESSION['user_id']) && $_SESSION['role'] === 'agency'): ?>
                        <a href="manage_cars.php">Manage Cars</a>
                        <a href="bookings.php">Manage Bookings</a>
                    <?php endif; ?>
                </div>
                <div class="footer-section">
                    <h3>Contact Us</h3>
                    <p><i class="fas fa-phone"></i> +1 234 567 890</p>
                    <p><i class="fas fa-envelope"></i> info@rcar.com</p>
                    <p><i class="fas fa-map-marker-alt"></i> 123 Car Street, City</p>
                </div>
            </div>
            <div class="footer-bottom">
                <p>&copy; <?php echo date('Y'); ?> RCar. All rights reserved.</p>
            </div>
        </div>
    </footer>

    <script>
        // Validate date selection
        document.getElementById('start_date').addEventListener('change', function() {
            document.getElementById('end_date').min = this.value;
        });
    </script>
</body>
</html> 