<?php
require_once __DIR__ . "/../config/db.php";
$id = $_GET['id'] ?? 0;

if($_SERVER['REQUEST_METHOD'] === 'POST'){
  $stmt = $pdo->prepare("UPDATE projects SET title=?, target_amount=?, current_amount=?, status=?, deadline=? WHERE id=?");
  $stmt->execute([
    $_POST['title'], $_POST['target_amount'], $_POST['current_amount'], 
    $_POST['status'], $_POST['deadline'], $id
  ]);
  echo "<div class='alert alert-success'>Project updated successfully.</div>";
  exit;
}

$stmt = $pdo->prepare("SELECT * FROM projects WHERE id=?");
$stmt->execute([$id]);
$project = $stmt->fetch();
?>
<form method="post">
  <div class="mb-3"><label>Title</label>
    <input type="text" name="title" value="<?= htmlspecialchars($project['title']) ?>" class="form-control">
  </div>
  <div class="mb-3"><label>Target</label>
    <input type="number" step="0.01" name="target_amount" value="<?= $project['target_amount'] ?>" class="form-control">
  </div>
  <div class="mb-3"><label>Current</label>
    <input type="number" step="0.01" name="current_amount" value="<?= $project['current_amount'] ?>" class="form-control">
  </div>
  <div class="mb-3"><label>Status</label>
    <select name="status" class="form-select">
      <option <?= $project['status']=='Pending'?'selected':'' ?>>Pending</option>
      <option <?= $project['status']=='In Progress'?'selected':'' ?>>In Progress</option>
      <option <?= $project['status']=='Completed'?'selected':'' ?>>Completed</option>
    </select>
  </div>
  <div class="mb-3"><label>Deadline</label>
    <input type="date" name="deadline" value="<?= $project['deadline'] ?>" class="form-control">
  </div>
  <button class="btn btn-success">Save</button>
</form>
