<?php
session_start();
if(!isset($_SESSION['user_id'])){
    header("Location: ../login.php");
    exit;
}
require_once "../config/db.php";

$id = $_GET['id'] ?? 0;
$stmt = $pdo->prepare("SELECT * FROM project_categories WHERE id=?");
$stmt->execute([$id]);
$category = $stmt->fetch();

if(!$category) die("Category not found.");

$message = '';
if($_SERVER['REQUEST_METHOD'] === 'POST'){
    $name = trim($_POST['name']);
    if($name){
        $stmt = $pdo->prepare("UPDATE project_categories SET name=? WHERE id=?");
        $stmt->execute([$name, $id]);
        $message = "Category updated successfully!";
        // Refresh category
        $stmt = $pdo->prepare("SELECT * FROM project_categories WHERE id=?");
        $stmt->execute([$id]);
        $category = $stmt->fetch();
    } else {
        $message = "Category name is required.";
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>Edit Project Category</title>
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
<link href="https://cdn.jsdelivr.net/npm/bootstrap-icons/font/bootstrap-icons.css" rel="stylesheet">
</head>
<body>
<div class="container py-4">
    <h3 class="mb-4"><i class="bi bi-pencil me-2 text-warning"></i>Edit Project Category</h3>
    <?php if($message): ?>
        <div class="alert alert-info"><?= htmlspecialchars($message) ?></div>
    <?php endif; ?>
    <form method="POST">
        <div class="mb-3">
            <label class="form-label">Category Name</label>
            <input type="text" name="name" class="form-control" value="<?= htmlspecialchars($category['name']) ?>" required>
        </div>
        <button class="btn btn-warning"><i class="bi bi-pencil me-2"></i>Update Category</button>
        <a href="manage_categories.php" class="btn btn-secondary"><i class="bi bi-arrow-left me-2"></i>Back</a>
    </form>
</div>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
