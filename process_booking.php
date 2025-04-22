<?php
session_start();
require_once 'db.php';

// Show and log errors
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
ini_set('log_errors', 1);
ini_set('error_log', 'error_log.txt');
error_reporting(E_ALL);

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    error_log("User not logged in.");
    header('Location: login.php?error=Please log in to book a car');
    exit;
}

// Handle POST submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Sanitize input
    $car_id = isset($_POST['car_id']) ? (int)$_POST['car_id'] : 0;
    $pickup_date = trim($_POST['pickup_date']);
    $return_date = trim($_POST['return_date']);
    $pickup_location = trim($_POST['pickup_location']);
    $user_id = $_SESSION['user_id'];

    error_log("Received booking POST: car_id=$car_id, pickup=$pickup_date, return=$return_date");

    // Validate dates
    if (!preg_match('/^\d{4}-\d{2}-\d{2}$/', $pickup_date) || 
        !preg_match('/^\d{4}-\d{2}-\d{2}$/', $return_date)) {
        error_log("Invalid date format.");
        header("Location: car.php?id=$car_id&error=Invalid date format");
        exit;
    }

    try {
        $conn->begin_transaction();

        // Fetch car details
        $stmt = $conn->prepare("SELECT * FROM cars WHERE id = ?");
        $stmt->bind_param("i", $car_id);
        $stmt->execute();
        $car_result = $stmt->get_result();
        $car = $car_result->fetch_assoc();

        if (!$car) {
            throw new Exception("Car not found");
        }

        // Calculate total price
        $start = new DateTime($pickup_date);
        $end = new DateTime($return_date);
        $interval = $start->diff($end);
        $total_days = $interval->days + 1;
        $total_price = $total_days * $car['price_per_day'];

        // Check availability
        $query = "SELECT COUNT(*) as count FROM bookings 
                  WHERE car_id = ? 
                  AND status NOT IN ('cancelled')
                  AND (
                        (start_date <= ? AND end_date >= ?) OR 
                        (start_date <= ? AND end_date >= ?) OR 
                        (start_date >= ? AND end_date <= ?)
                  )";
        $stmt = $conn->prepare($query);
        $stmt->bind_param("issssss", $car_id, $pickup_date, $pickup_date, $return_date, $return_date, $pickup_date, $return_date);
        $stmt->execute();
        $result = $stmt->get_result();
        $overlap = $result->fetch_assoc();

        if ($overlap['count'] > 0) {
            throw new Exception("Car is not available during selected dates");
        }

        // Insert into bookings
        $stmt = $conn->prepare("INSERT INTO bookings (user_id, car_id, start_date, end_date, total_price, pickup_location, status)
                                VALUES (?, ?, ?, ?, ?, ?, 'pending')");
        $stmt->bind_param("iissds", $user_id, $car_id, $pickup_date, $return_date, $total_price, $pickup_location);
        if (!$stmt->execute()) {
            throw new Exception("Failed to insert booking: " . $stmt->error);
        }

        // Optional: update car status (if needed)
        $stmt = $conn->prepare("UPDATE cars SET status = 'rented' WHERE id = ?");
        $stmt->bind_param("i", $car_id);
        $stmt->execute();

        // Optional: insert notification (if table exists)
        try {
            $stmt = $conn->prepare("INSERT INTO notifications (user_id, type, message)
                                    VALUES (?, 'booking', CONCAT('New booking for ', ?, ' from ', ?, ' to ', ?))");
            $stmt->bind_param("isss", $car['posted_by'], $car['title'], $pickup_date, $return_date);
            $stmt->execute();
        } catch (Exception $e) {
            error_log("Notification failed: " . $e->getMessage());
            // Don't throw the exception, just log it
        }

        $conn->commit();
        error_log("Booking completed for car ID: $car_id by user ID: $user_id");

        header("Location: car.php?id=$car_id&success=1");
        exit;

    } catch (Exception $e) {
        error_log("Booking failed: " . $e->getMessage());
        $conn->rollback();
        header("Location: car.php?id=$car_id&error=" . urlencode($e->getMessage()));
        exit;
    }

} else {
    error_log("Invalid request method.");
    header("Location: catalog.php");
    exit;
}