<?php
require_once "../config/db.php";
$id = $_GET['id'] ?? 0;
$stmt = $pdo->prepare("UPDATE employees SET status='fired', updated_at=NOW() WHERE id=?");
$stmt->execute([$id]);
header("Location: manage_employee.php");
exit;
