<?php
// PEP_Admin.php – FULLY FUNCTIONAL + AGGREGATED SALES REPORT + EXACT PDF MATCH + ERROR-FREE
error_reporting(E_ALL);
ini_set('display_errors', 1);
session_start();
include 'config.php';

// === FPDF INCLUDE ===
require('fpdf/fpdf.php');

if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
  $_SESSION['error'] = "Only admins have access to the admin portal.";
  header("Location: PEP_Main.php");
  exit;
}

// Initialize messages
$success = $error = '';
$report_html = '';
$report_data = [];

// Determine active tab
$active_tab = $_POST['tab'] ?? 'employee-accounts';

// === EMPLOYEE CRUD ===
if (($_POST['action'] ?? '') === 'add_employee') {
  $email = trim($_POST['empEmail'] ?? '');
  $password = $_POST['empPassword'] ?? '';
  $role = $_POST['role'] ?? 'employee';
  if (!$email || !$password || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
    $error = "Valid email and password required.";
  } else {
    $stmt = $conn->prepare("SELECT employee_id FROM Employee WHERE employee_email = ?");
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $stmt->store_result();
    if ($stmt->num_rows > 0) {
      $error = "Email already exists.";
    } else {
      $hash = password_hash($password, PASSWORD_DEFAULT);
      $stmt = $conn->prepare("INSERT INTO Employee (employee_email, password_hash, role) VALUES (?, ?, ?)");
      $stmt->bind_param("sss", $email, $hash, $role);
      $success = $stmt->execute() ? "Employee added." : "Error: " . $stmt->error;
    }
    $stmt->close();
  }
}
if (($_POST['action'] ?? '') === 'update_employee') {
  $id = $_POST['id'] ?? 0;
  $email = trim($_POST['email'] ?? '');
  $role = $_POST['role'] ?? 'employee';
  $password = $_POST['password'] ?? '';
  if ($id && $email && filter_var($email, FILTER_VALIDATE_EMAIL)) {
    if ($password) {
      $hash = password_hash($password, PASSWORD_DEFAULT);
      $stmt = $conn->prepare("UPDATE Employee SET employee_email = ?, password_hash = ?, role = ? WHERE employee_id = ?");
      $stmt->bind_param("sssi", $email, $hash, $role, $id);
    } else {
      $stmt = $conn->prepare("UPDATE Employee SET employee_email = ?, role = ? WHERE employee_id = ?");
      $stmt->bind_param("ssi", $email, $role, $id);
    }
    $success = $stmt->execute() ? "Employee updated." : "Error: " . $stmt->error;
    $stmt->close();
  } else {
    $error = "Invalid data.";
  }
}
if (($_POST['action'] ?? '') === 'delete_employee') {
  $id = $_POST['id'] ?? 0;
  if ($id) {
    $stmt = $conn->prepare("DELETE FROM Employee WHERE employee_id = ? AND role != 'admin'");
    $stmt->bind_param("i", $id);
    $success = $stmt->execute() ? "Employee deleted." : "Error: " . $stmt->error;
    $stmt->close();
  }
}
// === TASKS ===
if (($_POST['action'] ?? '') === 'add_task') {
  $emp_id = $_POST['employee_id'] ?? 0;
  $desc = $_POST['task_description'] ?? '';
  $date = $_POST['date'] ?? '';
  if ($emp_id && $desc && $date) {
    $stmt = $conn->prepare("INSERT INTO EmployeeTasks (employee_id, task_description, task_date) VALUES (?, ?, ?)");
    $stmt->bind_param("iss", $emp_id, $desc, $date);
    $success = $stmt->execute() ? "Task added." : "Error: " . $stmt->error;
    $stmt->close();
  } else $error = "All fields required.";
}
if (($_POST['action'] ?? '') === 'update_task') {
  $id = $_POST['id'] ?? 0;
  $emp_id = $_POST['employee_id'] ?? 0;
  $desc = $_POST['task_description'] ?? '';
  $date = $_POST['date'] ?? '';
  if ($id && $emp_id && $desc && $date) {
    $stmt = $conn->prepare("UPDATE EmployeeTasks SET employee_id = ?, task_description = ?, task_date = ? WHERE task_id = ?");
    $stmt->bind_param("issi", $emp_id, $desc, $date, $id);
    $success = $stmt->execute() ? "Task updated." : "Error: " . $stmt->error;
    $stmt->close();
  } else $error = "All fields required.";
}
if (($_POST['action'] ?? '') === 'delete_task') {
  $id = $_POST['id'] ?? 0;
  if ($id) {
    $stmt = $conn->prepare("DELETE FROM EmployeeTasks WHERE task_id = ?");
    $stmt->bind_param("i", $id);
    $success = $stmt->execute() ? "Task deleted." : "Error: " . $stmt->error;
    $stmt->close();
  }
}
// === SCHEDULES ===
if (($_POST['action'] ?? '') === 'add_schedule') {
  $emp_id = $_POST['employee_id'] ?? 0;
  $date = $_POST['date'] ?? '';
  $time = $_POST['shift_time'] ?? '';
  $tasks = $_POST['role_tasks'] ?? '';
  if ($emp_id && $date && $time && $tasks) {
    $stmt = $conn->prepare("INSERT INTO EmployeeSchedules (employee_id, shift_date, shift_time, role_tasks) VALUES (?, ?, ?, ?)");
    $stmt->bind_param("isss", $emp_id, $date, $time, $tasks);
    $success = $stmt->execute() ? "Schedule added." : "Error: " . $stmt->error;
    $stmt->close();
  } else $error = "All fields required.";
}
if (($_POST['action'] ?? '') === 'update_schedule') {
  $id = $_POST['id'] ?? 0;
  $emp_id = $_POST['employee_id'] ?? 0;
  $date = $_POST['date'] ?? '';
  $time = $_POST['shift_time'] ?? '';
  $tasks = $_POST['role_tasks'] ?? '';
  if ($id && $emp_id && $date && $time && $tasks) {
    $stmt = $conn->prepare("UPDATE EmployeeSchedules SET employee_id = ?, shift_date = ?, shift_time = ?, role_tasks = ? WHERE schedule_id = ?");
    $stmt->bind_param("isssi", $emp_id, $date, $time, $tasks, $id);
    $success = $stmt->execute() ? "Schedule updated." : "Error: " . $stmt->error;
    $stmt->close();
  } else $error = "All fields required.";
}
if (($_POST['action'] ?? '') === 'delete_schedule') {
  $id = $_POST['id'] ?? 0;
  if ($id) {
    $stmt = $conn->prepare("DELETE FROM EmployeeSchedules WHERE schedule_id = ?");
    $stmt->bind_param("i", $id);
    $success = $stmt->execute() ? "Schedule deleted." : "Error: " . $stmt->error;
    $stmt->close();
  }
}
// === PRODUCTS ===
if (($_POST['action'] ?? '') === 'add_product') {
  $name = $_POST['name'] ?? '';
  $desc = $_POST['description'] ?? '';
  $price = $_POST['price'] ?? 0;
  $stock = $_POST['stock_quantity'] ?? 0;
  $img = '';
  if (!empty($_FILES['image']['name'])) {
    $dir = "uploads/";
    $file = $dir . basename($_FILES["image"]["name"]);
    if (move_uploaded_file($_FILES["image"]["tmp_name"], $file)) $img = $file;
    else $error = "Image upload failed.";
  }
  if ($name && $desc && $price > 0 && $stock >= 0) {
    $stmt = $conn->prepare("INSERT INTO Product (name, description, price, image_path, stock_quantity) VALUES (?, ?, ?, ?, ?)");
    $stmt->bind_param("ssdsi", $name, $desc, $price, $img, $stock);
    $success = $stmt->execute() ? "Product added." : "Error: " . $stmt->error;
    $stmt->close();
  } else $error = "All fields required.";
}
if (($_POST['action'] ?? '') === 'update_product') {
  $id = $_POST['id'] ?? 0;
  $name = $_POST['name'] ?? '';
  $desc = $_POST['description'] ?? '';
  $price = $_POST['price'] ?? 0;
  $stock = $_POST['stock_quantity'] ?? 0;
  $img = $_POST['existing_image'] ?? '';
  if (!empty($_FILES['image']['name'])) {
    $dir = "uploads/";
    $file = $dir . basename($_FILES["image"]["name"]);
    if (move_uploaded_file($_FILES["image"]["tmp_name"], $file)) $img = $file;
  }
  if ($id && $name && $desc && $price > 0 && $stock >= 0) {
    $stmt = $conn->prepare("UPDATE Product SET name = ?, description = ?, price = ?, image_path = ?, stock_quantity = ? WHERE product_id = ?");
    $stmt->bind_param("ssdsii", $name, $desc, $price, $img, $stock, $id);
    $success = $stmt->execute() ? "Product updated." : "Error: " . $stmt->error;
    $stmt->close();
  } else $error = "All fields required.";
}
if (($_POST['action'] ?? '') === 'delete_product') {
  $id = $_POST['id'] ?? 0;
  if ($id) {
    $stmt = $conn->prepare("DELETE FROM Product WHERE product_id = ?");
    $stmt->bind_param("i", $id);
    $success = $stmt->execute() ? "Product deleted." : "Error: " . $stmt->error;
    $stmt->close();
  }
}
// === REVIEWS ===
if (($_POST['action'] ?? '') === 'reply_review') {
  $id = $_POST['id'] ?? 0;
  $reply = trim($_POST['reply'] ?? '');
  if ($id && $reply !== '') {
    $stmt = $conn->prepare("UPDATE Review SET reply_text = ?, reply_date = NOW() WHERE review_id = ?");
    $stmt->bind_param("si", $reply, $id);
    $success = $stmt->execute() ? "Reply sent." : "Error: " . $stmt->error;
    $stmt->close();
  } else {
    $error = "Reply required.";
  }
}
if (($_POST['action'] ?? '') === 'delete_review') {
  $id = $_POST['id'] ?? 0;
  if ($id) {
    $stmt = $conn->prepare("DELETE FROM Review WHERE review_id = ?");
    $stmt->bind_param("i", $id);
    $success = $stmt->execute() ? "Review deleted." : "Error: " . $stmt->error;
    $stmt->close();
  }
}

