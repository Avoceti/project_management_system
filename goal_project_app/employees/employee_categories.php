<?php
if (session_status() === PHP_SESSION_NONE) session_start();
if(!isset($_SESSION['user_id'])){
    header("Location: ../login.php");
    exit;
}
require_once "../config/db.php";

// Pagination setup
$limit = 12;
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
if ($page < 1) $page = 1;
$offset = ($page - 1) * $limit;

$search = $_GET['search'] ?? '';
$searchQuery = "";
$params = [];

if ($search) {
    $searchQuery = "WHERE name LIKE :search";
    $params[':search'] = "%$search%";
}

// Count total
$stmt = $pdo->prepare("SELECT COUNT(*) FROM employee_categories $searchQuery");
$stmt->execute($params);
$total = $stmt->fetchColumn();
$totalPages = ceil($total / $limit);

// Fetch categories
$stmt = $pdo->prepare("SELECT * FROM employee_categories $searchQuery ORDER BY name ASC LIMIT :limit OFFSET :offset");
foreach ($params as $k => &$v) $stmt->bindParam($k, $v, PDO::PARAM_STR);
$stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
$stmt->bindValue(':offset', $offset, PDO::PARAM_INT);
$stmt->execute();
$categories = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Employee Categories</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
  <style>
    body { background:#f8f9fa; }
    .sidebar {
        width: 240px;
        height: 100vh;
        position: fixed;
        left: 0;
        top: 0;
        background: rgba(255, 255, 255, 1);
        padding-top: 20px;
    }
    .sidebar h4 {
        color: rgba(37, 36, 36, 1);
        text-align: center;
        margin-bottom: 20px;
    }
    .sidebar a {
        display: block;
        color: #060606ff;
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
    .table th, .table td { vertical-align: middle; }
  </style>
</head>
<body>
  <!-- Sidebar -->
  <div class="sidebar">
      <h4>Dashboard</h4>
      <a href="dashboard.php">🏠 Dashboard</a>
      <a href="add_payments.php">💳 Add Payments</a>
      <a href="manage_employee.php">👥 Manage Employees</a>
      <a href="employee_categories.php" class="active">📂 Employee Categories</a>
  </div>

  <!-- Content -->
  <div class="content">
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h3>Employee Categories</h3>
        <a href="employee_categories_add.php" class="btn btn-primary btn-sm">+ Add Category</a>
    </div>

    <!-- Live Search -->
    <div class="mb-3">
        <input type="text" id="liveSearch" value="<?=htmlspecialchars($search)?>" class="form-control" placeholder="Search categories...">
    </div>

    <div class="card">
        <div class="card-body p-0">
            <div class="table-responsive" id="tableData">
                <table class="table table-striped table-hover mb-0">
                    <thead class="table-dark">
                        <tr>
                            <th>ID</th>
                            <th>Name</th>
                            <th>Created At</th>
                            <th>Updated At</th>
                            <th width="180">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if($categories): ?>
                            <?php foreach($categories as $cat): ?>
                                <tr>
                                    <td><?=$cat['id']?></td>
                                    <td><?=htmlspecialchars($cat['name'])?></td>
                                    <td><?=$cat['created_at']?></td>
                                    <td><?=$cat['updated_at']?></td>
                                    <td>
                                        <a href="employee_categories_edit.php?id=<?=$cat['id']?>" class="btn btn-sm btn-warning">Edit</a>
                                        <a href="employee_categories_delete.php?id=<?=$cat['id']?>" class="btn btn-sm btn-danger" onclick="return confirm('Delete this category?')">Delete</a>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <tr><td colspan="5" class="text-center">No categories found.</td></tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <!-- Pagination -->
    <nav class="mt-3">
        <ul class="pagination justify-content-center">
            <?php if ($page > 1): ?>
                <li class="page-item"><a class="page-link" href="?page=<?=$page-1?>&search=<?=urlencode($search)?>">Previous</a></li>
            <?php endif; ?>

            <li class="page-item disabled"><span class="page-link">Page <?=$page?> of <?=$totalPages?></span></li>

            <?php if ($page < $totalPages): ?>
                <li class="page-item"><a class="page-link" href="?page=<?=$page+1?>&search=<?=urlencode($search)?>">Next</a></li>
            <?php endif; ?>
        </ul>
    </nav>
  </div>

  <script>
    document.getElementById('liveSearch').addEventListener('keyup', function() {
        let query = this.value;
        let xhr = new XMLHttpRequest();
        xhr.open('GET', 'search_categories.php?search=' + encodeURIComponent(query), true);
        xhr.onload = function() {
            if (xhr.status === 200) {
                document.getElementById('tableData').innerHTML = xhr.responseText;
            }
        };
        xhr.send();
    });
  </script>
</body>
</html>
