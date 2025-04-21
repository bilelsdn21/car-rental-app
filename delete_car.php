<?php
session_start();
require_once 'db.php';

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: index.html");
    exit();
}

// Get car ID from URL
$car_id = (int)$_GET['id'];
$user_id = (int)$_SESSION['user_id'];
$role = $_SESSION['role'];

try {
    // Start transaction
    mysqli_autocommit($conn, FALSE);

    // Check if user is authorized to delete this car
    $stmt = $conn->prepare("SELECT posted_by, image_url FROM cars WHERE id = ?");
    if (!$stmt) {
        throw new Exception("Failed to prepare query: " . $conn->error);
    }
    
    $stmt->bind_param("i", $car_id);
    if (!$stmt->execute()) {
        throw new Exception("Failed to execute query: " . $stmt->error);
    }
    
    $result = $stmt->get_result();
    if (!$result) {
        throw new Exception("Failed to get result: " . $stmt->error);
    }
    
    $car = $result->fetch_assoc();
    
    if (!$car) {
        throw new Exception("Car not found");
    }

    // Check if user is authorized
    if ($role !== 'admin' && $car['posted_by'] != $user_id) {
        throw new Exception("You are not authorized to delete this car");
    }

    // Check for active bookings
    $stmt = $conn->prepare("SELECT COUNT(*) as booking_count FROM bookings WHERE car_id = ? AND status IN ('pending', 'confirmed')");
    if (!$stmt) {
        throw new Exception("Failed to prepare booking check query: " . $conn->error);
    }
    
    $stmt->bind_param("i", $car_id);
    if (!$stmt->execute()) {
        throw new Exception("Failed to execute booking check query: " . $stmt->error);
    }
    
    $result = $stmt->get_result();
    if (!$result) {
        throw new Exception("Failed to get booking check result: " . $stmt->error);
    }
    
    $booking = $result->fetch_assoc();
    if ($booking['booking_count'] > 0) {
        throw new Exception("Cannot delete car with active bookings. Please cancel all pending bookings first.");
    }

    // Delete the car
    $stmt = $conn->prepare("DELETE FROM cars WHERE id = ?");
    if (!$stmt) {
        throw new Exception("Failed to prepare delete query: " . $conn->error);
    }
    
    $stmt->bind_param("i", $car_id);
    if (!$stmt->execute()) {
        throw new Exception("Failed to delete car: " . $stmt->error);
    }

    // Commit transaction
    mysqli_commit($conn);
    
    // Redirect with success message
    header("Location: my_cars.php?success=1");
    exit();

} catch (Exception $e) {
    // Rollback transaction on error
    mysqli_rollback($conn);
    
    // Log the error
    error_log("Car deletion error - Car ID: $car_id, User ID: $user_id, Role: $role - Error: " . $e->getMessage());
    
    // Redirect with error message
    header("Location: my_cars.php?error=1");
    exit();
}
?>