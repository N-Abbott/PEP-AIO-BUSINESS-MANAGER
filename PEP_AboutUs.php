<?php
// PEP_AboutUs.php (updated: removed style tag, added link to styles.css, removed animation classes and JS observer)

session_start(); // Start session for login state

?>

<!DOCTYPE html>
<html lang="en">
<head>
<title>About Us - Petrongolo Evergreen Plantation</title>
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
  <a href="login.php?return=PEP_AboutUs.php" class="btn btn-primary login-btn">Login / Sign Up</a>
<?php endif; ?>
<img src="Banner_Logo.png" class="img-fluid banner" alt="Banner Logo">
<div class="navigation-bar">
<div class="container">
<div class="d-flex justify-content-between align-items-center">
<a class="nav-link" href="PEP_Main.php">Home</a>
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
<section id="about" class="py-5">
<div class="container">
<h1 class="section-title">About Us</h1>
<p>Petrongolo Evergreen Plantation is a family-owned choose-and-cut Christmas tree farm located in Hammonton, New Jersey. For generations, we've been dedicated to providing fresh, high-quality evergreen trees, wreaths, poinsettias, and holiday decorations to families in the community.</p>
<p>Our farm opens the day after Thanksgiving, offering a festive experience with hayrides, hot chocolate, and the joy of selecting your perfect tree. We accept cash, Venmo, and PayPal for your convenience.</p>
</div>
</section>
<section id="history" class="py-5 bg-mid">
<div class="container">
<h2 class="section-title">Our History</h2>
<p>Founded over 50 years ago, Petrongolo Evergreen Plantation has grown from a small family operation into a beloved local tradition. We plant new trees every year to ensure a sustainable supply, focusing on varieties like Fraser Fir, Douglas Fir, and Balsam Fir.</p>
</div>
</section>
<section id="team" class="py-5">
<div class="container">
<h2 class="section-title">Meet the Family</h2>
<p>Our team is passionate about creating memorable holiday experiences. From planting to pruning, we handle every step with care to bring you the best trees possible.</p>
</div>
</section>
<section id="sustainability" class="py-5 bg-mid">
<div class="container">
<h2 class="section-title">Sustainability Practices</h2>
<p>We believe in responsible farming. Our trees are grown without harmful chemicals, and we replant annually to maintain our beautiful landscape for future generations.</p>
</div>
</section>
<footer class="text-center">
<p><a href="https://www.facebook.com/p/Petrongolo-Evergreen-Plantation-100064604442455/" target="_blank" style="color: #fff;">Facebook</a> | Email: info@petrongolo.com | Phone: (609) 567-0336</p>
<p>&copy; 2025 Petrongolo Evergreen Plantation. All rights reserved.</p>
<div>
<a href="PEP_Main.php" style="color: #fff;">Home</a> | <a href="#" style="color: #fff;">About</a> | <a href="PEP_Catalog.php" style="color: #fff;">Catalog</a> | <a href="PEP_Reviews.php" style="color: #fff;">Reviews</a> | <a href="PEP_VisitUs.php" style="color: #fff;">Visit Us</a> | <a href="PEP_ContactUs.php" style="color: #fff;">Contact</a>
</div>
</footer>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js" integrity="sha384-YvpcrYf0tY3lHB60NNkmXc5s9fDVZLESaAA55NDzOxhy9GkcIdslK1eN7N6jIeHz" crossorigin="anonymous"></script>
</body>
</html>