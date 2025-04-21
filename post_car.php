<?php
// Enable full error reporting for debugging
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);
session_start();
require_once 'db.php';

// Check if user is logged in and is an agency
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'agency') {
    header('Location: login.php');
    exit();
}

$success_message = $error_message = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Get form data
    $title = $_POST['title'] ?? '';
    $make = $_POST['make'] ?? '';
    $model = $_POST['model'] ?? '';
    $year = $_POST['year'] ?? '';
    $price = $_POST['price'] ?? '';
    $location = $_POST['location'] ?? '';
    $description = $_POST['description'] ?? '';
    
    // Validate form data
    if (empty($title) || empty($make) || empty($model) || empty($year) || 
        empty($price) || empty($location) || empty($description)) {
        $error_message = "All fields are required";
    }
    
    // Handle image upload
    if (isset($_FILES['car_image']) && $_FILES['car_image']['error'] == 0) {
        $allowed_types = ['image/jpeg', 'image/png', 'image/webp'];
        $max_size = 5 * 1024 * 1024; // 5MB
        
        $file_type = $_FILES['car_image']['type'];
        $file_size = $_FILES['car_image']['size'];
        $file_name = $_FILES['car_image']['name'];
        
        if (!in_array($file_type, $allowed_types)) {
            $error_message = "Invalid file type. Only JPG, PNG, and WEBP are allowed.";
        } elseif ($file_size > $max_size) {
            $error_message = "File size too large. Maximum allowed size is 5MB.";
        } else {
            $upload_dir = 'assets/images/cars/';
            
            if (!is_dir($upload_dir)) {
                mkdir($upload_dir, 0777, true);
            }
            
            $file_extension = strtolower(pathinfo($file_name, PATHINFO_EXTENSION));
            $new_filename = uniqid() . '.' . $file_extension;
            $target_path = $upload_dir . $new_filename;
            
            if (move_uploaded_file($_FILES['car_image']['tmp_name'], $target_path)) {
                $image_path = 'cars/' . $new_filename;
            } else {
                $error_message = "Failed to upload image.";
            }
        }
    } else {
        $error_message = "Please upload an image.";
    }
    
    if (empty($error_message)) {
        try {
            // Insert car data into database
            $query = "INSERT INTO cars (title, make, model, year, price_per_day, location, 
                     description, image_url, posted_by, status) 
                     VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, 'available')";
            
            $stmt = $conn->prepare($query);
            if ($stmt === false) {
                throw new Exception("Failed to prepare query: " . $conn->error);
            }
            
            $stmt->bind_param('sssidssis', $title, $make, $model, $year, $price, 
                            $location, $description, $image_path, $_SESSION['user_id']);
            
            if ($stmt->execute()) {
                $success_message = "Car posted successfully!";
                // Clear form data after successful submission
                $_POST = array();
            } else {
                $error_message = "Failed to post car: " . $stmt->error;
                throw new Exception("Failed to post car: " . $stmt->error);
            }
        } catch (Exception $e) {
            error_log("Error posting car: " . $e->getMessage());
            $error_message = "Failed to post car: " . $e->getMessage();
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Post a Car - RCar</title>
    <link rel="stylesheet" href="assets/style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
</head>
<body>
    <?php include 'includes/navbar.php'; ?>

    <div class="post-car-header">
        <div class="container">
            <h1>Post a New Car</h1>
            <p>Add a new car to your rental fleet</p>
        </div>
    </div>

    <div class="container">
        <div class="post-car-form-container">
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

            <form method="POST" enctype="multipart/form-data" class="post-car-form">
                <div class="form-grid">
                    <div class="form-group">
                        <label for="title">Car Title*</label>
                        <input type="text" id="title" name="title" value="<?php echo htmlspecialchars($_POST['title'] ?? ''); ?>" required>
                    </div>

                    <div class="form-group">
                        <label for="make">Make*</label>
                        <input type="text" id="make" name="make" value="<?php echo htmlspecialchars($_POST['make'] ?? ''); ?>" required>
                    </div>

                    <div class="form-group">
                        <label for="model">Model*</label>
                        <input type="text" id="model" name="model" value="<?php echo htmlspecialchars($_POST['model'] ?? ''); ?>" required>
                    </div>

                    <div class="form-group">
                        <label for="year">Year*</label>
                        <input type="number" id="year" name="year" min="1900" max="<?php echo date('Y') + 1; ?>" 
                               value="<?php echo htmlspecialchars($_POST['year'] ?? ''); ?>" required>
                    </div>

                    <div class="form-group">
                        <label for="price">Daily Rate (USD)*</label>
                        <input type="number" id="price" name="price" min="0" step="0.01" 
                               value="<?php echo htmlspecialchars($_POST['price'] ?? ''); ?>" required>
                    </div>

                    <div class="form-group">
                        <label for="location">Location*</label>
                        <input type="text" id="location" name="location" 
                               value="<?php echo htmlspecialchars($_POST['location'] ?? ''); ?>" required>
                    </div>
                </div>

                <div class="form-group">
                    <label for="description">Description*</label>
                    <textarea id="description" name="description" rows="5" required><?php echo htmlspecialchars($_POST['description'] ?? ''); ?></textarea>
                </div>

                <div class="form-group">
                    <label for="car_image">Car Image*</label>
                    <input type="file" id="car_image" name="car_image" accept="image/jpeg,image/png,image/webp" required>
                    <p class="help-text">Upload a JPG, PNG, or WEBP image (max 5MB)</p>
                </div>

                <button type="submit" class="submit-btn">
                    <i class="fas fa-plus"></i> Post Car
                </button>
            </form>
        </div>
    </div>

    <?php include 'includes/footer.php'; ?>
</body>
</html>