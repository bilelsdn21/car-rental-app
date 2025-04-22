<?php
session_start();
require_once 'db.php';

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

// Get car ID from URL
$car_id = (int)$_GET['id'];
$user_id = (int)$_SESSION['user_id'];
$role = $_SESSION['role'];

// Debug logging
error_log("Delete request - Car ID: $car_id, User ID: $user_id, Role: $role");

try {
    // Get car details and verify ownership
    $stmt = $conn->prepare("SELECT id, posted_by FROM cars WHERE id = ?");
    if (!$stmt) {
        throw new Exception("Failed to prepare query: " . $conn->error);
    }
    
    $stmt->bind_param("i", $car_id);
    if (!$stmt->execute()) {
        throw new Exception("Failed to execute query: " . $conn->error);
    }
    
    $result = $stmt->get_result();
    if (!$result) {
        throw new Exception("Failed to get result: " . $conn->error);
    }
    
    $car = $result->fetch_assoc();
    
    if (!$car) {
        throw new Exception("Car not found");
    }

    // Check if user is authorized
    if ($role !== 'admin' && $car['posted_by'] != $user_id) {
        throw new Exception("You are not authorized to delete this car");
    }

    // Deleting the car - with cascading delete for bookings
    $stmt = $conn->prepare("DELETE FROM cars WHERE id = ?");
    if (!$stmt) {
        throw new Exception("Failed to prepare deletion query: " . $conn->error);
    }
    
    $stmt->bind_param("i", $car_id);
    if (!$stmt->execute()) {
        throw new Exception("Failed to execute delete query: " . $conn->error);
    }

    // Redirect back to my_cars.php with success message
    header("Location: my_cars.php?success=1");
    exit();

} catch (Exception $e) {
    // Display the error
    error_log("Deletion error - Car ID: $car_id, User ID: $user_id, Role: $role - Error: " . $e->getMessage());
    
    // Redirect back to my_cars.php with error message
    header("Location: my_cars.php?error=1");
    exit();
}
?>