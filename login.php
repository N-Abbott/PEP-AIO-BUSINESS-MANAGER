<?php
// New file: login.php (handles all login/signup logic, verification, and displays the forms statically)

ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

session_start(); // Start session for login state
include 'config.php'; // Include DB connection

$success = $error = '';
$return_url = isset($_GET['return']) ? $_GET['return'] : 'PEP_Main.php'; // Default to main if no return

// Handle verification if token provided
if (isset($_GET['verify'])) {
    $token = $_GET['verify'];
    $sql = "SELECT id FROM custLogin WHERE verification_token = ? AND verified = 0";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("s", $token);
    $stmt->execute();
    $stmt->bind_result($id);
    if ($stmt->fetch()) {
        $stmt->close();
        $sql = "UPDATE custLogin SET verified = 1, verification_token = NULL WHERE id = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("i", $id);
        if ($stmt->execute()) {
            $success = "Account verified! Please sign in.";
        } else {
            $error = "Verification failed.";
        }
    } else {
        $error = "Invalid or already verified token.";
    }
    $stmt->close();
}

// Handle Customer Sign Up
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['action']) && $_POST['action'] == 'signup') {
    $first_name = $_POST['fname'];
    $last_name = $_POST['lname'];
    $phone_number = $_POST['phone'];
    $email_address = $_POST['email'];
    $username = $_POST['username'];
    $password = $_POST['password'];

    // Check for duplicates
    $sql = "SELECT COUNT(*) FROM custLogin WHERE username = ? OR email_address = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("ss", $username, $email_address);
    $stmt->execute();
    $stmt->bind_result($count);
    $stmt->fetch();
    $stmt->close();

    if ($count > 0) {
        $error = "Error: Username or email already exists.";
    } else {
        // Hash password
        $password_hash = password_hash($password, PASSWORD_DEFAULT);

        // Insert into DB (note: assumes table has verification_token and verified columns)
        $sql = "INSERT INTO custLogin (first_name, last_name, phone_number, email_address, username, password_hash, verified) VALUES (?, ?, ?, ?, ?, ?, 0)";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("ssssss", $first_name, $last_name, $phone_number, $email_address, $username, $password_hash);

        if ($stmt->execute()) {
            $id = $stmt->insert_id;
            // Generate verification token
            $token = md5(uniqid(rand(), true));
            $sql = "UPDATE custLogin SET verification_token = ? WHERE id = ?";
            $update_stmt = $conn->prepare($sql);
            $update_stmt->bind_param("si", $token, $id);
            $update_stmt->execute();
            $update_stmt->close();

            // Send verification email (replace 'yourdomain.com' with your actual domain)
            $to = $email_address;
            $subject = "Verify Your Account at Petrongolo Evergreen Plantation";
            $message = "Thank you for signing up! Please click the button below to verify your account:<br><br><br>
                        <a href='https://petrongoloevergreenplantation.com/login.php?verify=$token' style='background-color: #2c5530; color: #fff; padding: 10px 20px; text-decoration: none;'>Confirm Account</a>";
            $headers = "From: noreply@petrongoloevergreenplantation.com\r\n";
            $headers .= "Content-type: text/html\r\n";
            if (mail($to, $subject, $message, $headers)) {
                $success = "Verification email sent! Please check your email to confirm your account.";
            } else {
                $error = "Account created, but failed to send verification email. Please contact support.";
            }
        } else {
            $error = "Error creating account.";
        }
        $stmt->close();
    }
}

