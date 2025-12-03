<?php
ob_start();                    // ← Fixes redirect issues (must be first)
session_start();
include 'config.php';

$success = $error = '';
$return_url = $_GET['return'] ?? 'PEP_Main.php';

// ---------------------------------------------------------------------
// 1. Email verification
// ---------------------------------------------------------------------
if (isset($_GET['verify'])) {
    $token = $_GET['verify'];
    $sql = "SELECT customer_id FROM Customer WHERE verification_token = ? AND verified = 0";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("s", $token);
    $stmt->execute();
    $stmt->bind_result($id);
    if ($stmt->fetch()) {
        $stmt->close();
        $sql = "UPDATE Customer SET verified = 1, verification_token = NULL WHERE customer_id = ?";
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
    $first_name = trim($_POST['fname'] ?? '');
    $last_name  = trim($_POST['lname'] ?? '');
    $phone_number = trim($_POST['phone'] ?? '');
    $email      = trim($_POST['email'] ?? '');
    $username   = trim($_POST['username'] ?? '');
    $password   = $_POST['password'] ?? '';

    if (!$first_name || !$last_name || !$email || !$username || !$password) {
        $error = "All fields are required.";
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error = "Invalid email address.";
    } else {
        $sql = "SELECT COUNT(*) FROM Customer WHERE username = ? OR email = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("ss", $username, $email);
        $stmt->execute();
        $stmt->bind_result($count);
        $stmt->fetch();
        $stmt->close();

        if ($count > 0) {
            $error = "Username or email already taken.";
        } else {
            $password_hash = password_hash($password, PASSWORD_DEFAULT);
            $token = bin2hex(random_bytes(32));

            $sql = "INSERT INTO Customer (first_name, last_name, phone_number, email, username, password_hash, verification_token, verified)
                    VALUES (?, ?, ?, ?, ?, ?, ?, 0)";
            $stmt = $conn->prepare($sql);
            $stmt->bind_param("sssssss", $first_name, $last_name, $phone_number, $email, $username, $password_hash, $token);

            if ($stmt->execute()) {
                $verify_link = "https://petrongoloevergreenplantation.com/login.php?verify=$token";
                $message = "<p>Welcome to Petrongolo Evergreen Plantation!</p>
                            <p>Click the button below to verify your account:</p>
                            <a href='$verify_link' style='background:#2c5530;color:#fff;padding:12px 24px;text-decoration:none;border-radius:8px;display:inline-block;margin:10px 0;'>
                            Verify My Account</a>";

                $headers = "From: noreply@petrongoloevergreenplantation.com\r\nContent-type: text/html\r\n";
                mail($email, "Verify Your Account", $message, $headers)
                    ? $success = "Account created! Check your email to verify."
                    : $success = "Account created! (Email failed to send)";
            } else {
                $error = "Signup failed. Try again.";
            }
            $stmt->close();
        }
    }
}

// ---------------------------------------------------------------------
// 3. Customer Login → REDIRECTS CORRECTLY
// ---------------------------------------------------------------------
if ($_POST['action'] ?? '' === 'customer_login') {
    $username = trim($_POST['custUsername'] ?? '');
    $password = $_POST['custPassword'] ?? '';

    $sql = "SELECT customer_id, password_hash, verified FROM Customer WHERE username = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("s", $username);
    $stmt->execute();
    $stmt->bind_result($id, $hash, $verified);
    if ($stmt->fetch() && $verified && password_verify($password, $hash)) {
        $_SESSION['user_id'] = $id;
        $_SESSION['role'] = 'customer';
        $stmt->close();
        $conn->close();
        header("Location: $return_url");
        exit;
    }
    $stmt->close();
    $error = $verified ? "Invalid username or password." : "Please verify your email first.";
}

// ---------------------------------------------------------------------
// 4. Employee Login → REDIRECTS CORRECTLY
// ---------------------------------------------------------------------
if ($_POST['action'] ?? '' === 'employee_login') {
    $email = trim($_POST['empEmail'] ?? '');
    $password = $_POST['empPassword'] ?? '';

    $sql = "SELECT employee_id, password_hash, role FROM Employee WHERE employee_email = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $stmt->bind_result($id, $hash, $role);
    if ($stmt->fetch() && password_verify($password, $hash)) {
        $_SESSION['user_id'] = $id;
        $_SESSION['role'] = $role;
        $_SESSION['email'] = $email;
        $stmt->close();
        $conn->close();
        header("Location: PEP_EmployeePortal.php");
        exit;
    }
    $stmt->close();
    $error = "Invalid employee credentials.";
}

$conn->close();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Login • Petrongolo Evergreen Plantation</title>
    <link rel="icon" type="image/png" href="Tree.png">

    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&family=Oswald:wght@500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">

    <style>
        :root { --green: #2c5530; --green-light: #3a7b40; --bg: #0f1a12; --text: #e0e0e0; }
        * { margin:0; padding:0; box-sizing:border-box; }
        body {
            font-family: 'Inter', sans-serif;
            background: linear-gradient(135deg, #0a1a0d 0%, #0f2b15 50%, #0a1a0d 100%);
            color: var(--text);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 20px;
            background-attachment: fixed;
        }
        .login-container { width:100%; max-width:480px; }
        .card {
            background: rgba(20,35,22,0.65);
            backdrop-filter: blur(16px);
            border-radius: 20px;
            border: 1px solid rgba(60,100,65,0.3);
            overflow: hidden;
            box-shadow: 0 20px 40px rgba(0,0,0,0.4);
            transition: transform .3s;
        }
        .card:hover { transform: translateY(-8px); }
        .header { text-align:center; padding:40px 30px 20px; background:linear-gradient(to bottom,rgba(44,85,48,0.9),transparent); }
        .header img { height:80px; filter:drop-shadow(0 4px 8px rgba(0,0,0,0.5)); margin-bottom:16px; }
        .header h1 { font-family:'Oswald',sans-serif; font-size:2.4rem; color:#c8e6c9; letter-spacing:1px; }
        .header p { opacity:0.9; font-weight:300; }

        .tab-switch { display:flex; margin:30px 30px 0; border-radius:12px; overflow:hidden; border:1px solid rgba(100,140,97,0.3); background:rgba(0,0,0,0.3); }
        .tab-btn { flex:1; padding:14px; background:transparent; border:none; color:#aaa; font-weight:500; cursor:pointer; transition:.3s; }
        .tab-btn.active { background:var(--green); color:white; box-shadow:0 4px 15px rgba(44,85,48,0.4); }
        .tab-btn i { margin-right:8px; }

        .form-container { padding:30px; display:none; }
        .form-container.active { display:block; animation:fadeIn .4s; }

        .form-group { margin-bottom:20px; }
        label { display:block; margin-bottom:8px; color:#c8e6c9; font-weight:500; font-size:0.95rem; }
        input {
            width:100%; padding:14px 16px; border:1px solid rgba(100,140,97,0.4); border-radius:10px;
            background:rgba(15,26,18,0.7); color:white; font-size:1rem; transition:.3s;
        }
        input:focus { outline:none; border-color:var(--green); box-shadow:0 0 0 3px rgba(44,85,48,0.25); background:rgba(20,35,22,0.9); }

        .btn-primary {
            width:100%; padding:14px; background:var(--green); color:white; border:none; border-radius:10px;
            font-size:1.1rem; font-weight:600; cursor:pointer; transition:.3s; margin-top:10px;
        }
        .btn-primary:hover { background:var(--green-light); transform:translateY(-2px); box-shadow:0 8px 25px rgba(44,85,48,0.4); }

        .link { text-align:center; margin-top:20px; }
        .link a { color:#88b389; text-decoration:none; font-weight:500; }
        .link a:hover { color:#a8d5a9; text-decoration:underline; }

        .alert { margin:20px 30px; padding:14px; border-radius:10px; text-align:center; font-size:0.95rem; }
        .alert-success { background:rgba(76,175,80,0.2); border:1px solid rgba(76,175,80,0.5); color:#c8e6c9; }
        .alert-danger { background:rgba(244,67,54,0.2); border:1px solid rgba(244,67,54,0.5); color:#ff9999; }

        .back-btn { display:block; text-align:center; margin:30px; color:#88b389; text-decoration:none; font-weight:500; }

        @keyframes fadeIn { from {opacity:0; transform:translateY(10px);} to {opacity:1; transform:none;} }
        @media (max-width:480px) { .header h1 {font-size:2rem;} }
    </style>
</head>
<body>

<div class="login-container">
    <div class="card">
        <div class="header">
            <img src="Tree.png" alt="Logo">
            <h1>Petrongolo</h1>
            <p>Evergreen Plantation</p>
        </div>

        <?php if ($success): ?>
            <div class="alert alert-success"><?= htmlspecialchars($success) ?></div>
        <?php endif; ?>
        <?php if ($error): ?>
            <div class="alert alert-danger"><?= htmlspecialchars($error) ?></div>
        <?php endif; ?>

        <div class="tab-switch">
            <button class="tab-btn active" onclick="switchTab('customer')"><i class="fas fa-user"></i> Customer</button>
            <button class="tab-btn" onclick="switchTab('employee')"><i class="fas fa-briefcase"></i> Employee</button>
        </div>

        <!-- Customer -->
        <div id="customer" class="form-container active">
            <form action="login.php?return=<?= urlencode($return_url) ?>" method="post" id="custLogin">
                <input type="hidden" name="action" value="customer_login">
                <div class="form-group"><label>Username</label><input type="text" name="custUsername" required placeholder="Your username"></div>
                <div class="form-group"><label>Password</label><input type="password" name="custPassword" required placeholder="••••••••"></div>
                <button type="submit" class="btn-primary">Sign In as Customer</button>
                <div class="link"><a href="#" onclick="showSignup()">No account? Sign up here</a></div>
            </form>

            <form action="login.php?return=<?= urlencode($return_url) ?>" method="post" id="custSignup" style="display:none;">
                <input type="hidden" name="action" value="signup">
                <div class="form-group"><label>First Name</label><input type="text" name="fname" required></div>
                <div class="form-group"><label>Last Name</label><input type="text" name="lname" required></div>
                <div class="form-group"><label>Phone (optional)</label><input type="tel" name="phone"></div>
                <div class="form-group"><label>Email</label><input type="email" name="email" required></div>
                <div class="form-group"><label>Username</label><input type="text" name="username" required></div>
                <div class="form-group"><label>Password</label><input type="password" name="password" required></div>
                <button type="submit" class="btn-primary">Create Account</button>
                <div class="link"><a href="#" onclick="showSignin()">Already have an account? Sign in</a></div>
            </form>
        </div>

        <!-- Employee -->
        <div id="employee" class="form-container">
            <form action="login.php" method="post">
                <input type="hidden" name="action" value="employee_login">
                <div class="form-group"><label>Employee Email</label><input type="email" name="empEmail" required placeholder="name@petrongolo.com"></div>
                <div class="form-group"><label>Password</label><input type="password" name="empPassword" required placeholder="••••••••"></div>
                <button type="submit" class="btn-primary">Sign In as Employee</button>
            </form>
        </div>

        <a href="<?= htmlspecialchars($return_url) ?>" class="back-btn">Back to Site</a>
    </div>
</div>

<script>
function switchTab(tab) {
    document.querySelectorAll('.tab-btn').forEach(b => b.classList.remove('active'));
    document.querySelectorAll('.form-container').forEach(f => f.classList.remove('active'));
    document.querySelector(`button[onclick="switchTab('${tab}')"]`).classList.add('active');
    document.getElementById(tab).classList.add('active');
    if (tab === 'customer') { document.getElementById('custLogin').style.display = 'block'; document.getElementById('custSignup').style.display = 'none'; }
}
function showSignup() { document.getElementById('custLogin').style.display = 'none'; document.getElementById('custSignup').style.display = 'block'; }
function showSignin() { document.getElementById('custLogin').style.display = 'block'; document.getElementById('custSignup').style.display = 'none'; }
window.onload = () => switchTab('customer');
</script>

</body>
</html>