<?php
session_start();
require_once 'db.php';

// Check if user is logged in and is an agency
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'agency') {
    header('Location: login.php');
    exit();
}

// Check if car ID is provided
if (!isset($_GET['id'])) {
    header('Location: my_cars.php');
    exit();
}

$car_id = (int)$_GET['id'];
$success_message = $error_message = '';

// Fetch car details
try {
    $stmt = $conn->prepare("SELECT * FROM cars WHERE id = ? AND posted_by = ?");
    $stmt->bind_param('ii', $car_id, $_SESSION['user_id']);
    $stmt->execute();
    $result = $stmt->get_result();
    $car = $result->fetch_assoc();

    if (!$car) {
        header('Location: my_cars.php');
        exit();
    }
} catch (Exception $e) {
    error_log("Error fetching car: " . $e->getMessage());
    header('Location: my_cars.php');
    exit();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Get form data
    $title = $_POST['title'] ?? '';
    $make = $_POST['make'] ?? '';
    $model = $_POST['model'] ?? '';
    $year = $_POST['year'] ?? '';
    $price = $_POST['price'] ?? '';
    $location = $_POST['location'] ?? '';
    $description = $_POST['description'] ?? '';
    $status = $_POST['status'] ?? 'available';
    
    // Validate form data
    if (empty($title) || empty($make) || empty($model) || empty($year) || 
        empty($price) || empty($location) || empty($description)) {
        $error_message = "All fields are required";
    } else {
        try {
            $image_path = $car['image_url'] ?? ''; // Keep existing image by default
            
            // Handle new image selection if provided
            if (isset($_POST['image_path']) && !empty($_POST['image_path'])) {
                $image_path = $_POST['image_path'];
                
                // Validate that the image exists in the assets folder
                $allowed_paths = array(
                    'assets/images/cars/',
                    'assets/images/'
                );
                
                $valid_path = false;
                foreach ($allowed_paths as $path) {
                    if (file_exists($path . $image_path)) {
                        $valid_path = true;
                        $image_path = $path . $image_path;
                        break;
                    }
                }
                
                if (!$valid_path) {
                    throw new Exception("Invalid image path. Please select an image from the assets folder.");
                }
            } else {
                // Keep existing image if no new image is selected
                $image_path = $car['image_url'];
            }
            
            // Update car data in database
            $query = "UPDATE cars SET 
                     title = ?, make = ?, model = ?, year = ?, 
                     price_per_day = ?, location = ?, description = ?, 
                     image_url = ?, status = ? 
                     WHERE id = ? AND posted_by = ?";
            
            $stmt = $conn->prepare($query);
            if ($stmt === false) {
                throw new Exception("Failed to prepare query: " . $conn->error);
            }
            
            $stmt->bind_param('sssidssssii', 
                            $title, 
                            $make, 
                            $model, 
                            $year, 
                            $price, 
                            $location, 
                            $description, 
                            $image_path, 
                            $status, 
                            $car_id, 
                            $_SESSION['user_id']);
            
            if ($stmt->execute()) {
                $success_message = "Car updated successfully!";
                // Refresh car data
                $stmt = $conn->prepare("SELECT * FROM cars WHERE id = ? AND posted_by = ?");
                $stmt->bind_param('ii', $car_id, $_SESSION['user_id']);
                $stmt->execute();
                $result = $stmt->get_result();
                $car = $result->fetch_assoc();
            } else {
                throw new Exception("Failed to update car: " . $stmt->error);
            }
        } catch (Exception $e) {
            error_log("Error updating car: " . $e->getMessage());
            $error_message = "Failed to update car. " . $e->getMessage();
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Car - RCar</title>
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
                <h1>Edit Car</h1>
                <a href="my_cars.php" class="btn-secondary">
                    <i class="fas fa-arrow-left"></i> Back to My Cars
                </a>
            </div>

            <div class="edit-car-container">
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

                <form method="POST" class="edit-car-form">
                    <div class="form-grid">
                        <div class="form-group">
                            <label for="title">Car Title*</label>
                            <input type="text" id="title" name="title" 
                                   value="<?php echo htmlspecialchars($car['title']); ?>" required>
                        </div>

                        <div class="form-group">
                            <label for="make">Make*</label>
                            <input type="text" id="make" name="make" 
                                   value="<?php echo htmlspecialchars($car['make']); ?>" required>
                        </div>

                        <div class="form-group">
                            <label for="model">Model*</label>
                            <input type="text" id="model" name="model" 
                                   value="<?php echo htmlspecialchars($car['model']); ?>" required>
                        </div>

                        <div class="form-group">
                            <label for="year">Year*</label>
                            <input type="number" id="year" name="year" 
                                   min="1900" max="<?php echo date('Y') + 1; ?>" 
                                   value="<?php echo htmlspecialchars($car['year']); ?>" required>
                        </div>

                        <div class="form-group">
                            <label for="price">Daily Rate (USD)*</label>
                            <input type="number" id="price" name="price" 
                                   min="0" step="0.01" 
                                   value="<?php echo htmlspecialchars($car['price_per_day']); ?>" required>
                        </div>

                        <div class="form-group">
                            <label for="location">Location*</label>
                            <input type="text" id="location" name="location" 
                                   value="<?php echo htmlspecialchars($car['location']); ?>" required>
                        </div>

                        <div class="form-group">
                            <label for="status">Status*</label>
                            <select id="status" name="status" required>
                                <option value="available" <?php echo $car['status'] === 'available' ? 'selected' : ''; ?>>
                                    Available
                                </option>
                                <option value="maintenance" <?php echo $car['status'] === 'maintenance' ? 'selected' : ''; ?>>
                                    Under Maintenance
                                </option>
                            </select>
                        </div>
                    </div>

                    <div class="form-group">
                        <label for="description">Description*</label>
                        <textarea id="description" name="description" rows="5" required><?php echo htmlspecialchars($car['description']); ?></textarea>
                    </div>

                    <div class="form-group">
                        <label for="image_path">Car Image</label>
                        <select id="image_path" name="image_path">
                            <option value="">Select an image</option>
                            <?php
                            // Get all images from the assets folder
                            $image_dirs = array('assets/images/cars/', 'assets/images/');
                            foreach ($image_dirs as $dir) {
                                if (is_dir($dir)) {
                                    $images = glob($dir . '*.{jpg,jpeg,png,webp}', GLOB_BRACE);
                                    foreach ($images as $image) {
                                        $relative_path = str_replace($dir, '', $image);
                                        echo '<option value="' . htmlspecialchars($relative_path) . '" 
                                             ' . (basename($car['image_url']) === $relative_path ? 'selected' : '') . '>
                                            ' . htmlspecialchars($relative_path) . '
                                        </option>';
                                    }
                                }
                            }
                            ?>
                        </select>
                        <p class="help-text">Select an image from the assets folder</p>
                    </div>

                    <button type="submit" class="submit-btn">
                        <i class="fas fa-save"></i> Save Changes
                    </button>
                </form>
            </div>
        </div>
    </div>

    <?php include 'includes/footer.php'; ?>
</body>
</html> 