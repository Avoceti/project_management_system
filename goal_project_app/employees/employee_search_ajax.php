<?php
session_start();
if(!isset($_SESSION['user_id'])){
    echo json_encode(['success'=>false]);
    exit;
}
require_once "../config/db.php";

$q = trim($_GET['q'] ?? '');
if(!$q){
    echo json_encode(['success'=>false]);
    exit;
}

$stmt = $pdo->prepare("SELECT id, full_name, salary FROM employees WHERE status='active' AND (employee_id LIKE ? OR nid LIKE ? OR full_name LIKE ?) LIMIT 1");
$like = "%$q%";
$stmt->execute([$like, $like, $like]);
$employee = $stmt->fetch(PDO::FETCH_ASSOC);

if($employee){
    echo json_encode([
        'success'=>true,
        'id'=>$employee['id'],
        'full_name'=>$employee['full_name'],
        'salary'=>$employee['salary']
    ]);
} else {
    echo json_encode(['success'=>false]);
}
