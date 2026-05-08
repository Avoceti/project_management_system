<?php
require_once "../config/db.php";

$employee_id = $_GET['employee_id'] ?? 0;

if(!$employee_id) {
    echo json_encode(['salary'=>0,'employee_payments'=>[]]);
    exit;
}

// Get salary
$stmt = $pdo->prepare("SELECT salary FROM employees WHERE id=?");
$stmt->execute([$employee_id]);
$salary = $stmt->fetchColumn() ?? 0;

// Get previous payments
$stmt = $pdo->prepare("
    SELECT p.payment_date, p.amount, m.name AS method_name, p.notes
    FROM employee_payments p
    LEFT JOIN payment_methods m ON p.method_id = m.id
    WHERE p.employee_id=?
    ORDER BY p.payment_date DESC
");
$stmt->execute([$employee_id]);
$payments = $stmt->fetchAll(PDO::FETCH_ASSOC);

echo json_encode(['salary'=>$salary,'payments'=>$payments]);
