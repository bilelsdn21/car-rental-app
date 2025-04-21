<?php
require_once 'includes/session_manager.php';
require_once 'db.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = $_POST['email'] ?? '';
    $password = $_POST['password'] ?? '';
    
    if (empty($email) || empty($password)) {
        $error_message = "All fields are required.";
    } else {
        // Prepare and execute the query
        $stmt = $conn->prepare("SELECT id, password, role FROM users WHERE email = ?");
        $stmt->bind_param("s", $email);
        $stmt->execute();
        $result = $stmt->get_result();
        
        if ($result->num_rows === 1) {
            $user = $result->fetch_assoc();
            
            // Verify password
            if (password_verify($password, $user['password'])) {
                // Set session variables
                $_SESSION['user_id'] = $user['id'];
                $_SESSION['role'] = $user['role'];
                
                // Fetch user's name
                $stmt = $conn->prepare("SELECT name FROM users WHERE id = ?");
                $stmt->bind_param("i", $user['id']);
                $stmt->execute();
                $name_result = $stmt->get_result();
                
                if ($name_result->num_rows === 1) {
                    $user_data = $name_result->fetch_assoc();
                    $_SESSION['name'] = $user_data['name'];
                }
                
                // Redirect based on role
                if ($user['role'] === 'admin') {
                    header('Location: dashboard.php');
                } else {
                    header('Location: index.php');
                }
                exit();
            } else {
                $error_message = "Invalid email or password.";
            }
        } else {
            $error_message = "Invalid email or password.";
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - RCar</title>
    <link rel="stylesheet" href="assets/style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
</head>
<body>
    <?php include 'includes/navbar.php'; ?>

    <div class="container">
        <div class="login-container">
            <h2>Welcome Back</h2>
            
            <?php if (isset($error_message)): ?>
                <div class="error-message">
                    <i class="fas fa-exclamation-circle"></i>
                    <p><?php echo htmlspecialchars($error_message); ?></p>
                </div>
            <?php endif; ?>

            <form action="login.php" method="POST" class="login-form">
                <div class="form-group">
                    <label for="email"><i class="fas fa-envelope"></i> Email</label>
                    <input type="email" id="email" name="email" required>
                </div>

                <div class="form-group">
                    <label for="password"><i class="fas fa-lock"></i> Password</label>
                    <input type="password" id="password" name="password" required>
                </div>

                <div class="form-group">
                    <button type="submit" class="btn-primary">
                        <i class="fas fa-sign-in-alt"></i> Log In
                    </button>
                </div>

                <div class="form-group">
                    <p>Don't have an account? <a href="register.php">Sign up</a></p>
                </div>
            </form>
        </div>
    </div>

    <?php include 'includes/footer.php'; ?>

    <style>
    .login-container {
        max-width: 400px;
        margin: 4rem auto;
        padding: 2rem;
        background: #fff;
        border-radius: 10px;
        box-shadow: 0 2px 4px rgba(0,0,0,0.1);
    }

    .login-container h2 {
        text-align: center;
        margin-bottom: 2rem;
        color: #333;
    }

    .form-group {
        margin-bottom: 1.5rem;
    }

    .form-group label {
        display: block;
        margin-bottom: 0.5rem;
        color: #666;
    }

    .form-group input {
        width: 100%;
        padding: 0.8rem;
        border: 1px solid #ddd;
        border-radius: 5px;
        font-size: 1rem;
    }

    .form-group button {
        width: 100%;
        padding: 0.8rem;
        background: #007bff;
        color: #fff;
        border: none;
        border-radius: 5px;
        cursor: pointer;
        font-size: 1rem;
        transition: background 0.3s;
    }

    .form-group button:hover {
        background: #0056b3;
    }

    .error-message {
        background: #fff3f3;
        border: 1px solid #f8d7da;
        border-radius: 5px;
        padding: 1rem;
        margin-bottom: 1.5rem;
        color: #721c24;
    }

    .error-message i {
        margin-right: 0.5rem;
    }

    @media (max-width: 768px) {
        .login-container {
            margin: 2rem auto;
            padding: 1.5rem;
        }
    }
    </style>
</body>
</html>