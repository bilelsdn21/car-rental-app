<?php
session_start();
require_once 'db.php';

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header("Location: index.html");
    exit();
}

if (!isset($_GET['id'])) {
    header("Location: dashboard.php");
    exit();
}

$user_id = $_GET['id'];

// Check if user exists and is not an admin
$stmt = $pdo->prepare("SELECT role FROM users WHERE id = ?");
$stmt->execute([$user_id]);
$user = $stmt->fetch();

if (!$user || $user['role'] === 'admin') {
    header("Location: dashboard.php");
    exit();
}

// Delete user's cars first (due to foreign key constraint)
$stmt = $pdo->prepare("DELETE FROM cars WHERE posted_by = ?");
$stmt->execute([$user_id]);

// Delete the user
$stmt = $pdo->prepare("DELETE FROM users WHERE id = ?");
$stmt->execute([$user_id]);

header("Location: dashboard.php");
exit();
?> 