<?php
session_start();
require_once 'db.php';

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit();
}

$success_message = $error_message = '';

// Mark notifications as read if requested
if (isset($_GET['mark_read'])) {
    try {
        $stmt = $conn->prepare("UPDATE notifications SET is_read = 1 WHERE id = ? AND user_id = ?");
        $stmt->bind_param('ii', $_GET['mark_read'], $_SESSION['user_id']);
        
        if ($stmt->execute()) {
            $success_message = "Notification marked as read";
        } else {
            throw new Exception("Failed to mark notification as read");
        }
    } catch (Exception $e) {
        $error_message = "Failed to mark notification as read. Please try again.";
    }
}

// Get all notifications for the user
try {
    $stmt = $conn->prepare("
        SELECT n.*, u.name as sender_name 
        FROM notifications n 
        LEFT JOIN users u ON n.sender_id = u.id 
        WHERE n.user_id = ? 
        ORDER BY n.created_at DESC
    ");
    $stmt->bind_param('i', $_SESSION['user_id']);
    $stmt->execute();
    $result = $stmt->get_result();
    
    $notifications = [];
    while ($row = $result->fetch_assoc()) {
        $notifications[] = [
            'id' => $row['id'],
            'type' => $row['type'],
            'message' => $row['message'],
            'sender_name' => $row['sender_name'],
            'is_read' => $row['is_read'],
            'created_at' => $row['created_at']
        ];
    }
} catch (Exception $e) {
    $error_message = "Failed to load notifications. Please try again.";
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Notifications - RCar</title>
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
                <a href="dashboard.php">
                    <i class="fas fa-home"></i>
                    <span>Dashboard</span>
                </a>
                <?php if ($_SESSION['role'] === 'agency'): ?>
                    <a href="my_cars.php">
                        <i class="fas fa-car"></i>
                        <span>My Cars</span>
                    </a>
                <?php endif; ?>
                <a href="bookings.php">
                    <i class="fas fa-calendar-alt"></i>
                    <span>Bookings</span>
                </a>
                <a href="settings.php">
                    <i class="fas fa-cog"></i>
                    <span>Settings</span>
                </a>
            </nav>
        </div>

        <div class="dashboard-content">
            <div class="dashboard-header">
                <h1>Notifications</h1>
            </div>

            <div class="notifications-container">
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

                <?php if (empty($notifications)): ?>
                    <div class="no-notifications">
                        <p>No notifications yet</p>
                    </div>
                <?php else: ?>
                    <div class="notifications-list">
                        <?php foreach ($notifications as $notification): ?>
                            <div class="notification-item 
                                   <?php echo $notification['is_read'] ? 'read' : 'unread'; ?>">
                                <div class="notification-content">
                                    <p><?php echo htmlspecialchars($notification['message']); ?></p>
                                    <small><?php echo date('F j, Y g:i A', strtotime($notification['created_at'])); ?></small>
                                </div>
                                <div class="notification-actions">
                                    <?php if (!$notification['is_read']): ?>
                                        <a href="?mark_read=<?php echo $notification['id']; ?>" 
                                           class="mark-read">
                                            <i class="fas fa-check-circle"></i>
                                        </a>
                                    <?php endif; ?>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>


</body>
</html>
