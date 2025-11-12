<?php
// login.php – Updated to use Customer & Employee tables
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);
session_start();
include 'config.php';
$success = $error = '';
$return_url = $_GET['return'] ?? 'PEP_Main.php';

// ---------------------------------------------------------------------
// 1. Email verification
// ---------------------------------------------------------------------
if (isset($_GET['verify'])) {
    $token = $_GET['verify'];
    $sql   = "SELECT customer_id FROM Customer WHERE verification_token = ? AND verified = 0";
    $stmt  = $conn->prepare($sql);
    $stmt->bind_param("s", $token);
    $stmt->execute();
    $stmt->bind_result($id);
    if ($stmt->fetch()) {
        $stmt->close();
        $sql  = "UPDATE Customer SET verified = 1, verification_token = NULL WHERE customer_id = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("i", $id);
        $stmt->execute() ? $success = "Account verified! Please sign in." : $error = "Verification failed.";
    } else {
        $error = "Invalid or already verified token.";
    }
    $stmt->close();
}

// ---------------------------------------------------------------------
// 2. Customer Sign-Up
// ---------------------------------------------------------------------
if ($_POST['action'] ?? '' === 'signup') {
    $first_name   = trim($_POST['fname'] ?? '');
    $last_name    = trim($_POST['lname'] ?? '');
    $phone_number = trim($_POST['phone'] ?? '');
    $email        = trim($_POST['email'] ?? '');
    $username     = trim($_POST['username'] ?? '');
    $password     = $_POST['password'] ?? '';

    if (!$first_name || !$last_name || !$email || !$username || !$password) {
        $error = "All fields are required.";
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error = "Invalid email.";
    } else {
        $sql  = "SELECT COUNT(*) FROM Customer WHERE username = ? OR email = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("ss", $username, $email);
        $stmt->execute();
        $stmt->bind_result($count);
        $stmt->fetch();
        $stmt->close();

        if ($count > 0) {
            $error = "Username or email already exists.";
        } else {
            $password_hash = password_hash($password, PASSWORD_DEFAULT);
            $sql  = "INSERT INTO Customer (first_name, last_name, phone_number, email, username, password_hash, verified)
                     VALUES (?, ?, ?, ?, ?, ?, 0)";
            $stmt = $conn->prepare($sql);
            $stmt->bind_param("ssssss", $first_name, $last_name, $phone_number, $email, $username, $password_hash);
            if ($stmt->execute()) {
                $id    = $stmt->insert_id;
                $token = md5(uniqid(rand(), true));

                $upd = $conn->prepare("UPDATE Customer SET verification_token = ? WHERE customer_id = ?");
                $upd->bind_param("si", $token, $id);
                $upd->execute();
                $upd->close();

                $to      = $email;
                $subject = "Verify Your Account";
                $message = "<p>Click the button below to verify your account:</p>
                            <a href='https://petrongoloevergreenplantation.com/login.php?verify=$token'
                               style='background:#2c5530;color:#fff;padding:10px 20px;text-decoration:none;border-radius:4px;'>
                               Confirm Account
                            </a>";
                $headers = "From: noreply@petrongoloevergreenplantation.com\r\nContent-type: text/html\r\n";

                mail($to, $subject, $message, $headers)
                    ? $success = "Verification email sent!"
                    : $error   = "Account created, but email failed.";
            } else {
                $error = "Error creating account.";
            }
            $stmt->close();
        }
    }
}

// ---------------------------------------------------------------------
// 3. Customer Login
// ---------------------------------------------------------------------
if ($_POST['action'] ?? '' === 'customer_login') {
    $username = trim($_POST['custUsername'] ?? '');
    $password = $_POST['custPassword'] ?? '';

    if (!$username || !$password) {
        $error = "Username and password required.";
    } else {
        $sql  = "SELECT customer_id, password_hash, verified FROM Customer WHERE username = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("s", $username);
        $stmt->execute();
        $stmt->bind_result($id, $password_hash, $verified);
        if ($stmt->fetch()) {
            if (!$verified) {
                $error = "Please verify your email.";
            } elseif (password_verify($password, $password_hash)) {
                $_SESSION['user_id'] = $id;
                $_SESSION['role']     = 'customer';
                header("Location: $return_url");
                exit;
            } else {
                $error = "Invalid credentials.";
            }
        } else {
            $error = "Invalid credentials.";
        }
        $stmt->close();
    }
}

