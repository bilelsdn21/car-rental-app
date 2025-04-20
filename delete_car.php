<?php
session_start();
require_once 'db.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: index.html");
    exit();
}

if (!isset($_GET['id'])) {
    header("Location: dashboard.php");
    exit();
}

$car_id = $_GET['id'];
$user_id = $_SESSION['user_id'];
$role = $_SESSION['role'];

// Check if user is authorized to delete this car
$stmt = $pdo->prepare("SELECT posted_by FROM cars WHERE id = ?");
$stmt->execute([$car_id]);
$car = $stmt->fetch();

if (!$car || ($role !== 'admin' && $car['posted_by'] != $user_id)) {
    header("Location: dashboard.php");
    exit();
}

// Delete the car
$stmt = $pdo->prepare("DELETE FROM cars WHERE id = ?");
$stmt->execute([$car_id]);

header("Location: dashboard.php");
exit();
?> 