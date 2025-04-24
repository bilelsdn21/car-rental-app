<?php
session_start();
require_once 'db.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = $_POST['email'];
    $password = $_POST['password'];

    // Input validation to block suspicious inputs
    if (preg_match("/['\"=;]/", $email) || stripos($email, "OR") !== false) {
        $error = "Invalid email format. Suspicious input detected.";
    } else {
        // Prepare statement to prevent SQL injection
        $stmt = $conn->prepare("SELECT id, name, email, password, role FROM users WHERE email = ?");
        $stmt->bind_param("s", $email);
        $stmt->execute();
        $result = $stmt->get_result();
        $user = $result->fetch_assoc();
        
        if ($user && password_verify($password, $user['password'])) {
            // Login successful
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['name'] = $user['name'];
            $_SESSION['role'] = $user['role'];
            
            header('Location: dashboard.php');
            exit();
        } else {
            $error = "Invalid email or password";
        }
        $stmt->close();
    }
}
$conn->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login (Secure) - RCar</title>
    <link rel="stylesheet" href="assets/style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
</head>
<body>
    <?php include 'includes/navbar.php'; ?>
    
    <div class="auth-container">
        <div class="auth-box">
            <h1>Welcome Back (Secure)</h1>
            <p class="auth-subtitle">Log in to your account</p>
            
            <?php if (isset($error)): ?>
                <div class="error"><?php echo htmlspecialchars($error); ?></div>
            <?php endif; ?>
            
            <form class="auth-form" method="POST" action="">
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
                        <input type="password" id="password" name="password" required>
                    </div>
                </div>
                
                <button type="submit" class="auth-btn">Login</button>
            </form>
            
            <div class="auth-links">
                <p>Don't have an account?</p>
                <div class="register-options">
                    <a href="register.php?type=user" class="btn-outline">Register as User</a>
                    <a href="register.php?type=agency" class="btn-outline">Register as Agency</a>
                </div>
            </div>
        </div>
    </div>

    <?php include 'includes/footer.php'; ?>
</body>
</html>