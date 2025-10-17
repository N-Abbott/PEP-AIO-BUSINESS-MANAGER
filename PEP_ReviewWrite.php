<?php 
session_start();
include 'config.php';

// Ensure the user is logged in
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'customer') {
    echo "<script>
            alert('You must be logged in to submit a review.');
            window.location.href='PEP_Reviews.html';
          </script>";
    exit;
}

// Initialize feedback messages
$success = $error = "";

// Handle review submission
if ($_SERVER["REQUEST_METHOD"] === "POST" && isset($_POST['action']) && $_POST['action'] === 'submit_review') {
    #$name = ($_SESSION['fname'] ?? '') . ' ' . ($_SESSION['lname'] ?? '');
    $name = trim($_POST['name'] ?? '');
    $rating = intval($_POST['rating'] ?? 0);
    $review = trim($_POST['comment'] ?? '');
    $submitted_Date = date('Y-m-d H:i:s');
    
    echo "<pre style='background:#fee;padding:10px;border:1px solid #f00;'>";
    echo "DEBUG:\n";
    echo "Name: " . var_export($_POST['name'] ?? null, true) . "\n";
    echo "Rating: " . var_export($_POST['rating'] ?? null, true) . "\n";
    echo "Review: " . var_export($_POST['comment'] ?? null, true) . "\n";
    echo "</pre>";

    // Validate input
    if (empty($name) || empty($rating) || empty($review)) {
        $error = "All fields are required.";
    } else {
        $stmt = $conn->prepare("INSERT INTO review (name, rating, review, submitted_Date) VALUES (?, ?, ?, NOW())");
        $stmt->bind_param("sis", $name, $rating, $review);

        if ($stmt->execute()) {
            $success = "Review submitted successfully!";
            echo "<script>
                    alert('✅ Review submitted successfully!');
                    window.location.href='PEP_Reviews.html';
                  </script>";
            exit;
        } else {
            $error = "Error submitting review: " . htmlspecialchars($stmt->error);
        }

        $stmt->close();
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <title>Write a Review - Petrongolo Evergreen Plantation</title>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-QWTKZyjpPEjISv5WaRU9OFeRpok6YctnYmDr5pNlyT2bRjXh0JMhjY6hW+ALEwIH" crossorigin="anonymous">
  <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Oswald:wght@400;700&family=Roboto:wght@400;700&display=swap">
  <style>
    html, body {
      width: 100%;
      overflow-x: hidden;
      scroll-behavior: smooth;
    }
    body {
      font-family: 'Roboto', sans-serif;
      background-color: #f8f9fa;
      color: #333;
    }
    .banner {
      width: 100%;
      height: auto;
    }
    .navigation-bar {
      background-color: #2c5530;
      padding: 10px 0;
    }
    .navigation-bar .nav-link {
      color: #fff !important;
      font-weight: 700;
      margin: 0 20px;
      text-decoration: underline;
    }
    .navigation-bar .tree-divider {
      height: 20px;
      width: 20px;
      border-radius: 50%;
      margin: 0 10px;
      vertical-align: middle;
    }
    .section-title {
      text-align: center;
      margin-bottom: 40px;
      color: #2c5530;
      font-family: 'Oswald', sans-serif;
      font-weight: 700;
    }
    footer {
      background-color: #2c5530;
      color: #fff;
      padding: 20px 0;
    }
    form .form-control {
      background-color: #fff;
      color: #333;
      border: 1px solid #e9ecef;
    }
  </style>
</head>
<body>
  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js" integrity="sha384-YvpcrYf0tY3lHB60NNkmXc5s9fDVZLESaAA55NDzOxhy9GkcIdslK1eN7N6jIeHz" crossorigin="anonymous"></script>
  <img src="Banner_Logo.png" class="img-fluid banner" alt="Banner Logo">
  <div class="navigation-bar">
    <div class="container">
      <div class="d-flex justify-content-between align-items-center">
        <a class="nav-link" href="PEP_Main.html">Home</a>
        <img src="Tree.png" class="tree-divider" alt="Tree Divider">
        <a class="nav-link" href="PEP_AboutUs.html">About</a>
        <img src="Tree.png" class="tree-divider" alt="Tree Divider">
        <a class="nav-link" href="PEP_Catalog.html">Catalog</a>
        <img src="Tree.png" class="tree-divider" alt="Tree Divider">
        <a class="nav-link" href="PEP_Reviews.html">Reviews</a>
        <img src="Tree.png" class="tree-divider" alt="Tree Divider">
        <a class="nav-link" href="PEP_VisitUs.html">Visit Us</a>
        <img src="Tree.png" class="tree-divider" alt="Tree Divider">
        <a class="nav-link" href="PEP_ContactUs.html">Contact</a>
      </div>
    </div>
  </div>
  <section id="write-review" class="py-5">
    <div class="container">
      <h1 class="section-title">Write Your Review</h1>
<form method="POST" action="">
  <input type="hidden" name="action" value="submit_review">

  <div class="mb-3">
    <label for="name" class="form-label">Your Name</label>
    <input type="text" class="form-control" id="name" name="name"
           value="<?php echo htmlspecialchars($_SESSION['username'] ?? ''); ?>"
           placeholder="Enter your name" required>
  </div>

  <div class="mb-3">
    <label for="rating" class="form-label">Rating</label>
    <select class="form-select" id="rating" name="rating" required>
      <option value="5">5 Stars</option>
      <option value="4">4 Stars</option>
      <option value="3">3 Stars</option>
      <option value="2">2 Stars</option>
      <option value="1">1 Star</option>
    </select>
  </div>

  <div class="mb-3">
    <label for="comment" class="form-label">Your Review</label>
    <textarea class="form-control" id="comment" name="comment" rows="5"
              placeholder="Share your experience..." required></textarea>
  </div>

  <button type="submit" class="btn btn-primary">Publish</button>
</form>

<?php if (!empty($error)) echo "<p class='text-danger'>$error</p>"; ?>
<?php if (!empty($success)) echo "<p class='text-success'>$success</p>"; ?>
      <p class="mt-3 text-muted">Your review will be moderated before appearing on the site.</p>
    </div>
  </section>
  <footer class="text-center">
    <p><a href="https://www.facebook.com/p/Petrongolo-Evergreen-Plantation-100064604442455/" target="_blank" style="color: #fff;">Facebook</a> | Email: info@petrongolo.com | Phone: (609) 567-0336</p>
    <p>&copy; 2025 Petrongolo Evergreen Plantation. All rights reserved.</p>
    <div>
      <a href="PEP_Main.html" style="color: #fff;">Home</a> | <a href="PEP_AboutUs.html" style="color: #fff;">About</a> | <a href="PEP_Catalog.html" style="color: #fff;">Catalog</a> | <a href="PEP_Reviews.html" style="color: #fff;">Reviews</a> | <a href="PEP_VisitUs.html" style="color: #fff;">Visit Us</a> | <a href="PEP_ContactUs.html" style="color: #fff;">Contact</a>
    </div>
  </footer>
  <script>
    function handleSubmitReview() {
      alert('Review submitted! (Static placeholder - will be moderated)');
      window.location.href = 'PEP_Reviews.html'; // Redirect back
    }
  </script>
</body>
</html>