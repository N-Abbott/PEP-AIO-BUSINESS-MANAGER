<?php
// PEP_EmployeePortal.php – FULLY FUNCTIONAL + SECURE LOGIN + ADMIN ACCESS + MESSAGES + SALES + TASKS + SCHEDULE + ERROR-FREE
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);
session_start();

// === SECURE LOGIN CHECK: EMPLOYEE OR ADMIN ONLY ===
if (!isset($_SESSION['role']) || !in_array($_SESSION['role'], ['employee', 'admin'])) {
    $_SESSION['error'] = "You must be logged in to access the employee portal.";
    header("Location: PEP_Main.php");
    exit;
}

include 'config.php'; // <-- $conn = new mysqli(...)

/* --------------------------------------------------------------
   1. ADMIN LOGIN – USES AdminAccount (admin_id, username, password_hash)
   -------------------------------------------------------------- */
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['admin_password'])) {
    $input_password = $_POST['admin_password'] ?? '';
    $username = trim($_POST['admin_username'] ?? '');
    if (!$username) {
        $error = "Username required.";
    } else {
        $stmt = $conn->prepare("SELECT admin_id, password_hash FROM AdminAccount WHERE username = ? LIMIT 1");
        $stmt->bind_param("s", $username);
        $stmt->execute();
        $result = $stmt->get_result();
        if ($result && $result->num_rows > 0) {
            $row = $result->fetch_assoc();
            if (password_verify($input_password, $row['password_hash'])) {
                $_SESSION['role'] = 'admin';
                $_SESSION['user_id'] = $row['admin_id'];
                $_SESSION['admin_username'] = $username;
                header("Location: PEP_Admin.php");
                exit;
            } else {
                $error = "Invalid password.";
            }
        } else {
            $error = "Admin account not found.";
        }
        $stmt->close();
    }
}

/* --------------------------------------------------------------
   2. DETERMINE ACTIVE TAB
   -------------------------------------------------------------- */
$active_tab = $_POST['tab'] ?? $_GET['tab'] ?? 'tasks'; // Support GET & POST

/* --------------------------------------------------------------
   3. TASKS TAB
   -------------------------------------------------------------- */
$tasks_result = null;
if ($active_tab === 'tasks') {
    $employee_id = $_SESSION['user_id'] ?? 0;
    if ($employee_id) {
        $stmt = $conn->prepare("SELECT task_id, task_description, task_date FROM EmployeeTasks WHERE employee_id = ? ORDER BY task_date");
        $stmt->bind_param("i", $employee_id);
        $stmt->execute();
        $tasks_result = $stmt->get_result();
        $stmt->close();
    }
}

/* --------------------------------------------------------------
   4. SCHEDULE TAB
   -------------------------------------------------------------- */
$schedules_result = null;
if ($active_tab === 'schedule') {
    $employee_id = $_SESSION['user_id'] ?? 0;
    if ($employee_id) {
        $stmt = $conn->prepare("SELECT schedule_id, shift_date, shift_time, role_tasks FROM EmployeeSchedules WHERE employee_id = ? ORDER BY shift_date");
        $stmt->bind_param("i", $employee_id);
        $stmt->execute();
        $schedules_result = $stmt->get_result();
        $stmt->close();
    }
}

/* --------------------------------------------------------------
   5. SALES TAB – FULLY SECURE CART + STOCK CHECK + CASH CHECKOUT
   -------------------------------------------------------------- */
$products_result = null;
$cart_total = 0;
$cart_items = [];

