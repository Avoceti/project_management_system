<?php
session_start();
if(!isset($_SESSION['user_id'])){
    echo json_encode(['payments'=>[]]);
    exit;
}
require_once "../config/db.php";

$employee_id = (int)($_GET['employee_id'] ?? 0);
if(!$employee_id){ echo json_encode(['payments'=>[]]); exit; }

$stmt = $pdo->prepare("
    SELECT p.*, m.name as method, e.salary 
    FROM employee_payments p
    LEFT JOIN payment_methods m ON p.method_id = m.id
    LEFT JOIN employees e ON p.employee_id = e.id
    WHERE p.employee_id = ?
    ORDER BY p.payment_date DESC
");
$stmt->execute([$employee_id]);
$payments = $stmt->fetchAll(PDO::FETCH_ASSOC);

echo json_encode(['payments'=>$payments]);
