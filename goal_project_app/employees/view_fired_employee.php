<?php
session_start();
if(!isset($_SESSION['user_id'])){
    header("Location: ../login.php");
    exit;
}

require_once "../config/db.php";

if(!isset($_GET['id']) || !is_numeric($_GET['id'])){
    die("Invalid employee ID.");
}

$emp_id = (int)$_GET['id'];

// Fetch fired employee details
$stmt = $pdo->prepare("
    SELECT e.*, c.name AS category_name 
    FROM employees e 
    LEFT JOIN employee_categories c ON e.category_id=c.id 
    WHERE e.id=:id AND e.status='fired'
");
$stmt->execute([':id'=>$emp_id]);
$employee = $stmt->fetch(PDO::FETCH_ASSOC);

if(!$employee){
    die("Fired employee not found.");
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>View Fired Employee</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons/font/bootstrap-icons.css">
    <style>
        body { background:#f8f9fa; }
        .sidebar { min-height:100vh; background:#343a40; color:#fff; padding:15px; }
        .sidebar a { color:#fff; display:block; padding:8px; margin-bottom:5px; border-radius:5px; text-decoration:none; }
        .sidebar a:hover { background:#495057; }
        .content { padding:20px; }
        .employee-photo { width:120px; height:120px; object-fit:cover; border-radius:10px; }
        .info-card { background:#fff; border-radius:12px; box-shadow:0 6px 18px rgba(16,24,40,0.06); padding:16px; margin-bottom:16px; }
        .info-row { margin-bottom:10px; }
        .info-row i { width:25px; }
    </style>
</head>
<body>
<div class="container-fluid">
<div class="row">
    <!-- Sidebar -->
    <div class="col-md-3 col-lg-2 sidebar">
        <h4>Menu</h4>
        <a href="../dashboard.php">Dashboard</a>
        <a href="manage_employee.php">Manage Employees</a>
        <a href="fired_employees.php">Fired Employees</a>
        <a href="employee_categories.php">Employee Categories</a>
        <a href="add_payments.php">Add Payments</a>
    </div>

    <!-- Content -->
    <div class="col-md-9 col-lg-10 content">
        <h3 class="mb-3"><i class="bi bi-person-lines-fill me-2"></i> Fired Employee Details</h3>

        <div class="info-card text-center">
            <img src="../uploads/employees/<?= htmlspecialchars($employee['photo']) ?>" class="employee-photo mb-3" alt="photo">
            <h4><?= htmlspecialchars($employee['full_name']) ?></h4>
            <p><span class="badge bg-danger"><?= ucfirst($employee['status']) ?></span></p>
        </div>

        <div class="info-card">
            <h5>Personal Information</h5>
            <div class="info-row"><i class="bi bi-person-badge"></i> Employee ID: <?= htmlspecialchars($employee['employee_id']) ?></div>
            <div class="info-row"><i class="bi bi-calendar-event"></i> DOB: <?= htmlspecialchars($employee['dob']) ?></div>
            <div class="info-row"><i class="bi bi-telephone"></i> Phone: <?= htmlspecialchars($employee['phone']) ?></div>
            <div class="info-row"><i class="bi bi-globe2"></i> Nationality: <?= htmlspecialchars($employee['nationality']) ?></div>
            <div class="info-row"><i class="bi bi-gender-ambiguous"></i> Sex: <?= htmlspecialchars($employee['sex']) ?></div>
            <div class="info-row"><i class="bi bi-people-fill"></i> Marital Status: <?= htmlspecialchars($employee['marital_status']) ?></div>
        </div>

        <div class="info-card">
            <h5>Location & Tribe</h5>
            <div class="info-row"><i class="bi bi-house-door"></i> Village: <?= htmlspecialchars($employee['village']) ?></div>
            <div class="info-row"><i class="bi bi-map"></i> T/A: <?= htmlspecialchars($employee['ta']) ?></div>
            <div class="info-row"><i class="bi bi-map"></i> GVH: <?= htmlspecialchars($employee['gvh']) ?></div>
            <div class="info-row"><i class="bi bi-geo-alt"></i> District: <?= htmlspecialchars($employee['district']) ?></div>
            <div class="info-row"><i class="bi bi-people"></i> Tribe: <?= htmlspecialchars($employee['tribe']) ?></div>
            <div class="info-row"><i class="bi bi-house"></i> Current Town: <?= htmlspecialchars($employee['current_town']) ?></div>
        </div>

        <div class="info-card">
            <h5>Employment Details</h5>
            <div class="info-row"><i class="bi bi-cash-stack"></i> Salary: $<?= number_format($employee['salary'],2) ?></div>
            <div class="info-row"><i class="bi bi-tags-fill"></i> Category: <?= htmlspecialchars($employee['category_name'] ?? '-') ?></div>
            <div class="info-row"><i class="bi bi-calendar-check"></i> Created At: <?= htmlspecialchars($employee['created_at']) ?></div>
            <div class="info-row"><i class="bi bi-calendar-x"></i> Updated At: <?= htmlspecialchars($employee['updated_at']) ?></div>
        </div>

        <a href="fired_employees.php" class="btn btn-secondary mt-3"><i class="bi bi-arrow-left"></i> Back to Fired Employees</a>
    </div>
</div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
