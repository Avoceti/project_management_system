<?php
session_start();
if(!isset($_SESSION['user_id'])){
    header("Location: ../login.php");
    exit;
}

require_once "../config/db.php";

$message = '';

// Pagination setup
$limit = 5;
$page = isset($_GET['page']) && is_numeric($_GET['page']) ? max(1,(int)$_GET['page']) : 1;
$offset = ($page - 1) * $limit;

// Handle Add or Edit
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = trim($_POST['method_name']);
    $id = $_POST['id'] ?? null;

    if ($name) {
        if ($id) {
            // Edit existing method
            $stmt = $pdo->prepare("UPDATE payment_methods SET name=? WHERE id=?");
            $stmt->execute([$name, $id]);
            $message = "Payment method updated successfully!";
        } else {
            // Add new method
            $stmt = $pdo->prepare("INSERT INTO payment_methods (name, created_at) VALUES (?, NOW())");
            $stmt->execute([$name]);
            $message = "Payment method added successfully!";
        }
    } else {
        $message = "Method name cannot be empty.";
    }
}

// Handle Delete
if (isset($_GET['delete'])) {
    $id = (int)$_GET['delete'];
    $pdo->prepare("DELETE FROM payment_methods WHERE id=?")->execute([$id]);
    header("Location: payment_methods.php");
    exit;
}

// Fetch total methods for pagination
$totalMethods = (int)$pdo->query("SELECT COUNT(*) FROM payment_methods")->fetchColumn();
$totalPages = $totalMethods > 0 ? ceil($totalMethods / $limit) : 1;
if ($page > $totalPages) $page = $totalPages;

// Fetch paginated methods
$methodsStmt = $pdo->prepare("SELECT * FROM payment_methods ORDER BY created_at DESC LIMIT :limit OFFSET :offset");
$methodsStmt->bindValue(':limit', $limit, PDO::PARAM_INT);
$methodsStmt->bindValue(':offset', $offset, PDO::PARAM_INT);
$methodsStmt->execute();
$methods = $methodsStmt->fetchAll(PDO::FETCH_ASSOC);

