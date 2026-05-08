<?php
session_start();
require_once "../config/db.php";

$today = date('Y-m-d');
$notifications = $pdo->query("
    SELECT 'Goal' AS type, id, title, deadline 
    FROM goals 
    WHERE deadline <= '$today' AND progress < 100
    UNION ALL
    SELECT 'Project' AS type, id, title, deadline 
    FROM projects 
    WHERE deadline <= '$today' AND progress < 100
")->fetchAll();

echo json_encode($notifications);
