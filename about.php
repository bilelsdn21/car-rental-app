<?php
session_start();
require_once 'db.php';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>About Us - RCar</title>
    <link rel="stylesheet" href="assets/style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
</head>
<body>
    <?php include 'includes/navbar.php'; ?>

    <section class="about-header">
        <div class="container">
            <h1>About RCar</h1>
            <p>Your trusted partner in premium car rentals</p>
        </div>
    </section>

    <section class="about-content">
        <div class="container">
            <div class="about-grid">
                <div class="about-text">
                    <h2>Our Story</h2>
                    <p>RCar was founded with a simple mission: to make premium car rentals accessible, convenient, and enjoyable for everyone. We believe that every journey should be special, whether it's a business trip, a family vacation, or a weekend getaway.</p>
                    
                    <h2>Our Vision</h2>
                    <p>To be the leading car rental platform that connects car enthusiasts with their dream rides while providing exceptional service and unforgettable experiences.</p>
                    
                    <h2>Why Choose RCar?</h2>
                    <ul class="about-list">
                        <li>
                            <i class="fas fa-check-circle"></i>
                            <div>
                                <h3>Quality Assured</h3>
                                <p>All vehicles on our platform undergo rigorous quality checks and regular maintenance.</p>
                            </div>
                        </li>
                        <li>
                            <i class="fas fa-check-circle"></i>
                            <div>
                                <h3>Verified Agencies</h3>
                                <p>We partner only with trusted and verified car rental agencies.</p>
                            </div>
                        </li>
                        <li>
                            <i class="fas fa-check-circle"></i>
                            <div>
                                <h3>Transparent Pricing</h3>
                                <p>No hidden fees or surprise charges. What you see is what you pay.</p>
                            </div>
                        </li>
                        <li>
                            <i class="fas fa-check-circle"></i>
                            <div>
                                <h3>24/7 Support</h3>
                                <p>Our dedicated customer service team is always here to help.</p>
                            </div>
                        </li>
                    </ul>
                </div>
                <div class="about-stats">
                    <div class="stat-box">
                        <i class="fas fa-car"></i>
                        <h3>500+</h3>
                        <p>Premium Vehicles</p>
                    </div>
                    <div class="stat-box">
                        <i class="fas fa-users"></i>
                        <h3>10,000+</h3>
                        <p>Happy Customers</p>
                    </div>
                    <div class="stat-box">
                        <i class="fas fa-building"></i>
                        <h3>50+</h3>
                        <p>Trusted Agencies</p>
                    </div>
                    <div class="stat-box">
                        <i class="fas fa-map-marker-alt"></i>
                        <h3>20+</h3>
                        <p>Cities Covered</p>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <section class="team-section">
        <div class="container">
            <h2>Our Leadership Team</h2>
            <div class="team-grid">
                <div class="team-member">
                    <img src="assets/images/team1.jpg" alt="CEO">
                    <h3>John Smith</h3>
                    <p class="position">CEO & Founder</p>
                    <p class="bio">With over 15 years in the automotive industry, John leads our vision for the future.</p>
                </div>
                <div class="team-member">
                    <img src="assets/images/team2.jpg" alt="CTO">
                    <h3>Sarah Johnson</h3>
                    <p class="position">Chief Technology Officer</p>
                    <p class="bio">Sarah ensures our platform provides the best user experience possible.</p>
                </div>
                <div class="team-member">
                    <img src="assets/images/team3.jpg" alt="COO">
                    <h3>Michael Chen</h3>
                    <p class="position">Chief Operations Officer</p>
                    <p class="bio">Michael oversees our day-to-day operations and agency partnerships.</p>
                </div>
            </div>
        </div>
    </section>

    <?php include 'includes/footer.php'; ?>
</body>
</html> 