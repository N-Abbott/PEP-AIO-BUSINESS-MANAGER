<?php
// PEP_Main.php – updated per requirements
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);
session_start(); // login state
include 'config.php'; // $conn (mysqli)

// ---------------------------------------------------------------------
// 1. Get the 3 top-selling products (by total quantity sold)
// ---------------------------------------------------------------------
$topSql = "
    SELECT p.product_id, p.name, p.price, p.image_path, p.description,
           COALESCE(SUM(si.quantity),0) AS total_sold
    FROM Product p
    LEFT JOIN SaleItems si ON p.product_id = si.product_id
    GROUP BY p.product_id
    ORDER BY total_sold DESC, p.name ASC
    LIMIT 3";
$topResult = $conn->query($topSql);
$hotProducts = [];
while ($row = $topResult->fetch_assoc()) {
    $hotProducts[] = $row;
}
$conn->close();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <title>Petrongolo Evergreen Plantation</title>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css"
          rel="stylesheet"
          integrity="sha384-QWTKZyjpPEjISv5WaRU9OFeRpok6YctnYmDr5pNlyT2bRjXh0JMhjY6hW+ALEwIH"
          crossorigin="anonymous">
    <link rel="stylesheet"
          href="https://fonts.googleapis.com/css2?family=Oswald:wght@400;700&family=Roboto:wght@400;700&display=swap">
    <link rel="icon" type="image/png" href="Tree.png">
    <link rel="stylesheet" href="styles.css">
    <style>
        .btn-primary,.btn-primary:hover,.btn-primary:focus,.btn-primary:active{
            background-color:#2d6a4f !important;border-color:#2d6a4f !important;color:#fff !important;
        }
        .login-btn{position:absolute;top:1rem;right:1rem;z-index:10;}
        footer.bg-dark-green{background-color:#2d6a4f !important;}
        .img-breaker .carousel-item img{height:350px;object-fit:cover;}
    </style>
</head>
<body>
<?php
if (isset($_SESSION['error'])) {
    echo "<div class='alert alert-danger'>" . htmlspecialchars($_SESSION['error']) . "</div>";
    unset($_SESSION['error']);
}
?>
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

<!-- ====================== IMAGE BREAKER ====================== -->
<section class="img-breaker py-4">
    <div id="heroCarousel" class="carousel slide" data-bs-ride="carousel">
        <div class="carousel-inner">
            <div class="carousel-item active">
                <img src="pictures/Carasel1.jpg" class="d-block w-100" alt="Farm landscape">
            </div>
            <div class="carousel-item">
                <img src="pictures/Carasel2.jpg" class="d-block w-100" alt="Family cutting tree">
            </div>
            <div class="carousel-item">
                <img src="pictures/Carasel4.jpg" class="d-block w-100" alt="Hand-made wreaths">
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
</section>

<!-- ====================== WELCOME SECTION ====================== -->
<section id="welcome" class="py-5 bg-mid">
    <div class="container text-center">
        <h1 class="section-title">Welcome to Petrongolo Evergreen Plantation</h1>
        <p class="lead text-dark mb-4">
            A cherished family-owned choose-and-cut Christmas tree farm nestled in the heart of Hammonton, NJ.
            We open the day after Thanksgiving and invite you to start a new holiday tradition.
        </p>
        <p class="text-dark">
            Stroll through rows of fragrant evergreens, pick the perfect tree with your loved ones,
            and enjoy complimentary hot cocoa, wagon rides, and festive photo ops.
            Every tree you cut is fresh, locally grown, and ready to fill your home with holiday cheer.
        </p>
        <a href="PEP_Catalog.php" class="btn btn-primary mt-3">Browse Catalog</a>
    </div>
</section>

<!-- ====================== ABOUT SECTION ====================== -->
<section id="about" class="py-5">
    <div class="container">
        <h2 class="section-title">About Our Farm</h2>
        <p class="text-dark">
            For <strong>three generations</strong> the Petrongolo family has been cultivating premium evergreens,
            hand-crafting wreaths, and growing vibrant poinsettias right here on our 40-acre plantation.
        </p>
        <p class="text-dark">
            Sustainability is at our core—we replant every tree we harvest, use organic practices,
            and maintain the natural beauty of the Pine Barrens. When you choose a Petrongolo tree,
            you’re supporting a local family, protecting the environment, and bringing home a piece of New Jersey tradition.
        </p>
        <p class="text-dark">
            Beyond trees, explore our holiday shop filled with handmade ornaments, fresh garlands,
            and seasonal gifts that make perfect presents.
        </p>

        <!-- CENTERED BUTTON -->
        <div class="text-center mt-4">
            <a href="PEP_AboutUs.php" class="btn btn-primary">Learn More About Us</a>
        </div>
    </div>
</section>

<!-- ====================== HOT SELLERS ====================== -->
<section id="catalog" class="py-5 bg-mid">
    <div class="container">
        <h2 class="section-title text-center mb-4">Hot Sellers</h2>
        <?php if (empty($hotProducts)): ?>
            <p class="text-center text-muted">No sales data yet – check back soon!</p>
        <?php else: ?>
            <div class="row justify-content-center">
                <?php foreach ($hotProducts as $p): ?>
                    <div class="col-md-4 mb-4">
                        <div class="card bg-transparent border-0 h-100">
                            <?php
                            $img = $p['image_path'] && file_exists($p['image_path'])
                                ? $p['image_path']
                                : 'pictures/placeholder.jpg';
                            ?>
                            <img src="<?= htmlspecialchars($img) ?>"
                                 class="card-img-top"
                                 alt="<?= htmlspecialchars($p['name']) ?>">
                            <div class="card-body text-center d-flex flex-column">
                                <h5 class="card-title"><?= htmlspecialchars($p['name']) ?></h5>
                                <p class="card-text flex-grow-1">
                                    <?= htmlspecialchars($p['description'] ?: 'No description available.') ?>
                                </p>
                                <p class="card-text fw-bold text-success">
                                    $<?= number_format((float)$p['price'], 2) ?>
                                </p>
                                <a href="PEP_Catalog.php#product-<?= $p['product_id'] ?>"
                                   class="btn btn-primary mt-auto">View Details</a>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
        <div class="text-center mt-4">
            <a href="PEP_Catalog.php" class="btn btn-primary">See Full Catalog</a>
        </div>
    </div>
</section>

<!-- ====================== CONTACT SECTION ====================== -->
<section id="contact" class="py-5">
    <div class="container">
        <h3 class="section-title">Get in Touch</h3>
        <p class="text-dark">
            Thank you for reaching out to Petrongolo Evergreen Plantation!
            We'll get back to you soon.
        </p>

        <!-- FORM NOW POSTS TO PEP_ContactUs.php -->
        <form action="PEP_ContactUs.php" method="post" class="needs-validation" novalidate>
            <div class="mb-3">
                <label for="name" class="form-label text-dark">Name</label>
                <input type="text" class="form-control" id="name" name="name" required>
                <div class="invalid-feedback">Please enter your name.</div>
            </div>
            <div class="mb-3">
                <label for="email" class="form-label text-dark">Email</label>
                <input type="email" class="form-control" id="email" name="email" required>
                <div class="invalid-feedback">Please enter a valid email.</div>
            </div>
            <div class="mb-3">
                <label for="message" class="form-label text-dark">Message</label>
                <textarea class="form-control" id="message" name="message" rows="3" required></textarea>
                <div class="invalid-feedback">Please write a message.</div>
            </div>
            <div class="text-center">
                <button type="submit" class="btn btn-primary">Send</button>
            </div>
        </form>
    </div>
</section>

<!-- ====================== FOOTER ====================== -->
<footer class="text-center py-4 bg-dark-green text-white">
    <p>
        <a href="https://www.facebook.com/p/Petrongolo-Evergreen-Plantation-100064604442455/"
           target="_blank" class="text-white">Facebook</a>
        | Email: info@petrongolo.com | Phone: (609) 567-0336
    </p>
    <iframe src="https://www.google.com/maps/embed?pb=!1m18!1m12!1m3!1d3074.663747983165!2d-74.739235684646!3d39.641058279464!2m3!1f0!2f0!3f0!3m2!1i1024!2i768!4f13.1!3m3!1m2!1s0x89c13a4a5b5e4b0b%3A0x9f2c4e5d6a7b8c9d!2s7541%20Weymouth%20Rd%2C%20Hammonton%2C%20NJ%2008037!5e0!3m2!1sen!2sus!4v1728321600000!5m2!1sen!2sus"
            allowfullscreen="" loading="lazy"
            referrerpolicy="no-referrer-when-downgrade"
            style="width:100%;max-width:600px;height:300px;border:0;"></iframe>
    <p>&copy; 2025 Petrongolo Evergreen Plantation. All rights reserved.</p>
    <div>
        <a href="#" class="text-white">Home</a> |
        <a href="PEP_AboutUs.php" class="text-white">About</a> |
        <a href="PEP_Catalog.php" class="text-white">Catalog</a> |
        <a href="PEP_Reviews.php" class="text-white">Reviews</a> |
        <a href="PEP_VisitUs.php" class="text-white">Visit Us</a> |
        <a href="PEP_ContactUs.php" class="text-white">Contact</a>
    </div>
</footer>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"
        integrity="sha384-YvpcrYf0tY3lHB60NNkmXc5s9fDVZLESaAA55NDzOxhy9GkcIdslK1eN7N6jIeHz"
        crossorigin="anonymous"></script>

<!-- Bootstrap form validation -->
<script>
(() => {
  'use strict';
  const forms = document.querySelectorAll('.needs-validation');
  Array.from(forms).forEach(form => {
    form.addEventListener('submit', event => {
      if (!form.checkValidity()) {
        event.preventDefault();
        event.stopPropagation();
      }
      form.classList.add('was-validated');
    }, false);
  });
})();
</script>
</body>
</html>