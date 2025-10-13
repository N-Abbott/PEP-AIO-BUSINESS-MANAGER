<!DOCTYPE html>
<html lang="en">
<head>
  <title>Admin Portal - Petrongolo Evergreen Plantation</title>
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
    .content {
      padding: 20px;
    }
    .section-title {
      text-align: center;
      margin-bottom: 40px;
      color: #2c5530;
      font-family: 'Oswald', sans-serif;
      font-weight: 700;
    }
    .tab-content {
      margin-top: 20px;
    }
    .nav-tabs .nav-link {
      color: #000; /* Black text */
      border-color: #000 #000 #f8f9fa; /* Black outlines on top/left/right, bottom matches bg */
    }
    .nav-tabs .nav-link.active {
      color: #000;
      border-color: #000 #000 #f8f9fa;
      border-bottom-color: #fff; /* White bottom for active */
    }
  </style>
</head>
<body>
  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js" integrity="sha384-YvpcrYf0tY3lHB60NNkmXc5s9fDVZLESaAA55NDzOxhy9GkcIdslK1eN7N6jIeHz" crossorigin="anonymous"></script>
  <nav class="navbar navbar-expand-lg">
    <div class="container-fluid">
      <a class="navbar-brand" href="#">Admin Portal</a>
      <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav" aria-controls="navbarNav" aria-expanded="false" aria-label="Toggle navigation">
        <span class="navbar-toggler-icon"></span>
      </button>
      <div class="collapse navbar-collapse" id="navbarNav">
        <ul class="navbar-nav ms-auto">
          <li class="nav-item">
            <a class="nav-link" href="PEP_EmployeeSchedule.html">Back to Employee Portal</a>
          </li>
          <li class="nav-item">
            <a class="nav-link" href="PEP_Main.html">Return to Customer Page</a>
          </li>
        </ul>
      </div>
    </div>
  </nav>
  <div class="content">
    <h2 class="section-title">Admin Management</h2>
    <ul class="nav nav-tabs" id="adminTabs" role="tablist">
      <li class="nav-item" role="presentation">
        <button class="nav-link active" id="schedule-tab" data-bs-toggle="tab" data-bs-target="#schedule" type="button" role="tab" aria-controls="schedule" aria-selected="true" style="color: #2c5530;"><p style="color: #2c5530;">Edit Schedules</p></button>
      </li>
      <li class="nav-item" role="presentation">
        <button class="nav-link" id="tasks-tab" data-bs-toggle="tab" data-bs-target="#tasks" type="button" role="tab" aria-controls="tasks" aria-selected="false"><p style="color: #2c5530;">Edit Tasks</p></button>
      </li>
      <li class="nav-item" role="presentation">
        <button class="nav-link" id="accounts-tab" data-bs-toggle="tab" data-bs-target="#accounts" type="button" role="tab" aria-controls="accounts" aria-selected="false"><p style="color: #2c5530;">Manage Accounts</p></button>
      </li>
    </ul>
    <div class="tab-content" id="adminTabContent">
      <div class="tab-pane fade show active" id="schedule" role="tabpanel" aria-labelledby="schedule-tab">
        <h4>Edit Schedules</h4>
        <p>Placeholder for schedule management. Upload or edit table below.</p>
        <form>
          <div class="mb-3">
            <label for="scheduleFile" class="form-label">Upload Schedule (CSV/Excel)</label>
            <input type="file" class="form-control" id="scheduleFile">
          </div>
          <button type="button" class="btn btn-primary" onclick="alert('Schedule updated! (Placeholder)')">Update</button>
        </form>
        <table class="table table-striped mt-3">
          <thead>
            <tr>
              <th>Employee</th>
              <th>Date</th>
              <th>Shift</th>
              <th>Actions</th>
            </tr>
          </thead>
          <tbody>
            <tr>
              <td>John Doe</td>
              <td>Nov 1</td>
              <td>9 AM - 5 PM</td>
              <td><button class="btn btn-sm btn-warning" onclick="alert('Edit schedule (Placeholder)')">Edit</button></td>
            </tr>
            <!-- Add more -->
          </tbody>
        </table>
      </div>
      <div class="tab-pane fade" id="tasks" role="tabpanel" aria-labelledby="tasks-tab">
        <h4>Edit Tasks</h4>
        <p>Placeholder for task management. Assign or edit below.</p>
        <form>
          <div class="mb-3">
            <label for="taskEmployee" class="form-label">Assign to Employee</label>
            <select class="form-select" id="taskEmployee">
              <option>John Doe</option>
              <!-- From DB later -->
            </select>
          </div>
          <div class="mb-3">
            <label for="taskDesc" class="form-label">Description</label>
            <input type="text" class="form-control" id="taskDesc" placeholder="Task description">
          </div>
          <div class="mb-3">
            <label for="taskDue" class="form-label">Due Date</label>
            <input type="date" class="form-control" id="taskDue">
          </div>
          <button type="button" class="btn btn-primary" onclick="alert('Task assigned! (Placeholder)')">Assign</button>
        </form>
        <ul class="list-group mt-3">
          <li class="list-group-item d-flex justify-content-between align-items-center">
            Check inventory - John Doe - Nov 1
            <button class="btn btn-sm btn-warning" onclick="alert('Edit task (Placeholder)')">Edit</button>
          </li>
          <!-- Add more -->
        </ul>
      </div>
      <div class="tab-pane fade" id="accounts" role="tabpanel" aria-labelledby="accounts-tab">
        <h4>Manage Accounts</h4>
        <p>Placeholder for account management. Add/edit employees.</p>
        <form>
          <div class="mb-3">
            <label for="empName" class="form-label">Employee Name</label>
            <input type="text" class="form-control" id="empName" placeholder="Name">
          </div>
          <div class="mb-3">
            <label for="empEmail" class="form-label">Email</label>
            <input type="email" class="form-control" id="empEmail" placeholder="Email">
          </div>
          <div class="mb-3">
            <label for="empPassword" class="form-label">Password</label>
            <input type="password" class="form-control" id="empPassword" placeholder="Password">
          </div>
          <button type="button" class="btn btn-primary" onclick="alert('Account added! (Placeholder)')">Add Employee</button>
        </form>
        <table class="table table-striped mt-3">
          <thead>
            <tr>
              <th>Name</th>
              <th>Email</th>
              <th>Role</th>
              <th>Actions</th>
            </tr>
          </thead>
          <tbody>
            <tr>
              <td>John Doe</td>
              <td>john@example.com</td>
              <td>Employee</td>
              <td><button class="btn btn-sm btn-warning" onclick="alert('Edit account (Placeholder)')">Edit</button></td>
            </tr>
            <!-- Add more -->
          </tbody>
        </table>
      </div>
    </div>
  </div>
</body>
</html>