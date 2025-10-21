<?php
// PEP_Admin.php (updated: added logout to sidebar; persist active tab after POST using hidden 'tab' input and dynamic class; added edit functionalities with modals for accounts, tasks, schedules)

error_reporting(E_ALL);
ini_set('display_errors', 1);

session_start();
include 'config.php';

if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
  $_SESSION['error'] = "Only admins have access to the admin portal.";
  header("Location: PEP_Main.php");
  exit;
}

// Determine active tab from POST or default to 'employee-accounts'
$active_tab = $_POST['tab'] ?? 'employee-accounts';

// Handle Add Employee
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['action']) && $_POST['action'] == 'add_employee') {
  $email = trim($_POST['empEmail'] ?? '');
  $password = $_POST['empPassword'] ?? '';
  $role = $_POST['role'] ?? 'employee';
  if (empty($email) || empty($password) || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
    $error = "Valid email and password required.";
  } else {
    $checkStmt = $conn->prepare("SELECT id FROM employeeLogin WHERE email = ?");
    $checkStmt->bind_param("s", $email);
    $checkStmt->execute();
    $checkStmt->store_result();
    if ($checkStmt->num_rows > 0) {
      $error = "Email already exists.";
    } else {
      $password_hash = password_hash($password, PASSWORD_DEFAULT);
      $stmt = $conn->prepare("INSERT INTO employeeLogin (email, password, role) VALUES (?, ?, ?)");
      $stmt->bind_param("sss", $email, $password_hash, $role);
      if ($stmt->execute()) {
        $success = "Employee added successfully.";
      } else {
        $error = "Error adding employee: " . $stmt->error;
      }
      $stmt->close();
    }
    $checkStmt->close();
  }
}

// Handle Update Employee
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['action']) && $_POST['action'] == 'update_employee') {
  $id = $_POST['id'] ?? 0;
  $email = trim($_POST['email'] ?? '');
  $role = $_POST['role'] ?? 'employee';
  $password = $_POST['password'] ?? '';
  if ($id > 0 && !empty($email) && filter_var($email, FILTER_VALIDATE_EMAIL)) {
    if (!empty($password)) {
      $password_hash = password_hash($password, PASSWORD_DEFAULT);
      $stmt = $conn->prepare("UPDATE employeeLogin SET email = ?, password = ?, role = ? WHERE id = ?");
      $stmt->bind_param("sssi", $email, $password_hash, $role, $id);
    } else {
      $stmt = $conn->prepare("UPDATE employeeLogin SET email = ?, role = ? WHERE id = ?");
      $stmt->bind_param("ssi", $email, $role, $id);
    }
    if ($stmt->execute()) {
      $success = "Employee updated successfully.";
    } else {
      $error = "Error updating employee: " . $stmt->error;
    }
    $stmt->close();
  } else {
    $error = "Valid email required.";
  }
}

// Handle Delete Employee
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['action']) && $_POST['action'] == 'delete_employee') {
  $id = $_POST['id'] ?? 0;
  if ($id > 0) {
    $stmt = $conn->prepare("DELETE FROM employeeLogin WHERE id = ? AND role != 'admin'");
    $stmt->bind_param("i", $id);
    if ($stmt->execute()) {
      $success = "Employee deleted successfully.";
    } else {
      $error = "Error deleting employee: " . $stmt->error;
    }
    $stmt->close();
  } else {
    $error = "Invalid employee ID.";
  }
}

// Handle Add Task
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['action']) && $_POST['action'] === 'add_task') {
  $employee_email = $_POST['employee_email'] ?? '';
  $task_description = $_POST['task_description'] ?? '';
  $date = $_POST['date'] ?? '';
  if (!empty($employee_email) && !empty($task_description) && !empty($date)) {
    $stmt = $conn->prepare("INSERT INTO employee_tasks (employee_email, task_description, date) VALUES (?, ?, ?)");
    $stmt->bind_param("sss", $employee_email, $task_description, $date);
    if ($stmt->execute()) {
      $success = "Task added.";
    } else {
      $error = "Error adding task: " . $stmt->error;
    }
    $stmt->close();
  } else {
    $error = "All fields required.";
  }
}

// Handle Update Task
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['action']) && $_POST['action'] == 'update_task') {
  $id = $_POST['id'] ?? 0;
  $employee_email = $_POST['employee_email'] ?? '';
  $task_description = $_POST['task_description'] ?? '';
  $date = $_POST['date'] ?? '';
  if ($id > 0 && !empty($employee_email) && !empty($task_description) && !empty($date)) {
    $stmt = $conn->prepare("UPDATE employee_tasks SET employee_email = ?, task_description = ?, date = ? WHERE id = ?");
    $stmt->bind_param("sssi", $employee_email, $task_description, $date, $id);
    if ($stmt->execute()) {
      $success = "Task updated successfully.";
    } else {
      $error = "Error updating task: " . $stmt->error;
    }
    $stmt->close();
  } else {
    $error = "All fields required.";
  }
}

// Handle Delete Task
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['action']) && $_POST['action'] === 'delete_task') {
  $id = $_POST['id'] ?? 0;
  if ($id > 0) {
    $stmt = $conn->prepare("DELETE FROM employee_tasks WHERE id = ?");
    $stmt->bind_param("i", $id);
    if ($stmt->execute()) {
      $success = "Task deleted.";
    } else {
      $error = "Error deleting task: " . $stmt->error;
    }
    $stmt->close();
  }
}

