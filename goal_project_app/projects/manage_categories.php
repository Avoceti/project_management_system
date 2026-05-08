<?php
session_start();
if(!isset($_SESSION['user_id'])){
    header("Location: ../login.php");
    exit;
}
require_once "../config/db.php";

// Fetch categories
$categories = $pdo->query("SELECT * FROM project_categories ORDER BY created_at DESC")->fetchAll(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>Manage Project Categories</title>
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
<link href="https://cdn.jsdelivr.net/npm/bootstrap-icons/font/bootstrap-icons.css" rel="stylesheet">
<style>
body { background-color: #f8f9fa; font-family: 'Inter', sans-serif; }
.sidebar { min-height: 100vh; background: #fff; box-shadow: 2px 0 8px rgba(0,0,0,0.08); padding: 1.5rem 1rem; }
.sidebar .nav-link { color: #495057; font-weight: 500; margin-bottom: 6px; display:flex; align-items:center; gap:8px; }
.sidebar .nav-link.active { background:#e9ecef; border-radius:8px; }
.content { padding: 40px; width: 100%; }
.card-modern { border-radius:12px; box-shadow:0 4px 12px rgba(0,0,0,0.08); background:#fff; }
.table thead th { border-bottom:2px solid #dee2e6; }
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
          <a href="add_category.php" class="nav-link"><i class="bi bi-plus-circle"></i> Add Category</a>
          <a href="manage_categories.php" class="nav-link active"><i class="bi bi-list-task"></i> Manage Categories</a>
        </div>
      </li>

      <li><a href="../logout.php" class="nav-link"><i class="bi bi-box-arrow-right"></i> Logout</a></li>
    </ul>
  </div>

  <!-- Main content -->
  <div class="content flex-grow-1">
    <div class="card-modern p-4">
      <h3 class="mb-4"><i class="bi bi-list-task me-2 text-primary"></i>Manage Project Categories</h3>

      <div class="mb-3">
        <input id="searchCategories" class="form-control" placeholder="Search categories...">
      </div>

      <div class="table-responsive">
        <table class="table table-hover align-middle">
          <thead>
            <tr>
              <th>#</th>
              <th>Name</th>
              <th>Created At</th>
              <th>Action</th>
            </tr>
          </thead>
          <tbody id="categoryTable">
            <?php foreach($categories as $i => $c): ?>
            <tr>
              <td><?= $i+1 ?></td>
              <td><?= htmlspecialchars($c['name']) ?></td>
              <td><?= htmlspecialchars($c['created_at']) ?></td>
              <td>
                <a href="edit_category.php?id=<?= $c['id'] ?>" class="btn btn-sm btn-warning"><i class="bi bi-pencil"></i> Edit</a>
                <a href="delete_category.php?id=<?= $c['id'] ?>" class="btn btn-sm btn-danger" onclick="return confirm('Delete this category?');"><i class="bi bi-trash"></i> Delete</a>
              </td>
            </tr>
            <?php endforeach; ?>
          </tbody>
        </table>
      </div>
    </div>
  </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
<script>
document.getElementById('searchCategories').addEventListener('input', function(){
  const term = this.value.toLowerCase();
  document.querySelectorAll('#categoryTable tr').forEach(tr => {
    tr.style.display = tr.textContent.toLowerCase().includes(term) ? '' : 'none';
  });
});
</script>
</body>
</html>
