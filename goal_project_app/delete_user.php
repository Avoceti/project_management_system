<?php
session_start();
if(!isset($_SESSION['user_id'])){
    header("Location: login.php");
    exit;
}

require_once "config/db.php";

if(!isset($_GET['id']) || !is_numeric($_GET['id'])){
    header("Location: manage_users.php");
    exit;
}

$id = (int)$_GET['id'];

// Prevent deleting yourself
if($id === $_SESSION['user_id']){
    $_SESSION['alert'] = ['type' => 'danger', 'message' => "You cannot delete your own account."];
    header("Location: manage_users.php");
    exit;
}

// Prevent deleting if only 2 users remain
$totalUsers = (int)$pdo->query("SELECT COUNT(*) FROM users")->fetchColumn();
if($totalUsers <= 2){
    $_SESSION['alert'] = ['type' => 'danger', 'message' => "Cannot delete user. At least 2 users must remain in the system."];
    header("Location: manage_users.php");
    exit;
}

// Delete user
$stmt = $pdo->prepare("DELETE FROM users WHERE id = ?");
$stmt->execute([$id]);

$_SESSION['alert'] = ['type' => 'success', 'message' => "User deleted successfully."];
header("Location: manage_users.php");
exit;
?>
