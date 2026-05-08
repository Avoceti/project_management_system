<?php
if (session_status() === PHP_SESSION_NONE) session_start();
if(!isset($_SESSION['user_id'])) exit;

require_once __DIR__ . '/../config/db.php';

$type = $_POST['type'] ?? '';
$id = intval($_POST['id'] ?? 0);

if(!$type || !$id) exit;

try {
    if($type === 'Goal'){
        // Optional: mark as read by setting a "viewed" column
        $stmt = $pdo->prepare("UPDATE goals SET viewed = 1 WHERE id = ?");
        $stmt->execute([$id]);
    } elseif($type === 'Project'){
        $stmt = $pdo->prepare("UPDATE projects SET viewed = 1 WHERE id = ?");
        $stmt->execute([$id]);
    }
    echo 'success';
} catch(PDOException $e){
    http_response_code(500);
    echo $e->getMessage();
}