if ($active_tab === 'sales') {
    if (!isset($_SESSION['cart'])) {
        $_SESSION['cart'] = [];
    }

    /* ----- ADD TO CART ----- */
    if ($_SERVER["REQUEST_METHOD"] == "POST" && ($_POST['action'] ?? '') == 'add_to_cart') {
        $product_id = (int)($_POST['product_id'] ?? 0);
        $quantity = (int)($_POST['quantity'] ?? 0);
        if ($product_id && $quantity > 0) {
            $stmt = $conn->prepare("SELECT stock_quantity, price FROM Product WHERE product_id = ?");
            $stmt->bind_param("i", $product_id);
            $stmt->execute();
            $res = $stmt->get_result();
            $prod = $res->fetch_assoc();
            $stmt->close();
            if ($prod && $quantity <= $prod['stock_quantity']) {
                $_SESSION['cart'][$product_id] = ($_SESSION['cart'][$product_id] ?? 0) + $quantity;
                $success = "Added to cart.";
            } else {
                $error = "Not enough stock.";
            }
        }
    }

    /* ----- REMOVE FROM CART ----- */
    if ($_SERVER["REQUEST_METHOD"] == "POST" && ($_POST['action'] ?? '') == 'remove_from_cart') {
        $product_id = (int)($_POST['product_id'] ?? 0);
        unset($_SESSION['cart'][$product_id]);
    }

    /* ----- CHECKOUT CASH ----- */
    if ($_SERVER["REQUEST_METHOD"] == "POST" && ($_POST['action'] ?? '') == 'checkout_cash') {
        $customer_email = trim($_POST['customer_email'] ?? '');
        if (!empty($_SESSION['cart'])) {

            // Backup before clearing
             $_SESSION['cart_backup'] = $_SESSION['cart'];

            $conn->begin_transaction();
            try {
                $stmt = $conn->prepare("INSERT INTO Sales (payment_method, sale_date) VALUES ('cash', NOW())");
                $stmt->execute();
                $sale_id = $conn->insert_id;
                $stmt->close();

                foreach ($_SESSION['cart'] as $pid => $qty) {
                    $stmt = $conn->prepare("SELECT price, stock_quantity FROM Product WHERE product_id = ? FOR UPDATE");
                    $stmt->bind_param("i", $pid);
                    $stmt->execute();
                    $res = $stmt->get_result();
                    $p = $res->fetch_assoc();
                    $stmt->close();

                    if ($p && $qty <= $p['stock_quantity']) {
                        $line_total = $p['price'] * $qty;
                        $stmt = $conn->prepare("INSERT INTO SaleItems (sale_id, product_id, quantity, line_total) VALUES (?, ?, ?, ?)");
                        $stmt->bind_param("iiid", $sale_id, $pid, $qty, $line_total);
                        $stmt->execute();
                        $stmt->close();

                        $stmt = $conn->prepare("UPDATE Product SET stock_quantity = stock_quantity - ? WHERE product_id = ?");
                        $stmt->bind_param("ii", $qty, $pid);
                        $stmt->execute();
                        $stmt->close();
                    } else {
                        throw new Exception("Stock error for product $pid");
                    }
                }
                $conn->commit();
                $success = "Cash sale completed.";
                $_SESSION['cart'] = [];
                // ========================= SEND CASH RECEIPT ============================
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
            <th>Product</th>
            <th>Quantity</th>
            <th>Price</th>
            <th>Tax</th>
            <th>Subtotal</th>
          </tr>";

    $total_all = 0;

    // Restore original cart (saved before clearing)
    $cart_items_email = [];
    foreach ($_SESSION['cart_backup'] ?? [] as $pid => $qty) {
        $cart_items_email[$pid] = ($cart_items_email[$pid] ?? 0) + $qty;
    }

    foreach ($cart_items_email as $pid => $qty) {
        $stmt = $conn->prepare("SELECT name, price FROM Product WHERE product_id = ?");
        $stmt->bind_param("i", $pid);
        $stmt->execute();
        $res = $stmt->get_result();
        $p = $res->fetch_assoc();
        $stmt->close();

        if ($p) {
            $line_total = $p['price'] * $qty;
            $total_all += $line_total;

            $message .= "<tr>
                <td>" . htmlspecialchars($p['name']) . "</td>
                <td>" . intval($qty) . "</td>
                <td>$" . number_format($p['price'], 2) . "</td>
                <td>$0.00</td>
                <td>$" . number_format($line_total, 2) . "</td>
            </tr>";
        }
    }

    $message .= "
        </table>

        <p style='margin-top:15px; font-size:16px;'><strong>Total:</strong> $" . number_format($total_all, 2) . "</p>
        <p><strong>Payment Method:</strong> Cash</p>
        <p><strong>Date:</strong> " . date("Y-m-d H:i:s") . "</p>

        <p style='color:#2c5530;'>We appreciate your business!</p>
        <hr>

        <p style='font-size:0.9em;color:#555;'>Petrongolo Evergreen Plantation<br>
        noreply@petrongoloevergreenplantation.com</p>

      </div>
    </body>
    </html>";

    $headers  = "From: noreply@petrongoloevergreenplantation.com\r\n";
    $headers .= "Reply-To: noreply@petrongoloevergreenplantation.com\r\n";
    $headers .= "Content-type: text/html; charset=UTF-8\r\n";

    mail($customer_email, $subject, $message, $headers);
}

            } catch (Exception $e) {
                $conn->rollback();
                $error = "Transaction failed: " . $e->getMessage();
            }
        } else {
            $error = "Cart is empty.";
        }
    }

