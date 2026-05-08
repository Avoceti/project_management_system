<?php
if (session_status() === PHP_SESSION_NONE) session_start();
if(!isset($_SESSION['user_id'])){
    header("Location: ../login.php");
    exit;
}
require_once "../config/db.php";

$id = $_GET['id'] ?? 0;

$stmt = $pdo->prepare("DELETE FROM employee_categories WHERE id = ?");
$stmt->execute([$id]);

header("Location: employee_categories.php?msg=deleted");
exit;
