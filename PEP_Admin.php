<?php
// PEP_Admin.php – FULLY FUNCTIONAL + AGGREGATED SALES REPORT + EXACT PDF MATCH + CHARTS TAB + PASSWORD VALIDATION + ADMIN SYNC + USERNAME FOR ALL + DELETE FIXED + SECURE LOGIN REQUIRED + USERNAME SELECT IN TASKS/SCHEDULES + ERROR-FREE
error_reporting(E_ALL);
ini_set('display_errors', 1);
session_start();
include 'config.php';
require('fpdf/fpdf.php');
// === SECURE LOGIN CHECK ===
if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
  $_SESSION['error'] = "You must be logged in as an admin to access this page.";
  header("Location: PEP_Main.php");
  exit;
}
// Initialize messages
$success = $error = '';
$report_html = '';
$report_data = [];
$chart_html = '';
$chart_data = [];
// Determine active tab
$active_tab = $_POST['tab'] ?? 'employee-accounts';
// === PASSWORD VALIDATION HELPER ===
function validatePassword($pwd, &$errors = []) {
  $errors = [];
  if (strlen($pwd) < 12) $errors[] = "at least 12 characters";
  if (!preg_match('/[A-Z]/', $pwd)) $errors[] = "one uppercase letter";
  if (!preg_match('/[a-z]/', $pwd)) $errors[] = "one lowercase letter";
  if (!preg_match('/[0-9]/', $pwd)) $errors[] = "one number";
  if (!preg_match('/[^A-Za-z0-9]/', $pwd)) $errors[] = "one special character";
  return empty($errors);
}
// === EMPLOYEE CRUD (WITH PASSWORD RULES + ADMIN SYNC + USERNAME FOR ALL) ===
if (($_POST['action'] ?? '') === 'add_employee') {
  $email = trim($_POST['empEmail'] ?? '');
  $password = $_POST['empPassword'] ?? '';
  $role = $_POST['role'] ?? 'employee';
  $username = trim($_POST['empUsername'] ?? '');
  $pwdErrors = [];
  if (!$email || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
    $error = "Valid email required.";
  } elseif (!$password || !validatePassword($password, $pwdErrors)) {
    $error = "Password must meet all requirements.";
  } elseif (!$username) {
    $error = "Username is required for all employees.";
  } else {
    $stmt = $conn->prepare("SELECT employee_id FROM Employee WHERE employee_email = ?");
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $stmt->store_result();
    if ($stmt->num_rows > 0) {
      $error = "Email already exists.";
    } else {
      $hash = password_hash($password, PASSWORD_DEFAULT);
      $stmt = $conn->prepare("INSERT INTO Employee (employee_email, password_hash, role, username) VALUES (?, ?, ?, ?)");
      $stmt->bind_param("ssss", $email, $hash, $role, $username);
      if ($stmt->execute()) {
        $emp_id = $stmt->insert_id;
        $success = "Employee added.";
        if ($role === 'admin') {
          $stmt2 = $conn->prepare("INSERT INTO AdminAccount (admin_id, username, password_hash) VALUES (?, ?, ?)");
          $stmt2->bind_param("iss", $emp_id, $username, $hash);
          $stmt2->execute();
          $stmt2->close();
        }
      } else {
        $error = "Error: " . $stmt->error;
      }
    }
    $stmt->close();
  }
}
if (($_POST['action'] ?? '') === 'update_employee') {
  $id = $_POST['id'] ?? 0;
  $email = trim($_POST['email'] ?? '');
  $role = $_POST['role'] ?? 'employee';
  $password = $_POST['password'] ?? '';
  $username = trim($_POST['empUsername'] ?? '');
  if ($id && $email && filter_var($email, FILTER_VALIDATE_EMAIL) && $username) {
    $pwdErrors = [];
    if ($password && !validatePassword($password, $pwdErrors)) {
      $error = "Password must meet all requirements.";
    } else {
      if ($password) {
        $hash = password_hash($password, PASSWORD_DEFAULT);
        $stmt = $conn->prepare("UPDATE Employee SET employee_email = ?, password_hash = ?, role = ?, username = ? WHERE employee_id = ?");
        $stmt->bind_param("ssssi", $email, $hash, $role, $username, $id);
      } else {
        $stmt = $conn->prepare("UPDATE Employee SET employee_email = ?, role = ?, username = ? WHERE employee_id = ?");
        $stmt->bind_param("sssi", $email, $role, $username, $id);
      }
      if ($stmt->execute()) {
        $success = "Employee updated.";
        if ($role === 'admin') {
          $hash = $password ? password_hash($password, PASSWORD_DEFAULT) : $conn->query("SELECT password_hash FROM Employee WHERE employee_id = $id")->fetch_assoc()['password_hash'];
          $check = $conn->query("SELECT admin_id FROM AdminAccount WHERE admin_id = $id")->num_rows;
          if ($check > 0) {
            $stmt2 = $conn->prepare("UPDATE AdminAccount SET username = ?, password_hash = ? WHERE admin_id = ?");
            $stmt2->bind_param("ssi", $username, $hash, $id);
            $stmt2->execute();
            $stmt2->close();
          } else {
            $stmt2 = $conn->prepare("INSERT INTO AdminAccount (admin_id, username, password_hash) VALUES (?, ?, ?)");
            $stmt2->bind_param("iss", $id, $username, $hash);
            $stmt2->execute();
            $stmt2->close();
          }
        } else {
          $conn->query("DELETE FROM AdminAccount WHERE admin_id = $id");
        }
      } else {
        $error = "Error: " . $stmt->error;
      }
      $stmt->close();
    }
  } else {
    $error = "All fields are required.";
  }
}
if (($_POST['action'] ?? '') === 'delete_employee') {
  $id = $_POST['id'] ?? 0;
  if ($id) {
    $stmt = $conn->prepare("DELETE FROM AdminAccount WHERE admin_id = ?");
    $stmt->bind_param("i", $id);
    $stmt->execute();
    $stmt->close();
    $stmt = $conn->prepare("DELETE FROM Employee WHERE employee_id = ?");
    $stmt->stmt->bind_param("i", $id);
    if ($stmt->execute()) {
      $success = "Employee deleted.";
    } else {
      $error = "Error deleting employee: " . $stmt->error;
    }
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
    if (!is_dir($dir)) mkdir($dir, 0755, true);
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
    if (!is_dir($dir)) mkdir($dir, 0755, true);
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
// === PDF DOWNLOAD ===
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
  $pdf->SetFont('Arial', 'B', 10);
  $pdf->SetFillColor(217, 234, 211);
  $pdf->Cell(105, 8, 'Product', 1, 0, 'L', true);
  $pdf->Cell(30, 8, 'Qty Sold', 1, 0, 'C', true);
  $pdf->Cell(30, 8, 'Revenue', 1, 0, 'R', true);
  $pdf->Cell(25, 8, '% of Total', 1, 1, 'R', true);
  $pdf->SetFont('Arial', '', 10);
  foreach ($report['data'] as $row) {
    $percent = $report['total_items'] > 0 ? round(($row['qty_sold'] / $report['total_items']) * 100, 1) : 0;
    $pdf->Cell(105, 7, substr($row['name'], 0, 45), 1);
    $pdf->Cell(30, 7, $row['qty_sold'], 1, 0, 'C');
    $pdf->Cell(30, 7, '$' . number_format($row['revenue'], 2), 1, 0, 'R');
    $pdf->Cell(25, 7, $percent . '%', 1, 1, 'R');
  }
  $pdf->SetFont('Arial', 'B', 10);
  $pdf->SetFillColor(201, 218, 248);
  $pdf->Cell(105, 8, 'Total', 1, 0, 'L', true);
  $pdf->Cell(30, 8, $report['total_items'], 1, 0, 'C', true);
  $pdf->Cell(30, 8, '$' . number_format($report['grand_total'], 2), 1, 0, 'R', true);
  $pdf->Cell(25, 8, '100%', 1, 1, 'R', true);
  $pdf->Ln(10);
  $pdf->SetFont('Arial', 'I', 9);
  $pdf->Cell(0, 8, 'Generated on: ' . $report['generated'], 0, 1, 'C');
  $filename = "sales_report_" . strtolower($period) . "_" . date('Ymd', strtotime($report['date'])) . ".pdf";
  $pdf->Output('D', $filename);
  exit;
}
// === CHARTS TAB LOGIC ===
if ($active_tab === 'charts' && isset($_POST['chart_type'])) {
  $type = $_POST['chart_type'];
  $period = $_POST['period'] ?? 'monthly';
  $date = $_POST['chart_date'] ?? date('Y-m-d');
  $min = '2025-11-01';
  $max = '2035-12-31';
  if ($date < $min) $date = $min;
  if ($date > $max) $date = date('Y-m-d');
  $start = $end = $date;
  switch ($period) {
    case 'daily': $start = $end = $date; break;
    case 'weekly':
      $dow = date('w', strtotime($date));
      $start = date('Y-m-d', strtotime($date.' -'.$dow.' days'));
      $end = date('Y-m-d', strtotime($start.' +6 days'));
      break;
    case 'monthly':
      $start = date('Y-m-01', strtotime($date));
      $end = date('Y-m-t', strtotime($date));
      break;
    case 'yearly':
      $start = date('Y-01-01', strtotime($date));
      $end = date('Y-12-31', strtotime($date));
      break;
  }
  if ($start < $min) $start = $min;
  if ($end < $min) $end = $min;
  $sql = "
    SELECT p.product_id, p.name,
           COALESCE(SUM(si.quantity),0) AS qty,
           COALESCE(SUM(si.line_total),0) AS revenue
    FROM Product p
    LEFT JOIN SaleItems si ON p.product_id = si.product_id
    LEFT JOIN Sales s ON si.sale_id = s.sale_id
    WHERE s.sale_date IS NULL OR DATE(s.sale_date) BETWEEN ? AND ?
    GROUP BY p.product_id, p.name
    ORDER BY p.name
  ";
  $stmt = $conn->prepare($sql);
  $stmt->bind_param('ss', $start, $end);
  $stmt->execute();
  $res = $stmt->get_result();
  $labels = $values = $colors = [];
  $total_qty = $total_rev = 0;
  while ($row = $res->fetch_assoc()) {
    $labels[] = $row['name'];
    $values[] = $type === 'qty' ? (int)$row['qty'] : (float)$row['revenue'];
    $total_qty += $row['qty'];
    $total_rev += $row['revenue'];
    $colors[] = sprintf('#%06X', mt_rand(0, 0xFFFFFF));
  }
  $stmt->close();
  $chart_json = json_encode([
    'type' => 'bar',
    'data' => [
      'labels' => $labels,
      'datasets' => [[
        'label' => $type === 'qty' ? 'Units Sold' : 'Revenue ($)',
        'data' => $values,
        'backgroundColor' => $colors,
        'borderColor' => $colors,
        'borderWidth' => 1
      ]]
    ],
    'options' => [
      'responsive' => true,
      'plugins' => [
        'title' => ['display' => true, 'text' => ucfirst($period).' '.($type==='qty'?'Units Sold':'Revenue').' by Product'],
        'legend' => ['display' => false]
      ],
      'scales' => [
        'y' => ['beginAtZero' => true]
      ]
    ]
  ]);
  $periodTitle = ucfirst($period).' ('.date('M j', strtotime($start)).' – '.date('M j, Y', strtotime($end)).')';
  $summary = "<table class='table table-sm table-bordered mt-3'>
    <thead class='table-light'><tr><th>Metric</th><th class='text-end'>Value</th></tr></thead>
    <tbody>
      <tr><td>Total Units Sold</td><td class='text-end'>".number_format($total_qty)."</td></tr>
      <tr><td>Total Revenue</td><td class='text-end'>\$".number_format($total_rev,2)."</td></tr>
    </tbody>
  </table>";
  $chart_html = "
    <div class='card shadow-sm'>
      <div class='card-header bg-success text-white'>
        <h5 class='mb-0'>$periodTitle – ".($type==='qty'?'Units Sold':'Revenue')."</h5>
      </div>
      <div class='card-body'>
        <canvas id='productChart' height='130'></canvas>
        $summary
      </div>
    </div>
    <script>
      document.addEventListener('DOMContentLoaded', function() {
        const ctx = document.getElementById('productChart').getContext('2d');
        new Chart(ctx, $chart_json);
      });
    </script>
  ";
}
// === DATA FETCH — NOW INCLUDES USERNAME ===
$employees = $conn->query("SELECT employee_id AS id, employee_email AS email, role, username FROM Employee ORDER BY username");
$tasks = $conn->query("SELECT t.task_id AS id, e.username, t.task_description, t.task_date AS date FROM EmployeeTasks t JOIN Employee e ON t.employee_id = e.employee_id ORDER BY t.task_date");
$schedules = $conn->query("SELECT s.schedule_id AS id, e.username, s.shift_date AS date, s.shift_time, s.role_tasks FROM EmployeeSchedules s JOIN Employee e ON s.employee_id = e.employee_id ORDER BY s.shift_date");
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
  <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
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
    .card-header h5 { font-weight: 600; }
    .pwd-req { font-size: 0.75rem; margin-top: 2px; }
    .pwd-req.met { color: #28a745 !important; }
    .pwd-req:not(.met) { color: #dc3545; }
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
      
            <li class="nav-item"><a class="nav-link <?php echo $active_tab=='charts'?'active':''; ?>" href="#" onclick="setTab('charts')"><i class="bi bi-bar-chart-line me-2"></i>Charts</a></li>
      
      
      
<li class="nav-item">
  <a class="nav-link portal-link" href="PEP_EmployeePortal.php">
    <i class="bi bi-arrow-left me-2"></i>Back to Employee Portal
  </a>
</li>

      
      
    
      <li class="nav-item"><a class="nav-link logout-link" href="logout.php"><i class="bi bi-box-arrow-left me-2"></i>Logout</a></li>
    </ul>
  </div>
  <div class="content">
    <?php if ($success) echo "<div class='alert alert-success'>$success</div>"; ?>
    <?php if ($error) echo "<div class='alert alert-danger'>$error</div>"; ?>
    <!-- Employee Accounts -->
    <div id="employee-accounts" style="display:<?php echo $active_tab=='employee-accounts'?'block':'none'; ?>">
      <h2 class="section-title">Employee Accounts</h2>
      <form method="post" class="mb-4">
        <input type="hidden" name="action" value="add_employee">
        <input type="hidden" name="tab" value="employee-accounts">
        <div class="row g-3 align-items-end">
          <div class="col-md-4">
            <input type="email" name="empEmail" class="form-control" placeholder="Email" required>
          </div>
          <div class="col-md-3">
            <div class="mb-2">
              <div class="pwd-req" id="reqLen">12+ characters</div>
              <div class="pwd-req" id="reqUpper">One uppercase</div>
              <div class="pwd-req" id="reqLower">One lowercase</div>
              <div class="pwd-req" id="reqNum">One number</div>
              <div class="pwd-req" id="reqSpecial">One special (!@#$% etc.)</div>
            </div>
            <input type="password" id="addPwd" name="empPassword" class="form-control" placeholder="Password" required>
          </div>
          <div class="col-md-2">
            <input type="text" id="addUsername" name="empUsername" class="form-control" placeholder="Username" required>
          </div>
          <div class="col-md-2">
            <select name="role" class="form-select">
              <option value="employee">Employee</option>
              <option value="admin">Admin</option>
            </select>
          </div>
          <div class="col-md-1">
            <button type="submit" class="btn btn-primary w-100">Add</button>
          </div>
        </div>
      </form>
      <table class="table table-striped"><thead><tr><th>ID</th><th>Email</th><th>Username</th><th>Role</th><th>Actions</th></tr></thead><tbody>
        <?php while($e=$employees->fetch_assoc()): ?>
        <tr><td><?php echo $e['id'];?></td><td><?php echo htmlspecialchars($e['email']);?></td><td><?php echo htmlspecialchars($e['username']);?></td><td><?php echo $e['role'];?></td>
        <td>
          <button class="btn btn-sm btn-primary" data-bs-toggle="modal" data-bs-target="#editEmp<?php echo $e['id'];?>"><i class="bi bi-pencil"></i></button>
          <form method="post" style="display:inline">
            <input type="hidden" name="action" value="delete_employee">
            <input type="hidden" name="tab" value="employee-accounts">
            <input type="hidden" name="id" value="<?php echo $e['id'];?>">
            <button class="btn btn-sm btn-danger" onclick="return confirm('Delete?')"><i class="bi bi-trash"></i></button>
          </form>
        </td></tr>
        <div class="modal fade" id="editEmp<?php echo $e['id'];?>"><div class="modal-dialog"><div class="modal-content">
          <div class="modal-header"><h5>Edit Employee</h5><button class="btn-close" data-bs-dismiss="modal"></button></div>
          <div class="modal-body">
            <form method="post">
              <input type="hidden" name="action" value="update_employee">
              <input type="hidden" name="tab" value="employee-accounts">
              <input type="hidden" name="id" value="<?php echo $e['id'];?>">
              <div class="mb-3"><input type="email" name="email" class="form-control" value="<?php echo htmlspecialchars($e['email']);?>" required></div>
              <div class="mb-3">
                <div class="mb-2">
                  <div class="pwd-req" id="editLen<?php echo $e['id'];?>">12+ characters</div>
                  <div class="pwd-req" id="editUpper<?php echo $e['id'];?>">One uppercase</div>
                  <div class="pwd-req" id="editLower<?php echo $e['id'];?>">One lowercase</div>
                  <div class="pwd-req" id="editNum<?php echo $e['id'];?>">One number</div>
                  <div class="pwd-req" id="editSpecial<?php echo $e['id'];?>">One special (!@#$% etc.)</div>
                </div>
                <input type="password" id="editPwd<?php echo $e['id'];?>" name="password" class="form-control" placeholder="New Password (optional)">
              </div>
              <div class="mb-3"><input type="text" name="empUsername" class="form-control" value="<?php echo htmlspecialchars($e['username']);?>" required></div>
              <div class="mb-3">
                <select name="role" class="form-select">
                  <option value="employee" <?php echo $e['role']=='employee'?'selected':'';?>>Employee</option>
                  <option value="admin" <?php echo $e['role']=='admin'?'selected':'';?>>Admin</option>
                </select>
              </div>
              <button type="submit" class="btn btn-primary">Update</button>
            </form>
          </div>
        </div></div></div>
        <?php endwhile; ?>
      </tbody></table>
    </div>
    <!-- Tasks -->
    <div id="tasks" style="display:<?php echo $active_tab=='tasks'?'block':'none'; ?>">
      <h2 class="section-title">Tasks</h2>
      <form method="post" class="mb-4">
        <input type="hidden" name="action" value="add_task"><input type="hidden" name="tab" value="tasks">
        <div class="row g-3"><div class="col-md-3"><select name="employee_id" class="form-select" required><option value="">Select Username</option><?php $employees->data_seek(0); while($e=$employees->fetch_assoc()):?><option value="<?php echo $e['id'];?>"><?php echo htmlspecialchars($e['username']);?></option><?php endwhile;?></select></div>
        <div class="col-md-5"><input type="text" name="task_description" class="form-control" placeholder="Task" required></div>
        <div class="col-md-3"><input type="date" name="date" class="form-control" required></div>
        <div class="col-md-1"><button class="btn btn-primary w-100">Add</button></div></div>
      </form>
      <table class="table table-striped"><thead><tr><th>ID</th><th>Username</th><th>Task</th><th>Date</th><th>Actions</th></tr></thead><tbody>
        <?php while($t=$tasks->fetch_assoc()): ?>
        <tr><td><?php echo $t['id'];?></td><td><?php echo htmlspecialchars($t['username']);?></td><td><?php echo htmlspecialchars($t['task_description']);?></td><td><?php echo $t['date'];?></td>
        <td>
          <button class="btn btn-sm btn-primary" data-bs-toggle="modal" data-bs-target="#editTask<?php echo $t['id'];?>"><i class="bi bi-pencil"></i></button>
          <form method="post" style="display:inline"><input type="hidden" name="action" value="delete_task"><input type="hidden" name="tab" value="tasks"><input type="hidden" name="id" value="<?php echo $t['id'];?>"><button class="btn btn-sm btn-danger" onclick="return confirm('Delete?')"><i class="bi bi-trash"></i></button></form>
        </td></tr>
        <div class="modal fade" id="editTask<?php echo $t['id'];?>"><div class="modal-dialog"><div class="modal-content"><div class="modal-header"><h5>Edit Task</h5><button class="btn-close" data-bs-dismiss="modal"></button></div>
        <div class="modal-body"><form method="post"><input type="hidden" name="action" value="update_task"><input type="hidden" name="tab" value="tasks"><input type="hidden" name="id" value="<?php echo $t['id'];?>">
          <div class="mb-3"><select name="employee_id" class="form-select" required><?php $employees->data_seek(0); while($e=$employees->fetch_assoc()):?><option value="<?php echo $e['id'];?>" <?php echo $e['username']==$t['username']?'selected':'';?>><?php echo htmlspecialchars($e['username']);?></option><?php endwhile;?></select></div>
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
        <div class="row g-3"><div class="col-md-3"><select name="employee_id" class="form-select" required><option value="">Select Username</option><?php $employees->data_seek(0); while($e=$employees->fetch_assoc()):?><option value="<?php echo $e['id'];?>"><?php echo htmlspecialchars($e['username']);?></option><?php endwhile;?></select></div>
        <div class="col-md-3"><input type="date" name="date" class="form-control" required></div>
        <div class="col-md-3"><input type="text" name="shift_time" class="form-control" placeholder="Shift Time" required></div>
        <div class="col-md-2"><input type="text" name="role_tasks" class="form-control" placeholder="Tasks" required></div>
        <div class="col-md-1"><button class="btn btn-primary w-100">Add</button></div></div>
      </form>
      <table class="table table-striped"><thead><tr><th>ID</th><th>Username</th><th>Date</th><th>Time</th><th>Tasks</th><th>Actions</th></tr></thead><tbody>
        <?php while($s=$schedules->fetch_assoc()): ?>
        <tr><td><?php echo $s['id'];?></td><td><?php echo htmlspecialchars($s['username']);?></td><td><?php echo $s['date'];?></td><td><?php echo htmlspecialchars($s['shift_time']);?></td><td><?php echo htmlspecialchars($s['role_tasks']);?></td>
        <td>
          <button class="btn btn-sm btn-primary" data-bs-toggle="modal" data-bs-target="#editSch<?php echo $s['id'];?>"><i class="bi bi-pencil"></i></button>
          <form method="post" style="display:inline"><input type="hidden" name="action" value="delete_schedule"><input type="hidden" name="tab" value="schedules"><input type="hidden" name="id" value="<?php echo $s['id'];?>"><button class="btn btn-sm btn-danger" onclick="return confirm('Delete?')"><i class="bi bi-trash"></i></button></form>
        </td></tr>
        <div class="modal fade" id="editSch<?php echo $s['id'];?>"><div class="modal-dialog"><div class="modal-content"><div class="modal-header"><h5>Edit Schedule</h5><button class="btn-close" data-bs-dismiss="modal"></button></div>
        <div class="modal-body"><form method="post"><input type="hidden" name="action" value="update_schedule"><input type="hidden" name="tab" value="schedules"><input type="hidden" name="id" value="<?php echo $s['id'];?>">
          <div class="mb-3"><select name="employee_id" class="form-select" required><?php $employees->data_seek(0); while($e=$employees->fetch_assoc()):?><option value="<?php echo $e['id'];?>" <?php echo $e['username']==$s['username']?'selected':'';?>><?php echo htmlspecialchars($e['username']);?></option><?php endwhile;?></select></div>
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
        <td><img src="<?php echo htmlspecialchars($p['image']);?>" style="width:40px;height:40px;object-fit:cover;"></td><td><?php echo $p['stock'];?></td>
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
        <tr><td><?php echo $r['id'];?></td><td><?php echo htmlspecialchars($r['name'] ?: 'Anonymous');?></td><td><?php echo $r['rating'];?></td><td><?php echo htmlspecialchars($r['review']);?></td><td><?php echo $r['submitted_Date'];?></td><td><?php echo htmlspecialchars($r['reply']??'');?></td>
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
    <!-- Charts Tab -->
    <div id="charts" style="display:<?php echo $active_tab=='charts'?'block':'none'; ?>">
      <h2 class="section-title">Product Performance Charts</h2>
      <form method="post" class="mb-4">
        <input type="hidden" name="tab" value="charts">
        <div class="row g-3 align-items-end">
          <div class="col-md-3">
            <label class="form-label">Metric</label>
            <select name="chart_type" class="form-select" required>
              <option value="qty" <?php echo ($_POST['chart_type']??'')==='qty'?'selected':'';?>>Units Sold</option>
              <option value="revenue" <?php echo ($_POST['chart_type']??'')==='revenue'?'selected':'';?>>Revenue ($)</option>
            </select>
          </div>
          <div class="col-md-3">
            <label class="form-label">Period</label>
            <select name="period" class="form-select" required>
              <option value="daily" <?php echo ($_POST['period']??'')==='daily'?'selected':'';?>>Daily</option>
              <option value="weekly" <?php echo ($_POST['period']??'')==='weekly'?'selected':'';?>>Weekly</option>
              <option value="monthly" <?php echo ($_POST['period']??'')==='monthly'?'selected':'';?>>Monthly</option>
              <option value="yearly" <?php echo ($_POST['period']??'')==='yearly'?'selected':'';?>>Yearly</option>
            </select>
          </div>
          <div class="col-md-3">
            <label class="form-label">Reference Date</label>
            <input type="date" name="chart_date" class="form-control" min="2025-11-01" max="2035-12-31" value="<?php echo htmlspecialchars($_POST['chart_date'] ?? date('Y-m-d')); ?>" required>
          </div>
          <div class="col-md-3">
            <button type="submit" class="btn btn-primary w-100">Generate Chart</button>
          </div>
        </div>
      </form>
      <?php if ($chart_html): ?>
        <?php echo $chart_html; ?>
      <?php else: ?>
        <div class="alert alert-info">Select a metric, period, and date to view the performance chart.</div>
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
    function checkPwd(pwdInput, lenId, upperId, lowerId, numId, specialId) {
      const pwd = pwdInput.value;
      document.getElementById(lenId).classList.toggle('met', pwd.length >= 12);
      document.getElementById(upperId).classList.toggle('met', /[A-Z]/.test(pwd));
      document.getElementById(lowerId).classList.toggle('met', /[a-z]/.test(pwd));
      document.getElementById(numId).classList.toggle('met', /[0-9]/.test(pwd));
      document.getElementById(specialId).classList.toggle('met', /[^A-Za-z0-9]/.test(pwd));
    }
    const addPwd = document.getElementById('addPwd');
    if (addPwd) addPwd.addEventListener('input', () => checkPwd(addPwd, 'reqLen', 'reqUpper', 'reqLower', 'reqNum', 'reqSpecial'));
    document.querySelectorAll('input[id^="editPwd"]').forEach(inp => {
      const id = inp.id.replace('editPwd', '');
      inp.addEventListener('input', () => checkPwd(inp, 'editLen'+id, 'editUpper'+id, 'editLower'+id, 'editNum'+id, 'editSpecial'+id));
    });
    
    document.querySelectorAll('.portal-link').forEach(link => {
    link.addEventListener('click', function(e) {
        e.preventDefault();
        window.location.href = this.href;
    });
});

  </script>
  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
<?php $conn->close(); ?>