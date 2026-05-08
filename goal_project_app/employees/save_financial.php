<?php
session_start();
if(!isset($_SESSION['user_id'])){
    header("Location: ../login.php");
    exit;
}

require_once "../config/db.php";

// Ensure the uploads folder exists
$uploadDir = '../uploads/';
if (!is_dir($uploadDir)) {
    mkdir($uploadDir, 0755, true);
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $employee_id   = $_POST['employee_id'] ?? '';
    $bank_name     = trim($_POST['bank_name'] ?? '');
    $account_number= trim($_POST['account_number'] ?? '');
    $account_name  = trim($_POST['account_name'] ?? '');
    $photoPath     = null;

    if (empty($employee_id) || empty($bank_name) || empty($account_number) || empty($account_name)) {
        $_SESSION['error'] = "All required fields must be filled.";
        header("Location: employee_financial_details.php");
        exit;
    }

    // Handle photo upload
    if (isset($_FILES['employee_photo']) && $_FILES['employee_photo']['error'] === UPLOAD_ERR_OK) {
        $tmpName = $_FILES['employee_photo']['tmp_name'];
        $fileName = time() . '_' . basename($_FILES['employee_photo']['name']);
        $targetPath = $uploadDir . $fileName;

        if (move_uploaded_file($tmpName, $targetPath)) {
            $photoPath = 'uploads/' . $fileName; // Save relative path
        } else {
            $_SESSION['warning'] = "Photo could not be uploaded, but record will be saved.";
        }
    }

    try {
        // Check for duplicate financial record for same employee
        $checkStmt = $pdo->prepare("SELECT id FROM employee_financials WHERE employee_id = ?");
        $checkStmt->execute([$employee_id]);
        if ($checkStmt->rowCount() > 0) {
            $_SESSION['warning'] = "This employee already has a financial record.";
            header("Location: employee_financial_details.php");
            exit;
        }

        // Insert new financial record
        $stmt = $pdo->prepare("INSERT INTO employee_financials 
            (employee_id, bank_name, account_number, account_name, photo) 
            VALUES (:employee_id, :bank_name, :account_number, :account_name, :photo)");

        $stmt->execute([
            ':employee_id'    => $employee_id,
            ':bank_name'      => $bank_name,
            ':account_number' => $account_number,
            ':account_name'   => $account_name,
            ':photo'          => $photoPath
        ]);

        $_SESSION['success'] = "Financial record saved successfully!";
        header("Location: employee_financial_details.php");
        exit;

    } catch (PDOException $e) {
        $_SESSION['error'] = "Database error: " . $e->getMessage();
        header("Location: employee_financial_details.php");
        exit;
    }
} else {
    header("Location: employee_financial_details.php");
    exit;
}
