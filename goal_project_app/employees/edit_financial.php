<?php
session_start();
if(!isset($_SESSION['user_id'])){
    header("Location: ../login.php");
    exit;
}

require_once "../config/db.php";

$id = $_GET['id'] ?? 0;
if (!$id) {
    header("Location: employee_financial_details.php");
    exit;
}

// Fetch current record
$stmt = $pdo->prepare("SELECT f.*, e.full_name, e.employee_id, e.nid 
                       FROM employee_financials f 
                       LEFT JOIN employees e ON f.employee_id = e.id 
                       WHERE f.id = ?");
$stmt->execute([$id]);
$record = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$record) {
    $_SESSION['error'] = "Financial record not found.";
    header("Location: employee_financial_details.php");
    exit;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>Edit Financial Record</title>
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
.employee-photo { width: 60px; height: 60px; object-fit: cover; border-radius: 50%; cursor: pointer; margin-bottom:10px; }
@media (max-width:768px){ .sidebar{position:relative;width:100%;min-height:auto;} .content{margin-left:0;} }
</style>
</head>
<body>

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
  <h3 class="mb-4"><i class="bi bi-pencil-square me-2 text-warning"></i> Edit Financial Record</h3>

  <div class="card-modern">
    <form action="update_financial.php" method="post" enctype="multipart/form-data">
      <input type="hidden" name="id" value="<?= $record['id'] ?>">
      <input type="hidden" name="employee_id" value="<?= $record['employee_id'] ?>" disabled>

      <div class="mb-3">
        <label class="form-label">Employee</label>
        <input type="text" class="form-control" value="<?= $record['employee_id'] ?> - <?= $record['nid'] ?>" readonly>
        <input type="text" class="form-control mt-1" value="<?= $record['full_name'] ?>" readonly>
        <small class="text-muted">Employee cannot be changed</small>
      </div>

      <div class="mb-3">
        <label for="bank_name" class="form-label">Bank Name</label>
        <input type="text" name="bank_name" id="bank_name" class="form-control" required value="<?= htmlspecialchars($record['bank_name']) ?>">
      </div>
      <div class="mb-3">
        <label for="account_number" class="form-label">Account Number</label>
        <input type="text" name="account_number" id="account_number" class="form-control" required value="<?= htmlspecialchars($record['account_number']) ?>">
      </div>
      <div class="mb-3">
        <label for="account_name" class="form-label">Account Name</label>
        <input type="text" name="account_name" id="account_name" class="form-control" required value="<?= htmlspecialchars($record['account_name']) ?>">
      </div>
      <div class="mb-3">
        <label class="form-label">Current Photo</label><br>
        <?php if(!empty($record['photo']) && file_exists("../".$record['photo'])): ?>
            <img src="../<?= htmlspecialchars($record['photo']) ?>" class="employee-photo" alt="Photo">
        <?php else: ?>
            <i class="bi bi-person-circle fs-3 text-secondary"></i>
        <?php endif; ?>
        <input type="file" name="employee_photo" class="form-control mt-2">
        <small class="text-muted">Leave empty to keep current photo</small>
      </div>

      <button type="submit" class="btn btn-warning"><i class="bi bi-save me-1"></i> Update Record</button>
      <a href="employee_financial_details.php" class="btn btn-secondary">Cancel</a>
    </form>
  </div>
</main>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