// Handle Add Schedule
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['action']) && $_POST['action'] === 'add_schedule') {
  $employee_email = $_POST['employee_email'] ?? '';
  $date = $_POST['date'] ?? '';
  $shift_time = $_POST['shift_time'] ?? '';
  $role_tasks = $_POST['role_tasks'] ?? '';
  if (!empty($employee_email) && !empty($date) && !empty($shift_time) && !empty($role_tasks)) {
    $stmt = $conn->prepare("INSERT INTO employee_schedules (employee_email, date, shift_time, role_tasks) VALUES (?, ?, ?, ?)");
    $stmt->bind_param("ssss", $employee_email, $date, $shift_time, $role_tasks);
    if ($stmt->execute()) {
      $success = "Schedule added.";
    } else {
      $error = "Error adding schedule: " . $stmt->error;
    }
    $stmt->close();
  } else {
    $error = "All fields required.";
  }
}

// Handle Update Schedule
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['action']) && $_POST['action'] == 'update_schedule') {
  $id = $_POST['id'] ?? 0;
  $employee_email = $_POST['employee_email'] ?? '';
  $date = $_POST['date'] ?? '';
  $shift_time = $_POST['shift_time'] ?? '';
  $role_tasks = $_POST['role_tasks'] ?? '';
  if ($id > 0 && !empty($employee_email) && !empty($date) && !empty($shift_time) && !empty($role_tasks)) {
    $stmt = $conn->prepare("UPDATE employee_schedules SET employee_email = ?, date = ?, shift_time = ?, role_tasks = ? WHERE id = ?");
    $stmt->bind_param("ssssi", $employee_email, $date, $shift_time, $role_tasks, $id);
    if ($stmt->execute()) {
      $success = "Schedule updated successfully.";
    } else {
      $error = "Error updating schedule: " . $stmt->error;
    }
    $stmt->close();
  } else {
    $error = "All fields required.";
  }
}

// Handle Delete Schedule
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['action']) && $_POST['action'] === 'delete_schedule') {
  $id = $_POST['id'] ?? 0;
  if ($id > 0) {
    $stmt = $conn->prepare("DELETE FROM employee_schedules WHERE id = ?");
    $stmt->bind_param("i", $id);
    if ($stmt->execute()) {
      $success = "Schedule deleted.";
    } else {
      $error = "Error deleting schedule: " . $stmt->error;
    }
    $stmt->close();
  }
}

// Handle Search Customers
$customers_sql = "SELECT id, first_name, last_name, email_address, username, phone_number FROM custLogin";
$search_term = $_GET['search'] ?? '';
if (!empty($search_term)) {
  $customers_sql .= " WHERE email_address LIKE ?";
  $stmt = $conn->prepare($customers_sql);
  $search_param = "%$search_term%";
  $stmt->bind_param("s", $search_param);
  $stmt->execute();
  $customers_result = $stmt->get_result();
  $stmt->close();
} else {
  $customers_result = $conn->query($customers_sql);
}

// Handle Delete Customer
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['action']) && $_POST['action'] === 'delete_customer') {
  $id = $_POST['id'] ?? 0;
  if ($id > 0) {
    $stmt = $conn->prepare("DELETE FROM custLogin WHERE id = ?");
    $stmt->bind_param("i", $id);
    if ($stmt->execute()) {
      $success = "Customer deleted.";
    } else {
      $error = "Error deleting customer: " . $stmt->error;
    }
    $stmt->close();
  }
}

// Handle Email All Customers
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['action']) && $_POST['action'] === 'email_all') {
  $subject = $_POST['subject'] ?? '';
  $message = $_POST['message'] ?? '';
  if (!empty($subject) && !empty($message)) {
    $emails_sql = "SELECT email_address FROM custLogin";
    $emails_result = $conn->query($emails_sql);
    if ($emails_result->num_rows > 0) {
      while ($row = $emails_result->fetch_assoc()) {
        $to = $row['email_address'];
        $headers = "From: info@petrongolo.com\r\n";
        $headers .= "Reply-To: info@petrongolo.com\r\n";
        $headers .= "Content-Type: text/plain; charset=UTF-8\r\n";
        if (mail($to, $subject, $message, $headers)) {
          // Success per email; log if needed
        } else {
          $error = "Error sending to $to.";
          break; // Or continue
        }
      }
      if (!isset($error)) {
        $success = "Emails sent to all customers.";
      }
    } else {
      $error = "No customers found.";
    }
  } else {
    $error = "Subject and message required.";
  }
}

// Handle Add Product
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['action']) && $_POST['action'] == 'add_product') {
  $name = $_POST['name'] ?? '';
  $description = $_POST['description'] ?? '';
  $price = $_POST['price'] ?? 0;
  $stock_number = $_POST['stock_number'] ?? 0;
  $image_path = '';

  if (!empty($name) && !empty($description) && $price > 0) {
    if (isset($_FILES['image']) && $_FILES['image']['error'] == 0) {
      $upload_dir = 'uploads/';
      if (!is_dir($upload_dir)) mkdir($upload_dir, 0755, true);
      $image_path = $upload_dir . basename($_FILES['image']['name']);
      if (move_uploaded_file($_FILES['image']['tmp_name'], $image_path)) {
        // Image uploaded successfully
      } else {
        $error = "Error uploading image.";
      }
    }

    if (!isset($error)) {
      $stmt = $conn->prepare("INSERT INTO products (name, description, price, image, stock_number) VALUES (?, ?, ?, ?, ?)");
      $stmt->bind_param("ssdsi", $name, $description, $price, $image_path, $stock_number);
      if ($stmt->execute()) {
        $success = "Product added successfully.";
      } else {
        $error = "Error adding product: " . $stmt->error;
      }
      $stmt->close();
    }
  } else {
    $error = "All fields required.";
  }
}