// If editing, fetch method data
$editMethod = null;
if (isset($_GET['edit'])) {
    $stmt = $pdo->prepare("SELECT * FROM payment_methods WHERE id=?");
    $stmt->execute([$_GET['edit']]);
    $editMethod = $stmt->fetch(PDO::FETCH_ASSOC);
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>Payment Methods</title>
<meta name="viewport" content="width=device-width, initial-scale=1">
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
<link href="https://cdn.jsdelivr.net/npm/bootstrap-icons/font/bootstrap-icons.css" rel="stylesheet">
<style>
body { font-family:'Inter', sans-serif; background:#f6f7fb; }
.sidebar { width:250px; min-height:100vh; background:#fff; border-right:1px solid #e5e7eb; padding:1rem; position:fixed; top:0; left:0; }
.sidebar h4 { color:#0d6efd; margin-bottom:1.5rem; }
.sidebar .nav-link { color:#333; border-radius:6px; padding:.5rem .75rem; font-weight:500; display:flex; align-items:center; gap:6px; }
.sidebar .nav-link.active, .sidebar .nav-link:hover { color:#0d6efd; background:#f1f5f9; }
.sidebar .submenu { padding-left:1.5rem; }
.content { margin-left:270px; padding:24px; }
.card-modern { background:#fff; border-radius:12px; box-shadow:0 6px 18px rgba(16,24,40,0.06); border:1px solid rgba(16,24,40,0.04); padding:20px; }
.table td, .table th { vertical-align: middle; }
.pagination { justify-content:center; }
@media (max-width:768px){ .sidebar{position:relative;width:100%;min-height:auto;} .content{margin-left:0;} }
</style>
</head>
<body>

<!-- Sidebar -->
<aside class="sidebar d-flex flex-column">
  <h4><i class="bi bi-people me-2"></i> Employees</h4>
  <nav class="nav flex-column">
    <a class="nav-link" href="../index.php"><i class="bi bi-speedometer2 me-2"></i> Dashboard</a>
    <a class="nav-link" href="employees.php"><i class="bi bi-person-lines-fill me-2"></i> Manage Employees</a>
    <a class="nav-link" href="employee_payments.php"><i class="bi bi-cash-stack me-2"></i> Pay Employees</a>
    <a class="nav-link active" href="#"><i class="bi bi-credit-card me-2"></i> Payment Methods</a>
    <a class="nav-link text-danger mt-auto" href="../logout.php"><i class="bi bi-box-arrow-right me-2"></i> Logout</a>
  </nav>
</aside>

<!-- Main content -->
<main class="content">
  <h3 class="mb-4"><i class="bi bi-credit-card me-2 text-primary"></i>Payment Methods</h3>

  <?php if($message): ?>
    <div class="alert alert-info"><?= htmlspecialchars($message) ?></div>
  <?php endif; ?>

  <div class="card-modern mb-4">
    <form method="POST" class="row g-3 align-items-center">
      <div class="col-md-6">
        <input type="text" name="method_name" class="form-control" placeholder="Enter payment method" value="<?= htmlspecialchars($editMethod['name'] ?? '') ?>" required>
        <?php if ($editMethod): ?>
          <input type="hidden" name="id" value="<?= $editMethod['id'] ?>">
        <?php endif; ?>
      </div>
      <div class="col-auto">
        <button type="submit" class="btn btn-primary"><i class="bi bi-plus-circle me-1"></i><?= $editMethod ? 'Update' : 'Add' ?></button>
        <?php if ($editMethod): ?>
          <a href="payment_methods.php" class="btn btn-secondary"><i class="bi bi-x-circle me-1"></i>Cancel</a>
        <?php endif; ?>
      </div>
    </form>
  </div>

  <div class="card-modern table-responsive">
    <div class="mb-3">
      <input id="searchInput" class="form-control" placeholder="Search payment methods...">
    </div>
    <table class="table table-hover align-middle" id="methodTable">
      <thead>
        <tr>
          <th>#</th>
          <th>Method Name</th>
          <th>Created At</th>
          <th>Action</th>
        </tr>
      </thead>
      <tbody>
        <?php foreach($methods as $idx => $m): ?>
        <tr>
          <td><?= $offset + $idx + 1 ?></td>
          <td><?= htmlspecialchars($m['name']) ?></td>
          <td><?= htmlspecialchars($m['created_at']) ?></td>
          <td>
            <a href="?edit=<?= $m['id'] ?>" class="btn btn-sm btn-warning"><i class="bi bi-pencil"></i></a>
            <a href="?delete=<?= $m['id'] ?>" class="btn btn-sm btn-danger" onclick="return confirm('Are you sure?');"><i class="bi bi-trash"></i></a>
          </td>
        </tr>
        <?php endforeach; ?>
      </tbody>
    </table>

    <!-- Pagination -->
    <nav class="mt-3">
      <ul class="pagination">
        <li class="page-item <?= ($page<=1)?'disabled':'' ?>">
          <a class="page-link" href="?page=<?= max(1,$page-1) ?>">Previous</a>
        </li>
        <li class="page-item disabled"><span class="page-link">Page <?= $page ?> of <?= $totalPages ?></span></li>
        <li class="page-item <?= ($page>=$totalPages)?'disabled':'' ?>">
          <a class="page-link" href="?page=<?= min($totalPages,$page+1) ?>">Next</a>
        </li>
      </ul>
    </nav>

  </div>
</main>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
<script>
// Live search
document.getElementById('searchInput').addEventListener('input', function(){
    const term = this.value.toLowerCase();
    document.querySelectorAll('#methodTable tbody tr').forEach(tr => {
        tr.style.display = tr.textContent.toLowerCase().includes(term) ? '' : 'none';
    });
});
</script>
</body>
</html>
