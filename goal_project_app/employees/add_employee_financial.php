<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    header("Location: ../login.php");
    exit;
}

require_once "../config/db.php";

$id = $_GET['id'] ?? 0;
$financial = null;

// Fetch existing record if editing
if ($id) {
    $stmt = $pdo->prepare("SELECT * FROM employee_financials WHERE id=? LIMIT 1");
    $stmt->execute([$id]);
    $financial = $stmt->fetch(PDO::FETCH_ASSOC);
}

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $employee_id = $_POST['employee_id'] ?? '';
    $bank_name   = $_POST['bank_name'] ?? '';
    $account_number = $_POST['account_number'] ?? '';
    $account_name   = $_POST['account_name'] ?? '';
    $photo = $financial['photo'] ?? '';

    // Handle photo upload
    if (isset($_FILES['photo']) && $_FILES['photo']['error'] === UPLOAD_ERR_OK) {
        $ext = pathinfo($_FILES['photo']['name'], PATHINFO_EXTENSION);
        $filename = 'uploads/financial_photos/' . uniqid() . '.' . $ext;
        if (!is_dir('uploads/financial_photos')) mkdir('uploads/financial_photos', 0755, true);
        move_uploaded_file($_FILES['photo']['tmp_name'], "../$filename");
        $photo = $filename;
    }

    if ($id) {
        // Update
        $stmt = $pdo->prepare("UPDATE employee_financials SET employee_id=?, bank_name=?, account_number=?, account_name=?, photo=? WHERE id=?");
        $stmt->execute([$employee_id, $bank_name, $account_number, $account_name, $photo, $id]);
    } else {
        // Insert
        $stmt = $pdo->prepare("INSERT INTO employee_financials (employee_id, bank_name, account_number, account_name, photo) VALUES (?,?,?,?,?)");
        $stmt->execute([$employee_id, $bank_name, $account_number, $account_name, $photo]);
    }

    header("Location: employee_financial_details.php");
    exit;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title><?= $id ? 'Edit' : 'Add' ?> Employee Financial Record</title>
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
.form-label { font-weight:500; }
.employee-photo-preview { width:80px; height:80px; object-fit:cover; border-radius:50%; margin-bottom:10px; }
@media (max-width:768px){ .sidebar{position:relative;width:100%;min-height:auto;} .content{margin-left:0;} }
</style>
</head>
<body>

<!-- Sidebar -->
<aside class="sidebar d-flex flex-column">
  <h4><i class="bi bi-people-fill me-2"></i> Employee Panel</h4>
  <nav class="nav flex-column">
    <a class="nav-link" href="dashboard.php"><i class="bi bi-speedometer2 me-2"></i> Dashboard</a>
    <a class="nav-link active" href="employee_financial_details.php"><i class="bi bi-bank me-2"></i> Financial Details</a>
    <a class="nav-link" href="manage_employee.php"><i class="bi bi-list-task me-2"></i> Manage Employees</a>
    <a class="nav-link text-danger mt-auto" href="../logout.php"><i class="bi bi-box-arrow-right me-2"></i> Logout</a>
  </nav>
</aside>

<main class="content">
  <h3 class="mb-4"><i class="bi bi-bank me-2 text-primary"></i> <?= $id ? 'Edit' : 'Add' ?> Employee Financial Record</h3>

  <div class="card-modern">
    <form method="post" enctype="multipart/form-data">
      <div class="mb-3">
        <label class="form-label">Search Employee (ID / Name / NID)</label>
        <input type="text" id="employeeSearch" class="form-control" placeholder="Type to search..." value="<?= $financial['full_name'] ?? '' ?>">
        <input type="hidden" name="employee_id" id="employeeId" value="<?= $financial['employee_id'] ?? '' ?>">
      </div>

      <div class="mb-3">
        <label class="form-label">Bank Name</label>
        <input type="text" name="bank_name" class="form-control" value="<?= $financial['bank_name'] ?? '' ?>" required>
      </div>

      <div class="mb-3">
        <label class="form-label">Account Number</label>
        <input type="text" name="account_number" class="form-control" value="<?= $financial['account_number'] ?? '' ?>" required>
      </div>

      <div class="mb-3">
        <label class="form-label">Account Name</label>
        <input type="text" name="account_name" class="form-control" value="<?= $financial['account_name'] ?? '' ?>" required>
      </div>

      <div class="mb-3">
        <label class="form-label">Photo</label><br>
        <?php if (!empty($financial['photo']) && file_exists("../".$financial['photo'])): ?>
            <img src="../<?= htmlspecialchars($financial['photo']) ?>" class="employee-photo-preview" alt="Photo"><br>
        <?php endif; ?>
        <input type="file" name="photo" class="form-control">
      </div>

      <button type="submit" class="btn btn-primary"><?= $id ? 'Update' : 'Save' ?></button>
      <a href="employee_financial_details.php" class="btn btn-secondary">Cancel</a>
    </form>
  </div>
</main>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
<script>
// Auto-fill employee info by searching ID, Name, or NID
const searchInput = document.getElementById('employeeSearch');
const employeeIdInput = document.getElementById('employeeId');

let timeout;
searchInput.addEventListener('input', function(){
    clearTimeout(timeout);
    timeout = setTimeout(() => {
        const term = this.value.trim();
        if (!term) return;

        fetch(`search_employee_name.php?term=${encodeURIComponent(term)}`)
        .then(res => res.json())
        .then(data => {
            if (data.length > 0) {
                const first = data[0];
                searchInput.value = first.full_name;
                employeeIdInput.value = first.id;
            }
        });
    }, 300);
});
</script>
</body>
</html>