// Handle Update Product
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['action']) && $_POST['action'] == 'update_product') {
  $id = $_POST['id'] ?? 0;
  $name = $_POST['name'] ?? '';
  $description = $_POST['description'] ?? '';
  $price = $_POST['price'] ?? 0;
  $stock_number = $_POST['stock_number'] ?? 0;
  $image_path = $_POST['existing_image'] ?? '';

  if ($id > 0 && !empty($name) && !empty($description) && $price > 0) {
    if (isset($_FILES['image']) && $_FILES['image']['error'] == 0) {
      $upload_dir = 'uploads/';
      if (!is_dir($upload_dir)) mkdir($upload_dir, 0755, true);
      $image_path = $upload_dir . basename($_FILES['image']['name']);
      if (move_uploaded_file($_FILES['image']['tmp_name'], $image_path)) {
        // New image uploaded
      } else {
        $error = "Error uploading image.";
      }
    }

    if (!isset($error)) {
      $stmt = $conn->prepare("UPDATE products SET name = ?, description = ?, price = ?, image = ?, stock_number = ? WHERE id = ?");
      $stmt->bind_param("ssdsii", $name, $description, $price, $image_path, $stock_number, $id);
      if ($stmt->execute()) {
        $success = "Product updated successfully.";
      } else {
        $error = "Error updating product: " . $stmt->error;
      }
      $stmt->close();
    }
  } else {
    $error = "All fields required.";
  }
}

// Handle Delete Product
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['action']) && $_POST['action'] == 'delete_product') {
  $id = $_POST['id'] ?? 0;
  if ($id > 0) {
    $stmt = $conn->prepare("DELETE FROM products WHERE id = ?");
    $stmt->bind_param("i", $id);
    if ($stmt->execute()) {
      $success = "Product deleted successfully.";
    } else {
      $error = "Error deleting product: " . $stmt->error;
    }
    $stmt->close();
  } else {
    $error = "Invalid product ID.";
  }
}

// Handle Reply Review
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['action']) && $_POST['action'] == 'reply_review') {
  $id = $_POST['id'] ?? 0;
  $reply = trim($_POST['reply'] ?? '');
  if ($id > 0 && !empty($reply)) {
    $stmt = $conn->prepare("UPDATE review SET reply = ?, reply_date = NOW() WHERE id = ?");
    $stmt->bind_param("si", $reply, $id);
    if ($stmt->execute()) {
      // Get reviewer's email
      $name = '';
      $get_name_stmt = $conn->prepare("SELECT name FROM review WHERE id = ?");
      $get_name_stmt->bind_param("i", $id);
      $get_name_stmt->execute();
      $get_name_stmt->bind_result($name);
      $get_name_stmt->fetch();
      $get_name_stmt->close();

      if ($name) {
        $get_email_stmt = $conn->prepare("SELECT email_address FROM custLogin WHERE username = ?");
        $get_email_stmt->bind_param("s", $name);
        $get_email_stmt->execute();
        $get_email_stmt->bind_result($email);
        $get_email_stmt->fetch();
        $get_email_stmt->close();

        if ($email) {
          $subject = "Your review has received a reply";
          $message = "Hello,\n\nAn admin has replied to your review on Petrongolo Evergreen Plantation. Please visit the reviews page to see it.\n\nBest regards,\nPetrongolo Team";
          $headers = "From: info@petrongolo.com\r\nReply-To: info@petrongolo.com\r\nContent-Type: text/plain; charset=UTF-8";
          mail($email, $subject, $message, $headers);
        }
      }
      $success = "Reply added and notification sent.";
    } else {
      $error = "Error adding reply: " . $stmt->error;
    }
    $stmt->close();
  } else {
    $error = "Reply text is required.";
  }
}

// Handle Delete Review
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['action']) && $_POST['action'] == 'delete_review') {
  $id = $_POST['id'] ?? 0;
  if ($id > 0) {
    // Get reviewer's email before delete
    $name = '';
    $get_name_stmt = $conn->prepare("SELECT name FROM review WHERE id = ?");
    $get_name_stmt->bind_param("i", $id);
    $get_name_stmt->execute();
    $get_name_stmt->bind_result($name);
    $get_name_stmt->fetch();
    $get_name_stmt->close();

    if ($name) {
      $get_email_stmt = $conn->prepare("SELECT email_address FROM custLogin WHERE username = ?");
      $get_email_stmt->bind_param("s", $name);
      $get_email_stmt->execute();
      $get_email_stmt->bind_result($email);
      $get_email_stmt->fetch();
      $get_email_stmt->close();

      if ($email) {
        $subject = "Your review was deleted";
        $message = "Hello,\n\nYour review on Petrongolo Evergreen Plantation was deleted for violating our review terms.\n\nBest regards,\nPetrongolo Team";
        $headers = "From: info@petrongolo.com\r\nReply-To: info@petrongolo.com\r\nContent-Type: text/plain; charset=UTF-8";
        mail($email, $subject, $message, $headers);
      }
    }

    $stmt = $conn->prepare("DELETE FROM review WHERE id = ?");
    $stmt->bind_param("i", $id);
    if ($stmt->execute()) {
      $success = "Review deleted and notification sent.";
    } else {
      $error = "Error deleting review: " . $stmt->error;
    }
    $stmt->close();
  } else {
    $error = "Invalid review ID.";
  }
}

// Fetch Employees
$employees_sql = "SELECT id, email, role FROM employeeLogin";
$employees_result = $conn->query($employees_sql);

// Fetch Tasks
$tasks_result = $conn->query("SELECT * FROM employee_tasks ORDER BY date");

