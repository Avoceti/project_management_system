<?php
if (session_status() === PHP_SESSION_NONE) session_start();
if (!isset($_SESSION['user_id'])) {
    header("Location: ../login.php");
    exit;
}

require_once __DIR__ . "/../config/db.php";

/* ---------- Pagination Setup ---------- */
$perPage = 5;
$page = isset($_GET['page']) && is_numeric($_GET['page']) ? (int) $_GET['page'] : 1;
$offset = ($page - 1) * $perPage;

/* ---------- Search ---------- */
$search = $_GET['search'] ?? '';

/* ---------- Count total ---------- */
$countSql = "SELECT COUNT(*) FROM goals WHERE title LIKE :search";
$stmtCount = $pdo->prepare($countSql);
$stmtCount->execute(['search' => "%$search%"]);
$totalItems = $stmtCount->fetchColumn();
$totalPages = ceil($totalItems / $perPage);

/* ---------- Fetch data ---------- */
$sql = "SELECT * FROM goals 
        WHERE title LIKE :search 
        ORDER BY created_at DESC 
        LIMIT :limit OFFSET :offset";
$stmt = $pdo->prepare($sql);
$stmt->bindValue(':search', "%$search%", PDO::PARAM_STR);
$stmt->bindValue(':limit', $perPage, PDO::PARAM_INT);
$stmt->bindValue(':offset', $offset, PDO::PARAM_INT);
$stmt->execute();
$goals = $stmt->fetchAll();
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>List Goals</title>
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
<link href="https://cdn.jsdelivr.net/npm/bootstrap-icons/font/bootstrap-icons.css" rel="stylesheet">
<style>
body { background:#f8f9fa; }
.sidebar {
    min-height: 100vh;
    width: 220px;
    background: #fff;
    border-right: 1px solid #dee2e6;
    padding: 1rem;
    position: fixed;
    top: 0;
    left: 0;
    overflow-y: auto;
}
.sidebar h5 { font-weight: 600; color: #0d6efd; margin-bottom: 1rem; }
.sidebar .nav-link { color:#333; border-radius:.4rem; margin:.2rem 0; display:flex; align-items:center; gap:6px; }
.sidebar .nav-link:hover, .sidebar .nav-link.active { background:#f8f9fa; color:#0d6efd; }
.content { margin-left: 230px; padding: 2rem; }
@media(max-width:768px){
    .sidebar { position: relative; width: 100%; min-height:auto; border-right:none; }
    .content { margin-left:0; padding:1rem; }
}
</style>
</head>
<body>

<div class="sidebar">
    <h5><i class="bi bi-speedometer2"></i> Dashboard</h5>
    <ul class="nav flex-column">
        <li><a class="nav-link" href="../index.php"><i class="bi bi-house"></i> Home</a></li>
        <li>
            <a class="nav-link" data-bs-toggle="collapse" href="#goalsMenu"><i class="bi bi-flag"></i> Goals</a>
            <div class="collapse show" id="goalsMenu">
                <ul class="nav flex-column ms-3">
                    <li><a class="nav-link" href="add_goal.php"><i class="bi bi-plus-circle"></i> Add Goal</a></li>
                    <li><a class="nav-link active" href="list_goals.php"><i class="bi bi-list-task"></i> List Goals</a></li>
                </ul>
            </div>
        </li>
        <li>
            <a class="nav-link" data-bs-toggle="collapse" href="#projectsMenu"><i class="bi bi-kanban"></i> Projects</a>
            <div class="collapse" id="projectsMenu">
                <ul class="nav flex-column ms-3">
                    <li><a class="nav-link" href="../projects/add_project.php"><i class="bi bi-plus-circle"></i> Add Project</a></li>
                    <li><a class="nav-link" href="../projects/list_projects.php"><i class="bi bi-list-task"></i> List Projects</a></li>
                </ul>
            </div>
        </li>
        <li><a class="nav-link text-danger" href="../logout.php"><i class="bi bi-box-arrow-right"></i> Logout</a></li>
    </ul>
</div>

<div class="content">
    <h3 class="mb-4"><i class="bi bi-flag text-primary"></i> Goals</h3>

    <!-- Search -->
    <form method="get" class="mb-3">
        <div class="input-group">
            <input type="text" name="search" value="<?= htmlspecialchars($search) ?>" class="form-control" placeholder="Search goals by title...">
            <button class="btn btn-primary" type="submit"><i class="bi bi-search"></i></button>
            <a href="list_goals.php" class="btn btn-secondary">Reset</a>
        </div>
    </form>

    <!-- Table -->
    <div class="card shadow-sm">
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-hover align-middle">
                    <thead class="table-light">
                        <tr>
                            <th>Title</th>
                            <th>Target</th>
                            <th>Current</th>
                            <th>Progress</th>
                            <th>Status</th>
                            <th>Deadline</th>
                            <th>Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (empty($goals)): ?>
                            <tr><td colspan="7" class="text-center text-muted">No goals found</td></tr>
                        <?php else: ?>
                            <?php foreach ($goals as $goal): 
                                $target = $goal['target_amount'] ?? 0;
                                $current = $goal['current_amount'] ?? 0;
                                $progress = $target > 0 ? round(($current / $target) * 100, 2) : 0;
                            ?>
                            <tr>
                                <td><?= htmlspecialchars($goal['title']) ?></td>
                                <td>$<?= number_format($target, 2) ?></td>
                                <td>$<?= number_format($current, 2) ?></td>
                                <td>
                                    <div class="progress" style="height:6px;">
                                        <div class="progress-bar <?= $progress < 50 ? 'bg-danger' : 'bg-success' ?>" style="width: <?= $progress ?>%;"></div>
                                    </div>
                                    <small><?= $progress ?>%</small>
                                </td>
                                <td><?= htmlspecialchars($goal['status']) ?></td>
                                <td><?= htmlspecialchars($goal['deadline']) ?></td>
                                <td>
                                    <a href="view_goal.php?id=<?= $goal['id'] ?>" class="btn btn-sm btn-primary"><i class="bi bi-eye"></i></a>
                                    <a href="edit_goal.php?id=<?= $goal['id'] ?>" class="btn btn-sm btn-warning"><i class="bi bi-pencil"></i></a>
                                    <button class="btn btn-sm btn-danger btn-delete" data-id="<?= $goal['id'] ?>"><i class="bi bi-trash"></i></button>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>

            <!-- Pagination -->
            <?php if ($totalPages > 1): ?>
            <nav>
                <ul class="pagination justify-content-center">
                    <li class="page-item <?= ($page <= 1) ? 'disabled' : '' ?>">
                        <a class="page-link" href="?search=<?= urlencode($search) ?>&page=<?= $page - 1 ?>">Previous</a>
                    </li>
                    <?php for ($i = 1; $i <= $totalPages; $i++): ?>
                        <li class="page-item <?= ($i == $page) ? 'active' : '' ?>">
                            <a class="page-link" href="?search=<?= urlencode($search) ?>&page=<?= $i ?>"><?= $i ?></a>
                        </li>
                    <?php endfor; ?>
                    <li class="page-item <?= ($page >= $totalPages) ? 'disabled' : '' ?>">
                        <a class="page-link" href="?search=<?= urlencode($search) ?>&page=<?= $page + 1 ?>">Next</a>
                    </li>
                </ul>
            </nav>
            <?php endif; ?>
        </div>
    </div>
</div>

<script src="https://code.jquery.com/jquery-3.7.0.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
<script>
$(document).on('click', '.btn-delete', function(){
    if(confirm('Are you sure you want to delete this goal?')){
        let id = $(this).data('id');
        $.post('../ajax/delete_item.php', {type:'Goal', id:id}, function(){
            location.reload();
        });
    }
});
</script>
</body>
</html>
