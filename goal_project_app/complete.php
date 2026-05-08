<?php
session_start();
if(!isset($_SESSION['user_id'])){
    header("Location: login.php");
    exit;
}

require_once __DIR__ . '/config/db.php';

$limit = 5;

// Pagination setup
$pageGoals = isset($_GET['pageGoals']) && is_numeric($_GET['pageGoals']) ? max(1, (int)$_GET['pageGoals']) : 1;
$pageProjects = isset($_GET['pageProjects']) && is_numeric($_GET['pageProjects']) ? max(1, (int)$_GET['pageProjects']) : 1;

try {
    /* --- Completed Goals --- */
    $totalGoals = (int)$pdo->query("SELECT COUNT(*) FROM goals WHERE target_amount > 0 AND current_amount >= target_amount")->fetchColumn();
    $totalGoalPages = $totalGoals > 0 ? ceil($totalGoals / $limit) : 1;
    if ($pageGoals > $totalGoalPages) $pageGoals = $totalGoalPages;
    $offsetGoals = ($pageGoals - 1) * $limit;

    $stmt = $pdo->prepare("SELECT * FROM goals WHERE target_amount > 0 AND current_amount >= target_amount ORDER BY created_at DESC LIMIT :limit OFFSET :offset");
    $stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
    $stmt->bindValue(':offset', $offsetGoals, PDO::PARAM_INT);
    $stmt->execute();
    $completedGoals = $stmt->fetchAll(PDO::FETCH_ASSOC);

    /* --- Completed Projects --- */
    $totalProjects = (int)$pdo->query("SELECT COUNT(*) FROM projects WHERE LOWER(status) = 'completed'")->fetchColumn();
    $totalProjectPages = $totalProjects > 0 ? ceil($totalProjects / $limit) : 1;
    if ($pageProjects > $totalProjectPages) $pageProjects = $totalProjectPages;
    $offsetProjects = ($pageProjects - 1) * $limit;

    $stmt = $pdo->prepare("
        SELECT p.*, c.name AS category_name 
        FROM projects p 
        LEFT JOIN project_categories c ON p.category_id=c.id
        WHERE LOWER(p.status) = 'completed'
        ORDER BY p.created_at DESC
        LIMIT :limit OFFSET :offset
    ");
    $stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
    $stmt->bindValue(':offset', $offsetProjects, PDO::PARAM_INT);
    $stmt->execute();
    $completedProjects = $stmt->fetchAll(PDO::FETCH_ASSOC);

} catch (PDOException $e) {
    $completedGoals = [];
    $completedProjects = [];
    $totalGoalPages = $totalProjectPages = 1;
    $pageGoals = $pageProjects = 1;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>Completed Goals & Projects</title>
<meta name="viewport" content="width=device-width,initial-scale=1">
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
<link href="https://cdn.jsdelivr.net/npm/bootstrap-icons/font/bootstrap-icons.css" rel="stylesheet">
<style>
body { font-family: Inter, system-ui, -apple-system, "Segoe UI", Roboto, "Helvetica Neue", Arial; background:#f6f7fb; }
.sidebar { width:250px; min-height:100vh; background:#fff; border-right:1px solid #e5e7eb; padding:1rem; position:fixed; top:0; left:0; }
.sidebar h4 { color:#0d6efd; margin-bottom:1.5rem; }
.sidebar .nav-link { color:#333; border-radius:6px; padding:.5rem .75rem; font-weight:500; }
.sidebar .nav-link.active, .sidebar .nav-link:hover { color:#0d6efd; background:#f1f5f9; }
.sidebar .submenu { padding-left:1.5rem; }
.content { margin-left:270px; padding:24px; }
.card-modern { background:#fff; border-radius:12px; box-shadow:0 6px 18px rgba(16,24,40,0.06); border:1px solid rgba(16,24,40,0.04); padding:16px; }
.search-box { max-width:360px; }
.footer { text-align:center; color:#6c757d; padding:18px 0; margin-top:28px; border-top:1px solid rgba(16,24,40,0.06); }
.progress { height:8px; }
@media (max-width:768px){ .sidebar{position:relative;width:100%;min-height:auto;} .content{margin-left:0;} }
</style>
</head>
<body>

<!-- Sidebar -->
<aside class="sidebar d-flex flex-column">
  <h4><i class="bi bi-bullseye me-2"></i> Admin Panel</h4>
  <nav class="nav flex-column">
    <a class="nav-link" href="index.php"><i class="bi bi-speedometer2 me-2"></i> Dashboard</a>
    <a class="nav-link active" href="complete.php"><i class="bi bi-check2-circle me-2"></i> Complete</a>
    <a class="nav-link" href="settings.php"><i class="bi bi-gear me-2"></i> Settings</a>
    <a class="nav-link text-danger mt-auto" href="logout.php"><i class="bi bi-box-arrow-right me-2"></i> Logout</a>
  </nav>
</aside>

<!-- Main content -->
<main class="content">
<h3 class="mb-4"><i class="bi bi-check2-circle me-2 text-success"></i> Completed Goals & Projects</h3>

<!-- Completed Goals -->
<section class="card-modern mb-4">
  <div class="d-flex justify-content-between align-items-center mb-3">
    <h5><i class="bi bi-bullseye me-2 text-primary"></i> Completed Goals</h5>
    <input id="searchGoals" class="form-control search-box" placeholder="Search goals...">
  </div>
  <?php if (!empty($completedGoals)): ?>
  <div class="table-responsive">
    <table class="table table-hover align-middle">
      <thead>
        <tr>
          <th>Title</th>
          <th>Deadline</th>
          <th>Target</th>
          <th>Current</th>
          <th>Progress</th>
          <th>Status</th>
        </tr>
      </thead>
      <tbody>
        <?php foreach($completedGoals as $g): 
          $progress = $g['target_amount'] > 0 ? round(($g['current_amount'] / $g['target_amount'])*100,2) : 0;
        ?>
        <tr>
          <td><?= htmlspecialchars($g['title']) ?></td>
          <td><?= htmlspecialchars($g['deadline'] ?? '-') ?></td>
          <td>$<?= number_format($g['target_amount'],2) ?></td>
          <td>$<?= number_format($g['current_amount'],2) ?></td>
          <td>
            <div class="progress mb-1">
              <div class="progress-bar <?= $progress<50?'bg-danger':'bg-success' ?>" style="width:<?= $progress ?>%"></div>
            </div>
            <small><?= $progress ?>%</small>
          </td>
          <td><span class="badge bg-success"><?= htmlspecialchars($g['status'] ?? 'Completed') ?></td>
        </tr>
        <?php endforeach; ?>
      </tbody>
    </table>
  </div>

  <!-- Pagination for goals -->
  <nav class="mt-3">
    <ul class="pagination justify-content-center">
      <li class="page-item <?= ($pageGoals<=1)?'disabled':'' ?>">
        <a class="page-link" href="?pageGoals=<?= max(1,$pageGoals-1) ?>&pageProjects=<?= $pageProjects ?>">Previous</a>
      </li>
      <li class="page-item disabled"><span class="page-link">Page <?= $pageGoals ?> of <?= $totalGoalPages ?></span></li>
      <li class="page-item <?= ($pageGoals>=$totalGoalPages)?'disabled':'' ?>">
        <a class="page-link" href="?pageGoals=<?= min($totalGoalPages,$pageGoals+1) ?>&pageProjects=<?= $pageProjects ?>">Next</a>
      </li>
    </ul>
  </nav>
  <?php else: ?>
    <p class="text-muted mb-0">No completed goals yet.</p>
  <?php endif; ?>
</section>

<!-- Completed Projects -->
<section class="card-modern mb-4">
  <div class="d-flex justify-content-between align-items-center mb-3">
    <h5><i class="bi bi-rocket-takeoff me-2 text-success"></i> Completed Projects</h5>
    <input id="searchProjects" class="form-control search-box" placeholder="Search projects...">
  </div>
  <?php if (!empty($completedProjects)): ?>
  <div class="table-responsive">
    <table id="projectTable" class="table table-hover align-middle">
      <thead>
        <tr>
          <th>Project</th>
          <th>Category</th>
          <th>Budget</th>
          <th>Spent</th>
          <th>Progress</th>
          <th>Status</th>
        </tr>
      </thead>
      <tbody>
        <?php foreach ($completedProjects as $p): ?>
        <tr>
          <td><?= htmlspecialchars($p['title']) ?></td>
          <td><?= htmlspecialchars($p['category_name'] ?? '-') ?></td>
          <td>$<?= number_format($p['target_amount'] ?? 0,2) ?></td>
          <td>$<?= number_format($p['current_amount'] ?? 0,2) ?></td>
          <td>
            <div class="progress mb-1">
              <div class="progress-bar bg-success" style="width:<?= (int)$p['progress'] ?>%;"></div>
            </div>
            <small><?= (int)$p['progress'] ?>%</small>
          </td>
          <td><span class="badge bg-success"><?= htmlspecialchars($p['status']) ?></span></td>
        </tr>
        <?php endforeach; ?>
      </tbody>
    </table>
  </div>

  <!-- Pagination for projects -->
  <nav class="mt-3">
    <ul class="pagination justify-content-center">
      <li class="page-item <?= ($pageProjects<=1)?'disabled':'' ?>">
        <a class="page-link" href="?pageGoals=<?= $pageGoals ?>&pageProjects=<?= max(1,$pageProjects-1) ?>">Previous</a>
      </li>
      <li class="page-item disabled"><span class="page-link">Page <?= $pageProjects ?> of <?= $totalProjectPages ?></span></li>
      <li class="page-item <?= ($pageProjects>=$totalProjectPages)?'disabled':'' ?>">
        <a class="page-link" href="?pageGoals=<?= $pageGoals ?>&pageProjects=<?= min($totalProjectPages,$pageProjects+1) ?>">Next</a>
      </li>
    </ul>
  </nav>
  <?php else: ?>
    <p class="text-muted mb-0">No completed projects yet.</p>
  <?php endif; ?>
</section>

<div class="footer">&copy; <?= date('Y') ?> Project Management System</div>
</main>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
<script>
// Live search for goals
document.getElementById('searchGoals').addEventListener('input', function(){
  const term = this.value.toLowerCase();
  document.querySelectorAll('table tbody tr').forEach(tr=>{
    if(tr.closest('section').querySelector('#searchGoals')){
      tr.style.display = tr.textContent.toLowerCase().includes(term)?'':'none';
    }
  });
});

// Live search for projects
document.getElementById('searchProjects').addEventListener('input', function(){
  const term = this.value.toLowerCase();
  document.querySelectorAll('#projectTable tbody tr').forEach(tr=>{
    tr.style.display = tr.textContent.toLowerCase().includes(term)?'':'none';
  });
});
</script>
</body>
</html>
