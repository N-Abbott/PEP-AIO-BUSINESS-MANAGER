<?php session_start(); ?>
<!DOCTYPE html>
<html lang="en">
<head>
<title>Catalog - Petrongolo Evergreen Plantation</title>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-QWTKZyjpPEjISv5WaRU9OFeRpok6YctnYmDr5pNlyT2bRjXh0JMhjY6hW+ALEwIH" crossorigin="anonymous">
<link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Oswald:wght@400;700&family=Roboto:wght@400;700&display=swap">
<style>
html {
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
  color: #333;
}
.product-card .card-title {
  color: #2c5530;
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
      <a class="nav-link" href="PEP_AboutUs.html">About</a>
      <img src="Tree.png" class="tree-divider" alt="Tree Divider">
      <a class="nav-link" href="PEP_Reviews.html">Reviews</a>
      <img src="Tree.png" class="tree-divider" alt="Tree Divider">
      <a class="nav-link" href="PEP_VisitUs.html">Visit Us</a>
      <img src="Tree.png" class="tree-divider" alt="Tree Divider">
      <a class="nav-link" href="PEP_ContactUs.html">Contact</a>
    </div>
  </div>
</div>
<section id="catalog" class="py-5 animate-bottom">
  <div class="container">
    <h1 class="section-title">Our Catalog</h1>
    <p>Browse our selection of fresh evergreen trees, wreaths, poinsettias, and holiday decorations. Prices are approximate and depend on size/availability. Visit us for the full experience!</p>
    <div class="row">
      <div class="col-md-3 mb-4">
        <div class="card bg-transparent border-0 product-card">
          <img src="******************************" class="card-img-top" alt="Fraser Fir Tree (Small)">
          <div class="card-body text-center">
            <h5 class="card-title">Fraser Fir Tree (Small, 5-6 ft)</h5>
            <p class="card-text">Popular Christmas tree with strong branches and excellent needle retention. Perfect for smaller spaces.</p>
            <p class="card-text">Price: $50 - $70</p>
          </div>
        </div>
      </div>
      <div class="col-md-3 mb-4">
        <div class="card bg-transparent border-0 product-card">
          <img src="******************************" class="card-img-top" alt="Fraser Fir Tree (Medium)">
          <div class="card-body text-center">
            <h5 class="card-title">Fraser Fir Tree (Medium, 7-8 ft)</h5>
            <p class="card-text">Ideal size for most homes, with dense foliage and a classic shape.</p>
            <p class="card-text">Price: $80 - $100</p>
          </div>
        </div>
      </div>
      <div class="col-md-3 mb-4">
        <div class="card bg-transparent border-0 product-card">
          <img src="******************************" class="card-img-top" alt="Fraser Fir Tree (Large)">
          <div class="card-body text-center">
            <h5 class="card-title">Fraser Fir Tree (Large, 9+ ft)</h5>
            <p class="card-text">Grand statement piece with sturdy branches for heavy ornaments.</p>
            <p class="card-text">Price: $110+</p>
          </div>
        </div>
      </div>
      <div class="col-md-3 mb-4">
        <div class="card bg-transparent border-0 product-card">
          <img src="******************************" class="card-img-top" alt="Douglas Fir Tree">
          <div class="card-body text-center">
            <h5 class="card-title">Douglas Fir Tree</h5>
            <p class="card-text">Soft needles and a pleasant citrus scent, great for families.</p>
            <p class="card-text">Price: $40 - $120 depending on size</p>
          </div>
        </div>
      </div>
      <div class="col-md-3 mb-4">
        <div class="card bg-transparent border-0 product-card">
          <img src="******************************" class="card-img-top" alt="Blue Spruce Tree">
          <div class="card-body text-center">
            <h5 class="card-title">Blue Spruce Tree</h5>
            <p class="card-text">Striking blue-green needles and symmetrical shape.</p>
            <p class="card-text">Price: $60 - $150 depending on size</p>
          </div>
        </div>
      </div>
      <div class="col-md-3 mb-4">
        <div class="card bg-transparent border-0 product-card">
          <img src="******************************" class="card-img-top" alt="Norway Spruce Tree">
          <div class="card-body text-center">
            <h5 class="card-title">Norway Spruce Tree</h5>
            <p class="card-text">Traditional tree with good needle retention when fresh-cut.</p>
            <p class="card-text">Price: $50 - $130 depending on size</p>
          </div>
        </div>
      </div>
      <div class="col-md-3 mb-4">
        <div class="card bg-transparent border-0 product-card">
          <img src="******************************" class="card-img-top" alt="White Pine Tree">
          <div class="card-body text-center">
            <h5 class="card-title">White Pine Tree</h5>
            <p class="card-text">Long, soft needles and minimal fragrance, pet-friendly option.</p>
            <p class="card-text">Price: $40 - $100 depending on size</p>
          </div>
        </div>
      </div>
      <div class="col-md-3 mb-4">
        <div class="card bg-transparent border-0 product-card">
          <img src="******************************" class="card-img-top" alt="Plain Holiday Wreath">
          <div class="card-body text-center">
            <h5 class="card-title">Plain Holiday Wreath</h5>
            <p class="card-text">Fresh evergreen wreath, simple and elegant for doors or walls.</p>
            <p class="card-text">Price: $25</p>
          </div>
        </div>
      </div>
      <div class="col-md-3 mb-4">
        <div class="card bg-transparent border-0 product-card">
          <img src="******************************" class="card-img-top" alt="Decorated Holiday Wreath">
          <div class="card-body text-center">
            <h5 class="card-title">Decorated Holiday Wreath</h5>
            <p class="card-text">Handmade with bows, pinecones, and berries for festive charm.</p>
            <p class="card-text">Price: $35 - $45</p>
          </div>
        </div>
      </div>
      <div class="col-md-3 mb-4">
        <div class="card bg-transparent border-0 product-card">
          <img src="******************************" class="card-img-top" alt="Large Decorated Wreath">
          <div class="card-body text-center">
            <h5 class="card-title">Large Decorated Wreath</h5>
            <p class="card-text">Oversized wreath with extra decorations for grand entrances.</p>
            <p class="card-text">Price: $50</p>
          </div>
        </div>
      </div>
      <div class="col-md-3 mb-4">
        <div class="card bg-transparent border-0 product-card">
          <img src="******************************" class="card-img-top" alt="Red Poinsettia">
          <div class="card-body text-center">
            <h5 class="card-title">Red Poinsettia</h5>
            <p class="card-text">Classic vibrant red blooms to brighten your holiday decor.</p>
            <p class="card-text">Price: $10 - $20</p>
          </div>
        </div>
      </div>
      <div class="col-md-3 mb-4">
        <div class="card bg-transparent border-0 product-card">
          <img src="******************************" class="card-img-top" alt="White Poinsettia">
          <div class="card-body text-center">
            <h5 class="card-title">White Poinsettia</h5>
            <p class="card-text">Elegant white variety for a sophisticated holiday look.</p>
            <p class="card-text">Price: $10 - $20</p>
          </div>
        </div>
      </div>
      <div class="col-md-3 mb-4">
        <div class="card bg-transparent border-0 product-card">
          <img src="******************************" class="card-img-top" alt="Grave Blanket">
          <div class="card-body text-center">
            <h5 class="card-title">Grave Blanket</h5>
            <p class="card-text">Evergreen arrangement with decorations for memorial sites.</p>
            <p class="card-text">Price: $30 - $50</p>
          </div>
        </div>
      </div>
      <div class="col-md-3 mb-4">
        <div class="card bg-transparent border-0 product-card">
          <img src="******************************" class="card-img-top" alt="White Pine Roping (25 ft)">
          <div class="card-body text-center">
            <h5 class="card-title">White Pine Roping (25 ft)</h5>
            <p class="card-text">Fresh garland for mantels, railings, or outdoor decor.</p>
            <p class="card-text">Price: $25</p>
          </div>
        </div>
      </div>
      <div class="col-md-3 mb-4">
        <div class="card bg-transparent border-0 product-card">
          <img src="******************************" class="card-img-top" alt="Holiday Trimmings Bundle">
          <div class="card-body text-center">
            <h5 class="card-title">Holiday Trimmings Bundle</h5>
            <p class="card-text">Assorted fresh greens and branches for custom decorations.</p>
            <p class="card-text">Price: $15</p>
          </div>
        </div>
      </div>
    </div>
  </div>
</section>
<footer class="text-center">
  <p><a href="https://www.facebook.com/p/Petrongolo-Evergreen-Plantation-100064604442455/" target="_blank" style="color: #fff;">Facebook</a> | Email: info@petrongolo.com | Phone: (609) 567-0336</p>
     <iframe src="https://www.google.com/maps/embed?pb=!1m18!1m12!1m3!1d3074.663747983165!2d-74.739235684646!3d39.641058279464!2m3!1f0!2f0!3f0!3m2!1i1024!2i768!4f13.1!3m3!1m2!1s0x89c13a4a5b5e4b0b%3A0x9f2c4e5d6a7b8c9d!2s7541%20Weymouth%20Rd%2C%20Hammonton%2C%20NJ%2008037!5e0!3m2!1sen!2sus!4v1728321600000!5m2!1sen!2sus" allowfullscreen="" loading="lazy" referrerpolicy="no-referrer-when-downgrade"></iframe>

  <p>&copy; 2025 Petrongolo Evergreen Plantation. All rights reserved.</p>
  <div>
    <a href="index.html" style="color: #fff;">Home</a> | <a href="PEP_AboutUs.html" style="color: #fff;">About</a> | <a href="#" style="color: #fff;">Catalog</a> | <a href="PEP_Reviews.html" style="color: #fff;">Reviews</a> | <a href="PEP_VisitUs.html" style="color: #fff;">Visit Us</a> | <a href="PEP_ContactUs.html" style="color: #fff;">Contact</a>
  </div>
</footer>
<script>
// Scroll animation observer
const sections = document.querySelectorAll('.animate-bottom, .animate-left, .animate-zoom, .animate-right');
const options = {
  threshold: 0.05
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