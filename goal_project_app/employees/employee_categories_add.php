<?php
if (session_status() === PHP_SESSION_NONE) session_start();
if(!isset($_SESSION['user_id'])){
    header("Location: ../login.php");
    exit;
}
require_once "../config/db.php";

$error = $success = "";

// Handle Add Category
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = trim($_POST['name']);

    if ($name === "") {
        $error = "Category name is required.";
    } else {
        $stmt = $pdo->prepare("INSERT INTO employee_categories (name, created_at, updated_at) VALUES (:name, NOW(), NOW())");
        $stmt->execute([':name' => $name]);
        $success = "Category added successfully.";
    }
}

// Fetch recent categories
$stmt = $pdo->query("SELECT * FROM employee_categories ORDER BY created_at DESC LIMIT 5");
$recentCategories = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Count total
$totalCategories = $pdo->query("SELECT COUNT(*) FROM employee_categories")->fetchColumn();
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Add Category</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
  <style>
    body { background:#f8f9fa; }
    .sidebar {
        width: 240px;
        height: 100vh;
        position: fixed;
        left: 0;
        top: 0;
        background: #212529;
        padding-top: 20px;
    }
    .sidebar h4 {
        color: #fff;
        text-align: center;
        margin-bottom: 20px;
    }
    .sidebar a {
        display: block;
        color: #adb5bd;
        padding: 12px 20px;
        text-decoration: none;
        font-size: 15px;
    }
    .sidebar a:hover, .sidebar a.active {
        background: #0d6efd;
        color: #fff;
        border-radius: 5px;
    }
    .content { margin-left: 260px; padding: 20px; }
    .card-modern { border-radius:12px; box-shadow:0 6px 18px rgba(16,24,40,0.06); }
  </style>
</head>
<body>
<div class="sidebar">
    <h4>Dashboard</h4>
    <a href="dashboard.php">🏠 Dashboard</a>
    <a href="add_payments.php">💳 Add Payments</a>
    <a href="manage_employee.php">👥 Manage Employees</a>
    <a href="employee_categories.php" class="active">📂 Employee Categories</a>
</div>

<div class="content">
  <h3 class="mb-3">➕ Add Employee Category</h3>

  <?php if($error): ?><div class="alert alert-danger"><?=$error?></div><?php endif; ?>
  <?php if($success): ?><div class="alert alert-success"><?=$success?></div><?php endif; ?>

  <div class="row">
    <!-- Add Form -->
    <div class="col-lg-6 mb-3">
      <div class="card card-modern">
        <div class="card-body">
          <form method="post">
            <div class="mb-3">
              <label class="form-label">Category Name</label>
              <input type="text" name="name" class="form-control" required>
            </div>
            <div class="d-flex justify-content-between">
              <button class="btn btn-primary">💾 Save</button>
              <a href="employee_categories.php" class="btn btn-secondary">Cancel</a>
            </div>
          </form>
        </div>
      </div>
    </div>

    <!-- Sidebar Info -->
    <div class="col-lg-6 mb-3">
      <div class="card card-modern mb-3">
        <div class="card-body">
          <h5>Total Categories</h5>
          <p class="display-6 text-primary fw-bold"><?=$totalCategories?></p>
        </div>
      </div>

      <div class="card card-modern">
        <div class="card-body">
          <h5>Recent Categories</h5>
          <?php if($recentCategories): ?>
            <ul class="list-group">
              <?php foreach($recentCategories as $cat): ?>
                <li class="list-group-item d-flex justify-content-between align-items-center">
                  <?=htmlspecialchars($cat['name'])?>
                  <span class="badge bg-secondary"><?=$cat['created_at']?></span>
                </li>
              <?php endforeach; ?>
            </ul>
          <?php else: ?>
            <p class="text-muted">No categories found.</p>
          <?php endif; ?>
        </div>
      </div>
    </div>
  </div>
</div>
</body>
</html>
