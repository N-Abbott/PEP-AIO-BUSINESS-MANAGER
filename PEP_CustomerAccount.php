<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

session_start();
include 'config.php'; // DB connection

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'customer') {
  header("Location: PEP_Main.php");
  exit;
}

$user_id = $_SESSION['user_id'];
$success = $error = '';

// Fetch current user data
$sql = "SELECT first_name, last_name, phone_number, email_address, username FROM custLogin WHERE id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $user_id);
$stmt->execute();
$stmt->bind_result($first_name, $last_name, $phone_number, $email_address, $username);
$stmt->fetch();
$stmt->close();

// Handle Profile Update
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['action']) && $_POST['action'] == 'update_profile') {
  $new_first_name = $_POST['first_name'];
  $new_last_name = $_POST['last_name'];
  $new_phone_number = $_POST['phone_number'];
  $new_email_address = $_POST['email_address'];
  $new_username = $_POST['username'];

  $sql = "UPDATE custLogin SET first_name = ?, last_name = ?, phone_number = ?, email_address = ?, username = ? WHERE id = ?";
  $stmt = $conn->prepare($sql);
  $stmt->bind_param("sssssi", $new_first_name, $new_last_name, $new_phone_number, $new_email_address, $new_username, $user_id);

  if ($stmt->execute()) {
    $success = "Profile updated successfully!";
  } else {
    $error = "Error updating profile.";
  }
  $stmt->close();
}

// Handle Password Change
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['action']) && $_POST['action'] == 'change_password') {
  $old_password = $_POST['old_password'];
  $new_password = $_POST['new_password'];
  $confirm_password = $_POST['confirm_password'];

  if ($new_password !== $confirm_password) {
    $error = "New passwords do not match.";
  } else {
    // Verify old password
    $sql = "SELECT password_hash FROM custLogin WHERE id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $user_id);
    $stmt->execute();
    $stmt->bind_result($password_hash);
    $stmt->fetch();
    $stmt->close();

    if (password_verify($old_password, $password_hash)) {
      $new_hash = password_hash($new_password, PASSWORD_DEFAULT);
      $sql = "UPDATE custLogin SET password_hash = ? WHERE id = ?";
      $stmt = $conn->prepare($sql);
      $stmt->bind_param("si", $new_hash, $user_id);
      if ($stmt->execute()) {
        $success = "Password changed successfully!";
      } else {
        $error = "Error changing password.";
      }
      $stmt->close();
    } else {
      $error = "Incorrect old password.";
    }
  }
}

// Handle Logout
if (isset($_POST['action']) && $_POST['action'] == 'logout') {
  session_destroy();
  header("Location: PEP_Main.php");
  exit;
}

// Handle Delete Account
if (isset($_POST['action']) && $_POST['action'] == 'delete_account') {
  $sql = "DELETE FROM custLogin WHERE id = ?";
  $stmt = $conn->prepare($sql);
  $stmt->bind_param("i", $user_id);
  if ($stmt->execute()) {
    session_destroy();
    header("Location: PEP_Main.php");
    exit;
  } else {
    $error = "Error deleting account.";
  }
  $stmt->close();
}

$conn->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <title>My Account - Petrongolo Evergreen Plantation</title>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
  <style>
    /* Reuse styles from PEP_Main.php for consistency */
    body { font-family: 'Roboto', sans-serif; background-color: #f8f9fa; color: #333; }
    .section-title { text-align: center; margin-bottom: 40px; color: #2c5530; font-family: 'Oswald', sans-serif; font-weight: 700; }
    .btn-danger { background-color: #dc3545; } /* Red delete button */
  </style>
</head>
<body>
  <div class="container mt-5">
    <a href="PEP_Main.php" class="btn btn-secondary mb-4">Return to Home</a>
    <h2 class="section-title">My Account</h2>

    <?php if ($success) echo "<div class='alert alert-success'>$success</div>"; ?>
    <?php if ($error) echo "<div class='alert alert-danger'>$error</div>"; ?>

    <!-- Profile Edit Form -->
    <form action="PEP_CustomerAccount.php" method="post">
      <input type="hidden" name="action" value="update_profile">
      <div class="mb-3">
        <label for="first_name">First Name</label>
        <input type="text" class="form-control" id="first_name" name="first_name" value="<?php echo htmlspecialchars($first_name); ?>">
      </div>
      <div class="mb-3">
        <label for="last_name">Last Name</label>
        <input type="text" class="form-control" id="last_name" name="last_name" value="<?php echo htmlspecialchars($last_name); ?>">
      </div>
      <div class="mb-3">
        <label for="phone_number">Phone Number</label>
        <input type="tel" class="form-control" id="phone_number" name="phone_number" value="<?php echo htmlspecialchars($phone_number); ?>">
      </div>
      <div class="mb-3">
        <label for="email_address">Email Address</label>
        <input type="email" class="form-control" id="email_address" name="email_address" value="<?php echo htmlspecialchars($email_address); ?>">
      </div>
      <div class="mb-3">
        <label for="username">Username</label>
        <input type="text" class="form-control" id="username" name="username" value="<?php echo htmlspecialchars($username); ?>">
      </div>
      <button type="submit" class="btn btn-primary">Update Profile</button>
    </form>

    <hr>

    <!-- Password Change Form -->
    <form action="PEP_CustomerAccount.php" method="post">
      <input type="hidden" name="action" value="change_password">
      <div class="mb-3">
        <label for="old_password">Old Password</label>
        <input type="password" class="form-control" id="old_password" name="old_password" required>
      </div>
      <div class="mb-3">
        <label for="new_password">New Password</label>
        <input type="password" class="form-control" id="new_password" name="new_password" required>
      </div>
      <div class="mb-3">
        <label for="confirm_password">Confirm New Password</label>
        <input type="password" class="form-control" id="confirm_password" name="confirm_password" required>
      </div>
      <button type="submit" class="btn btn-primary">Change Password</button>
    </form>

    <hr>

    <!-- Logout and Delete -->
    <form action="PEP_CustomerAccount.php" method="post" class="d-inline">
      <input type="hidden" name="action" value="logout">
      <button type="submit" class="btn btn-secondary">Logout</button>
    </form>
    <form action="PEP_CustomerAccount.php" method="post" class="d-inline" onsubmit="return confirm('Are you sure you want to delete your account? This action cannot be undone.');">
      <input type="hidden" name="action" value="delete_account">
      <button type="submit" class="btn btn-danger ms-2">Delete Account</button>
    </form>
  </div>
  <br>
  <br>

  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>