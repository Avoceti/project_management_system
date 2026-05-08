<?php
if (session_status() === PHP_SESSION_NONE) session_start();
require_once __DIR__ . "/../config/db.php";

header('Content-Type: application/json');

$action = $_POST['action'] ?? '';

if($action === 'delete'){
    $id = $_POST['id'] ?? 0;
    if($id){
        $stmt = $pdo->prepare("DELETE FROM goals WHERE id=?");
        $stmt->execute([$id]);
        echo json_encode(['success'=>true]);
        exit;
    }
}

echo json_encode(['success'=>false]);
