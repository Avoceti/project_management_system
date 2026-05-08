<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    header("Location: ../login.php");
    exit;
}
require_once "../config/db.php";

// Pagination setup
$limit = 15;
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
if ($page < 1) $page = 1;
$offset = ($page - 1) * $limit;

// Count active employees
$totalEmployees = (int)$pdo->query("SELECT COUNT(*) FROM employees WHERE status='active'")->fetchColumn();
$totalPages = ceil($totalEmployees / $limit);

// Summary stats
$totalMale = (int)$pdo->query("SELECT COUNT(*) FROM employees WHERE status='active' AND sex='Male'")->fetchColumn();
$totalFemale = (int)$pdo->query("SELECT COUNT(*) FROM employees WHERE status='active' AND sex='Female'")->fetchColumn();
$avgSalary = $pdo->query("SELECT AVG(salary) FROM employees WHERE status='active'")->fetchColumn();
$avgSalary = $avgSalary ? number_format($avgSalary, 2) : "0.00";

// Fetch active employees
$sql = "SELECT e.id, e.employee_id, e.full_name, e.phone, e.nid, e.photo, 
               e.salary, e.district, e.sex, e.status, c.name AS category_name
        FROM employees e
        LEFT JOIN employee_categories c ON e.category_id = c.id
        WHERE e.status='active'
        ORDER BY e.full_name ASC
        LIMIT :limit OFFSET :offset";
