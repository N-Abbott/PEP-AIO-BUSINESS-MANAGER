<!DOCTYPE html>
<html lang="en">
<head>
  <title>Employee Portal - Schedule - Petrongolo Evergreen Plantation</title>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-QWTKZyjpPEjISv5WaRU9OFeRpok6YctnYmDr5pNlyT2bRjXh0JMhjY6hW+ALEwIH" crossorigin="anonymous">
  <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Oswald:wght@400;700&family=Roboto:wght@400;700&display=swap">
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
    .sidebar .nav-link {
      color: #006400; /* Dark green text for better visibility/contrast */
      font-weight: 600;
      padding: 10px;
      border-radius: 5px;
      margin-bottom: 10px;
    }
    .sidebar .nav-link:hover {
      background-color: #d4e6d4;
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
    .upload-section {
      display: none; /* Hidden for non-admin; show via JS/role check later */
    }
  </style>
</head>
<body>
  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js" integrity="sha384-YvpcrYf0tY3lHB60NNkmXc5s9fDVZLESaAA55NDzOxhy9GkcIdslK1eN7N6jIeHz" crossorigin="anonymous"></script>
  <nav class="navbar navbar-expand-lg">
    <div class="container-fluid">
      <a class="navbar-brand" href="#">Employee Portal</a>
      <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav" aria-controls="navbarNav" aria-expanded="false" aria-label="Toggle navigation">
        <span class="navbar-toggler-icon"></span>
      </button>
    </div>
  </nav>
  <div class="sidebar">
    <h4 style="color: white;">Options</h4>
    <ul class="nav flex-column">
      <li class="nav-item">
        <a class="nav-link active" href="PEP_EmployeeSchedule.html">View Schedule</a>
      </li>
      <li class="nav-item">
        <a class="nav-link" href="PEP_EmployeeTasks.html">View Tasks</a>
      </li>
      <li class="nav-item">
        <a class="nav-link" href="#" data-bs-toggle="modal" data-bs-target="#adminLoginModal">Admin Access</a>
      </li>
      <li class="nav-item">
        <a class="nav-link" href="PEP_Main.html">Return Home</a>
      </li>
    </ul>
  </div>
  <div class="content">
    <h2 class="section-title">Your Schedule</h2>
    <p>Welcome to your schedule view. Below is a placeholder table—admin can upload/update via backend later.</p>
    <table class="table table-striped">
      <thead>
        <tr>
          <th>Date</th>
          <th>Shift Time</th>
          <th>Role/Tasks</th>
        </tr>
      </thead>
      <tbody>
        <tr>
          <td>Nov 1, 2025</td>
          <td>9 AM - 5 PM</td>
          <td>Sales Assistance</td>
        </tr>
        <tr>
          <td>Nov 2, 2025</td>
          <td>9 AM - 5 PM</td>
          <td>Inventory Check</td>
        </tr>
        <!-- Add more rows as needed -->
      </tbody>
    </table>
    <div class="upload-section">
      <h4>Admin: Upload Schedule</h4>
      <form>
        <div class="mb-3">
          <label for="scheduleFile" class="form-label">Upload Schedule (CSV/Excel)</label>
          <input type="file" class="form-control" id="scheduleFile">
        </div>
        <button type="button" class="btn btn-primary" onclick="alert('Schedule uploaded! (Placeholder)')">Upload</button>
      </form>
    </div>
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
          <form>
            <div class="mb-3">
              <label for="adminPassword" class="form-label">Password</label>
              <input type="password" class="form-control" id="adminPassword" placeholder="Enter admin password">
            </div>
            <button type="button" class="btn btn-primary" onclick="handleAdminLogin()">Login</button>
            <button type="button" class="btn btn-secondary mt-2" onclick="bypassToAdmin()">Test Bypass to Admin</button>
          </form>
        </div>
      </div>
    </div>
  </div>
  <script>
    function handleAdminLogin() {
      alert('Admin login successful! Redirecting... (Placeholder)');
      window.location.href = 'PEP_Admin.html';
    }

    function bypassToAdmin() {
      window.location.href = 'PEP_Admin.html';
    }
  </script>
</body>
</html>