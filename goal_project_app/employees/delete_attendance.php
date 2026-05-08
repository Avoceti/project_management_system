<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    header("Location: ../login.php");
    exit;
}

require_once __DIR__ . '/../config/db.php';

// Check if ID is passed
if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    die("Invalid attendance ID.");
}

$id = (int)$_GET['id'];

// Delete attendance record
$stmt = $pdo->prepare("DELETE FROM employee_attendance WHERE id = ?");
if ($stmt->execute([$id])) {
    // Redirect back to attendance page after deletion
    header("Location: employee_attendance.php?deleted=1");
    exit;
} else {
    die("Failed to delete the attendance record.");
}
