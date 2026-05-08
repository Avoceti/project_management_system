<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    header("Location: ../login.php");
    exit;
}
require_once "../config/db.php";

// Get employee ID
$id = $_GET['id'] ?? 0;

// Fetch employee
$stmt = $pdo->prepare("SELECT * FROM employees WHERE id=? LIMIT 1");
$stmt->execute([$id]);
$employee = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$employee) {
    die("Employee not found");
}

// Update employee
if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $full_name     = $_POST['full_name'];
    $village       = $_POST['village'];
    $ta            = $_POST['ta'];
    $gvh           = $_POST['gvh'];
    $district      = $_POST['district'];
    $tribe         = $_POST['tribe'];
    $current_town  = $_POST['current_town'];
    $dob           = $_POST['dob'];
    $nid           = $_POST['nid'];
    $phone         = $_POST['phone'];
    $nationality   = $_POST['nationality'];
    $marital_status= $_POST['marital_status'];
    $sex           = $_POST['sex'];
    $salary        = $_POST['salary'];
    $category_id   = $_POST['category_id'];
    $status        = $_POST['status'];

    $photo = $employee['photo']; // Keep old photo if not updated
    if (!empty($_FILES['photo']['name'])) {
        $uploadDir = "uploads/employees/";
        $fileName  = time() . "_" . basename($_FILES['photo']['name']);
        $target    = $uploadDir . $fileName;

        if (move_uploaded_file($_FILES['photo']['tmp_name'], $target)) {
            $photo = $fileName;
        }
    }

    $sql = "UPDATE employees SET 
                full_name=?, village=?, ta=?, gvh=?, district=?, tribe=?, 
                current_town=?, dob=?, nid=?, phone=?, nationality=?, 
                marital_status=?, sex=?, salary=?, category_id=?, status=?, photo=?, updated_at=NOW() 
            WHERE id=?";
    $stmt = $pdo->prepare($sql);
    $stmt->execute([
        $full_name, $village, $ta, $gvh, $district, $tribe,
        $current_town, $dob, $nid, $phone, $nationality,
        $marital_status, $sex, $salary, $category_id, $status, $photo, $id
    ]);

    header("Location: manage_employee.php?success=updated");
    exit;
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Edit Employee</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
  <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons/font/bootstrap-icons.css" rel="stylesheet">
  <style>
    body { font-family: 'Inter', sans-serif; background:#f6f7fb; }
    .sidebar { width:260px; min-height:100vh; background:#fff; border-right:1px solid #e5e7eb; padding:1.5rem; position:fixed; top:0; left:0; }
    .sidebar h4 { color:#0d6efd; margin-bottom:1.5rem; }
    .sidebar .nav-link { color:#333; border-radius:6px; padding:.5rem .75rem; font-weight:500; display:flex; align-items:center; gap:8px; }
    .sidebar .nav-link.active, .sidebar .nav-link:hover { color:#0d6efd; background:#f1f5f9; }
    .content { margin-left:280px; padding:24px; }
    .card { border-radius: 15px; box-shadow: 0 4px 10px rgba(0,0,0,0.1); }
    .employee-photo { width: 100px; height: 100px; object-fit: cover; border-radius: 50%; }
  </style>
</head>
<body>

<div class="d-flex">
  <!-- Sidebar -->
  <div class="sidebar d-flex flex-column">
    <h4><i class="bi bi-people-fill me-2"></i> Employee Panel</h4>
    <nav class="nav flex-column">
      <a class="nav-link" href="../dashboard.php"><i class="bi bi-speedometer2 me-2"></i> Dashboard</a>
      <a class="nav-link" href="add_employee.php"><i class="bi bi-plus-circle me-2"></i> Add Employee</a>
      <a class="nav-link active" href="manage_employee.php"><i class="bi bi-list-task me-2"></i> Manage Employees</a>
      <a class="nav-link" href="employee_categories.php"><i class="bi bi-tags me-2"></i> Employee Categories</a>
      <a class="nav-link" href="add_payments.php"><i class="bi bi-cash-stack me-2"></i> Pay Employee</a>
      <a class="nav-link" href="payment_methods.php"><i class="bi bi-credit-card-2-front me-2"></i> Payment Methods</a>
    </nav>
  </div>

  <!-- Main content -->
<!-- Main content -->
<div class="content">
  <div class="card p-4">
    <h3 class="mb-3"><i class="bi bi-pencil-square me-2"></i> Edit Employee</h3>
    <form method="POST" enctype="multipart/form-data">
      <div class="row g-3">

        <div class="col-md-6">
          <label class="form-label">Full Name</label>
          <div class="input-group">
            <span class="input-group-text"><i class="bi bi-person-fill"></i></span>
            <input type="text" name="full_name" value="<?= htmlspecialchars($employee['full_name']) ?>" class="form-control" required>
          </div>
        </div>

        <div class="col-md-6">
          <label class="form-label">Phone</label>
          <div class="input-group">
            <span class="input-group-text"><i class="bi bi-telephone"></i></span>
            <input type="text" name="phone" value="<?= htmlspecialchars($employee['phone']) ?>" class="form-control" required>
          </div>
        </div>

        <div class="col-md-6">
          <label class="form-label">NID</label>
          <div class="input-group">
            <span class="input-group-text"><i class="bi bi-credit-card-2-front"></i></span>
            <input type="text" name="nid" value="<?= htmlspecialchars($employee['nid']) ?>" class="form-control">
          </div>
        </div>

        <div class="col-md-6">
          <label class="form-label">District</label>
          <div class="input-group">
            <span class="input-group-text"><i class="bi bi-geo-alt"></i></span>
            <input type="text" name="district" value="<?= htmlspecialchars($employee['district']) ?>" class="form-control">
          </div>
        </div>

        <div class="col-md-6">
          <label class="form-label">Sex</label>
          <div class="input-group">
            <span class="input-group-text"><i class="bi bi-gender-ambiguous"></i></span>
            <select name="sex" class="form-select">
              <option <?= strtolower($employee['sex'])=='male'?'selected':'' ?> value="male">Male</option>
              <option <?= strtolower($employee['sex'])=='female'?'selected':'' ?> value="female">Female</option>
              <option <?= strtolower($employee['sex'])=='other'?'selected':'' ?> value="other">Other</option>
            </select>
          </div>
        </div>

        <div class="col-md-6">
          <label class="form-label">Status</label>
          <div class="input-group">
            <span class="input-group-text"><i class="bi bi-person-check"></i></span>
            <select name="status" class="form-select">
              <option <?= strtolower(trim($employee['status']))=='active'?'selected':'' ?> value="active">Active</option>
              <option <?= strtolower(trim($employee['status']))=='fired'?'selected':'' ?> value="fired">Fired</option>
            </select>
          </div>
        </div>

        <div class="col-md-6">
          <label class="form-label">Village</label>
          <div class="input-group">
            <span class="input-group-text"><i class="bi bi-house"></i></span>
            <input type="text" name="village" value="<?= htmlspecialchars($employee['village']) ?>" class="form-control">
          </div>
        </div>

        <div class="col-md-6">
          <label class="form-label">T/A</label>
          <div class="input-group">
            <span class="input-group-text"><i class="bi bi-geo"></i></span>
            <input type="text" name="ta" value="<?= htmlspecialchars($employee['ta']) ?>" class="form-control">
          </div>
        </div>

        <div class="col-md-6">
          <label class="form-label">GVH</label>
          <div class="input-group">
            <span class="input-group-text"><i class="bi bi-map"></i></span>
            <input type="text" name="gvh" value="<?= htmlspecialchars($employee['gvh']) ?>" class="form-control">
          </div>
        </div>

        <div class="col-md-6">
          <label class="form-label">Current Town</label>
          <div class="input-group">
            <span class="input-group-text"><i class="bi bi-building"></i></span>
            <input type="text" name="current_town" value="<?= htmlspecialchars($employee['current_town']) ?>" class="form-control">
          </div>
        </div>

        <div class="col-md-6">
          <label class="form-label">Date of Birth</label>
          <div class="input-group">
            <span class="input-group-text"><i class="bi bi-calendar-date"></i></span>
            <input type="date" name="dob" value="<?= htmlspecialchars($employee['dob']) ?>" class="form-control">
          </div>
        </div>

        <div class="col-md-6">
          <label class="form-label">Nationality</label>
          <div class="input-group">
            <span class="input-group-text"><i class="bi bi-globe"></i></span>
            <input type="text" name="nationality" value="<?= htmlspecialchars($employee['nationality']) ?>" class="form-control">
          </div>
        </div>

        <div class="col-md-6">
          <label class="form-label">Marital Status</label>
          <div class="input-group">
            <span class="input-group-text"><i class="bi bi-heart"></i></span>
            <select name="marital_status" class="form-select">
              <option <?= $employee['marital_status']=='single'?'selected':'' ?> value="single">Single</option>
              <option <?= $employee['marital_status']=='married'?'selected':'' ?> value="married">Married</option>
              <option <?= $employee['marital_status']=='divorced'?'selected':'' ?> value="divorced">Divorced</option>
              <option <?= $employee['marital_status']=='widowed'?'selected':'' ?> value="widowed">Widowed</option>
            </select>
          </div>
        </div>

        <div class="col-md-6">
          <label class="form-label">Salary</label>
          <div class="input-group">
            <span class="input-group-text"><i class="bi bi-currency-dollar"></i></span>
            <input type="number" step="0.01" name="salary" value="<?= htmlspecialchars($employee['salary']) ?>" class="form-control" required>
          </div>
        </div>

        <div class="col-md-6">
          <label class="form-label">Category</label>
          <div class="input-group">
            <span class="input-group-text"><i class="bi bi-tags"></i></span>
            <select name="category_id" class="form-select">
              <?php
              $cats = $pdo->query("SELECT id,name FROM employee_categories ORDER BY name")->fetchAll(PDO::FETCH_ASSOC);
              foreach ($cats as $cat): ?>
                <option value="<?= $cat['id'] ?>" <?= $employee['category_id']==$cat['id']?'selected':'' ?>>
                  <?= htmlspecialchars($cat['name']) ?>
                </option>
              <?php endforeach; ?>
            </select>
          </div>
        </div>

        <div class="col-md-6">
          <label class="form-label">Photo</label><br>
          <?php if (!empty($employee['photo']) && file_exists("uploads/employees/".$employee['photo'])): ?>
            <img src="uploads/employees/<?= $employee['photo'] ?>" class="employee-photo mb-2"><br>
          <?php endif; ?>
          <input type="file" name="photo" class="form-control">
        </div>

      </div>

      <div class="mt-3">
        <button type="submit" class="btn btn-success"><i class="bi bi-save me-2"></i> Save Changes</button>
        <a href="manage_employee.php" class="btn btn-secondary"><i class="bi bi-arrow-left me-2"></i> Back</a>
      </div>

    </form>
  </div>
</div>

</div>

</body>
</html>