// Fetch Schedules
$schedules_result = $conn->query("SELECT * FROM employee_schedules ORDER BY date");

// Fetch Products
$products_result = $conn->query("SELECT * FROM products");

// Fetch Reviews
$reviews_result = $conn->query("SELECT * FROM review ORDER BY submitted_Date DESC");

$conn->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <title>Admin Portal - Petrongolo Evergreen Plantation</title>
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
    /* Add more styles as needed */
  </style>
</head>
<body>
  <nav class="navbar navbar-expand-lg">
    <div class="container-fluid">
      <a class="navbar-brand" href="#">Admin Portal</a>
      <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav" aria-controls="navbarNav" aria-expanded="false" aria-label="Toggle navigation">
        <span class="navbar-toggler-icon"></span>
      </button>
      <div class="collapse navbar-collapse" id="navbarNav">
        <ul class="navbar-nav ms-auto">
          <!-- Optional top logout if needed, but main one in sidebar -->
        </ul>
      </div>
    </div>
  </nav>
  <div class="sidebar">
    <h4>Admin Menu</h4>
    <ul class="nav flex-column">
      <li class="nav-item">
        <a class="nav-link<?php if ($active_tab == 'employee-accounts') echo ' active'; ?>" data-bs-toggle="tab" href="#employee-accounts">Employee Accounts</a>
      </li>
      <li class="nav-item">
        <a class="nav-link<?php if ($active_tab == 'employee-tasks') echo ' active'; ?>" data-bs-toggle="tab" href="#employee-tasks">Employee Tasks</a>
      </li>
      <li class="nav-item">
        <a class="nav-link<?php if ($active_tab == 'employee-schedules') echo ' active'; ?>" data-bs-toggle="tab" href="#employee-schedules">Employee Schedules</a>
      </li>
      <li class="nav-item">
        <a class="nav-link<?php if ($active_tab == 'customer-accounts') echo ' active'; ?>" data-bs-toggle="tab" href="#customer-accounts">Customer Accounts</a>
      </li>
      <li class="nav-item">
        <a class="nav-link<?php if ($active_tab == 'products') echo ' active'; ?>" data-bs-toggle="tab" href="#products">Products</a>
      </li>
      <li class="nav-item">
        <a class="nav-link<?php if ($active_tab == 'reviews') echo ' active'; ?>" data-bs-toggle="tab" href="#reviews">Reviews</a>
      </li>
      <li class="nav-item">
        <a class="nav-link" href="logout.php"><i class="bi bi-box-arrow-left me-2"></i>Logout</a>
      </li>
    </ul>
  </div>
  <div class="content tab-content">
    <?php if (!empty($error)) echo "<div class='alert alert-danger'>$error</div>"; ?>
    <?php if (!empty($success)) echo "<div class='alert alert-success'>$success</div>"; ?>

    <div class="tab-pane fade<?php if ($active_tab == 'employee-accounts') echo ' show active'; ?>" id="employee-accounts" role="tabpanel" aria-labelledby="employee-accounts-tab">
      <h4 class="mt-3">Manage Employee Accounts</h4>
      <form action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>" method="post">
        <input type="hidden" name="action" value="add_employee">
        <input type="hidden" name="tab" value="employee-accounts">
        <div class="mb-3">
          <label for="empEmail" class="form-label">Employee Email</label>
          <input type="email" class="form-control" id="empEmail" name="empEmail" required>
        </div>
        <div class="mb-3">
          <label for="empPassword" class="form-label">Password</label>
          <input type="password" class="form-control" id="empPassword" name="empPassword" required>
        </div>
        <div class="mb-3">
          <label for="role" class="form-label">Role</label>
          <select class="form-select" id="role" name="role">
            <option value="employee">Employee</option>
            <option value="admin">Admin</option>
          </select>
        </div>
        <button type="submit" class="btn btn-primary"><i class="bi bi-person-plus me-2"></i>Add Employee</button>
      </form>
      <table class="table table-striped table-hover mt-3">
        <thead>
          <tr>
            <th>ID</th>
            <th>Email</th>
            <th>Role</th>
            <th>Actions</th>
          </tr>
        </thead>
        <tbody>
          <?php mysqli_data_seek($employees_result, 0); if ($employees_result->num_rows > 0): ?>
            <?php while ($emp = $employees_result->fetch_assoc()): ?>
              <tr>
                <td><?php echo htmlspecialchars($emp['id']); ?></td>
                <td><?php echo htmlspecialchars($emp['email']); ?></td>
                <td><?php echo htmlspecialchars($emp['role']); ?></td>
                <td>
                  <button class="btn btn-sm btn-primary" data-bs-toggle="modal" data-bs-target="#editEmployeeModal<?php echo $emp['id']; ?>"><i class="bi bi-pencil"></i> Edit</button>
                  <form action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>" method="post" style="display:inline;">
                    <input type="hidden" name="action" value="delete_employee">
                    <input type="hidden" name="tab" value="employee-accounts">
                    <input type="hidden" name="id" value="<?php echo $emp['id']; ?>">
                    <button type="submit" class="btn btn-sm btn-danger" onclick="return confirm('Delete this employee?');"><i class="bi bi-trash"></i> Delete</button>
                  </form>
                </td>
              </tr>
              <!-- Edit Employee Modal -->
              <div class="modal fade" id="editEmployeeModal<?php echo $emp['id']; ?>" tabindex="-1" aria-labelledby="editEmployeeLabel<?php echo $emp['id']; ?>" aria-hidden="true">
                <div class="modal-dialog">
                  <div class="modal-content">
                    <div class="modal-header">
                      <h5 class="modal-title" id="editEmployeeLabel<?php echo $emp['id']; ?>">Edit Employee</h5>
                      <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body">
                      <form action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>" method="post">
                        <input type="hidden" name="action" value="update_employee">
                        <input type="hidden" name="tab" value="employee-accounts">
                        <input type="hidden" name="id" value="<?php echo $emp['id']; ?>">
                        <div class="mb-3">
                          <label for="email<?php echo $emp['id']; ?>" class="form-label">Email</label>
                          <input type="email" class="form-control" id="email<?php echo $emp['id']; ?>" name="email" value="<?php echo htmlspecialchars($emp['email']); ?>" required>
                        </div>
                        <div class="mb-3">
                          <label for="password<?php echo $emp['id']; ?>" class="form-label">New Password (optional)</label>
                          <input type="password" class="form-control" id="password<?php echo $emp['id']; ?>" name="password">
                        </div>
                        <div class="mb-3">
                          <label for="role<?php echo $emp['id']; ?>" class="form-label">Role</label>
                          <select class="form-select" id="role<?php echo $emp['id']; ?>" name="role">
                            <option value="employee"<?php if ($emp['role'] == 'employee') echo ' selected'; ?>>Employee</option>
                            <option value="admin"<?php if ($emp['role'] == 'admin') echo ' selected'; ?>>Admin</option>
                          </select>
                        </div>
                        <button type="submit" class="btn btn-primary">Update Employee</button>
                      </form>
                    </div>
                  </div>
                </div>
              </div>
            <?php endwhile; ?>
          <?php else: ?>
            <tr><td colspan="4" class="text-center">No employees found.</td></tr>
          <?php endif; ?>
        </tbody>
      </table>
    </div>
    <div class="tab-pane fade<?php if ($active_tab == 'employee-tasks') echo ' show active'; ?>" id="employee-tasks" role="tabpanel" aria-labelledby="employee-tasks-tab">
      <h4 class="mt-3">Manage Employee Tasks</h4>
      <form action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>" method="post">
        <input type="hidden" name="action" value="add_task">
        <input type="hidden" name="tab" value="employee-tasks">
        <div class="mb-3">
          <label for="employee_email" class="form-label">Employee Email</label>
          <input type="email" class="form-control" id="employee_email" name="employee_email" required>
        </div>
        <div class="mb-3">
          <label for="task_description" class="form-label">Task Description</label>
          <textarea class="form-control" id="task_description" name="task_description" required></textarea>
        </div>
        <div class="mb-3">
          <label for="date" class="form-label">Date</label>
          <input type="date" class="form-control" id="date" name="date" required>
        </div>
        <button type="submit" class="btn btn-primary"><i class="bi bi-plus-circle me-2"></i>Add Task</button>
      </form>
      <table class="table table-striped table-hover mt-3">
        <thead>
          <tr>
            <th>ID</th>
            <th>Employee Email</th>
            <th>Task Description</th>
            <th>Date</th>
            <th>Actions</th>
          </tr>
        </thead>
        <tbody>
          <?php mysqli_data_seek($tasks_result, 0); if ($tasks_result->num_rows > 0): ?>
            <?php while ($task = $tasks_result->fetch_assoc()): ?>
              <tr>
                <td><?php echo htmlspecialchars($task['id']); ?></td>
                <td><?php echo htmlspecialchars($task['employee_email']); ?></td>
                <td><?php echo htmlspecialchars($task['task_description']); ?></td>
                <td><?php echo htmlspecialchars($task['date']); ?></td>
                <td>
                  <button class="btn btn-sm btn-primary" data-bs-toggle="modal" data-bs-target="#editTaskModal<?php echo $task['id']; ?>"><i class="bi bi-pencil"></i> Edit</button>
                  <form action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>" method="post" style="display:inline;">
                    <input type="hidden" name="action" value="delete_task">
                    <input type="hidden" name="tab" value="employee-tasks">
                    <input type="hidden" name="id" value="<?php echo $task['id']; ?>">
                    <button type="submit" class="btn btn-sm btn-danger" onclick="return confirm('Delete this task?');"><i class="bi bi-trash"></i> Delete</button>
                  </form>
                </td>
              </tr>
              <!-- Edit Task Modal -->
              <div class="modal fade" id="editTaskModal<?php echo $task['id']; ?>" tabindex="-1" aria-labelledby="editTaskLabel<?php echo $task['id']; ?>" aria-hidden="true">
                <div class="modal-dialog">
                  <div class="modal-content">
                    <div class="modal-header">
                      <h5 class="modal-title" id="editTaskLabel<?php echo $task['id']; ?>">Edit Task</h5>
                      <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body">
                      <form action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>" method="post">
                        <input type="hidden" name="action" value="update_task">
                        <input type="hidden" name="tab" value="employee-tasks">
                        <input type="hidden" name="id" value="<?php echo $task['id']; ?>">
                        <div class="mb-3">
                          <label for="employee_email<?php echo $task['id']; ?>" class="form-label">Employee Email</label>
                          <input type="email" class="form-control" id="employee_email<?php echo $task['id']; ?>" name="employee_email" value="<?php echo htmlspecialchars($task['employee_email']); ?>" required>
                        </div>
                        <div class="mb-3">
                          <label for="task_description<?php echo $task['id']; ?>" class="form-label">Task Description</label>
                          <textarea class="form-control" id="task_description<?php echo $task['id']; ?>" name="task_description" required><?php echo htmlspecialchars($task['task_description']); ?></textarea>
                        </div>
                        <div class="mb-3">
                          <label for="date<?php echo $task['id']; ?>" class="form-label">Date</label>
                          <input type="date" class="form-control" id="date<?php echo $task['id']; ?>" name="date" value="<?php echo htmlspecialchars($task['date']); ?>" required>
                        </div>
                        <button type="submit" class="btn btn-primary">Update Task</button>
                      </form>
                    </div>
                  </div>
                </div>
              </div>
            <?php endwhile; ?>
          <?php else: ?>
            <tr><td colspan="5" class="text-center">No tasks found.</td></tr>
          <?php endif; ?>
        </tbody>
      </table>
    </div>
    <div class="tab-pane fade<?php if ($active_tab == 'employee-schedules') echo ' show active'; ?>" id="employee-schedules" role="tabpanel" aria-labelledby="employee-schedules-tab">
      <h4 class="mt-3">Manage Employee Schedules</h4>
      <form action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>" method="post">
        <input type="hidden" name="action" value="add_schedule">
        <input type="hidden" name="tab" value="employee-schedules">
        <div class="mb-3">
          <label for="employee_email" class="form-label">Employee Email</label>
          <input type="email" class="form-control" id="employee_email" name="employee_email" required>
        </div>
        <div class="mb-3">
          <label for="date" class="form-label">Date</label>
          <input type="date" class="form-control" id="date" name="date" required>
        </div>
        <div class="mb-3">
          <label for="shift_time" class="form-label">Shift Time</label>
          <input type="text" class="form-control" id="shift_time" name="shift_time" required>
        </div>
        <div class="mb-3">
          <label for="role_tasks" class="form-label">Role/Tasks</label>
          <textarea class="form-control" id="role_tasks" name="role_tasks" required></textarea>
        </div>
        <button type="submit" class="btn btn-primary"><i class="bi bi-plus-circle me-2"></i>Add Schedule</button>
      </form>
      <table class="table table-striped table-hover mt-3">
        <thead>
          <tr>
            <th>ID</th>
            <th>Employee Email</th>
            <th>Date</th>
            <th>Shift Time</th>
            <th>Role/Tasks</th>
            <th>Actions</th>
          </tr>
        </thead>
        <tbody>
          <?php mysqli_data_seek($schedules_result, 0); if ($schedules_result->num_rows > 0): ?>
            <?php while ($schedule = $schedules_result->fetch_assoc()): ?>
              <tr>
                <td><?php echo htmlspecialchars($schedule['id']); ?></td>
                <td><?php echo htmlspecialchars($schedule['employee_email']); ?></td>
                <td><?php echo htmlspecialchars($schedule['date']); ?></td>
                <td><?php echo htmlspecialchars($schedule['shift_time']); ?></td>
                <td><?php echo htmlspecialchars($schedule['role_tasks']); ?></td>
                <td>
                  <button class="btn btn-sm btn-primary" data-bs-toggle="modal" data-bs-target="#editScheduleModal<?php echo $schedule['id']; ?>"><i class="bi bi-pencil"></i> Edit</button>
                  <form action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>" method="post" style="display:inline;">
                    <input type="hidden" name="action" value="delete_schedule">
                    <input type="hidden" name="tab" value="employee-schedules">
                    <input type="hidden" name="id" value="<?php echo $schedule['id']; ?>">
                    <button type="submit" class="btn btn-sm btn-danger" onclick="return confirm('Delete this schedule?');"><i class="bi bi-trash"></i> Delete</button>
                  </form>
                </td>
              </tr>
              <!-- Edit Schedule Modal -->
              <div class="modal fade" id="editScheduleModal<?php echo $schedule['id']; ?>" tabindex="-1" aria-labelledby="editScheduleLabel<?php echo $schedule['id']; ?>" aria-hidden="true">
                <div class="modal-dialog">
                  <div class="modal-content">
                    <div class="modal-header">
                      <h5 class="modal-title" id="editScheduleLabel<?php echo $schedule['id']; ?>">Edit Schedule</h5>
                      <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body">
                      <form action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>" method="post">
                        <input type="hidden" name="action" value="update_schedule">
                        <input type="hidden" name="tab" value="employee-schedules">
                        <input type="hidden" name="id" value="<?php echo $schedule['id']; ?>">
                        <div class="mb-3">
                          <label for="employee_email<?php echo $schedule['id']; ?>" class="form-label">Employee Email</label>
                          <input type="email" class="form-control" id="employee_email<?php echo $schedule['id']; ?>" name="employee_email" value="<?php echo htmlspecialchars($schedule['employee_email']); ?>" required>
                        </div>
                        <div class="mb-3">
                          <label for="date<?php echo $schedule['id']; ?>" class="form-label">Date</label>
                          <input type="date" class="form-control" id="date<?php echo $schedule['id']; ?>" name="date" value="<?php echo htmlspecialchars($schedule['date']); ?>" required>
                        </div>
                        <div class="mb-3">
                          <label for="shift_time<?php echo $schedule['id']; ?>" class="form-label">Shift Time</label>
                          <input type="text" class="form-control" id="shift_time<?php echo $schedule['id']; ?>" name="shift_time" value="<?php echo htmlspecialchars($schedule['shift_time']); ?>" required>
                        </div>
                        <div class="mb-3">
                          <label for="role_tasks<?php echo $schedule['id']; ?>" class="form-label">Role/Tasks</label>
                          <textarea class="form-control" id="role_tasks<?php echo $schedule['id']; ?>" name="role_tasks" required><?php echo htmlspecialchars($schedule['role_tasks']); ?></textarea>
                        </div>
                        <button type="submit" class="btn btn-primary">Update Schedule</button>
                      </form>
                    </div>
                  </div>
                </div>
              </div>
            <?php endwhile; ?>
          <?php else: ?>
            <tr><td colspan="6" class="text-center">No schedules found.</td></tr>
          <?php endif; ?>
        </tbody>
      </table>
    </div>
    <div class="tab-pane fade<?php if ($active_tab == 'customer-accounts') echo ' show active'; ?>" id="customer-accounts" role="tabpanel" aria-labelledby="customer-accounts-tab">
      <h4 class="mt-3">Manage Customer Accounts</h4>
      <form action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>" method="get" class="mb-3">
        <input type="hidden" name="tab" value="customer-accounts">
        <div class="input-group">
          <input type="text" class="form-control" name="search" placeholder="Search by email" value="<?php echo htmlspecialchars($search_term); ?>">
          <button type="submit" class="btn btn-primary"><i class="bi bi-search me-2"></i>Search</button>
        </div>
      </form>
      <form action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>" method="post" class="mb-3">
        <input type="hidden" name="action" value="email_all">
        <input type="hidden" name="tab" value="customer-accounts">
        <div class="mb-3">
          <label for="subject" class="form-label">Email Subject</label>
          <input type="text" class="form-control" name="subject" placeholder="Subject" required>
        </div>
        <div class="mb-3">
          <label for="message" class="form-label">Email Message</label>
          <textarea class="form-control" name="message" rows="3" placeholder="Message" required></textarea>
        </div>
        <button type="submit" class="btn btn-primary"><i class="bi bi-envelope me-2"></i>Email All</button>
      </form>
      <table class="table table-striped table-hover">
        <thead>
          <tr>
            <th>ID</th>
            <th>First Name</th>
            <th>Last Name</th>
            <th>Email</th>
            <th>Username</th>
            <th>Phone Number</th>
            <th>Actions</th>
          </tr>
        </thead>
        <tbody>
          <?php if ($customers_result->num_rows > 0): ?>
            <?php while ($cust = $customers_result->fetch_assoc()): ?>
              <tr>
                <td><?php echo htmlspecialchars($cust['id']); ?></td>
                <td><?php echo htmlspecialchars($cust['first_name']); ?></td>
                <td><?php echo htmlspecialchars($cust['last_name']); ?></td>
                <td><?php echo htmlspecialchars($cust['email_address']); ?></td>
                <td><?php echo htmlspecialchars($cust['username']); ?></td>
                <td><?php echo htmlspecialchars($cust['phone_number']); ?></td>
                <td>
                  <form action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>" method="post" style="display:inline;">
                    <input type="hidden" name="action" value="delete_customer">
                    <input type="hidden" name="tab" value="customer-accounts">
                    <input type="hidden" name="id" value="<?php echo $cust['id']; ?>">
                    <button type="submit" class="btn btn-sm btn-danger" onclick="return confirm('Delete this customer?');"><i class="bi bi-trash"></i> Delete</button>
                  </form>
                </td>
              </tr>
            <?php endwhile; ?>
          <?php else: ?>
            <tr><td colspan="7" class="text-center">No customers found.</td></tr>
          <?php endif; ?>
        </tbody>
      </table>
    </div>
    <div class="tab-pane fade<?php if ($active_tab == 'products') echo ' show active'; ?>" id="products" role="tabpanel" aria-labelledby="products-tab">
      <h4 class="mt-3">Edit Products</h4>
      <form action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>" method="post" enctype="multipart/form-data">
        <input type="hidden" name="action" value="add_product">
        <input type="hidden" name="tab" value="products">
        <div class="mb-3">
          <label for="name" class="form-label">Product Name</label>
          <input type="text" class="form-control" id="name" name="name" required>
        </div>
        <div class="mb-3">
          <label for="description" class="form-label">Description</label>
          <textarea class="form-control" id="description" name="description" required></textarea>
        </div>
        <div class="mb-3">
          <label for="price" class="form-label">Price</label>
          <input type="number" step="0.01" class="form-control" id="price" name="price" required>
        </div>
        <div class="mb-3">
          <label for="stock_number" class="form-label">Stock Number</label>
          <input type="number" class="form-control" id="stock_number" name="stock_number" required>
        </div>
        <div class="mb-3">
          <label for="image" class="form-label">Image</label>
          <input type="file" class="form-control" id="image" name="image" accept="image/*" required>
        </div>
        <button type="submit" class="btn btn-primary"><i class="bi bi-plus-circle me-2"></i>Add Product</button>
      </form>
      <table class="table table-striped table-hover mt-3">
        <thead>
          <tr>
            <th>ID</th>
            <th>Name</th>
            <th>Description</th>
            <th>Price</th>
            <th>Stock</th>
            <th>Image</th>
            <th>Actions</th>
          </tr>
        </thead>
        <tbody>
          <?php mysqli_data_seek($products_result, 0); if ($products_result->num_rows > 0): ?>
            <?php while ($product = $products_result->fetch_assoc()): ?>
              <tr>
                <td><?php echo htmlspecialchars($product['id']); ?></td>
                <td><?php echo htmlspecialchars($product['name']); ?></td>
                <td><?php echo htmlspecialchars($product['description']); ?></td>
                <td>$<?php echo number_format($product['price'], 2); ?></td>
                <td><?php echo htmlspecialchars($product['stock_number']); ?></td>
                <td><img src="<?php echo htmlspecialchars($product['image']); ?>" alt="Product Image" style="width: 50px; height: auto;"></td>
                <td>
                  <button class="btn btn-sm btn-primary" data-bs-toggle="modal" data-bs-target="#editProductModal<?php echo $product['id']; ?>"><i class="bi bi-pencil"></i> Edit</button>
                  <form action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>" method="post" style="display:inline;">
                    <input type="hidden" name="action" value="delete_product">
                    <input type="hidden" name="tab" value="products">
                    <input type="hidden" name="id" value="<?php echo $product['id']; ?>">
                    <button type="submit" class="btn btn-sm btn-danger" onclick="return confirm('Delete this product?');"><i class="bi bi-trash"></i> Delete</button>
                  </form>
                </td>
              </tr>
              <!-- Edit Product Modal -->
              <div class="modal fade" id="editProductModal<?php echo $product['id']; ?>" tabindex="-1" aria-labelledby="editProductLabel<?php echo $product['id']; ?>" aria-hidden="true">
                <div class="modal-dialog">
                  <div class="modal-content">
                    <div class="modal-header">
                      <h5 class="modal-title" id="editProductLabel<?php echo $product['id']; ?>">Edit Product</h5>
                      <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body">
                      <form action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>" method="post" enctype="multipart/form-data">
                        <input type="hidden" name="action" value="update_product">
                        <input type="hidden" name="tab" value="products">
                        <input type="hidden" name="id" value="<?php echo $product['id']; ?>">
                        <input type="hidden" name="existing_image" value="<?php echo htmlspecialchars($product['image']); ?>">
                        <div class="mb-3">
                          <label for="name<?php echo $product['id']; ?>" class="form-label">Name</label>
                          <input type="text" class="form-control" id="name<?php echo $product['id']; ?>" name="name" value="<?php echo htmlspecialchars($product['name']); ?>" required>
                        </div>
                        <div class="mb-3">
                          <label for="description<?php echo $product['id']; ?>" class="form-label">Description</label>
                          <textarea class="form-control" id="description<?php echo $product['id']; ?>" name="description" required><?php echo htmlspecialchars($product['description']); ?></textarea>
                        </div>
                        <div class="mb-3">
                          <label for="price<?php echo $product['id']; ?>" class="form-label">Price</label>
                          <input type="number" step="0.01" class="form-control" id="price<?php echo $product['id']; ?>" name="price" value="<?php echo $product['price']; ?>" required>
                        </div>
                        <div class="mb-3">
                          <label for="stock_number<?php echo $product['id']; ?>" class="form-label">Stock Number</label>
                          <input type="number" class="form-control" id="stock_number<?php echo $product['id']; ?>" name="stock_number" value="<?php echo $product['stock_number']; ?>" required>
                        </div>
                        <div class="mb-3">
                          <label for="image<?php echo $product['id']; ?>" class="form-label">New Image (optional)</label>
                          <input type="file" class="form-control" id="image<?php echo $product['id']; ?>" name="image" accept="image/*">
                        </div>
                        <button type="submit" class="btn btn-primary">Update Product</button>
                      </form>
                    </div>
                  </div>
                </div>
              </div>
            <?php endwhile; ?>
          <?php else: ?>
            <tr><td colspan="7" class="text-center">No products found.</td></tr>
          <?php endif; ?>
        </tbody>
      </table>
    </div>
    <div class="tab-pane fade<?php if ($active_tab == 'reviews') echo ' show active'; ?>" id="reviews" role="tabpanel" aria-labelledby="reviews-tab">
      <h4 class="mt-3">Review Monitoring</h4>
      <table class="table table-striped table-hover">
        <thead>
          <tr>
            <th>ID</th>
            <th>Name</th>
            <th>Rating</th>
            <th>Review</th>
            <th>Date</th>
            <th>Reply</th>
            <th>Actions</th>
          </tr>
        </thead>
        <tbody>
          <?php mysqli_data_seek($reviews_result, 0); if ($reviews_result->num_rows > 0): ?>
            <?php while ($row = $reviews_result->fetch_assoc()): ?>
              <tr>
                <td><?php echo htmlspecialchars($row['id']); ?></td>
                <td><?php echo htmlspecialchars($row['name']); ?></td>
                <td><?php echo htmlspecialchars($row['rating']); ?></td>
                <td><?php echo htmlspecialchars($row['review']); ?></td>
                <td><?php echo htmlspecialchars($row['submitted_Date']); ?></td>
                <td><?php echo htmlspecialchars($row['reply'] ?? 'No reply yet'); ?></td>
                <td>
                  <form action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>" method="post" class="mb-2">
                    <input type="hidden" name="action" value="reply_review">
                    <input type="hidden" name="tab" value="reviews">
                    <input type="hidden" name="id" value="<?php echo $row['id']; ?>">
                    <div class="mb-1">
                      <textarea class="form-control" name="reply" rows="2" placeholder="Enter reply"><?php echo htmlspecialchars($row['reply'] ?? ''); ?></textarea>
                    </div>
                    <button type="submit" class="btn btn-sm btn-primary">Submit Reply</button>
                  </form>
                  <form action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>" method="post" style="display:inline;">
                    <input type="hidden" name="action" value="delete_review">
                    <input type="hidden" name="tab" value="reviews">
                    <input type="hidden" name="id" value="<?php echo $row['id']; ?>">
                    <button type="submit" class="btn btn-sm btn-danger" onclick="return confirm('Delete this review?');"><i class="bi bi-trash"></i> Delete</button>
                  </form>
                </td>
              </tr>
            <?php endwhile; ?>
          <?php else: ?>
            <tr><td colspan="7" class="text-center">No reviews found.</td></tr>
          <?php endif; ?>
        </tbody>
      </table>
    </div>
  </div>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js" integrity="sha384-YvpcrYf0tY3lHB60NNkmXc5s9fDVZLESaAA55NDzOxhy9GkcIdslK1eN7N6jIeHz" crossorigin="anonymous"></script>
</body>
</html>