/* ----- CHECKOUT VENMO CONFIRM ----- */
if ($_SERVER["REQUEST_METHOD"] == "POST" && ($_POST['action'] ?? '') == 'confirm_venmo') {

    $customer_email = trim($_POST['customer_email'] ?? '');

    if (!empty($_SESSION['cart'])) {

        // Backup cart for receipt
        $_SESSION['cart_backup'] = $_SESSION['cart'];

        $conn->begin_transaction();

        try {
            // Create sale
            $stmt = $conn->prepare("INSERT INTO Sales (payment_method, sale_date) VALUES ('venmo', NOW())");
            $stmt->execute();
            $sale_id = $conn->insert_id;
            $stmt->close();

            // Process items
            foreach ($_SESSION['cart'] as $pid => $qty) {
                $stmt = $conn->prepare("SELECT price, stock_quantity FROM Product WHERE product_id = ? FOR UPDATE");
                $stmt->bind_param("i", $pid);
                $stmt->execute();
                $res = $stmt->get_result();
                $p = $res->fetch_assoc();
                $stmt->close();

                if ($p && $qty <= $p['stock_quantity']) {

                    $line_total = $p['price'] * $qty;

                    // Insert SaleItems row
                    $stmt = $conn->prepare("INSERT INTO SaleItems (sale_id, product_id, quantity, line_total) VALUES (?, ?, ?, ?)");
                    $stmt->bind_param("iiid", $sale_id, $pid, $qty, $line_total);
                    $stmt->execute();
                    $stmt->close();

                    // Deduct stock
                    $stmt = $conn->prepare("UPDATE Product SET stock_quantity = stock_quantity - ? WHERE product_id = ?");
                    $stmt->bind_param("ii", $qty, $pid);
                    $stmt->execute();
                    $stmt->close();

                } else {
                    throw new Exception("Insufficient stock for product $pid");
                }
            }

            $conn->commit();

            /* ---------------------------------------------
               SEND VENMO RECEIPT EMAIL (MATCH FORMAT)
            ---------------------------------------------- */
            if (!empty($customer_email)) {

                $subject = "Your Purchase Receipt from Petrongolo Evergreen Plantation";

                $message = "
                <html>
                <body style='font-family: Arial, sans-serif; background:#f8f9fa; padding:20px;'>
                  <div style='max-width:600px; margin:auto; background:#fff; border:1px solid #ddd; border-radius:10px; padding:20px;'>

                    <h2 style='color:#2c5530;'>Thank you for your purchase!</h2>

                    <table border='1' cellpadding='8' cellspacing='0' width='100%' style='border-collapse:collapse;'>
                      <tr style='background:#2c5530; color:white;'>
                        <th>Product</th>
                        <th>Qty</th>
                        <th>Price</th>
                        <th>Tax</th>
                        <th>Subtotal</th>
                      </tr>";

                $total_all = 0;

                foreach ($_SESSION['cart_backup'] as $pid => $qty) {
                    $stmt = $conn->prepare("SELECT name, price FROM Product WHERE product_id = ?");
                    $stmt->bind_param("i", $pid);
                    $stmt->execute();
                    $res = $stmt->get_result();
                    $p = $res->fetch_assoc();
                    $stmt->close();

                    if ($p) {
                        $line_total = $p['price'] * $qty;
                        $total_all += $line_total;

                        $message .= "
                        <tr>
                          <td>".htmlspecialchars($p['name'])."</td>
                          <td>$qty</td>
                          <td>$".number_format($p['price'],2)."</td>
                          <td>$0.00</td>
                          <td>$".number_format($line_total,2)."</td>
                        </tr>";
                    }
                }

                $message .= "
                    </table>
                    <p><strong>Total:</strong> $".number_format($total_all,2)."</p>
                    <p><strong>Payment Method:</strong> Venmo</p>
                    <p><strong>Date:</strong> ".date("Y-m-d H:i:s")."</p>

                    <p style='color:#2c5530;'>We appreciate your business!</p>

                  </div>
                </body>
                </html>";

                $headers  = "From: noreply@petrongoloevergreenplantation.com\r\n";
                $headers .= "Content-Type: text/html; charset=UTF-8\r\n";

                mail($customer_email, $subject, $message, $headers);
            }

            $_SESSION['cart'] = [];
            $success = "Transaction completed with Venmo.";

        } catch (Exception $e) {
            $conn->rollback();
            $error = "Error: " . $e->getMessage();
        }

    } else {
        $error = "Cart is empty.";
    }
}

    /* ----- FETCH PRODUCTS ----- */
    $products_result = $conn->query("SELECT product_id AS id, name, price, stock_quantity FROM Product WHERE stock_quantity > 0 ORDER BY name");

    /* ----- BUILD CART DISPLAY ----- */
    if (!empty($_SESSION['cart'])) {
        foreach ($_SESSION['cart'] as $pid => $qty) {
            $stmt = $conn->prepare("SELECT name, price FROM Product WHERE product_id = ?");
            $stmt->bind_param("i", $pid);
            $stmt->execute();
            $res = $stmt->get_result();
            $p = $res->fetch_assoc();
            $stmt->close();
            if ($p) {
                $cart_items[] = [
                    'id' => $pid,
                    'name' => $p['name'],
                    'qty' => $qty,
                    'price' => $p['price'],
                    'subtotal' => $p['price'] * $qty
                ];
                $cart_total += $p['price'] * $qty;
            }
        }
    }
}

