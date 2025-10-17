<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

session_start();
include 'config.php'; // Assuming this sets up $conn as mysqli

// Handle POST requests
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    if (isset($_POST['action'])) {
        $action = $_POST['action'];
        
        try {
            if ($action == 'customer_login') {
                $custUsername = $_POST['custUsername'];
                $custPassword = $_POST['custPassword'];
                
                $stmt = $conn->prepare("SELECT * FROM custLogin WHERE username = ?");
                $stmt->bind_param("s", $custUsername);
                $stmt->execute();
                $result = $stmt->get_result();
                $user = $result->fetch_assoc();
                
                if ($user && password_verify($custPassword, $user['password_hash'])) {
                    $_SESSION['user_id'] = $user['id'];
                    $_SESSION['role'] = 'customer';
                    header("Location: " . $_SERVER['PHP_SELF']);
                    exit;
                } else {
                    header("Location: " . $_SERVER['PHP_SELF'] . "?error=invalid_credentials");
                    exit;
                }
            } elseif ($action == 'signup') {
                $fname = $_POST['fname'];
                $lname = $_POST['lname'];
                $phone = $_POST['phone'];
                $email = $_POST['email'];
                $username = $_POST['username'];
                $password_hash = password_hash($_POST['password'], PASSWORD_DEFAULT);
                
                $stmt = $conn->prepare("INSERT INTO custLogin (first_name, last_name, phone_number, email_address, username, password_hash) 
                                        VALUES (?, ?, ?, ?, ?, ?)");
                $stmt->bind_param("ssssss", $fname, $lname, $phone, $email, $username, $password_hash);
                $stmt->execute();
                
                $userId = $conn->insert_id;
                $_SESSION['user_id'] = $userId;
                $_SESSION['role'] = 'customer';
                
                header("Location: " . $_SERVER['PHP_SELF']);
                exit;
            } elseif ($action == 'employee_login') {
                $empEmail = $_POST['empEmail'];
                $empPassword = $_POST['empPassword'];
                
                $stmt = $conn->prepare("SELECT * FROM employeeLogin WHERE email = ?");
                $stmt->bind_param("s", $empEmail);
                $stmt->execute();
                $result = $stmt->get_result();
                $user = $result->fetch_assoc();
                
                if ($user && password_verify($empPassword, $user['password'])) {
                    $_SESSION['user_id'] = $user['id'];
                    $_SESSION['role'] = $user['role'];
                    header("Location: " . $_SERVER['PHP_SELF']);
                    exit;
                } else {
                    header("Location: " . $_SERVER['PHP_SELF'] . "?error=invalid_credentials");
                    exit;
                }
            }
        } catch (Exception $e) {
            error_log("Error: " . $e->getMessage());
            header("Location: " . $_SERVER['PHP_SELF'] . "?error=database_error");
            exit;
        }
    }
}
?>
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
.list-btn {
  position: absolute;
  top: 10px;
  right: 120px; /* Positioned to the left of login button */
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
  <a href="PEP_CustomerList.php" class="btn btn-primary list-btn">My List</a>
<?php else: ?>
  <button type="button" class="btn btn-primary login-btn" data-bs-toggle="modal" data-bs-target="#loginModal">Login / Sign Up</button>
  <button type="button" class="btn btn-primary list-btn" onclick="showListAlert()">My List</button>
<?php endif; ?>
<img src="Banner_Logo.png" class="img-fluid banner" alt="Banner Logo">
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
<section id="catalog" class="py-5 animate-bottom">
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
              <div class="card-body text-center">
                <h5 class="card-title">' . htmlspecialchars($product['name']) . '</h5>
                <p class="card-text">' . htmlspecialchars($product['description']) . '</p>
                <p class="card-text">Price: $' . number_format($product['price'], 2) . '</p>';
          if (isset($_SESSION['user_id']) && $_SESSION['role'] === 'customer') {
            echo '<button type="button" class="btn btn-success add-to-list-btn" data-product-id="' . $product['id'] . '">Add to List</button>';
          } else {
            echo '<button type="button" class="btn btn-success" onclick="showListAlert()">Add to List</button>';
          }
          echo '</div>
            </div>
          </div>';
        }
      } else {
        echo '<p>No products available.</p>';
      }
      ?>
    </div>
  </div>
</section>
<footer class="text-center">
  <p><a href="https://www.facebook.com/p/Petrongolo-Evergreen-Plantation-100064604442455/" target="_blank" style="color: #fff;">Facebook</a> | Email: info@petrongolo.com | Phone: (609) 567-0336</p>
  <iframe src="https://www.google.com/maps/embed?pb=!1m18!1m12!1m3!1d3074.663747983165!2d-74.739235684646!3d39.641058279464!2m3!1f0!2f0!3f0!3m2!1i1024!2i768!4f13.1!3m3!1m2!1s0x89c13a4a5b5e4b0b%3A0x9f2c4e5d6a7b8c9d!2s7541%20Weymouth%20Rd%2C%20Hammonton%2C%20NJ%2008037!5e0!3m2!1sen!2sus!4v1728321600000!5m2!1sen!2sus" allowfullscreen="" loading="lazy" referrerpolicy="no-referrer-when-downgrade"></iframe>
  <p>&copy; 2025 Petrongolo Evergreen Plantation. All rights reserved.</p>
  <div>
    <a href="PEP_Main.php" style="color: #fff;">Home</a> | <a href="PEP_AboutUs.php" style="color: #fff;">About</a> | <a href="#" style="color: #fff;">Catalog</a> | <a href="PEP_Reviews.php" style="color: #fff;">Reviews</a> | <a href="PEP_VisitUs.php" style="color: #fff;">Visit Us</a> | <a href="PEP_ContactUs.php" style="color: #fff;">Contact</a>
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
<form action="<?php echo $_SERVER['PHP_SELF']; ?>" method="post" id="customerSignInForm">
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
<form action="<?php echo $_SERVER['PHP_SELF']; ?>" method="post" id="customerSignUpForm" style="display: none;">
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
<form action="<?php echo $_SERVER['PHP_SELF']; ?>" method="post">
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
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js" integrity="sha384-YvpcrYf0tY3lHB60NNkmXc5s9fDVZLESaAA55NDzOxhy9GkcIdslK1eN7N6jIeHz" crossorigin="anonymous"></script>
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
// Show alert and open login modal for list access when not logged in
function showListAlert() {
  alert("Please login to access your list.");
  const loginModal = new bootstrap.Modal(document.getElementById('loginModal'));
  loginModal.show();
}
// AJAX for add to list
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
<?php if (isset($_GET['error'])): ?>
  alert("<?php echo htmlspecialchars($_GET['error'] == 'invalid_credentials' ? 'Invalid username or password.' : ($_GET['error'] == 'database_error' ? 'Database error occurred.' : 'Signup failed. Username or email may already exist.')); ?>");
  const loginModal = new bootstrap.Modal(document.getElementById('loginModal'));
  loginModal.show();
<?php endif; ?>
</script>
</body>
</html>