<?php
session_start();
if(!isset($_SESSION['user_id'])){
    header("Location: ../login.php");
    exit;
}

require_once "../config/db.php";

$message = '';
if($_SERVER['REQUEST_METHOD'] === 'POST'){
    $employee_id = (int)($_POST['employee_id'] ?? 0);
    $salary = (float)($_POST['salary'] ?? 0);
    $allowance = (float)($_POST['allowance'] ?? 0);
    $total_payment = $salary + $allowance;
    $payment_date = $_POST['payment_date'] ?? date('Y-m-d');
    $method_id = (int)($_POST['method_id'] ?? 0);
    $notes = trim($_POST['notes'] ?? '');

    if($employee_id && $salary > 0){
        $stmt = $pdo->prepare("INSERT INTO employee_payments (employee_id, amount, allowance, total_payment, payment_date, method_id, notes, created_at) VALUES (?, ?, ?, ?, ?, ?, ?, NOW())");
        $stmt->execute([$employee_id, $salary, $allowance, $total_payment, $payment_date, $method_id, $notes]);
        $message = "Payment recorded successfully! Total: $" . number_format($total_payment,2);
    } else {
        $message = "Please select an employee and enter salary.";
    }
}

/* Fetch payment methods */
$methods = $pdo->query("SELECT id, name FROM payment_methods ORDER BY name ASC")->fetchAll(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>Add Payment</title>
<meta name="viewport" content="width=device-width, initial-scale=1">
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
.table td, .table th { vertical-align: middle; }
.form-control { border-radius:8px; }
.btn { border-radius:8px; }
@media (max-width:768px){ .sidebar{position:relative;width:100%;min-height:auto;} .content{margin-left:0;} }
</style>
</head>
<body>

<!-- Sidebar -->
<aside class="sidebar d-flex flex-column">
  <h4><i class="bi bi-cash-stack me-2"></i> Employee Panel</h4>
  <nav class="nav flex-column">
    <a class="nav-link" href="../index.php"><i class="bi bi-speedometer2 me-2"></i> Dashboard</a>
    <a class="nav-link active" href="add_payments.php"><i class="bi bi-plus-circle me-2"></i> Add Payment</a>
    <a class="nav-link" href="payments.php"><i class="bi bi-list-task me-2"></i> Manage Payments</a>
    <a class="nav-link" href="employees.php"><i class="bi bi-people me-2"></i> Manage Employees</a>
    <a class="nav-link text-danger mt-auto" href="../logout.php"><i class="bi bi-box-arrow-right me-2"></i> Logout</a>
  </nav>
</aside>

<main class="content">
  <h3 class="mb-4"><i class="bi bi-plus-circle me-2 text-primary"></i>Add Employee Payment</h3>

  <?php if($message): ?>
    <div class="alert alert-info"><?= htmlspecialchars($message) ?></div>
  <?php endif; ?>

  <div class="card-modern mb-4">
    <form method="POST" id="paymentForm">
      <div class="row g-3 mb-3">
        <div class="col-md-6">
          <label class="form-label fw-semibold"><i class="bi bi-person-circle me-1"></i> Employee ID, NID or Name <span class="text-danger">*</span></label>
          <input type="text" id="employeeSearch" class="form-control" placeholder="Type Employee ID, NID or Name" required>
          <input type="hidden" name="employee_id" id="employee_id">
        </div>
        <div class="col-md-6">
          <label class="form-label fw-semibold"><i class="bi bi-person-fill me-1"></i> Full Name</label>
          <input type="text" id="employeeName" class="form-control" readonly>
        </div>
      </div>

      <div class="row g-3 mb-3">
        <div class="col-md-6">
          <label class="form-label fw-semibold"><i class="bi bi-currency-dollar me-1"></i> Salary</label>
          <input type="number" name="salary" id="salary" class="form-control" step="0.01" readonly required>
        </div>
        <div class="col-md-6">
          <label class="form-label fw-semibold"><i class="bi bi-plus-square me-1"></i> Allowance</label>
          <input type="number" name="allowance" id="allowance" class="form-control" step="0.01" value="0">
        </div>
      </div>

      <div class="row g-3 mb-3">
        <div class="col-md-6">
          <label class="form-label fw-semibold"><i class="bi bi-calculator me-1"></i> Total Payment</label>
          <input type="number" name="total_payment" id="total_payment" class="form-control" step="0.01" readonly>
        </div>
        <div class="col-md-6">
          <label class="form-label fw-semibold"><i class="bi bi-calendar-check me-1"></i> Payment Date</label>
          <input type="date" name="payment_date" class="form-control" value="<?= date('Y-m-d') ?>" required>
        </div>
      </div>

      <div class="row g-3 mb-3">
        <div class="col-md-6">
          <label class="form-label fw-semibold"><i class="bi bi-credit-card me-1"></i> Payment Method</label>
          <select name="method_id" class="form-select" required>
            <option value="">Select Method</option>
            <?php foreach($methods as $m): ?>
              <option value="<?= $m['id'] ?>"><?= htmlspecialchars($m['name']) ?></option>
            <?php endforeach; ?>
          </select>
        </div>
      </div>

      <div class="mb-3">
        <label class="form-label fw-semibold"><i class="bi bi-card-text me-1"></i> Notes</label>
        <textarea name="notes" class="form-control" rows="3" placeholder="Optional notes"></textarea>
      </div>

      <button type="submit" class="btn btn-primary"><i class="bi bi-plus-circle me-1"></i> Add Payment</button>
    </form>
  </div>

  <!-- Previous Payments Table -->
  <div class="card-modern">
    <h5 class="mb-3"><i class="bi bi-clock-history me-2"></i> Previous Payments</h5>
    <input type="text" id="searchPayments" class="form-control mb-3" placeholder="Search previous payments...">
    <div class="table-responsive">
      <table class="table table-hover align-middle" id="paymentsTable">
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
        <tbody></tbody>
      </table>
    </div>
    <nav>
      <ul class="pagination justify-content-center" id="paymentsPagination"></ul>
    </nav>
  </div>

</main>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
<script>
const employeeSearch = document.getElementById('employeeSearch');
const employeeName = document.getElementById('employeeName');
const employeeIdInput = document.getElementById('employee_id');
const salaryInput = document.getElementById('salary');
const allowanceInput = document.getElementById('allowance');
const totalInput = document.getElementById('total_payment');
const paymentsTableBody = document.querySelector('#paymentsTable tbody');
const paymentsPagination = document.getElementById('paymentsPagination');
const searchInput = document.getElementById('searchPayments');

let timer;
let currentPayments = [];
let currentPage = 1;
const perPage = 12;

function updateTotal(){
    const salary = parseFloat(salaryInput.value) || 0;
    const allowance = parseFloat(allowanceInput.value) || 0;
    totalInput.value = (salary + allowance).toFixed(2);
}

// Live employee search + autofill
employeeSearch.addEventListener('input', function(){
    clearTimeout(timer);
    const q = this.value.trim();
    if(!q){ employeeName.value=''; employeeIdInput.value=''; salaryInput.value=''; return; }

    timer = setTimeout(()=>{
        fetch('employee_search_ajax.php?q='+encodeURIComponent(q))
        .then(res=>res.json())
        .then(data=>{
            if(data.success){
                employeeName.value = data.full_name;
                employeeIdInput.value = data.id;
                salaryInput.value = parseFloat(data.salary || 0).toFixed(2);
                updateTotal();
                fetchPayments(data.id);
            } else {
                employeeName.value=''; employeeIdInput.value=''; salaryInput.value=''; totalInput.value='';
                paymentsTableBody.innerHTML='';
            }
        }).catch(err=>console.error(err));
    }, 300);
});

allowanceInput.addEventListener('input', updateTotal);

function fetchPayments(employeeId){
    if(!employeeId) return paymentsTableBody.innerHTML='';
    fetch('payments_ajax.php?employee_id='+employeeId)
    .then(res=>res.json())
    .then(data=>{
        currentPayments = data.payments || [];
        currentPage = 1;
        renderPayments();
    });
}

function renderPayments(){
    const start = (currentPage-1)*perPage;
    const paginated = currentPayments.slice(start, start+perPage);
    paymentsTableBody.innerHTML = paginated.map(p=>`
        <tr>
            <td>${p.payment_date || '-'}</td>
            <td>$${parseFloat(p.salary||0).toFixed(2)}</td>
            <td>$${parseFloat(p.allowance||0).toFixed(2)}</td>
            <td>$${parseFloat(p.total_payment||0).toFixed(2)}</td>
            <td>${p.method || '-'}</td>
            <td>${p.notes || '-'}</td>
        </tr>
    `).join('');

    const totalPages = Math.ceil(currentPayments.length/perPage);
    paymentsPagination.innerHTML='';
    if(totalPages<=1) return;
    paymentsPagination.innerHTML = `
      <li class="page-item ${currentPage<=1?'disabled':''}"><a class="page-link" href="#" onclick="changePage(${currentPage-1});return false;">Previous</a></li>
      <li class="page-item disabled"><span class="page-link">Page ${currentPage} of ${totalPages}</span></li>
      <li class="page-item ${currentPage>=totalPages?'disabled':''}"><a class="page-link" href="#" onclick="changePage(${currentPage+1});return false;">Next</a></li>
    `;
}

function changePage(page){ currentPage = page; renderPayments(); }

searchInput.addEventListener('input', function(){
    const term = this.value.toLowerCase();
    Array.from(paymentsTableBody.querySelectorAll('tr')).forEach(tr=>{
        tr.style.display = tr.textContent.toLowerCase().includes(term)?'':'none';
    });
});
</script>
</body>
</html>
