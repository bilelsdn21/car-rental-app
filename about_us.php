<!DOCTYPE html>
<html lang="en">
  <head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <link
      href="https://cdn.jsdelivr.net/npm/remixicon@4.3.0/fonts/remixicon.css"
      rel="stylesheet"
    />
    <link rel="stylesheet" href="style_about.css" />
    <link rel="stylesheet" href="styles.css" />
    <script src="script.js" defer></script>
    <title>About Us | RENTI</title>
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
                  <li><a href="profile.php?tab=payments">Payment Methods</a></li>
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

    <main>
      <div class="container">
        <h1>About Our Car Rental Service</h1>
        
        <div class="about-section">
          <img src="https://images.unsplash.com/photo-1506610654-064fbba4780c?w=600&auto=format&fit=crop&q=60&ixlib=rb-4.0.3&ixid=M3wxMjA3fDB8MHxzZWFyY2h8NzN8fGNhcnxlbnwwfHwwfHx8MA%3D%3D" alt="Our rental cars">
          
          <div class="about-text">
            <h2>Our Story</h2>
            <p>---------exemple de texte -------</p>                
            <h2>Our Mission</h2>
            <p>We strive to make car rental simple. Our team is dedicated to providing excellent customer service and well-maintained vehicles at competitive prices.</p>
            
            <h2>Why Choose Us?</h2>
            <p>With Rcar Rentals, you get transparent pricing, no hidden fees, 24/7 customer support, and a wide selection of vehicles to meet every need and budget.</p>
          </div>
        </div>
        
        <h2>What We Offer</h2>
        <div class="features">
          <div class="feature">
            <h3>Wide Selection</h3>
            <p>From economy cars to luxury SUVs, we have the perfect vehicle for your needs.</p>
          </div>
          
          <div class="feature">
            <h3>24/7 Support</h3>
            <p>Our customer service team is available around the clock to assist you.</p>
          </div>
          
          <div class="feature">
            <h3>Competitive Prices</h3>
            <p>We offer some of the most affordable rates in the industry with no hidden fees.</p>
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
            <li><a href="#">Demo</a></li>
            <li><a href="#">Security</a></li>
            <li><a href="#">FAQ</a></li>
            <li><a href="#">Features</a></li>
          </ul>
        </div>
        <div class="footer__col">
          <h4>Follow Us</h4>
          <ul class="footer__socials">
            <li><a href="#"><i class="ri-facebook-fill"></i></a></li>
            <li><a href="#"><i class="ri-twitter-fill"></i></a></li>
            <li><a href="#"><i class="ri-linkedin-fill"></i></a></li>
          </ul>
        </div>
      </div>
      <div class="footer__bar">
        Copyright © 2024 Web Design Mastery. All rights reserved.
      </div>
    </footer>
  </body>
</html>