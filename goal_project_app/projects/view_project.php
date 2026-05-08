<?php
session_start();
if(!isset($_SESSION['user_id'])){
    header("Location: ../login.php");
    exit;
}
require_once "../config/db.php";

$id = $_GET['id'] ?? 0;
$stmt = $pdo->prepare("SELECT p.*, c.name AS category_name 
                       FROM projects p 
                       LEFT JOIN project_categories c ON p.category_id = c.id
                       WHERE p.id=?");
$stmt->execute([$id]);
$project = $stmt->fetch();

if(!$project){
    die("Project not found.");
}

$target = $project['target_amount'] ?? 0;
$current = $project['current_amount'] ?? 0;
$progress = $target > 0 ? round(($current / $target) * 100, 2) : 0;
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>View Project</title>
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
<link href="https://cdn.jsdelivr.net/npm/bootstrap-icons/font/bootstrap-icons.css" rel="stylesheet">
<style>
html, body {
    height: 100%;
    margin: 0;
    overflow: hidden;
    background:#f8f9fa;
}
.sidebar {
    height: 100vh;
    width: 220px;
    background: #fff;
    border-right: 1px solid #dee2e6;
    padding: 1rem 0;
    position: fixed;
    top: 0;
    left: 0;
    overflow-y: auto;
}
.sidebar h5 { font-weight: 600; color: #333; padding-left:1rem; margin-bottom:1rem; }
.sidebar a { display:block; padding:.6rem 1rem; margin:.2rem 0; color:#333; text-decoration:none; border-radius:.4rem; cursor:pointer; }
.sidebar a:hover, .sidebar a.active { background:#f8f9fa; color:#0d6efd; }
.sidebar .submenu { padding-left:2rem; font-size:.9rem; display:none; }
.main-content {
    margin-left:220px;
    padding:1rem 2rem;
    height: 100vh;
    overflow-y: auto;
    display: flex;
    flex-direction: column;
    align-items: flex-start;
}
.card-modern {
    background:#fff;
    border:1px solid #dee2e6;
    border-radius:10px;
    box-shadow:0 4px 8px rgba(0,0,0,0.05);
    margin-bottom:1.5rem;
    max-width:700px;
    width:100%;
    padding:2rem;
}
.card-modern table th,
.card-modern table td {
    padding:0.75rem 1.5rem;
}
.card-modern table th { width:35%; }
.chart-container { width:100%; height:300px; margin-top:1.5rem; }
.table th i { margin-right:0.5rem; }
@media(max-width:768px){
    .sidebar { position: relative; width: 100%; height: auto; }
    .main-content { margin-left:0; padding:1rem; height:auto; }
    .card-modern { padding:1rem; max-width:100%; }
    .chart-container { height:250px; }
}
</style>
</head>
<body>

<div class="container-fluid h-100">
  <div class="row h-100">
    <!-- Sidebar -->
    <nav class="sidebar">
      <h5><i class="bi bi-speedometer2"></i> Dashboard</h5>
      <a href="../index.php"><i class="bi bi-house"></i> Home</a>

      <a class="menu-toggle"><i class="bi bi-flag"></i> Goals <i class="bi bi-chevron-down float-end"></i></a>
      <div class="submenu">
        <a href="../goals/add_goal.php"><i class="bi bi-plus-circle"></i> Add Goal</a>
        <a href="../goals/list_goals.php"><i class="bi bi-list-task"></i> List Goals</a>
      </div>

      <a class="menu-toggle"><i class="bi bi-diagram-3"></i> Projects <i class="bi bi-chevron-down float-end"></i></a>
      <div class="submenu">
        <a href="add_project.php"><i class="bi bi-plus-circle"></i> Add Project</a>
        <a href="list_projects.php"><i class="bi bi-list-task"></i> List Projects</a>
      </div>

      <a href="../logout.php"><i class="bi bi-box-arrow-right"></i> Logout</a>
    </nav>

    <!-- Main Content -->
    <main class="main-content">
      <h3 class="mb-4"><i class="bi bi-eye"></i> View Project</h3>

      <div class="card-modern">
        <!-- Project Details Table -->
        <table class="table table-hover align-middle mb-0">
          <tbody>
            <tr>
              <th><i class="bi bi-card-text"></i> Title</th>
              <td><?= htmlspecialchars($project['title']) ?></td>
            </tr>
            <tr>
              <th><i class="bi bi-tags"></i> Category</th>
              <td><?= htmlspecialchars($project['category_name'] ?? '-') ?></td>
            </tr>
            <tr>
              <th><i class="bi bi-file-earmark-text"></i> Description</th>
              <td><?= htmlspecialchars($project['description']) ?></td>
            </tr>
            <tr>
              <th><i class="bi bi-cash-stack"></i> Target</th>
              <td>$<?= number_format($target, 2) ?></td>
            </tr>
            <tr>
              <th><i class="bi bi-wallet2"></i> Current</th>
              <td>$<?= number_format($current, 2) ?></td>
            </tr>
            <tr>
              <th><i class="bi bi-clipboard-check"></i> Status</th>
              <td><?= htmlspecialchars($project['status']) ?></td>
            </tr>
            <tr>
              <th><i class="bi bi-calendar3"></i> Deadline</th>
              <td><?= htmlspecialchars($project['deadline']) ?></td>
            </tr>
            <tr>
              <th><i class="bi bi-bar-chart-line-fill"></i> Progress</th>
              <td>
                <div class="progress" style="height:8px;">
                  <div class="progress-bar <?= $progress < 50 ? 'bg-danger' : 'bg-success' ?>" style="width:<?= $progress ?>%;"></div>
                </div>
                <small><?= $progress ?>% Complete</small>
              </td>
            </tr>
          </tbody>
        </table>

        <!-- Buttons -->
        <div class="mt-3 text-start">
          <a href="edit_project.php?id=<?= $project['id'] ?>" class="btn btn-warning"><i class="bi bi-pencil"></i> Edit</a>
          <a href="list_projects.php" class="btn btn-secondary"><i class="bi bi-arrow-left"></i> Back</a>
        </div>

        <!-- Graph -->
        <h6 class="mt-4 mb-2"><i class="bi bi-graph-up"></i> Progress Graph</h6>
        <div class="chart-container">
          <canvas id="progressChart"></canvas>
        </div>
      </div>
    </main>
  </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script src="https://code.jquery.com/jquery-3.7.0.min.js"></script>
<script>
// Sidebar dropdown toggle
$('.menu-toggle').click(function(){
    $(this).next('.submenu').slideToggle();
});

// Chart
const ctx = document.getElementById('progressChart').getContext('2d');
new Chart(ctx, {
    type: 'line',
    data: {
        labels: ['0%', 'Target', 'Current'],
        datasets: [{
            label: 'Progress',
            data: [0, <?= $target ?>, <?= $current ?>],
            borderColor: '<?= $progress < 50 ? "#dc3545" : "#198754" ?>',
            backgroundColor: 'rgba(0,0,0,0)',
            tension: 0.4,
            fill: false,
            pointRadius: 5
        }]
    },
    options: {
        responsive: true,
        maintainAspectRatio: false,
        plugins: { legend: { display: false } },
        scales: { y: { beginAtZero: true } }
    }
});
</script>
</body>
</html>
