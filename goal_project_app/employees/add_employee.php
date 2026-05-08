<?php
session_start();
if(!isset($_SESSION['user_id'])){
    header("Location: ../login.php");
    exit;
}
require_once "../config/db.php";

$message = '';
$categories = $pdo->query("SELECT id, name FROM employee_categories ORDER BY name ASC")->fetchAll(PDO::FETCH_ASSOC);

// Generate automatic Employee ID
function generateEmployeeID($pdo){
    $lastId = (int)$pdo->query("SELECT id FROM employees ORDER BY id DESC LIMIT 1")->fetchColumn();
    return 'EMP'.str_pad($lastId+1, 4, '0', STR_PAD_LEFT);
}

$autoEmpID = generateEmployeeID($pdo);

if($_SERVER['REQUEST_METHOD'] === 'POST'){
    $full_name      = $_POST['full_name'] ?? '';
    $village        = $_POST['village'] ?? '';
    $ta             = $_POST['ta'] ?? '';
    $gvh            = $_POST['gvh'] ?? '';
    $district       = $_POST['district'] ?? '';
    $tribe          = $_POST['tribe'] ?? '';
    $town           = $_POST['town'] ?? '';
    $dob            = $_POST['dob'] ?? '';
    $nid            = $_POST['nid'] ?? '';
    $phone          = $_POST['phone'] ?? '';
    $nationality    = $_POST['nationality'] ?? '';
    $marital_status = $_POST['marital_status'] ?? '';
    $employee_id    = $_POST['employee_id'] ?? $autoEmpID;
    $sex            = $_POST['sex'] ?? '';
    $salary         = isset($_POST['salary']) ? (float)$_POST['salary'] : 0;
    $category_id    = $_POST['category_id'] ?? null; // NEW field
    $status         = 'active';

    // Photo upload
    $photo = '';
    if(isset($_FILES['photo']) && $_FILES['photo']['error'] === 0){
        $ext = pathinfo($_FILES['photo']['name'], PATHINFO_EXTENSION);
        $photo = 'uploads/'.uniqid().'.'.$ext;
        move_uploaded_file($_FILES['photo']['tmp_name'], '../'.$photo);
    }

    if($full_name && $employee_id){
        $stmt = $pdo->prepare("INSERT INTO employees 
            (full_name, village, ta, gvh, district, tribe, current_town, dob, nid, phone, nationality, marital_status, employee_id, sex, photo, salary, category_id, status, created_at) 
            VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, NOW())");
        $stmt->execute([$full_name, $village, $ta, $gvh, $district, $tribe, $town, $dob, $nid, $phone, $nationality, $marital_status, $employee_id, $sex, $photo, $salary, $category_id, $status]);
        $message = "Employee added successfully!";
        $autoEmpID = generateEmployeeID($pdo); // refresh ID for next employee
    } else {
        $message = "Full Name is required.";
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>Add Employee</title>
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
<link href="https://cdn.jsdelivr.net/npm/bootstrap-icons/font/bootstrap-icons.css" rel="stylesheet">
<style>
body { font-family: 'Inter', sans-serif; background:#f6f7fb; }
.sidebar { width:260px; min-height:100vh; background:#fff; border-right:1px solid #e5e7eb; padding:1.5rem; position:fixed; top:0; left:0; }
.sidebar h4 { color:#0d6efd; margin-bottom:1.5rem; }
.sidebar .nav-link { color:#333; border-radius:6px; padding:.5rem .75rem; font-weight:500; display:flex; align-items:center; gap:8px; }
.sidebar .nav-link.active, .sidebar .nav-link:hover { color:#0d6efd; background:#f1f5f9; }
.sidebar .submenu { padding-left:1.5rem; }
.content { margin-left:280px; padding:24px; }
.card-modern { background:#fff; border-radius:12px; box-shadow:0 6px 18px rgba(16,24,40,0.06); border:1px solid rgba(16,24,40,0.04); padding:20px; }
.form-control { border-radius:8px; padding-left:2.5rem; }
.input-group-text { width:2.5rem; justify-content:center; border-radius:8px; }
textarea.form-control { resize:none; }
.btn-primary { border-radius:8px; }
.alert { border-radius:8px; }
@media (max-width:768px){ .sidebar{position:relative;width:100%;min-height:auto;} .content{margin-left:0;} }
</style>
</head>
<body>
<div class="d-flex">
  <!-- Sidebar -->
  <div class="sidebar">
    <h4><i class="bi bi-people-fill me-2 text-primary"></i> Employee Panel</h4>
    <ul class="nav flex-column">
      <li><a href="../index.php" class="nav-link"><i class="bi bi-speedometer2"></i> Dashboard</a></li>
      <li>
        <a class="nav-link" data-bs-toggle="collapse" href="#employeeMenu">
          <i class="bi bi-people"></i> Employees
        </a>
        <div class="collapse show ps-3" id="employeeMenu">
          <a href="add_employee.php" class="nav-link active"><i class="bi bi-plus-circle"></i> Add Employee</a>
          <a href="manage_employee.php" class="nav-link"><i class="bi bi-list-task"></i> Manage Employees</a>
          <a href="employee_category.php" class="nav-link"><i class="bi bi-tags"></i> Employee Category</a>
          <a href="pay_employee.php" class="nav-link"><i class="bi bi-cash-stack"></i> Pay Employee</a>
        </div>
      </li>
      <li><a href="../logout.php" class="nav-link text-danger"><i class="bi bi-box-arrow-right"></i> Logout</a></li>
    </ul>
  </div>

  <!-- Main content -->
  <div class="content flex-grow-1">
    <div class="card-modern p-4">
      <h3 class="mb-4"><i class="bi bi-plus-circle me-2 text-primary"></i>Add Employee</h3>
      <?php if($message): ?>
        <div class="alert alert-info"><?= htmlspecialchars($message) ?></div>
      <?php endif; ?>
      <form method="POST" enctype="multipart/form-data">
        <div class="row g-3">

          <!-- Full Name -->
          <div class="col-md-6">
            <label class="form-label fw-semibold">Full Name</label>
            <div class="input-group">
              <span class="input-group-text"><i class="bi bi-person-fill"></i></span>
              <input type="text" name="full_name" class="form-control" required>
            </div>
          </div>

          <!-- Employee ID -->
          <div class="col-md-6">
            <label class="form-label fw-semibold">Employee ID</label>
            <div class="input-group">
              <span class="input-group-text"><i class="bi bi-hash"></i></span>
              <input type="text" name="employee_id" class="form-control" value="<?= htmlspecialchars($autoEmpID) ?>" readonly>
            </div>
          </div>

          <!-- Category -->
          <div class="col-md-6">
            <label class="form-label fw-semibold">Category</label>
            <div class="input-group">
              <span class="input-group-text"><i class="bi bi-tags"></i></span>
              <select name="category_id" class="form-control" required>
                <option value="">Select Category</option>
                <?php foreach($categories as $cat): ?>
                  <option value="<?= $cat['id'] ?>"><?= htmlspecialchars($cat['name']) ?></option>
                <?php endforeach; ?>
              </select>
            </div>
          </div>

          <!-- Village -->
          <div class="col-md-6">
            <label class="form-label fw-semibold">Village</label>
            <div class="input-group">
              <span class="input-group-text"><i class="bi bi-house"></i></span>
              <input type="text" name="village" class="form-control">
            </div>
          </div>

          <!-- T/A -->
          <div class="col-md-6">
            <label class="form-label fw-semibold">T/A</label>
            <div class="input-group">
              <span class="input-group-text"><i class="bi bi-geo-alt"></i></span>
              <input type="text" name="ta" class="form-control">
            </div>
          </div>

          <!-- GVH -->
          <div class="col-md-6">
            <label class="form-label fw-semibold">GVH</label>
            <div class="input-group">
              <span class="input-group-text"><i class="bi bi-geo"></i></span>
              <input type="text" name="gvh" class="form-control">
            </div>
          </div>

          <!-- District -->
          <div class="col-md-6">
            <label class="form-label fw-semibold">District</label>
            <div class="input-group">
              <span class="input-group-text"><i class="bi bi-geo-alt-fill"></i></span>
              <input type="text" name="district" class="form-control">
            </div>
          </div>

          <!-- Tribe -->
          <div class="col-md-6">
            <label class="form-label fw-semibold">Tribe</label>
            <div class="input-group">
              <span class="input-group-text"><i class="bi bi-people-fill"></i></span>
              <input type="text" name="tribe" class="form-control">
            </div>
          </div>

          <!-- Current Town -->
          <div class="col-md-6">
            <label class="form-label fw-semibold">Current Town</label>
            <div class="input-group">
              <span class="input-group-text"><i class="bi bi-building"></i></span>
              <input type="text" name="town" class="form-control">
            </div>
          </div>

          <!-- Date of Birth -->
          <div class="col-md-6">
            <label class="form-label fw-semibold">Date of Birth</label>
            <div class="input-group">
              <span class="input-group-text"><i class="bi bi-calendar"></i></span>
              <input type="date" name="dob" class="form-control">
            </div>
          </div>

          <!-- NID -->
          <div class="col-md-6">
            <label class="form-label fw-semibold">NID</label>
            <div class="input-group">
              <span class="input-group-text"><i class="bi bi-credit-card-2-front"></i></span>
              <input type="text" name="nid" class="form-control">
            </div>
          </div>

          <!-- Phone -->
          <div class="col-md-6">
            <label class="form-label fw-semibold">Phone</label>
            <div class="input-group">
              <span class="input-group-text"><i class="bi bi-telephone"></i></span>
              <input type="text" name="phone" class="form-control">
            </div>
          </div>

          <!-- Nationality -->
          <div class="col-md-6">
            <label class="form-label fw-semibold">Nationality</label>
            <div class="input-group">
              <span class="input-group-text"><i class="bi bi-globe"></i></span>
              <input type="text" name="nationality" class="form-control">
            </div>
          </div>

          <!-- Marital Status -->
          <div class="col-md-6">
            <label class="form-label fw-semibold">Marital Status</label>
            <div class="input-group">
              <span class="input-group-text"><i class="bi bi-heart"></i></span>
              <select name="marital_status" class="form-control">
                <option value="">Select</option>
                <option value="Single">Single</option>
                <option value="Married">Married</option>
                <option value="Divorced">Divorced</option>
                <option value="Widowed">Widowed</option>
              </select>
            </div>
          </div>

          <!-- Sex -->
          <div class="col-md-6">
            <label class="form-label fw-semibold">Sex</label>
            <div class="input-group">
              <span class="input-group-text"><i class="bi bi-gender-ambiguous"></i></span>
              <select name="sex" class="form-control">
                <option value="">Select</option>
                <option value="Male">Male</option>
                <option value="Female">Female</option>
              </select>
            </div>
          </div>

          <!-- Salary -->
          <div class="col-md-6">
            <label class="form-label fw-semibold">Salary ($)</label>
            <div class="input-group">
              <span class="input-group-text"><i class="bi bi-cash"></i></span>
              <input type="number" name="salary" class="form-control" min="0" step="0.01">
            </div>
          </div>

          <!-- Photo -->
          <div class="col-md-6">
            <label class="form-label fw-semibold">Photo</label>
            <input type="file" name="photo" class="form-control">
          </div>
        </div>

        <div class="mt-4">
          <button type="submit" class="btn btn-primary"><i class="bi bi-plus-circle me-2"></i>Add Employee</button>
          <a href="manage_employee.php" class="btn btn-secondary ms-2"><i class="bi bi-list-task me-2"></i>Manage Employees</a>
        </div>
      </form>
    </div>
  </div>
</div>
</body>
</html>
