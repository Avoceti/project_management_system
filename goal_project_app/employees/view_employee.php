<?php
session_start();
require_once "../config/db.php";

$id = $_GET['id'] ?? 0;
$id = (int)$id;

// Fetch employee details
$stmt = $pdo->prepare("
    SELECT e.*, c.name AS category_name
    FROM employees e
    LEFT JOIN employee_categories c ON e.category_id = c.id
    WHERE e.id = :id
");
$stmt->execute([':id'=>$id]);
$employee = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$employee) {
    die("Employee not found.");
}

// Payments data
$limit = 12;
$page = isset($_GET['page']) ? max(1,(int)$_GET['page']) : 1;
$offset = ($page-1)*$limit;

$stmt = $pdo->prepare("
    SELECT ep.*, pm.name AS method_name
    FROM employee_payments ep
    LEFT JOIN payment_methods pm ON ep.method_id = pm.id
    WHERE ep.employee_id = :id
    ORDER BY ep.payment_date DESC
    LIMIT :limit OFFSET :offset
");
$stmt->bindValue(':id', $id, PDO::PARAM_INT);
$stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
$stmt->bindValue(':offset', $offset, PDO::PARAM_INT);
$stmt->execute();
$payments = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Payments summary cards
$summary = $pdo->prepare("
    SELECT SUM(amount) AS total_salary, SUM(allowance) AS total_allowance
    FROM employee_payments
    WHERE employee_id=:id
");
$summary->execute([':id'=>$id]);
$summaryData = $summary->fetch(PDO::FETCH_ASSOC);
$totalSalary = $summaryData['total_salary'] ?? 0;
$totalAllowance = $summaryData['total_allowance'] ?? 0;
$totalPayment = $totalSalary + $totalAllowance;

// Total pages for pagination
$totalCount = $pdo->prepare("SELECT COUNT(*) FROM employee_payments WHERE employee_id=:id");
$totalCount->execute([':id'=>$id]);
$totalPages = ceil($totalCount->fetchColumn()/$limit);

// Attendance data
$attLimit = 12;
$attPage = isset($_GET['attPage']) ? max(1,(int)$_GET['attPage']) : 1;
$attOffset = ($attPage-1)*$attLimit;

$stmt = $pdo->prepare("
    SELECT * FROM employee_attendance
    WHERE employee_id=:id
    ORDER BY date DESC
    LIMIT :limit OFFSET :offset
");
$stmt->bindValue(':id', $id, PDO::PARAM_INT);
$stmt->bindValue(':limit', $attLimit, PDO::PARAM_INT);
$stmt->bindValue(':offset', $attOffset, PDO::PARAM_INT);
$stmt->execute();
$attendance = $stmt->fetchAll(PDO::FETCH_ASSOC);

$attCount = $pdo->prepare("SELECT COUNT(*) FROM employee_attendance WHERE employee_id=:id");
$attCount->execute([':id'=>$id]);
$attTotalPages = ceil($attCount->fetchColumn()/$attLimit);

?>

<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>Employee Details</title>
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
<link href="https://cdn.jsdelivr.net/npm/bootstrap-icons/font/bootstrap-icons.css" rel="stylesheet">
<style>
body { font-family: 'Inter', sans-serif; background:#f5f6fa; }
.sidebar { width:260px; min-height:100vh; background:#fff; border-right:1px solid #e5e7eb; padding:1.5rem; position:fixed; top:0; left:0; }
.sidebar h4 { color:#0d6efd; margin-bottom:1.5rem; }
.sidebar .nav-link { color:#333; border-radius:6px; padding:.5rem .75rem; display:flex; align-items:center; gap:8px; font-weight:500; }
.sidebar .nav-link.active, .sidebar .nav-link:hover { color:#0d6efd; background:#f1f5f9; }
.content { margin-left:280px; padding:2rem; }
.card-modern { background:#fff; border-radius:15px; box-shadow:0 6px 18px rgba(0,0,0,0.05); padding:25px; margin-bottom:20px; }
.employee-photo { width:160px; height:160px; object-fit:cover; border-radius:12px; cursor:pointer; border:2px solid #0d6efd; }
.info-table td { padding:10px 12px; vertical-align:middle; }
.badge-status { font-size:.9rem; padding:.4em .7em; border-radius:.45rem; }
.badge-active { background-color:#198754;color:#fff; }
.badge-fired { background-color:#dc3545;color:#fff; }
.summary-card { border-radius:12px; padding:15px; text-align:center; color:#fff; font-weight:600; }
.card-salary { background:#0d6efd; }
.card-allowance { background:#198754; }
.card-total { background:#6c757d; }
.table td, .table th { vertical-align:middle; }
@media (max-width:768px){ .sidebar{position:relative;width:100%;min-height:auto;} .content{margin-left:0;} .employee-photo{width:100px;height:100px;} }
</style>
</head>
<body>

<aside class="sidebar d-flex flex-column">
  <h4><i class="bi bi-people-fill me-2"></i> Employee Panel</h4>
  <nav class="nav flex-column">
    <a class="nav-link" href="dashboard.php"><i class="bi bi-speedometer2 me-2"></i> Dashboard</a>
    <a class="nav-link" href="add_employee.php"><i class="bi bi-plus-circle me-2"></i> Add Employee</a>
    <a class="nav-link active" href="manage_employee.php"><i class="bi bi-list-task me-2"></i> Manage Employees</a>
    <a class="nav-link" href="employee_categories.php"><i class="bi bi-tags me-2"></i> Employee Categories</a>
  </nav>
</aside>

<main class="content">
  <div class="d-flex justify-content-between align-items-center mb-4">
    <h3 class="mb-0"><i class="bi bi-person-badge me-2 text-primary"></i> Employee Details</h3>
    <div>
      <a href="edit_employee.php?id=<?= $employee['id'] ?>" class="btn btn-primary me-2"><i class="bi bi-pencil-square me-1"></i>Edit</a>
      <a href="add_payments.php?employee_id=<?= $employee['id'] ?>" class="btn btn-success"><i class="bi bi-cash-stack me-1"></i>Pay</a>
    </div>
  </div>

  <div class="card-modern">
    <div class="row">
      <div class="col-md-3 text-center mb-3">
        <?php if(!empty($employee['photo']) && file_exists("uploads/employees/".$employee['photo'])): ?>
          <img src="uploads/employees/<?= htmlspecialchars($employee['photo']) ?>" class="employee-photo mb-2" onclick="window.open(this.src)">
        <?php else: ?>
          <i class="bi bi-person-circle fs-1 text-secondary"></i>
        <?php endif; ?>
        <form method="POST" enctype="multipart/form-data" action="upload_photo.php">
          <input type="hidden" name="employee_id" value="<?= $employee['id'] ?>">
          <input type="file" name="photo" class="form-control form-control-sm mt-1" onchange="this.form.submit()">
        </form>
      </div>

      <div class="col-md-9">
        <!-- Tabs -->
        <ul class="nav nav-tabs mb-3" id="employeeTabs" role="tablist">
          <li class="nav-item" role="presentation">
            <button class="nav-link active" id="details-tab" data-bs-toggle="tab" data-bs-target="#details" type="button">Details</button>
          </li>
          <li class="nav-item" role="presentation">
            <button class="nav-link" id="payments-tab" data-bs-toggle="tab" data-bs-target="#payments" type="button">Payments</button>
          </li>
          <li class="nav-item" role="presentation">
            <button class="nav-link" id="attendance-tab" data-bs-toggle="tab" data-bs-target="#attendance" type="button">Attendance</button>
          </li>
        </ul>

        <div class="tab-content">
          <!-- Details Tab -->
          <div class="tab-pane fade show active" id="details">
            <table class="table table-borderless info-table">
              <tr><td><i class="bi bi-person-fill me-2"></i>Name</td><td><?= htmlspecialchars($employee['full_name']) ?></td></tr>
              <tr><td><i class="bi bi-house-fill me-2"></i>Village / T/A / GVH</td><td><?= htmlspecialchars($employee['village'] ?? '-') ?> / <?= htmlspecialchars($employee['ta'] ?? '-') ?> / <?= htmlspecialchars($employee['gvh'] ?? '-') ?></td></tr>
              <tr><td><i class="bi bi-geo-alt-fill me-2"></i>District / Tribe</td><td><?= htmlspecialchars($employee['district'] ?? '-') ?> / <?= htmlspecialchars($employee['tribe'] ?? '-') ?></td></tr>
              <tr><td><i class="bi bi-geo-alt me-2"></i>Current Town</td><td><?= htmlspecialchars($employee['current_town'] ?? '-') ?></td></tr>
              <tr><td><i class="bi bi-calendar-fill me-2"></i>DOB</td><td><?= htmlspecialchars($employee['dob'] ?? '-') ?></td></tr>
              <tr><td><i class="bi bi-credit-card-2-front-fill me-2"></i>NID</td><td><?= htmlspecialchars($employee['nid'] ?? '-') ?></td></tr>
              <tr><td><i class="bi bi-telephone-fill me-2"></i>Phone</td><td><?= htmlspecialchars($employee['phone'] ?? '-') ?></td></tr>
              <tr><td><i class="bi bi-globe me-2"></i>Nationality</td><td><?= htmlspecialchars($employee['nationality'] ?? '-') ?></td></tr>
              <tr><td><i class="bi bi-heart-fill me-2"></i>Marital Status</td><td><?= htmlspecialchars($employee['marital_status'] ?? '-') ?></td></tr>
              <tr><td><i class="bi bi-gender-ambiguous me-2"></i>Sex</td><td><?= htmlspecialchars($employee['sex'] ?? '-') ?></td></tr>
              <tr><td><i class="bi bi-cash-stack me-2"></i>Salary</td><td>$<?= number_format($employee['salary'] ?? 0,2) ?></td></tr>
              <tr><td><i class="bi bi-tags-fill me-2"></i>Category</td><td><?= htmlspecialchars($employee['category_name'] ?? '-') ?></td></tr>
              <tr><td><i class="bi bi-person-check-fill me-2"></i>Status</td><td>
                <span class="badge-status <?= strtolower($employee['status'])=='active'?'badge-active':'badge-fired' ?>">
                  <?= ucfirst($employee['status'] ?: 'Fired') ?>
                </span>
              </td></tr>
            </table>
          </div>

          <!-- Payments Tab -->
          <div class="tab-pane fade" id="payments">
            <div class="d-flex gap-3 mb-3 flex-wrap">
              <div class="summary-card card-salary">Salary: $<?= number_format($totalSalary,2) ?></div>
              <div class="summary-card card-allowance">Allowance: $<?= number_format($totalAllowance,2) ?></div>
              <div class="summary-card card-total">Total: $<?= number_format($totalPayment,2) ?></div>
            </div>

            <input id="paymentSearch" class="form-control mb-3" placeholder="Search payments...">
            <div class="table-responsive">
              <table class="table table-hover align-middle">
                <thead>
                  <tr>
                    <th>Date</th>
                    <th>Salary</th>
                    <th>Allowance</th>
                    <th>Total Payment</th>
                    <th>Method</th>
                    <th>Notes</th>
                  </tr>
                </thead>
                <tbody id="paymentTable">
                  <?php foreach($payments as $p):
                    $allowance = $p['allowance'] ?? 0;
                    $totalPay = ($p['amount'] ?? 0)+$allowance;
                  ?>
                  <tr>
                    <td><?= htmlspecialchars($p['payment_date']) ?></td>
                    <td>$<?= number_format($p['amount'] ?? 0,2) ?></td>
                    <td>$<?= number_format($allowance,2) ?></td>
                    <td>$<?= number_format($totalPay,2) ?></td>
                    <td><?= htmlspecialchars($p['method_name'] ?? '-') ?></td>
                    <td><?= htmlspecialchars($p['notes'] ?? '-') ?></td>
                  </tr>
                  <?php endforeach; ?>
                </tbody>
              </table>
            </div>

            <!-- Payments Pagination -->
            <nav class="mt-3">
              <ul class="pagination justify-content-center">
                <li class="page-item <?= ($page<=1)?'disabled':'' ?>">
                  <a class="page-link" href="?id=<?= $id ?>&page=<?= max(1,$page-1) ?>">Previous</a>
                </li>
                <li class="page-item disabled"><span class="page-link">Page <?= $page ?> of <?= $totalPages ?></span></li>
                <li class="page-item <?= ($page>=$totalPages)?'disabled':'' ?>">
                  <a class="page-link" href="?id=<?= $id ?>&page=<?= min($totalPages,$page+1) ?>">Next</a>
                </li>
              </ul>
            </nav>
          </div>

          <!-- Attendance Tab -->
          <div class="tab-pane fade" id="attendance">
            <input id="attendanceSearch" class="form-control mb-3" placeholder="Search attendance...">
            <div class="table-responsive">
              <table class="table table-hover align-middle" id="attendanceTable">
                <thead>
                  <tr>
                    <th>Date</th>
                    <th>Status</th>
                    <th>Check-In</th>
                    <th>Check-Out</th>
                    <th>Notes</th>
                  </tr>
                </thead>
                <tbody>
                  <?php foreach($attendance as $a): ?>
                  <tr>
                    <td><?= htmlspecialchars($a['date']) ?></td>
                    <td><?= htmlspecialchars($a['status']) ?></td>
                    <td><?= htmlspecialchars($a['check_in']) ?></td>
                    <td><?= htmlspecialchars($a['check_out']) ?></td>
                    <td><?= htmlspecialchars($a['notes'] ?? '-') ?></td>
                  </tr>
                  <?php endforeach; ?>
                </tbody>
              </table>
            </div>
            <nav class="mt-3">
              <ul class="pagination justify-content-center">
                <li class="page-item <?= ($attPage<=1)?'disabled':'' ?>">
                  <a class="page-link" href="?id=<?= $id ?>&attPage=<?= max(1,$attPage-1) ?>">Previous</a>
                </li>
                <li class="page-item disabled"><span class="page-link">Page <?= $attPage ?> of <?= $attTotalPages ?></span></li>
                <li class="page-item <?= ($attPage>=$attTotalPages)?'disabled':'' ?>">
                  <a class="page-link" href="?id=<?= $id ?>&attPage=<?= min($attTotalPages,$attPage+1) ?>">Next</a>
                </li>
              </ul>
            </nav>
          </div>

        </div>
      </div>
    </div>
  </div>

</main>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
<script>
document.getElementById('paymentSearch').addEventListener('input', function(){
  const term = this.value.toLowerCase();
  document.querySelectorAll('#paymentTable tr').forEach(tr=>{
    tr.style.display = tr.textContent.toLowerCase().includes(term) ? '' : 'none';
  });
});
document.getElementById('attendanceSearch').addEventListener('input', function(){
  const term = this.value.toLowerCase();
  document.querySelectorAll('#attendanceTable tr').forEach(tr=>{
    tr.style.display = tr.textContent.toLowerCase().includes(term) ? '' : 'none';
  });
});
</script>
</body>
</html>
