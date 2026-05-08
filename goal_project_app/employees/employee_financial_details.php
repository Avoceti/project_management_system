<?php
session_start();
if(!isset($_SESSION['user_id'])){
    header("Location: ../login.php");
    exit;
}

require_once "../config/db.php";

// Pagination
$limit = 12;
$page = isset($_GET['page']) ? max(1,(int)$_GET['page']) : 1;
$offset = ($page-1)*$limit;

// Search filter
$search = $_GET['search'] ?? '';
$searchSql = '';
$params = [];
if (!empty($search)) {
    $searchSql = " AND (e.full_name LIKE :search OR e.employee_id LIKE :search OR e.nid LIKE :search)";
    $params[':search'] = "%$search%";
}

// Count total financial records
$countSql = "SELECT COUNT(*) FROM employee_financials f
             LEFT JOIN employees e ON f.employee_id = e.id
             WHERE 1 $searchSql";
$countStmt = $pdo->prepare($countSql);
$countStmt->execute($params);
$totalRecords = $countStmt->fetchColumn();
$totalPages = $totalRecords > 0 ? ceil($totalRecords/$limit) : 1;

// Fetch financial records
$sql = "SELECT f.*, e.full_name, e.employee_id, e.nid, e.photo AS employee_photo
        FROM employee_financials f
        LEFT JOIN employees e ON f.employee_id = e.id
        WHERE 1 $searchSql
        ORDER BY e.full_name ASC
        LIMIT :limit OFFSET :offset";
$stmt = $pdo->prepare($sql);
foreach ($params as $key => $value) $stmt->bindValue($key,$value);
$stmt->bindValue(':limit',$limit,PDO::PARAM_INT);
$stmt->bindValue(':offset',$offset,PDO::PARAM_INT);
$stmt->execute();
$records = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>Employee Financial Details</title>
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
.actions-btns a { margin-right: 5px; margin-bottom: 2px; }
.list-group-item-action:hover { cursor:pointer; }
.alert { font-size:0.9rem; }
@media (max-width:768px){ .sidebar{position:relative;width:100%;min-height:auto;} .content{margin-left:0;} }
.position-relative{position:relative;}
.list-group { max-height:200px; overflow-y:auto; }
</style>
</head>
<body>

<!-- Sidebar -->
<aside class="sidebar d-flex flex-column">
  <h4><i class="bi bi-people-fill me-2"></i> Employee Panel</h4>
  <nav class="nav flex-column">
    <a class="nav-link" href="dashboard.php"><i class="bi bi-speedometer2 me-2"></i> Dashboard</a>
    <a class="nav-link active" href="employee_financial_details.php"><i class="bi bi-wallet2 me-2"></i> Financial Details</a>
    <a class="nav-link" href="../index.php"><i class="bi bi-arrow-left me-2"></i> Back to Main</a>
    <a class="nav-link text-danger mt-auto" href="../logout.php"><i class="bi bi-box-arrow-right me-2"></i> Logout</a>
  </nav>
</aside>

<main class="content">
  <h3 class="mb-3"><i class="bi bi-wallet2 me-2 text-primary"></i> Employee Financial Details</h3>

  <!-- Alerts -->
  <?php if(!empty($_SESSION['success'])): ?>
      <div class="alert alert-success"><?= $_SESSION['success']; unset($_SESSION['success']); ?></div>
  <?php endif; ?>
  <?php if(!empty($_SESSION['warning'])): ?>
      <div class="alert alert-warning"><?= $_SESSION['warning']; unset($_SESSION['warning']); ?></div>
  <?php endif; ?>
  <?php if(!empty($_SESSION['error'])): ?>
      <div class="alert alert-danger"><?= $_SESSION['error']; unset($_SESSION['error']); ?></div>
  <?php endif; ?>

  <!-- Search Form -->
  <form method="get" class="mb-3">
      <div class="input-group">
          <input type="text" name="search" class="form-control" placeholder="Search by Name, ID, or NID..." value="<?= htmlspecialchars($search) ?>">
          <button class="btn btn-primary" type="submit"><i class="bi bi-search"></i></button>
      </div>
  </form>

  <!-- Add Financial Record Button -->
  <button class="btn btn-primary mb-3" data-bs-toggle="modal" data-bs-target="#addFinancialModal"><i class="bi bi-plus-circle me-1"></i> Add Financial Detail</button>

  <!-- Financial Records Table -->
  <div class="card-modern table-responsive">
      <table class="table table-hover align-middle">
          <thead class="table-light">
              <tr>
                  <th>Photo</th>
                  <th>Employee ID</th>
                  <th>Name</th>
                  <th>Bank Name</th>
                  <th>Account Number</th>
                  <th>Account Name</th>
                  <th>Actions</th>
              </tr>
          </thead>
          <tbody>
              <?php if($records): ?>
                  <?php foreach($records as $r): ?>
                      <tr>
                          <td>
                              <?php if(!empty($r['employee_photo']) && file_exists("../".$r['employee_photo'])): ?>
                                  <img src="../<?= htmlspecialchars($r['employee_photo']) ?>" class="employee-photo" alt="Photo">
                              <?php else: ?>
                                  <i class="bi bi-person-circle fs-3 text-secondary"></i>
                              <?php endif; ?>
                          </td>
                          <td><?= htmlspecialchars($r['employee_id']) ?></td>
                          <td><?= htmlspecialchars($r['full_name']) ?></td>
                          <td><?= htmlspecialchars($r['bank_name']) ?></td>
                          <td><?= htmlspecialchars($r['account_number']) ?></td>
                          <td><?= htmlspecialchars($r['account_name']) ?></td>
                          <td class="actions-btns">
                              <a href="edit_financial.php?id=<?= $r['id'] ?>" class="btn btn-sm btn-warning"><i class="bi bi-pencil"></i></a>
                              <a href="delete_financial.php?id=<?= $r['id'] ?>" class="btn btn-sm btn-danger" onclick="return confirm('Delete this record?')"><i class="bi bi-trash"></i></a>
                          </td>
                      </tr>
                  <?php endforeach; ?>
              <?php else: ?>
                  <tr><td colspan="7" class="text-center">No financial records found.</td></tr>
              <?php endif; ?>
          </tbody>
      </table>

      <!-- Pagination -->
      <nav class="mt-3">
          <ul class="pagination justify-content-center">
              <li class="page-item <?= ($page<=1)?'disabled':'' ?>">
                  <a class="page-link" href="?page=<?= max(1,$page-1) ?>&search=<?= urlencode($search) ?>">Previous</a>
              </li>
              <li class="page-item disabled"><span class="page-link">Page <?= $page ?> of <?= $totalPages ?></span></li>
              <li class="page-item <?= ($page>=$totalPages)?'disabled':'' ?>">
                  <a class="page-link" href="?page=<?= min($totalPages,$page+1) ?>&search=<?= urlencode($search) ?>">Next</a>
              </li>
          </ul>
      </nav>
  </div>
