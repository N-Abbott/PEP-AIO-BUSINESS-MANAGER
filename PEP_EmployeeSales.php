<?php
session_start();
if (!isset($_SESSION['role']) || ($_SESSION['role'] !== 'employee' && $_SESSION['role'] !== 'admin')) {
  header("Location: PEP_Main.php");
  exit;
}
include 'config.php';

// Handle Admin Password Submission
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['admin_password'])) {
  $input_password = $_POST['admin_password'] ?? '';
  $admin_sql = "SELECT password FROM Admin LIMIT 1";
  $admin_result = $conn->query($admin_sql);
  if ($admin_result && $admin_result->num_rows > 0) {
    $admin_row = $admin_result->fetch_assoc();
    $admin_hash = $admin_row['password'];
    if (password_verify($input_password, $admin_hash)) {
      $_SESSION['role'] = 'admin';
      header("Location: PEP_Admin.php");
      exit;
    } else {
      header("Location: PEP_EmployeeSales.php");
      exit;
    }
  } else {
    $error = "Admin password not set: " . $conn->error;
  }
}

// Initialize cart if not set
if (!isset($_SESSION['cart'])) {
  $_SESSION['cart'] = array();
}

// Handle Add to Cart
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['action']) && $_POST['action'] == 'add_to_cart') {
  $product_id = $_POST['product_id'] ?? 0;
  $quantity = $_POST['quantity'] ?? 0;
  if ($product_id > 0 && $quantity > 0) {
    $stmt = $conn->prepare("SELECT stock_number FROM products WHERE id = ?");
    $stmt->bind_param("i", $product_id);
    $stmt->execute();
    $result = $stmt->get_result();
    $product = $result->fetch_assoc();
    $stmt->close();
    if ($product && $quantity <= $product['stock_number']) {
      if (isset($_SESSION['cart'][$product_id])) {
        $_SESSION['cart'][$product_id] += $quantity;
      } else {
        $_SESSION['cart'][$product_id] = $quantity;
      }
      // Removed success message for add to cart
    } else {
      $error = "Insufficient stock or invalid product.";
    }
  } else {
    $error = "Invalid quantity or product.";
  }
}

// Handle Remove from Cart
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['action']) && $_POST['action'] == 'remove_from_cart') {
  $product_id = $_POST['product_id'] ?? 0;
  if ($product_id > 0 && isset($_SESSION['cart'][$product_id])) {
    unset($_SESSION['cart'][$product_id]);
    // Removed success message for remove from cart
  }
}

// Get optional customer email
$customer_email = isset($_POST['customer_email']) ? trim($_POST['customer_email']) : null;

