<?php session_start(); ?>
<!DOCTYPE html>
<html lang="en">
<head>
<title>About Us - Petrongolo Evergreen Plantation</title>
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
</style>
</head>
<body>
  <?php if (isset($_SESSION['user_id']) && $_SESSION['role'] === 'customer'): ?>
  <a href="PEP_CustomerAccount.php" class="btn btn-primary login-btn">My Account</a>
<?php else: ?>
  <button type="button" class="btn btn-primary login-btn" data-bs-toggle="modal" data-bs-target="#loginModal">Login / Sign Up</button>
<?php endif; ?>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js" integrity="sha384-YvpcrYf0tY3lHB60NNkmXc5s9fDVZLESaAA55NDzOxhy9GkcIdslK1eN7N6jIeHz" crossorigin="anonymous"></script>
<img src="Banner_Logo.png" class="img-fluid banner" alt="Banner Logo">
<div class="navigation-bar">
<div class="container">
<div class="d-flex justify-content-between align-items-center">
<a class="nav-link" href="PEP_Main.php">Home</a>
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
<section id="about" class="py-5 animate-bottom">
<div class="container">
<h1 class="section-title">About Us</h1>
<p>Petrongolo Evergreen Plantation is a family-owned choose-and-cut Christmas tree farm located in Hammonton, New Jersey. For generations, we've been dedicated to providing fresh, high-quality evergreen trees, wreaths, poinsettias, and holiday decorations to families in the community.</p>
<p>Our farm opens the day after Thanksgiving, offering a festive experience with hayrides, hot chocolate, and the joy of selecting your perfect tree. We accept cash, Venmo, and PayPal for your convenience.</p>
</div>
</section>
<section id="history" class="py-5 bg-mid animate-left">
<div class="container">
<h2 class="section-title">Our History</h2>
<p>Founded over 50 years ago, Petrongolo Evergreen Plantation has grown from a small family operation into a beloved local tradition. We plant new trees every year to ensure a sustainable supply, focusing on varieties like Fraser Firs that are perfect for holiday decorating.</p>
<p>As a third-generation family business, we pride ourselves on our hands-on approach, from planting to customer service, ensuring every visit is memorable.</p>
</div>
</section>
<section id="mission" class="py-5 animate-zoom">
<div class="container">
<h2 class="section-title">Our Mission</h2>
<p>At Petrongolo, our goal is to make the holiday season easier and more enjoyable. By transitioning from manual pen-and-paper processes to a modern system, we aim to save time, reduce errors, and improve the experience for both our team and customers.</p>
<p>We envision a website that provides essential information like location, hours, products, and prices, so customers can plan their visits without needing to call during off-hours.</p>
</div>
</section>
<section id="gallery" class="py-5 bg-mid animate-right">
<div class="container">
<h2 class="section-title">Farm Gallery</h2>
<div class="row">
<div class="col-md-4 mb-4">
<img src="https://lookaside.fbsbx.com/lookaside/crawler/media/?media_id=543368757826601" class="img-fluid" alt="Farm Photo 1">
</div>
<div class="col-md-4 mb-4">
<img src="https://lookaside.fbsbx.com/lookaside/crawler/media/?media_id=531931625636981" class="img-fluid" alt="Farm Photo 2">
</div>
<div class="col-md-4 mb-4">
<img src="https://lookaside.fbsbx.com/lookaside/crawler/media/?media_id=523011796528964" class="img-fluid" alt="Farm Photo 3">
</div>
</div>
</div>
</section>
<footer class="text-center">
<p><a href="https://www.facebook.com/p/Petrongolo-Evergreen-Plantation-100064604442455/" target="_blank" style="color: #fff;">Facebook</a> | Email: info@petrongolo.com | Phone: (609) 567-0336</p>
<iframe src="https://www.google.com/maps/embed?pb=!1m18!1m12!1m3!1d3074.663747983165!2d-74.739235684646!3d39.641058279464!2m3!1f0!2f0!3f0!3m2!1i1024!2i768!4f13.1!3m3!1m2!1s0x89c13a4a5b5e4b0b%3A0x9f2c4e5d6a7b8c9d!2s7541%20Weymouth%20Rd%2C%20Hammonton%2C%20NJ%2008037!5e0!3m2!1sen!2sus!4v1728321600000!5m2!1sen!2sus" allowfullscreen="" loading="lazy" referrerpolicy="no-referrer-when-downgrade"></iframe>
<p>&copy; 2025 Petrongolo Evergreen Plantation. All rights reserved.</p>
<div>
<a href="index.html" style="color: #fff;">Home</a> | <a href="#" style="color: #fff;">About</a> | <a href="PEP_Catalog.html" style="color: #fff;">Catalog</a> | <a href="PEP_Reviews.html" style="color: #fff;">Reviews</a> | <a href="PEP_VisitUs.html" style="color: #fff;">Visit Us</a> | <a href="PEP_ContactUs.html" style="color: #fff;">Contact</a>
</div>
</footer>
<script>
// Scroll animation observer
const sections = document.querySelectorAll('.animate-bottom, .animate-left, .animate-zoom, .animate-right');
const options = {
threshold: 0.1
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
// Modal form toggle functions
function showCustomerLogin() {
  document.getElementById('customerForm').style.display = 'block';
  document.getElementById('employeeForm').style.display = 'none';
  document.getElementById('customerSignInForm').style.display = 'block';
  document.getElementById('customerSignUpForm').style.display = 'none';
}

function showEmployeeLogin() {
  document.getElementById('customerForm').style.display = 'none';
  document.getElementById('employeeForm').style.display = 'block';
}

function showSignUp() {
  document.getElementById('customerSignInForm').style.display = 'none';
  document.getElementById('customerSignUpForm').style.display = 'block';
}

function showSignIn() {
  document.getElementById('customerSignInForm').style.display = 'block';
  document.getElementById('customerSignUpForm').style.display = 'none';
}
</script>
</body>
</html>