<?php
session_start();
if(!isset($_SESSION['user_id'])){
    header("Location: ../login.php");
    exit;
}
require_once __DIR__ . '/../config/db.php';

// Pagination
$limit  = 10;
$page   = isset($_GET['page']) && is_numeric($_GET['page']) ? (int)$_GET['page'] : 1;
if($page < 1) $page = 1;
$offset = ($page-1) * $limit;

// Filter by employee (if ?id=123 passed)
$employee_id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
$employee = null;

if($employee_id){
    $stmt = $pdo->prepare("SELECT * FROM employees WHERE id=? LIMIT 1");
    $stmt->execute([$employee_id]);
    $employee = $stmt->fetch(PDO::FETCH_ASSOC);
    if(!$employee){
        die("Employee not found.");
    }

    $stmt = $pdo->prepare("SELECT * FROM employee_attendance WHERE employee_id=:employee_id ORDER BY date DESC LIMIT :limit OFFSET :offset");
    $stmt->bindValue(':employee_id', $employee_id, PDO::PARAM_INT);
    $stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
    $stmt->bindValue(':offset', $offset, PDO::PARAM_INT);
    $stmt->execute();
    $attendance = $stmt->fetchAll(PDO::FETCH_ASSOC);

    $stmtTotal = $pdo->prepare("SELECT COUNT(*) FROM employee_attendance WHERE employee_id=?");
    $stmtTotal->execute([$employee_id]);
    $totalRecords = (int)$stmtTotal->fetchColumn();

} else {
    $stmt = $pdo->prepare("
        SELECT a.*, e.full_name 
        FROM employee_attendance a
        LEFT JOIN employees e ON a.employee_id = e.id
        ORDER BY a.date DESC 
        LIMIT :limit OFFSET :offset
    ");
    $stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
    $stmt->bindValue(':offset', $offset, PDO::PARAM_INT);
    $stmt->execute();
    $attendance = $stmt->fetchAll(PDO::FETCH_ASSOC);

    $stmtTotal = $pdo->query("SELECT COUNT(*) FROM employee_attendance");
    $totalRecords = (int)$stmtTotal->fetchColumn();
}

$totalPages = $totalRecords > 0 ? ceil($totalRecords / $limit) : 1;

// Summary counts
$stats = ['present'=>0, 'absent'=>0, 'late'=>0];
foreach($attendance as $row){
    $status = strtolower($row['status'] ?? '');
    if($status === 'present'){
        $stats['present']++;
    } elseif($status === 'absent'){
        $stats['absent']++;
    } else {
        $stats['late']++; // Treat empty status as Late
    }
}

// Helper to format time AM/PM
function formatTime($time){
    if(!$time) return '-';
    return date("h:i A", strtotime($time));
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>Employee Attendance</title>
<meta name="viewport" content="width=device-width,initial-scale=1">
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
<link href="https://cdn.jsdelivr.net/npm/bootstrap-icons/font/bootstrap-icons.css" rel="stylesheet">
<style>
body { background:#f6f7fb; font-family: Inter, system-ui, -apple-system, Segoe UI, Roboto, sans-serif; }
.sidebar { width:230px; min-height:100vh; background:#fff; border-right:1px solid #e5e7eb; padding:1rem; position:fixed; top:0; left:0; }
.sidebar h4 { color:#0d6efd; margin-bottom:1.2rem; font-size:1.2rem; }
.sidebar .nav-link { color:#333; border-radius:6px; padding:.45rem .65rem; font-weight:500; font-size:0.95rem; margin-bottom:4px; }
.sidebar .nav-link.active, .sidebar .nav-link:hover { color:#0d6efd; background:#f1f5f9; }
.sidebar .submenu { padding-left:1.2rem; }
.content { margin-left:250px; padding:24px; }
.card-modern { border-radius:12px; box-shadow:0 6px 18px rgba(16,24,40,0.06); border:1px solid rgba(16,24,40,0.04); padding:20px; background:#fff; }
.table td, .table th { vertical-align:middle; }
@media (max-width:768px){ .sidebar{position:relative;width:100%;min-height:auto;} .content{margin-left:0;} }
</style>
</head>
<body>

<!-- Sidebar -->
<aside class="sidebar d-flex flex-column">
  <h4><i class="bi bi-people-fill me-2"></i> Employee Panel</h4>
  <nav class="nav flex-column">
    <a class="nav-link" href="dashboard.php"><i class="bi bi-speedometer2 me-2"></i> Dashboard</a>
    <a class="nav-link" href="manage_employee.php"><i class="bi bi-list-task me-2"></i> Manage Employees</a>
    <a class="nav-link active" href="employee_attendance.php"><i class="bi bi-calendar-check me-2"></i> Attendance</a>
    <a class="nav-link" href="../index.php"><i class="bi bi-arrow-left me-2"></i> Back to Main</a>
    <a class="nav-link text-danger mt-auto" href="../logout.php"><i class="bi bi-box-arrow-right me-2"></i> Logout</a>
  </nav>
</aside>

<!-- Main -->
<main class="content">
  <h3 class="mb-4"><i class="bi bi-calendar-check me-2 text-primary"></i> Employee Attendance</h3>

  <!-- Summary cards -->
  <div class="row mb-4">
    <div class="col-md-4">
      <div class="card-modern text-center">
        <h6 class="text-success"><i class="bi bi-check-circle-fill"></i> Present</h6>
        <h3><?= $stats['present'] ?></h3>
      </div>
    </div>
    <div class="col-md-4">
      <div class="card-modern text-center">
        <h6 class="text-danger"><i class="bi bi-x-circle-fill"></i> Absent</h6>
        <h3><?= $stats['absent'] ?></h3>
      </div>
    </div>
    <div class="col-md-4">
      <div class="card-modern text-center">
        <h6 class="text-warning"><i class="bi bi-exclamation-triangle-fill"></i> Late</h6>
        <h3><?= $stats['late'] ?></h3>
      </div>
    </div>
  </div>

  <?php if($employee): ?>
    <div class="card-modern mb-4">
      <h5><i class="bi bi-person-badge me-2"></i> Attendance for <?= htmlspecialchars($employee['full_name']) ?></h5>
    </div>
  <?php endif; ?>

  <div class="card-modern">
    <div class="d-flex justify-content-between align-items-center mb-3">
      <h5 class="mb-0"><i class="bi bi-clock-history me-2 text-success"></i> Records</h5>
      <input id="attendanceSearch" class="form-control w-auto" placeholder="Search..." />
    </div>
    <div class="table-responsive">
      <table class="table table-hover align-middle">
        <thead>
          <tr>
            <?php if(!$employee): ?><th>Employee</th><?php endif; ?>
            <th>Date</th>
            <th>Status</th>
            <th>Check In</th>
            <th>Check Out</th>
            <th>Notes</th>
            <th>Action</th>
          </tr>
        </thead>
        <tbody id="attendanceTable">
          <?php foreach($attendance as $row): ?>
            <tr>
              <?php if(!$employee): ?><td><?= htmlspecialchars($row['full_name'] ?? '-') ?></td><?php endif; ?>
              <td><?= htmlspecialchars($row['date']) ?></td>
              <td>
                <?php 
                    $status = strtolower($row['status'] ?? '');
                    if($status === 'present'): ?>
                        <span class="badge bg-success">Present</span>
                    <?php elseif($status === 'absent'): ?>
                        <span class="badge bg-danger">Absent</span>
                    <?php else: ?>
                        <span class="badge bg-warning text-dark">Late</span>
                <?php endif; ?>
              </td>
              <td><?= formatTime($row['check_in'] ?? '') ?></td>
              <td><?= formatTime($row['check_out'] ?? '') ?></td>
              <td><?= htmlspecialchars($row['notes'] ?? '-') ?></td>
              <td>
                <a href="edit_attendance.php?id=<?= $row['id'] ?>" class="btn btn-sm btn-outline-primary"><i class="bi bi-pencil"></i></a>
                <a href="delete_attendance.php?id=<?= $row['id'] ?>" onclick="return confirm('Delete this record?')" class="btn btn-sm btn-outline-danger"><i class="bi bi-trash"></i></a>
              </td>
            </tr>
          <?php endforeach; ?>
        </tbody>
      </table>
    </div>

    <!-- Pagination -->
    <nav class="mt-3">
      <ul class="pagination justify-content-center">
        <li class="page-item <?= ($page<=1)?'disabled':'' ?>">
          <a class="page-link" href="?<?= $employee_id ? "id=$employee_id&" : "" ?>page=<?= max(1,$page-1) ?>">Previous</a>
        </li>
        <li class="page-item disabled"><span class="page-link">Page <?= $page ?> of <?= $totalPages ?></span></li>
        <li class="page-item <?= ($page>=$totalPages)?'disabled':'' ?>">
          <a class="page-link" href="?<?= $employee_id ? "id=$employee_id&" : "" ?>page=<?= min($totalPages,$page+1) ?>">Next</a>
        </li>
      </ul>
    </nav>
  </div>
</main>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
<script>
document.getElementById('attendanceSearch').addEventListener('input', function(){
  const term = this.value.toLowerCase();
  document.querySelectorAll('#attendanceTable tr').forEach(tr=>{
    tr.style.display = tr.textContent.toLowerCase().includes(term) ? '' : 'none';
  });
});
</script>
</body>
</html>
