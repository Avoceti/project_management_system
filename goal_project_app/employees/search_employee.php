<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    exit("Unauthorized");
}
require_once "../config/db.php";

// Search query
$q = isset($_GET['q']) ? trim($_GET['q']) : "";

// Pagination
$limit = 12;
$page = isset($_GET['page']) ? max(1, (int)$_GET['page']) : 1;
$offset = ($page - 1) * $limit;

$where = "";
$params = [];
if ($q !== "") {
    $where = "WHERE full_name LIKE :q OR employee_id LIKE :q OR phone LIKE :q";
    $params[':q'] = "%$q%";
}

// Count total
$countSql = "SELECT COUNT(*) FROM employees $where";
$countStmt = $pdo->prepare($countSql);
$countStmt->execute($params);
$totalEmployees = (int)$countStmt->fetchColumn();
$totalPages = max(1, ceil($totalEmployees / $limit));

// Fetch employees
$sql = "SELECT id, employee_id, full_name, phone, salary, status, photo, created_at
        FROM employees 
        $where
        ORDER BY full_name ASC
        LIMIT :limit OFFSET :offset";
$stmt = $pdo->prepare($sql);
foreach ($params as $k => $v) {
    $stmt->bindValue($k, $v);
}
$stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
$stmt->bindValue(':offset', $offset, PDO::PARAM_INT);
$stmt->execute();
$employees = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<table class="table table-hover align-middle">
  <thead class="table-light">
    <tr>
      <th>#</th>
      <th>Photo</th>
      <th>Employee ID</th>
      <th>Full Name</th>
      <th>Phone</th>
      <th>Salary</th>
      <th>Status</th>
      <th>Created</th>
      <th>Actions</th>
    </tr>
  </thead>
  <tbody>
    <?php if (empty($employees)): ?>
      <tr><td colspan="9" class="text-center">No employees found</td></tr>
    <?php else: ?>
      <?php foreach ($employees as $i => $emp): ?>
        <tr>
          <td><?= $offset + $i + 1 ?></td>
          <td>
            <?php if (!empty($emp['photo']) && file_exists("uploads/employees/" . $emp['photo'])): ?>
              <a href="view_employee.php?id=<?= $emp['id'] ?>">
                <img src="uploads/employees/<?= htmlspecialchars($emp['photo']) ?>" class="employee-photo" alt="Photo" style="width:50px;height:50px;object-fit:cover;border-radius:50%;">
              </a>
            <?php else: ?>
              <span class="text-muted">No photo</span>
            <?php endif; ?>
          </td>
          <td><?= htmlspecialchars($emp['employee_id']) ?></td>
          <td><?= htmlspecialchars($emp['full_name']) ?></td>
          <td><?= htmlspecialchars($emp['phone']) ?></td>
          <td>$<?= number_format($emp['salary'], 2) ?></td>
          <td class="<?= $emp['status']=='active'?'status-active':'status-inactive' ?>">
            <?= ucfirst($emp['status']) ?>
          </td>
          <td><?= date("Y-m-d", strtotime($emp['created_at'])) ?></td>
          <td>
            <a href="view_employee.php?id=<?= $emp['id'] ?>" class="btn btn-sm btn-info">👁 View</a>
            <a href="edit_employee.php?id=<?= $emp['id'] ?>" class="btn btn-sm btn-primary">✏️ Edit</a>
            <?php if ($emp['status'] === 'active'): ?>
              <a href="?action=fire&id=<?= $emp['id'] ?>" class="btn btn-sm btn-warning" onclick="return confirm('Fire this employee?')">🔥 Fire</a>
            <?php else: ?>
              <a href="?action=restore&id=<?= $emp['id'] ?>" class="btn btn-sm btn-success">♻️ Restore</a>
            <?php endif; ?>
            <a href="?action=delete&id=<?= $emp['id'] ?>" class="btn btn-sm btn-danger" onclick="return confirm('Delete permanently?')">🗑️ Delete</a>
          </td>
        </tr>
      <?php endforeach; ?>
    <?php endif; ?>
  </tbody>
</table>

<!-- Pagination -->
<div class="d-flex justify-content-between align-items-center">
  <a href="search_employee.php?q=<?= urlencode($q) ?>&page=<?= max(1, $page-1) ?>" 
     class="btn btn-outline-secondary btn-sm <?= $page==1?'disabled':'' ?>">Previous</a>
  <span>Page <?= $page ?> of <?= $totalPages ?></span>
  <a href="search_employee.php?q=<?= urlencode($q) ?>&page=<?= min($totalPages, $page+1) ?>" 
     class="btn btn-outline-secondary btn-sm <?= $page==$totalPages?'disabled':'' ?>">Next</a>
</div>
