<?php
session_start();
require_once 'db.php';

// Get car ID from URL
$car_id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

try {
    // Get car details with agency information
    $query = "SELECT c.*, u.name as agency_name, u.email as agency_email 
              FROM cars c 
              JOIN users u ON c.posted_by = u.id 
              WHERE c.id = ? AND c.status = 'available'";
    
    $stmt = $conn->prepare($query);
    if ($stmt === false) {
        throw new Exception("Failed to prepare query: " . $conn->error);
    }
    
    $stmt->bind_param('i', $car_id);
    
    if (!$stmt->execute()) {
        throw new Exception("Failed to execute query: " . $stmt->error);
    }
    
    $result = $stmt->get_result();
    $car = $result->fetch_assoc();
    
    if (!$car) {
        throw new Exception("Car not found");
    }
    
} catch (Exception $e) {
    error_log("Car details error: " . $e->getMessage());
    $error_message = "Sorry, we couldn't find the car you're looking for.";
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo isset($car) ? htmlspecialchars($car['title']) : 'Car Not Found'; ?> - RCar</title>
    <link rel="stylesheet" href="assets/style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
</head>
<body>
    <?php include 'includes/navbar.php'; ?>

    <div class="container">
        <?php if (isset($error_message)): ?>
            <div class="error-container">
                <i class="fas fa-exclamation-circle"></i>
                <h2>Error</h2>
                <p><?php echo htmlspecialchars($error_message); ?></p>
                <a href="catalog.php" class="btn-primary">Back to Catalog</a>
            </div>
        <?php else: ?>
            <div class="car-details-container">
                <div class="car-gallery">
                    <img src="<?php echo htmlspecialchars($car['image_url']); ?>" 
                         alt="<?php echo htmlspecialchars($car['title']); ?>"
                         class="main-image">
                </div>

                <div class="car-info-container">
                    <div class="car-header">
                        <h1><?php echo htmlspecialchars($car['title']); ?></h1>
                        <span class="car-category"><?php echo htmlspecialchars($car['category']); ?></span>
                    </div>

                    <div class="car-meta">
                        <div class="meta-item">
                            <i class="fas fa-car"></i>
                            <span><?php echo htmlspecialchars($car['make'] . ' ' . $car['model'] . ' ' . $car['year']); ?></span>
                        </div>
                        <div class="meta-item">
                            <i class="fas fa-map-marker-alt"></i>
                            <span><?php echo htmlspecialchars($car['location']); ?></span>
                        </div>
                        <div class="meta-item">
                            <i class="fas fa-building"></i>
                            <span>Listed by <?php echo htmlspecialchars($car['agency_name']); ?></span>
                        </div>
                        <div class="meta-item price">
                            <i class="fas fa-tag"></i>
                            <span>$<?php echo number_format($car['price_per_day'], 2); ?> / day</span>
                        </div>
                    </div>

                    <div class="car-description">
                        <h2>Description</h2>
                        <p><?php echo nl2br(htmlspecialchars($car['description'])); ?></p>
                    </div>

                    <?php if (!empty($car['features'])): ?>
                        <div class="car-features">
                            <h2>Features</h2>
                            <div class="features-grid">
                                <?php 
                                $features = json_decode($car['features'], true);
                                if (is_array($features)):
                                    foreach ($features as $feature): ?>
                                        <div class="feature-item">
                                            <i class="fas fa-check"></i>
                                            <span><?php echo htmlspecialchars($feature); ?></span>
                                        </div>
                                    <?php endforeach;
                                endif; ?>
                            </div>
                        </div>
                    <?php endif; ?>

                    <div class="booking-section">
                        <?php if (isset($_SESSION['user_id'])): ?>
                            <?php if ($_SESSION['role'] === 'agency'): ?>
                                <div class="agency-message">
                                    <i class="fas fa-info-circle"></i>
                                    <p>As an agency, you cannot book cars directly. Please ask your clients to book through their personal accounts.</p>
                                </div>
                            <?php else: ?>
                                <div class="booking-section">
                                    <?php if (isset($_SESSION['user_id'])): ?>
                                        <?php if ($_SESSION['role'] === 'agency'): ?>
                                            <div class="error-message">
                                                <i class="fas fa-exclamation-circle"></i>
                                                <p>Agencies are not allowed to book cars. Please use a regular user account to make bookings.</p>
                                            </div>
                                        <?php else: ?>
                                            <form action="process_booking.php" method="POST" class="booking-form">
                                                <input type="hidden" name="car_id" value="<?php echo $car['id']; ?>">
                                                
                                                <div class="form-group">
                                                    <label for="pickup_date">Pickup Date</label>
                                                    <input type="date" id="pickup_date" name="pickup_date" required 
                                                           min="<?php echo date('Y-m-d'); ?>">
                                                    <small id="pickup_date_error" class="error-text"></small>
                                                </div>
                                                
                                                <div class="form-group">
                                                    <label for="return_date">Return Date</label>
                                                    <input type="date" id="return_date" name="return_date" required>
                                                    <small id="return_date_error" class="error-text"></small>
                                                </div>
                                                
                                                <div class="form-group">
                                                    <label for="pickup_location">Pickup Location</label>
                                                    <input type="text" id="pickup_location" name="pickup_location" 
                                                           value="<?php echo htmlspecialchars($car['location']); ?>" required>
                                                    <small id="pickup_location_error" class="error-text"></small>
                                                </div>

                                                <button type="submit" class="btn-primary btn-book">
                                                    <i class="fas fa-calendar-check"></i> Book Now
                                                </button>
                                                
                                                <div id="booking-result" class="booking-result"></div>
                                            </form>

                                            <script>
                                            document.querySelector('.booking-form').addEventListener('submit', async function(e) {
                                                e.preventDefault();
                                                
                                                const formData = new FormData(this);
                                                const bookingResult = document.getElementById('booking-result');
                                                
                                                try {
                                                    const response = await fetch('process_booking.php', {
                                                        method: 'POST',
                                                        body: formData
                                                    });
                                                    
                                                    const result = await response.json();
                                                    
                                                    if (result.success) {
                                                        bookingResult.innerHTML = `
                                                            <div class="success-message">
                                                                <i class="fas fa-check-circle"></i>
                                                                <p>Booking successful! Your booking ID is: ${result.booking_id}</p>
                                                            </div>
                                                        `;
                                                    } else {
                                                        bookingResult.innerHTML = `
                                                            <div class="error-message">
                                                                <i class="fas fa-exclamation-circle"></i>
                                                                <p>${result.message}</p>
                                                            </div>
                                                        `;
                                                    }
                                                } catch (error) {
                                                    bookingResult.innerHTML = `
                                                        <div class="error-message">
                                                            <i class="fas fa-exclamation-circle"></i>
                                                            <p>An error occurred: ${error.message}</p>
                                                        </div>
                                                    `;
                                                }
                                            });
                                            </script>
                                        <?php endif; ?>
                                    <?php else: ?>
                                        <div class="login-prompt">
                                            <p>Please log in to book this car</p>
                                            <a href="login.php?redirect=<?php echo urlencode($_SERVER['REQUEST_URI']); ?>" class="btn-primary">
                                                <i class="fas fa-sign-in-alt"></i> Log In to Book
                                            </a>
                                        </div>
                                    <?php endif; ?>
                                </div>

                                <script>
                                function validateForm() {
                                    let pickupDate = document.getElementById('pickup_date').value;
                                    let returnDate = document.getElementById('return_date').value;
                                    let pickupLocation = document.getElementById('pickup_location').value;

                                    // Clear previous errors
                                    document.getElementById('pickup_date_error').textContent = '';
                                    document.getElementById('return_date_error').textContent = '';
                                    document.getElementById('pickup_location_error').textContent = '';

                                    // Validate dates
                                    if (pickupDate && returnDate) {
                                        let pickup = new Date(pickupDate);
                                        let returnDateObj = new Date(returnDate);
                                        let today = new Date();

                                        if (pickup < today) {
                                            document.getElementById('pickup_date_error').textContent = 'Pickup date cannot be in the past';
                                            return false;
                                        }

                                        if (returnDateObj < pickup) {
                                            document.getElementById('return_date_error').textContent = 'Return date must be after pickup date';
                                            return false;
                                        }
                                    }

                                    // Validate pickup location
                                    if (pickupLocation.trim() === '') {
                                        document.getElementById('pickup_location_error').textContent = 'Please enter a valid pickup location';
                                        return false;
                                    }

                                    return true;
                                }
                                </script>
                            <?php endif; ?>
                        <?php else: ?>
                            <div class="login-prompt">
                                <p>Please log in to book this car</p>
                                <a href="login.php?redirect=<?php echo urlencode($_SERVER['REQUEST_URI']); ?>" class="btn-primary">
                                    <i class="fas fa-sign-in-alt"></i> Log In to Book
                                </a>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        <?php endif; ?>
    </div>

    <style>
    .error-message {
        background-color: #fff3f3;
        border: 1px solid #ffcccc;
        color: #d32f2f;
        padding: 1rem;
        border-radius: 8px;
        margin-bottom: 1rem;
    }

    .error-message i {
        color: #d32f2f;
        margin-right: 0.5rem;
    }

    .agency-message {
        background-color: #f5f5f5;
        border: 1px solid #e0e0e0;
        color: #616161;
        padding: 1rem;
        border-radius: 8px;
        margin-bottom: 1rem;
    }

    .agency-message i {
        color: #616161;
        margin-right: 0.5rem;
        margin: 0;
        font-size: 16px;
    }

    .error-text {
        color: #d32f2f;
        font-size: 14px;
        margin-top: 5px;
        display: block;
    }

    .car-details-container {
        display: grid;
        grid-template-columns: 1fr 1fr;
        gap: 2rem;
        padding: 2rem 0;
    }

    .car-gallery {
        position: sticky;
        top: 2rem;
    }

    .main-image {
        width: 100%;
        height: 400px;
        object-fit: cover;
        border-radius: 8px;
    }

    .car-header {
        display: flex;
        align-items: center;
        justify-content: space-between;
        margin-bottom: 1.5rem;
    }

    .car-header h1 {
        margin: 0;
        font-size: 2rem;
        color: #333;
    }

    .car-category {
        background: #f0f2f5;
        padding: 0.5rem 1rem;
        border-radius: 20px;
        font-size: 0.875rem;
        color: #666;
    }

    .car-meta {
        display: grid;
        grid-template-columns: repeat(2, 1fr);
        gap: 1rem;
        margin-bottom: 2rem;
    }

    .meta-item {
        display: flex;
        align-items: center;
        gap: 0.5rem;
        padding: 1rem;
        background: #f8f9fa;
        border-radius: 8px;
    }

    .meta-item i {
        color: #3498db;
        font-size: 1.25rem;
    }

    .meta-item.price {
        font-size: 1.25rem;
        font-weight: 600;
        color: #2ecc71;
    }

    .car-description {
        margin-bottom: 2rem;
    }

    .car-description h2,
    .car-features h2 {
        font-size: 1.5rem;
        margin-bottom: 1rem;
        color: #333;
    }

    .features-grid {
        display: grid;
        grid-template-columns: repeat(auto-fill, minmax(200px, 1fr));
        gap: 1rem;
        margin-bottom: 2rem;
    }

    .feature-item {
        display: flex;
        align-items: center;
        gap: 0.5rem;
        padding: 0.5rem;
        background: #f8f9fa;
        border-radius: 4px;
    }

    .feature-item i {
        color: #2ecc71;
    }

    .booking-section {
        background: #f8f9fa;
        padding: 2rem;
        border-radius: 8px;
    }

    .booking-form {
        display: grid;
        gap: 1rem;
    }

    .form-group {
        display: flex;
        flex-direction: column;
        gap: 0.5rem;
    }

    .form-group label {
        font-weight: 500;
        color: #666;
    }

    .form-group input {
        padding: 0.75rem;
        border: 1px solid #ddd;
        border-radius: 4px;
        font-size: 1rem;
    }

    .btn-book {
        padding: 1rem;
        font-size: 1.125rem;
    }

    .login-prompt {
        text-align: center;
        padding: 2rem;
    }

    .login-prompt p {
        margin-bottom: 1rem;
        color: #666;
    }

    .error-container {
        text-align: center;
        padding: 4rem 2rem;
    }

    .error-container i {
        font-size: 3rem;
        color: #e74c3c;
        margin-bottom: 1rem;
    }

    @media (max-width: 768px) {
        .car-details-container {
            grid-template-columns: 1fr;
        }

        .car-gallery {
            position: static;
        }

        .main-image {
            height: 300px;
        }

        .car-meta {
            grid-template-columns: 1fr;
        }
    }
    </style>

    <script>
    document.addEventListener('DOMContentLoaded', function() {
        const pickupDate = document.getElementById('pickup_date');
        const returnDate = document.getElementById('return_date');

        if (pickupDate && returnDate) {
            pickupDate.addEventListener('change', function() {
                returnDate.min = this.value;
                if (returnDate.value && returnDate.value < this.value) {
                    returnDate.value = this.value;
                }
            });
        }
    });
    </script>
</body>
</html>
