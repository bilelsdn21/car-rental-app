<?php
session_start();
require_once 'db.php';

$type = isset($_GET['type']) ? $_GET['type'] : 'user';
if (!in_array($type, ['user', 'agency'])) {
    $type = 'user';
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = $_POST['name'];
    $email = $_POST['email'];
    $password = password_hash($_POST['password'], PASSWORD_DEFAULT);
    $role = $type;
    
    // Check if email already exists
    $stmt = $conn->prepare("SELECT id FROM users WHERE email = ?");
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result->num_rows > 0) {
        $error = "Email already exists";
    } else {
        // Create new user
        $stmt = $conn->prepare("INSERT INTO users (name, email, password, role) VALUES (?, ?, ?, ?)");
        $stmt->bind_param("ssss", $name, $email, $password, $role);
        
        if ($stmt->execute()) {
            $_SESSION['user_id'] = $stmt->insert_id;
            $_SESSION['name'] = $name;
            $_SESSION['role'] = $role;
            
            header('Location: dashboard.php');
            exit();
        } else {
            $error = "Registration failed: " . $conn->error;
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Register - RCar</title>
    <link rel="stylesheet" href="assets/style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
</head>
<body>
    <?php include 'includes/navbar.php'; ?>
    
    <div class="auth-container">
        <div class="auth-box">
            <h1>Create Account</h1>
            <p class="auth-subtitle">Register as <?php echo ucfirst($type); ?></p>
            
            <?php if (isset($error)): ?>
                <div class="error"><?php echo htmlspecialchars($error); ?></div>
            <?php endif; ?>
            
            <form class="auth-form" method="POST" action="">
                <div class="form-group">
                    <label for="name">Full Name</label>
                    <div class="input-group">
                        <i class="fas fa-user"></i>
                        <input type="text" id="name" name="name" required>
                    </div>
                </div>
                
                <div class="form-group">
                    <label for="email">Email</label>
                    <div class="input-group">
                        <i class="fas fa-envelope"></i>
                        <input type="email" id="email" name="email" required>
                    </div>
                </div>
                
                <div class="form-group">
                    <label for="password">Password</label>
                    <div class="input-group">
                        <i class="fas fa-lock"></i>
                        <input type="password" id="password" name="password" required minlength="6">
                    </div>
                </div>
                
                <button type="submit" class="auth-btn">Create Account</button>
            </form>
            
            <div class="auth-links">
                <p>Already have an account? <a href="login.php" class="lak">Login</a></p>
                <?php if ($type === 'user'): ?>
                    <p>Want to list your cars? <a href="register.php?type=agency" class="lak">Register as Agency</a></p>
                <?php else: ?>
                    <p>Want to rent cars? <a href="register.php?type=user" class="lak">Register as User</a></p>
                <?php endif; ?>
            </div>
        </div>
    </div>
</body>
</html> 