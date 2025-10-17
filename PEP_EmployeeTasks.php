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
      header("Location: PEP_EmployeeTasks.php");
      exit;
    }
  } else {
    $error = "Admin password not set: " . $conn->error;
  }
}

// Fetch Tasks (only own for all users)
$email = $_SESSION['email'] ?? '';
$stmt = $conn->prepare("SELECT * FROM employee_tasks WHERE employee_email = ? ORDER BY date");
$stmt->bind_param("s", $email);
$stmt->execute();
$tasks_result = $stmt->get_result();
$stmt->close();

$conn->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <title>Employee Portal - Tasks - Petrongolo Evergreen Plantation</title>
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
        <a class="nav-link active" href="PEP_EmployeeTasks.php"><i class="bi bi-check2-square me-2"></i>View Tasks</a>
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
    <h2 class="section-title">Your Tasks</h2>
    <?php if ($tasks_result->num_rows > 0): ?>
      <table class="table table-striped table-hover">
        <thead>
          <tr>
            <th>Task</th>
            <th>Due Date</th>
          </tr>
        </thead>
        <tbody>
          <?php while ($row = $tasks_result->fetch_assoc()): ?>
            <tr>
              <td><?php echo htmlspecialchars($row['task_description']); ?></td>
              <td><?php echo htmlspecialchars($row['date']); ?></td>
            </tr>
          <?php endwhile; ?>
        </tbody>
      </table>
    <?php else: ?>
      <p class="text-center">No tasks assigned yet.</p>
    <?php endif; ?>
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