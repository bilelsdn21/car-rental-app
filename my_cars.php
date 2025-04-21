<?php
session_start();
require_once 'db.php';

// Check if user is logged in and is an agency
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'agency') {
    header('Location: login.php');
    exit();
}

// Handle car deletion
if (isset($_GET['action']) && $_GET['action'] === 'delete' && isset($_GET['id'])) {
    $car_id = (int)$_GET['id'];
    $user_id = $_SESSION['user_id'];
    
    try {
        // First check if the car belongs to this agency
        $stmt = $conn->prepare("SELECT image_url FROM cars WHERE id = ? AND posted_by = ?");
        $stmt->bind_param('ii', $car_id, $user_id);
        $stmt->execute();
        $result = $stmt->get_result();
        $car = $result->fetch_assoc();
        
        if ($car) {
            // First check if there are any active bookings
            $stmt = $conn->prepare("SELECT COUNT(*) as booking_count FROM bookings WHERE car_id = ? AND status IN ('pending', 'confirmed')");
            if (!$stmt) {
                throw new Exception("Failed to prepare booking check query: " . $conn->error);
            }
            
            $stmt->bind_param('i', $car_id);
            if (!$stmt->execute()) {
                throw new Exception("Failed to execute booking check query: " . $stmt->error);
            }
            
            $result = $stmt->get_result();
            if (!$result) {
                throw new Exception("Failed to get booking check result: " . $stmt->error);
            }
            
            $booking = $result->fetch_assoc();
            if ($booking['booking_count'] > 0) {
                throw new Exception("Cannot delete car with active bookings. Please cancel all pending bookings first.");
            }

            // Delete the car from database
            $stmt = $conn->prepare("DELETE FROM cars WHERE id = ? AND posted_by = ?");
            if (!$stmt) {
                throw new Exception("Failed to prepare delete query: " . $conn->error);
            }
            
            $stmt->bind_param('ii', $car_id, $user_id);
            if (!$stmt->execute()) {
                throw new Exception("Failed to delete car: " . $stmt->error);
            }
            
            // Check if rows were affected
            if ($conn->affected_rows > 0) {
                header('Location: my_cars.php?success=1');
                exit();
            } else {
                throw new Exception("No rows affected. Car may not exist.");
            }
        } else {
            throw new Exception("Car not found or does not belong to this agency");
        }
    } catch (Exception $e) {
        error_log("Error deleting car: " . $e->getMessage());
        header('Location: my_cars.php?error=1');
        exit();
    }
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
                <a href="post_car.php" class="btn-primary">
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
                                    <a href="edit_car.php?id=<?php echo $car['id']; ?>" class="btn-secondary">
                                        <i class="fas fa-edit"></i>
                                        Edit
                                    </a>
                                    <a href="delete_car.php?id=<?php echo $car['id']; ?>" 
                                       class="btn-danger" 
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

    <style>
    .cars-grid {
        display: grid;
        grid-template-columns: repeat(auto-fill, minmax(300px, 1fr));
        gap: 2rem;
        padding: 2rem;
    }

    .car-card {
        background: white;
        border-radius: 8px;
        box-shadow: 0 2px 4px rgba(0,0,0,0.1);
        overflow: hidden;
    }

    .car-image {
        width: 100%;
        height: 200px;
        overflow: hidden;
    }

    .car-image img {
        width: 100%;
        height: 100%;
        object-fit: cover;
    }

    .car-details {
        padding: 1.5rem;
    }

    .car-details h3 {
        margin: 0 0 1rem 0;
        color: #333;
    }

    .car-info {
        margin: 0.5rem 0;
        color: #666;
        display: flex;
        align-items: center;
        gap: 0.5rem;
    }

    .car-actions {
        display: flex;
        gap: 1rem;
        margin-top: 1.5rem;
    }

    .btn-secondary {
        background: #f5f5f5;
        color: #333;
        padding: 0.5rem 1rem;
        border-radius: 4px;
        text-decoration: none;
        display: flex;
        align-items: center;
        gap: 0.5rem;
        transition: background-color 0.2s;
    }

    .btn-secondary:hover {
        background: #e0e0e0;
    }

    .btn-danger {
        background: #ef5350;
        color: white;
        border: none;
        padding: 0.5rem 1rem;
        border-radius: 4px;
        cursor: pointer;
        display: flex;
        align-items: center;
        gap: 0.5rem;
        transition: background-color 0.2s;
    }

    .btn-danger:hover {
        background: #e53935;
    }

    .no-results {
        text-align: center;
        padding: 4rem 2rem;
        background: white;
        border-radius: 8px;
        box-shadow: 0 2px 4px rgba(0,0,0,0.1);
    }

    .no-results i {
        font-size: 4rem;
        color: #e0e0e0;
        margin-bottom: 1rem;
    }

    .no-results h2 {
        color: #333;
        margin-bottom: 1rem;
    }

    .no-results p {
        color: #666;
    }

    .success-message, .error-message {
        padding: 1rem;
        border-radius: 4px;
        margin: 1rem 0;
        display: flex;
        align-items: center;
        gap: 0.5rem;
    }

    .success-message {
        background: #f0f9eb;
        color: #2e7d32;
    }

    .error-message {
        background: #fff3f3;
        color: #c62828;
    }

    .success-message i, .error-message i {
        font-size: 1.25rem;
    }
    </style>
</body>
</html>