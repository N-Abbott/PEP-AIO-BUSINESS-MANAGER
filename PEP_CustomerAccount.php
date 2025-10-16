<?php
session_start();
include 'config.php'; // DB connection

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'customer') {
  header("Location: PEP_Main.php");
  exit;
}

$user_id = $_SESSION['user_id'];
$success = $error = '';

// Fetch current user data
$sql = "SELECT fname, lname, phone, email, username FROM users WHERE id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $user_id);
$stmt->execute();
$stmt->bind_result($fname, $lname, $phone, $email, $username);
$stmt->fetch();
$stmt->close();

// Handle Profile Update
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['action']) && $_POST['action'] == 'update_profile') {
  $new_fname = $_POST['fname'];
  $new_lname = $_POST['lname'];
  $new_phone = $_POST['phone'];
  $new_email = $_POST['email'];
  $new_username = $_POST['username'];

  $sql = "UPDATE users SET fname = ?, lname = ?, phone = ?, email = ?, username = ? WHERE id = ?";
  $stmt = $conn->prepare($sql);
  $stmt->bind_param("sssssi", $new_fname, $new_lname, $new_phone, $new_email, $new_username, $user_id);

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
    $sql = "SELECT password_hash FROM users WHERE id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $user_id);
    $stmt->execute();
    $stmt->bind_result($password_hash);
    $stmt->fetch();
    $stmt->close();

    if (password_verify($old_password, $password_hash)) {
      $new_hash = password_hash($new_password, PASSWORD_DEFAULT);
      $sql = "UPDATE users SET password_hash = ? WHERE id = ?";
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
  $sql = "DELETE FROM users WHERE id = ?";
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
        <label for="fname">First Name</label>
        <input type="text" class="form-control" id="fname" name="fname" value="<?php echo htmlspecialchars($fname); ?>">
      </div>
      <div class="mb-3">
        <label for="lname">Last Name</label>
        <input type="text" class="form-control" id="lname" name="lname" value="<?php echo htmlspecialchars($lname); ?>">
      </div>
      <div class="mb-3">
        <label for="phone">Phone Number</label>
        <input type="tel" class="form-control" id="phone" name="phone" value="<?php echo htmlspecialchars($phone); ?>">
      </div>
      <div class="mb-3">
        <label for="email">Email</label>
        <input type="email" class="form-control" id="email" name="email" value="<?php echo htmlspecialchars($email); ?>">
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

  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>