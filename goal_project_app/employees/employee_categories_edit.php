<?php
if (session_status() === PHP_SESSION_NONE) session_start();
if(!isset($_SESSION['user_id'])){
    header("Location: ../login.php");
    exit;
}
require_once "../config/db.php";

$id = $_GET['id'] ?? 0;
$stmt = $pdo->prepare("SELECT * FROM employee_categories WHERE id = ?");
$stmt->execute([$id]);
$category = $stmt->fetch();

if (!$category) {
    die("Category not found.");
}

$error = $success = "";

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = trim($_POST['name']);

    if ($name === "") {
        $error = "Category name is required.";
    } else {
        $stmt = $pdo->prepare("UPDATE employee_categories SET name=:name, updated_at=NOW() WHERE id=:id");
        $stmt->execute([':name' => $name, ':id' => $id]);
        $success = "Category updated successfully.";
        // Refresh category
        $stmt = $pdo->prepare("SELECT * FROM employee_categories WHERE id = ?");
        $stmt->execute([$id]);
        $category = $stmt->fetch();
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Edit Category</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
  <style>
    body { background:#f8f9fa; }
    .sidebar { width:220px; height:100vh; position:fixed; left:0; top:0; background:#343a40; padding-top:20px; }
    .sidebar a { display:block; color:#ddd; padding:12px; text-decoration:none; }
    .sidebar a:hover { background:#495057; color:#fff; }
    .content { margin-left:230px; padding:20px; }
  </style>
</head>
<body>
<div class="sidebar">
    <a href="add_payments.php">Add Payments</a>
    <a href="manage_employee.php">Manage Employees</a>
    <a href="employee_categories.php" class="bg-secondary">Employee Categories</a>
</div>

<div class="content">
  <h3>Edit Employee Category</h3>

  <?php if($error): ?><div class="alert alert-danger"><?=$error?></div><?php endif; ?>
  <?php if($success): ?><div class="alert alert-success"><?=$success?></div><?php endif; ?>

  <form method="post" class="card p-3" style="max-width:400px;">
    <div class="mb-3">
      <label class="form-label">Category Name</label>
      <input type="text" name="name" value="<?=htmlspecialchars($category['name'])?>" class="form-control" required>
    </div>
    <button class="btn btn-primary">Update</button>
    <a href="employee_categories.php" class="btn btn-secondary">Cancel</a>
  </form>
</div>
</body>
</html>
