<?php
session_start();
require_once 'db.php';

// Initialize arrays for query parameters
$params = [];
$types = '';
$is_search = isset($_GET['search']);

// Handle filters
$location = isset($_GET['location']) ? trim($_GET['location']) : '';
$category = isset($_GET['category']) ? trim($_GET['category']) : '';
$make = isset($_GET['make']) ? trim($_GET['make']) : '';
$min_price = isset($_GET['min_price']) && !empty($_GET['min_price']) ? (float)$_GET['min_price'] : 0;
$max_price = isset($_GET['max_price']) && !empty($_GET['max_price']) ? (float)$_GET['max_price'] : 1000000;
$min_days = isset($_GET['min_days']) && !empty($_GET['min_days']) ? (int)$_GET['min_days'] : 1;
$max_days = isset($_GET['max_days']) && !empty($_GET['max_days']) ? (int)$_GET['max_days'] : 30;

try {
    // Get all distinct values for dropdowns
    $makes_query = "SELECT DISTINCT make FROM cars WHERE status = 'available' ORDER BY make";
    $makes_result = $conn->query($makes_query);
    $makes = $makes_result->fetch_all(MYSQLI_ASSOC);

    $categories_query = "SELECT DISTINCT category FROM cars WHERE status = 'available' ORDER BY category";
    $categories_result = $conn->query($categories_query);
    $categories = $categories_result->fetch_all(MYSQLI_ASSOC);

    $locations_query = "SELECT DISTINCT location FROM cars WHERE status = 'available' ORDER BY location";
    $locations_result = $conn->query($locations_query);
    $locations = $locations_result->fetch_all(MYSQLI_ASSOC);

    // Build main search query
    $query = "SELECT c.*, u.name as agency_name 
              FROM cars c 
              JOIN users u ON c.posted_by = u.id 
              WHERE c.status = 'available'";

    if ($is_search) {
        if (!empty($location)) {
            $query .= " AND c.location LIKE ?";
            $params[] = "%$location%";
            $types .= "s";
        }

        if (!empty($category) && $category !== 'All Categories') {
            $query .= " AND c.category = ?";
            $params[] = $category;
            $types .= "s";
        }

        if (!empty($make)) {
            $query .= " AND c.make = ?";
            $params[] = $make;
            $types .= "s";
        }

        if ($min_price > 0 || $max_price < 1000000) {
            $query .= " AND c.price_per_day BETWEEN ? AND ?";
            $params[] = $min_price;
            $params[] = $max_price;
            $types .= "dd";
        }

        if ($min_days > 1 || $max_days < 30) {
            $query .= " AND c.min_rental_days <= ? AND c.max_rental_days >= ?";
            $params[] = $max_days;
            $params[] = $min_days;
            $types .= "ii";
        }
    }

    $query .= " ORDER BY c.created_at DESC";

    // Prepare and execute query
    $stmt = $conn->prepare($query);
    if ($stmt === false) {
        throw new Exception("Failed to prepare query: " . $conn->error);
    }

    if (!empty($params)) {
        $stmt->bind_param($types, ...$params);
    }

    if (!$stmt->execute()) {
        throw new Exception("Failed to execute query: " . $stmt->error);
    }

    $result = $stmt->get_result();
    $cars = $result->fetch_all(MYSQLI_ASSOC);

    // Get unique makes and categories for filters
    $makes_query = "SELECT DISTINCT make FROM cars WHERE status = 'available' ORDER BY make";
    $categories_query = "SELECT DISTINCT category FROM cars WHERE status = 'available' AND category IS NOT NULL ORDER BY category";
    $locations_query = "SELECT DISTINCT location FROM cars WHERE status = 'available' ORDER BY location";

    $makes = $conn->query($makes_query)->fetch_all(MYSQLI_ASSOC);
    $categories = $conn->query($categories_query)->fetch_all(MYSQLI_ASSOC);
    $locations = $conn->query($locations_query)->fetch_all(MYSQLI_ASSOC);
    
} catch (Exception $e) {
    // Log the error and show a user-friendly message
    error_log("Catalog error: " . $e->getMessage());
    $error_message = "Sorry, we encountered an error while loading the catalog. Please try again later.";
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Car Catalog - RCar</title>
    <link rel="stylesheet" href="assets/style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
</head>
<body>
    <?php include 'includes/navbar.php'; ?>

    <div class="catalog-header">
        <div class="container">
            <h1>Available Cars</h1>
            <form id="filterForm" class="filter-form" method="GET">
                <div class="filter-group">
                    <label for="location">
                        <i class="fas fa-map-marker-alt"></i> Location
                    </label>
                    <select id="location" name="location" class="select2-search">
                        <option value="">All Locations</option>
                        <?php foreach ($locations as $loc): ?>
                            <option value="<?php echo htmlspecialchars($loc['location']); ?>"
                                <?php echo $location === $loc['location'] ? 'selected' : ''; ?>>
                                <?php echo htmlspecialchars($loc['location']); ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div class="filter-group">
                    <label for="make">
                        <i class="fas fa-car"></i> Make
                    </label>
                    <select id="make" name="make" class="select2-search">
                        <option value="">All Makes</option>
                        <?php foreach ($makes as $m): ?>
                            <option value="<?php echo htmlspecialchars($m['make']); ?>"
                                <?php echo $make === $m['make'] ? 'selected' : ''; ?>>
                                <?php echo htmlspecialchars($m['make']); ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div class="filter-group">
                    <label for="category">
                        <i class="fas fa-tags"></i> Category
                    </label>
                    <select id="category" name="category" class="select2-search">
                        <option value="">All Categories</option>
                        <?php foreach ($categories as $cat): ?>
                            <option value="<?php echo htmlspecialchars($cat['category']); ?>"
                                <?php echo $category === $cat['category'] ? 'selected' : ''; ?>>
                                <?php echo htmlspecialchars($cat['category']); ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div class="filter-group price-range">
                    <label>
                        <i class="fas fa-dollar-sign"></i> Price Range (per day)
                    </label>
                    <div class="range-inputs">
                        <input type="number" id="min_price" name="min_price" 
                               value="<?php echo $min_price; ?>" min="0" placeholder="Min">
                        <span>-</span>
                        <input type="number" id="max_price" name="max_price" 
                               value="<?php echo $max_price; ?>" min="0" placeholder="Max">
                    </div>
                </div>

                <button type="submit" name="search" class="filter-btn">
                    <i class="fas fa-search"></i> Search
                </button>
                </form>
            </div>
        </div>
    </div>

    <div class="container">
        <?php if (isset($error_message)): ?>
            <div class="no-results">
                <i class="fas fa-exclamation-circle"></i>
                <h2>Error Loading Catalog</h2>
                <p><?php echo htmlspecialchars($error_message); ?></p>
            </div>
        <?php else: ?>
            <?php if (empty($cars)): ?>
                <div class="no-results">
                    <i class="fas fa-car-side"></i>
                    <h2>No Cars Found</h2>
                    <p>Try adjusting your filters to see more results.</p>
                </div>
            <?php else: ?>
                <div class="car-grid">
                    <?php foreach ($cars as $car): ?>
                        <div class="car-card">
                            <img src="<?php echo !empty($car['image_url']) ? htmlspecialchars($car['image_url']) : 'assets/images/default-car.jpg'; ?>" 
                                 alt="<?php echo htmlspecialchars($car['make'] . ' ' . $car['model']); ?>" 
                                 class="car-image">
                            <div class="car-info">
                                <h3><?php echo htmlspecialchars($car['make'] . ' ' . $car['model'] . ' (' . $car['year'] . ')'); ?></h3>
                                <div class="car-meta">
                                    <span><i class="fas fa-map-marker-alt"></i> <?php echo htmlspecialchars($car['location']); ?></span>
                                    <span><i class="fas fa-car"></i> <?php echo htmlspecialchars($car['make'] . ' ' . $car['model']); ?></span>
                                    <span><i class="fas fa-calendar"></i> <?php echo htmlspecialchars($car['year']); ?></span>
                                </div>
                                <div class="car-details">
                                    <div class="car-price">
                                        <span class="price">$<?php echo number_format($car['price_per_day'], 2); ?></span>
                                        <span class="per-day">per day</span>
                                    </div>
                                    <div class="rental-duration">
                                        <span><i class="fas fa-clock"></i> <?php echo $car['min_rental_days']; ?>-<?php echo $car['max_rental_days']; ?> days</span>
                                    </div>
                                </div>
                                <a href="car.php?id=<?php echo $car['id']; ?>" class="view-details-btn">
                                    <i class="fas fa-info-circle"></i> View Details
                                </a>
                            </div>
                            <div class="car-features">
                                <?php 
                                if (!empty($car['features'])) {
                                    $features = json_decode($car['features'], true);
                                    if (is_array($features)) {
                                        foreach (array_slice($features, 0, 3) as $feature): ?>
                                            <span class="feature-tag"><?php echo htmlspecialchars($feature); ?></span>
                                        <?php endforeach;
                                    }
                                }
                                ?>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
        <?php endif; ?>
        </div>
    </div>

    <?php include 'includes/footer.php'; ?>

    <script>
        // Validate date selection
        document.getElementById('start_date').addEventListener('change', function() {
            document.getElementById('end_date').min = this.value;
        });

        // Validate price range
        document.getElementById('min_price').addEventListener('change', function() {
            document.getElementById('max_price').min = this.value;
        });
    </script>
</body>
</html> 