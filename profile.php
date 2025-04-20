<?php
require_once 'session_init.php';
require_once 'config.php';

if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit();
}

$user_id = $_SESSION['user_id'];
$tab = isset($_GET['tab']) ? $_GET['tab'] : 'profile';

// Generate CSRF token if not exists
if (!isset($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}

// Fetch user's bookings
$stmt = $conn->prepare("
    SELECT b.*, c.make, c.model, c.year, c.image, c.price_per_day, c.pickup_location 
    FROM bookings b 
    JOIN cars c ON b.car_id = c.id 
    WHERE b.user_id = ? 
    ORDER BY b.created_at DESC
");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$bookings = $stmt->get_result();

// Fetch user details
$stmt = $conn->prepare("SELECT * FROM users WHERE id = ?");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$user = $stmt->get_result()->fetch_assoc();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Profile | RENTI</title>
    <link href="https://cdn.jsdelivr.net/npm/remixicon@4.3.0/fonts/remixicon.css" rel="stylesheet">
    <link rel="stylesheet" href="styles.css">
    <script src="script.js" defer></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        .profile-container {
            max-width: 1200px;
            margin: 2rem auto;
            padding: 0 1rem;
        }
        .user-info {
            background: white;
            padding: 2rem;
            border-radius: 10px;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
            margin-bottom: 2rem;
        }
        .orders-section {
            margin-top: 2rem;
        }
        .order-card {
            background: white;
            border-radius: 10px;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
            margin-bottom: 1.5rem;
            overflow: hidden;
            transition: transform 0.2s;
        }
        .order-card:hover {
            transform: translateY(-2px);
        }
        .order-header {
            background: #f8f9fa;
            padding: 1rem;
            border-bottom: 1px solid #e9ecef;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
        .order-content {
            padding: 1.5rem;
            display: flex;
            gap: 2rem;
        }
        .car-image {
            width: 200px;
            height: 150px;
            object-fit: cover;
            border-radius: 5px;
        }
        .order-details {
            flex: 1;
        }
        .detail-row {
            display: flex;
            gap: 1rem;
            margin-bottom: 0.5rem;
            align-items: center;
        }
        .detail-row i {
            width: 20px;
            color: #666;
        }
        .status-badge {
            padding: 0.25rem 0.75rem;
            border-radius: 15px;
            font-size: 0.875rem;
            font-weight: 500;
        }
        .status-pending {
            background: #fff3cd;
            color: #856404;
        }
        .status-confirmed {
            background: #d4edda;
            color: #155724;
        }
        .status-cancelled {
            background: #f8d7da;
            color: #721c24;
        }
        .cancel-btn {
            background: #dc3545;
            color: white;
            border: none;
            padding: 0.5rem 1rem;
            border-radius: 5px;
            cursor: pointer;
            transition: background-color 0.2s;
        }
        .cancel-btn:hover {
            background: #c82333;
        }
        .cancel-btn:disabled {
            background: #6c757d;
            cursor: not-allowed;
        }
        @media (max-width: 768px) {
            .order-content {
                flex-direction: column;
            }
            .car-image {
                width: 100%;
                height: 200px;
            }
        }
    </style>
</head>
<body>
    <header>
        <nav>
            <div class="nav__header">
                <div class="nav__logo">
                    <a href="index (3).php">Rcar</a>
                </div>
                <div class="nav__menu__btn" id="menu-btn">
                    <i class="ri-menu-line"></i>
                </div>
            </div>
            <ul class="nav__links">
                <li><a href="index (3).php">Home</a></li>
                <li><a href="catalog.php">Catalog</a></li>
                <li><a href="about_us.php">About Us</a></li>
                <li><a href="contact.php">Contact</a></li>
            </ul>
            <div class="nav__btn">
                <?php include 'check_login.php'; ?>
                <?php if (isLoggedIn()): ?>
                    <div class="user-menu">
                        <div class="user-icon">
                            <i class="ri-user-line"></i>
                        </div>
                        <div class="user-dropdown">
                            <div class="user-info">
                                <span><?php echo htmlspecialchars(getUserEmail()); ?></span>
                            </div>
                            <ul class="dropdown-menu">
                                <li><a href="profile.php">Profile</a></li>
                                <li><a href="profile.php?tab=orders">My Orders</a></li>
                                <li><a href="profile.php?tab=payments">Payment Methods</a></li>
                                <li><a href="logout.php">Logout</a></li>
                            </ul>
                        </div>
                    </div>
                <?php else: ?>
                    <a href="login(1).php"><button class="btn">Get Started</button></a>
                <?php endif; ?>
            </div>
        </nav>
    </header>

    <main class="profile-container">
        <div class="profile-header">
            <div class="profile-avatar">
                <i class="ri-user-line"></i>
            </div>
            <div class="profile-info">
                <h1><?php echo htmlspecialchars($user['username']); ?></h1>
                <p>Member since: <?php echo date('F Y'); ?></p>
                <p>Email: <?php echo htmlspecialchars($user['email']); ?></p>
            </div>
            <button class="profile-edit-btn" id="edit-profile-btn">Edit Profile</button>
        </div>

        <div class="profile-tabs">
            <div class="profile-tab active" data-tab="personal">Personal Info</div>
            <div class="profile-tab" data-tab="orders">My Orders</div>
            <div class="profile-tab" data-tab="payments">Payment Methods</div>
        </div>

        <div class="profile-content">
            <?php if ($tab === 'profile'): ?>
                <div class="profile-section active" id="personal-section">
                    <form id="profile-form">
                        <div class="form-group">
                            <label for="fullname">Full Name</label>
                            <input type="text" id="fullname" name="fullname" value="<?php echo htmlspecialchars($user['username']); ?>">
                        </div>
                        <div class="form-group">
                            <label for="email">Email</label>
                            <input type="email" id="email" name="email" value="<?php echo htmlspecialchars($user['email']); ?>" readonly disabled>
                        </div>
                        <div class="form-group">
                            <label for="phone">Phone Number</label>
                            <input type="tel" id="phone" name="phone" value="<?php echo htmlspecialchars($user['phone']); ?>">
                        </div>
                        <div class="form-group">
                            <label for="address">Address</label>
                            <input type="text" id="address" name="address" value="<?php echo htmlspecialchars($user['address']); ?>">
                        </div>
                        <div class="form-group">
                            <label for="license">Driver's License Number</label>
                            <input type="text" id="license" name="license" value="<?php echo htmlspecialchars($user['license']); ?>">
                        </div>
                        <button type="submit" class="save-btn">Save Changes</button>
                    </form>
                </div>
            <?php elseif ($tab === 'orders'): ?>
                <div class="profile-section" id="orders-section">
                    <h2>My Orders</h2>
                    <?php if ($bookings->num_rows > 0): ?>
                        <div class="orders__list">
                            <?php while($booking = $bookings->fetch_assoc()): ?>
                                <div class="order__card">
                                    <div class="order__image">
                                        <img src="<?php echo htmlspecialchars($booking['image']); ?>" 
                                             alt="<?php echo htmlspecialchars($booking['make'] . ' ' . $booking['model']); ?>"
                                             onerror="this.src='assets/default-car.jpg'" class="car-image">
                                    </div>
                                    <div class="order__details">
                                        <h3><?php echo htmlspecialchars($booking['make'] . ' ' . $booking['model']); ?></h3>
                                        <div class="order__info">
                                            <div class="info__group">
                                                <i class="ri-file-list-line"></i>
                                                <span><strong>Booking ID:</strong> #<?php echo $booking['id']; ?></span>
                                            </div>
                                            <div class="info__group">
                                                <i class="ri-calendar-line"></i>
                                                <span><strong>Pickup:</strong> <?php echo date('M d, Y', strtotime($booking['pickup_date'])); ?></span>
                                            </div>
                                            <div class="info__group">
                                                <i class="ri-calendar-check-line"></i>
                                                <span><strong>Return:</strong> <?php echo date('M d, Y', strtotime($booking['return_date'])); ?></span>
                                            </div>
                                            <div class="info__group">
                                                <i class="ri-map-pin-line"></i>
                                                <span><strong>Location:</strong> <?php echo htmlspecialchars($booking['pickup_location']); ?></span>
                                            </div>
                                            <div class="info__group">
                                                <i class="ri-money-dollar-circle-line"></i>
                                                <span><strong>Total:</strong> $<?php echo number_format($booking['total_price'], 2); ?></span>
                                            </div>
                                        </div>
                                        <div class="order__status">
                                            <span class="status-badge status-<?php echo strtolower($booking['status']); ?>">
                                                <?php echo ucfirst($booking['status']); ?>
                                            </span>
                                            <?php if ($booking['status'] === 'pending'): ?>
                                                <button class="btn btn-cancel" onclick="cancelBooking(<?php echo $booking['id']; ?>)" data-csrf="<?php echo $_SESSION['csrf_token']; ?>">
                                                    <i class="fas fa-times"></i> Cancel Booking
                                                </button>
                                            <?php endif; ?>
                                        </div>
                                    </div>
                                </div>
                            <?php endwhile; ?>
                        </div>
                    <?php else: ?>
                        <div class="empty-state">
                            <i class="ri-shopping-bag-line"></i>
                            <h3>No Orders Yet</h3>
                            <p>You haven't made any bookings yet. Start exploring our catalog!</p>
                            <a href="catalog.php" class="btn">Browse Cars</a>
                        </div>
                    <?php endif; ?>
                </div>
            <?php elseif ($tab === 'payments'): ?>
                <div class="profile-section" id="payments-section">
                    <div class="payment-card">
                        <div class="payment-icon">
                            <i class="ri-bank-card-line"></i>
                        </div>
                        <div class="payment-details">
                            <h3>Visa ending in 4242</h3>
                            <p>Expires: 12/25</p>
                        </div>
                        <button class="profile-edit-btn">Edit</button>
                    </div>
                    <div class="payment-card">
                        <div class="payment-icon">
                            <i class="ri-bank-card-line"></i>
                        </div>
                        <div class="payment-details">
                            <h3>Mastercard ending in 8888</h3>
                            <p>Expires: 08/26</p>
                        </div>
                        <button class="profile-edit-btn">Edit</button>
                    </div>
                    <button class="add-payment-btn">
                        <i class="ri-add-line"></i>
                        Add Payment Method
                    </button>
                </div>
            <?php endif; ?>
        </div>
    </main>

    <footer>
        <div class="section__container footer__container">
            <div class="footer__col">
                <h4>Resources</h4>
                <ul class="footer__links">
                    <li><a href="#">Installation Manual</a></li>
                    <li><a href="#">Release Note</a></li>
                    <li><a href="#">Community Help</a></li>
                </ul>
            </div>
            <div class="footer__col">
                <h4>Company</h4>
                <ul class="footer__links">
                    <li><a href="#">About Us</a></li>
                    <li><a href="#">Career</a></li>
                    <li><a href="#">Press</a></li>
                    <li><a href="#">Support</a></li>
                </ul>
            </div>
            <div class="footer__col">
                <h4>Product</h4>
                <ul class="footer__links">
                    <li><a href="#">Demo</a></li>
                    <li><a href="#">Security</a></li>
                    <li><a href="#">FAQ</a></li>
                    <li><a href="#">Features</a></li>
                </ul>
            </div>
            <div class="footer__col">
                <h4>Follow Us</h4>
                <ul class="footer__socials">
                    <li>
                        <a href="#"><i class="ri-facebook-fill"></i></a>
                    </li>
                    <li>
                        <a href="#"><i class="ri-twitter-fill"></i></a>
                    </li>
                    <li>
                        <a href="#"><i class="ri-linkedin-fill"></i></a>
                    </li>
                </ul>
            </div>
        </div>
        <div class="footer__bar">
            Copyright © 2024 Web Design Mastery. All rights reserved.
        </div>
    </footer>

    <script>
    function cancelBooking(bookingId) {
        if (!confirm('Are you sure you want to cancel this booking?')) {
            return;
        }

        const csrfToken = document.querySelector(`button[onclick="cancelBooking(${bookingId})"]`).dataset.csrf;
        
        fetch('process_booking.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/x-www-form-urlencoded',
            },
            body: `action=cancel&booking_id=${bookingId}&csrf_token=${csrfToken}`
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                alert(data.message);
                location.reload();
            } else {
                alert(data.message || 'Failed to cancel booking');
            }
        })
        .catch(error => {
            console.error('Error:', error);
            alert('An error occurred while cancelling the booking');
        });
    }
    </script>
</body>
</html>
