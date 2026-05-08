<?php
require_once __DIR__ . "/../config/db.php";
$id = $_GET['id'] ?? 0;
$stmt = $pdo->prepare("SELECT * FROM projects WHERE id=?");
$stmt->execute([$id]);
$project = $stmt->fetch();

if(!$project){ echo "Not found."; exit; }

$progress = $project['target_amount'] > 0 
    ? round(($project['current_amount'] / $project['target_amount']) * 100, 2) 
    : 0;
?>
<h5><?= htmlspecialchars($project['title']) ?></h5>
<p><strong>Target:</strong> $<?= number_format($project['target_amount'],2) ?></p>
<p><strong>Current:</strong> $<?= number_format($project['current_amount'],2) ?></p>
<p><strong>Status:</strong> <?= htmlspecialchars($project['status']) ?></p>
<p><strong>Deadline:</strong> <?= htmlspecialchars($project['deadline']) ?></p>
<div class="progress">
  <div class="progress-bar" style="width:<?= $progress ?>%"></div>
</div>
