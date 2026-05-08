<?php
if (session_status() === PHP_SESSION_NONE) session_start();
if (!isset($_SESSION['user_id'])) {
    header("Location: ../login.php");
    exit;
}

require_once __DIR__ . "/../config/db.php";

// Fetch goal
$id = $_GET['id'] ?? 0;
$stmt = $pdo->prepare("SELECT * FROM goals WHERE id=?");
$stmt->execute([$id]);
$goal = $stmt->fetch();

if (!$goal) die("Goal not found.");

// Handle update
$error = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $title    = $_POST['title'] ?? '';
    $target   = $_POST['target_amount'] ?? 0;
    $current  = $_POST['current_amount'] ?? 0;
    $status   = $_POST['status'] ?? '';
    $deadline = $_POST['deadline'] ?? '';

    if ($title && $target >= 0 && $current >= 0) {
        $stmt = $pdo->prepare("UPDATE goals SET title=?, target_amount=?, current_amount=?, status=?, deadline=? WHERE id=?");
        $stmt->execute([$title, $target, $current, $status, $deadline, $id]);
        header("Location: view_goal.php?id=$id");
        exit;
    } else {
        $error = "Please fill in all required fields correctly.";
    }
}

$target   = $goal['target_amount'] ?? 0;
$current  = $goal['current_amount'] ?? 0;
$progress = $target > 0 ? round(($current / $target) * 100, 2) : 0;
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>Edit Goal</title>
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
<link href="https://cdn.jsdelivr.net/npm/bootstrap-icons/font/bootstrap-icons.css" rel="stylesheet">
<style>
body { background:#f8f9fa; }

/* Sidebar desktop */
.sidebar {
  background: #fff;
  border-right: 1px solid #dee2e6;
  min-height: 100vh;
  width: 250px;
  position: fixed;
  top: 0; left: 0;
  padding: 1rem;
}

/* Sidebar mobile (hidden by default) */
@media (max-width: 991px) {
  .sidebar {
    left: -250px;
    transition: left .3s ease;
    z-index: 1050;
  }
  .sidebar.show {
    left: 0;
  }
  .main-content {
    margin-left: 0 !important;
    width: 100% !important;
  }
}

/* Main content */
.main-content {
  margin-left: 250px;
  padding: 2rem;
}
.sidebar h5 {
  font-weight: 600;
  color: #0d6efd;
  margin-bottom: 1rem;
}
.sidebar .nav-link {
  color: #333;
  border-radius: .4rem;
  margin: .2rem 0;
}
.sidebar .nav-link:hover,
.sidebar .nav-link.active {
  background: #f8f9fa;
  color: #0d6efd;
}
</style>
</head>
<body>

<!-- Sidebar -->
<nav id="sidebar" class="sidebar">
  <h5><i class="bi bi-speedometer2"></i> Dashboard</h5>
  <ul class="nav flex-column">
    <li class="nav-item">
      <a href="../index.php" class="nav-link"><i class="bi bi-house"></i> Home</a>
    </li>

    <!-- Goals Dropdown -->
    <li class="nav-item">
      <a class="nav-link" data-bs-toggle="collapse" href="#goalsMenu" role="button">
        <i class="bi bi-flag"></i> Goals
      </a>
      <div class="collapse show" id="goalsMenu">
        <a href="add_goal.php" class="nav-link ms-3"><i class="bi bi-plus-circle"></i> Add Goal</a>
        <a href="list_goals.php" class="nav-link ms-3 active"><i class="bi bi-list-task"></i> List Goals</a>
      </div>
    </li>

    <!-- Projects Dropdown -->
    <li class="nav-item">
      <a class="nav-link" data-bs-toggle="collapse" href="#projectsMenu" role="button">
        <i class="bi bi-diagram-3"></i> Projects
      </a>
      <div class="collapse" id="projectsMenu">
        <a href="../projects/add_project.php" class="nav-link ms-3"><i class="bi bi-plus-circle"></i> Add Project</a>
        <a href="../projects/list_projects.php" class="nav-link ms-3"><i class="bi bi-list-task"></i> List Projects</a>
      </div>
    </li>

    <li class="nav-item">
      <a href="../logout.php" class="nav-link"><i class="bi bi-box-arrow-right"></i> Logout</a>
    </li>
  </ul>
</nav>

<!-- Main Content -->
<main class="main-content">
  <!-- Mobile toggle button -->
  <button class="btn btn-outline-primary d-lg-none mb-3" onclick="toggleSidebar()">
    <i class="bi bi-list"></i> Menu
  </button>

  <h3 class="mb-4"><i class="bi bi-pencil"></i> Edit Goal</h3>

  <?php if ($error): ?>
    <div class="alert alert-danger"><?= htmlspecialchars($error) ?></div>
  <?php endif; ?>

  <div class="card shadow-sm p-4">
    <form method="post">
      <div class="mb-3">
        <label class="form-label">Title</label>
        <div class="input-group">
          <span class="input-group-text"><i class="bi bi-type"></i></span>
          <input type="text" name="title" class="form-control" value="<?= htmlspecialchars($goal['title']) ?>" required>
        </div>
      </div>

      <div class="mb-3">
        <label class="form-label">Target Amount</label>
        <div class="input-group">
          <span class="input-group-text"><i class="bi bi-currency-dollar"></i></span>
          <input type="number" step="0.01" name="target_amount" class="form-control" value="<?= $target ?>" required>
        </div>
      </div>

      <div class="mb-3">
        <label class="form-label">Current Amount</label>
        <div class="input-group">
          <span class="input-group-text"><i class="bi bi-wallet2"></i></span>
          <input type="number" step="0.01" name="current_amount" class="form-control" value="<?= $current ?>" required>
        </div>
      </div>

      <div class="mb-3">
        <label class="form-label">Status</label>
        <div class="input-group">
          <span class="input-group-text"><i class="bi bi-check2-circle"></i></span>
          <select name="status" class="form-select">
            <option value="Pending"   <?= $goal['status']=='Pending'?'selected':'' ?>>Pending</option>
            <option value="In Progress" <?= $goal['status']=='In Progress'?'selected':'' ?>>In Progress</option>
            <option value="Completed" <?= $goal['status']=='Completed'?'selected':'' ?>>Completed</option>
          </select>
        </div>
      </div>

      <div class="mb-3">
        <label class="form-label">Deadline</label>
        <div class="input-group">
          <span class="input-group-text"><i class="bi bi-calendar-event"></i></span>
          <input type="date" name="deadline" class="form-control" value="<?= $goal['deadline'] ?>" required>
        </div>
      </div>

      <div class="mb-3">
        <label class="form-label">Progress</label>
        <div class="progress" style="height:8px;">
          <div class="progress-bar <?= $progress < 50 ? 'bg-danger' : 'bg-success' ?>" style="width: <?= $progress ?>%;"></div>
        </div>
        <small><?= $progress ?>% Complete</small>
      </div>

      <button class="btn btn-success"><i class="bi bi-save"></i> Save Changes</button>
      <a href="view_goal.php?id=<?= $goal['id'] ?>" class="btn btn-secondary"><i class="bi bi-arrow-left"></i> Cancel</a>
    </form>
  </div>
</main>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
<script>
function toggleSidebar(){
  document.getElementById("sidebar").classList.toggle("show");
}
</script>
</body>
</html>