// Handle Customer Login
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['action']) && $_POST['action'] == 'customer_login') {
    $username = $_POST['custUsername'];
    $password = $_POST['custPassword'];

    $sql = "SELECT id, password_hash, verified FROM custLogin WHERE username = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("s", $username);
    $stmt->execute();
    $stmt->bind_result($id, $password_hash, $verified);
    if ($stmt->fetch()) {
        if ($verified == 0) {
            $error = "Please verify your email before logging in.";
        } elseif (password_verify($password, $password_hash)) {
            $_SESSION['user_id'] = $id;
            $_SESSION['role'] = 'customer';
            $success = "Login successful!";
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

// Handle Employee Login
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['action']) && $_POST['action'] == 'employee_login') {
    $email = $_POST['empEmail'];
    $password = $_POST['empPassword'];

    $sql = "SELECT id, password, role FROM employeeLogin WHERE email = ? AND role IN ('employee', 'admin')";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $stmt->bind_result($id, $password_hash, $role);
    if ($stmt->fetch() && password_verify($password, $password_hash)) {
        $_SESSION['user_id'] = $id;
        $_SESSION['role'] = $role;
        $success = "Login successful! Redirecting to portal...";
        header("Location: PEP_EmployeeSchedule.php");
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
    <title>Login / Sign Up - Petrongolo Evergreen Plantation</title>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-QWTKZyjpPEjISv5WaRU9OFeRpok6YctnYmDr5pNlyT2bRjXh0JMhjY6hW+ALEwIH" crossorigin="anonymous">
    <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Oswald:wght@400;700&family=Roboto:wght@400;700&display=swap">
    <link rel="icon" type="image/png" href="Tree.png">
    <style>
        /* Reuse styles from PEP_Main.php for consistency */
        body {
            font-family: 'Roboto', sans-serif;
            background-color: #f8f9fa;
            color: #333;
        }
        .container {
            max-width: 600px;
            margin-top: 50px;
        }
        .btn-primary {
            background-color: #2c5530;
            border: none;
        }
        .form-control {
            background-color: #fff;
            color: #333;
            border: 1px solid #e9ecef;
        }
    </style>
</head>
<body>
    <div class="container">
        <h2 class="text-center mb-4">Login or Sign Up</h2>
        <?php if ($success) echo "<div class='alert alert-success'>$success</div>"; ?>
        <?php if ($error) echo "<div class='alert alert-danger'>$error</div>"; ?>

        <div class="text-center mb-4">
            <button type="button" class="btn btn-primary mb-3" onclick="showCustomerLogin()">Customer Login / Sign Up</button>
            <button type="button" class="btn btn-secondary mb-3" onclick="showEmployeeLogin()">Employee Login</button>
        </div>

        <div id="customerForm" style="display: none;">
            <form action="login.php?return=<?php echo urlencode($return_url); ?>" method="post" id="customerSignInForm">
                <input type="hidden" name="action" value="customer_login">
                <h6>Customer Sign In</h6>
                <div class="mb-3">
                    <label for="custUsername" class="form-label">Username</label>
                    <input type="text" class="form-control" id="custUsername" name="custUsername" placeholder="Username" required>
                </div>
                <div class="mb-3">
                    <label for="custPassword" class="form-label">Password</label>
                    <input type="password" class="form-control" id="custPassword" name="custPassword" placeholder="Password" required>
                </div>
                <button type="submit" class="btn btn-primary">Sign In</button>
                <p class="mt-2">Don't have an account? <a href="#" onclick="showSignUp()">Sign Up</a></p>
            </form>
            <form action="login.php?return=<?php echo urlencode($return_url); ?>" method="post" id="customerSignUpForm" style="display: none;">
                <input type="hidden" name="action" value="signup">
                <h6>Customer Sign Up</h6>
                <div class="mb-3">
                    <label for="fname" class="form-label">First Name</label>
                    <input type="text" class="form-control" id="fname" name="fname" placeholder="First Name" required>
                </div>
                <div class="mb-3">
                    <label for="lname" class="form-label">Last Name</label>
                    <input type="text" class="form-control" id="lname" name="lname" placeholder="Last Name" required>
                </div>
                <div class="mb-3">
                    <label for="phone" class="form-label">Phone Number</label>
                    <input type="tel" class="form-control" id="phone" name="phone" placeholder="Phone Number" required>
                </div>
                <div class="mb-3">
                    <label for="email" class="form-label">Email</label>
                    <input type="email" class="form-control" id="email" name="email" placeholder="Email" required>
                </div>
                <div class="mb-3">
                    <label for="username" class="form-label">Username</label>
                    <input type="text" class="form-control" id="username" name="username" placeholder="Username" required>
                </div>
                <div class="mb-3">
                    <label for="password" class="form-label">Password</label>
                    <input type="password" class="form-control" id="password" name="password" placeholder="Password" required>
                </div>
                <button type="submit" class="btn btn-primary">Sign Up</button>
                <p class="mt-2">Already have an account? <a href="#" onclick="showSignIn()">Sign In</a></p>
            </form>
        </div>

        <div id="employeeForm" style="display: none;">
            <form action="login.php?return=<?php echo urlencode($return_url); ?>" method="post">
                <input type="hidden" name="action" value="employee_login">
                <h6>Employee Sign In</h6>
                <div class="mb-3">
                    <label for="empEmail" class="form-label">Email</label>
                    <input type="email" class="form-control" id="empEmail" name="empEmail" placeholder="Email" required>
                </div>
                <div class="mb-3">
                    <label for="empPassword" class="form-label">Password</label>
                    <input type="password" class="form-control" id="empPassword" name="empPassword" placeholder="Password" required>
                </div>
                <button type="submit" class="btn btn-primary">Sign In</button>
            </form>
        </div>

        <div class="text-center mt-4">
            <a href="<?php echo $return_url; ?>" class="btn btn-secondary">Go Back</a>
        </div>
    </div>

    <script>
        // Form toggle functions
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
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js" integrity="sha384-YvpcrYf0tY3lHB60NNkmXc5s9fDVZLESaAA55NDzOxhy9GkcIdslK1eN7N6jIeHz" crossorigin="anonymous"></script>
</body>
</html>