// ---------------------------------------------------------------------
// 4. Employee Login – now redirects to PEP_EmployeePortal.php
// ---------------------------------------------------------------------
if ($_POST['action'] ?? '' === 'employee_login') {
    $email    = trim($_POST['empEmail'] ?? '');
    $password = $_POST['empPassword'] ?? '';

    if (!$email || !$password) {
        $error = "Email and password required.";
    } else {
        $sql  = "SELECT employee_id, password_hash, role FROM Employee WHERE employee_email = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("s", $email);
        $stmt->execute();
        $stmt->bind_result($id, $password_hash, $role);
        if ($stmt->fetch() && password_verify($password, $password_hash)) {
            $_SESSION['user_id'] = $id;
            $_SESSION['role']     = $role;
            $_SESSION['email']    = $email;   // needed for tasks/schedule queries
            header("Location: PEP_EmployeePortal.php");   // <-- NEW REDIRECT
            exit;
        } else {
            $error = "Invalid credentials.";
        }
        $stmt->close();
    }
}
$conn->close();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <title>Login / Sign Up – Petrongolo Evergreen Plantation</title>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet"
          href="https://fonts.googleapis.com/css2?family=Oswald:wght@400;700&family=Roboto:wght@400;700&display=swap">
    <link rel="icon" type="image/png" href="Tree.png">
    <style>
        body {font-family:'Roboto',sans-serif;background:#f8f9fa;}
        .container {max-width:600px;margin-top:50px;}
        .btn-primary {background:#2c5530;border:none;}
        .btn-primary:hover {background:#5c8c61;}
    </style>
</head>
<body>
<div class="container">
    <h2 class="text-center mb-4">Login or Sign Up</h2>

    <?php if ($success): ?>
        <div class="alert alert-success"><?= htmlspecialchars($success) ?></div>
    <?php endif; ?>
    <?php if ($error): ?>
        <div class="alert alert-danger"><?= htmlspecialchars($error) ?></div>
    <?php endif; ?>

    <div class="text-center mb-4">
        <button type="button" class="btn btn-primary mb-2" onclick="showCustomerLogin()">Customer</button>
        <button type="button" class="btn btn-secondary mb-2" onclick="showEmployeeLogin()">Employee</button>
    </div>

    <!-- ==================== CUSTOMER FORMS ==================== -->
    <div id="customerForm" style="display:none;">
        <!-- Sign-In -->
        <form action="login.php?return=<?= urlencode($return_url) ?>" method="post" id="customerSignInForm">
            <input type="hidden" name="action" value="customer_login">
            <h6>Customer Sign In</h6>
            <div class="mb-3"><label>Username</label><input type="text" class="form-control" name="custUsername" required></div>
            <div class="mb-3"><label>Password</label><input type="password" class="form-control" name="custPassword" required></div>
            <button type="submit" class="btn btn-primary">Sign In</button>
            <p class="mt-2"><a href="#" onclick="showSignUp()">Create an account</a></p>
        </form>

        <!-- Sign-Up -->
        <form action="login.php?return=<?= urlencode($return_url) ?>" method="post" id="customerSignUpForm" style="display:none;">
            <input type="hidden" name="action" value="signup">
            <h6>Customer Sign Up</h6>
            <div class="mb-3"><label>First Name</label><input type="text" class="form-control" name="fname" required></div>
            <div class="mb-3"><label>Last Name</label><input type="text" class="form-control" name="lname" required></div>
            <div class="mb-3"><label>Phone (optional)</label><input type="tel" class="form-control" name="phone"></div>
            <div class="mb-3"><label>Email</label><input type="email" class="form-control" name="email" required></div>
            <div class="mb-3"><label>Username</label><input type="text" class="form-control" name="username" required></div>
            <div class="mb-3"><label>Password</label><input type="password" class="form-control" name="password" required></div>
            <button type="submit" class="btn btn-primary">Sign Up</button>
            <p class="mt-2"><a href="#" onclick="showSignIn()">Already have an account? Sign In</a></p>
        </form>
    </div>

    <!-- ==================== EMPLOYEE FORM ==================== -->
    <div id="employeeForm" style="display:none;">
        <form action="login.php?return=<?= urlencode($return_url) ?>" method="post">
            <input type="hidden" name="action" value="employee_login">
            <h6>Employee Sign In</h6>
            <div class="mb-3"><label>Email</label><input type="email" class="form-control" name="empEmail" required></div>
            <div class="mb-3"><label>Password</label><input type="password" class="form-control" name="empPassword" required></div>
            <button type="submit" class="btn btn-primary">Sign In</button>
        </form>
    </div>

    <div class="text-center mt-4">
        <a href="<?= htmlspecialchars($return_url) ?>" class="btn btn-secondary">Go Back</a>
    </div>
</div>

<script>
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

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>