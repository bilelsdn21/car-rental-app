<?php
session_start();
require_once 'db.php';

// Check if user is logged in and is admin
if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] !== 'admin') {
    header('Location: login.php');
    exit();
}

// Handle agency deletion
if (isset($_POST['delete_agency'])) {
    $agency_id = $_POST['agency_id'];
    
    // Begin transaction
    $conn->begin_transaction();
    
    try {
        // Delete agency's cars
        $stmt = $conn->prepare("DELETE FROM cars WHERE posted_by = ?");
        $stmt->bind_param("i", $agency_id);
        $stmt->execute();
        
        // Delete agency's bookings
        $stmt = $conn->prepare("DELETE FROM bookings WHERE car_id IN (SELECT id FROM cars WHERE posted_by = ?)");
        $stmt->bind_param("i", $agency_id);
        $stmt->execute();
        

        
        // Delete agency account
        $stmt = $conn->prepare("DELETE FROM users WHERE id = ? AND role = 'agency'");
        $stmt->bind_param("i", $agency_id);
        $stmt->execute();
        
        $conn->commit();
        $success = "Agency deleted successfully";
    } catch (Exception $e) {
        $conn->rollback();
        $error = "Error deleting agency: " . $e->getMessage();
    }
}

// Get all agencies with their stats
$query = "SELECT u.*, 
          COUNT(DISTINCT c.id) as total_cars,
          COUNT(DISTINCT b.id) as total_bookings,
          SUM(CASE WHEN b.status = 'completed' THEN b.total_price ELSE 0 END) as total_earnings
          FROM users u 
          LEFT JOIN cars c ON u.id = c.posted_by
          LEFT JOIN bookings b ON c.id = b.car_id
          WHERE u.role = 'agency'
          GROUP BY u.id
          ORDER BY u.name";

$result = $conn->query($query);
$agencies = $result->fetch_all(MYSQLI_ASSOC);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Agencies - RCar</title>
    <link rel="stylesheet" href="assets/style.css">
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
                <a href="dashboard.php"><i class="fas fa-home"></i> Dashboard</a>
                <a href="users.php"><i class="fas fa-users"></i> Users</a>
                <a href="agencies.php" class="active"><i class="fas fa-building"></i> Agencies</a>
                <a href="bookings.php"><i class="fas fa-calendar"></i> Bookings</a>
                <a href="settings.php"><i class="fas fa-cog"></i> Settings</a>
            </nav>
        </aside>

        <main class="dashboard-content">
            <div class="dashboard-header">
                <h1>Manage Agencies</h1>
            </div>

            <?php if (isset($success)): ?>
                <div class="success"><?php echo htmlspecialchars($success); ?></div>
            <?php endif; ?>

            <?php if (isset($error)): ?>
                <div class="error"><?php echo htmlspecialchars($error); ?></div>
            <?php endif; ?>

            <div class="table-container">
                <table class="data-table">
                    <thead>
                        <tr>
                            <th>Name</th>
                            <th>Email</th>
                            <th>Total Cars</th>
                            <th>Total Bookings</th>
                            <th>Total Earnings</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($agencies as $agency): ?>
                            <tr>
                                <td><?php echo htmlspecialchars($agency['name']); ?></td>
                                <td><?php echo htmlspecialchars($agency['email']); ?></td>
                                <td><?php echo $agency['total_cars']; ?></td>
                                <td><?php echo $agency['total_bookings']; ?></td>
                                <td>$<?php echo number_format($agency['total_earnings'], 2); ?></td>
                                <td class="actions">
                                    <a href="edit_agency.php?id=<?php echo $agency['id']; ?>" class="btn-icon" title="Edit">
                                        <i class="fas fa-edit"></i>
                                    </a>
                                    <form method="POST" action="" class="inline-form" onsubmit="return confirm('Are you sure you want to delete this agency? This will also delete all their cars and bookings.');">
                                        <input type="hidden" name="agency_id" value="<?php echo $agency['id']; ?>">
                                        <button type="submit" name="delete_agency" class="btn-icon delete" title="Delete">
                                            <i class="fas fa-trash"></i>
                                        </button>
                                    </form>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </main>
    </div>
</body>
</html> 