<?php
if(session_status() === PHP_SESSION_NONE) session_start();
require_once __DIR__."/../config/db.php";

$type = $_POST['type'] ?? '';
$id = $_POST['id'] ?? 0;

if($type && $id){
    if($type === 'Goal'){
        $stmt = $pdo->prepare("UPDATE goals SET viewed = 1 WHERE id = ?");
    } else if($type === 'Project'){
        $stmt = $pdo->prepare("UPDATE projects SET viewed = 1 WHERE id = ?");
    }
    $stmt->execute([$id]);
    echo json_encode(['success'=>true]);
} else {
    echo json_encode(['success'=>false]);
}
