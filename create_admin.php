<?php
require_once 'db.php';

// Admin user details
$name = 'Admin';
$email = 'admin@rentipro.com';
$password = password_hash('admin123', PASSWORD_DEFAULT);
$role = 'admin';

// Check if admin already exists
$stmt = $conn->prepare("SELECT id FROM users WHERE email = ?");
$stmt->bind_param("s", $email);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 0) {
    // Create admin user
    $stmt = $conn->prepare("INSERT INTO users (name, email, password, role) VALUES (?, ?, ?, ?)");
    $stmt->bind_param("ssss", $name, $email, $password, $role);
    
    if ($stmt->execute()) {
        echo "Admin user created successfully!<br>";
        echo "Email: admin@rentipro.com<br>";
        echo "Password: admin123";
    } else {
        echo "Error creating admin user: " . $conn->error;
    }
} else {
    echo "Admin user already exists!";
}

$stmt->close();
$conn->close();
?> 