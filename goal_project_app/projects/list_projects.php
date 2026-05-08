<?php
session_start();
if(!isset($_SESSION['user_id'])){
    header("Location: ../login.php");
    exit;
}

require_once "../config/db.php";

/* --- Pagination setup --- */
$limit = 10;
$page = isset($_GET['page']) && is_numeric($_GET['page']) ? max(1, (int)$_GET['page']) : 1;
$offset = ($page - 1) * $limit;

/* --- Fetch projects with category --- */
$totalProjects = (int)$pdo->query("SELECT COUNT(*) FROM projects")->fetchColumn();
$totalPages = $totalProjects > 0 ? ceil($totalProjects / $limit) : 1;
if($page > $totalPages) $page = $totalPages;

$stmt = $pdo->prepare("
    SELECT p.*, c.name AS category
    FROM projects p
    LEFT JOIN project_categories c ON p.category_id = c.id
    ORDER BY p.created_at DESC
    LIMIT :limit OFFSET :offset
");
$stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
$stmt->bindValue(':offset', $offset, PDO::PARAM_INT);
$stmt->execute();
$projects = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>Project List</title>
<meta name="viewport" content="width=device-width, initial-scale=1">
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
<link href="https://cdn.jsdelivr.net/npm/bootstrap-icons/font/bootstrap-icons.css" rel="stylesheet">
<style>
body { font-family: 'Inter', sans-serif; background:#f6f7fb; }
.sidebar { width:260px; min-height:100vh; background:#fff; border-right:1px solid #e5e7eb; padding:1.5rem; position:fixed; top:0; left:0; }
.sidebar h4 { color:#0d6efd; margin-bottom:1.5rem; }
.sidebar .nav-link { color:#333; border-radius:6px; padding:.5rem .75rem; font-weight:500; display:flex; align-items:center; gap:8px; }
.sidebar .nav-link.active, .sidebar .nav-link:hover { color:#0d6efd; background:#f1f5f9; }
.sidebar .submenu { padding-left:1.5rem; }
.content { margin-left:280px; padding:24px; }
.card-modern { background:#fff; border-radius:12px; box-shadow:0 6px 18px rgba(16,24,40,0.06); border:1px solid rgba(16,24,40,0.04); padding:20px; }
.table td, .table th { vertical-align: middle; }
.search-box { max-width:360px; }
.footer { text-align:center; color:#6c757d; padding:18px 0; margin-top:28px; border-top:1px solid rgba(16,24,40,0.06); }
@media (max-width:768px){ .sidebar{position:relative;width:100%;min-height:auto;} .content{margin-left:0;} }
</style>
</head>
<body>

<!-- Sidebar -->
<aside class="sidebar d-flex flex-column">
  <h4><i class="bi bi-kanban me-2"></i> Projects Panel</h4>
  <nav class="nav flex-column">
    <a class="nav-link" href="../index.php"><i class="bi bi-speedometer2 me-2"></i> Dashboard</a>
    <a class="nav-link" data-bs-toggle="collapse" href="#projectMenu" role="button" aria-expanded="true">
      <i class="bi bi-kanban me-2"></i> Projects
    </a>
    <div class="collapse show submenu" id="projectMenu">
      <a class="nav-link" href="add_project.php"><i class="bi bi-plus-circle me-2"></i> Add Project</a>
      <a class="nav-link active" href="list_projects.php"><i class="bi bi-list-task me-2"></i> List Projects</a>
      <a class="nav-link" href="manage_categories.php"><i class="bi bi-tags me-2"></i> Project Categories</a>
    </div>
    <a class="nav-link text-danger mt-auto" href="../logout.php"><i class="bi bi-box-arrow-right me-2"></i> Logout</a>
  </nav>
</aside>

<!-- Main content -->
<main class="content">
  <h3 class="mb-4"><i class="bi bi-list-task me-2 text-primary"></i> Project List</h3>

  <div class="d-flex justify-content-between align-items-center mb-3">
    <input id="searchInput" class="form-control search-box" placeholder="Search projects..." autocomplete="off">
  </div>

  <div class="card-modern table-responsive">
    <table class="table table-hover align-middle">
      <thead>
        <tr>
          <th>Title</th>
          <th>Description</th>
          <th>Start Date</th>
          <th>Deadline</th>
          <th>Target</th>
          <th>Current</th>
          <th>Progress</th>
          <th>Status</th>
          <th>Category</th>
          <th>Action</th>
        </tr>
      </thead>
      <tbody id="projectTable">
        <?php foreach($projects as $p): ?>
        <tr>
          <td><?= htmlspecialchars($p['title']) ?></td>
          <td><?= htmlspecialchars($p['description']) ?></td>
          <td><?= htmlspecialchars($p['start_date']) ?></td>
          <td><?= htmlspecialchars($p['deadline']) ?></td>
          <td>$<?= number_format($p['target_amount'],2) ?></td>
          <td>$<?= number_format($p['current_amount'],2) ?></td>
          <td>
            <div class="progress" style="height:8px;">
              <div class="progress-bar <?= $p['progress']<50?'bg-danger':'bg-success' ?>" style="width:<?= (int)$p['progress'] ?>%;"></div>
            </div>
            <small><?= (int)$p['progress'] ?>%</small>
          </td>
          <td><?= htmlspecialchars($p['status']) ?></td>
          <td><?= htmlspecialchars($p['category'] ?? '-') ?></td>
          <td>
            <a href="view_project.php?id=<?= $p['id'] ?>" class="btn btn-sm btn-info"><i class="bi bi-eye"></i></a>
            <a href="edit_project.php?id=<?= $p['id'] ?>" class="btn btn-sm btn-warning"><i class="bi bi-pencil"></i></a>
            <a href="delete_project.php?id=<?= $p['id'] ?>" class="btn btn-sm btn-danger" onclick="return confirm('Are you sure?');"><i class="bi bi-trash"></i></a>
          </td>
        </tr>
        <?php endforeach; ?>
      </tbody>
    </table>

    <!-- Pagination -->
    <nav class="mt-3">
      <ul class="pagination justify-content-center">
        <li class="page-item <?= ($page<=1)?'disabled':'' ?>">
          <a class="page-link" href="?page=<?= max(1,$page-1) ?>">Previous</a>
        </li>
        <li class="page-item disabled"><span class="page-link">Page <?= $page ?> of <?= $totalPages ?></span></li>
        <li class="page-item <?= ($page>=$totalPages)?'disabled':'' ?>">
          <a class="page-link" href="?page=<?= min($totalPages,$page+1) ?>">Next</a>
        </li>
      </ul>
    </nav>
  </div>

  <div class="footer">&copy; <?= date('Y') ?> Project Management System</div>
</main>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
<script>
// Live search with suggestion
document.getElementById('searchInput').addEventListener('input', function(){
  const term = this.value.toLowerCase();
  document.querySelectorAll('#projectTable tr').forEach(tr=>{
    tr.style.display = tr.textContent.toLowerCase().includes(term) ? '' : 'none';
  });
});
</script>
</body>
</html>
