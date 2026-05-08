<?php
session_start();
if(!isset($_SESSION['user_id'])){
    exit('Unauthorized');
}

require_once __DIR__ . '/config/db.php';

// Current user info
$stmt = $pdo->prepare("SELECT * FROM users WHERE id = ?");
$stmt->execute([$_SESSION['user_id']]);
$currentUser = $stmt->fetch(PDO::FETCH_ASSOC);
$currentUserId = $currentUser['id'];
$currentUserRole = $currentUser['role'];

// Search query
$search = $_GET['search'] ?? '';
$search = trim($search);

// Fetch users matching search
$query = "SELECT * FROM users WHERE name LIKE :search OR email LIKE :search OR role LIKE :search ORDER BY created_at DESC";
$stmt = $pdo->prepare($query);
$stmt->execute([':search' => "%$search%"]);
$users = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Generate table rows
foreach($users as $user) {
    $canEdit = false;
    $canDelete = false;

    // Role-based edit permissions
    if($currentUserRole === 'Manager') {
        $canEdit = true; // Manager can edit anyone
        $canDelete = $user['id'] != $currentUserId; // Manager cannot delete themselves
    } elseif($currentUserRole === 'Admin') {
        $canEdit = $user['role'] === 'User';
        $canDelete = $user['role'] === 'User';
    } elseif($currentUserRole === 'User') {
        $canEdit = $user['id'] === $currentUserId;
        $canDelete = false;
    }

    echo "<tr>";
    echo "<td>" . htmlspecialchars($user['name']) . "</td>";
    echo "<td>" . htmlspecialchars($user['email']) . "</td>";
    echo "<td>" . htmlspecialchars($user['role']) . "</td>";
    echo "<td>" . htmlspecialchars($user['created_at']) . "</td>";
    echo "<td>";
    // Edit button
    if($canEdit) {
        echo "<a href='edit_user.php?id={$user['id']}' class='btn btn-sm btn-warning me-1'><i class='bi bi-pencil'></i></a>";
    } else {
        echo "<button class='btn btn-sm btn-warning me-1' disabled><i class='bi bi-pencil'></i></button>";
    }

    // Delete button
    if($canDelete) {
        echo "<a href='delete_user.php?id={$user['id']}' class='btn btn-sm btn-danger' onclick='return confirm(\"Are you sure you want to delete this user?\");'><i class='bi bi-trash'></i></a>";
    } else {
        echo "<button class='btn btn-sm btn-danger' disabled><i class='bi bi-trash'></i></button>";
    }

    echo "</td>";
    echo "</tr>";
}
