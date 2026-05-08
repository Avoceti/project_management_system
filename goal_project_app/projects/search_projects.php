<?php
session_start();
if(!isset($_SESSION['user_id'])) exit;

require_once "../config/db.php";

$term = $_GET['q'] ?? '';
if(!$term) exit;

$stmt = $pdo->prepare("
    SELECT p.id, p.title, c.name AS category_name 
    FROM projects p 
    LEFT JOIN categories c ON p.category_id = c.id 
    WHERE p.title LIKE ? OR c.name LIKE ?
    ORDER BY p.created_at DESC
    LIMIT 10
");
$stmt->execute(["%$term%", "%$term%"]);
$results = $stmt->fetchAll(PDO::FETCH_ASSOC);

foreach($results as $p){
    echo '<li class="list-group-item list-group-item-action" onclick="selectProject('.$p['id'].', \''.htmlspecialchars($p['title'],ENT_QUOTES).'\')">'
        . htmlspecialchars($p['title']) .' <small class="text-muted">('.($p['category_name'] ?? '-') .')</small></li>';
}
?>
