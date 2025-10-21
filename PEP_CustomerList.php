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
// Check for product_id column
$check_column = $conn->query("SHOW COLUMNS FROM customer_lists LIKE 'product_id'");
if ($check_column->num_rows == 0) {
  $error = "Database setup error: 'product_id' column missing in customer_lists. Please recreate the table with the provided SQL.";
}
// Handle Add to List (from catalog) - AJAX friendly
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['action']) && $_POST['action'] == 'add') {
  try {
    $product_id = $_POST['product_id'];
    $quantity = $_POST['quantity'] ?? 1;
    $sql = "INSERT INTO customer_lists (user_id, product_id, quantity) VALUES (?, ?, ?) ON DUPLICATE KEY UPDATE quantity = quantity + VALUES(quantity)";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("iii", $user_id, $product_id, $quantity);
    if ($stmt->execute()) {
      echo "Item added to list!";
    } else {
      echo "Error adding item: " . $stmt->error;
    }
    $stmt->close();
  } catch (Exception $e) {
    echo "Error: " . $e->getMessage();
  }
  exit; // End response for AJAX
}
// Handle Update Quantity
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['action']) && $_POST['action'] == 'update') {
  try {
    $item_id = $_POST['item_id'];
    $new_quantity = $_POST['new_quantity'];
    if ($new_quantity > 0) {
      $sql = "UPDATE customer_lists SET quantity = ? WHERE id = ? AND user_id = ?";
      $stmt = $conn->prepare($sql);
      $stmt->bind_param("iii", $new_quantity, $item_id, $user_id);
      if ($stmt->execute()) {
        $success = "Quantity updated!";
      } else {
        $error = "Error updating quantity: " . $stmt->error;
      }
    } else {
      // If quantity <=0, remove
      $sql = "DELETE FROM customer_lists WHERE id = ? AND user_id = ?";
      $stmt = $conn->prepare($sql);
      $stmt->bind_param("ii", $item_id, $user_id);
      if ($stmt->execute()) {
        $success = "Item removed!";
      } else {
        $error = "Error removing item: " . $stmt->error;
      }
    }
    $stmt->close();
  } catch (Exception $e) {
    $error = "Error: " . $e->getMessage();
  }
}
// Handle Remove
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['action']) && $_POST['action'] == 'remove') {
  try {
    $item_id = $_POST['item_id'];
    $sql = "DELETE FROM customer_lists WHERE id = ? AND user_id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("ii", $item_id, $user_id);
    if ($stmt->execute()) {
      $success = "Item removed!";
    } else {
      $error = "Error removing item: " . $stmt->error;
    }
    $stmt->close();
  } catch (Exception $e) {
    $error = "Error: " . $e->getMessage();
  }
}
// Fetch List Items
try {
  $sql = "SELECT cl.id, cl.quantity, p.name, p.price FROM customer_lists cl JOIN products p ON cl.product_id = p.id WHERE cl.user_id = ?";
  $stmt = $conn->prepare($sql);
  $stmt->bind_param("i", $user_id);
  $stmt->execute();
  $result = $stmt->get_result();
  $items = [];
  $total = 0;
  while ($row = $result->fetch_assoc()) {
    $row_total = $row['quantity'] * $row['price'];
    $row['row_total'] = $row_total;
    $total += $row_total;
    $items[] = $row;
  }
  $stmt->close();
} catch (Exception $e) {
  $error = "Error fetching list: " . $e->getMessage();
}
$conn->close();
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <title>My Shopping List - Petrongolo Evergreen Plantation</title>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
  <style>
    body { font-family: 'Roboto', sans-serif; background-color: #f8f9fa; color: #333; }
    .section-title { text-align: center; margin-bottom: 40px; color: #2c5530; font-family: 'Oswald', sans-serif; font-weight: 700; }
  </style>
</head>
<body>
  <div class="container mt-5">
    <a href="PEP_Catalog.php" class="btn btn-secondary mb-4">Back to Catalog</a>
    <h2 class="section-title">My Shopping List</h2>
    <?php if ($success) echo "<div class='alert alert-success'>$success</div>"; ?>
    <?php if ($error) echo "<div class='alert alert-danger'>$error</div>"; ?>
    <?php if (empty($items)): ?>
      <p>Your shopping list is empty. Add items from the catalog!</p>
    <?php else: ?>
      <table class="table table-bordered">
        <thead>
          <tr>
            <th>Product</th>
            <th>Price</th>
            <th>Quantity</th>
            <th>Total</th>
            <th>Actions</th>
          </tr>
        </thead>
        <tbody>
          <?php foreach ($items as $item): ?>
            <tr>
              <td><?php echo htmlspecialchars($item['name']); ?></td>
              <td>$<?php echo number_format($item['price'], 2); ?></td>
              <td>
                <form action="PEP_CustomerList.php" method="post" class="d-inline">
                  <input type="hidden" name="action" value="update">
                  <input type="hidden" name="item_id" value="<?php echo $item['id']; ?>">
                  <input type="number" name="new_quantity" value="<?php echo $item['quantity']; ?>" min="0" style="width: 60px;">
                  <button type="submit" class="btn btn-sm btn-primary">Update</button>
                </form>
              </td>
              <td>$<?php echo number_format($item['row_total'], 2); ?></td>
              <td>
                <form action="PEP_CustomerList.php" method="post" class="d-inline">
                  <input type="hidden" name="action" value="remove">
                  <input type="hidden" name="item_id" value="<?php echo $item['id']; ?>">
                  <button type="submit" class="btn btn-sm btn-danger">Remove</button>
                </form>
              </td>
            </tr>
          <?php endforeach; ?>
        </tbody>
      </table>
      <div class="text-end">
        <p><strong>Total:</strong> $<?php echo number_format($total, 2); ?></p>
      </div>
    <?php endif; ?>
  </div>
  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>