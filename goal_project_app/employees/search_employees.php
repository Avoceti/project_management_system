<?php
require_once "../config/db.php";

header('Content-Type: application/json');

$q = $_GET['q'] ?? '';
$q = trim($q);

if ($q === '') {
    echo json_encode(['success' => false]);
    exit;
}

// Search by employee_id, nid, or full_name
$stmt = $pdo->prepare("
    SELECT id, full_name 
    FROM employees 
    WHERE employee_id LIKE ? OR nid LIKE ? OR full_name LIKE ? 
    LIMIT 1
");
$like = "%$q%";
$stmt->execute([$like, $like, $like]);
$employee = $stmt->fetch(PDO::FETCH_ASSOC);

if ($employee) {
    echo json_encode(['success' => true, 'id' => $employee['id'], 'full_name' => $employee['full_name']]);
} else {
    echo json_encode(['success' => false]);
}