// === SALES REPORT LOGIC (AGGREGATED BY PRODUCT) ===
if ($active_tab === 'sales-report' && isset($_POST['generate_report'])) {
  $period = $_POST['period'] ?? 'daily';
  $selected_date = $_POST['report_date'] ?? date('Y-m-d');

  // Enforce date range: Nov 1, 2025 → Dec 31, 2035
  $min_date = '2025-11-01';
  $max_date = '2035-12-31';
  if ($selected_date < $min_date) $selected_date = $min_date;
  if ($selected_date > $max_date) $selected_date = date('Y-m-d');

  $start_date = $end_date = $selected_date;

  switch ($period) {
    case 'daily':
      $start_date = $end_date = $selected_date;
      break;
    case 'weekly':
      $day_of_week = date('w', strtotime($selected_date));
      $start_date = date('Y-m-d', strtotime($selected_date . ' -' . $day_of_week . ' days'));
      $end_date = date('Y-m-d', strtotime($start_date . ' +6 days'));
      break;
    case 'monthly':
      $start_date = date('Y-m-01', strtotime($selected_date));
      $end_date = date('Y-m-t', strtotime($selected_date));
      break;
    case 'yearly':
      $start_date = date('Y-01-01', strtotime($selected_date));
      $end_date = date('Y-12-31', strtotime($selected_date));
      break;
  }

  if ($start_date < $min_date) $start_date = $min_date;
  if ($end_date < $min_date) $end_date = $min_date;

  // === AGGREGATE BY PRODUCT ===
  $stmt = $conn->prepare("
    SELECT 
      p.product_id,
      p.name,
      COALESCE(SUM(si.quantity), 0) AS qty_sold,
      COALESCE(SUM(si.line_total), 0) AS revenue
    FROM Product p
    LEFT JOIN SaleItems si ON p.product_id = si.product_id
    LEFT JOIN Sales s ON si.sale_id = s.sale_id
    WHERE s.sale_date IS NULL OR DATE(s.sale_date) BETWEEN ? AND ?
    GROUP BY p.product_id, p.name
    ORDER BY p.name
  ");
  $stmt->bind_param("ss", $start_date, $end_date);
  $stmt->execute();
  $result = $stmt->get_result();

  $report_data = [];
  $grand_total = 0;
  $total_items = 0;
  while ($row = $result->fetch_assoc()) {
    $report_data[] = $row;
    $grand_total += $row['revenue'];
    $total_items += $row['qty_sold'];
  }
  $stmt->close();

  // === GENERATE HTML (EXACT MATCH TO YOUR PDF) ===
  $period_title = ucfirst($period) . " Sales Report";
  if ($period === 'weekly') {
    $period_title = "Weekly Sales Report (Sun " . date('M j', strtotime($start_date)) . " – Sat " . date('M j, Y', strtotime($end_date)) . ")";
  } elseif ($period === 'monthly') {
    $period_title = date('F Y', strtotime($start_date)) . " Sales Report";
  } elseif ($period === 'yearly') {
    $period_title = date('Y', strtotime($start_date)) . " Sales Report";
  }

  $report_date_display = date('M j, Y', strtotime($selected_date));

  $report_html = "<h4 class='mb-3 text-center'>$period_title</h4>";
  $report_html .= "<p class='text-center mb-4'>$report_date_display</p>";
  $report_html .= "<table class='table table-bordered table-sm' style='font-size: 0.9em;'><thead style='background-color: #d9ead3;'><tr><th style='width: 55%;'>Product</th><th class='text-center' style='width: 15%;'>Qty Sold</th><th class='text-end' style='width: 15%;'>Revenue</th><th class='text-end' style='width: 15%;'>% of Total</th></tr></thead><tbody>";

  foreach ($report_data as $row) {
    $percent = $total_items > 0 ? round(($row['qty_sold'] / $total_items) * 100, 1) : 0;
    $report_html .= "<tr>
      <td>" . htmlspecialchars($row['name']) . "</td>
      <td class='text-center'>" . $row['qty_sold'] . "</td>
      <td class='text-end'>\$" . number_format($row['revenue'], 2) . "</td>
      <td class='text-end'>{$percent}%</td>
    </tr>";
  }

  $report_html .= "<tr style='background-color: #c9daf8; font-weight: bold;'><td>Total</td><td class='text-center'>$total_items</td><td class='text-end'>\$" . number_format($grand_total, 2) . "</td><td class='text-end'>100%</td></tr>";
  $report_html .= "</tbody></table>";
  $report_html .= "<p class='text-center mt-4 text-muted' style='font-size: 0.85em;'>Generated on: " . date('M j, Y g:i A') . "</p>";

  // Store for PDF
  $_SESSION['last_report'] = [
    'title' => $period_title,
    'date' => $report_date_display,
    'data' => $report_data,
    'grand_total' => $grand_total,
    'total_items' => $total_items,
    'generated' => date('M j, Y g:i A'),
    'period' => $period
  ];
}

// === PDF DOWNLOAD (EXACT MATCH TO YOUR IMAGE) ===
if ($active_tab === 'sales-report' && isset($_POST['download_pdf']) && !empty($_SESSION['last_report'])) {
  $report = $_SESSION['last_report'];
  $period = $report['period'] ?? 'daily';

  $pdf = new FPDF('P', 'mm', 'A4');
  $pdf->AddPage();
  $pdf->SetFont('Arial', 'B', 16);
  $pdf->Cell(0, 10, $report['title'], 0, 1, 'C');
  $pdf->Ln(5);
  $pdf->SetFont('Arial', '', 12);
  $pdf->Cell(0, 8, $report['date'], 0, 1, 'C');
  $pdf->Ln(10);

  // Table Header
  $pdf->SetFont('Arial', 'B', 10);
  $pdf->SetFillColor(217, 234, 211); // Light green
  $pdf->Cell(105, 8, 'Product', 1, 0, 'L', true);
  $pdf->Cell(30, 8, 'Qty Sold', 1, 0, 'C', true);
  $pdf->Cell(30, 8, 'Revenue', 1, 0, 'R', true);
  $pdf->Cell(25, 8, '% of Total', 1, 1, 'R', true);

  // Table Rows
  $pdf->SetFont('Arial', '', 10);
  foreach ($report['data'] as $row) {
    $percent = $report['total_items'] > 0 ? round(($row['qty_sold'] / $report['total_items']) * 100, 1) : 0;
    $pdf->Cell(105, 7, substr($row['name'], 0, 45), 1);
    $pdf->Cell(30, 7, $row['qty_sold'], 1, 0, 'C');
    $pdf->Cell(30, 7, '$' . number_format($row['revenue'], 2), 1, 0, 'R');
    $pdf->Cell(25, 7, $percent . '%', 1, 1, 'R');
  }

  // Total Row
  $pdf->SetFont('Arial', 'B', 10);
  $pdf->SetFillColor(201, 218, 248); // Light blue
  $pdf->Cell(105, 8, 'Total', 1, 0, 'L', true);
  $pdf->Cell(30, 8, $report['total_items'], 1, 0, 'C', true);
  $pdf->Cell(30, 8, '$' . number_format($report['grand_total'], 2), 1, 0, 'R', true);
  $pdf->Cell(25, 8, '100%', 1, 1, 'R', true);

  // Generated
  $pdf->Ln(10);
  $pdf->SetFont('Arial', 'I', 9);
  $pdf->Cell(0, 8, 'Generated on: ' . $report['generated'], 0, 1, 'C');

  $filename = "sales_report_" . strtolower($period) . "_" . date('Ymd', strtotime($report['date'])) . ".pdf";
  $pdf->Output('D', $filename);
  exit;
}

// === DATA FETCH ===
$employees = $conn->query("SELECT employee_id AS id, employee_email AS email, role FROM Employee");
$tasks = $conn->query("SELECT t.task_id AS id, e.employee_email, t.task_description, t.task_date AS date FROM EmployeeTasks t JOIN Employee e ON t.employee_id = e.employee_id");
$schedules = $conn->query("SELECT s.schedule_id AS id, e.employee_email, s.shift_date AS date, s.shift_time, s.role_tasks FROM EmployeeSchedules s JOIN Employee e ON s.employee_id = e.employee_id");
$products = $conn->query("SELECT product_id AS id, name, description, price, image_path AS image, stock_quantity AS stock FROM Product");
$reviews = $conn->query("SELECT r.review_id AS id, CONCAT(c.first_name,' ',c.last_name) AS name, r.rating, r.review_text AS review, r.submitted_date AS submitted_Date, r.reply_text AS reply FROM Review r LEFT JOIN Customer c ON r.customer_id = c.customer_id");
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <title>Admin Portal - Petrongolo Evergreen Plantation</title>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
  <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Oswald:wght@400;700&family=Roboto:wght@400;700&display=swap">
  <style>
    html,body{height:100%;margin:0;background:#f8f9fa;font-family:'Roboto',sans-serif;}
    .sidebar{position:fixed;right:0;top:0;width:250px;height:100vh;background:#2c5530;padding:20px;overflow-y:auto;box-shadow:-2px 0 5px rgba(0,0,0,0.1);}
    .sidebar h4{color:#fff;margin-bottom:20px;}
    .sidebar .nav-link{color:#fff;padding:10px;border-radius:5px;margin-bottom:10px;font-weight:600;transition:.3s;}
    .sidebar .nav-link:hover,.sidebar .nav-link.active{background:#5c8c61;color:#fff !important;}
    .content{margin-right:250px;padding:20px;}
    .section-title{text-align:center;margin:40px 0;color:#2c5530;font-family:'Oswald',sans-serif;font-weight:700;}
    .table th{background:#2c5530;color:#fff;}
    .btn-primary{background:#2c5530;border:none;}
    .btn-primary:hover{background:#5c8c61;}
  </style>
</head>
<body>
  <div class="sidebar">
    <h4>Admin Options</h4>
    <ul class="nav flex-column">
      <li class="nav-item"><a class="nav-link <?php echo $active_tab=='employee-accounts'?'active':''; ?>" href="#" onclick="setTab('employee-accounts')"><i class="bi bi-person me-2"></i>Employees</a></li>
      <li class="nav-item"><a class="nav-link <?php echo $active_tab=='tasks'?'active':''; ?>" href="#" onclick="setTab('tasks')"><i class="bi bi-check2-square me-2"></i>Tasks</a></li>
      <li class="nav-item"><a class="nav-link <?php echo $active_tab=='schedules'?'active':''; ?>" href="#" onclick="setTab('schedules')"><i class="bi bi-calendar-event me-2"></i>Schedules</a></li>
      <li class="nav-item"><a class="nav-link <?php echo $active_tab=='products'?'active':''; ?>" href="#" onclick="setTab('products')"><i class="bi bi-box me-2"></i>Products</a></li>
      <li class="nav-item"><a class="nav-link <?php echo $active_tab=='reviews'?'active':''; ?>" href="#" onclick="setTab('reviews')"><i class="bi bi-chat-dots me-2"></i>Reviews</a></li>
      <li class="nav-item"><a class="nav-link <?php echo $active_tab=='sales-report'?'active':''; ?>" href="#" onclick="setTab('sales-report')"><i class="bi bi-graph-up me-2"></i>Sales Report</a></li>
      <li class="nav-item">
        <a class="nav-link logout-link" href="logout.php" onclick="this.innerHTML='<i class=&quot;bi bi-hourglass-split me-2&quot;></i>Logging out...';">
          <i class="bi bi-box-arrow-left me-2"></i>Logout
        </a>
      </li>
    </ul>
  </div>

  <div class="content">
    <?php if ($success) echo "<div class='alert alert-success'>$success</div>"; ?>
    <?php if ($error) echo "<div class='alert alert-danger'>$error</div>"; ?>

    <!-- Employee Accounts -->
    <div id="employee-accounts" style="display:<?php echo $active_tab=='employee-accounts'?'block':'none'; ?>">
      <h2 class="section-title">Employee Accounts</h2>
      <form method="post" class="mb-4">
        <input type="hidden" name="action" value="add_employee"><input type="hidden" name="tab" value="employee-accounts">
        <div class="row g-3"><div class="col-md-5"><input type="email" name="empEmail" class="form-control" placeholder="Email" required></div>
        <div class="col-md-4"><input type="password" name="empPassword" class="form-control" placeholder="Password" required></div>
        <div class="col-md-2"><select name="role" class="form-select"><option value="employee">Employee</option><option value="admin">Admin</option></select></div>
        <div class="col-md-1"><button class="btn btn-primary w-100">Add</button></div></div>
      </form>
      <table class="table table-striped"><thead><tr><th>ID</th><th>Email</th><th>Role</th><th>Actions</th></tr></thead><tbody>
        <?php while($e=$employees->fetch_assoc()): ?>
        <tr><td><?php echo $e['id'];?></td><td><?php echo htmlspecialchars($e['email']);?></td><td><?php echo $e['role'];?></td>
        <td>
          <button class="btn btn-sm btn-primary" data-bs-toggle="modal" data-bs-target="#editEmp<?php echo $e['id'];?>"><i class="bi bi-pencil"></i></button>
          <form method="post" style="display:inline"><input type="hidden" name="action" value="delete_employee"><input type="hidden" name="tab" value="employee-accounts"><input type="hidden" name="id" value="<?php echo $e['id'];?>"><button class="btn btn-sm btn-danger" onclick="return confirm('Delete?')"><i class="bi bi-trash"></i></button></form>
        </td></tr>
        <div class="modal fade" id="editEmp<?php echo $e['id'];?>"><div class="modal-dialog"><div class="modal-content"><div class="modal-header"><h5>Edit Employee</h5><button class="btn-close" data-bs-dismiss="modal"></button></div>
        <div class="modal-body"><form method="post"><input type="hidden" name="action" value="update_employee"><input type="hidden" name="tab" value="employee-accounts"><input type="hidden" name="id" value="<?php echo $e['id'];?>">
          <div class="mb-3"><input type="email" name="email" class="form-control" value="<?php echo htmlspecialchars($e['email']);?>" required></div>
          <div class="mb-3"><input type="password" name="password" class="form-control" placeholder="New Password (optional)"></div>
          <div class="mb-3"><select name="role" class="form-select"><option value="employee" <?php echo $e['role']=='employee'?'selected':'';?>>Employee</option><option value="admin" <?php echo $e['role']=='admin'?'selected':'';?>>Admin</option></select></div>
          <button class="btn btn-primary">Update</button></form></div></div></div></div>
        <?php endwhile; ?>
      </tbody></table>
    </div>

    <!-- Tasks -->
    <div id="tasks" style="display:<?php echo $active_tab=='tasks'?'block':'none'; ?>">
      <h2 class="section-title">Tasks</h2>
      <form method="post" class="mb-4">
        <input type="hidden" name="action" value="add_task"><input type="hidden" name="tab" value="tasks">
        <div class="row g-3"><div class="col-md-3"><select name="employee_id" class="form-select" required><option value="">Employee</option><?php $employees->data_seek(0); while($e=$employees->fetch_assoc()):?><option value="<?php echo $e['id'];?>"><?php echo htmlspecialchars($e['email']);?></option><?php endwhile;?></select></div>
        <div class="col-md-5"><input type="text" name="task_description" class="form-control" placeholder="Task" required></div>
        <div class="col-md-3"><input type="date" name="date" class="form-control" required></div>
        <div class="col-md-1"><button class="btn btn-primary w-100">Add</button></div></div>
      </form>
      <table class="table table-striped"><thead><tr><th>ID</th><th>Email</th><th>Task</th><th>Date</th><th>Actions</th></tr></thead><tbody>
        <?php while($t=$tasks->fetch_assoc()): ?>
        <tr><td><?php echo $t['id'];?></td><td><?php echo htmlspecialchars($t['employee_email']);?></td><td><?php echo htmlspecialchars($t['task_description']);?></td><td><?php echo $t['date'];?></td>
        <td>
          <button class="btn btn-sm btn-primary" data-bs-toggle="modal" data-bs-target="#editTask<?php echo $t['id'];?>"><i class="bi bi-pencil"></i></button>
          <form method="post" style="display:inline"><input type="hidden" name="action" value="delete_task"><input type="hidden" name="tab" value="tasks"><input type="hidden" name="id" value="<?php echo $t['id'];?>"><button class="btn btn-sm btn-danger" onclick="return confirm('Delete?')"><i class="bi bi-trash"></i></button></form>
        </td></tr>
        <div class="modal fade" id="editTask<?php echo $t['id'];?>"><div class="modal-dialog"><div class="modal-content"><div class="modal-header"><h5>Edit Task</h5><button class="btn-close" data-bs-dismiss="modal"></button></div>
        <div class="modal-body"><form method="post"><input type="hidden" name="action" value="update_task"><input type="hidden" name="tab" value="tasks"><input type="hidden" name="id" value="<?php echo $t['id'];?>">
          <div class="mb-3"><select name="employee_id" class="form-select" required><?php $employees->data_seek(0); while($e=$employees->fetch_assoc()):?><option value="<?php echo $e['id'];?>" <?php echo $e['email']==$t['employee_email']?'selected':'';?>><?php echo htmlspecialchars($e['email']);?></option><?php endwhile;?></select></div>
          <div class="mb-3"><input type="text" name="task_description" class="form-control" value="<?php echo htmlspecialchars($t['task_description']);?>" required></div>
          <div class="mb-3"><input type="date" name="date" class="form-control" value="<?php echo $t['date'];?>" required></div>
          <button class="btn btn-primary">Update</button></form></div></div></div></div>
        <?php endwhile; ?>
      </tbody></table>
    </div>

    <!-- Schedules -->
    <div id="schedules" style="display:<?php echo $active_tab=='schedules'?'block':'none'; ?>">
      <h2 class="section-title">Schedules</h2>
      <form method="post" class="mb-4">
        <input type="hidden" name="action" value="add_schedule"><input type="hidden" name="tab" value="schedules">
        <div class="row g-3"><div class="col-md-3"><select name="employee_id" class="form-select" required><option value="">Employee</option><?php $employees->data_seek(0); while($e=$employees->fetch_assoc()):?><option value="<?php echo $e['id'];?>"><?php echo htmlspecialchars($e['email']);?></option><?php endwhile;?></select></div>
        <div class="col-md-3"><input type="date" name="date" class="form-control" required></div>
        <div class="col-md-3"><input type="text" name="shift_time" class="form-control" placeholder="Shift Time" required></div>
        <div class="col-md-2"><input type="text" name="role_tasks" class="form-control" placeholder="Tasks" required></div>
        <div class="col-md-1"><button class="btn btn-primary w-100">Add</button></div></div>
      </form>
      <table class="table table-striped"><thead><tr><th>ID</th><th>Email</th><th>Date</th><th>Time</th><th>Tasks</th><th>Actions</th></tr></thead><tbody>
        <?php while($s=$schedules->fetch_assoc()): ?>
        <tr><td><?php echo $s['id'];?></td><td><?php echo htmlspecialchars($s['employee_email']);?></td><td><?php echo $s['date'];?></td><td><?php echo htmlspecialchars($s['shift_time']);?></td><td><?php echo htmlspecialchars($s['role_tasks']);?></td>
        <td>
          <button class="btn btn-sm btn-primary" data-bs-toggle="modal" data-bs-target="#editSch<?php echo $s['id'];?>"><i class="bi bi-pencil"></i></button>
          <form method="post" style="display:inline"><input type="hidden" name="action" value="delete_schedule"><input type="hidden" name="tab" value="schedules"><input type="hidden" name="id" value="<?php echo $s['id'];?>"><button class="btn btn-sm btn-danger" onclick="return confirm('Delete?')"><i class="bi bi-trash"></i></button></form>
        </td></tr>
        <div class="modal fade" id="editSch<?php echo $s['id'];?>"><div class="modal-dialog"><div class="modal-content"><div class="modal-header"><h5>Edit Schedule</h5><button class="btn-close" data-bs-dismiss="modal"></button></div>
        <div class="modal-body"><form method="post"><input type="hidden" name="action" value="update_schedule"><input type="hidden" name="tab" value="schedules"><input type="hidden" name="id" value="<?php echo $s['id'];?>">
          <div class="mb-3"><select name="employee_id" class="form-select" required><?php $employees->data_seek(0); while($e=$employees->fetch_assoc()):?><option value="<?php echo $e['id'];?>" <?php echo $e['email']==$s['employee_email']?'selected':'';?>><?php echo htmlspecialchars($e['email']);?></option><?php endwhile;?></select></div>
          <div class="mb-3"><input type="date" name="date" class="form-control" value="<?php echo $s['date'];?>" required></div>
          <div class="mb-3"><input type="text" name="shift_time" class="form-control" value="<?php echo htmlspecialchars($s['shift_time']);?>" required></div>
          <div class="mb-3"><input type="text" name="role_tasks" class="form-control" value="<?php echo htmlspecialchars($s['role_tasks']);?>" required></div>
          <button class="btn btn-primary">Update</button></form></div></div></div></div>
        <?php endwhile; ?>
      </tbody></table>
    </div>

    <!-- Products -->
    <div id="products" style="display:<?php echo $active_tab=='products'?'block':'none'; ?>">
      <h2 class="section-title">Products</h2>
      <form method="post" enctype="multipart/form-data" class="mb-4">
        <input type="hidden" name="action" value="add_product"><input type="hidden" name="tab" value="products">
        <div class="row g-3"><div class="col-md-2"><input type="text" name="name" class="form-control" placeholder="Name" required></div>
        <div class="col-md-3"><input type="text" name="description" class="form-control" placeholder="Description" required></div>
        <div class="col-md-1"><input type="number" step="0.01" name="price" class="form-control" placeholder="Price" required></div>
        <div class="col-md-1"><input type="number" name="stock_quantity" class="form-control" placeholder="Stock" required></div>
        <div class="col-md-3"><input type="file" name="image" class="form-control"></div>
        <div class="col-md-2"><button class="btn btn-primary w-100">Add</button></div></div>
      </form>
      <table class="table table-striped"><thead><tr><th>ID</th><th>Name</th><th>Desc</th><th>Price</th><th>Img</th><th>Stock</th><th>Actions</th></tr></thead><tbody>
        <?php while($p=$products->fetch_assoc()): ?>
        <tr><td><?php echo $p['id'];?></td><td><?php echo htmlspecialchars($p['name']);?></td><td><?php echo htmlspecialchars($p['description']);?></td><td>$<?php echo number_format($p['price'],2);?></td>
        <td><img src="<?php echo htmlspecialchars($p['image']);?>" style="width:40px;"></td><td><?php echo $p['stock'];?></td>
        <td>
          <button class="btn btn-sm btn-primary" data-bs-toggle="modal" data-bs-target="#editProd<?php echo $p['id'];?>"><i class="bi bi-pencil"></i></button>
          <form method="post" style="display:inline"><input type="hidden" name="action" value="delete_product"><input type="hidden" name="tab" value="products"><input type="hidden" name="id" value="<?php echo $p['id'];?>"><button class="btn btn-sm btn-danger" onclick="return confirm('Delete?')"><i class="bi bi-trash"></i></button></form>
        </td></tr>
        <div class="modal fade" id="editProd<?php echo $p['id'];?>"><div class="modal-dialog"><div class="modal-content"><div class="modal-header"><h5>Edit Product</h5><button class="btn-close" data-bs-dismiss="modal"></button></div>
        <div class="modal-body"><form method="post" enctype="multipart/form-data"><input type="hidden" name="action" value="update_product"><input type="hidden" name="tab" value="products"><input type="hidden" name="id" value="<?php echo $p['id'];?>"><input type="hidden" name="existing_image" value="<?php echo htmlspecialchars($p['image']);?>">
          <div class="mb-3"><input type="text" name="name" class="form-control" value="<?php echo htmlspecialchars($p['name']);?>" required></div>
          <div class="mb-3"><input type="text" name="description" class="form-control" value="<?php echo htmlspecialchars($p['description']);?>" required></div>
          <div class="mb-3"><input type="number" step="0.01" name="price" class="form-control" value="<?php echo $p['price'];?>" required></div>
          <div class="mb-3"><input type="number" name="stock_quantity" class="form-control" value="<?php echo $p['stock'];?>" required></div>
          <div class="mb-3"><input type="file" name="image" class="form-control"></div>
          <button class="btn btn-primary">Update</button></form></div></div></div></div>
        <?php endwhile; ?>
      </tbody></table>
    </div>

    <!-- Reviews -->
    <div id="reviews" style="display:<?php echo $active_tab=='reviews'?'block':'none'; ?>">
      <h2 class="section-title">Reviews</h2>
      <table class="table table-striped"><thead><tr><th>ID</th><th>Name</th><th>Rating</th><th>Review</th><th>Date</th><th>Reply</th><th>Actions</th></tr></thead><tbody>
        <?php while($r=$reviews->fetch_assoc()): ?>
        <tr><td><?php echo $r['id'];?></td><td><?php echo htmlspecialchars($r['name']);?></td><td><?php echo $r['rating'];?></td><td><?php echo htmlspecialchars($r['review']);?></td><td><?php echo $r['submitted_Date'];?></td><td><?php echo htmlspecialchars($r['reply']??'');?></td>
        <td>
          <form method="post" class="mb-1"><input type="hidden" name="action" value="reply_review"><input type="hidden" name="tab" value="reviews"><input type="hidden" name="id" value="<?php echo $r['id'];?>"><textarea name="reply" class="form-control" rows="1" placeholder="Reply"><?php echo htmlspecialchars($r['reply']??'');?></textarea><button class="btn btn-sm btn-primary mt-1">Reply</button></form>
          <form method="post" style="display:inline"><input type="hidden" name="action" value="delete_review"><input type="hidden" name="tab" value="reviews"><input type="hidden" name="id" value="<?php echo $r['id'];?>"><button class="btn btn-sm btn-danger" onclick="return confirm('Delete?')"><i class="bi bi-trash"></i></button></form>
        </td></tr>
        <?php endwhile; ?>
      </tbody></table>
    </div>

    <!-- Sales Report -->
    <div id="sales-report" style="display:<?php echo $active_tab=='sales-report'?'block':'none'; ?>">
      <h2 class="section-title">Sales Report</h2>
      <form method="post" class="mb-4">
        <input type="hidden" name="tab" value="sales-report">
        <div class="row g-3 align-items-end">
          <div class="col-md-3">
            <label class="form-label">Period</label>
            <select name="period" class="form-select" required>
              <option value="daily">Daily</option>
              <option value="weekly">Weekly (Sun–Sat)</option>
              <option value="monthly">Monthly</option>
              <option value="yearly">Yearly</option>
            </select>
          </div>
          <div class="col-md-3">
            <label class="form-label">Select Date</label>
            <input type="date" name="report_date" class="form-control" min="2025-11-01" max="2035-12-31" value="<?php echo date('Y-m-d'); ?>" required>
          </div>
          <div class="col-md-3">
            <button type="submit" name="generate_report" class="btn btn-primary w-100">Generate Report</button>
          </div>
        </div>
      </form>

      <?php if (!empty($report_html)): ?>
        <div class="mb-3">
          <?php echo $report_html; ?>
          <form method="post" class="mt-3">
            <input type="hidden" name="tab" value="sales-report">
            <button type="submit" name="download_pdf" class="btn btn-success">
              <i class="bi bi-file-earmark-pdf"></i> Download PDF
            </button>
          </form>
        </div>
      <?php endif; ?>
    </div>
  </div>

  <form id="tabForm" method="post"><input type="hidden" name="tab" id="tabInput"></form>

  <script>
    function setTab(t) {
      document.getElementById('tabInput').value = t;
      document.getElementById('tabForm').submit();
    }
    document.querySelectorAll('.nav-link:not(.logout-link)').forEach(link => {
      link.addEventListener('click', e => {
        e.preventDefault();
        const match = link.getAttribute('onclick')?.match(/'([^']+)'/);
        if (match) setTab(match[1]);
      });
    });
  </script>
  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
<?php $conn->close(); ?>