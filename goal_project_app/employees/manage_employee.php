<?php
session_start();
require_once "../config/db.php";

// Pagination
$limit = 12;
$page = isset($_GET['page']) && is_numeric($_GET['page']) ? max(1,(int)$_GET['page']) : 1;
$offset = ($page-1)*$limit;

// Count active employees
$totalEmployees = (int)$pdo->query("SELECT COUNT(*) FROM employees WHERE status='active'")->fetchColumn();
$totalPages = $totalEmployees > 0 ? ceil($totalEmployees/$limit) : 1;

// Fetch active employees
$stmt = $pdo->prepare("
    SELECT id, employee_id, full_name, nid, phone, district, sex, status, photo
    FROM employees
    WHERE status='active'
    ORDER BY full_name ASC
    LIMIT :limit OFFSET :offset
");
$stmt->bindValue(':limit',$limit,PDO::PARAM_INT);
$stmt->bindValue(':offset',$offset,PDO::PARAM_INT);
$stmt->execute();
$employees = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>Manage Employees</title>
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
<link href="https://cdn.jsdelivr.net/npm/bootstrap-icons/font/bootstrap-icons.css" rel="stylesheet">
<style>
body { font-family: 'Inter', sans-serif; background:#f6f7fb; }
.sidebar { width:260px; min-height:100vh; background:#fff; border-right:1px solid #e5e7eb; padding:1.5rem; position:fixed; top:0; left:0; }
.sidebar h4 { color:#0d6efd; margin-bottom:1.5rem; }
.sidebar .nav-link { color:#333; border-radius:6px; padding:.5rem .75rem; font-weight:500; display:flex; align-items:center; gap:8px; }
.sidebar .nav-link.active, .sidebar .nav-link:hover { color:#0d6efd; background:#f1f5f9; }
.content { margin-left:280px; padding:24px; }
.card-modern { background:#fff; border-radius:12px; box-shadow:0 6px 18px rgba(16,24,40,0.06); border:1px solid rgba(16,24,40,0.04); padding:20px; }
.table td, .table th { vertical-align: middle; text-align:center; }
img.employee-photo { width:50px; height:50px; object-fit:cover; border-radius:50%; cursor:pointer; }
.actions-btns a { margin-right:5px; margin-bottom:2px; }
@media (max-width:768px){ .sidebar{position:relative;width:100%;min-height:auto;} .content{margin-left:0;} }
</style>
</head>
<body>

<!-- Sidebar -->
<aside class="sidebar d-flex flex-column">
  <h4><i class="bi bi-people-fill me-2"></i> Employee Panel</h4>
  <nav class="nav flex-column">
    <a class="nav-link" href="dashboard.php"><i class="bi bi-speedometer2 me-2"></i> Dashboard</a>
    <a class="nav-link" href="add_employee.php"><i class="bi bi-plus-circle me-2"></i> Add Employee</a>
    <a class="nav-link active" href="manage_employee.php"><i class="bi bi-list-task me-2"></i> Manage Employee</a>
    <a class="nav-link" href="fired_employees.php"><i class="bi bi-person-dash me-2"></i> Fired Employees</a>
  </nav>
</aside>

<main class="content">
  <h3 class="mb-4"><i class="bi bi-people-fill me-2 text-primary"></i> Manage Employees</h3>

  <input id="searchInput" class="form-control mb-3" placeholder="Search employees..." autocomplete="off">

  <div class="card-modern table-responsive">
    <table class="table table-hover align-middle">
      <thead>
        <tr>
          <th>Photo</th>
          <th>Employee ID</th>
          <th>Full Name</th>
          <th>NID</th>
          <th>Phone</th>
          <th>District</th>
          <th>Sex</th>
          <th>Status</th>
          <th>Action</th>
        </tr>
      </thead>
      <tbody id="employeeTable">
        <?php foreach($employees as $e): ?>
        <tr>
          <td>
            <?php if (!empty($e['photo']) && file_exists("../" . $e['photo'])): ?>
              <a href="../<?= htmlspecialchars($e['photo']) ?>" target="_blank">
                <img src="../<?= htmlspecialchars($e['photo']) ?>" class="employee-photo" alt="Photo">
              </a>
            <?php else: ?>
              <i class="bi bi-person-circle text-secondary fs-3"></i>
            <?php endif; ?>
          </td>
          <td><?= htmlspecialchars($e['employee_id']) ?></td>
          <td><?= htmlspecialchars($e['full_name']) ?></td>
          <td><?= htmlspecialchars($e['nid']) ?></td>
          <td><?= htmlspecialchars($e['phone']) ?></td>
          <td><?= htmlspecialchars($e['district']) ?></td>
          <td><?= htmlspecialchars($e['sex']) ?></td>
          <td><span class="badge bg-success">Active</span></td>
          <td class="actions-btns">
            <a href="view_employee.php?id=<?= $e['id'] ?>" class="btn btn-sm btn-info"><i class="bi bi-eye"></i></a>
            <a href="edit_employee.php?id=<?= $e['id'] ?>" class="btn btn-sm btn-warning"><i class="bi bi-pencil"></i></a>
            <a href="fire_employee.php?id=<?= $e['id'] ?>" class="btn btn-sm btn-danger" onclick="return confirm('Fire this employee?');"><i class="bi bi-person-x"></i></a>
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
</main>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
<script>
document.getElementById('searchInput').addEventListener('input', function(){
  const term = this.value.toLowerCase();
  document.querySelectorAll('#employeeTable tr').forEach(tr=>{
    tr.style.display = tr.textContent.toLowerCase().includes(term) ? '' : 'none';
  });
});
</script>
</body>
</html>
