<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    header("Location: ../login.php");
    exit;
}

require_once "../config/db.php";

if (isset($_GET['id']) && is_numeric($_GET['id'])) {
    $id = (int)$_GET['id'];

    try {
        // Update status to active
        $stmt = $pdo->prepare("UPDATE employees SET status = 'active', updated_at = NOW() WHERE id = :id");
        $stmt->execute([':id' => $id]);

        $_SESSION['message'] = "Employee restored successfully.";
    } catch (PDOException $e) {
        $_SESSION['error'] = "Error restoring employee: " . $e->getMessage();
    }
}

header("Location: fired_employees.php");
exit;
