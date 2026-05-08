<?php
session_start();
if(!isset($_SESSION['user_id'])){
    header("Location: ../login.php");
    exit;
}
require_once "../config/db.php";

$message = '';
if($_SERVER['REQUEST_METHOD'] === 'POST'){
    $name = trim($_POST['name'] ?? '');
    if($name){
        $stmt = $pdo->prepare("INSERT INTO project_categories (name, created_at) VALUES (?, NOW())");
        $stmt->execute([$name]);
        $message = "Category '$name' added successfully!";
    } else {
        $message = "Category name is required.";
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>Add Project Category</title>
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
<link href="https://cdn.jsdelivr.net/npm/bootstrap-icons/font/bootstrap-icons.css" rel="stylesheet">
<style>
body { background-color: #f8f9fa; font-family: 'Inter', sans-serif; }
.sidebar { min-height: 100vh; background: #fff; box-shadow: 2px 0 8px rgba(0,0,0,0.08); padding: 1.5rem 1rem; }
.sidebar .nav-link { color: #495057; font-weight: 500; margin-bottom: 6px; display:flex; align-items:center; gap:8px; }
.sidebar .nav-link.active { background:#e9ecef; border-radius:8px; }
.content { padding: 40px; width: 100%; }
.card-goal { border-radius:12px; box-shadow:0 4px 12px rgba(0,0,0,0.08); background:#fff; }
.btn-primary { border-radius: 8px; }
h3 { font-weight:600; }
.form-control { border-radius:8px; }
.alert { border-radius:8px; }
</style>
</head>
<body>
<div class="d-flex">
  <!-- Sidebar -->
  <div class="sidebar">
    <h4 class="mb-4"><i class="bi bi-bar-chart-steps me-2 text-primary"></i> Dashboard</h4>
    <ul class="nav flex-column">
      <li><a href="../index.php" class="nav-link"><i class="bi bi-speedometer2"></i> Home</a></li>

      <li>
        <a class="nav-link" data-bs-toggle="collapse" href="#projectMenu">
          <i class="bi bi-kanban"></i> Projects
        </a>
        <div class="collapse show ps-3" id="projectMenu">
          <a href="add_project.php" class="nav-link"><i class="bi bi-plus-circle"></i> Add Project</a>
          <a href="list_projects.php" class="nav-link"><i class="bi bi-list-task"></i> List Projects</a>
        </div>
      </li>

      <li>
        <a class="nav-link" data-bs-toggle="collapse" href="#categoryMenu">
          <i class="bi bi-tags"></i> Project Categories
        </a>
        <div class="collapse show ps-3" id="categoryMenu">
          <a href="add_category.php" class="nav-link active"><i class="bi bi-plus-circle"></i> Add Category</a>
          <a href="manage_categories.php" class="nav-link"><i class="bi bi-list-task"></i> Manage Categories</a>
        </div>
      </li>

      <li><a href="../logout.php" class="nav-link"><i class="bi bi-box-arrow-right"></i> Logout</a></li>
    </ul>
  </div>

  <!-- Main content -->
  <div class="content flex-grow-1">
    <div class="card card-goal p-4">
      <h3 class="mb-4"><i class="bi bi-plus-circle me-2 text-primary"></i>Add Project Category</h3>
      <?php if($message): ?>
        <div class="alert alert-info"><?= htmlspecialchars($message) ?></div>
      <?php endif; ?>
      <form method="POST">
        <div class="mb-3">
          <label class="form-label fw-semibold">Category Name</label>
          <input type="text" name="name" class="form-control" placeholder="Enter category name" required>
        </div>
        <div class="mt-3">
          <button type="submit" class="btn btn-primary"><i class="bi bi-plus-circle me-2"></i>Add Category</button>
          <a href="manage_categories.php" class="btn btn-secondary ms-2"><i class="bi bi-list-task me-2"></i>Manage Categories</a>
        </div>
      </form>
    </div>
  </div>
</div>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
