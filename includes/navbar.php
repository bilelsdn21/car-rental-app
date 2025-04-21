<?php
require_once 'session_manager.php';
require_once 'helpers.php';

// Get the current page path
$current_page = basename($_SERVER['PHP_SELF']);
?>
<nav class="navbar">
    <div class="logo">
        <a href="index.php">RCar</a>
    </div>
    <div class="nav-links">
        <?php if (strpos($current_page, 'dashboard.php') !== false || 
               strpos($current_page, 'settings.php') !== false || 
               strpos($current_page, 'my_cars.php') !== false || 
               strpos($current_page, 'bookings.php') !== false): ?>
            <a href="dashboard.php" <?php echo $current_page == 'dashboard.php' ? 'class="active"' : ''; ?>>Dashboard</a>
            <?php if (isAdmin()): ?>
                <a href="users.php" <?php echo $current_page == 'users.php' ? 'class="active"' : ''; ?>>Users</a>
            <?php endif; ?>
            <?php if (isAgency()): ?>
                <a href="my_cars.php" <?php echo $current_page == 'my_cars.php' ? 'class="active"' : ''; ?>>My Cars</a>
            <?php endif; ?>
            <a href="bookings.php" <?php echo $current_page == 'bookings.php' ? 'class="active"' : ''; ?>>Bookings</a>
            <a href="settings.php" <?php echo $current_page == 'settings.php' ? 'class="active"' : ''; ?>>Settings</a>
        <?php else: ?>
            <a href="index.php" <?php echo $current_page == 'index.php' ? 'class="active"' : ''; ?>>Home</a>
            <a href="catalog.php" <?php echo $current_page == 'catalog.php' ? 'class="active"' : ''; ?>>Catalog</a>
            
            <?php if (isLoggedIn()): ?>
                <?php if (isAdmin()): ?>
                    <a href="users.php" <?php echo $current_page == 'users.php' ? 'class="active"' : ''; ?>>Users</a>
                    <a href="agencies.php" <?php echo $current_page == 'agencies.php' ? 'class="active"' : ''; ?>>Agencies</a>
                <?php endif; ?>
                
                <?php if (isAgency()): ?>
                    <a href="my_cars.php" <?php echo $current_page == 'my_cars.php' ? 'class="active"' : ''; ?>>My Cars</a>
                    <a href="post_car.php" <?php echo $current_page == 'post_car.php' ? 'class="active"' : ''; ?>>Post Car</a>
                <?php endif; ?>
                
                <a href="bookings.php" <?php echo $current_page == 'bookings.php' ? 'class="active"' : ''; ?>>Bookings</a>
            <?php endif; ?>
            
            <a href="about.php" <?php echo $current_page == 'about.php' ? 'class="active"' : ''; ?>>About</a>
            <a href="contact.php" <?php echo $current_page == 'contact.php' ? 'class="active"' : ''; ?>>Contact</a>
        <?php endif; ?>
    </div>
    <div class="nav-user">
        <?php if (isLoggedIn()): ?>
            <div class="user-menu">
                <span><?php echo htmlspecialchars($_SESSION['name'] ?? 'User'); ?></span>
                <a href="dashboard.php">Dashboard</a>
                <a href="logout.php">Logout</a>
            </div>
        <?php else: ?>
            <div class="auth-buttons">
                <a href="login.php" class="btn-outline">Login</a>
                <a href="register.php" class="btn-primary">Register</a>
            </div>
        <?php endif; ?>
    </div>
</nav>