</main>

<!-- Add Financial Modal -->
<div class="modal fade" id="addFinancialModal" tabindex="-1" aria-labelledby="addFinancialLabel" aria-hidden="true">
  <div class="modal-dialog">
    <form action="save_financial.php" method="post" class="modal-content" enctype="multipart/form-data">
      <div class="modal-header">
        <h5 class="modal-title" id="addFinancialLabel"><i class="bi bi-plus-circle me-1"></i> Add Financial Detail</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
      </div>
      <div class="modal-body position-relative">
        <div class="mb-3">
          <label for="employee_search" class="form-label">Employee</label>
          <input type="text" id="employee_search" class="form-control" placeholder="Type ID, Name, or NID..." required autocomplete="off">
          <input type="hidden" name="employee_id" id="employee_id">
          <div id="employee_suggestions" class="list-group position-absolute w-100" style="z-index:1000;"></div>
        </div>
        <div class="mb-3">
          <label for="employee_name" class="form-label">Name</label>
          <input type="text" id="employee_name" class="form-control" readonly>
        </div>
        <div class="mb-3">
          <label for="employee_photo" class="form-label">Photo (optional)</label>
          <input type="file" name="employee_photo" id="employee_photo" class="form-control">
        </div>
        <div class="mb-3">
          <label for="bank_name" class="form-label">Bank Name</label>
          <input type="text" name="bank_name" id="bank_name" class="form-control" required>
        </div>
        <div class="mb-3">
          <label for="account_number" class="form-label">Account Number</label>
          <input type="text" name="account_number" id="account_number" class="form-control" required>
        </div>
        <div class="mb-3">
          <label for="account_name" class="form-label">Account Name</label>
          <input type="text" name="account_name" id="account_name" class="form-control" required>
        </div>
      </div>
      <div class="modal-footer">
        <button type="submit" class="btn btn-primary">Save</button>
      </div>
    </form>
  </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
<script>
// Auto-fill employee name
const searchInput = document.getElementById('employee_search');
const suggestions = document.getElementById('employee_suggestions');
const hiddenInput = document.getElementById('employee_id');
const nameInput = document.getElementById('employee_name');

searchInput.addEventListener('input', function(){
    const term = this.value.trim();
    hiddenInput.value = '';
    nameInput.value = '';
    if(term.length < 2){ suggestions.innerHTML=''; return; }

    fetch(`search_employee_name.php?q=${encodeURIComponent(term)}`)
        .then(res => res.json())
        .then(data => {
            suggestions.innerHTML='';
            data.forEach(emp=>{
                const item=document.createElement('a');
                item.className='list-group-item list-group-item-action';
                item.textContent=`${emp.full_name} (${emp.employee_id} | ${emp.nid})`;
                item.href='#';
                item.addEventListener('click', e=>{
                    e.preventDefault();
                    searchInput.value = `${emp.employee_id} - ${emp.nid}`;
                    hiddenInput.value = emp.id;
                    nameInput.value = emp.full_name;
                    suggestions.innerHTML='';
                });
                suggestions.appendChild(item);
            });
        });
});
</script>

</body>
</html>
