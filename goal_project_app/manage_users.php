<?php
session_start();
if(!isset($_SESSION['user_id'])){
    header("Location: login.php");
    exit;
}

require_once __DIR__ . '/config/db.php';

// Current logged-in user
$stmt = $pdo->prepare("SELECT * FROM users WHERE id=?");
$stmt->execute([$_SESSION['user_id']]);
$currentUser = $stmt->fetch(PDO::FETCH_ASSOC);
$currentUserId = $currentUser['id'];
$currentUserRole = $currentUser['role'];

// Fetch all users
$stmt = $pdo->query("SELECT * FROM users ORDER BY created_at DESC");
$users = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>Manage Users</title>
<meta name="viewport" content="width=device-width, initial-scale=1">
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
<link href="https://cdn.jsdelivr.net/npm/bootstrap-icons/font/bootstrap-icons.css" rel="stylesheet">
<style>
body { font-family: 'Inter', sans-serif; background:#f6f7fb; }
.sidebar { width:250px; min-height:100vh; background:#fff; border-right:1px solid #e5e7eb; padding:1rem; position:fixed; top:0; left:0; }
.sidebar h4 { color:#0d6efd; margin-bottom:1.5rem; }
.sidebar .nav-link { color:#333; border-radius:6px; padding:.5rem .75rem; font-weight:500; display:flex; align-items:center; gap:8px; }
.sidebar .nav-link.active, .sidebar .nav-link:hover { color:#0d6efd; background:#f1f5f9; }
.sidebar .submenu { padding-left:1.5rem; }
.content { margin-left:270px; padding:24px; }
.card-modern { background:#fff; border-radius:12px; box-shadow:0 6px 18px rgba(16,24,40,0.06); border:1px solid rgba(16,24,40,0.04); padding:20px; }
.table td, .table th { vertical-align: middle; }
.footer { text-align:center; color:#6c757d; padding:18px 0; margin-top:28px; border-top:1px solid rgba(16,24,40,0.06); }
@media (max-width:768px){ .sidebar{position:relative;width:100%;min-height:auto;} .content{margin-left:0;} }
</style>
</head>
<body>

<!-- Sidebar -->
<aside class="sidebar d-flex flex-column">
  <h4><i class="bi bi-people me-2"></i> Admin Panel</h4>
  <nav class="nav flex-column">
    <a class="nav-link" href="index.php"><i class="bi bi-speedometer2 me-2"></i> Dashboard</a>
    <a class="nav-link active" data-bs-toggle="collapse" href="#userMenu" role="button" aria-expanded="true">
      <i class="bi bi-people me-2"></i> Users
    </a>
    <div class="collapse show submenu" id="userMenu">
      <a href="add_users.php" class="nav-link"><i class="bi bi-plus-circle me-2"></i> Add User</a>
      <a href="manage_users.php" class="nav-link active"><i class="bi bi-list-task me-2"></i> Manage Users</a>
    </div>
    <a class="nav-link text-danger mt-auto" href="logout.php"><i class="bi bi-box-arrow-right me-2"></i> Logout</a>
  </nav>
</aside>

<!-- Main content -->
<main class="content">
  <h3 class="mb-4"><i class="bi bi-people me-2 text-primary"></i> Manage Users</h3>

  <div class="card-modern table-responsive">
    <table class="table table-hover align-middle">
      <thead>
        <tr>
          <th>Name</th>
          <th>Email</th>
          <th>Role</th>
          <th>Created At</th>
          <th>Action</th>
        </tr>
      </thead>
      <tbody>
        <?php foreach($users as $user): ?>
        <tr>
          <td><?= htmlspecialchars($user['name']) ?></td>
          <td><?= htmlspecialchars($user['email']) ?></td>
          <td><?= htmlspecialchars($user['role']) ?></td>
          <td><?= htmlspecialchars($user['created_at']) ?></td>
          <td>
            <!-- Edit Button -->
            <?php
            $canEdit = false;
            if($currentUserRole === 'Manager') $canEdit = true; // Manager edits all
            elseif($currentUserRole === 'Admin' && $user['role']==='User') $canEdit = true; // Admin edits Users
            elseif($currentUserId === $user['id']) $canEdit = true; // User edits self
            ?>
            <?php if($canEdit): ?>
              <a href="edit_user.php?id=<?= $user['id'] ?>" class="btn btn-sm btn-warning">
                <i class="bi bi-pencil"></i>
              </a>
            <?php else: ?>
              <button class="btn btn-sm btn-warning" disabled title="Cannot edit this user">
                <i class="bi bi-pencil"></i>
              </button>
            <?php endif; ?>

            <!-- Delete Button -->
            <?php
            $canDelete = false;
            if($currentUserRole==='Manager') $canDelete = true; // Manager deletes all
            elseif($currentUserRole==='Admin' && $user['role']==='User') $canDelete = true; // Admin deletes Users
            ?>
            <?php if($canDelete && $user['id']!=$currentUserId): ?>
              <a href="delete_user.php?id=<?= $user['id'] ?>" class="btn btn-sm btn-danger" onclick="return confirm('Are you sure?');">
                <i class="bi bi-trash"></i>
              </a>
            <?php else: ?>
              <button class="btn btn-sm btn-danger" disabled title="Cannot delete this user">
                <i class="bi bi-trash"></i>
              </button>
            <?php endif; ?>
          </td>
        </tr>
        <?php endforeach; ?>
      </tbody>
    </table>
  </div>

  <div class="footer">&copy; <?= date('Y') ?> Goal & Project Management System</div>
</main>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
