<?php
session_start();
if(!isset($_SESSION['user_id'])){
    header("Location: ../login.php");
    exit;
}
require_once "../config/db.php";

// Fetch years for filter dropdown
$years = $pdo->query("SELECT DISTINCT YEAR(payment_date) AS year FROM employee_payments ORDER BY year DESC")->fetchAll(PDO::FETCH_COLUMN);
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>Active Employee Payments</title>
<meta name="viewport" content="width=device-width, initial-scale=1">
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
<link href="https://cdn.jsdelivr.net/npm/bootstrap-icons/font/bootstrap-icons.css" rel="stylesheet">
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<style>
body { font-family: Inter, sans-serif; background:#f6f7fb; }
.sidebar { width:250px; min-height:100vh; background:#fff; border-right:1px solid #e5e7eb; padding:1rem; position:fixed; top:0; left:0; }
.sidebar h4 { color:#0d6efd; margin-bottom:1.5rem; }
.sidebar .nav-link { color:#333; border-radius:6px; padding:.5rem .75rem; font-weight:500; }
.sidebar .nav-link.active, .sidebar .nav-link:hover { color:#0d6efd; background:#f1f5f9; }
.content { margin-left:270px; padding:24px; }
.card-modern { background:#fff; border-radius:12px; box-shadow:0 6px 18px rgba(16,24,40,0.06); border:1px solid rgba(16,24,40,0.04); padding:24px; }
.table td, .table th { vertical-align: middle; }
.search-box { max-width:360px; }
@media(max-width:768px){ .sidebar{position:relative;width:100%;min-height:auto;} .content{margin-left:0;} }
</style>
</head>
<body>

<!-- Sidebar -->
<aside class="sidebar">
  <h4><i class="bi bi-people-fill me-2"></i> Employees</h4>
  <nav class="nav flex-column">
    <a class="nav-link" href="dashboard.php"><i class="bi bi-speedometer2 me-2"></i> Dashboard</a>
    <a class="nav-link" href="manage_employee.php"><i class="bi bi-person-badge me-2"></i> Manage Employees</a>
    <a class="nav-link" href="add_employee.php"><i class="bi bi-person-plus me-2"></i> Add Employee</a>
    <a class="nav-link active" href="#"><i class="bi bi-wallet2 me-2"></i> Payments list</a>
    <a class="nav-link" href="add_payments.php"><i class="bi bi-cash-coin me-2"></i> Add Payment</a>
    <a class="nav-link text-danger mt-auto" href="../logout.php"><i class="bi bi-box-arrow-right me-2"></i> Logout</a>
  </nav>
</aside>

<!-- Main Content -->
<main class="content">
  <div class="card-modern">
    <h3 class="mb-4"><i class="bi bi-wallet2 me-2 text-success"></i> Active Employee Payments</h3>

    <div class="row mb-3">
      <div class="col-md-4">
        <input id="searchInput" class="form-control search-box" placeholder="Search by Name, Employee ID, or NID...">
      </div>
      <div class="col-md-3">
        <select id="filterMonth" class="form-select">
          <option value="">All Months</option>
          <?php for($m=1;$m<=12;$m++): ?>
            <option value="<?= $m ?>"><?= date('F', mktime(0,0,0,$m,1)) ?></option>
          <?php endfor; ?>
        </select>
      </div>
      <div class="col-md-3">
        <select id="filterYear" class="form-select">
          <option value="">All Years</option>
          <?php foreach($years as $y): ?>
            <option value="<?= $y ?>"><?= $y ?></option>
          <?php endforeach; ?>
        </select>
      </div>
    </div>

    <div id="paymentsData"><!-- Data loads here via AJAX --></div>
  </div>
</main>

<script>
function loadPayments(page=1) {
    const search = $("#searchInput").val();
    const month = $("#filterMonth").val();
    const year  = $("#filterYear").val();

    $.post("payments_live_fetch.php", {
        search: search,
        month: month,
        year: year,
        page: page
    }, function(data){
        $("#paymentsData").html(data);
    });
}

$(document).ready(function(){
    loadPayments(); // Initial load

    $("#searchInput, #filterMonth, #filterYear").on("input change", function(){
        loadPayments(1); // reset to first page on new filter
    });

    $(document).on("click", ".prev-page, .next-page", function(e){
        e.preventDefault();
        var page = $(this).data("page");
        loadPayments(page);
    });
});
</script>
</body>
</html>
