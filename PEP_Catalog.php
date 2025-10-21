<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);
session_start();
include 'config.php'; // Assuming this sets up $conn as mysqli
// No POST handling here anymore, as it's moved to login.php
?>
<!DOCTYPE html>
<html lang="en">
<head>
<title>Catalog - Petrongolo Evergreen Plantation</title>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-QWTKZyjpPEjISv5WaRU9OFeRpok6YctnYmDr5pNlyT2bRjXh0JMhjY6hW+ALEwIH" crossorigin="anonymous">
<link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Oswald:wght@400;700&family=Roboto:wght@400;700&display=swap">
<link rel="stylesheet" href="styles.css">
<style>
    .list-btn {
  position: absolute;
  top: 10px;
  right: 150px; /* Positioned to the left of login button */
  z-index: 10;
  background-color: #2c5530;
  color: #fff;
  border: none;
}

</style>
</head>
<body>
<div class="banner-wrapper">
<img src="Banner_Logo.png" class="img-fluid banner" alt="Banner Logo">
<?php if (isset($_SESSION['user_id']) && $_SESSION['role'] === 'customer'): ?>
  <a href="PEP_CustomerAccount.php" class="btn btn-primary login-btn">My Account</a>
  <a href="PEP_CustomerList.php" class="btn btn-primary list-btn" style="background-color: #2c5530; color: #fff; border: none;">My List</a>
<?php else: ?>
  <a href="login.php?return=PEP_Catalog.php" class="btn btn-primary login-btn">Login / Sign Up</a>
  <a href="login.php?return=PEP_CustomerList.php" class="btn btn-primary list-btn" style="background-color: #2c5530; color: #fff; border: none;">My List</a>
<?php endif; ?>
</div>
<div class="navigation-bar">
<div class="container">
<div class="d-flex justify-content-between align-items-center">
<a class="nav-link" href="PEP_Main.php">Home</a>
<img src="Tree.png" class="tree-divider" alt="Tree Divider">
<a class="nav-link" href="PEP_AboutUs.php">About</a>
<img src="Tree.png" class="tree-divider" alt="Tree Divider">
<a class="nav-link" href="PEP_Reviews.php">Reviews</a>
<img src="Tree.png" class="tree-divider" alt="Tree Divider">
<a class="nav-link" href="PEP_VisitUs.php">Visit Us</a>
<img src="Tree.png" class="tree-divider" alt="Tree Divider">
<a class="nav-link" href="PEP_ContactUs.php">Contact</a>
</div>
</div>
</div>
<section id="catalog" class="py-5">
<div class="container">
<h1 class="section-title">Our Catalog</h1>
<p>Browse our selection of fresh evergreen trees, wreaths, poinsettias, and holiday decorations. Prices are approximate and depend on size/availability. Visit us for the full experience!</p>
<div class="row">
<?php
$sql = "SELECT id, name, description, price, image FROM products";
$result = $conn->query($sql);
if ($result->num_rows > 0) {
  while ($product = $result->fetch_assoc()) {
    echo '<div class="col-md-3 mb-4">
            <div class="card bg-transparent border-0 product-card">
              <img src="' . htmlspecialchars($product['image']) . '" class="card-img-top" alt="' . htmlspecialchars($product['name']) . '">
              <div class="card-body text-center" style="color: #000;">
                <h5 class="card-title" style="color: #000;">' . htmlspecialchars($product['name']) . '</h5>
                <p class="card-text" style="color: #000;">' . htmlspecialchars($product['description']) . '</p>
                <p class="card-text" style="color: #000;">Price: $' . number_format($product['price'], 2) . '</p>';
    if (isset($_SESSION['user_id']) && $_SESSION['role'] === 'customer') {
      echo '<button type="button" class="btn btn-success add-to-list-btn" data-product-id="' . $product['id'] . '">Add to List</button>';
    } else {
      echo '<a href="login.php?return=PEP_Catalog.php" class="btn btn-success">Add to List (Login Required)</a>';
    }
    echo '</div>
            </div>
          </div>';
  }
} else {
  echo '<p>No products available.</p>';
}
$conn->close();
?>
</div>
</div>
</section>
<footer class="text-center">
<p><a href="https://www.facebook.com/p/Petrongolo-Evergreen-Plantation-100064604442455/" target="_blank" style="color: #fff;">Facebook</a> | Email: info@petrongolo.com | Phone: (609) 567-0336</p>
<p>&copy; 2025 Petrongolo Evergreen Plantation. All rights reserved.</p>
<div>
<a href="PEP_Main.php" style="color: #fff;">Home</a> | <a href="PEP_AboutUs.php" style="color: #fff;">About</a> | <a href="#" style="color: #fff;">Catalog</a> | <a href="PEP_Reviews.php" style="color: #fff;">Reviews</a> | <a href="PEP_VisitUs.php" style="color: #fff;">Visit Us</a> | <a href="PEP_ContactUs.php" style="color: #fff;">Contact</a>
</div>
</footer>
<script>
// AJAX for add to list (only for logged-in users)
<?php if (isset($_SESSION['user_id']) && $_SESSION['role'] === 'customer'): ?>
document.querySelectorAll('.add-to-list-btn').forEach(button => {
  button.addEventListener('click', function() {
    const productId = this.getAttribute('data-product-id');
    console.log('Adding product ID: ' + productId); // Debug log
    const formData = new FormData();
    formData.append('action', 'add');
    formData.append('product_id', productId);
    formData.append('quantity', 1);
    fetch('PEP_CustomerList.php', {
      method: 'POST',
      body: formData
    })
    .then(response => response.text())
    .then(data => {
      console.log('Response: ' + data); // Debug log
      alert(data.trim() || 'Item added to list!');
    })
    .catch(error => {
      console.error('Error: ' + error); // Debug log
      alert('Error adding item.');
    });
  });
});
<?php endif; ?>
</script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js" integrity="sha384-YvpcrYf0tY3lHB60NNkmXc5s9fDVZLESaAA55NDzOxhy9GkcIdslK1eN7N6jIeHz" crossorigin="anonymous"></script>
</body>
</html>