// Handle Checkout Cash
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['action']) && $_POST['action'] == 'checkout_cash') {
  if (!empty($_SESSION['cart'])) {
    $conn->begin_transaction();
    try {
      foreach ($_SESSION['cart'] as $product_id => $quantity) {
        $stmt = $conn->prepare("SELECT price, stock_number FROM products WHERE id = ? FOR UPDATE");
        $stmt->bind_param("i", $product_id);
        $stmt->execute();
        $result = $stmt->get_result();
        $product = $result->fetch_assoc();
        $stmt->close();
        if ($product && $quantity <= $product['stock_number']) {
          $total = $product['price'] * $quantity;
          $stmt = $conn->prepare("INSERT INTO sales_log (product_id, quantity, total, payment_method) VALUES (?, ?, ?, 'cash')");
          $stmt->bind_param("iid", $product_id, $quantity, $total);
          $stmt->execute();
          $stmt->close();
          $stmt = $conn->prepare("UPDATE products SET stock_number = stock_number - ? WHERE id = ?");
          $stmt->bind_param("ii", $quantity, $product_id);
          $stmt->execute();
          $stmt->close();
        } else {
          throw new Exception("Insufficient stock for product ID $product_id");
        }
      }
      $conn->commit();

      // Email receipt if address provided
      if (!empty($customer_email)) {

    $payment_method = 'Cash';
    $subject = "Your Purchase Receipt from Petrongolo Evergreen Plantation";

    $message = "
    <html>
    <body style='font-family: Arial, sans-serif; background-color:#f8f9fa; padding:20px;'>
      <div style='max-width:600px; margin:auto; background:#fff; border:1px solid #ddd; border-radius:10px; padding:20px;'>
        <h2 style='color:#2c5530;'>Thank you for your purchase!</h2>
        <p>Here are your order details:</p>
        <table border='1' cellpadding='8' cellspacing='0' width='100%' style='border-collapse: collapse; text-align:center;'>
          <tr style='background-color:#2c5530; color:white;'>
            <th>Product</th><th>Quantity</th><th>Price</th><th>Subtotal</th>
          </tr>";

    $total_all = 0;

    // Combine duplicate items to show correct quantities
    $cart_items = [];
    foreach ($_SESSION['cart'] as $product_id => $quantity) {
        $cart_items[$product_id] = ($cart_items[$product_id] ?? 0) + $quantity;
    }

    foreach ($cart_items as $product_id => $quantity) {
        $stmt = $conn->prepare("SELECT name, price FROM products WHERE id = ?");
        $stmt->bind_param("i", $product_id);
        $stmt->execute();
        $result = $stmt->get_result();
        $product = $result->fetch_assoc();
        $stmt->close();

        if ($product) {
            $line_total = $product['price'] * $quantity;
            $total_all += $line_total;

            $message .= "<tr>
                <td>" . htmlspecialchars($product['name']) . "</td>
                <td>" . intval($quantity) . "</td>
                <td>$" . number_format($product['price'], 2) . "</td>
                <td>$" . number_format($line_total, 2) . "</td>
            </tr>";
        }
    }

    $message .= "
        </table>
        <p style='margin-top:15px; font-size:16px;'><strong>Total:</strong> $" . number_format($total_all, 2) . "</p>
        <p><strong>Payment Method:</strong> {$payment_method}</p>
        <p><strong>Date:</strong> " . date("Y-m-d H:i:s") . "</p>
        <p style='color:#2c5530;'>We appreciate your business!</p>
        <hr>
        <p style='font-size:0.9em;color:#555;'>Petrongolo Evergreen Plantation<br>
        noreply@petrongoloevergreenplantation.com</p>
      </div>
    </body>
    </html>";

    // Proper headers
    $headers  = "From: noreply@petrongoloevergreenplantation.com\r\n";
    $headers .= "Reply-To: noreply@petrongoloevergreenplantation.com\r\n";
    $headers .= "Content-type: text/html; charset=UTF-8\r\n";

    if (mail($customer_email, $subject, $message, $headers)) {
        $success .= " Receipt emailed to {$customer_email}.";
    } else {
        $error = "Transaction completed, but failed to send receipt.";
    }
}


      $_SESSION['cart'] = array();
      $success = "Transaction completed with cash.";
      if (!empty($customer_email)) {
        $success .= " Receipt emailed to {$customer_email}.";
      }

    } catch (Exception $e) {
      $conn->rollback();
      $error = "Error during transaction: " . $e->getMessage();
    }
  } else {
    $error = "Cart is empty.";
  }
}

