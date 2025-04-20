<?php
session_start();
require_once 'db.php';

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit();
}

// Handle booking cancellation
if (isset($_POST['cancel_booking']) && isset($_POST['booking_id'])) {
    $booking_id = (int)$_POST['booking_id'];
    
    try {
        // First, get the booking details to check permissions
        $stmt = $conn->prepare("SELECT b.*, c.status as car_status 
                              FROM bookings b 
                              JOIN cars c ON b.car_id = c.id 
                              WHERE b.id = ?");
        $stmt->bind_param('i', $booking_id);
        $stmt->execute();
        $result = $stmt->get_result();
        $booking = $result->fetch_assoc();
        
        if (!$booking) {
            throw new Exception("Booking not found.");
        }

        // Check permissions
        if ($_SESSION['role'] === 'admin') {
            // Admin can cancel any booking
        } else if ($_SESSION['role'] === 'agency') {
            // Agency can only cancel their own bookings
            $stmt = $conn->prepare("SELECT id FROM cars WHERE id = ? AND posted_by = ?");
            $stmt->bind_param('ii', $booking['car_id'], $_SESSION['user_id']);
            $stmt->execute();
            if (!$stmt->get_result()->fetch_assoc()) {
                throw new Exception("You don't have permission to cancel this booking.");
            }
        } else {
            // Regular user can only cancel their own bookings
            if ($booking['user_id'] != $_SESSION['user_id']) {
                throw new Exception("You can only cancel your own bookings.");
            }
        }

        // Check if booking can be cancelled
        if (!in_array($booking['status'], ['pending', 'confirmed'])) {
            throw new Exception("This booking cannot be cancelled as it's already " . $booking['status']);
        }

        // Start transaction
        $conn->begin_transaction();

        // Update booking status
        $stmt = $conn->prepare("UPDATE bookings SET status = 'cancelled' WHERE id = ?");
        $stmt->bind_param('i', $booking_id);
        if (!$stmt->execute()) {
            throw new Exception("Failed to cancel booking.");
        }

        // Update car status
        $stmt = $conn->prepare("UPDATE cars SET status = 'available' WHERE id = ?");
        $stmt->bind_param('i', $booking['car_id']);
        if (!$stmt->execute()) {
            throw new Exception("Failed to update car status.");
        }

        // Commit transaction
        $conn->commit();

        // Success
        $success_message = "Booking cancelled successfully.";
        
    } catch (Exception $e) {
        // Rollback transaction on error
        if ($conn->in_transaction) {
            $conn->rollback();
        }
        
        // Log error
        error_log("Booking cancellation error for ID " . $booking_id . ": " . $e->getMessage());
        
        // Set error message
        $error_message = $e->getMessage();
    }
}

// Fetch bookings based on user role
try {
    if (isset($_SESSION['role']) && $_SESSION['role'] === 'admin') {
        // For admin, show all bookings
        $query = "SELECT b.*, c.make, c.model, c.year, c.image_url, c.price_per_day, 
                  c.location, u1.name as agency_name, u2.name as user_name 
                  FROM bookings b 
                  JOIN cars c ON b.car_id = c.id 
                  JOIN users u1 ON c.posted_by = u1.id 
                  JOIN users u2 ON b.user_id = u2.id 
                  ORDER BY b.created_at DESC";
        $result = $conn->query($query);
        $bookings = $result ? $result->fetch_all(MYSQLI_ASSOC) : [];
    } else if (isset($_SESSION['role']) && $_SESSION['role'] === 'agency') {
        // For agencies, show bookings for their cars
        $query = "SELECT b.*, c.make, c.model, c.year, c.image_url, c.price_per_day, 
                  c.location, u1.name as user_name, u1.email as user_email,
                  u2.name as agency_name 
                  FROM bookings b 
                  JOIN cars c ON b.car_id = c.id 
                  JOIN users u1 ON b.user_id = u1.id 
                  JOIN users u2 ON c.posted_by = u2.id 
                  WHERE c.posted_by = ? 
                  ORDER BY b.created_at DESC";
        $stmt = $conn->prepare($query);
        $stmt->bind_param('i', $_SESSION['user_id']);
        $stmt->execute();
        $result = $stmt->get_result();
        $bookings = $result->fetch_all(MYSQLI_ASSOC);
    } else {
        // For regular users, show their bookings
        $query = "SELECT b.*, c.make, c.model, c.year, c.image_url, c.price_per_day, 
                  c.location, u1.name as agency_name,
                  u2.name as user_name 
                  FROM bookings b 
                  JOIN cars c ON b.car_id = c.id 
                  JOIN users u1 ON c.posted_by = u1.id 
                  JOIN users u2 ON b.user_id = u2.id
                  WHERE b.user_id = ? 
                  ORDER BY b.created_at DESC";
        $stmt = $conn->prepare($query);
        $stmt->bind_param('i', $_SESSION['user_id']);
        $stmt->execute();
        $result = $stmt->get_result();
        $bookings = $result->fetch_all(MYSQLI_ASSOC);
    }
} catch (Exception $e) {
    error_log("Bookings error: " . $e->getMessage());
    $error_message = "Sorry, we encountered an error while loading your bookings. Please try again later.";
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>My Bookings - RCar</title>
    <link rel="stylesheet" href="assets/style.css">
    <link rel="stylesheet" href="assets/bookings-style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
</head>
<body>
    <?php include 'includes/navbar.php'; ?>

    <div class="dashboard">
        <div class="sidebar">
            <div class="sidebar-header">
                <h2>Dashboard</h2>
            </div>
            <nav class="sidebar-nav">
                <a href="dashboard.php" class="<?php echo basename($_SERVER['PHP_SELF']) === 'dashboard.php' ? 'active' : ''; ?>">
                    <i class="fas fa-home"></i> Dashboard
                </a>
                <?php if ($_SESSION['role'] === 'admin'): ?>
                    <a href="users.php" class="<?php echo basename($_SERVER['PHP_SELF']) === 'users.php' ? 'active' : ''; ?>">
                        <i class="fas fa-users"></i> Users
                    </a>
                <?php endif; ?>
                <?php if ($_SESSION['role'] === 'agency'): ?>
                    <a href="my_cars.php" class="<?php echo basename($_SERVER['PHP_SELF']) === 'my_cars.php' ? 'active' : ''; ?>">
                        <i class="fas fa-car"></i> My Cars
                    </a>
                <?php endif; ?>
                <a href="bookings.php" class="<?php echo basename($_SERVER['PHP_SELF']) === 'bookings.php' ? 'active' : ''; ?>">
                    <i class="fas fa-calendar"></i> Bookings
                </a>
                <a href="settings.php" class="<?php echo basename($_SERVER['PHP_SELF']) === 'settings.php' ? 'active' : ''; ?>">
                    <i class="fas fa-gear"></i> Settings
                </a>
            </nav>
        </div>
        <div class="dashboard-content">
            <div class="dashboard-header">
                <h1><?php echo (isset($_SESSION['role']) && $_SESSION['role'] === 'agency') ? 'Booking Requests' : 'My Bookings'; ?></h1>
            </div>

            <?php if (isset($success_message)): ?>
                <div class="success">
                    <?php echo htmlspecialchars($success_message); ?>
                </div>
            <?php endif; ?>

            <?php if (isset($error_message)): ?>
                <div class="error">
                    <?php echo htmlspecialchars($error_message); ?>
                </div>
            <?php endif; ?>

            <?php if (empty($bookings)): ?>
                <div class="no-results">
                    <div class="empty-state">
                        <i class="fas fa-calendar-times"></i>
                        <h2>No Bookings Yet</h2>
                        <p>Your rental journey begins here. Explore our premium car collection and make your first booking.</p>
                        <a href="catalog.php" class="btn-primary">Start Exploring</a>
                    </div>
                </div>
            <?php else: ?>
                <?php if (isset($_SESSION['role']) && $_SESSION['role'] === 'admin'): ?>
                <div class="bookings-admin-grid">
                    <?php foreach ($bookings as $booking): ?>
                        <article class="booking-admin-card">
                            <div class="booking-admin-imgbox">
                                <img src="<?php echo htmlspecialchars($booking['image_url']); ?>" alt="<?php echo htmlspecialchars($booking['make'] . ' ' . $booking['model']); ?>">
                                <span class="booking-status <?php echo strtolower($booking['status']); ?>">
                                    <?php echo htmlspecialchars(ucfirst($booking['status'])); ?>
                                </span>
                            </div>
                            <div class="booking-admin-content">
                                <header class="booking-admin-header">
                                    <h2><?php echo htmlspecialchars($booking['make'] . ' ' . $booking['model'] . ' (' . $booking['year'] . ')'); ?></h2>
                                    <span class="booking-admin-id">#<?php echo $booking['id']; ?></span>
                                </header>
                                <div class="booking-admin-meta">
                                    <span><i class="fas fa-user"></i> <b>User:</b> <?php echo htmlspecialchars($booking['user_name']); ?></span>
                                    <span><i class="fas fa-building"></i> <b>Agency:</b> <?php echo htmlspecialchars($booking['agency_name']); ?></span>
                                    <span><i class="fas fa-map-marker-alt"></i> <b>Location:</b> <?php echo htmlspecialchars($booking['location']); ?></span>
                                </div>
                                <div class="booking-admin-dates">
                                    <span><i class="fas fa-calendar"></i> <?php echo date('M d, Y', strtotime($booking['start_date'])); ?> - <?php echo date('M d, Y', strtotime($booking['end_date'])); ?></span>
                                </div>
                                <div class="booking-admin-pricing">
                                    <span><i class="fas fa-tag"></i> $<?php echo number_format($booking['price_per_day'], 2); ?> / day</span>
                                    <span><i class="fas fa-dollar-sign"></i> <b>Total:</b> $<?php echo number_format($booking['total_price'], 2); ?></span>
                                </div>
                            </div>
                        </article>
                    <?php endforeach; ?>
                </div>
                <?php else: ?>
                <div class="bookings-grid">
                    <?php foreach ($bookings as $booking): ?>
                        <div class="booking-card">
                            <div class="booking-image">
                                <img src="<?php echo htmlspecialchars($booking['image_url']); ?>" 
                                     alt="<?php echo htmlspecialchars($booking['make'] . ' ' . $booking['model']); ?>">
                                <span class="booking-status <?php echo strtolower($booking['status']); ?>">
                                    <?php echo htmlspecialchars(ucfirst($booking['status'])); ?>
                                </span>
                            </div>
                            <div class="booking-details">
                                <div class="car-info">
                                    <h3><?php echo htmlspecialchars($booking['make'] . ' ' . $booking['model']); ?></h3>
                                    <p class="agency">By <?php echo htmlspecialchars($booking['user_name']); ?></p>
                                </div>
                                <div class="booking-info-grid">
                                    <div class="info-item">
                                        <i class="fas fa-calendar"></i>
                                        <span><?php echo date('M d, Y', strtotime($booking['start_date'])); ?> - 
                                              <?php echo date('M d, Y', strtotime($booking['end_date'])); ?></span>
                                    </div>
                                    <div class="info-item">
                                        <i class="fas fa-map-marker-alt"></i>
                                        <span><?php echo htmlspecialchars($booking['location']); ?></span>
                                    </div>
                                    <div class="info-item price">
                                        <i class="fas fa-tag"></i>
                                        <span>$<?php echo number_format($booking['price_per_day'], 2); ?> / day</span>
                                    </div>
                                    <div class="info-item total">
                                        <i class="fas fa-dollar-sign"></i>
                                        <span>Total: $<?php echo number_format($booking['total_price'], 2); ?></span>
                                    </div>
                                </div>
                                <?php if (in_array($booking['status'], ['pending', 'confirmed'])): ?>
                                    <div class="booking-actions">
                                        <form method="POST" class="cancel-form">
                                            <input type="hidden" name="booking_id" value="<?php echo $booking['id']; ?>">
                                            <button type="submit" name="cancel_booking" class="btn-danger" 
                                                    onclick="return confirm('Are you sure you want to cancel this booking?')">
                                                <i class="fas fa-times"></i> Cancel Booking
                                            </button>
                                        </form>
                                    </div>
                                <?php endif; ?>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
                <?php endif; ?>
            <?php endif; ?>
        </div>
    </div>

<?php // Ensure all PHP blocks are closed correctly ?>

</body>
</html>