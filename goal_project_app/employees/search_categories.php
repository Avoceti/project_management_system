<?php
require_once "../config/db.php";

$search = $_GET['search'] ?? '';
$searchQuery = "";
$params = [];

if ($search) {
    $searchQuery = "WHERE name LIKE :search";
    $params[':search'] = "%$search%";
}

$stmt = $pdo->prepare("SELECT * FROM employee_categories $searchQuery ORDER BY name ASC LIMIT 12");
$stmt->execute($params);
$categories = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<table class="table table-striped table-hover mb-0">
    <thead class="table-dark">
        <tr>
            <th>ID</th>
            <th>Name</th>
            <th>Created At</th>
            <th>Updated At</th>
            <th width="180">Actions</th>
        </tr>
    </thead>
    <tbody>
        <?php if($categories): ?>
            <?php foreach($categories as $cat): ?>
                <tr>
                    <td><?=$cat['id']?></td>
                    <td><?=htmlspecialchars($cat['name'])?></td>
                    <td><?=$cat['created_at']?></td>
                    <td><?=$cat['updated_at']?></td>
                    <td>
                        <a href="employee_categories_edit.php?id=<?=$cat['id']?>" class="btn btn-sm btn-warning">Edit</a>
                        <a href="employee_categories_delete.php?id=<?=$cat['id']?>" class="btn btn-sm btn-danger" onclick="return confirm('Delete this category?')">Delete</a>
                    </td>
                </tr>
            <?php endforeach; ?>
        <?php else: ?>
            <tr><td colspan="5" class="text-center">No categories found.</td></tr>
        <?php endif; ?>
    </tbody>
</table>
