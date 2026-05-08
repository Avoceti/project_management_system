<?php
session_start();
if(!isset($_SESSION['user_id'])){
    header("Location: ../login.php");
    exit;
}
require_once "../config/db.php";

$id = $_GET['id'] ?? 0;
$stmt = $pdo->prepare("DELETE FROM projects WHERE id=?");
$stmt->execute([$id]);

header("Location: list_projects.php");
exit;
