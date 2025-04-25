
php
CopyInsert
<?php
session_start();
require_once 'db.php';

// Check if user is logged in and is an agency
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'agency') {
    header('Location: login.php');
    exit();
}

// Fetch agency's cars
try {
    $query = "SELECT c.*, 
              (SELECT COUNT(*) FROM bookings b WHERE b.car_id = c.id AND b.status = 'confirmed') as active_bookings 
              FROM cars c 
              WHERE c.posted_by = ? 
              ORDER BY c.created_at DESC";
    
    $stmt = $conn->prepare($query);
    $stmt->bind_param('i', $_SESSION['user_id']);
    $stmt->execute();
    $result = $stmt->get_result();
    $cars = $result->fetch_all(MYSQLI_ASSOC);
    
} catch (Exception $e) {
    error_log("Error fetching cars: " . $e->getMessage());
    $error_message = "Sorry, we encountered an error while loading your cars. Please try again later.";
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>My Cars - RCar</title>
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
                <a href="my_cars.php" class="active">
                    <i class="fas fa-car"></i>
                    <span>My Cars</span>
                </a>
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
                <h1>My Cars</h1>
                <a href="post_car.php" class="btn-add-car">
                    <i class="fas fa-plus"></i> Add New Car
                </a>
            </div>

            <?php if (isset($_GET['error'])): ?>
                <div class="error-message">
                    <i class="fas fa-exclamation-circle"></i>
                    <p>Failed to delete car. Please try again.</p>
                </div>
            <?php endif; ?>

            <?php if (isset($_GET['success'])): ?>
                <div class="success-message">
                    <i class="fas fa-check-circle"></i>
                    <p>Car deleted successfully.</p>
                </div>
            <?php endif; ?>

            <?php if (empty($cars)): ?>
                <div class="no-results">
                    <i class="fas fa-car"></i>
                    <h2>No cars found</h2>
                    <p>You haven't posted any cars yet.</p>
                    <a href="post_car.php" class="btn-primary">Post Your First Car</a>
                </div>
            <?php else: ?>
                <div class="cars-grid">
                    <?php foreach ($cars as $car): ?>
                        <div class="car-card">
                            <div class="car-image">
                                <img src="<?php echo !empty($car['image_url']) ? htmlspecialchars($car['image_url']) : 'assets/default_car.png'; ?>" 
                                     alt="<?php echo htmlspecialchars($car['make'] . ' ' . $car['model']); ?>">
                                <span class="car-status <?php echo strtolower($car['status']); ?>">
                                    <?php echo htmlspecialchars(ucfirst($car['status'])); ?>
                                </span>
                            </div>
                            <div class="car-details">
                                <h3><?php echo htmlspecialchars($car['title']); ?></h3>
                                <div class="car-info">
                                    <p>
                                        <i class="fas fa-tag"></i>
                                        $<?php echo number_format($car['price_per_day'], 2); ?> / day
                                    </p>
                                    <p>
                                        <i class="fas fa-map-marker-alt"></i>
                                        <?php echo htmlspecialchars($car['location']); ?>
                                    </p>
                                    <p>
                                        <i class="fas fa-calendar-check"></i>
                                        <?php echo $car['active_bookings']; ?> Active Bookings
                                    </p>
                                </div>
                                <div class="car-actions">
                                    <a href="edit_car.php?id=<?php echo $car['id']; ?>" class="btn-edit">
                                        <i class="fas fa-edit"></i>
                                        Edit
                                    </a>
                                    <a href="delete_car.php?id=<?php echo $car['id']; ?>" 
                                       class="btn-delete" 
                                       onclick="return confirm('Are you sure you want to delete this car?');">
                                        <i class="fas fa-trash"></i>
                                        Delete
                                    </a>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
        </div>
    </div>

</body>
</html>