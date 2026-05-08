<?php
session_start();
if(!isset($_SESSION['user_id'])){
    http_response_code(403);
    exit('Forbidden');
}

require_once "../config/db.php";

$q = $_GET['q'] ?? '';
$q = trim($q);

header('Content-Type: application/json');

if(strlen($q) < 2){
    echo json_encode([]);
    exit;
}

$stmt = $pdo->prepare("
    SELECT id, employee_id, full_name, nid
    FROM employees
    WHERE full_name LIKE :term OR employee_id LIKE :term OR nid LIKE :term
    LIMIT 10
");
$stmt->execute([':term' => "%$q%"]);
$results = $stmt->fetchAll(PDO::FETCH_ASSOC);

echo json_encode($results);
