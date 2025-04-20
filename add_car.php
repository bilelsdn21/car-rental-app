<?php
session_start();
require_once 'db.php';

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'agency') {
    header("Location: index.html");
    exit();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $title = $_POST['title'];
    $description = $_POST['description'];
    $image = $_POST['image'];
    $price = $_POST['price'];
    $posted_by = $_SESSION['user_id'];

    try {
        $stmt = $pdo->prepare("INSERT INTO cars (title, description, image, price, posted_by) VALUES (?, ?, ?, ?, ?)");
        $stmt->execute([$title, $description, $image, $price, $posted_by]);
        header("Location: dashboard.php");
        exit();
    } catch (PDOException $e) {
        $error = "Failed to add car: " . $e->getMessage();
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Add New Car - Rental Site</title>
    <link rel="stylesheet" href="assets/style.css">
</head>
<body>
    <div class="container">
        <div class="form-container">
            <h1>Add New Car</h1>
            <?php if (isset($error)): ?>
                <div class="error"><?php echo $error; ?></div>
            <?php endif; ?>
            <form method="POST">
                <div class="form-group">
                    <label for="title">Title:</label>
                    <input type="text" id="title" name="title" required>
                </div>
                <div class="form-group">
                    <label for="description">Description:</label>
                    <textarea id="description" name="description" required></textarea>
                </div>
                <div class="form-group">
                    <label for="image">Image URL:</label>
                    <input type="url" id="image" name="image" required>
                </div>
                <div class="form-group">
                    <label for="price">Price per day ($):</label>
                    <input type="number" id="price" name="price" step="0.01" required>
                </div>
                <button type="submit">Add Car</button>
            </form>
            <a href="dashboard.php" class="button">Back to Dashboard</a>
        </div>
    </div>
</body>
</html> 