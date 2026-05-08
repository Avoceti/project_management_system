<?php
require_once "../config/db.php";

$limit = 10; // show 10 per page
$page = isset($_POST['page']) ? (int)$_POST['page'] : 1;
if ($page < 1) $page = 1;
$offset = ($page - 1) * $limit;

$search = $_POST['search'] ?? '';
$month  = $_POST['month'] ?? '';
$year   = $_POST['year'] ?? '';

$sql = "
    SELECT ep.*, 
           e.id AS emp_id, 
           e.full_name, 
           e.employee_id AS emp_code, 
           e.nid, 
           e.status, 
           m.name AS method_name
    FROM employee_payments ep
    INNER JOIN employees e ON ep.employee_id = e.id
    LEFT JOIN payment_methods m ON ep.method_id = m.id
    WHERE e.status='Active'
";
$params = [];

if ($search) {
    $sql .= " AND (e.full_name LIKE ? OR e.employee_id LIKE ? OR e.nid LIKE ?)";
    $params[] = "%$search%";
    $params[] = "%$search%";
    $params[] = "%$search%";
}
if ($month) {
    $sql .= " AND MONTH(ep.payment_date)=?";
    $params[] = $month;
}
if ($year) {
    $sql .= " AND YEAR(ep.payment_date)=?";
    $params[] = $year;
}

// Count total rows
$countSql = "SELECT COUNT(*) FROM ($sql) AS sub";
$stmt = $pdo->prepare($countSql);
$stmt->execute($params);
$totalRecords = $stmt->fetchColumn();
$totalPages = $totalRecords > 0 ? ceil($totalRecords / $limit) : 1;

// Add pagination
$sql .= " ORDER BY ep.payment_date DESC LIMIT $limit OFFSET $offset";
$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$payments = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<table class="table table-hover align-middle table-striped">
  <thead class="table-dark">
    <tr>
      <th>Employee</th>
      <th>Employee ID</th>
      <th>NID</th>
      <th>Status</th>
      <th>Amount</th>
      <th>Payment Date</th>
      <th>Method</th>
      <th>Action</th>
    </tr>
  </thead>
  <tbody>
    <?php if ($payments): ?>
      <?php foreach ($payments as $p): ?>
      <tr>
        <td>
          <a href="view_employee.php?id=<?= (int)$p['emp_id'] ?>" class="text-decoration-none">
            <?= htmlspecialchars($p['full_name']) ?>
          </a>
        </td>
        <td><?= htmlspecialchars($p['emp_code']) ?></td>
        <td><?= htmlspecialchars($p['nid']) ?></td>
        <td><span class="badge bg-success">Active</span></td>
        <td>$<?= number_format($p['amount'], 2) ?></td>
        <td><?= htmlspecialchars($p['payment_date']) ?></td>
        <td><?= htmlspecialchars($p['method_name'] ?? '-') ?></td>
        <td>
          <a class="btn btn-sm btn-primary" href="view_employee.php?id=<?= (int)$p['emp_id'] ?>">
            <i class="bi bi-eye me-1"></i> View
          </a>
        </td>
      </tr>
      <?php endforeach; ?>
    <?php else: ?>
      <tr>
        <td colspan="8" class="text-center">No payments found</td>
      </tr>
    <?php endif; ?>
  </tbody>
</table>

<?php if ($totalPages > 1): ?>
<div class="d-flex justify-content-center align-items-center gap-3 mt-3">
  <button 
    class="btn btn-outline-primary btn-sm prev-page" 
    data-page="<?= max(1, $page-1) ?>" 
    <?= ($page == 1) ? 'disabled' : '' ?>>
    Previous
  </button>
  <span><strong><?= $page ?></strong> of <strong><?= $totalPages ?></strong></span>
  <button 
    class="btn btn-outline-primary btn-sm next-page" 
    data-page="<?= min($totalPages, $page+1) ?>" 
    <?= ($page == $totalPages) ? 'disabled' : '' ?>>
    Next
  </button>
</div>
<?php endif; ?>
