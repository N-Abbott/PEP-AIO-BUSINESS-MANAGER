<?php
session_start(); // Start session for login state
include 'config.php'; // Include DB connection

$success = $error = '';

// Handle Customer Sign Up
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['action']) && $_POST['action'] == 'signup') {
  $fname = $_POST['fname'];
  $lname = $_POST['lname'];
  $phone = $_POST['phone'];
  $email = $_POST['email'];
  $username = $_POST['username'];
  $password = $_POST['password'];
  $role = 'customer';

  // Hash password
  $password_hash = password_hash($password, PASSWORD_DEFAULT);

  // Insert into DB
  $sql = "INSERT INTO users (fname, lname, phone, email, username, password_hash, role) VALUES (?, ?, ?, ?, ?, ?, ?)";
  $stmt = $conn->prepare($sql);
  $stmt->bind_param("sssssss", $fname, $lname, $phone, $email, $username, $password_hash, $role);

  if ($stmt->execute()) {
    $success = "Account created! Please sign in.";
  } else {
    $error = "Error: Username or email already exists.";
  }
  $stmt->close();
}

// Handle Customer Login
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['action']) && $_POST['action'] == 'customer_login') {
  $username = $_POST['custUsername'];
  $password = $_POST['custPassword'];

  $sql = "SELECT id, password_hash FROM users WHERE username = ? AND role = 'customer'";
  $stmt = $conn->prepare($sql);
  $stmt->bind_param("s", $username);
  $stmt->execute();
  $stmt->bind_result($id, $password_hash);
  if ($stmt->fetch() && password_verify($password, $password_hash)) {
    $_SESSION['user_id'] = $id;
    $_SESSION['role'] = 'customer';
    $success = "Login successful!";
    // Redirect or stay (e.g., header("Location: PEP_Main.php"); exit; )
  } else {
    $error = "Invalid credentials.";
  }
  $stmt->close();
}

// Handle Employee Login (similar, redirect to portal)
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['action']) && $_POST['action'] == 'employee_login') {
  $email = $_POST['empEmail'];
  $password = $_POST['empPassword'];

  $sql = "SELECT id, password_hash, role FROM users WHERE email = ? AND role IN ('employee', 'admin')";
  $stmt = $conn->prepare($sql);
  $stmt->bind_param("s", $email);
  $stmt->execute();
  $stmt->bind_result($id, $password_hash, $role);
  if ($stmt->fetch() && password_verify($password, $password_hash)) {
    $_SESSION['user_id'] = $id;
    $_SESSION['role'] = $role;
    $success = "Login successful! Redirecting to portal...";
    header("Location: PEP_EmployeeSchedule.html"); // Change to .php later
    exit;
  } else {
    $error = "Invalid credentials.";
  }
  $stmt->close();
}

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
<style>
html, body {
  width: 100%;
  overflow-x: hidden;
  scroll-behavior: smooth;
}
body {
font-family: 'Roboto', sans-serif;
background-color: #f8f9fa;
color: #333;
        }
.banner {
width: 100%;
height: auto;
        }
.navigation-bar {
background-color: #2c5530;
padding: 10px 0;
        }
.navigation-bar .nav-link {
color: #fff !important;
font-weight: 700;
margin: 0 20px;
text-decoration: underline;
        }
.navigation-bar .tree-divider {
height: 20px;
width: 20px;
border-radius: 50%;
margin: 0 10px;
vertical-align: middle;
        }
.hero-carousel .carousel-item img {
height: 80vh;
object-fit: cover;
        }
.section-title {
text-align: center;
margin-bottom: 40px;
color: #2c5530;
font-family: 'Oswald', sans-serif;
font-weight: 700;
        }
h1, h2, h3, h4, .card-title {
font-family: 'Oswald', sans-serif;
font-weight: 700;
color: #2c5530;
        }
.card {
background-color: #fff;
color: #333;
border: 1px solid #e9ecef;
        }
.bg-mid {
background-color: #e9ecef;
        }
footer {
background-color: #2c5530;
color: #fff;
padding: 20px 0;
        }
.animate-bottom {
opacity: 0;
transform: translateY(100px);
transition: opacity 1.5s ease-out, transform 1.5s ease-out;
        }
.animate-bottom.visible {
opacity: 1;
transform: translateY(0);
        }
.animate-left {
opacity: 0;
transform: translateX(-100px);
transition: opacity 1.5s ease-out, transform 1.5s ease-out;
        }
.animate-left.visible {
opacity: 1;
transform: translateX(0);
        }
.animate-zoom {
opacity: 0;
transform: scale(0.8);
transition: opacity 1.5s ease-out, transform 1.5s ease-out;
        }
.animate-zoom.visible {
opacity: 1;
transform: scale(1);
        }
.animate-right {
opacity: 0;
transform: translateX(100px);
transition: opacity 1.5s ease-out, transform 1.5s ease-out;
        }
.animate-right.visible {
opacity: 1;
transform: translateX(0);
        }
.cta-section {
text-align: center;
padding: 40px 0;
background-color: #2c5530;
color: #fff;
        }