/* --------------------------------------------------------------
   6. MESSAGES TAB – FULLY SECURE + SEARCH + SORT + REPLY + DELETE
   -------------------------------------------------------------- */
$messages_result = null;
$search_term = '';
$sort_order = 'DESC';

if ($active_tab === 'messages') {
    $search_term = trim($_POST['search'] ?? $_GET['search'] ?? '');
    $sort_order = strtoupper($_POST['sort'] ?? $_GET['sort'] ?? 'DESC');
    $sort_order = ($sort_order === 'ASC') ? 'ASC' : 'DESC';

    $sql = "SELECT contact_id, name, email, message,
                   DATE_FORMAT(created_at, '%Y-%m-%d %H:%i') AS sent_at,
                   replied, reply_date
            FROM Contact
            WHERE 1=1";
    $params = [];
    $types = '';
    if ($search_term !== '') {
        $sql .= " AND (name LIKE ? OR email LIKE ? OR message LIKE ?)";
        $like = '%' . $search_term . '%';
        $params[] = $like; $params[] = $like; $params[] = $like;
        $types .= 'sss';
    }
    $sql .= " ORDER BY created_at $sort_order";

    $stmt = $conn->prepare($sql);
    if (!empty($params)) {
        $stmt->bind_param($types, ...$params);
    }
    $stmt->execute();
    $messages_result = $stmt->get_result();
    $stmt->close();

    // MARK AS REPLIED
    if ($_SERVER["REQUEST_METHOD"] == "POST" && ($_POST['action'] ?? '') == 'mark_replied') {
        $cid = (int)($_POST['contact_id'] ?? 0);
        $stmt = $conn->prepare("UPDATE Contact SET replied = 1, reply_date = NOW() WHERE contact_id = ?");
        $stmt->bind_param("i", $cid);
        $stmt->execute();
        $stmt->close();
        $redirect = "PEP_EmployeePortal.php?tab=messages";
        if ($search_term) $redirect .= "&search=" . urlencode($search_term);
        if ($sort_order !== 'DESC') $redirect .= "&sort=" . $sort_order;
        header("Location: $redirect");
        exit;
    }

    // DELETE MESSAGE
    if ($_SERVER["REQUEST_METHOD"] == "POST" && ($_POST['action'] ?? '') == 'delete_message') {
        $cid = (int)($_POST['contact_id'] ?? 0);
        $stmt = $conn->prepare("DELETE FROM Contact WHERE contact_id = ?");
        $stmt->bind_param("i", $cid);
        $stmt->execute();
        $stmt->close();
        $redirect = "PEP_EmployeePortal.php?tab=messages";
        if ($search_term) $redirect .= "&search=" . urlencode($search_term);
        if ($sort_order !== 'DESC') $redirect .= "&sort=" . $sort_order;
        header("Location: $redirect");
        exit;
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <title>Employee Portal – Petrongolo Evergreen Plantation</title>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Oswald:wght@400;700&family=Roboto:wght@400;700&display=swap">
    <link rel="icon" type="image/png" href="Tree.png">
    <link rel="stylesheet" href="styles.css">
    <style>
        html,body{height:100%;margin:0;background:#f8f9fa;font-family:'Roboto',sans-serif;}
        .navbar{background:#2c5530;box-shadow:0 2px 4px rgba(0,0,0,.1);}
        .navbar-brand,.nav-link{color:#fff !important;}
        .sidebar{position:fixed;right:0;top:0;width:250px;height:100vh;background:#2c5530;padding:20px;overflow-y:auto;box-shadow:-2px 0 5px rgba(0,0,0,.1);}
        .sidebar h4{color:#fff;margin-bottom:20px;}
        .sidebar .nav-link{color:#fff;padding:10px;border-radius:5px;margin-bottom:10px;font-weight:600;transition:.3s;}
        .sidebar .nav-link:hover,.sidebar .nav-link.active{background:#5c8c61;color:#fff !important;}
        .content{margin-right:250px;padding:20px;}
        .section-title{text-align:center;margin:40px 0;color:#2c5530;font-family:'Oswald',sans-serif;font-weight:700;}
        .table th{background:#2c5530;color:#fff;}
        .btn-primary{background:#2c5530;border:none;}
        .btn-primary:hover{background:#5c8c61;}
        .sidebar .tab-form button{background:none;border:none;color:inherit;width:100%;text-align:left;padding:10px;border-radius:5px;}
        .sidebar .tab-form button:hover,.sidebar .tab-form button.active{background:#5c8c61;color:#fff !important;}
    </style>
</head>
<body>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>

<?php if (!empty($success)): ?>
    <div class="alert alert-success alert-dismissible fade show position-fixed top-0 start-50 translate-middle-x" style="z-index: 9999; margin-top: 1rem;" role="alert">
        <?= htmlspecialchars($success) ?>
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
<?php endif; ?>
<?php if (!empty($error)): ?>
    <div class="alert alert-danger alert-dismissible fade show position-fixed top-0 start-50 translate-middle-x" style="z-index: 9999; margin-top: 1rem;" role="alert">
        <?= htmlspecialchars($error) ?>
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
<?php endif; ?>

<nav class="navbar navbar-expand-lg">
    <div class="container-fluid">
        <a class="navbar-brand" href="#">Employee Portal</a>
    </div>
</nav>

<!-- ====================== SIDEBAR ====================== -->
<div class="sidebar">
    <h4>Options</h4>
    <ul class="nav flex-column">
        <li class="nav-item">
            <form method="post" class="tab-form">
                <input type="hidden" name="tab" value="tasks">
                <button type="submit" class="nav-link <?= $active_tab==='tasks'?'active':'' ?>">
                    <i class="bi bi-check2-square me-2"></i>Tasks
                </button>
            </form>
        </li>
        <li class="nav-item">
            <form method="post" class="tab-form">
                <input type="hidden" name="tab" value="schedule">
                <button type="submit" class="nav-link <?= $active_tab==='schedule'?'active':'' ?>">
                    <i class="bi bi-calendar-event me-2"></i>Schedule
                </button>
            </form>
        </li>
        <li class="nav-item">
            <form method="post" class="tab-form">
                <input type="hidden" name="tab" value="sales">
                <button type="submit" class="nav-link <?= $active_tab==='sales'?'active':'' ?>">
                    <i class="bi bi-cart me-2"></i>Sales
                </button>
            </form>
        </li>
        <li class="nav-item">
            <form method="post" class="tab-form">
                <input type="hidden" name="tab" value="messages">
                <button type="submit" class="nav-link <?= $active_tab==='messages'?'active':'' ?>">
                    <i class="bi bi-envelope me-2"></i>Messages
                </button>
            </form>
        </li>
        <?php if ($_SESSION['role'] === 'admin'): ?>
        <li class="nav-item">
            <a class="nav-link" href="PEP_Admin.php">
                <i class="bi bi-shield-lock me-2"></i>Admin Panel
            </a>
        </li>
        <?php else: ?>
        <li class="nav-item">
            <a class="nav-link" href="#" data-bs-toggle="modal" data-bs-target="#adminLoginModal">
                <i class="bi bi-lock me-2"></i>Admin Access
            </a>
        </li>
        <?php endif; ?>
        <li class="nav-item">
            <a class="nav-link" href="logout.php">
                <i class="bi bi-box-arrow-left me-2"></i>Logout
            </a>
        </li>
    </ul>
</div>

<div class="content">

    <!-- ==================== TASKS TAB ==================== -->
    <div id="tasks" style="display:<?= $active_tab==='tasks'?'block':'none' ?>;">
        <h2 class="section-title">Your Tasks</h2>
        <?php if ($tasks_result && $tasks_result->num_rows > 0): ?>
            <div class="table-responsive">
                <table class="table table-striped table-hover">
                    <thead><tr><th>Task</th><th>Due Date</th></tr></thead>
                    <tbody>
                        <?php while ($row = $tasks_result->fetch_assoc()): ?>
                            <tr>
                                <td><?= htmlspecialchars($row['task_description']) ?></td>
                                <td><?= htmlspecialchars($row['task_date']) ?></td>
                            </tr>
                        <?php endwhile; ?>
                    </tbody>
                </table>
            </div>
        <?php else: ?>
            <p class="text-center text-muted">No tasks assigned yet.</p>
        <?php endif; ?>
    </div>

    <!-- ==================== SCHEDULE TAB ==================== -->
    <div id="schedule" style="display:<?= $active_tab==='schedule'?'block':'none' ?>;">
        <h2 class="section-title">Your Schedule</h2>
        <?php if ($schedules_result && $schedules_result->num_rows > 0): ?>
            <div class="table-responsive">
                <table class="table table-striped table-hover">
                    <thead><tr><th>Date</th><th>Shift Time</th><th>Role/Tasks</th></tr></thead>
                    <tbody>
                        <?php while ($row = $schedules_result->fetch_assoc()): ?>
                            <tr>
                                <td><?= htmlspecialchars($row['shift_date']) ?></td>
                                <td><?= htmlspecialchars($row['shift_time']) ?></td>
                                <td><?= htmlspecialchars($row['role_tasks']) ?></td>
                            </tr>
                        <?php endwhile; ?>
                    </tbody>
                </table>
            </div>
        <?php else: ?>
            <p class="text-center text-muted">No schedule assigned yet.</p>
        <?php endif; ?>
    </div>

    <!-- ==================== SALES TAB ==================== -->
    <div id="sales" style="display:<?= $active_tab==='sales'?'block':'none' ?>;">
        <h2 class="section-title">Input Sales</h2>

        <h4 class="mt-4">Available Products</h4>
        <div class="table-responsive">
            <table class="table table-striped align-middle">
                <thead class="table-dark">
                    <tr>
                        <th>Name</th>
                        <th class="text-end">Price</th>
                        <th class="text-center">Stock</th>
                        <th class="text-center">Qty</th>
                        <th class="text-center">Add</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if ($products_result && $products_result->num_rows > 0): ?>
                        <?php while ($p = $products_result->fetch_assoc()): ?>
                            <tr>
                                <form method="post" class="w-100">
                                    <input type="hidden" name="tab" value="sales">
                                    <input type="hidden" name="action" value="add_to_cart">
                                    <input type="hidden" name="product_id" value="<?= $p['id'] ?>">
                                    <td><?= htmlspecialchars($p['name']) ?></td>
                                    <td class="text-end fw-semibold">$<?= number_format($p['price'],2) ?></td>
                                    <td class="text-center"><?= $p['stock_quantity'] ?></td>
                                    <td class="text-center">
                                        <input type="number" name="quantity" min="1" max="<?= $p['stock_quantity'] ?>" value="1" class="form-control form-control-sm w-75 mx-auto">
                                    </td>
                                    <td class="text-center">
                                        <button type="submit" class="btn btn-primary btn-sm">
                                            <i class="bi bi-cart-plus"></i>
                                        </button>
                                    </td>
                                </form>
                            </tr>
                        <?php endwhile; ?>
                    <?php else: ?>
                        <tr><td colspan="5" class="text-center text-muted">No products in stock.</td></tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>

        <h4 class="mt-5">Current Cart</h4>
        <div class="table-responsive">
            <table class="table table-striped align-middle">
                <thead class="table-dark">
                    <tr>
                        <th>Name</th>
                        <th class="text-center">Qty</th>
                        <th class="text-end">Price</th>
                        <th class="text-end">Subtotal</th>
                        <th class="text-center">Remove</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (!empty($cart_items)): ?>
                        <?php foreach ($cart_items as $item): ?>
                            <tr>
                                <td><?= htmlspecialchars($item['name']) ?></td>
                                <td class="text-center"><?= $item['qty'] ?></td>
                                <td class="text-end">$<?= number_format($item['price'],2) ?></td>
                                <td class="text-end">$<?= number_format($item['subtotal'],2) ?></td>
                                <td class="text-center">
                                    <form method="post" style="display:inline;">
                                        <input type="hidden" name="tab" value="sales">
                                        <input type="hidden" name="action" value="remove_from_cart">
                                        <input type="hidden" name="product_id" value="<?= $item['id'] ?>">
                                        <button type="submit" class="btn btn-danger btn-sm">
                                            <i class="bi bi-trash"></i>
                                        </button>
                                    </form>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                        <tr class="table-secondary">
                            <td colspan="3" class="text-end fw-bold">Total</td>
                            <td class="text-end fw-bold">$<?= number_format($cart_total,2) ?></td>
                            <td></td>
                        </tr>
                    <?php else: ?>
                        <tr><td colspan="5" class="text-center text-muted">Cart is empty.</td></tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>

        <?php if (!empty($cart_items)): ?>
            <div class="mt-4 text-center">
                <button class="btn btn-primary btn-lg" data-bs-toggle="modal" data-bs-target="#checkoutModal">
                    <i class="bi bi-credit-card me-2"></i>Checkout
                </button>
            </div>
        <?php endif; ?>
    </div>

    <!-- ==================== MESSAGES TAB ==================== -->
    <div id="messages" style="display:<?= $active_tab==='messages'?'block':'none' ?>;">
        <h2 class="section-title">Customer Messages</h2>

        <div class="row mb-3">
            <div class="col-md-6">
                <form method="post" class="d-flex gap-2">
                    <input type="hidden" name="tab" value="messages">
                    <input type="text" name="search" class="form-control" placeholder="Search name / email / message..." value="<?= htmlspecialchars($search_term) ?>">
                    <button type="submit" class="btn btn-outline-secondary"><i class="bi bi-search"></i></button>
                </form>
            </div>
            <div class="col-md-6 text-end">
                <form method="post" class="d-inline">
                    <input type="hidden" name="tab" value="messages">
                    <?php if ($search_term): ?>
                        <input type="hidden" name="search" value="<?= htmlspecialchars($search_term) ?>">
                    <?php endif; ?>
                    <button name="sort" value="<?= $sort_order==='DESC'?'ASC':'DESC' ?>" class="btn btn-outline-secondary">
                        Sort <?= $sort_order==='DESC'?'↑ Ascending':'↓ Descending' ?>
                    </button>
                </form>
            </div>
        </div>

        <div class="table-responsive">
            <table class="table table-striped table-hover align-middle">
                <thead class="table-dark">
                    <tr>
                        <th>Sent</th>
                        <th>Name</th>
                        <th>Email</th>
                        <th>Message</th>
                        <th>Status</th>
                        <th width="180">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if ($messages_result && $messages_result->num_rows > 0): ?>
                        <?php while ($m = $messages_result->fetch_assoc()): ?>
                            <tr <?= $m['replied']?'class="table-success"':'' ?>>
                                <td><?= htmlspecialchars($m['sent_at']) ?></td>
                                <td><?= htmlspecialchars($m['name']) ?></td>
                                <td><?= htmlspecialchars($m['email']) ?></td>
                                <td class="text-break" style="max-width:300px;"><?= nl2br(htmlspecialchars($m['message'])) ?></td>
                                <td>
                                    <?php if ($m['replied']): ?>
                                        <span class="badge bg-success">Replied</span>
                                        <small class="d-block text-muted">
                                            <?= $m['reply_date'] ? date('M j, Y H:i', strtotime($m['reply_date'])) : '' ?>
                                        </small>
                                    <?php else: ?>
                                        <span class="badge bg-warning">Pending</span>
                                    <?php endif; ?>
                                </td>
                                <td class="text-center">
                                    <a href="mailto:<?= urlencode($m['email']) ?>?subject=Re:%20Contact%20from%20Petrongolo%20Evergreen&body=Hello%20<?= urlencode($m['name']) ?>%2C%0A%0A"
                                       class="btn btn-sm btn-primary" title="Reply via email">
                                        <i class="bi bi-reply"></i>
                                    </a>
                                    <?php if (!$m['replied']): ?>
                                        <form method="post" class="d-inline" onsubmit="return confirm('Mark as replied?');">
                                            <input type="hidden" name="tab" value="messages">
                                            <input type="hidden" name="action" value="mark_replied">
                                            <input type="hidden" name="contact_id" value="<?= $m['contact_id'] ?>">
                                            <button type="submit" class="btn btn-sm btn-success" title="Mark replied">
                                                <i class="bi bi-check-lg"></i>
                                            </button>
                                        </form>
                                    <?php endif; ?>
                                    <form method="post" class="d-inline" onsubmit="return confirm('Delete permanently?');">
                                        <input type="hidden" name="tab" value="messages">
                                        <input type="hidden" name="action" value="delete_message">
                                        <input type="hidden" name="contact_id" value="<?= $m['contact_id'] ?>">
                                        <button type="submit" class="btn btn-sm btn-danger" title="Delete">
                                            <i class="bi bi-trash"></i>
                                        </button>
                                    </form>
                                </td>
                            </tr>
                        <?php endwhile; ?>
                    <?php else: ?>
                        <tr><td colspan="6" class="text-center text-muted">No messages found.</td></tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>

</div>

<!-- ==================== ADMIN LOGIN MODAL ==================== -->
<?php if ($_SESSION['role'] !== 'admin'): ?>
<div class="modal fade" id="adminLoginModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Admin Access</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <form method="post">
                    <input type="hidden" name="tab" value="<?= $active_tab ?>">
                    <div class="mb-3">
                        <label class="form-label">Username</label>
                        <input type="text" class="form-control" name="admin_username" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Password</label>
                        <input type="password" class="form-control" name="admin_password" required>
                    </div>
                    <button type="submit" class="btn btn-primary w-100">Login</button>
                </form>
            </div>
        </div>
    </div>
</div>
<?php endif; ?>

<!-- ==================== CHECKOUT MODAL ==================== -->
<div class="modal fade" id="checkoutModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Checkout Options</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <form method="post">
                    <input type="hidden" name="tab" value="sales">
                    <div class="mb-3">
                        <label class="form-label">Customer Email (optional)</label>
                        <input type="email" class="form-control" name="customer_email" placeholder="Send receipt">
                    </div>
                    <input type="hidden" name="action" value="checkout_cash">
                    <button type="submit" class="btn btn-primary w-100 mb-2">
                        <i class="bi bi-cash me-2"></i>Cash
                    </button>
                </form>
                <button class="btn btn-primary w-100" data-bs-toggle="collapse" data-bs-target="#venmoCollapse">
                    <i class="bi bi-phone me-2"></i>Venmo
                </button>
                <div class="collapse mt-3" id="venmoCollapse">
                    <p>Scan the QR code to pay via Venmo.</p>
                    <img src="pictures/QR_code_for_mobile_English_Wikipedia.svg.png" alt="Venmo QR" class="img-fluid">
                    <form method="post" class="mt-3">
                        <input type="hidden" name="tab" value="sales">
                        <input type="hidden" name="action" value="confirm_venmo">
                        <input type="hidden" name="customer_email" id="hiddenVenmoEmail">
                        <button type="submit" class="btn btn-success w-100" onclick="return confirm('Confirm Venmo payment received?');">
                            <i class="bi bi-check-circle me-2"></i>Confirm Payment
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
    // Sync email from cash to venmo
    document.addEventListener('input', function(e) {
        if (e.target && e.target.name === 'customer_email') {
            const venmoEmail = document.getElementById('hiddenVenmoEmail');
            if (venmoEmail) venmoEmail.value = e.target.value;
        }
    });
</script>
</body>
</html>
<?php $conn->close(); ?>