<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    header("Location: ../login.php");
    exit;
}

require_once "../config/db.php";

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $employee_id = $_POST['employee_id'] ?? '';
    $date = $_POST['date'] ?? date('Y-m-d');
    $status = $_POST['status'] ?? '';
    $check_in = $_POST['check_in'] ?? '';
    $check_out = $_POST['check_out'] ?? '';
    $notes = $_POST['notes'] ?? '';

    // Automatically mark Late if status empty and check-in is after 07:30:00
    if (empty($status) && !empty($check_in) && $check_in > '07:30:00') {
        $status = 'Late';
        $notes = trim($notes . ' Late');
    }

    if (!empty($employee_id) && !empty($date) && !empty($status)) {
        $stmt = $pdo->prepare("INSERT INTO employee_attendance 
            (employee_id, date, status, check_in, check_out, notes, created_at, updated_at) 
            VALUES (?, ?, ?, ?, ?, ?, NOW(), NOW())");
        $stmt->execute([$employee_id, $date, $status, $check_in, $check_out, $notes]);

        header("Location: add_attendance.php?success=1");
        exit;
    } else {
        $error = "Please fill in all required fields.";
    }
}

// Attendance summary cards
$totalPresent = (int)$pdo->query("SELECT COUNT(*) FROM employee_attendance WHERE status='Present'")->fetchColumn();
$totalAbsent = (int)$pdo->query("SELECT COUNT(*) FROM employee_attendance WHERE status='Absent'")->fetchColumn();
$totalLate = (int)$pdo->query("SELECT COUNT(*) FROM employee_attendance WHERE status='Late' OR status=''")->fetchColumn();
$totalAttendance = $totalPresent + $totalAbsent + $totalLate;
$presentRate = $totalAttendance ? round(($totalPresent/$totalAttendance)*100,2) : 0;
$absentRate = $totalAttendance ? round(($totalAbsent/$totalAttendance)*100,2) : 0;
$lateRate = $totalAttendance ? round(($totalLate/$totalAttendance)*100,2) : 0;

// Pagination
$recordsPerPage = 50;
$page = isset($_GET['page']) ? max(1,(int)$_GET['page']) : 1;
$offset = ($page-1) * $recordsPerPage;
$totalRecords = (int)$pdo->query("SELECT COUNT(*) FROM employee_attendance")->fetchColumn();
$totalPages = ceil($totalRecords / $recordsPerPage);