.cta-section a {
background-color: #fff;
color: #2c5530;
        }
.cta-section a:hover {
background-color: #e9ecef;
        }
.gallery-grid a {
display: block;
margin-bottom: 20px;
background-color: #fff;
color: #333;
border: 1px solid #e9ecef;
        }
.gallery-grid a:hover {
background-color: #2c5530;
color: #fff;
        }
form .form-control {
background-color: #fff;
color: #333;
border: 1px solid #e9ecef;
        }
.product-card img {
height: 300px;
object-fit: cover;
        }
.product-card .card-body {
color: #fff;
        }
.product-card .card-title {
color: #fff;
        }
.login-btn {
  position: absolute;
  top: 10px;
  right: 10px;
  z-index: 10;
  background-color: #2c5530;
  color: #fff;
  border: none;
}
</style>
</head>
<body>
<?php if ($success) echo "<div class='alert alert-success'>$success</div>"; ?>
<?php if ($error) echo "<div class='alert alert-danger'>$error</div>"; ?>
<img src="Banner_Logo.png" class="img-fluid banner" alt="Banner Logo">
<div class="navigation-bar">
<div class="container">
<div class="d-flex justify-content-between align-items-center">
<a class="nav-link" href="PEP_AboutUs.html">About</a>
<img src="Tree.png" class="tree-divider" alt="Tree Divider">
<a class="nav-link" href="PEP_Catalog.html">Catalog</a>
<img src="Tree.png" class="tree-divider" alt="Tree Divider">
<a class="nav-link" href="PEP_Reviews.html">Reviews</a>
<img src="Tree.png" class="tree-divider" alt="Tree Divider">
<a class="nav-link" href="PEP_VisitUs.html">Visit Us</a>
<img src="Tree.png" class="tree-divider" alt="Tree Divider">
<a class="nav-link" href="PEP_ContactUs.html">Contact</a>
</div>
</div>
</div>
<div id="heroCarousel" class="carousel slide hero-carousel animate-bottom" data-bs-ride="carousel">
<div class="carousel-inner">
<div class="carousel-item active">
<img src="eevee1.HEIC" class="d-block w-100" alt="Tree 1">
</div>
<div class="carousel-item">
<img src="eevee2.HEIC" class="d-block w-100" alt="Tree 2">
</div>
<div class="carousel-item">
<img src="eevee3.HEIC" class="d-block w-100" alt="Tree 3">
</div>
<div class="carousel-item">
<img src="eevee2.HEIC" class="d-block w-100" alt="Tree 4">
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
<section id="hours" class="py-5 bg-mid animate-left">
<div class="container text-center">
<h3 class="section-title">Operating Hours</h3>
<p class="text-dark">Weekends: 9:00 AM - 5:00 PM</p>
<p class="text-dark">Weekdays: Closed (Check for updates)</p>
<p class="text-dark">Open seasonally starting the day after Thanksgiving.</p>
</div>
</section>
<section id="gallery" class="py-5 animate-zoom">
<div class="container">
<h3 class="section-title">Explore Our Farm</h3>
<div class="row gallery-grid">
<div class="col-md-3">
<a href="PEP_AboutUs.html" class="btn btn-light btn-block">About Us</a>
</div>
<div class="col-md-3">
<a href="PEP_Catalog.html" class="btn btn-light btn-block">Catalog</a>
</div>
<div class="col-md-3">
<a href="PEP_Reviews.html" class="btn btn-light btn-block">Reviews</a>
</div>
<div class="col-md-3">
<a href="PEP_VisitUs.html" class="btn btn-light btn-block">Visit Us</a>
</div>
<div class="col-md-3">
<a href="PEP_ContactUs.html" class="btn btn-light btn-block">Contact</a>
</div>
</div>
</div>
</section>
<section class="cta-section animate-right">
<div class="container">
<h2 style="color: #e9ecef;">Hot Sellers</h2><br>
<div class="row">
<div class="col-md-4 mb-4">
<div class="card bg-transparent border-0 product-card">
<img src="https://pacificgarland.com/cdn/shop/products/mixed-evergreen-holiday-everring-wreath_2000x.jpg?v=1698960912" class="card-img-top" alt="Holiday Wreath">
<div class="card-body text-center">
<h5 class="card-title">Holiday Wreath</h5>
<p class="card-text">Handmade fresh wreaths, decorated or plain. Great for doors and mantels.</p>
<p class="card-text">Price: $25 - $45</p>
</div>
</div>
</div>
<div class="col-md-4 mb-4">
<div class="card bg-transparent border-0 product-card">
<img src="https://almostheavenlytrees.com/cdn/shop/products/4312-fraser-fir-christmas-tree_480x480.jpg?v=1458931596" class="card-img-top" alt="Fraser Fir Tree">
<div class="card-body text-center">
<h5 class="card-title">Fraser Fir Tree</h5>
<p class="card-text">A popular Christmas tree with strong branches and excellent needle retention. Perfect for ornaments.</p>
<p class="card-text">Price: $50 - $150 depending on size</p>
</div>
</div>
</div>
<div class="col-md-4 mb-4">
<div class="card bg-transparent border-0 product-card">
<img src="https://extension.umn.edu/sites/extension.umn.edu/files/001%20The%20traditional%20red%20poinsettia.jpg" class="card-img-top" alt="Poinsettia">
<div class="card-body text-center">
<h5 class="card-title">Poinsettia</h5>
<p class="card-text">Vibrant red poinsettias to brighten your holiday decor.</p>
<p class="card-text">Price: $10 - $30</p>
</div>
</div>
</div>
</div>
</div>
</section>
<section id="contact" class="py-5 animate-bottom">
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
<a href="#" style="color: #fff;">Home</a> | <a href="PEP_AboutUs.html" style="color: #fff;">About</a> | <a href="PEP_Catalog.html" style="color: #fff;">Catalog</a> | <a href="PEP_Reviews.html" style="color: #fff;">Reviews</a> | <a href="PEP_VisitUs.html" style="color: #fff;">Visit Us</a> | <a href="PEP_ContactUs.html" style="color: #fff;">Contact</a>
</div>
</footer>
<!-- Login Modal -->
<div class="modal fade" id="loginModal" tabindex="-1" aria-labelledby="loginModalLabel" aria-hidden="true">
  <div class="modal-dialog">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title" id="loginModalLabel">Login or Sign Up</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <div class="modal-body">
        <div class="text-center">
          <button type="button" class="btn btn-primary mb-3" onclick="showCustomerLogin()">Customer Login / Sign Up</button>
          <button type="button" class="btn btn-secondary mb-3" onclick="showEmployeeLogin()">Employee Login</button>
        </div>
        <div id="customerForm" style="display: none;">
          <form action="PEP_Main.php" method="post" id="customerSignInForm">
            <input type="hidden" name="action" value="customer_login">
            <h6>Customer Sign In</h6>
            <div class="mb-3">
              <label for="custUsername" class="form-label">Username</label>
              <input type="text" class="form-control" id="custUsername" name="custUsername" placeholder="Username">
            </div>
            <div class="mb-3">
              <label for="custPassword" class="form-label">Password</label>
              <input type="password" class="form-control" id="custPassword" name="custPassword" placeholder="Password">
            </div>
            <button type="submit" class="btn btn-primary">Sign In</button>
            <p class="mt-2">Don't have an account? <a href="#" onclick="showSignUp()">Sign Up</a></p>
          </form>
          <form action="PEP_Main.php" method="post" id="customerSignUpForm" style="display: none;">
            <input type="hidden" name="action" value="signup">
            <h6>Customer Sign Up</h6>
            <div class="mb-3">
              <label for="fname" class="form-label">First Name</label>
              <input type="text" class="form-control" id="fname" name="fname" placeholder="First Name">
            </div>
            <div class="mb-3">
              <label for="lname" class="form-label">Last Name</label>
              <input type="text" class="form-control" id="lname" name="lname" placeholder="Last Name">
            </div>
            <div class="mb-3">
              <label for="phone" class="form-label">Phone Number</label>
              <input type="tel" class="form-control" id="phone" name="phone" placeholder="Phone Number">
            </div>
            <div class="mb-3">
              <label for="email" class="form-label">Email</label>
              <input type="email" class="form-control" id="email" name="email" placeholder="Email">
            </div>
            <div class="mb-3">
              <label for="username" class="form-label">Username</label>
              <input type="text" class="form-control" id="username" name="username" placeholder="Username">
            </div>
            <div class="mb-3">
              <label for="password" class="form-label">Password</label>
              <input type="password" class="form-control" id="password" name="password" placeholder="Password">
            </div>
            <button type="submit" class="btn btn-primary">Sign Up</button>
            <p class="mt-2">Already have an account? <a href="#" onclick="showSignIn()">Sign In</a></p>
          </form>
        </div>
        <div id="employeeForm" style="display: none;">
          <form action="PEP_Main.php" method="post">
            <input type="hidden" name="action" value="employee_login">
            <h6>Employee Sign In</h6>
            <div class="mb-3">
              <label for="empEmail" class="form-label">Email</label>
              <input type="email" class="form-control" id="empEmail" name="empEmail" placeholder="Email">
            </div>
            <div class="mb-3">
              <label for="empPassword" class="form-label">Password</label>
              <input type="password" class="form-control" id="empPassword" name="empPassword" placeholder="Password">
            </div>
            <button type="submit" class="btn btn-primary">Sign In</button>
          </form>
        </div>
      </div>
    </div>
  </div>
</div>
<script>
// Scroll animation observer
const sections = document.querySelectorAll('.animate-bottom, .animate-left, .animate-zoom, .animate-right');
const options = {
threshold: 0.025
    };
const observer = new IntersectionObserver((entries, observer) => {
entries.forEach(entry => {
if (entry.isIntersecting) {
entry.target.classList.add('visible');
            } else {
entry.target.classList.remove('visible');
            }
        });
    }, options);
sections.forEach(section => {
observer.observe(section);
    });
</script>
</body>
</html>