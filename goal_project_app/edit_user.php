<?php
session_start();
if(!isset($_SESSION['user_id'])){
    header("Location: login.php");
    exit;
}

require_once __DIR__ . '/config/db.php';

// Current user info
$stmt = $pdo->prepare("SELECT * FROM users WHERE id = ?");
$stmt->execute([$_SESSION['user_id']]);
$currentUser = $stmt->fetch(PDO::FETCH_ASSOC);
$currentUserId = $currentUser['id'];
$currentUserRole = $currentUser['role'];

// Target user to edit
$id = $_GET['id'] ?? 0;
$stmt = $pdo->prepare("SELECT * FROM users WHERE id = ?");
$stmt->execute([$id]);
$editUser = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$editUser) {
    $_SESSION['error'] = "User not found.";
    header("Location: manage_users.php");
    exit;
}

// Role-based restrictions
$canEditRole = false;
if($currentUserRole === 'Manager') {
    $canEditRole = true; // Manager can change any role
} elseif($currentUserRole === 'Admin') {
    $canEditRole = $editUser['role'] === 'User'; // Admin can change role only if user is 'User'
} elseif($currentUserRole === 'User') {
    $canEditRole = $currentUserId === $editUser['id']; // User can edit only self
}

// Only allow User to edit themselves
if($currentUserRole === 'User' && $currentUserId != $editUser['id']) {
    $_SESSION['error'] = "You cannot edit other users.";
    header("Location: manage_users.php");
    exit;
}

// Handle POST update
$message = "";
if($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = trim($_POST['name']);
    $email = trim($_POST['email']);
    $password = $_POST['password'];
    $role = $_POST['role'] ?? $editUser['role']; // default to current role

    if($password) {
        $hashedPassword = password_hash($password, PASSWORD_DEFAULT);
    } else {
        $hashedPassword = $editUser['password']; // keep current password
    }

    // Update database
    $stmt = $pdo->prepare("UPDATE users SET name=?, email=?, password=?, role=? WHERE id=?");
    $stmt->execute([$name, $email, $hashedPassword, $role, $editUser['id']]);
    $message = "User updated successfully!";
    // Refresh user data
    $stmt = $pdo->prepare("SELECT * FROM users WHERE id = ?");
    $stmt->execute([$id]);
    $editUser = $stmt->fetch(PDO::FETCH_ASSOC);
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>Edit User</title>
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
<link href="https://cdn.jsdelivr.net/npm/bootstrap-icons/font/bootstrap-icons.css" rel="stylesheet">
</head>
<body class="bg-light">
<div class="container mt-5">
    <div class="card p-4 shadow">
        <h4 class="mb-3">Edit User: <?= htmlspecialchars($editUser['name']) ?></h4>

        <?php if($message): ?>
            <div class="alert alert-success"><?= htmlspecialchars($message) ?></div>
        <?php endif; ?>

        <form method="post">
            <div class="mb-3">
                <label class="form-label">Full Name</label>
                <input type="text" name="name" class="form-control" required value="<?= htmlspecialchars($editUser['name']) ?>">
            </div>
            <div class="mb-3">
                <label class="form-label">Email</label>
                <input type="email" name="email" class="form-control" required value="<?= htmlspecialchars($editUser['email']) ?>">
            </div>
            <div class="mb-3">
                <label class="form-label">Password (leave blank to keep current)</label>
                <input type="password" name="password" class="form-control">
            </div>
            <?php if($canEditRole): ?>
            <div class="mb-3">
                <label class="form-label">Role</label>
                <select name="role" class="form-select" required>
                    <option value="Manager" <?= $editUser['role']=='Manager'?'selected':'' ?>>Manager</option>
                    <option value="Admin" <?= $editUser['role']=='Admin'?'selected':'' ?>>Admin</option>
                    <option value="User" <?= $editUser['role']=='User'?'selected':'' ?>>User</option>
                </select>
            </div>
            <?php else: ?>
                <input type="hidden" name="role" value="<?= $editUser['role'] ?>">
            <?php endif; ?>

            <button type="submit" class="btn btn-primary"><i class="bi bi-save me-1"></i> Update</button>
            <a href="manage_users.php" class="btn btn-secondary">Cancel</a>
        </form>
    </div>
</div>
</body>
</html>