// Fetch attendance records for current page
$stmt = $pdo->prepare("
    SELECT a.*, e.full_name, e.employee_id
    FROM employee_attendance a
    LEFT JOIN employees e ON a.employee_id = e.id
    ORDER BY a.date DESC
    LIMIT :offset, :limit
");
$stmt->bindValue(':offset', $offset, PDO::PARAM_INT);
$stmt->bindValue(':limit', $recordsPerPage, PDO::PARAM_INT);
$stmt->execute();
$attendanceRecords = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>Add Attendance</title>
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
<link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css" rel="stylesheet">
<style>
body { background-color: #f8f9fa; font-family: 'Inter', sans-serif; }
.sidebar { width: 250px; min-height: 100vh; background: #343a40; color: white; position: fixed; top: 0; left: 0; padding-top: 20px; }
.sidebar a { color: white; padding: 12px; display: block; text-decoration: none; }
.sidebar a:hover { background: #495057; }
.main-content { margin-left: 260px; padding: 20px; }
.card-modern { border-radius:12px; box-shadow:0 6px 18px rgba(16,24,40,0.06); border:1px solid rgba(16,24,40,0.04); }
.input-group-text { background-color: #f1f5f9; }
.table td, .table th { vertical-align: middle; }
</style>
</head>
<body>
<!-- Sidebar -->
<div class="sidebar">
<h4 class="text-center mb-4">Dashboard</h4>
<a href="../dashboard.php"><i class="bi bi-speedometer2 me-2"></i> Dashboard</a>
<a href="../employees/view_employee.php"><i class="bi bi-people me-2"></i> Employees</a>
<a href="../employee_attendance.php"><i class="bi bi-calendar-check me-2"></i> Attendance</a>
<a href="../payments.php"><i class="bi bi-cash me-2"></i> Payments</a>
<a href="../reports.php"><i class="bi bi-file-earmark-text me-2"></i> Reports</a>
<a href="../settings.php"><i class="bi bi-gear me-2"></i> Settings</a>
<a href="../logout.php"><i class="bi bi-box-arrow-right me-2"></i> Logout</a>
</div>

<div class="main-content">
<div class="container-fluid py-4">

<!-- Shift Cards -->
<div class="row g-4 mb-4">
  <div class="col-md-6">
    <div class="card card-modern p-3 text-center">
      <h6 class="text-muted"><i class="bi bi-sun-fill me-2 text-warning"></i> Day Shift</h6>
      <h5>7:30 AM - 4:30 PM</h5>
    </div>
  </div>
  <div class="col-md-6">
    <div class="card card-modern p-3 text-center">
      <h6 class="text-muted"><i class="bi bi-moon-fill me-2 text-primary"></i> Night Shift</h6>
      <h5>8:00 PM - 5:30 AM</h5>
    </div>
  </div>
</div>

<!-- Attendance Summary Cards -->
<div class="row g-4 mb-4">
  <div class="col-md-4">
    <div class="card card-modern p-3 text-center">
      <h6 class="text-muted"><i class="bi bi-check-circle-fill me-2 text-success"></i> Present</h6>
      <h3><?= $totalPresent ?> <small class="text-success">(<?= $presentRate ?>%)</small></h3>
    </div>
  </div>
  <div class="col-md-4">
    <div class="card card-modern p-3 text-center">
      <h6 class="text-muted"><i class="bi bi-x-circle-fill me-2 text-danger"></i> Absent</h6>
      <h3><?= $totalAbsent ?> <small class="text-danger">(<?= $absentRate ?>%)</small></h3>
    </div>
  </div>
  <div class="col-md-4">
    <div class="card card-modern p-3 text-center">
      <h6 class="text-muted"><i class="bi bi-exclamation-circle-fill me-2 text-warning"></i> Late</h6>
      <h3><?= $totalLate ?> <small class="text-warning">(<?= $lateRate ?>%)</small></h3>
    </div>
  </div>
</div>

<!-- Add Attendance Form -->
<div class="card card-modern shadow-lg mb-4">
<div class="card-header bg-primary text-white">
<h4 class="mb-0"><i class="bi bi-calendar-check"></i> Add Attendance</h4>
</div>
<div class="card-body">
<?php if (!empty($error)): ?>
  <div class="alert alert-danger"><?= htmlspecialchars($error) ?></div>
<?php endif; ?>
<?php if (isset($_GET['success']) && $_GET['success'] == 1): ?>
  <div class="alert alert-success alert-dismissible fade show" role="alert">
    <i class="bi bi-check-circle-fill"></i> Attendance has been saved successfully!
    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
  </div>
<?php endif; ?>

<form method="POST">
<div class="row mb-3">
  <div class="col-md-4 position-relative">
    <label class="form-label">Employee ID, NID or Name <span class="text-danger">*</span></label>
    <div class="input-group">
      <span class="input-group-text"><i class="bi bi-person-badge"></i></span>
      <input type="text" id="empSearch" class="form-control" placeholder="Type Employee ID, NID or Name">
    </div>
  </div>
  <div class="col-md-4 position-relative">
    <label class="form-label">Full Name</label>
    <div class="input-group">
      <span class="input-group-text"><i class="bi bi-person-fill"></i></span>
      <input type="text" id="empName" class="form-control" readonly>
    </div>
  </div>
  <div class="col-md-4">
    <label class="form-label">Date</label>
    <input type="date" name="date" class="form-control" value="<?= date('Y-m-d') ?>" readonly>
  </div>
</div>

<input type="hidden" name="employee_id" id="employee_id">

<div class="row mb-3">
  <div class="col-md-6">
    <label class="form-label">Status <span class="text-danger">*</span></label>
    <select name="status" class="form-select" required>
      <option value="Present">Present</option>
      <option value="Absent">Absent</option>
      <option value="Late">Late</option>
      <option value="Leave">Leave</option>
    </select>
  </div>
  <div class="col-md-3">
    <label class="form-label">Check In</label>
    <input type="time" name="check_in" class="form-control">
  </div>
  <div class="col-md-3">
    <label class="form-label">Check Out</label>
    <input type="time" name="check_out" class="form-control">
  </div>
</div>

<div class="mb-3">
  <label class="form-label">Notes</label>
  <textarea name="notes" class="form-control" rows="3"></textarea>
</div>

<div class="d-flex justify-content-end gap-2 mt-3">
  <button type="submit" class="btn btn-primary">
    <i class="bi bi-save"></i> Save Attendance
  </button>
  <a href="employee_attendance.php" class="btn btn-secondary">
    <i class="bi bi-x-circle"></i> Cancel
  </a>
</div>
</form>
</div>
</div>

<!-- Attendance Records Table -->
<div class="card card-modern shadow-lg mb-4">
<div class="card-header bg-secondary text-white">
<h5 class="mb-0"><i class="bi bi-table"></i> Attendance Records</h5>
</div>
<div class="card-body table-responsive">
<table class="table table-hover align-middle">
<thead>
<tr>
<th>Employee ID</th>
<th>Name</th>
<th>Date</th>
<th>Status</th>
<th>Check In</th>
<th>Check Out</th>
<th>Notes</th>
<th>Action</th>
</tr>
</thead>
<tbody>
<?php foreach($attendanceRecords as $att): ?>
<tr>
<td><?= htmlspecialchars($att['employee_id']) ?></td>
<td><?= htmlspecialchars($att['full_name']) ?></td>
<td><?= htmlspecialchars($att['date']) ?></td>
<td><?= htmlspecialchars($att['status'] ?: 'Late') ?></td>
<td><?= htmlspecialchars($att['check_in']) ?></td>
<td><?= htmlspecialchars($att['check_out']) ?></td>
<td><?= htmlspecialchars($att['notes']) ?></td>
<td>
  <a href="edit_attendance.php?id=<?= $att['id'] ?>" class="btn btn-sm btn-warning"><i class="bi bi-pencil-square"></i></a>
  <a href="delete_attendance.php?id=<?= $att['id'] ?>" class="btn btn-sm btn-danger" onclick="return confirm('Delete this record?')"><i class="bi bi-trash"></i></a>
</td>
</tr>
<?php endforeach; ?>
</tbody>
</table>

<!-- Pagination -->
<?php if($totalPages > 1): ?>
<nav class="mt-3">
  <ul class="pagination justify-content-center">
    <?php if($page>1): ?>
      <li class="page-item"><a class="page-link" href="?page=<?= $page-1 ?>">Previous</a></li>
    <?php else: ?>
      <li class="page-item disabled"><span class="page-link">Previous</span></li>
    <?php endif; ?>
    <li class="page-item disabled"><span class="page-link"><?= $page ?> of <?= $totalPages ?></span></li>
    <?php if($page<$totalPages): ?>
      <li class="page-item"><a class="page-link" href="?page=<?= $page+1 ?>">Next</a></li>
    <?php else: ?>
      <li class="page-item disabled"><span class="page-link">Next</span></li>
    <?php endif; ?>
  </ul>
</nav>
<?php endif; ?>

</div>
</div>

</div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
<script>
// Live auto-fill employee name as user types
let timer;
document.getElementById("empSearch").addEventListener("input", function(){
    clearTimeout(timer);
    let searchVal = this.value.trim();
    if(searchVal.length > 0){
        timer = setTimeout(() => {
            fetch("search_employees.php?q=" + encodeURIComponent(searchVal))
            .then(res => res.json())
            .then(data => {
                if(data.success){
                    document.getElementById("empName").value = data.full_name;
                    document.getElementById("employee_id").value = data.id;
                } else {
                    document.getElementById("empName").value = "";
                    document.getElementById("employee_id").value = "";
                }
            }).catch(err => console.error(err));
        }, 300);
    } else {
        document.getElementById("empName").value = "";
        document.getElementById("employee_id").value = "";
    }
});
</script>
</body>
</html>
