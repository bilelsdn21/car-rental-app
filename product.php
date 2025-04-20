<?php
require_once 'config.php';
session_start();

if (!isset($_GET['id'])) {
    header('Location: catalog.php');
    exit();
}

$car_id = $_GET['id'];
$stmt = $conn->prepare("SELECT * FROM cars WHERE id = ?");
$stmt->bind_param("i", $car_id);
$stmt->execute();
$result = $stmt->get_result();
$car = $result->fetch_assoc();

if (!$car) {
    header('Location: catalog.php');
    exit();
}
?>

<!DOCTYPE html>
<html lang="en">
  <head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <link
      href="https://cdn.jsdelivr.net/npm/remixicon@4.3.0/fonts/remixicon.css"
      rel="stylesheet"
    />
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-QWTKZyjpPEjISv5WaRU9OFeRpok6YctnYmDr5pNlyT2bRjXh0JMhjY6hW+ALEwIH" crossorigin="anonymous">
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js" integrity="sha384-YvpcrYf0tY3lHB60NNkmXc5s9fDVZLESaAA55NDzOxhy9GkcIdslK1eN7N6jIeHz" crossorigin="anonymous"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.7.2/css/all.min.css" integrity="sha512-Evv84Mr4kqVGRNSgIGL/F/aIDqQb7xQ2vcrdIwxfjThSH8CSR7PBEakCr51Ck+w+/U6swU2Im1vVX0SVk9ABhg==" crossorigin="anonymous" referrerpolicy="no-referrer" />
    <link rel="stylesheet" href="styles.css" />
    <title><?php echo htmlspecialchars($car['make'] . ' ' . $car['model']); ?> - Renti</title>
    <script src="script.js" defer></script>
    <style>
        .product__container {
            max-width: 1200px;
            margin: 0 auto;
            padding: 2rem;
        }

        .product__header {
            text-align: center;
            margin-bottom: 2rem;
        }

        .product__title {
            font-size: 2.5rem;
            color: #333;
            margin-bottom: 0.5rem;
        }

        .product__subtitle {
            color: #666;
            font-size: 1.1rem;
        }

        .product__content {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 2rem;
            margin-top: 2rem;
        }

        .carousel {
            background: #fff;
            border-radius: 10px;
            overflow: hidden;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
        }

        .carousel-item {
            background: #f8f9fa;
        }

        .carousel-item img {
            width: 100%;
            height: 400px;
            object-fit: cover;
        }

        .product__details {
            background: #fff;
            padding: 2rem;
            border-radius: 10px;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
        }

        .product__info h3 {
            color: #333;
            margin-bottom: 1.5rem;
        }

        .product__specs {
            display: grid;
            grid-template-columns: repeat(2, 1fr);
            gap: 1rem;
            margin-top: 2rem;
        }

        .product__spec {
            display: flex;
            align-items: center;
            gap: 0.5rem;
            color: #666;
        }

        .product__spec i {
            color: #4a6cf7;
            font-size: 1.2rem;
        }

        .rental__form {
            margin-top: 2rem;
            padding-top: 2rem;
            border-top: 1px solid #eee;
        }

        .form__group {
            margin-bottom: 1rem;
        }

        .form__group label {
            display: block;
            margin-bottom: 0.5rem;
            color: #333;
        }

        .form__group input {
            width: 100%;
            padding: 0.75rem;
            border: 1px solid #ddd;
            border-radius: 5px;
            font-size: 1rem;
        }

        .price__display {
            margin: 1.5rem 0;
            padding: 1rem;
            background: #f8f9fa;
            border-radius: 5px;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .price__value {
            font-size: 1.25rem;
            font-weight: 600;
            color: #4a6cf7;
        }

        .rent__btn {
            width: 100%;
            padding: 1rem;
            background: #4a6cf7;
            color: #fff;
            border: none;
            border-radius: 5px;
            font-size: 1rem;
            cursor: pointer;
            transition: background 0.3s ease;
        }

        .rent__btn:hover {
            background: #3a5cf5;
        }

        @media (max-width: 768px) {
            .product__content {
                grid-template-columns: 1fr;
            }
        }
    </style>
  </head>
  <body>
  <header>
        <nav>
            <div class="nav__header">
              <div class="nav__logo">
                <a href="index (3).php">Rcar</a>
              </div>
              <div class="nav__menu__btn" id="menu-btn">
                <i class="ri-menu-line"></i>
              </div>
            </div>
            <ul class="nav__links" >
              <li><a href="index (3).php">Home</a></li>
              <li><a href="catalog.php">Catalog</a></li>
              <li><a href="about_us.php">About Us</a></li>
              <li><a href="contact.php">Contact</a></li>
              <?php if (isLoggedIn()): ?>
                <li><a href="profile.php?tab=orders">Orders</a></li>
                <?php if (isAdmin()): ?>
                  <li><a href="admin_dashboard.php">Dashboard</a></li>
                  <li><a href="admin_dashboard.php?content=users">Users</a></li>
                <?php endif; ?>
              <?php endif; ?>
            </ul>
            <div class="nav__btn">
              <?php include 'check_login.php'; ?>
              <?php if (isLoggedIn()): ?>
                <div class="user-menu">
                  <div class="user-icon">
                    <i class="ri-user-line"></i>
                  </div>
                  <div class="user-dropdown">
                    <div class="user-info">
                      <span><?php echo htmlspecialchars(getUserEmail()); ?></span>
                    </div>
                    <ul class="dropdown-menu">
                      <li><a href="profile.php">Profile</a></li>
                      <li><a href="profile.php?tab=orders">My Orders</a></li>
                      <?php if (isAdmin()): ?>
                        <li><a href="admin_dashboard.php">Admin Dashboard</a></li>
                        <li><a href="admin_dashboard.php?content=users">Manage Users</a></li>
                      <?php endif; ?>
                      <li><a href="logout.php">Logout</a></li>
                    </ul>
                  </div>
                </div>
              <?php else: ?>
                <a href="login(1).php"><button class="btn" >Get Started</button></a>
              <?php endif; ?>
            </div>
        </nav>
    </header>

    <main class="product__container">
      <div class="product__header">
        <h1 class="product__title"><?php echo htmlspecialchars($car['make'] . ' ' . $car['model']); ?></h1>
        <p class="product__subtitle">Premium SUV with spacious interior and advanced features</p>
      </div>
      
      <div class="product__content">
        <div class="carousel">
                <div id="carouselExampleIndicators" class="carousel slide">
                    <div class="carousel-indicators">
                        <button type="button" data-bs-target="#carouselExampleIndicators" data-bs-slide-to="0" class="active" aria-current="true" aria-label="Slide 1"></button>
                        <button type="button" data-bs-target="#carouselExampleIndicators" data-bs-slide-to="1" aria-label="Slide 2"></button>
                        <button type="button" data-bs-target="#carouselExampleIndicators" data-bs-slide-to="2" aria-label="Slide 3"></button>
                    </div>
                    <div class="carousel-inner">
                    <div class="carousel-item active">
                        <img src="<?php echo htmlspecialchars($car['image']); ?>" 
                             alt="<?php echo htmlspecialchars($car['make'] . ' ' . $car['model']); ?>"
                             onerror="this.src='assets/default-car.jpg'">
                    </div>
                    <div class="carousel-item">
                        <img src="https://gwm.iq/sites/default/files/styles/16_9_small/public/2021-08/h6-3rd-black-interior2.jpg?itok=Q92WJZcx" alt="haval_interior">
                    </div>
                    <div class="carousel-item">
                        <img src="https://thorphaval.co.za/wp-content/uploads/2023/07/Haval-H6-GT-Image-13.jpg.webp" class="" alt="haval_interior">
                    </div>
                    </div>
                </div>
            </div>
        
            
            <div class="product__details">
            <div class="product__info">
                <h3>Vehicle Description</h3>
                <p><strong>Year:</strong> <?php echo htmlspecialchars($car['year']); ?></p>
                <p><strong>Category:</strong> <?php echo htmlspecialchars($car['category']); ?></p>
                <p><strong>Price per Day:</strong> $<?php echo number_format($car['price_per_day'], 2); ?></p>
                
                <div class="product__specs">
                <div class="product__spec">
                    <i class="ri-speed-up-line"></i>
                    <span>200 km/h Top Speed</span>
                </div>
                <div class="product__spec">
                    <i class="ri-settings-5-line"></i>
                    <span>6 Speed Automatic</span>
                </div>
                <div class="product__spec">
                    <i class="ri-roadster-line"></i>
                    <span>5 Seats</span>
                </div>
                <div class="product__spec">
                    <i class="ri-gas-station-line"></i>
                    <span>8.5L/100km</span>
                </div>
                <div class="product__spec">
                    <i class="ri-bluetooth-connect-line"></i>
                    <span>Bluetooth</span>
                </div>
                <div class="product__spec">
                    <i class="ri-airplay-line"></i>
                    <span>Apple CarPlay</span>
                </div>
                </div>
            </div>
            
            <div class="rental__form">
                <h3>Rental Details</h3>
                <?php if (isLoggedIn()): ?>
                    <form id="rental-form" action="process_booking.php" method="POST">
                        <input type="hidden" name="car_id" value="<?php echo $car_id; ?>">
                        <div class="form__group">
                            <label for="pickup-date">Pickup Date</label>
                            <input type="date" id="pickup-date" name="pickup_date" required min="<?php echo date('Y-m-d'); ?>">
                        </div>
                        <div class="form__group">
                            <label for="return-date">Return Date</label>
                            <input type="date" id="return-date" name="return_date" required>
                        </div>
                        <div class="form__group">
                            <label for="pickup-location">Pickup Location</label>
                            <input type="text" id="pickup-location" name="pickup_location" placeholder="Enter pickup location" required>
                        </div>
                        
                        <div class="price__display">
                            <span class="price__label">Total Price:</span>
                            <span class="price__value" id="total-price">$0.00</span>
                        </div>
                        
                        <button type="submit" class="btn rent__btn">Rent Now</button>
                    </form>
                <?php else: ?>
                    <div class="login-prompt">
                        <p>Please log in to rent this car.</p>
                        <a href="login(1).php?redirect=<?php echo urlencode($_SERVER['REQUEST_URI']); ?>" class="btn">Login to Continue</a>
                    </div>
                <?php endif; ?>
            </div>
            </div>
      </div>
    </main>

    <footer>
      <div class="section__container footer__container">
        <div class="footer__col">
          <h4>Resources</h4>
          <ul class="footer__links">
            <li><a href="#">Installation Manual</a></li>
            <li><a href="#">Release Note</a></li>
            <li><a href="#">Community Help</a></li>
          </ul>
        </div>
        <div class="footer__col">
          <h4>Company</h4>
          <ul class="footer__links">
            <li><a href="#">About Us</a></li>
            <li><a href="#">Career</a></li>
            <li><a href="#">Press</a></li>
            <li><a href="#">Support</a></li>
          </ul>
        </div>
        <div class="footer__col">
          <h4>Product</h4>
          <ul class="footer__links">
            <li><a href="#">Trailer</a></li>
            <li><a href="#">Security</a></li>
            <li><a href="#">FAQ</a></li>
            <li><a href="#">Features</a></li>
          </ul>
        </div>
        <div class="footer__col">
          <h4>Follow Us</h4>
          <ul class="footer__socials">
            <li>
              <a href="#"><i class="ri-facebook-fill"></i></a>
            </li>
            <li>
              <a href="#"><i class="ri-twitter-fill"></i></a>
            </li>
            <li>
              <a href="#"><i class="ri-linkedin-fill"></i></a>
            </li>
          </ul>
        </div>
      </div>
      <div class="footer__bar">
        Copyright © 2024 Web Design Mastery. All rights reserved.
      </div>
    </footer>

    <script>
    document.addEventListener('DOMContentLoaded', function() {
        const pickupDate = document.getElementById('pickup-date');
        const returnDate = document.getElementById('return-date');
        const totalPrice = document.getElementById('total-price');
        const pricePerDay = <?php echo $car['price_per_day']; ?>;
        
        function calculateTotal() {
            if (pickupDate.value && returnDate.value) {
                const start = new Date(pickupDate.value);
                const end = new Date(returnDate.value);
                const days = Math.ceil((end - start) / (1000 * 60 * 60 * 24)) + 1;
                
                if (days > 0) {
                    const total = days * pricePerDay;
                    totalPrice.textContent = '$' + total.toFixed(2);
                } else {
                    totalPrice.textContent = '$0.00';
                }
            }
        }
        
        if (pickupDate) {
            pickupDate.addEventListener('change', function() {
                if (returnDate) {
                    returnDate.min = this.value;
                }
                calculateTotal();
            });
        }
        
        if (returnDate) {
            returnDate.addEventListener('change', calculateTotal);
        }
    });
    </script>
  </body>
</html>