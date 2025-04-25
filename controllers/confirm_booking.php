<?php
session_start();
require_once 'db.php';

// Check if user is logged in and is an agency
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'agency') {
    header('Location: login.php');
    exit();
}

$success_message = $error_message = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $booking_id = $_POST['booking_id'] ?? '';
    $action = $_POST['action'] ?? '';
    
    if (empty($booking_id)) {
        $error_message = "Invalid booking ID";
    } else {
        try {
            // Get booking details
            $stmt = $conn->prepare("SELECT b.*, u.name as user_name, c.title as car_title 
                                  FROM bookings b 
                                  JOIN users u ON b.user_id = u.id 
                                  JOIN cars c ON b.car_id = c.id 
                                  WHERE b.id = ? AND c.posted_by = ?");
            $stmt->bind_param('ii', $booking_id, $_SESSION['user_id']);
            $stmt->execute();
            $result = $stmt->get_result();
            $booking = $result->fetch_assoc();

            if (!$booking) {
                $error_message = "Booking not found or you don't have permission to manage this booking";
            } else {
                // Update booking status
                $new_status = $action === 'accept' ? 'confirmed' : 'cancelled';
                $confirmed = $action === 'accept';

                $stmt = $conn->prepare("UPDATE bookings SET status = ?, confirmed = ? WHERE id = ?");
                $stmt->bind_param('sii', $new_status, $confirmed, $booking_id);
                
                if ($stmt->execute()) {


                    $success_message = "Booking status updated successfully!";
                } else {
                    throw new Exception("Failed to update booking status");
                }
            }
        } catch (Exception $e) {
            $error_message = "Failed to update booking status. Please try again.";
        }
    }
}

// Get pending bookings
try {
    $stmt = $conn->prepare("SELECT b.*, u.name as user_name, c.title as car_title 
                          FROM bookings b 
                          JOIN users u ON b.user_id = u.id 
                          JOIN cars c ON b.car_id = c.id 
                          WHERE c.posted_by = ? AND b.status = 'pending'
                          ORDER BY b.created_at DESC");
    $stmt->bind_param('i', $_SESSION['user_id']);
    $stmt->execute();
    $pending_bookings = $stmt->get_result();
} catch (Exception $e) {
    $error_message = "Failed to load bookings. Please try again.";
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Confirm Bookings - RCar</title>
    <link rel="stylesheet" href="assets/style.css">
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
                <a href="dashboard.php">
                    <i class="fas fa-home"></i>
                    <span>Dashboard</span>
                </a>
                <a href="my_cars.php">
                    <i class="fas fa-car"></i>
                    <span>My Cars</span>
                </a>
                <a href="bookings.php">
                    <i class="fas fa-calendar-alt"></i>
                    <span>Bookings</span>
                </a>
                <a href="settings.php">
                    <i class="fas fa-cog"></i>
                    <span>Settings</span>
                </a>
            </nav>
        </div>

        <div class="dashboard-content">
            <div class="dashboard-header">
                <h1>Pending Bookings</h1>
            </div>

            <div class="bookings-container">
                <?php if ($success_message): ?>
                    <div class="success">
                        <?php echo htmlspecialchars($success_message); ?>
                    </div>
                <?php endif; ?>

                <?php if ($error_message): ?>
                    <div class="error">
                        <?php echo htmlspecialchars($error_message); ?>
                    </div>
                <?php endif; ?>

                <?php if ($pending_bookings->num_rows > 0): ?>
                    <div class="bookings-list">
                        <?php while ($booking = $pending_bookings->fetch_assoc()): ?>
                            <div class="booking-card">
                                <div class="booking-header">
                                    <h3><?php echo htmlspecialchars($booking['car_title']); ?></h3>
                                    <span class="booking-status pending">Pending</span>
                                </div>
                                
                                <div class="booking-details">
                                    <p><strong>Customer:</strong> <?php echo htmlspecialchars($booking['user_name']); ?></p>
                                    <p><strong>Start Date:</strong> <?php echo date('F j, Y', strtotime($booking['start_date'])); ?></p>
                                    <p><strong>End Date:</strong> <?php echo date('F j, Y', strtotime($booking['end_date'])); ?></p>
                                    <p><strong>Total Price:</strong> $<?php echo number_format($booking['total_price'], 2); ?></p>
                                </div>

                                <div class="booking-actions">
                                    <form method="POST" class="confirm-form">
                                        <input type="hidden" name="booking_id" value="<?php echo $booking['id']; ?>">
                                        <input type="hidden" name="action" value="accept">
                                        <button type="submit" class="btn-primary">Accept Booking</button>
                                    </form>

                                    <form method="POST" class="reject-form">
                                        <input type="hidden" name="booking_id" value="<?php echo $booking['id']; ?>">
                                        <input type="hidden" name="action" value="reject">
                                        <button type="submit" class="btn-danger">Reject Booking</button>
                                    </form>
                                </div>
                            </div>
                        <?php endwhile; ?>
                    </div>
                <?php else: ?>
                    <div class="no-bookings">
                        <p>No pending bookings at the moment.</p>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>


</body>
</html>
