<?php
session_start();
if(!isset($_SESSION['user_id'])){
    header("Location: ../login.php");
    exit;
}

require_once "../config/db.php";

$id = (int)($_GET['id'] ?? 0);
if(!$id){
    header("Location: add_attendance.php");
    exit;
}

// Fetch attendance record
$stmt = $pdo->prepare("SELECT a.*, e.full_name, e.employee_id AS emp_id FROM employee_attendance a LEFT JOIN employees e ON a.employee_id=e.id WHERE a.id=?");
$stmt->execute([$id]);
$attendance = $stmt->fetch(PDO::FETCH_ASSOC);

if(!$attendance){
    header("Location: add_attendance.php");
    exit;
}

// Handle update
$error = '';
if($_SERVER['REQUEST_METHOD']=='POST'){
    $employee_id = (int)($_POST['employee_id'] ?? 0);
    $date = $_POST['date'] ?? date('Y-m-d');
    $status = $_POST['status'] ?? '';
    $check_in = $_POST['check_in'] ?? '';
    $check_out = $_POST['check_out'] ?? '';
    $notes = trim($_POST['notes'] ?? '');

    if($employee_id && $date && $status){
        $stmt = $pdo->prepare("UPDATE employee_attendance SET employee_id=?, date=?, status=?, check_in=?, check_out=?, notes=?, updated_at=NOW() WHERE id=?");
        $stmt->execute([$employee_id,$date,$status,$check_in,$check_out,$notes,$id]);
        header("Location: add_attendance.php?success=1");
        exit;
    } else {
        $error = "Please fill in all required fields.";
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>Edit Attendance</title>
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
<link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css" rel="stylesheet">
<style>
body { font-family: 'Inter', sans-serif; background:#f8f9fa; }
.card-modern { border-radius:12px; box-shadow:0 6px 18px rgba(16,24,40,0.06); border:1px solid rgba(16,24,40,0.04); padding:20px; }
.input-group-text { background-color: #f1f5f9; }
</style>
</head>
<body>
<div class="container py-4">
<h3>Edit Attendance</h3>

<?php if($error): ?>
<div class="alert alert-danger"><?= htmlspecialchars($error) ?></div>
<?php endif; ?>

<div class="card-modern mb-4">
<form method="POST" id="attendanceForm">
<div class="row mb-3">
  <div class="col-md-4">
    <label class="form-label">Employee ID, NID or Name *</label>
    <input type="text" id="employeeSearch" class="form-control" placeholder="Type Employee ID, NID or Name" value="<?= htmlspecialchars($attendance['emp_id']) ?>">
    <input type="hidden" name="employee_id" id="employee_id" value="<?= $attendance['employee_id'] ?>">
  </div>
  <div class="col-md-4">
    <label class="form-label">Full Name</label>
    <input type="text" id="employeeName" class="form-control" readonly value="<?= htmlspecialchars($attendance['full_name']) ?>">
  </div>
  <div class="col-md-4">
    <label class="form-label">Date</label>
    <input type="date" name="date" class="form-control" value="<?= $attendance['date'] ?>" required>
  </div>
</div>

<div class="row mb-3">
  <div class="col-md-6">
    <label class="form-label">Status *</label>
    <select name="status" class="form-select" required>
      <option value="Present" <?= $attendance['status']=='Present'?'selected':'' ?>>Present</option>
      <option value="Absent" <?= $attendance['status']=='Absent'?'selected':'' ?>>Absent</option>
      <option value="Late" <?= $attendance['status']=='Late'?'selected':'' ?>>Late</option>
      <option value="Leave" <?= $attendance['status']=='Leave'?'selected':'' ?>>Leave</option>
    </select>
  </div>
  <div class="col-md-3">
    <label class="form-label">Check In</label>
    <input type="time" name="check_in" class="form-control" value="<?= $attendance['check_in'] ?>">
  </div>
  <div class="col-md-3">
    <label class="form-label">Check Out</label>
    <input type="time" name="check_out" class="form-control" value="<?= $attendance['check_out'] ?>">
  </div>
</div>

<div class="mb-3">
  <label class="form-label">Notes</label>
  <textarea name="notes" class="form-control" rows="3"><?= htmlspecialchars($attendance['notes']) ?></textarea>
</div>

<button type="submit" class="btn btn-primary"><i class="bi bi-save"></i> Update Attendance</button>
<a href="add_attendance.php" class="btn btn-secondary"><i class="bi bi-x-circle"></i> Cancel</a>
</form>
</div>
</div>

<script>
// Live search for employee name
let timer;
document.getElementById('employeeSearch').addEventListener('input', function(){
    clearTimeout(timer);
    const q = this.value.trim();
    if(!q){ document.getElementById('employeeName').value=''; document.getElementById('employee_id').value=''; return; }

    timer = setTimeout(()=>{
        fetch('employee_search_ajax.php?q='+encodeURIComponent(q))
        .then(res=>res.json())
        .then(data=>{
            if(data.success){
                document.getElementById('employeeName').value = data.full_name;
                document.getElementById('employee_id').value = data.id;
            } else {
                document.getElementById('employeeName').value='';
                document.getElementById('employee_id').value='';
            }
        });
    }, 300);
});
</script>
</body>
</html>
