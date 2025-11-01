<?php
// PEP_Main.php (updated: removed style tag, added link to styles.css, removed animation classes and JS observer)

ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

session_start(); // Start session for login state
include 'config.php'; // Include DB connection

$success = $error = '';

// No POST handling here anymore, as it's moved to login.php

$conn->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
<title>Petrongolo Evergreen Plantation</title>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-QWTKZyjpPEjISv5WaRU9OFeRpok6YctnYmDr5pNlyT2bRjXh0JMhjY6hW+ALEwIH" crossorigin="anonymous">
<link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Oswald:wght@400;700&family=Roboto:wght@400;700&display=swap">
<link rel="icon" type="image/png" href="Tree.png">
<link rel="stylesheet" href="styles.css">
</head>
<body>
<?php if (isset($_SESSION['error'])) { echo "<div class='alert alert-danger'>".$_SESSION['error']."</div>"; unset($_SESSION['error']); } ?>
<?php if ($success) echo "<div class='alert alert-success'>$success</div>"; ?>
<?php if ($error) echo "<div class='alert alert-danger'>$error</div>"; ?>

<?php if (isset($_SESSION['user_id']) && $_SESSION['role'] === 'customer'): ?>
  <a href="PEP_CustomerAccount.php" class="btn btn-primary login-btn">My Account</a>
<?php else: ?>
  <a href="login.php?return=PEP_Main.php" class="btn btn-primary login-btn">Login / Sign Up</a>