$stmt = $pdo->prepare($sql);
$stmt->bindValue(':limit', (int)$limit, PDO::PARAM_INT);
$stmt->bindValue(':offset', (int)$offset, PDO::PARAM_INT);
$stmt->execute();
$employees = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>Active Employees</title>
<meta name="viewport" content="width=device-width,initial-scale=1">
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
<link href="https://cdn.jsdelivr.net/npm/bootstrap-icons/font/bootstrap-icons.css" rel="stylesheet">
<style>
body { background:#f6f7fb; font-family: Inter, system-ui, -apple-system, Segoe UI, Roboto, sans-serif; }
.sidebar { width:230px; min-height:100vh; background:#fff; border-right:1px solid #e5e7eb; padding:1rem; position:fixed; top:0; left:0; }
.sidebar h4 { color:#0d6efd; margin-bottom:1.2rem; font-size:1.2rem; }
.sidebar .nav-link { color:#333; border-radius:6px; padding:.45rem .65rem; font-weight:500; font-size:0.95rem; margin-bottom:4px; }
.sidebar .nav-link.active, .sidebar .nav-link:hover { color:#0d6efd; background:#f1f5f9; }
.content { margin-left:250px; padding:24px; }
.card-modern { border-radius:12px; box-shadow:0 6px 18px rgba(16,24,40,0.06); border:1px solid rgba(16,24,40,0.04); padding:20px; background:#fff; }
.table td, .table th { vertical-align:middle; text-align:center; }
.table-hover tbody tr:hover { background-color: #f1f5f9; }
.employee-photo { width: 50px; height: 50px; object-fit: cover; border-radius: 50%; cursor: pointer; }
.badge-status { font-size:0.85rem; padding:0.45em 0.6em; border-radius:12px; }
.summary-cards .card { border-radius:12px; box-shadow:0 4px 12px rgba(0,0,0,0.05); }
.footer-card { text-align:center; padding:15px; margin-top:20px; border-radius:12px; background:#0d6efd; color:#fff; }
@media (max-width:768px){ .sidebar{position:relative;width:100%;min-height:auto;} .content{margin-left:0;} }
</style>
</head>
<body>

<!-- Sidebar -->
<aside class="sidebar d-flex flex-column">
  <h4><i class="bi bi-people-fill me-2"></i> Employee Panel</h4>
  <nav class="nav flex-column">
    <a class="nav-link active" href="#"><i class="bi bi-person-check-fill me-2"></i> Active Employees</a>
    <a class="nav-link" href="fired_employees.php"><i class="bi bi-person-x-fill me-2"></i> Fired Employees</a>
    <a class="nav-link" href="manage_employee.php"><i class="bi bi-list-task me-2"></i> Manage All</a>
    <a class="nav-link" href="employee_attendance.php"><i class="bi bi-calendar-check me-2"></i> Attendance</a>
    <a class="nav-link" href="../index.php"><i class="bi bi-arrow-left me-2"></i> Back to Main</a>
    <a class="nav-link text-danger mt-auto" href="../logout.php"><i class="bi bi-box-arrow-right me-2"></i> Logout</a>
  </nav>
</aside>

<main class="content">
  <h3 class="mb-4"><i class="bi bi-person-check-fill text-success me-2"></i> Active Employees</h3>

  <!-- Summary Cards -->
  <div class="row summary-cards mb-4">
    <div class="col-md-3">
      <div class="card p-3 text-center">
        <h6>Total Active</h6>
        <h3 class="text-primary"><?= $totalEmployees ?></h3>
      </div>
    </div>
    <div class="col-md-3">
      <div class="card p-3 text-center">
        <h6>Male</h6>
        <h3 class="text-info"><?= $totalMale ?></h3>
      </div>
    </div>
    <div class="col-md-3">
      <div class="card p-3 text-center">
        <h6>Female</h6>
        <h3 class="text-danger"><?= $totalFemale ?></h3>
      </div>
    </div>
    <div class="col-md-3">
      <div class="card p-3 text-center">
        <h6>Average Salary</h6>
        <h3 class="text-success">$<?= $avgSalary ?></h3>
      </div>
    </div>
  </div>

  <!-- Search -->
  <form method="get" class="mb-3">
      <div class="input-group">
          <input type="text" name="search" id="employeeSearch" class="form-control" placeholder="Search employees...">
          <button type="submit" class="btn btn-primary"><i class="bi bi-search"></i></button>
      </div>
  </form>

  <!-- Employees Table -->
  <div class="card-modern table-responsive">
      <table class="table table-bordered table-hover align-middle">
          <thead class="table-light">
              <tr>
                  <th>Photo</th>
                  <th>Employee ID</th>
                  <th>Full Name</th>
                  <th>Phone</th>
                  <th>NID</th>
                  <th>District</th>
                  <th>Category</th>
                  <th>Salary</th>
                  <th>Status</th>
                  <th>Actions</th>
              </tr>
          </thead>
          <tbody id="employeeTable">
          <?php if ($employees): ?>
              <?php foreach ($employees as $emp): ?>
                  <tr>
                      <td>
                          <?php if (!empty($emp['photo']) && file_exists("../" . $emp['photo'])): ?>
                              <a href="../<?= htmlspecialchars($emp['photo']) ?>" target="_blank">
                                  <img src="../<?= htmlspecialchars($emp['photo']) ?>" class="employee-photo" alt="Photo">
                              </a>
                          <?php else: ?>
                              <i class="bi bi-person-circle text-secondary fs-3"></i>
                          <?php endif; ?>
                      </td>
                      <td><?= htmlspecialchars($emp['employee_id']) ?></td>
                      <td><?= htmlspecialchars($emp['full_name']) ?></td>
                      <td><?= htmlspecialchars($emp['phone']) ?></td>
                      <td><?= htmlspecialchars($emp['nid']) ?></td>
                      <td><?= htmlspecialchars($emp['district']) ?></td>
                      <td><?= htmlspecialchars($emp['category_name'] ?? '-') ?></td>
                      <td>$<?= number_format($emp['salary'], 2) ?></td>
                      <td><span class="badge bg-success badge-status">Active</span></td>
                      <td>
                          <a href="view_employee.php?id=<?= $emp['id'] ?>" class="btn btn-sm btn-outline-info"><i class="bi bi-eye"></i></a>
                          <a href="edit_employee.php?id=<?= $emp['id'] ?>" class="btn btn-sm btn-outline-warning"><i class="bi bi-pencil"></i></a>
                          <a href="delete_employee.php?id=<?= $emp['id'] ?>" class="btn btn-sm btn-outline-danger" onclick="return confirm('Fire this employee?')"><i class="bi bi-person-x"></i></a>
                      </td>
                  </tr>
              <?php endforeach; ?>
          <?php else: ?>
              <tr>
                  <td colspan="10" class="text-center">No active employees found.</td>
              </tr>
          <?php endif; ?>
          </tbody>
      </table>
  </div>

  <!-- Pagination -->
  <?php if ($totalPages > 1): ?>
      <nav class="mt-3">
          <ul class="pagination justify-content-center">
              <li class="page-item <?= ($page <= 1) ? 'disabled' : '' ?>">
                  <a class="page-link" href="?page=<?= $page - 1 ?>">Previous</a>
              </li>
              <?php for ($i = 1; $i <= $totalPages; $i++): ?>
                  <li class="page-item <?= ($i == $page) ? 'active' : '' ?>">
                      <a class="page-link" href="?page=<?= $i ?>"><?= $i ?></a>
                  </li>
              <?php endfor; ?>
              <li class="page-item <?= ($page >= $totalPages) ? 'disabled' : '' ?>">
                  <a class="page-link" href="?page=<?= $page + 1 ?>">Next</a>
              </li>
          </ul>
      </nav>
  <?php endif; ?>

 

  <!-- Praise-worthy Footer -->
  <div class="footer-card">
      <h6>Employee Management System</h6>
      <p class="mb-0">Empowering teams, one employee at a time.</p>
      <small>&copy; <?= date("Y") ?> Your Organization</small>
  </div>
</main>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
document.getElementById('employeeSearch').addEventListener('input', function(){
    const term = this.value.toLowerCase();
    document.querySelectorAll('#employeeTable tr').forEach(tr=>{
        tr.style.display = tr.textContent.toLowerCase().includes(term) ? '' : 'none';
    });
});

// Chart.js Spline Chart
const ctx = document.getElementById('employeeChart').getContext('2d');
new Chart(ctx, {
    type: 'line',
    data: {
        labels: ['Total Active', 'Male', 'Female'],
        datasets: [{
            label: 'Employees',
            data: [<?= $totalEmployees ?>, <?= $totalMale ?>, <?= $totalFemale ?>],
            borderColor: '#0d6efd',
            backgroundColor: 'rgba(13,110,253,0.2)',
            tension: 0.4, // Spline effect
            fill: true,
            pointBackgroundColor: '#0d6efd',
            pointRadius: 5
        }]
    },
    options: {
        responsive: true,
        plugins: {
            legend: { display: true, position: 'top' },
        },
        scales: {
            y: { beginAtZero: true, ticks: { stepSize: 1 } }
        }
    }
});
</script>

</body>
</html>
