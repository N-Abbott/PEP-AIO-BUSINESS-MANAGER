<?php
// PEP_ContactUs.php (updated: removed style tag, added link to styles.css, removed animation classes and JS observer)

session_start(); // Start session for login state

?>

<!DOCTYPE html>
<html lang="en">
<head>
<title>Contact Us - Petrongolo Evergreen Plantation</title>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-QWTKZyjpPEjISv5WaRU9OFeRpok6YctnYmDr5pNlyT2bRjXh0JMhjY6hW+ALEwIH" crossorigin="anonymous">
<link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Oswald:wght@400;700&family=Roboto:wght@400;700&display=swap">
<link rel="stylesheet" href="styles.css">
</head>
<body>
<?php if (isset($_SESSION['user_id']) && $_SESSION['role'] === 'customer'): ?>
  <a href="PEP_CustomerAccount.php" class="btn btn-primary login-btn">My Account</a>
<?php else: ?>
  <a href="login.php?return=PEP_ContactUs.php" class="btn btn-primary login-btn">Login / Sign Up</a>
<?php endif; ?>
<img src="Banner_Logo.png" class="img-fluid banner" alt="Banner Logo">
<div class="navigation-bar">
<div class="container">
<div class="d-flex justify-content-between align-items-center">
<a class="nav-link" href="PEP_Main.php">Home</a>
<img src="Tree.png" class="tree-divider" alt="Tree Divider">
<a class="nav-link" href="PEP_AboutUs.php">About</a>
<img src="Tree.png" class="tree-divider" alt="Tree Divider">
<a class="nav-link" href="PEP_Catalog.php">Catalog</a>
<img src="Tree.png" class="tree-divider" alt="Tree Divider">
<a class="nav-link" href="PEP_Reviews.php">Reviews</a>
<img src="Tree.png" class="tree-divider" alt="Tree Divider">
<a class="nav-link" href="PEP_VisitUs.php">Visit Us</a>
</div>
</div>
</div>
<section id="contact-intro" class="py-5">
<div class="container">
<h1 class="section-title">Contact Us</h1>
<p>Have questions about our trees, hours, or anything else? Reach out to us! We're here to help make your holiday season merry and bright.</p>
</div>
</section>
<section id="contact-info" class="py-5 bg-mid">
<div class="container text-center">
<h2 class="section-title">Our Contact Details</h2>
<p><strong>Address:</strong> 7541 Weymouth Rd, Hammonton, NJ 08037</p>
<p><strong>Phone:</strong> (609) 567-0336</p>
<p><strong>Email:</strong> info@petrongolo.com</p>
<p><strong>Facebook:</strong> <a href="https://www.facebook.com/p/Petrongolo-Evergreen-Plantation-100064604442455/" target="_blank">Visit our Facebook Page</a></p>
<p>Note: Our phone is a home line, so for off-hour inquiries, please use email or Facebook.</p>
</div>
</section>
<section id="contact-form" class="py-5">
<div class="container">
<h2 class="section-title">Send Us a Message</h2>
<form action="PEP_ContactUs.php" method="post">
<div class="mb-3">
<label for="name" class="form-label">Name</label>
<input type="text" class="form-control" id="name" name="name" placeholder="Your Name" required>
</div>
<div class="mb-3">
<label for="email" class="form-label">Email</label>
<input type="email" class="form-control" id="email" name="email" placeholder="Your Email" required>
</div>
<div class="mb-3">
<label for="message" class="form-label">Message</label>
<textarea class="form-control" id="message" name="message" rows="5" placeholder="Your Message" required></textarea>
</div>
<button type="submit" class="btn btn-primary">Send Message</button>
</form>
</div>
</section>
<section id="map" class="py-5 bg-mid">
<div class="container">
<h2 class="section-title">Find Us</h2>
<iframe src="https://www.google.com/maps/embed?pb=!1m18!1m12!1m3!1d3074.663747983165!2d-74.739235684646!3d39.641058279464!2m3!1f0!2f0!3f0!3m2!1i1024!2i768!4f13.1!3m3!1m2!1s0x89c13a4a5b5e4b0b%3A0x9f2c4e5d6a7b8c9d!2s7541%20Weymouth%20Rd%2C%20Hammonton%2C%20NJ%2008037!5e0!3m2!1sen!2sus!4v1728321600000!5m2!1sen!2sus" allowfullscreen="" loading="lazy" referrerpolicy="no-referrer-when-downgrade"></iframe>
</div>
</section>
<footer class="text-center">
<p><a href="https://www.facebook.com/p/Petrongolo-Evergreen-Plantation-100064604442455/" target="_blank" style="color: #fff;">Facebook</a> | Email: info@petrongolo.com | Phone: (609) 567-0336</p>
<p>&copy; 2025 Petrongolo Evergreen Plantation. All rights reserved.</p>
<div>
<a href="PEP_Main.php" style="color: #fff;">Home</a> | <a href="PEP_AboutUs.php" style="color: #fff;">About</a> | <a href="PEP_Catalog.php" style="color: #fff;">Catalog</a> | <a href="PEP_Reviews.php" style="color: #fff;">Reviews</a> | <a href="PEP_VisitUs.php" style="color: #fff;">Visit Us</a> | <a href="#" style="color: #fff;">Contact</a>
</div>
</footer>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js" integrity="sha384-YvpcrYf0tY3lHB60NNkmXc5s9fDVZLESaAA55NDzOxhy9GkcIdslK1eN7N6jIeHz" crossorigin="anonymous"></script>
</body>
</html>