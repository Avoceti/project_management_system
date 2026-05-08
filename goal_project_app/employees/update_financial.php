<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    header("Location: ../login.php");
    exit;
}
require_once "../config/db.php";

$id = $_POST['id'] ?? 0;
$bank_name = $_POST['bank_name'] ?? '';
$account_number = $_POST['account_number'] ?? '';
$account_name = $_POST['account_name'] ?? '';
$photoPath = '';

try {
    // Handle photo upload
    if (!empty($_FILES['photo']['name'])) {
        $uploadDir = "../uploads/";
        if (!is_dir($uploadDir)) {
            mkdir($uploadDir, 0777, true);
        }
        $fileName = time() . "_" . basename($_FILES['photo']['name']);
        $targetPath = $uploadDir . $fileName;

        if (move_uploaded_file($_FILES['photo']['tmp_name'], $targetPath)) {
            $photoPath = "uploads/" . $fileName;
        }
    }

    // Update financial record
    if ($photoPath) {
        $stmt = $pdo->prepare("UPDATE employee_financials 
                               SET bank_name=?, account_number=?, account_name=?, photo=? 
                               WHERE id=?");
        $stmt->execute([$bank_name, $account_number, $account_name, $photoPath, $id]);
    } else {
        $stmt = $pdo->prepare("UPDATE employee_financials 
                               SET bank_name=?, account_number=?, account_name=? 
                               WHERE id=?");
        $stmt->execute([$bank_name, $account_number, $account_name, $id]);
    }

    $_SESSION['success'] = "Financial record updated successfully.";
} catch (PDOException $e) {
    $_SESSION['error'] = "Error updating record: " . $e->getMessage();
}

header("Location: employee_financial_details.php");
exit;
