<?php
session_start();
if(!isset($_SESSION['user_id'])){
    header("Location: ../login.php");
    exit;
}

require_once "../config/db.php";

$id = $_GET['id'] ?? 0;
if (!$id) {
    $_SESSION['error'] = "Invalid financial record ID.";
    header("Location: employee_financial_details.php");
    exit;
}

// Fetch current record for photo
$stmt = $pdo->prepare("SELECT photo FROM employee_financials WHERE id=?");
$stmt->execute([$id]);
$record = $stmt->fetch(PDO::FETCH_ASSOC);
$photoPath = $record['photo'] ?? '';

try {
    // Delete the record
    $stmt = $pdo->prepare("DELETE FROM employee_financials WHERE id=?");
    $stmt->execute([$id]);

    // Delete the photo if exists
    if (!empty($photoPath) && file_exists("../".$photoPath)) {
        unlink("../".$photoPath);
    }

    $_SESSION['success'] = "Financial record deleted successfully.";
} catch (PDOException $e) {
    $_SESSION['error'] = "Error deleting record: " . $e->getMessage();
}

header("Location: employee_financial_details.php");
exit;