// Handle Checkout Venmo Confirm
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['action']) && $_POST['action'] == 'confirm_venmo') {
  if (!empty($_SESSION['cart'])) {
    $conn->begin_transaction();
    try {
      foreach ($_SESSION['cart'] as $product_id => $quantity) {
        $stmt = $conn->prepare("SELECT price, stock_number FROM products WHERE id = ? FOR UPDATE");
        $stmt->bind_param("i", $product_id);
        $stmt->execute();
        $result = $stmt->get_result();
        $product = $result->fetch_assoc();
        $stmt->close();
        if ($product && $quantity <= $product['stock_number']) {
          $total = $product['price'] * $quantity;
          $stmt = $conn->prepare("INSERT INTO sales_log (product_id, quantity, total, payment_method) VALUES (?, ?, ?, 'venmo')");
          $stmt->bind_param("iid", $product_id, $quantity, $total);
          $stmt->execute();
          $stmt->close();
          $stmt = $conn->prepare("UPDATE products SET stock_number = stock_number - ? WHERE id = ?");
          $stmt->bind_param("ii", $quantity, $product_id);
          $stmt->execute();
          $stmt->close();
        } else {
          throw new Exception("Insufficient stock for product ID $product_id");
        }
      }
      $conn->commit();

      // Email receipt if address provided
     if (!empty($customer_email)) {

    // set payment method (manually set this in each section)
    $payment_method = 'Venmo'; // or 'Cash' in the other block

    $subject = "Your Purchase Receipt from Petrongolo Evergreen Plantation";

    // Build HTML header
    $message = "
    <html>
    <body style='font-family: Arial, sans-serif; background-color:#f8f9fa; padding:20px;'>
      <div style='max-width:600px; margin:auto; background:#fff; border:1px solid #ddd; border-radius:10px; padding:20px;'>
        <h2 style='color:#2c5530;'>Thank you for your purchase!</h2>
        <p>Here are your order details:</p>
        <table border='1' cellpadding='8' cellspacing='0' width='100%' style='border-collapse: collapse; text-align:center;'>
          <tr style='background-color:#2c5530; color:white;'>
            <th>Product</th><th>Quantity</th><th>Price</th><th>Subtotal</th>
          </tr>";

    $total_all = 0;

    // combine duplicates correctly
    $cart_items = [];
    foreach ($_SESSION['cart'] as $product_id => $quantity) {
        $cart_items[$product_id] = ($cart_items[$product_id] ?? 0) + $quantity;
    }

    foreach ($cart_items as $product_id => $quantity) {
        $stmt = $conn->prepare("SELECT name, price FROM products WHERE id = ?");
        $stmt->bind_param("i", $product_id);
        $stmt->execute();
        $result = $stmt->get_result();
        $product = $result->fetch_assoc();
        $stmt->close();

        if ($product) {
            $line_total = $product['price'] * $quantity;
            $total_all += $line_total;

            $message .= "<tr>
                <td>" . htmlspecialchars($product['name']) . "</td>
                <td>" . intval($quantity) . "</td>
                <td>$" . number_format($product['price'], 2) . "</td>
                <td>$" . number_format($line_total, 2) . "</td>
            </tr>";
        }
    }

    $message .= "
        </table>
        <p style='margin-top:15px; font-size:16px;'><strong>Total:</strong> $" . number_format($total_all, 2) . "</p>
        <p><strong>Payment Method:</strong> {$payment_method}</p>
        <p><strong>Date:</strong> " . date("Y-m-d H:i:s") . "</p>
        <p style='color:#2c5530;'>We appreciate your business!</p>
        <hr>
        <p style='font-size:0.9em;color:#555;'>Petrongolo Evergreen Plantation<br>
        noreply@petrongoloevergreenplantation.com</p>
      </div>
    </body>
    </html>";

    // Proper headers for Hostinger (same as your working verification email)
    $headers  = "From: noreply@petrongoloevergreenplantation.com\r\n";
    $headers .= "Reply-To: noreply@petrongoloevergreenplantation.com\r\n";
    $headers .= "Content-type: text/html; charset=UTF-8\r\n";

    if (mail($customer_email, $subject, $message, $headers)) {
        $success .= " Receipt emailed to {$customer_email}.";
    } else {
        $error = "Transaction completed, but failed to send receipt.";
    }
}



      $_SESSION['cart'] = array();
      $success = "Transaction completed with Venmo.";
      if (!empty($customer_email)) {
        $success .= " Receipt emailed to {$customer_email}.";
      }

    } catch (Exception $e) {
      $conn->rollback();
      $error = "Error during transaction: " . $e->getMessage();
    }
  } else {
    $error = "Cart is empty.";
  }
}

