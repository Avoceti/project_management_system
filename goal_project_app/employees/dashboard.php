<?php
session_start();
if(!isset($_SESSION['user_id'])){
    header("Location: ../login.php");
    exit;
}

require_once __DIR__ . '/../config/db.php';

try {
    // Total salary obligations for active employees only
    $stmt = $pdo->query("SELECT IFNULL(SUM(salary),0) FROM employees WHERE status = 'active'");
    $totalSalary = (float)$stmt->fetchColumn();

    // Total paid to active employees only
    $stmt = $pdo->query("
        SELECT IFNULL(SUM(p.amount),0) 
        FROM employee_payments p
        INNER JOIN employees e ON p.employee_id = e.id
        WHERE e.status = 'active'
    ");
    $totalPaid = (float)$stmt->fetchColumn();

    // Pending = salary obligations - paid
    $totalPending = $totalSalary - $totalPaid;
    if ($totalPending < 0) $totalPending = 0;

    // Total employees
    $stmt = $pdo->query("SELECT COUNT(*) FROM employees");
    $totalEmployees = (int)$stmt->fetchColumn();

    // Active employees
    $stmt = $pdo->query("SELECT COUNT(*) FROM employees WHERE status = 'active'");
    $activeEmployees = (int)$stmt->fetchColumn();

    // Fired employees
    $stmt = $pdo->query("SELECT COUNT(*) FROM employees WHERE status != 'active'");
    $firedEmployees = (int)$stmt->fetchColumn();

    // Recent employees (last 5 added)
    $stmt = $pdo->query("SELECT * FROM employees ORDER BY created_at DESC LIMIT 5");
    $recentEmployees = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Recent payments (last 5)
    $stmt = $pdo->query("
        SELECT p.*, e.full_name, m.name AS method_name
        FROM employee_payments p
        LEFT JOIN employees e ON p.employee_id = e.id
        LEFT JOIN payment_methods m ON p.method_id = m.id
        ORDER BY p.payment_date DESC
        LIMIT 5
    ");
    $recentPayments = $stmt->fetchAll(PDO::FETCH_ASSOC);

} catch (PDOException $e) {
    $totalSalary = $totalPaid = $totalPending = $totalEmployees = $activeEmployees = $firedEmployees = 0;
    $recentEmployees = $recentPayments = [];
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="utf-8">
<title>Employee Dashboard</title>
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
.card-modern { border-radius:12px; box-shadow:0 6px 18px rgba(16,24,40,0.06); border:1px solid rgba(16,24,40,0.04); }
.footer { text-align:center; color:#6c757d; padding:18px 0; margin-top:28px; border-top:1px solid rgba(16,24,40,0.06); }
@media (max-width:768px){ .sidebar{position:relative;width:100%;min-height:auto;} .content{margin-left:0;} }
</style>
</head>
<body>

<!-- Sidebar -->
<aside class="sidebar d-flex flex-column">
<h4><i class="bi bi-people-fill me-2"></i> Employee Panel</h4>
<nav class="nav flex-column">
<a class="nav-link active" href="dashboard.php"><i class="bi bi-speedometer2 me-2"></i> Dashboard</a>

<a class="nav-link" data-bs-toggle="collapse" href="#employeeMenu" role="button" aria-expanded="false">
<i class="bi bi-person-badge me-2"></i> Employees
</a>
<div class="collapse submenu" id="employeeMenu">
<a class="nav-link" href="add_employee.php"><i class="bi bi-plus-circle me-2"></i> Add Employee</a>
<a class="nav-link" href="manage_employee.php"><i class="bi bi-list-task me-2"></i> Manage Employees</a>
<a class="nav-link" href="employee_attendance.php"><i class="bi bi-calendar-check me-2"></i> Attendance</a>
<a class="nav-link" href="add_attendance.php"><i class="bi bi-plus-circle me-2"></i> Add Attendance</a>
<a class="nav-link" href="fired_employees.php"><i class="bi bi-person-x-fill me-2 text-danger"></i> Fired Employees</a>
</div>

<a class="nav-link" data-bs-toggle="collapse" href="#paymentMenu" role="button" aria-expanded="false">
<i class="bi bi-cash-coin me-2"></i> Payments
</a>
<div class="collapse submenu" id="paymentMenu">
<a class="nav-link" href="add_payments.php"><i class="bi bi-credit-card me-2"></i> Pay Employee</a>
<a class="nav-link" href="payment_methods.php"><i class="bi bi-wallet2 me-2"></i> Payment Methods</a>
<a class="nav-link" href="payments.php"><i class="bi bi-wallet2 me-2"></i> Manage Payments</a>
<a class="nav-link" href="employee_financial_details.php"><i class="bi bi-wallet2 me-2"></i> Manage account details</a>

</div>

<a class="nav-link" href="../index.php"><i class="bi bi-arrow-left me-2"></i> Back to Main</a>
<a class="nav-link text-danger mt-auto" href="../logout.php"><i class="bi bi-box-arrow-right me-2"></i> Logout</a>
</nav>
</aside>

<!-- Main content -->
<main class="content">
<h3 class="mb-4"><i class="bi bi-speedometer2 me-2 text-primary"></i> Employee Dashboard</h3>

<!-- Stats Cards -->
<div class="row g-4 mb-4">
<div class="col-md-3">
<div class="card card-modern p-3">
<h6 class="text-muted"><i class="bi bi-people-fill me-2 text-primary"></i>Total Employees</h6>
<h3><?= $totalEmployees ?></h3>
</div>
</div>
<div class="col-md-3">
<div class="card card-modern p-3">
<h6 class="text-muted"><i class="bi bi-person-check-fill me-2 text-success"></i>Active Employees</h6>
<h3><?= $activeEmployees ?></h3>
</div>
</div>
<div class="col-md-3">
<div class="card card-modern p-3">
<h6 class="text-muted"><i class="bi bi-person-x-fill me-2 text-danger"></i>Fired Employees</h6>
<h3><?= $firedEmployees ?></h3>
</div>
</div>
<div class="col-md-3">
<div class="card card-modern p-3">
<h6 class="text-muted"><i class="bi bi-cash-stack me-2 text-warning"></i>Total Salary (Active)</h6>
<h3>$<?= number_format($totalSalary,2) ?></h3>
</div>
</div>
</div>

<div class="row g-4 mb-4">
<div class="col-md-6">
<div class="card card-modern p-3">
<h6 class="text-muted"><i class="bi bi-wallet2 me-2 text-success"></i>Total Paid (Active)</h6>
<h3>$<?= number_format($totalPaid,2) ?></h3>
</div>
</div>
<div class="col-md-6">
<div class="card card-modern p-3">
<h6 class="text-muted"><i class="bi bi-exclamation-triangle me-2 text-danger"></i>Pending Payment (Active)</h6>
<h3>$<?= number_format($totalPending,2) ?></h3>
</div>
</div>
</div>

<!-- Recent Employees -->
<div class="card card-modern p-3 mb-4">
<h5 class="mb-3"><i class="bi bi-clock-history me-2 text-primary"></i> Recently Added Employees</h5>
<?php if(!empty($recentEmployees)): ?>
<div class="table-responsive">
<table class="table table-hover align-middle">
<thead>
<tr>
<th>Name</th>
<th>District</th>
<th>Phone</th>
<th>Nationality</th>
<th>Joined</th>
<th>Attendance</th>
</tr>
</thead>
<tbody>
<?php foreach($recentEmployees as $emp): ?>
<tr>
<td><?= htmlspecialchars($emp['full_name']) ?></td>
<td><?= htmlspecialchars($emp['district']) ?></td>
<td><?= htmlspecialchars($emp['phone']) ?></td>
<td><?= htmlspecialchars($emp['nationality']) ?></td>
<td><?= htmlspecialchars(date("Y-m-d", strtotime($emp['created_at']))) ?></td>
<td>
<a class="btn btn-sm btn-primary" href="employee_attendance.php?id=<?= $emp['id'] ?>">
<i class="bi bi-calendar-check me-1"></i> View
</a>
</td>
</tr>
<?php endforeach; ?>
</tbody>
</table>
</div>
<?php else: ?>
<p class="text-muted mb-0">No employees added yet.</p>
<?php endif; ?>
</div>

<!-- Recent Payments -->
<div class="card card-modern p-3 mb-4">
<h5 class="mb-3"><i class="bi bi-cash-coin me-2 text-success"></i> Recent Payments</h5>
<?php if(!empty($recentPayments)): ?>
<div class="table-responsive">
<table class="table table-hover align-middle">
<thead>
<tr>
<th>Employee</th>
<th>Amount</th>
<th>Method</th>
<th>Date</th>
</tr>
</thead>
<tbody>
<?php foreach($recentPayments as $pay): ?>
<tr>
<td><?= htmlspecialchars($pay['full_name']) ?></td>
<td>
<?php
// Only show amounts if employee is active
$stmt = $pdo->prepare("SELECT status FROM employees WHERE id = ?");
$stmt->execute([$pay['employee_id']]);
$status = $stmt->fetchColumn();
echo ($status === 'active') ? '$'.number_format($pay['amount'],2) : '-';
?>
</td>
<td><?= htmlspecialchars($pay['method_name'] ?? '-') ?></td>
<td><?= htmlspecialchars(date("Y-m-d", strtotime($pay['payment_date']))) ?></td>
</tr>
<?php endforeach; ?>
</tbody>
</table>
</div>
<?php else: ?>
<p class="text-muted mb-0">No payments recorded yet.</p>
<?php endif; ?>
</div>

<div class="footer">&copy; <?= date('Y') ?> Employee Management System</div>
</main>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
