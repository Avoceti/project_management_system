<?php
session_start();
if(!isset($_SESSION['user_id'])){
    header("Location: login.php");
    exit;
}
require_once "config/db.php";

$message = "";

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = trim($_POST['email']);
    $oldPassword = $_POST['old_password'];
    $newPassword = $_POST['new_password'];
    $confirmPassword = $_POST['confirm_password'];

    if ($newPassword !== $confirmPassword) {
        $message = "❌ New password and confirmation do not match.";
    } else {
        // Fetch user by email
        $stmt = $pdo->prepare("SELECT * FROM users WHERE email = :email LIMIT 1");
        $stmt->execute(['email' => $email]);
        $user = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($user) {
            // Verify old password
            if (password_verify($oldPassword, $user['password'])) {
                // Hash new password
                $hashedPassword = password_hash($newPassword, PASSWORD_DEFAULT);

                // Update in DB
                $update = $pdo->prepare("UPDATE users SET password = :password WHERE id = :id");
                $update->execute(['password' => $hashedPassword, 'id' => $user['id']]);

                $message = "✅ Password updated successfully!";
            } else {
                $message = "❌ Old password is incorrect.";
            }
        } else {
            $message = "❌ No account found with that email.";
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>Change Password</title>
<meta name="viewport" content="width=device-width, initial-scale=1">
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
<link href="https://cdn.jsdelivr.net/npm/bootstrap-icons/font/bootstrap-icons.css" rel="stylesheet">
<style>
body { font-family: 'Inter', sans-serif; background:#f6f7fb; }
.sidebar { width:260px; min-height:100vh; background:#fff; border-right:1px solid #e5e7eb; padding:1.5rem; position:fixed; top:0; left:0; }
.sidebar h4 { color:#0d6efd; margin-bottom:1.5rem; }
.sidebar .nav-link { color:#333; border-radius:6px; padding:.5rem .75rem; font-weight:500; display:flex; align-items:center; gap:8px; }
.sidebar .nav-link.active, .sidebar .nav-link:hover { color:#0d6efd; background:#f1f5f9; }
.content { margin-left:280px; padding:24px; }
.card-modern { background:#fff; border-radius:12px; box-shadow:0 6px 18px rgba(16,24,40,0.06); border:1px solid rgba(16,24,40,0.04); padding:20px; }
@media (max-width:768px){ .sidebar{position:relative;width:100%;min-height:auto;} .content{margin-left:0;} }
</style>
</head>
<body>

<!-- Sidebar -->
<aside class="sidebar d-flex flex-column">
  <h4><i class="bi bi-shield-lock me-2"></i> User Panel</h4>
  <nav class="nav flex-column">
    <a class="nav-link" href="index.php"><i class="bi bi-speedometer2 me-2"></i> Dashboard</a>
    <a class="nav-link" href="projects/list_projects.php"><i class="bi bi-kanban me-2"></i> Projects</a>
    <a class="nav-link" href="add_users.php"><i class="bi bi-person-plus me-2"></i> Add User</a>
    <a class="nav-link active" href="change_password.php"><i class="bi bi-key me-2"></i> Change Password</a>
    <a class="nav-link text-danger mt-auto" href="logout.php"><i class="bi bi-box-arrow-right me-2"></i> Logout</a>
  </nav>
</aside>

<!-- Main content -->
<main class="content">
  <div class="container">
    <div class="row justify-content-center">
      <div class="col-md-6">
        <div class="card-modern">
          <h4 class="mb-3"><i class="bi bi-key me-2 text-primary"></i> Change Password</h4>
          <?php if ($message): ?>
            <div class="alert alert-info"><?= htmlspecialchars($message) ?></div>
          <?php endif; ?>
          <form method="POST">
            <div class="mb-3">
              <label class="form-label">Email</label>
              <input type="email" name="email" class="form-control" required>
            </div>
            <div class="mb-3">
              <label class="form-label">Old Password</label>
              <input type="password" name="old_password" class="form-control" required>
            </div>
            <div class="mb-3">
              <label class="form-label">New Password</label>
              <input type="password" name="new_password" class="form-control" required>
            </div>
            <div class="mb-3">
              <label class="form-label">Confirm New Password</label>
              <input type="password" name="confirm_password" class="form-control" required>
            </div>
            <button type="submit" class="btn btn-primary w-100">Update Password</button>
          </form>
        </div>
      </div>
    </div>
  </div>
</main>

</body>
</html>
