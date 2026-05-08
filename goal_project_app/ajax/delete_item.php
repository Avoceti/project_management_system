<?php
if (session_status() === PHP_SESSION_NONE) session_start();
if(!isset($_SESSION['user_id'])) exit('Unauthorized');

require_once __DIR__ . "/../config/db.php";

$type = $_POST['type'] ?? '';
$id = intval($_POST['id'] ?? 0);

if(!$type || !$id) exit('Invalid data');

$table = '';
if($type === 'Goal') $table = 'goals';
elseif($type === 'Project') $table = 'projects';
else exit('Invalid type');

$stmt = $pdo->prepare("DELETE FROM $table WHERE id = ?");
$stmt->execute([$id]);

echo 'success';
