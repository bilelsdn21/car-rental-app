<?php
session_start();
require_once 'db.php';

// Check if user is logged in and is an admin
if (!isset($_SESSION['user_id']) || !isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
    header('Location: login.php');
    exit();
}

// Handle user update
if (isset($_POST['update_user']) && isset($_POST['user_id'])) {
    $user_id = (int)$_POST['user_id'];
    $name = $_POST['name'] ?? '';
    $email = $_POST['email'] ?? '';
    $role = $_POST['role'] ?? '';
    $password = $_POST['password'] ?? '';
    
    // Validate inputs
    if (empty($name) || empty($email) || empty($role)) {
        $error_message = "All fields are required.";
        exit();
    }
    
    // Validate email format
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error_message = "Invalid email format.";
        exit();
    }
    
    // Check if email already exists
    $stmt = $conn->prepare("SELECT id FROM users WHERE email = ? AND id != ?");
    $stmt->bind_param('si', $email, $user_id);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result->num_rows > 0) {
        $error_message = "Email already exists.";
        exit();
    }
    
    // Prepare update query
    $update_fields = [];
    $params = [];
    $types = '';

    if (!empty($name)) {
        $update_fields[] = 'name = ?';
        $params[] = $name;
        $types .= 's';
    }
    if (!empty($email)) {
        $update_fields[] = 'email = ?';
        $params[] = $email;
        $types .= 's';
    }
    if (!empty($role)) {
        $update_fields[] = 'role = ?';
        $params[] = $role;
        $types .= 's';
    }
    if (!empty($password)) {
        $hashed_password = password_hash($password, PASSWORD_DEFAULT);
        $update_fields[] = 'password = ?';
        $params[] = $hashed_password;
        $types .= 's';
    }

    if (empty($update_fields)) {
        $error_message = "No changes to update.";
        exit();
    }

    $update_query = "UPDATE users SET " . implode(', ', $update_fields) . " WHERE id = ?";
    $params[] = $user_id;
    $types .= 'i';

    $stmt = $conn->prepare($update_query);
    if ($stmt === false) {
        $error_message = "Failed to prepare update statement.";
    } else {
        $stmt->bind_param($types, ...$params);
        if ($stmt->execute()) {
            $success_message = "User updated successfully.";
        } else {
            $error_message = "Failed to update user. Please try again.";
        }
    }
}

// Handle user deletion
if (isset($_POST['delete_user']) && isset($_POST['user_id'])) {
    $user_id = (int)$_POST['user_id'];
    
    // Delete user's cars if they're an agency
    $stmt = $conn->prepare("DELETE FROM cars WHERE posted_by = ?");
    $stmt->bind_param('i', $user_id);
    $stmt->execute();
    
    // Delete user
    $stmt = $conn->prepare("DELETE FROM users WHERE id = ?");
    $stmt->bind_param('i', $user_id);
    
    if ($stmt->execute()) {
        $success_message = "User deleted successfully.";
    } else {
        $error_message = "Failed to delete user. Please try again.";
    }
}

// Fetch all users
try {
    $stmt = $conn->prepare("SELECT * FROM users WHERE id != ? ORDER BY role, name");
    $stmt->bind_param('i', $_SESSION['user_id']);
    $stmt->execute();
    $result = $stmt->get_result();
    $users = $result->fetch_all(MYSQLI_ASSOC);
} catch (Exception $e) {
    $error_message = "Error fetching users: " . $e->getMessage();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Users - RCar</title>
    <link rel="stylesheet" href="assets/style.css">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    
</head>
<body>
    <?php include 'includes/navbar.php'; ?>

    <div class="dashboard-container">
        <aside class="sidebar">
            <div class="sidebar-header">
                <h2>Dashboard</h2>
            </div>
            <nav class="sidebar-nav">
                <a href="dashboard.php" class="<?php echo basename($_SERVER['PHP_SELF']) === 'dashboard.php' ? 'active' : ''; ?>">
                    <i class="fas fa-home"></i> Dashboard
                </a>
                <a href="users.php" class="<?php echo basename($_SERVER['PHP_SELF']) === 'users.php' ? 'active' : ''; ?>">
                    <i class="fas fa-users"></i> Users
                </a>
                <a href="bookings.php" class="<?php echo basename($_SERVER['PHP_SELF']) === 'bookings.php' ? 'active' : ''; ?>">
                    <i class="fas fa-calendar"></i> Bookings
                </a>
                <a href="settings.php" class="<?php echo basename($_SERVER['PHP_SELF']) === 'settings.php' ? 'active' : ''; ?>">
                    <i class="fas fa-cog"></i> Settings
                </a>
            </nav>
        </aside>
        <main class="dashboard-content">
            <div class="dashboard-header">
                <h1>Manage Users</h1>
            </div>

            <?php if (isset($error_message)): ?>
                <div class="alert alert-danger">
                    <?php echo $error_message; ?>
                </div>
            <?php endif; ?>

            <?php if (isset($success_message)): ?>
                <div class="alert alert-success">
                    <?php echo $success_message; ?>
                </div>
            <?php endif; ?>

            <?php // Show all users and agencies
                $userList = array_filter($users, fn($u) => in_array($u['role'], ['user', 'agency']));
            ?>
            <h3>Users & Agencies</h3>
            <div class="table-responsive mb-5">
                <table class="table table-bordered align-middle">
                    <thead class="table-light">
                        <tr>
                            <th>Name</th>
                            <th>Email</th>
                            <th>Role</th>
                            <th style="width:180px">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($userList as $user): ?>
                        <tr>
                            <form method="POST" class="user-update-form">
                                <input type="hidden" name="user_id" value="<?php echo $user['id']; ?>">
                                <td><input type="text" name="name" class="form-control form-control-sm" value="<?php echo htmlspecialchars($user['name']); ?>" required></td>
                                <td><input type="email" name="email" class="form-control form-control-sm" value="<?php echo htmlspecialchars($user['email']); ?>" required></td>
                                <td>
                                    <select name="role" class="form-select form-select-sm">
                                        <option value="user" <?php if ($user['role'] === 'user') echo 'selected'; ?>>User</option>
                                        <option value="agency" <?php if ($user['role'] === 'agency') echo 'selected'; ?>>Agency</option>
                                    </select>
                                </td>
                                <td class="d-flex gap-2">
                                    <input type="password" name="password" class="form-control form-control-sm" placeholder="New password">
                                    <button type="submit" name="update_user" class="btn btn-primary btn-sm"><i class="fas fa-save"></i></button>
                                </td>
                            </form>
                            <td class="d-flex gap-2">
                                <form method="POST" class="d-inline" onsubmit="return confirm('Are you sure you want to delete this user?')">
                                    <input type="hidden" name="user_id" value="<?php echo $user['id']; ?>">
                                    <button type="submit" name="delete_user" class="btn btn-danger btn-sm"><i class="fas fa-trash"></i></button>
                                </form>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        // Add smooth scrolling for navigation links
        document.querySelectorAll('.sidebar-nav a').forEach(anchor => {
            anchor.addEventListener('click', function(e) {
                e.preventDefault();
                window.scrollTo({
                    top: 0,
                    behavior: 'smooth'
                });
                window.location.href = this.getAttribute('href');
            });
        });

        // Form submission handling
        document.querySelectorAll('.user-update-form').forEach(form => {
            form.addEventListener('submit', function(e) {
                const password = this.querySelector('input[name="password"]').value;
                if (password && password.length < 6) {
                    e.preventDefault();
                    alert('Password must be at least 6 characters long');
                    return;
                }
            });
        });
    </script>
</body>
</html>