// Fetch Products
$products_sql = "SELECT id, name, price, stock_number FROM products WHERE stock_number > 0";
$products_result = $conn->query($products_sql);

$venmo_username = '@Petrongolo-Evergreen'; // Replace with actual Venmo username

$cart_total = 0;
if (!empty($_SESSION['cart'])) {
  foreach ($_SESSION['cart'] as $product_id => $quantity) {
    $stmt = $conn->prepare("SELECT price FROM products WHERE id = ?");
    $stmt->bind_param("i", $product_id);
    $stmt->execute();
    $result = $stmt->get_result();
    $product = $result->fetch_assoc();
    $stmt->close();
    if ($product) {
      $cart_total += $product['price'] * $quantity;
    }
  }
}

?>

<!DOCTYPE html>
<html lang="en">
<head>
  <title>Employee Portal - Sales - Petrongolo Evergreen Plantation</title>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-QWTKZyjpPEjISv5WaRU9OFeRpok6YctnYmDr5pNlyT2bRjXh0JMhjY6hW+ALEwIH" crossorigin="anonymous">
  <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Oswald:wght@400;700&family=Roboto:wght@400;700&display=swap">
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
  <style>
    html, body {
      height: 100%;
      width: 100%;
      overflow-x: hidden;
      font-family: 'Roboto', sans-serif;
      background-color: #f8f9fa;
      color: #333;
    }
    .navbar {
      background-color: #2c5530;
      box-shadow: 0 2px 4px rgba(0,0,0,0.1);
    }
    .navbar-brand, .nav-link {
      color: #fff !important;
    }
    .sidebar {
      height: 100vh;
      background-color: #2c5530;
      padding: 20px;
      position: fixed;
      right: 0;
      top: 0;
      width: 250px;
      overflow-y: auto;
      border-left: 1px solid #dee2e6;
      box-shadow: -2px 0 5px rgba(0,0,0,0.1);
    }
    .sidebar h4 {
      color: #fff;
    }
    .sidebar .nav-link {
      color: #fff;
      font-weight: 600;
      padding: 10px;
      border-radius: 5px;
      margin-bottom: 10px;
      transition: background-color 0.3s;
    }
    .sidebar .nav-link:hover {
      background-color: #5c8c61;
    }
    .sidebar .nav-link.active {
      background-color: #5c8c61;
      color: #fff !important;
    }
    .content {
      margin-right: 250px;
      padding: 20px;
    }
    .section-title {
      text-align: center;
      margin-bottom: 40px;
      color: #2c5530;
      font-family: 'Oswald', sans-serif;
      font-weight: 700;
    }
    .table {
      box-shadow: 0 2px 4px rgba(0,0,0,0.1);
    }
    .table th {
      background-color: #2c5530;
      color: #fff;
    }
    .btn-primary {
      background-color: #2c5530;
      border-color: #2c5530;
    }
    .btn-primary:hover {
      background-color: #5c8c61;
      border-color: #5c8c61;
    }
  </style>
