<?php
session_start();
include 'config.php'; // DB connection

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'customer') {
  header("Location: PEP_Main.php");
  exit;
}

$user_id = $_SESSION['user_id'];
$success = $error = '';

// Handle Update Quantity
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['action']) && $_POST['action'] == 'update_qty') {
  $item_id = $_POST['item_id'];
  $new_qty = $_POST['quantity'];
  if ($new_qty > 0) {
    $sql = "UPDATE shopping_lists SET quantity = ? WHERE id = ? AND user_id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("iii", $new_qty, $item_id, $user_id);
    $stmt->execute();
    $stmt->close();
    $success = "Quantity updated!";
  } else {
    $error = "Quantity must be greater than 0.";
  }
}

// Handle Remove Item
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['action']) && $_POST['action'] == 'remove_item') {
  $item_id = $_POST['item_id'];
  $sql = "DELETE FROM shopping_lists WHERE id = ? AND user_id = ?";
  $stmt = $conn->prepare($sql);
  $stmt->bind_param("ii", $item_id, $user_id);
  $stmt->execute();
  $stmt->close();
  $success = "Item removed!";
}

// Fetch List
$sql = "SELECT id, product_name, price, quantity FROM shopping_lists WHERE user_id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();

$total = 0;
while ($row = $result->fetch_assoc()) {
  $total += $row['price'] * $row['quantity'];
}
$tax_rate = 0.06625; // NJ sales tax 6.625%
$tax = $total * $tax_rate;
$grand_total = $total + $tax;

$stmt->close();
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

    <table class="table table-bordered">
      <thead>
        <tr>
          <th>Product</th>
          <th>Price</th>
          <th>Quantity</th>
          <th>Subtotal</th>
          <th>Actions</th>
        </tr>
      </thead>
      <tbody>
        <?php 
        $result->data_seek(0); // Reset result pointer
        while ($row = $result->fetch_assoc()): ?>
          <tr>
            <td><?php echo htmlspecialchars($row['product_name']); ?></td>
            <td>$<?php echo number_format($row['price'], 2); ?></td>
            <td>
              <form method="post" class="d-inline">
                <input type="hidden" name="action" value="update_qty">
                <input type="hidden" name="item_id" value="<?php echo $row['id']; ?>">
                <input type="number" name="quantity" value="<?php echo $row['quantity']; ?>" min="1" style="width: 60px;">
                <button type="submit" class="btn btn-sm btn-primary">Update</button>
              </form>
            </td>
            <td>$<?php echo number_format($row['price'] * $row['quantity'], 2); ?></td>
            <td>
              <form method="post" class="d-inline">
                <input type="hidden" name="action" value="remove_item">
                <input type="hidden" name="item_id" value="<?php echo $row['id']; ?>">
                <button type="submit" class="btn btn-sm btn-danger">Remove</button>
              </form>
            </td>
          </tr>
        <?php endwhile; ?>
      </tbody>
    </table>
    <div class="text-end">
      <p>Subtotal: $<?php echo number_format($total, 2); ?></p>
      <p>NJ Sales Tax (6.625%): $<?php echo number_format($tax, 2); ?></p>
      <p><strong>Grand Total: $<?php echo number_format($grand_total, 2); ?></strong></p>
    </div>
  </div>
  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>