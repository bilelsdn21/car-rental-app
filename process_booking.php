<?php
// Set content type to JSON
header('Content-Type: application/json');

// Error reporting
error_reporting(E_ALL);
ini_set('display_errors', 0);

session_start();
require_once 'db.php';

// Check if request method is POST
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['success' => false, 'message' => 'Method not allowed']);
    exit;
}

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    http_response_code(401);
    echo json_encode(['success' => false, 'message' => 'Please log in to book a car']);
    exit;
}

// Check if user is agency
if ($_SESSION['role'] === 'agency') {
    http_response_code(403);
    echo json_encode(['success' => false, 'message' => 'Agencies cannot book cars']);
    exit;
}

// Get and validate input
$car_id = (int)$_POST['car_id'];
$pickup_date = trim($_POST['pickup_date']);
$return_date = trim($_POST['return_date']);
$pickup_location = trim($_POST['pickup_location']);
$user_id = (int)$_SESSION['user_id'];

// Validate dates
if (!preg_match('/^\d{4}-\d{2}-\d{2}$/', $pickup_date) || 
    !preg_match('/^\d{4}-\d{2}-\d{2}$/', $return_date)) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'Invalid date format. Please use YYYY-MM-DD format.']);
    exit;
}

// Convert dates to DateTime objects
$start = DateTime::createFromFormat('Y-m-d', $pickup_date);
$end = DateTime::createFromFormat('Y-m-d', $return_date);
$today = new DateTime();

if (!$start || !$end) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'Invalid date format. Please use YYYY-MM-DD format.']);
    exit;
}

// Validate dates
if ($start < $today) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'Pickup date cannot be in the past']);
    exit;
}

if ($end < $start) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'Return date must be after pickup date']);
    exit;
}

// Calculate total price
$interval = $start->diff($end);
$total_days = $interval->days + 1;
$total_price = $total_days * $car['price_per_day'];

try {
    // Start transaction
    mysqli_autocommit($conn, FALSE);

    // Get car details including agency ID
    $query = "SELECT c.*, c.posted_by as agency_id, c.price_per_day, u.name as agency_name 
             FROM cars c 
             JOIN users u ON c.posted_by = u.id 
             WHERE c.id = ? AND c.status = 'available'";
    
    $stmt = $conn->prepare($query);
    if (!$stmt) {
        throw new Exception("Failed to prepare query: " . $conn->error);
    }
    
    if (!$stmt->bind_param('i', $car_id)) {
        throw new Exception("Failed to bind parameters: " . $stmt->error);
    }
    
    if (!$stmt->execute()) {
        throw new Exception("Failed to execute query: " . $stmt->error);
    }
    
    $result = $stmt->get_result();
    if (!$result) {
        throw new Exception("Failed to get result: " . $stmt->error);
    }
    
    $car = $result->fetch_assoc();
    if (!$car) {
        throw new Exception("Car not found or not available");
    }

    // Check if car is already booked for the selected dates
    $stmt = $conn->prepare("
        SELECT COUNT(*) as booking_count 
        FROM bookings 
        WHERE car_id = ? 
        AND status = 'pending' 
        AND ((start_date <= ? AND end_date >= ?) 
             OR (start_date <= ? AND end_date >= ?) 
             OR (start_date >= ? AND end_date <= ?))
    ");
    
    if (!$stmt) {
        throw new Exception("Failed to prepare query: " . $conn->error);
    }
    
    if (!$stmt->bind_param('issssss', $car_id, $pickup_date, $pickup_date, $return_date, $return_date, $pickup_date, $return_date)) {
        throw new Exception("Failed to bind parameters: " . $stmt->error);
    }
    
    if (!$stmt->execute()) {
        throw new Exception("Failed to execute query: " . $stmt->error);
    }
    
    $result = $stmt->get_result();
    if (!$result) {
        throw new Exception("Failed to get result: " . $stmt->error);
    }
    
    $booking = $result->fetch_assoc();
    if ($booking['booking_count'] > 0) {
        throw new Exception("Car is already booked for the selected dates");
    }

    // Insert booking
    $query = "INSERT INTO bookings (user_id, car_id, start_date, end_date, total_price, pickup_location) 
             VALUES (?, ?, ?, ?, ?, ?)";
    
    $stmt = $conn->prepare($query);
    if (!$stmt) {
        throw new Exception("Failed to prepare query: " . $conn->error);
    }
    
    if (!$stmt->bind_param('iissss', $user_id, $car_id, $pickup_date, $return_date, $total_price, $pickup_location)) {
        throw new Exception("Failed to bind parameters: " . $stmt->error);
    }
    
    if (!$stmt->execute()) {
        throw new Exception("Failed to execute query: " . $stmt->error);
    }
    
    $booking_id = $conn->insert_id;


    if (!$booking_id) {
        throw new Exception("Failed to get booking ID");
    }

    // Commit transaction
    $conn->commit();
    
    // Send booking confirmation email
    $to = $_SESSION['email'];
    $subject = 'Car Booking Confirmation';
    $message = "Thank you for booking with us!\n\n".
              "Booking Details:\n".
              "Car: {$car['make']} {$car['model']}\n".
              "Pickup Date: {$pickup_date}\n".
              "Return Date: {$return_date}\n".
              "Total Price: $".number_format($total_price, 2)."\n\n".
              "Booking ID: {$booking_id}\n\n".
              "Thank you for choosing our services!";
    
    mail($to, $subject, $message);
    
    echo json_encode([
        'success' => true,
        'message' => 'Booking successful!',
        'booking_id' => $booking_id
    ]);

} catch (Exception $e) {
    // Rollback transaction on error
    $conn->rollback();
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'message' => 'An error occurred: ' . $e->getMessage()
    ]);
}