<?php endif; ?>
<img src="Banner_Logo.png" class="img-fluid banner" alt="Banner Logo">
<div class="navigation-bar">
<div class="container">
<div class="d-flex justify-content-between align-items-center">
<a class="nav-link" href="PEP_AboutUs.php">About</a>
<img src="Tree.png" class="tree-divider" alt="Tree Divider">
<a class="nav-link" href="PEP_Catalog.php">Catalog</a>
<img src="Tree.png" class="tree-divider" alt="Tree Divider">
<a class="nav-link" href="PEP_Reviews.php">Reviews</a>
<img src="Tree.png" class="tree-divider" alt="Tree Divider">
<a class="nav-link" href="PEP_VisitUs.php">Visit Us</a>
<img src="Tree.png" class="tree-divider" alt="Tree Divider">
<a class="nav-link" href="PEP_ContactUs.php">Contact</a>
</div>
</div>
</div>
<div id="heroCarousel" class="carousel slide hero-carousel" data-bs-ride="carousel">
<div class="carousel-inner">
<div class="carousel-item active">
<img src="pictures/Carasel1.jpg" class="d-block w-100" alt="Family Picture">
</div>
<div class="carousel-item">
<img src="pictures/Carasel2.jpg" class="d-block w-100" alt="Tree 2">
</div>
<div class="carousel-item">
<img src="pictures/Carasel3.jpg" class="d-block w-100" alt="Tree 3">
</div>
<div class="carousel-item">
<img src="pictures/Carasel4.jpg" class="d-block w-100" alt="Tree 4">
</div>
</div>
<button class="carousel-control-prev" type="button" data-bs-target="#heroCarousel" data-bs-slide="prev">
<span class="carousel-control-prev-icon" aria-hidden="true"></span>
<span class="visually-hidden">Previous</span>
</button>
<button class="carousel-control-next" type="button" data-bs-target="#heroCarousel" data-bs-slide="next">
<span class="carousel-control-next-icon" aria-hidden="true"></span>
<span class="visually-hidden">Next</span>
</button>
</div>
<section id="hours" class="py-5 bg-mid">
<div class="container text-center">
<h3 class="section-title">Operating Hours</h3>
<p class="text-dark">Fri - Sun: 9:00 AM - 5:00 PM</p>
<p class="text-dark">Mon - Thurs: Closed (Check for updates)</p>
<p class="text-dark">Open seasonally starting Black Friday.</p>
</div>
</section>
<section id="gallery" class="py-5">
<div class="container">
<h3 class="section-title">Explore Our Farm</h3>
<div class="row gallery-grid">
<div class="col-md-3">
<a href="PEP_AboutUs.php" class="btn btn-light btn-block">About Us</a>
</div>
<div class="col-md-3">
<a href="PEP_Catalog.php" class="btn btn-light btn-block">Catalog</a>
</div>
<div class="col-md-3">
<a href="PEP_Reviews.php" class="btn btn-light btn-block">Reviews</a>
</div>
<div class="col-md-3">
<a href="PEP_VisitUs.php" class="btn btn-light btn-block">Visit Us</a>
</div>
<div class="col-md-3">
<a href="PEP_ContactUs.php" class="btn btn-light btn-block">Contact</a>
</div>
</div>
</div>
</section>
<section class="cta-section">
<div class="container">
<h2 style="color: #e9ecef;">Hot Sellers</h2><br>
<div class="row">
<div class="col-md-4 mb-4">
<div class="card bg-transparent border-0 product-card">
<img src="Wreath1.jpg" class="card-img-top" alt="Holiday Wreath">
<div class="card-body text-center">
<h5 class="card-title">Holiday Wreath</h5>
<p class="card-text">Handmade fresh wreaths, decorated or plain. Great for doors and mantels.</p>
<p class="card-text">Price:<br>Undecorated: $25<br>Decorated: $30</p>
</div>
</div>
</div>
<div class="col-md-4 mb-4">
<div class="card bg-transparent border-0 product-card">
<img src="https://almostheavenlytrees.com/cdn/shop/products/4312-fraser-fir-christmas-tree_480x480.jpg?v=1458931596" class="card-img-top" alt="Fraser Fir Tree">
<div class="card-body text-center">
<h5 class="card-title">Fraser Fir Tree</h5>
<p class="card-text">A popular Christmas tree with strong branches and excellent needle retention. Perfect for ornaments.</p>
<p class="card-text">Price: TBD</p>
</div>
</div>
</div>
<div class="col-md-4 mb-4">
<div class="card bg-transparent border-0 product-card">
<img src="redPointsetta1.jpg" class="card-img-top" alt="Poinsettia">
<div class="card-body text-center">
<h5 class="card-title">Red Poinsettia</h5>
<p class="card-text">Vibrant red poinsettias to brighten your holiday decor.</p>
<p class="card-text">Price:<br>6 Inch Pot: $10<br>8 Inch Pot: $20</p>
</div>
</div>
</div>
</div>
</div>
</section>
<section id="contact" class="py-5">
<div class="container">
<h3 class="section-title">Get in Touch</h3>
<p class="text-dark">Thank you for reaching out to Petrongolo Evergreen Plantation! We'd love to help. Please leave us a message, and we'll get back to you soon.</p>
<form>
<div class="mb-3">
<label for="name" class="form-label text-dark">Name</label>
<input type="text" class="form-control" id="name">
</div>
<div class="mb-3">
<label for="email" class="form-label text-dark">Email</label>
<input type="email" class="form-control" id="email">
</div>
<div class="mb-3">
<label for="message" class="form-label text-dark">Message</label>
<textarea class="form-control" id="message" rows="3"></textarea>
</div>
<button type="submit" class="btn btn-primary">Send</button>
</form>
</div>
</section>
<footer class="text-center">
<p><a href="https://www.facebook.com/p/Petrongolo-Evergreen-Plantation-100064604442455/" target="_blank" style="color: #fff;">Facebook</a> | Email: info@petrongolo.com | Phone: (609) 567-0336</p>
<iframe src="https://www.google.com/maps/embed?pb=!1m18!1m12!1m3!1d3074.663747983165!2d-74.739235684646!3d39.641058279464!2m3!1f0!2f0!3f0!3m2!1i1024!2i768!4f13.1!3m3!1m2!1s0x89c13a4a5b5e4b0b%3A0x9f2c4e5d6a7b8c9d!2s7541%20Weymouth%20Rd%2C%20Hammonton%2C%20NJ%2008037!5e0!3m2!1sen!2sus!4v1728321600000!5m2!1sen!2sus" allowfullscreen="" loading="lazy" referrerpolicy="no-referrer-when-downgrade"></iframe>
<p>&copy; 2025 Petrongolo Evergreen Plantation. All rights reserved.</p>
<div>
<a href="#" style="color: #fff;">Home</a> | <a href="PEP_AboutUs.php" style="color: #fff;">About</a> | <a href="PEP_Catalog.php" style="color: #fff;">Catalog</a> | <a href="PEP_Reviews.php" style="color: #fff;">Reviews</a> | <a href="PEP_VisitUs.php" style="color: #fff;">Visit Us</a> | <a href="PEP_ContactUs.php" style="color: #fff;">Contact</a>
</div>
</footer>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js" integrity="sha384-YvpcrYf0tY3lHB60NNkmXc5s9fDVZLESaAA55NDzOxhy9GkcIdslK1eN7N6jIeHz" crossorigin="anonymous"></script>
</body>
</html>