</head>
<body>
  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js" integrity="sha384-YvpcrYf0tY3lHB60NNkmXc5s9fDVZLESaAA55NDzOxhy9GkcIdslK1eN7N6jIeHz" crossorigin="anonymous"></script>
  <?php if (isset($success)) echo "<div class='alert alert-success alert-dismissible fade show' role='alert'>$success <button type='button' class='btn-close' data-bs-dismiss='alert' aria-label='Close'></button></div>"; ?>
  <?php if (isset($error)) echo "<div class='alert alert-danger alert-dismissible fade show' role='alert'>$error <button type='button' class='btn-close' data-bs-dismiss='alert' aria-label='Close'></button></div>"; ?>
  <nav class="navbar navbar-expand-lg">
    <div class="container-fluid">
      <a class="navbar-brand" href="#">Employee Portal</a>
      <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav" aria-controls="navbarNav" aria-expanded="false" aria-label="Toggle navigation">
        <span class="navbar-toggler-icon"></span>
      </button>
    </div>
  </nav>
  <div class="sidebar">
    <h4>Options</h4>
    <ul class="nav flex-column">
      <li class="nav-item">
        <a class="nav-link" href="PEP_EmployeeSchedule.php"><i class="bi bi-calendar-event me-2"></i>View Schedule</a>
      </li>
      <li class="nav-item">
        <a class="nav-link" href="PEP_EmployeeTasks.php"><i class="bi bi-check2-square me-2"></i>View Tasks</a>
      </li>
      <li class="nav-item">
        <a class="nav-link active" href="PEP_EmployeeSales.php"><i class="bi bi-cart me-2"></i>Input Sales</a>
      </li>
      <li class="nav-item">
        <a class="nav-link" href="#" data-bs-toggle="modal" data-bs-target="#adminLoginModal"><i class="bi bi-lock me-2"></i>Admin Access</a>
      </li>
      <li class="nav-item">
        <a class="nav-link" href="logout.php"><i class="bi bi-box-arrow-left me-2"></i>Logout</a>
      </li>
    </ul>
  </div>
  <div class="content">
    <h2 class="section-title">Input Sales</h2>
    <h4>Available Products</h4>
    <table class="table table-striped table-hover">
      <thead>
        <tr>
          <th>Name</th>
          <th>Price</th>
          <th>Stock</th>
          <th>Quantity</th>
          <th>Actions</th>
        </tr>
      </thead>
      <tbody>
        <?php if ($products_result->num_rows > 0): ?>
          <?php while ($product = $products_result->fetch_assoc()): ?>
            <tr>
              <form action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>" method="post">
                <input type="hidden" name="action" value="add_to_cart">
                <input type="hidden" name="product_id" value="<?php echo $product['id']; ?>">
                <td><?php echo htmlspecialchars($product['name']); ?></td>
                <td>$<?php echo number_format($product['price'], 2); ?></td>
                <td><?php echo htmlspecialchars($product['stock_number']); ?></td>
                <td><input type="number" name="quantity" min="1" max="<?php echo $product['stock_number']; ?>" value="1" required></td>
                <td><button type="submit" class="btn btn-primary btn-sm"><i class="bi bi-cart-plus"></i> Add to Cart</button></td>
              </form>
            </tr>
          <?php endwhile; ?>
        <?php else: ?>
          <tr><td colspan="5" class="text-center">No products available.</td></tr>
        <?php endif; ?>
      </tbody>
    </table>
    <h4 class="mt-4">Current Cart</h4>
    <table class="table table-striped table-hover">
      <thead>
        <tr>
          <th>Name</th>
          <th>Quantity</th>
          <th>Price</th>
          <th>Subtotal</th>
          <th>Actions</th>
        </tr>
      </thead>
      <tbody>
        <?php if (!empty($_SESSION['cart'])): ?>
          <?php foreach ($_SESSION['cart'] as $product_id => $quantity): ?>
            <?php
            $stmt = $conn->prepare("SELECT name, price FROM products WHERE id = ?");
            $stmt->bind_param("i", $product_id);
            $stmt->execute();
            $result = $stmt->get_result();
            $product = $result->fetch_assoc();
            $stmt->close();
            if ($product): ?>
              <tr>
                <td><?php echo htmlspecialchars($product['name']); ?></td>
                <td><?php echo $quantity; ?></td>
                <td>$<?php echo number_format($product['price'], 2); ?></td>
                <td>$<?php echo number_format($product['price'] * $quantity, 2); ?></td>
                <td>
                  <form action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>" method="post" style="display:inline;">
                    <input type="hidden" name="action" value="remove_from_cart">
                    <input type="hidden" name="product_id" value="<?php echo $product_id; ?>">
                    <button type="submit" class="btn btn-danger btn-sm"><i class="bi bi-trash"></i> Remove</button>
                  </form>
                </td>
              </tr>
            <?php endif; ?>
          <?php endforeach; ?>
          <tr>
            <td colspan="3" class="text-end"><strong>Total</strong></td>
            <td><strong>$<?php echo number_format($cart_total, 2); ?></strong></td>
            <td></td>
          </tr>
        <?php else: ?>
          <tr><td colspan="5" class="text-center">Cart is empty.</td></tr>
        <?php endif; ?>
      </tbody>
    </table>
    <?php if (!empty($_SESSION['cart'])): ?>
      <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#checkoutModal"><i class="bi bi-credit-card me-2"></i>Checkout</button>
    <?php endif; ?>
  </div>
  <!-- Checkout Modal -->
  <div class="modal fade" id="checkoutModal" tabindex="-1" aria-labelledby="checkoutLabel" aria-hidden="true">
    <div class="modal-dialog">
      <div class="modal-content">
        <div class="modal-header">
          <h5 class="modal-title" id="checkoutLabel">Checkout Options</h5>
          <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
        </div>
        <div class="modal-body">
            <form action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>" method="post">
    <div class="mb-3">
      <label for="customerEmail" class="form-label">Customer Email (optional)</label>
      <input type="email" class="form-control" id="customerEmail" name="customer_email" placeholder="Enter customer's email for receipt">
    </div>

    <input type="hidden" name="action" value="checkout_cash">
    <button type="submit" class="btn btn-primary mb-3 w-100">
      <i class="bi bi-cash me-2"></i>Cash
    </button>
  </form>

  <button class="btn btn-primary w-100" data-bs-toggle="collapse" data-bs-target="#venmoCollapse" aria-expanded="false" aria-controls="venmoCollapse">
    <i class="bi bi-phone me-2"></i>Venmo
  </button>

  <div class="collapse mt-3" id="venmoCollapse">
    <p>Scan the QR code to pay via Venmo.</p>
    <img src="https://chart.googleapis.com/chart?cht=qr&chs=200x200&chl=venmo%3A%2F%2Fpaycharge%3Ftxn%3Dpay%26recipients%3D<?php echo urlencode($venmo_username); ?>%26amount%3D<?php echo $cart_total; ?>%26note%3DPetrongolo%2520Purchase" alt="Venmo QR Code">

    <form action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>" method="post" class="mt-3">
      <input type="hidden" name="action" value="confirm_venmo">
      <input type="hidden" name="customer_email" id="hiddenCustomerEmail">
      <button type="submit" class="btn btn-success w-100" onclick="return confirm('Confirm payment received?');">
        <i class="bi bi-check-circle me-2"></i>Confirm Payment
      </button>
    </form>
  </div>

  <script>
    document.addEventListener('input', function() {
      const emailInput = document.getElementById('customerEmail');
      const hiddenEmail = document.getElementById('hiddenCustomerEmail');
      if (emailInput && hiddenEmail) hiddenEmail.value = emailInput.value;
    });
  </script>
        </div>
      </div>
    </div>
  </div>
  <!-- Admin Login Modal -->
  <div class="modal fade" id="adminLoginModal" tabindex="-1" aria-labelledby="adminLoginLabel" aria-hidden="true">
    <div class="modal-dialog">
      <div class="modal-content">
        <div class="modal-header">
          <h5 class="modal-title" id="adminLoginLabel">Admin Access</h5>
          <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
        </div>
        <div class="modal-body">
          <form action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>" method="post">
            <div class="mb-3">
              <label for="adminPassword" class="form-label">Password</label>
              <input type="password" class="form-control" id="adminPassword" name="admin_password" placeholder="Enter admin password" required>
            </div>
            <button type="submit" class="btn btn-primary">Login</button>
          </form>
        </div>
      </div>
    </div>
  </div>
</body>
</html>
<?php
$conn->close();
?>