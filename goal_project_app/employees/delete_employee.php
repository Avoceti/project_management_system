<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    header("Location: ../login.php");
    exit;
}

require_once __DIR__ . '/../config/db.php';

// Check if employee ID is passed
if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    die("Invalid employee ID.");
}

$employee_id = (int)$_GET['id'];

// Optional: Check if employee exists
$stmt = $pdo->prepare("SELECT * FROM employees WHERE id = ?");
$stmt->execute([$employee_id]);
$employee = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$employee) {
    die("Employee not found.");
}

// Delete employee record
$stmt = $pdo->prepare("DELETE FROM employees WHERE id = ?");
if ($stmt->execute([$employee_id])) {
    // Optionally delete related records, e.g., attendance or payments
    $pdo->prepare("DELETE FROM employee_attendance WHERE employee_id = ?")->execute([$employee_id]);
    $pdo->prepare("DELETE FROM employee_payments WHERE employee_id = ?")->execute([$employee_id]);

    // Redirect back to employee list with success message
    header("Location: manage_employee.php?deleted=1");
    exit;
} else {
    die("Failed to delete the employee.");
}
