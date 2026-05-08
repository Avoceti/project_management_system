<?php
if (session_status() === PHP_SESSION_NONE) session_start();
if(!isset($_SESSION['user_id'])) exit('Unauthorized');

require_once __DIR__ . "/../config/db.php";

$type = $_POST['type'] ?? '';
$id = intval($_POST['id'] ?? 0);

if(!$type || !$id) exit('Invalid data');

$table = '';
if($type === 'Goal') $table = 'goals';
elseif($type === 'Project') $table = 'projects';
else exit('Invalid type');

// Optional: mark as viewed, or delete the notification
// Assuming notifications are dynamic based on deadline & progress, we'll just send back success
// If you had a notifications table, you would update it like: UPDATE notifications SET viewed=1 WHERE type=? AND item_id=?

echo 'success';
