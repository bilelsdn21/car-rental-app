<?php
session_start();
require_once 'db.php';

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit();
}

$success_message = $error_message = '';

// Handle settings updates
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Get form data
    $email = $_POST['email'] ?? '';
    $current_password = $_POST['current_password'] ?? '';
    $new_password = $_POST['new_password'] ?? '';
    $confirm_password = $_POST['confirm_password'] ?? '';
    
    // Validate email
    if (!empty($email) && filter_var($email, FILTER_VALIDATE_EMAIL)) {
        try {
            // Check if email is already in use by another user
            $stmt = $conn->prepare("SELECT id FROM users WHERE email = ? AND id != ?");
            $stmt->bind_param('si', $email, $_SESSION['user_id']);
            $stmt->execute();
            $result = $stmt->get_result();
            
            if ($result->num_rows > 0) {
                $error_message = "This email is already in use by another account.";
            } else {
                // Update email
                $stmt = $conn->prepare("UPDATE users SET email = ? WHERE id = ?");
                $stmt->bind_param('si', $email, $_SESSION['user_id']);
                $stmt->execute();
            }
        } catch (Exception $e) {
            $error_message = "Failed to update email. Please try again.";
        }
    }

    // Handle password change
    if (!empty($current_password) && !empty($new_password) && !empty($confirm_password)) {
        try {
            // Verify current password
            $stmt = $conn->prepare("SELECT password FROM users WHERE id = ?");
            $stmt->bind_param('i', $_SESSION['user_id']);
            $stmt->execute();
            $result = $stmt->get_result();
            $user = $result->fetch_assoc();

            if (password_verify($current_password, $user['password'])) {
                if ($new_password === $confirm_password) {
                    $hashed_password = password_hash($new_password, PASSWORD_DEFAULT);
                    
                    // Update password
                    $stmt = $conn->prepare("UPDATE users SET password = ? WHERE id = ?");
                    $stmt->bind_param('si', $hashed_password, $_SESSION['user_id']);
                    $stmt->execute();
                    $success_message = "Settings updated successfully!";
                } else {
                    $error_message = "New password and confirmation do not match.";
                }
            } else {
                $error_message = "Current password is incorrect.";
            }
        } catch (Exception $e) {
            $error_message = "Failed to update password. Please try again.";
        }
    }

    // Handle agency profile updates
    if ($_SESSION['role'] === 'agency') {
        $agency_name = $_POST['agency_name'] ?? '';
        $contact_email = $_POST['contact_email'] ?? '';
        $contact_phone = $_POST['contact_phone'] ?? '';
        $address = $_POST['address'] ?? '';
        $business_hours = $_POST['business_hours'] ?? '';
        $logo_url = $_POST['logo_url'] ?? '';
        $description = $_POST['description'] ?? '';

        try {
            // Check if agency profile exists
            $stmt = $conn->prepare("SELECT id FROM agency_profiles WHERE agency_id = ?");
            $stmt->bind_param('i', $_SESSION['user_id']);
            $stmt->execute();
            $result = $stmt->get_result();

            if ($result->num_rows > 0) {
                // Update existing profile
                $stmt = $conn->prepare("
                    UPDATE agency_profiles SET 
                    agency_name = ?, 
                    contact_email = ?, 
                    contact_phone = ?, 
                    address = ?, 
                    business_hours = ?, 
                    logo_url = ?, 
                    description = ? 
                    WHERE agency_id = ?");
                $stmt->bind_param('sssssssi', 
                    $agency_name, 
                    $contact_email, 
                    $contact_phone, 
                    $address, 
                    $business_hours, 
                    $logo_url, 
                    $description, 
                    $_SESSION['user_id']);
            } else {
                // Create new profile
                $stmt = $conn->prepare("
                    INSERT INTO agency_profiles (
                        agency_id, 
                        agency_name, 
                        contact_email, 
                        contact_phone, 
                        address, 
                        business_hours, 
                        logo_url, 
                        description
                    ) VALUES (?, ?, ?, ?, ?, ?, ?, ?)");
                $stmt->bind_param('isssssss', 
                    $_SESSION['user_id'],
                    $agency_name, 
                    $contact_email, 
                    $contact_phone, 
                    $address, 
                    $business_hours, 
                    $logo_url, 
                    $description);
            }

            if ($stmt->execute()) {
                $success_message = "Agency profile updated successfully!";
            } else {
                throw new Exception("Failed to update agency profile");
            }
        } catch (Exception $e) {
            $error_message = "Failed to update agency profile. Please try again.";
        }
    }
}

// Fetch user's current information
try {
    $stmt = $conn->prepare("SELECT email, name FROM users WHERE id = ?");
    $stmt->bind_param('i', $_SESSION['user_id']);
    $stmt->execute();
    $result = $stmt->get_result();
    $user = $result->fetch_assoc();

    // Fetch agency profile if user is an agency
    if ($_SESSION['role'] === 'agency') {
        $stmt = $conn->prepare("SELECT * FROM agency_profiles WHERE agency_id = ?");
        $stmt->bind_param('i', $_SESSION['user_id']);
        $stmt->execute();
        $result = $stmt->get_result();
        $agency = $result->fetch_assoc();
    }
} catch (Exception $e) {
    $error_message = "Failed to load user information. Please try again.";
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Settings - RCar</title>
    <link rel="stylesheet" href="assets/style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
</head>
<body>
    <?php include 'includes/navbar.php'; ?>

    <div class="dashboard">
        <div class="sidebar">
            <div class="sidebar-header">
                <h2>Dashboard</h2>
            </div>
            <nav class="sidebar-nav">
                <a href="dashboard.php" class="<?php echo basename($_SERVER['PHP_SELF']) === 'dashboard.php' ? 'active' : ''; ?>">
                    <i class="fas fa-home"></i> Dashboard
                </a>
                <?php if ($_SESSION['role'] === 'admin'): ?>
                    <a href="users.php" class="<?php echo basename($_SERVER['PHP_SELF']) === 'users.php' ? 'active' : ''; ?>">
                        <i class="fas fa-users"></i> Users
                    </a>
                <?php endif; ?>
                <?php if ($_SESSION['role'] === 'agency'): ?>
                    <a href="my_cars.php" class="<?php echo basename($_SERVER['PHP_SELF']) === 'my_cars.php' ? 'active' : ''; ?>">
                        <i class="fas fa-car"></i> My Cars
                    </a>
                <?php endif; ?>
                <a href="bookings.php" class="<?php echo basename($_SERVER['PHP_SELF']) === 'bookings.php' ? 'active' : ''; ?>">
                    <i class="fas fa-calendar"></i> Bookings
                </a>
                <a href="settings.php" class="<?php echo basename($_SERVER['PHP_SELF']) === 'settings.php' ? 'active' : ''; ?>">
                    <i class="fas fa-gear"></i> Settings
                </a>
            </nav>
        </div>

        <div class="dashboard-content">
            <div class="dashboard-header">
                <h1>Settings</h1>
            </div>

            <div class="stats-container">
                <?php if ($success_message): ?>
                    <div class="success">
                        <?php echo htmlspecialchars($success_message); ?>
                    </div>
                <?php endif; ?>

                <?php if ($error_message): ?>
                    <div class="error">
                        <?php echo htmlspecialchars($error_message); ?>
                    </div>
                <?php endif; ?>

                <form method="POST" class="settings-form">
                    <div class="form-grid">
                        <div class="form-group">
                            <label for="email">Email Address*</label>
                            <input type="email" id="email" name="email" 
                                   value="<?php echo htmlspecialchars($user['email']); ?>" required>
                        </div>

                        <div class="form-group">
                            <label for="current_password">Current Password</label>
                            <input type="password" id="current_password" name="current_password">
                            <p class="help-text">Leave empty if you don't want to change your password</p>
                        </div>

                        <div class="form-group">
                            <label for="new_password">New Password</label>
                            <input type="password" id="new_password" name="new_password">
                            <p class="help-text">Leave empty if you don't want to change your password</p>
                        </div>

                        <div class="form-group">
                            <label for="confirm_password">Confirm New Password</label>
                            <input type="password" id="confirm_password" name="confirm_password">
                        </div>

                        <?php if ($_SESSION['role'] === 'agency'): ?>
                        <div class="form-group">
                            <label for="agency_name">Agency Name</label>
                            <input type="text" id="agency_name" name="agency_name" 
                                   value="<?php echo htmlspecialchars($agency['agency_name'] ?? ''); ?>">
                        </div>

                        <div class="form-group">
                            <label for="contact_email">Contact Email</label>
                            <input type="email" id="contact_email" name="contact_email" 
                                   value="<?php echo htmlspecialchars($agency['contact_email'] ?? ''); ?>">
                        </div>

                        <div class="form-group">
                            <label for="contact_phone">Contact Phone</label>
                            <input type="tel" id="contact_phone" name="contact_phone" 
                                   value="<?php echo htmlspecialchars($agency['contact_phone'] ?? ''); ?>">
                        </div>

                        <div class="form-group">
                            <label for="address">Address</label>
                            <textarea id="address" name="address" rows="3"><?php echo htmlspecialchars($agency['address'] ?? ''); ?></textarea>
                        </div>

                        <div class="form-group">
                            <label for="business_hours">Business Hours</label>
                            <textarea id="business_hours" name="business_hours" rows="3"><?php echo htmlspecialchars($agency['business_hours'] ?? ''); ?></textarea>
                        </div>

                        <div class="form-group">
                            <label for="logo_url">Logo URL</label>
                            <input type="text" id="logo_url" name="logo_url" 
                                   value="<?php echo htmlspecialchars($agency['logo_url'] ?? ''); ?>">
                        </div>

                        <div class="form-group">
                            <label for="description">Agency Description</label>
                            <textarea id="description" name="description" rows="5"><?php echo htmlspecialchars($agency['description'] ?? ''); ?></textarea>
                        </div>
                        <?php endif; ?>

                        <div class="form-group">
                            <button type="submit" class="btn-primary">Save Changes</button>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>


</body>
</html>
