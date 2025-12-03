<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);
session_start();
include 'config.php';
?>
<!DOCTYPE html>
<html lang="en">
<head>
<title>Catalog - Petrongolo Evergreen Plantation</title>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
<link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Oswald:wght@400;700&family=Roboto:wght@400;700&display=swap">
<link rel="stylesheet" href="styles.css">
<style>
    .list-btn {
        position: absolute;
        top: 10px;
        right: 150px;
        z-index: 10;
        background-color: #2c5530;
        color: #fff;
        border: none;
    }
    .out-of-stock {
        opacity: 0.6;
        pointer-events: none;
    }
    .stock-warning {
        color: #d9534f;
        font-weight: bold;
        font-size: 0.9em;
        margin: 8px 0;
    }
    .stock-low {
        color: #f0ad4e;
        font-weight: bold;
        font-size: 0.9em;
        margin: 8px 0;
    }
</style>
</head>
<body>

<div class="banner-wrapper">
    <img src="Banner_Logo.png" class="img-fluid banner" alt="Banner Logo">
    <?php if (isset($_SESSION['user_id']) && $_SESSION['role'] === 'customer'): ?>
        <a href="PEP_CustomerAccount.php" class="btn btn-primary login-btn">My Account</a>
        <a href="PEP_CustomerList.php" class="btn btn-primary list-btn" style="background-color: #2c5530; color: #fff; border: none;">My List</a>
    <?php else: ?>
        <a href="login.php?return=PEP_Catalog.php" class="btn btn-primary login-btn">Login / Sign Up</a>
        <a href="login.php?return=PEP_CustomerList.php" class="btn btn-primary list-btn" style="background-color: #2c5530; color: #fff; border: none;">My List</a>
    <?php endif; ?>
</div>

<div class="navigation-bar">
    <div class="container">
        <div class="d-flex justify-content-between align-items-center">
            <a class="nav-link" href="PEP_Main.php">Home</a>
            <img src="Tree.png" class="tree-divider" alt="Tree Divider">
            <a class="nav-link" href="PEP_AboutUs.php">About</a>
            <img src="Tree.png" class="tree-divider" alt="Tree Divider">
            <a class="nav-link" href="PEP_Reviews.php">Reviews</a>
            <img src="Tree.png" class="tree-divider" alt="Tree Divider">
            <a class="nav-link" href="PEP_VisitUs.php">Visit Us</a>
            <img src="Tree.png" class="tree-divider" alt="Tree Divider">
            <a class="nav-link" href="PEP_ContactUs.php">Contact</a>
        </div>
    </div>
</div>

<section id="catalog" class="py-5">
    <div class="container">
        <h1 class="section-title">Our Catalog</h1>
        <p>Browse our selection of fresh evergreen trees, wreaths, poinsettias, and holiday decorations. Prices are approximate and depend on size/availability. Visit us for the full experience!</p>

        <div class="row">
            <?php
            // Updated query to include stock quantity
            $sql = "SELECT product_id AS id, name, description, price, image_path AS image, stock_quantity 
                    FROM Product 
                    ORDER BY name";
            $result = $conn->query($sql);

            if ($result->num_rows > 0) {
                while ($product = $result->fetch_assoc()) {
                    $inStock = $product['stock_quantity'] > 0;
                    $lowStock = $product['stock_quantity'] > 0 && $product['stock_quantity'] <= 10;

                    echo '<div class="col-md-3 mb-4">
                        <div class="card bg-transparent border-0 product-card ' . (!$inStock ? 'out-of-stock' : '') . '">
                            <img src="' . htmlspecialchars($product['image']) . '" class="card-img-top" alt="' . htmlspecialchars($product['name']) . '">
                            <div class="card-body text-center" style="color: #000;">
                                <h5 class="card-title">' . htmlspecialchars($product['name']) . '</h5>
                                <p class="card-text">' . htmlspecialchars($product['description']) . '</p>
                                <p class="card-text fw-bold">Price: $' . number_format($product['price'], 2) . '</p>';

                    // Stock status message
                    if (!$inStock) {
                        echo '<p class="stock-warning">Out of Stock</p>';
                    } elseif ($lowStock) {
                        echo '<p class="stock-low">Only ' . $product['stock_quantity'] . ' left!</p>';
                    }

                    // Add to List Button Logic
                    if (isset($_SESSION['user_id']) && $_SESSION['role'] === 'customer') {
                        if ($inStock) {
                            echo '<button type="button" class="btn btn-success add-to-list-btn" data-product-id="' . $product['id'] . '">
                                    Add to List
                                  </button>';
                        } else {
                            echo '<button type="button" class="btn btn-secondary" disabled>Out of Stock</button>';
                        }
                    } else {
                        echo '<a href="login.php?return=PEP_Catalog.php" class="btn btn-success">
                                Add to List (Login Required)
                              </a>';
                    }

                    echo '</div></div></div>';
                }
            } else {
                echo '<div class="col-12"><p class="text-center">No products available at this time.</p></div>';
            }
            $conn->close();
            ?>
        </div>
    </div>
</section>

<footer class="text-center">
    <p><a href="https://www.facebook.com/p/Petrongolo-Evergreen-Plantation-100064604442455/" target="_blank" style="color: #fff;">Facebook</a> | Email: info@petrongolo.com | Phone: (609) 567-0336</p>
    <p>&copy; 2025 Petrongolo Evergreen Plantation. All rights reserved.</p>
    <div>
        <a href="PEP_Main.php" style="color: #fff;">Home</a> | 
        <a href="PEP_AboutUs.php" style="color: #fff;">About</a> | 
        <a href="PEP_Catalog.php" style="color: #fff;">Catalog</a> | 
        <a href="PEP_Reviews.php" style="color: #fff;">Reviews</a> | 
        <a href="PEP_VisitUs.php" style="color: #fff;">Visit Us</a> | 
        <a href="PEP_ContactUs.php" style="color: #fff;">Contact</a>
    </div>
</footer>

<script>
// Only enable Add to List for logged-in customers AND items in stock
<?php if (isset($_SESSION['user_id']) && $_SESSION['role'] === 'customer'): ?>
document.querySelectorAll('.add-to-list-btn').forEach(button => {
    button.addEventListener('click', function() {
        if (this.disabled) return; // Extra safety

        const productId = this.getAttribute('data-product-id');
        const formData = new FormData();
        formData.append('action', 'add');
        formData.append('product_id', productId);
        formData.append('quantity', 1);

        fetch('PEP_CustomerList.php', {
            method: 'POST',
            body: formData
        })
        .then(response => response.text())
        .then(data => {
            const message = data.trim();
            if (message.includes('success') || message === '' || message.toLowerCase().includes('added')) {
                alert('Item added to your list!');
                // Optional: Update stock count visually here via another AJAX call if desired
            } else {
                alert(message || 'Could not add item.');
            }
        })
        .catch(error => {
            console.error('Error:', error);
            alert('Error adding item to list.');
        });
    });
});
<?php endif; ?>
</script>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>