// Close connection
$conn->close();
exit();

// Handle booking cancellation
if (isset($_POST['action']) && $_POST['action'] === 'cancel') {
    // Verify CSRF token
    if (!isset($_POST['csrf_token']) || !verifyCSRFToken($_POST['csrf_token'])) {
        echo json_encode(['success' => false, 'message' => 'Invalid security token']);
        exit;
    }

    // Check if booking ID is provided
    if (!isset($_POST['booking_id'])) {
        echo json_encode(['success' => false, 'message' => 'Booking ID is required']);
        exit;
    }

    $booking_id = intval($_POST['booking_id']);

    // Verify that the booking belongs to the current user
    $stmt = $conn->prepare("SELECT user_id, status FROM bookings WHERE id = ?");
    $stmt->bind_param("i", $booking_id);
    $stmt->execute();
    $result = $stmt->get_result();
    $booking = $result->fetch_assoc();

    if (!$booking) {
        echo json_encode(['success' => false, 'message' => 'Booking not found']);
        exit;
    }

    if ($booking['user_id'] != $_SESSION['user_id']) {
        echo json_encode(['success' => false, 'message' => 'Unauthorized access']);
        exit;
    }

    if ($booking['status'] !== 'pending') {
        echo json_encode(['success' => false, 'message' => 'Only pending bookings can be cancelled']);
        exit;
    }

    // Start transaction
    $conn->begin_transaction();

    try {
        // Update booking status to cancelled
        $stmt = $conn->prepare("UPDATE bookings SET status = 'cancelled' WHERE id = ?");
        $stmt->bind_param("i", $booking_id);
        
        if (!$stmt->execute()) {
            throw new Exception("Failed to update booking status");
        }

        // Commit transaction
        $conn->commit();
        echo json_encode(['success' => true, 'message' => 'Booking cancelled successfully']);
    } catch (Exception $e) {
        // Rollback transaction on error
        $conn->rollback();
        echo json_encode(['success' => false, 'message' => $e->getMessage()]);
    }
    exit;
}
?>