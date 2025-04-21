<?php
require_once 'db.php';

function getUserDetails($user_id) {
    global $conn;
    
    $stmt = $conn->prepare("SELECT * FROM users WHERE id = ?");
    $stmt->bind_param("i", $user_id);
    $stmt->execute();
    return $stmt->get_result()->fetch_assoc();
}

function getCarDetails($car_id) {
    global $conn;
    
    $stmt = $conn->prepare("SELECT * FROM cars WHERE id = ?");
    $stmt->bind_param("i", $car_id);
    $stmt->execute();
    return $stmt->get_result()->fetch_assoc();
}

function getBookingDetails($booking_id) {
    global $conn;
    
    $stmt = $conn->prepare("
        SELECT b.*, c.make, c.model, c.year, c.image_url, c.price_per_day, 
               u.name as user_name, u.email as user_email
        FROM bookings b
        JOIN cars c ON b.car_id = c.id
        JOIN users u ON b.user_id = u.id
        WHERE b.id = ?
    ");
    $stmt->bind_param("i", $booking_id);
    $stmt->execute();
    return $stmt->get_result()->fetch_assoc();
}

function formatPrice($amount) {
    return '$' . number_format($amount, 2);
}

function formatDate($date) {
    return date('F j, Y', strtotime($date));
}

function calculateTotalPrice($price_per_day, $start_date, $end_date) {
    $start = strtotime($start_date);
    $end = strtotime($end_date);
    $days = ceil(($end - $start) / (60 * 60 * 24));
    return $price_per_day * $days;
}

function isCarAvailable($car_id, $start_date, $end_date) {
    global $conn;
    
    $stmt = $conn->prepare("
        SELECT COUNT(*) as count 
        FROM bookings 
        WHERE car_id = ? 
        AND (
            (start_date <= ? AND end_date >= ?) 
            OR (start_date <= ? AND end_date >= ?) 
            OR (start_date >= ? AND end_date <= ?)
        )
    ");
    
    $stmt->bind_param("issssss", $car_id, $start_date, $start_date, 
                      $end_date, $end_date, $start_date, $end_date);
    $stmt->execute();
    $result = $stmt->get_result();
    $row = $result->fetch_assoc();
    
    return $row['count'] == 0;
}
?>
