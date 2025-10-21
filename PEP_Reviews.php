<?php
// PEP_Reviews.php (updated: removed style tag, added link to styles.css, removed animation classes and JS observer)

session_start(); // Start session for login state

?>

<!DOCTYPE html>
<html lang="en">
<head>
<title>Reviews - Petrongolo Evergreen Plantation</title>
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
  <a href="login.php?return=PEP_Reviews.php" class="btn btn-primary login-btn">Login / Sign Up</a>
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
      <a class="nav-link" href="PEP_VisitUs.php">Visit Us</a>
      <img src="Tree.png" class="tree-divider" alt="Tree Divider">
      <a class="nav-link" href="PEP_ContactUs.php">Contact</a>
    </div>
  </div>
</div>
<section id="reviews" class="py-5">
  <div class="container">
    <h1 class="section-title">Customer Reviews</h1>
    <p>Read what our customers have to say about their experiences at Petrongolo Evergreen Plantation. Your feedback helps us grow!</p>
  </div>
</section>
<section id="submit-review" class="py-5 bg-mid">
  <div class="container text-center">
    <?php if (isset($_SESSION['user_id']) && $_SESSION['role'] === 'customer'): ?>
      <a href="PEP_ReviewWrite.php" class="btn leave-review-btn">Leave A Review</a>
    <?php else: ?>
      <a href="login.php?return=PEP_ReviewWrite.php" class="btn leave-review-btn">Leave A Review</a>
    <?php endif; ?>
    <p class="mt-3 text-muted">Note: Reviews are moderated before posting. You must be logged in to submit.</p>
  </div>
</section>
<section id="customer-reviews" class="py-5">
  <div class="container">
    <h2 class="section-title">What Our Customers Say</h2>
    <div class="row">
      <div class="col-md-4 mb-4">
        <div class="card review-card">
          <div class="card-body text-center">
            <h5 class="card-title">John Doe</h5>
            <p class="stars">★★★★★</p>
            <p class="card-text">"Best Christmas tree farm around! The staff is friendly, and the trees are fresh and beautiful."</p>
          </div>
        </div>
      </div>
      <div class="col-md-4 mb-4">
        <div class="card review-card">
          <div class="card-body text-center">
            <h5 class="card-title">Jane Smith</h5>
            <p class="stars">★★★★☆</p>
            <p class="card-text">"Great selection of wreaths and poinsettias. We'll be back next year!"</p>
          </div>
        </div>
      </div>
      <div class="col-md-4 mb-4">
        <div class="card review-card">
          <div class="card-body text-center">
            <h5 class="card-title">Mike Johnson</h5>
            <p class="stars">★★★★★</p>
            <p class="card-text">"Family tradition for years. Love the hayrides and hot chocolate!"</p>
          </div>
        </div>
      </div>
    </div>
  </div>
</section>
<footer class="text-center">
  <p><a href="https://www.facebook.com/p/Petrongolo-Evergreen-Plantation-100064604442455/" target="_blank" style="color: #fff;">Facebook</a> | Email: info@petrongolo.com | Phone: (609) 567-0336</p>
  <p>&copy; 2025 Petrongolo Evergreen Plantation. All rights reserved.</p>
  <div>
    <a href="PEP_Main.php" style="color: #fff;">Home</a> | <a href="PEP_AboutUs.php" style="color: #fff;">About</a> | <a href="PEP_Catalog.php" style="color: #fff;">Catalog</a> | <a href="#" style="color: #fff;">Reviews</a> | <a href="PEP_VisitUs.php" style="color: #fff;">Visit Us</a> | <a href="PEP_ContactUs.php" style="color: #fff;">Contact</a>
  </div>
</footer>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js" integrity="sha384-YvpcrYf0tY3lHB60NNkmXc5s9fDVZLESaAA55NDzOxhy9GkcIdslK1eN7N6jIeHz" crossorigin="anonymous"></script>
</body>
</html>