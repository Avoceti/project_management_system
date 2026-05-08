<?php
if (session_status() === PHP_SESSION_NONE) session_start();

require_once __DIR__ . "/../config/db.php";

/* Fetch all goals */
$goals = $pdo->query("SELECT * FROM goals ORDER BY created_at DESC")->fetchAll();
?>

<table class="table table-hover align-middle">
    <thead>
        <tr>
            <th>Title</th>
            <th>Target Amount</th>
            <th>Current Amount</th>
            <th>Progress</th>
            <th>Status</th>
            <th>Actions</th>
        </tr>
    </thead>
    <tbody id="goalsBody">
        <?php foreach($goals as $goal):
            $target = $goal['target_amount'] ?? 0;
            $current = $goal['current_amount'] ?? 0;
            $progress = $target>0 ? round(($current/$target)*100,2) : 0;
        ?>
        <tr id="goal-<?= $goal['id'] ?>">
            <td><?= htmlspecialchars($goal['title']) ?></td>
            <td>$<?= number_format($target,2) ?></td>
            <td>$<?= number_format($current,2) ?></td>
            <td>
                <div class="progress" style="height:6px;">
                    <div class="progress-bar" style="width: <?= $progress ?>%;"></div>
                </div>
                <small><?= $progress ?>%</small>
            </td>
            <td><?= htmlspecialchars($goal['status']) ?></td>
            <td>
                <button class="btn btn-sm btn-success btn-view" data-id="<?= $goal['id'] ?>"><i class="bi bi-eye"></i></button>
                <button class="btn btn-sm btn-warning btn-edit" data-id="<?= $goal['id'] ?>"><i class="bi bi-pencil"></i></button>
                <button class="btn btn-sm btn-danger btn-delete" data-id="<?= $goal['id'] ?>"><i class="bi bi-trash"></i></button>
            </td>
        </tr>
        <?php endforeach; ?>
    </tbody>
</table>

<script src="https://code.jquery.com/jquery-3.7.0.min.js"></script>
<script>
$(document).ready(function(){

    // Delete Goal
    $('.btn-delete').click(function(){
        let id = $(this).data('id');
        if(confirm('Are you sure you want to delete this goal?')){
            $.post('ajax_goals.php', {action:'delete', id:id}, function(response){
                if(response.success){
                    $('#goal-'+id).fadeOut();
                } else {
                    alert('Error deleting goal.');
                }
            }, 'json');
        }
    });

    // Edit Goal
    $('.btn-edit').click(function(){
        let id = $(this).data('id');
        window.location.href = 'edit_goal.php?id='+id;
    });

    // View Goal
    $('.btn-view').click(function(){
        let id = $(this).data('id');
        window.location.href = 'view_goal.php?id='+id;
    });

});
</script>
