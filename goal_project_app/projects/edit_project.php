<?php
session_start();
if(!isset($_SESSION['user_id'])){
    header("Location: ../login.php");
    exit;
}

require_once "../config/db.php";

$id = $_GET['id'] ?? 0;

// Fetch project
$stmt = $pdo->prepare("SELECT * FROM projects WHERE id=?");
$stmt->execute([$id]);
$project = $stmt->fetch();
if(!$project){
    die("Project not found.");
}

// Fetch categories
$categories = $pdo->query("SELECT * FROM project_categories ORDER BY name ASC")->fetchAll(PDO::FETCH_ASSOC);

$error = '';
if($_SERVER['REQUEST_METHOD'] === 'POST'){
    $title = $_POST['title'] ?? '';
    $description = $_POST['description'] ?? '';
    $start_date = $_POST['start_date'] ?? date('Y-m-d');
    $deadline = $_POST['deadline'] ?? '';
    $target_amount = isset($_POST['target_amount']) ? (float)$_POST['target_amount'] : 0;
    $current_amount = isset($_POST['current_amount']) ? (float)$_POST['current_amount'] : 0;
    $status = $_POST['status'] ?? '';
    $category_id = $_POST['category_id'] ?? null;

    if($title && $target_amount >= 0 && $current_amount >= 0){
        $progress = $target_amount > 0 ? round(($current_amount / $target_amount) * 100, 2) : 0;
        $stmt = $pdo->prepare("UPDATE projects SET title=?, description=?, start_date=?, deadline=?, target_amount=?, current_amount=?, progress=?, status=?, category_id=? WHERE id=?");
        $stmt->execute([$title, $description, $start_date, $deadline, $target_amount, $current_amount, $progress, $status, $category_id, $id]);
        header("Location: list_projects.php");
        exit;
    } else {
        $error = "Please fill in all required fields correctly.";
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>Edit Project</title>
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
<link href="https://cdn.jsdelivr.net/npm/bootstrap-icons/font/bootstrap-icons.css" rel="stylesheet">
<style>
body { background-color: #f8f9fa; font-family: 'Inter', sans-serif; }
.sidebar {
    min-height: 100vh;
    background: #fff;
    box-shadow: 2px 0 8px rgba(0,0,0,0.08);
    padding: 1.5rem 1rem;
    width: 250px;
}
.sidebar .nav-link {
    color: #495057;
    font-weight: 500;
    margin-bottom: 6px;
    display: flex;
    align-items: center;
    gap: 8px;
}
.sidebar .nav-link.active {
    background: #e9ecef;
    border-radius: 8px;
}
.content { padding: 30px; margin-left:50px; }
.card-project {
    border-radius: 12px;
    box-shadow: 0 4px 12px rgba(0,0,0,0.08);
    background:#fff;
}
.btn-primary { border-radius: 8px; }
h3 { font-weight: 600; }
.form-control { border-radius: 8px; }
textarea.form-control { resize: none; }
.alert { border-radius: 8px; }
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
          <a href="list_projects.php" class="nav-link active"><i class="bi bi-list-task"></i> List Projects</a>
        </div>
      </li>
      <li>
        <a class="nav-link" data-bs-toggle="collapse" href="#categoryMenu">
          <i class="bi bi-tags"></i> Categories
        </a>
        <div class="collapse ps-3" id="categoryMenu">
          <a href="add_category.php" class="nav-link"><i class="bi bi-plus-circle"></i> Add Category</a>
          <a href="manage_categories.php" class="nav-link"><i class="bi bi-list-task"></i> Manage Categories</a>
        </div>
      </li>
      <li><a href="../logout.php" class="nav-link"><i class="bi bi-box-arrow-right"></i> Logout</a></li>
    </ul>
  </div>

  <!-- Main content -->
  <div class="content flex-grow-1">
    <div class="card card-project p-4">
      <h3 class="mb-4"><i class="bi bi-pencil-square me-2 text-primary"></i>Edit Project</h3>
      <?php if($error): ?>
        <div class="alert alert-danger"><?= htmlspecialchars($error) ?></div>
      <?php endif; ?>
      <form method="POST">
        <div class="mb-3">
          <label class="form-label fw-semibold">Title</label>
          <input type="text" name="title" class="form-control" value="<?= htmlspecialchars($project['title']) ?>" required>
        </div>
        <div class="mb-3">
          <label class="form-label fw-semibold">Description</label>
          <textarea name="description" class="form-control" rows="4"><?= htmlspecialchars($project['description']) ?></textarea>
        </div>
        <div class="row g-3">
          <div class="col-md-6">
            <label class="form-label fw-semibold">Start Date</label>
            <input type="date" name="start_date" class="form-control" value="<?= htmlspecialchars($project['start_date']) ?>" required>
          </div>
          <div class="col-md-6">
            <label class="form-label fw-semibold">Deadline</label>
            <input type="date" name="deadline" class="form-control" value="<?= htmlspecialchars($project['deadline']) ?>">
          </div>
        </div>
        <div class="row g-3 mt-3">
          <div class="col-md-6">
            <label class="form-label fw-semibold">Target Amount ($)</label>
            <input type="number" name="target_amount" class="form-control" min="0" step="0.01" value="<?= $project['target_amount'] ?>" required>
          </div>
          <div class="col-md-6">
            <label class="form-label fw-semibold">Current Amount ($)</label>
            <input type="number" name="current_amount" class="form-control" min="0" step="0.01" value="<?= $project['current_amount'] ?>">
          </div>
        </div>
        <div class="row g-3 mt-3">
          <div class="col-md-6">
            <label class="form-label fw-semibold">Status</label>
            <select name="status" class="form-select">
              <option value="Pending" <?= $project['status']=='Pending'?'selected':'' ?>>Pending</option>
              <option value="In Progress" <?= $project['status']=='In Progress'?'selected':'' ?>>In Progress</option>
              <option value="Completed" <?= $project['status']=='Completed'?'selected':'' ?>>Completed</option>
            </select>
          </div>
          <div class="col-md-6">
            <label class="form-label fw-semibold">Category</label>
            <select name="category_id" class="form-select">
              <option value="">Select Category</option>
              <?php foreach($categories as $c): ?>
                <option value="<?= $c['id'] ?>" <?= $project['category_id']==$c['id']?'selected':'' ?>><?= htmlspecialchars($c['name']) ?></option>
              <?php endforeach; ?>
            </select>
          </div>
        </div>
        <div class="mt-4">
          <button type="submit" class="btn btn-primary"><i class="bi bi-save me-2"></i>Save Changes</button>
          <a href="list_projects.php" class="btn btn-secondary ms-2"><i class="bi bi-arrow-left me-2"></i>Back</a>
        </div>
      </form>
    </div>
  </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
