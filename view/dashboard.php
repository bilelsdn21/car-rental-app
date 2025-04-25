<?php
session_start();
require_once 'db.php';

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit();
}

// Get user data
$user_id = $_SESSION['user_id'];
$stmt = $conn->prepare("SELECT * FROM users WHERE id = ?");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$user = $stmt->get_result()->fetch_assoc();



// Get statistics based on user role
$total_users = 0;
$total_cars = 0;
$total_bookings = 0;

if ($user['role'] === 'admin') {
    // Admin stats
    $result = $conn->query("SELECT COUNT(*) as count FROM users WHERE role != 'admin'");
    $total_users = $result->fetch_assoc()['count'];
    
    $result = $conn->query("SELECT COUNT(*) as count FROM cars");
    $total_cars = $result->fetch_assoc()['count'];
    
    $result = $conn->query("SELECT COUNT(*) as count FROM bookings");
    $total_bookings = $result->fetch_assoc()['count'];
} elseif ($user['role'] === 'agency') {
    // Agency stats
    $stmt = $conn->prepare("SELECT COUNT(*) as count FROM cars WHERE posted_by = ?");
    $stmt->bind_param("i", $user_id);
    $stmt->execute();
    $total_cars = $stmt->get_result()->fetch_assoc()['count'];
    
    $stmt = $conn->prepare("SELECT COUNT(*) as count FROM bookings b 
        JOIN cars c ON b.car_id = c.id 
        WHERE c.posted_by = ?");
    $stmt->bind_param("i", $user_id);
    $stmt->execute();
    $total_bookings = $stmt->get_result()->fetch_assoc()['count'];
} else {
    // User stats
    $stmt = $conn->prepare("SELECT COUNT(*) as count FROM bookings WHERE user_id = ?");
    $stmt->bind_param("i", $user_id);
    $stmt->execute();
    $total_bookings = $stmt->get_result()->fetch_assoc()['count'];
}

// Get recent activities
$recent_activities = [];
if ($user['role'] === 'admin') {
    $query = "SELECT b.*, c.title as car_title, u.name as user_name, a.name as agency_name 
              FROM bookings b 
              JOIN cars c ON b.car_id = c.id 
              JOIN users u ON b.user_id = u.id 
              JOIN users a ON c.posted_by = a.id 
              ORDER BY b.created_at DESC LIMIT 5";
    $result = $conn->query($query);
    while ($row = $result->fetch_assoc()) {
        $recent_activities[] = $row;
    }
} elseif ($user['role'] === 'agency') {
    $stmt = $conn->prepare("SELECT b.*, c.title as car_title, u.name as user_name 
                           FROM bookings b 
                           JOIN cars c ON b.car_id = c.id 
                           JOIN users u ON b.user_id = u.id 
                           WHERE c.posted_by = ? 
                           ORDER BY b.created_at DESC LIMIT 5");
    $stmt->bind_param("i", $user_id);
    $stmt->execute();
    $result = $stmt->get_result();
    while ($row = $result->fetch_assoc()) {
        $recent_activities[] = $row;
    }
} else {
    $stmt = $conn->prepare("SELECT b.*, c.title as car_title, a.name as agency_name 
                           FROM bookings b 
                           JOIN cars c ON b.car_id = c.id 
                           JOIN users a ON c.posted_by = a.id 
                           WHERE b.user_id = ? 
                           ORDER BY b.created_at DESC LIMIT 5");
    $stmt->bind_param("i", $user_id);
    $stmt->execute();
    $result = $stmt->get_result();
    while ($row = $result->fetch_assoc()) {
        $recent_activities[] = $row;
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard - RCar</title>
    <link rel="stylesheet" href="assets/style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
</head>
<body>
    <nav class="navbar">
        <div class="logo">
            <a href="index.php">RCar</a>
        </div>
        <div class="nav-links">
            <a href="index.php">Home</a>
            <a href="catalog.php">Catalog</a>
            <a href="about.php">About Us</a>
            <a href="contact.php">Contact</a>
        </div>
        <div class="nav-user">
            <div class="notifications">

            </div>
            <div class="user-menu">
                <span><?php echo htmlspecialchars($user['name']); ?></span>
                <a href="logout.php">Logout</a>
            </div>
        </div>
    </nav>

    <div class="dashboard-container">
        <aside class="sidebar">
            <div class="sidebar-header">
                <h2>Dashboard</h2>
            </div>
            <nav class="sidebar-nav">
                <a href="dashboard.php" class="<?php echo basename($_SERVER['PHP_SELF']) === 'dashboard.php' ? 'active' : ''; ?>">
                    <i class="fas fa-home"></i> Dashboard
                </a>
                <?php if ($user['role'] === 'admin'): ?>
                    <a href="users.php" class="<?php echo basename($_SERVER['PHP_SELF']) === 'users.php' ? 'active' : ''; ?>">
                        <i class="fas fa-users"></i> Users
                    </a>
                <?php endif; ?>
                <?php if ($user['role'] === 'agency'): ?>
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
        </aside>

        <main class="dashboard-content">
            <div class="dashboard-header">
                <h1>Welcome, <?php echo htmlspecialchars($user['name']); ?>!</h1>
            </div>

            <div class="stats-container">
                <?php if ($user['role'] === 'admin'): ?>
                    <div class="stat-card">
                        <i class="fas fa-users"></i>
                        <h3>Total Users</h3>
                        <p><?php echo $total_users; ?></p>
                    </div>
                <?php endif; ?>
                
                <?php if ($user['role'] === 'admin' || $user['role'] === 'agency'): ?>
                    <div class="stat-card">
                        <i class="fas fa-car"></i>
                        <h3>Total Cars</h3>
                        <p><?php echo $total_cars; ?></p>
                    </div>
                <?php endif; ?>
                
                <div class="stat-card">
                    <i class="fas fa-calendar-check"></i>
                    <h3>Total Bookings</h3>
                    <p><?php echo $total_bookings; ?></p>
                </div>
            </div>

            <div class="recent-activity">
                <h2>Recent Activity</h2>
                <div class="activity-list">
                    <?php foreach ($recent_activities as $activity): ?>
                        <div class="activity-item">
                            <div class="activity-icon">
                                <i class="fas fa-calendar"></i>
                            </div>
                            <div class="activity-details">
                                <?php if ($user['role'] === 'admin'): ?>
                                    <p>
                                        <strong><?php echo htmlspecialchars($activity['user_name']); ?></strong> 
                                        booked 
                                        <strong><?php echo htmlspecialchars($activity['car_title']); ?></strong> 
                                        from 
                                        <strong><?php echo htmlspecialchars($activity['agency_name']); ?></strong>
                                    </p>
                                <?php elseif ($user['role'] === 'agency'): ?>
                                    <p>
                                        <strong><?php echo htmlspecialchars($activity['user_name']); ?></strong> 
                                        booked 
                                        <strong><?php echo htmlspecialchars($activity['car_title']); ?></strong>
                                    </p>
                                <?php else: ?>
                                    <p>
                                        You booked 
                                        <strong><?php echo htmlspecialchars($activity['car_title']); ?></strong> 
                                        from 
                                        <strong><?php echo htmlspecialchars($activity['agency_name']); ?></strong>
                                    </p>
                                <?php endif; ?>
                                <span class="activity-status status-<?php echo $activity['status']; ?>">
                                    <?php echo ucfirst($activity['status']); ?>
                                </span>
                                <span class="activity-date">
                                    <?php echo date('M d, Y', strtotime($activity['created_at'])); ?>
                                </span>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>
        </main>
    </div>
</body>
</html>