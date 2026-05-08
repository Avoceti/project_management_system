<?php
if (session_status() === PHP_SESSION_NONE) session_start();
if(!isset($_SESSION['user_id'])){
    header("Location: login.php");
    exit;
}
require_once __DIR__ . "/config/db.php";

/* ---------- KPI Data ---------- */
function formatMoney($amount){
    if($amount >= 1000000) return '$'.round($amount/1000000,3).'M';
    return '$'.number_format($amount,3);
}

$totalGoalEstimated = $pdo->query("SELECT SUM(target_amount) FROM goals")->fetchColumn() ?: 0;
$totalGoalSpent = $pdo->query("SELECT SUM(current_amount) FROM goals")->fetchColumn() ?: 0;
$totalGoalRemaining = max($totalGoalEstimated - $totalGoalSpent, 0);
$totalGoals = $pdo->query("SELECT COUNT(*) FROM goals")->fetchColumn();

$totalProjectEstimated = $pdo->query("SELECT SUM(target_amount) FROM projects")->fetchColumn() ?: 0;
$totalProjectSpent = $pdo->query("SELECT SUM(current_amount) FROM projects")->fetchColumn() ?: 0;
$totalProjectRemaining = max($totalProjectEstimated - $totalProjectSpent, 0);
$totalProjects = $pdo->query("SELECT COUNT(*) FROM projects")->fetchColumn();

/* ---------- Fetch Goals & Projects ---------- */
$goals = $pdo->query("SELECT * FROM goals ORDER BY created_at DESC")->fetchAll();
$projects = $pdo->query("SELECT * FROM projects ORDER BY created_at DESC")->fetchAll();

/* ---------- Recent Activity ---------- */
$recentItems = $pdo->query("
    SELECT id, title, target_amount, current_amount, progress, status, created_at, 'Project' AS type
    FROM projects
    UNION ALL
    SELECT id, title, target_amount, current_amount, progress, status, created_at, 'Goal' AS type
    FROM goals
    ORDER BY created_at DESC
    LIMIT 5
")->fetchAll();

/* ---------- Notifications ---------- */
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
$notifCount = count($notifications);

/* ---------- Welcome Message ---------- */
$showWelcome = false;
if(!isset($_SESSION['welcome_shown'])){
    $showWelcome = true;
    $_SESSION['welcome_shown'] = true;
}
$userName = $_SESSION['user_name'] ?? '';

function progressColor($progress){
    if($progress < 20) return 'red';
    if($progress >=30 && $progress <=49) return 'blue';
    if($progress >=50 && $progress <=99) return 'gold';
    if($progress == 100) return 'green';
    return 'gray';
}
function cardColor($progress){
    if($progress<20) return '#f8d7da';
    if($progress>=30 && $progress<=49) return '#cfe2ff';
    if($progress>=50 && $progress<=99) return '#000dff70';
    if($progress==100) return '#d4edda';
    return '#f8f9fa';
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>Dashboard</title>
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
<link href="https://cdn.jsdelivr.net/npm/bootstrap-icons/font/bootstrap-icons.css" rel="stylesheet">
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<style>
body.dark-mode { background:#121212; color:#f1f1f1; }
.sidebar { width:250px; min-height:100vh; background:#f8f9fa; border-right:1px solid #e6dedeff; padding:1rem; position:fixed; top:0; left:0; z-index:1020; }
.sidebar .nav-link { color:#333; display:flex; align-items:center; gap:6px; margin-bottom:4px; }
.sidebar .nav-link:hover { background:#e9ecef; border-radius:5px; }
.content { margin-left:260px; padding:20px 20px 60px 20px; }
.card-modern { background:#fff; border:1px solid #dee2e6; border-radius:12px; box-shadow:0 4px 8px hsla(0, 0%, 0%, 0.15); transition:transform 0.2s; margin-bottom:15px; }
.card-modern:hover { transform:translateY(-3px); }
body.dark-mode .card-modern { background:#1e1e1e; border-color:#444; }
.footer { text-align:center; padding:15px; margin-top:30px; font-size:14px; color:#666; border-top:1px solid #dee2e6; }
body.dark-mode .footer { color:#aaa; border-color:#444; }
canvas { max-height:180px !important; }
.badge-goal { background:#0d6efd; }
.badge-project { background:#198754; }
.welcome-toast { position:fixed; top:20px; right:20px; z-index:1050; }
.navbar-fixed { position:fixed; top:0; left:260px; right:0; z-index:1040; }
.search-box { max-width:300px; }
</style>
</head>
<body>

<!-- Sidebar -->
<div class="sidebar">
  <h4 class="mb-4"><i class="bi bi-bullseye text-primary"></i> Originals365</h4>
  <ul class="nav flex-column">
    <li><a class="nav-link" href="#"><i class="bi bi-speedometer2"></i> Overview</a></li>
    <li>
      <a class="nav-link" data-bs-toggle="collapse" href="#goalsMenu"><i class="bi bi-flag"></i> Goals</a>
      <div class="collapse" id="goalsMenu">
        <ul class="nav flex-column ms-3">
          <li><a class="nav-link" href="goals/add_goal.php"><i class="bi bi-plus-circle"></i> Add Goal</a></li>
          <li><a class="nav-link" href="goals/list_goals.php"><i class="bi bi-list"></i> List Goals</a></li>
        </ul>
      </div>
    </li>
    <li>
      <a class="nav-link" data-bs-toggle="collapse" href="#projectsMenu"><i class="bi bi-kanban"></i> Projects</a>
      <div class="collapse" id="projectsMenu">
        <ul class="nav flex-column ms-3">
          <li><a class="nav-link" href="projects/add_project.php"><i class="bi bi-plus-circle"></i> Add Project</a></li>
          <li><a class="nav-link" href="projects/list_projects.php"><i class="bi bi-list"></i> List Projects</a></li>
        </ul>
      </div>
    </li>
    <li><a class="nav-link" href="complete.php"><i class="bi bi-check2-circle me-2"></i> Complete</a></li>
    
    <!-- Project Categories Menu -->
    <li>
      <a class="nav-link" data-bs-toggle="collapse" href="#categoryMenu"><i class="bi bi-tags"></i> Project Categories </a>
      <div class="collapse show ps-3" id="categoryMenu">
        <a href="projects/add_category.php" class="nav-link"><i class="bi bi-plus-circle"></i> Add Category</a>
        <a href="projects/manage_categories.php" class="nav-link"><i class="bi bi-list-task"></i> Manage Categories</a>
      </div>
    </li>
    <a class="nav-link" data-bs-toggle="collapse" href="#employeeMenu" role="button" aria-expanded="false">
     <i class="bi bi-people-fill me-2"></i> Employees
   </a>
    <div class="collapse submenu" id="employeeMenu">
     <a class="nav-link" href="employees/dashboard.php"><i class="bi bi-speedometer2 me-2"></i> Dashboard</a>
     <a class="nav-link" href="employees/manage_employee.php"><i class="bi bi-person-badge me-2"></i> Manage Employees</a>
     <a class="nav-link" href="employees/employee_categories.php"><i class="bi bi-diagram-3 me-2"></i> Employee Categories</a>
     <a class="nav-link" href="employees/payments.php"><i class="bi bi-cash-coin me-2"></i> Pay Employee List</a>
     <a class="nav-link" href="employees/payments.php"><i class="bi bi-journal-text me-2"></i> Payment History</a>
    <a class="nav-link" href="employees/methods.php"><i class="bi bi-credit-card me-2"></i> Payment Methods</a>
    </div>

    <li>
      <a class="nav-link" data-bs-toggle="collapse" href="#settingsMenu"><i class="bi bi-gear"></i> Settings</a>
      <div class="collapse" id="settingsMenu">
        <ul class="nav flex-column ms-3">
          <li><a class="nav-link" href="change_password.php"><i class="bi bi-lock"></i> Change Password</a></li>
          <li><a class="nav-link" href="add_users.php"><i class="bi bi-person-plus"></i> Add User</a></li>
           <li><a class="nav-link" href="manage_users.php"><i class="bi bi-person-plus"></i> Manage Users</a></li>
          <li><a class="nav-link" id="darkModeToggle" href="#"><i class="bi bi-moon"></i> Dark Mode</a></li>
        </ul>
      </div>
    </li>
    <li><a class="nav-link text-danger" href="logout.php"><i class="bi bi-box-arrow-right"></i> Logout</a></li>
  </ul>
</div>

<!-- Content -->
<div class="content">

  <!-- Navbar -->
  <nav class="navbar navbar-expand-lg navbar-light bg-light rounded px-3 navbar-fixed">
    <div class="ms-auto d-flex align-items-center">
      <div class="me-3">
        <div class="card p-2 small text-dark">
            Welcome, <?= htmlspecialchars($userName) ?>!
        </div>
      </div>
      <div class="dropdown me-3">
        <a class="btn btn-light position-relative" href="#" data-bs-toggle="dropdown" id="notifDropdown">
          <i class="bi bi-bell fs-4"></i>
          <?php if($notifCount > 0): ?>
          <span class="position-absolute top-0 start-100 translate-middle badge rounded-pill bg-danger" id="notifBadge"><?= $notifCount ?></span>
          <?php endif; ?>
        </a>
        <ul class="dropdown-menu dropdown-menu-end p-2" style="width:300px; max-height:300px; overflow-y:auto;" id="notifList">
          <?php if($notifCount == 0): ?>
            <li><span class="dropdown-item-text text-muted">No notifications</span></li>
          <?php else: foreach($notifications as $n): ?>
            <li id="notif-<?= $n['type'] ?>-<?= $n['id'] ?>">
              <span class="dropdown-item">
                <i class="bi bi-exclamation-circle text-danger me-2"></i>
                <?= htmlspecialchars($n['type']) ?>: <?= htmlspecialchars($n['title']) ?> (<?= $n['deadline'] ?>)
                <button class="btn btn-sm btn-danger float-end btn-delete-notif" data-type="<?= $n['type'] ?>" data-id="<?= $n['id'] ?>"><i class="bi bi-trash"></i></button>
              </span>
            </li>
          <?php endforeach; endif; ?>
        </ul>
      </div>
      <button class="btn btn-outline-dark" id="topDarkMode"><i class="bi bi-moon-stars"></i></button>
    </div>
  </nav>

  <!-- KPI Cards -->
  <div class="row g-3 mt-5">
    <?php 
    $kpis = [
        ['label'=>'Total Goals','value'=>$totalGoals,'icon'=>'bi-flag-fill','color'=>'#0d6efd'],
        ['label'=>'Total Projects','value'=>$totalProjects,'icon'=>'bi-kanban-fill','color'=>'#198754'],
        ['label'=>'Goals Spent','value'=>formatMoney($totalGoalSpent),'icon'=>'bi-currency-dollar','color'=>'#0d6efd'],
        ['label'=>'Goals Estimated','value'=>formatMoney($totalGoalEstimated),'icon'=>'bi-currency-dollar','color'=>'hsla(45, 100%, 50%, 0.98)'],
        ['label'=>'Goals Remaining','value'=>formatMoney($totalGoalRemaining),'icon'=>'bi-wallet2','color'=>'#dc3545'],
        ['label'=>'Projects Spent','value'=>formatMoney($totalProjectSpent),'icon'=>'bi-currency-dollar','color'=>'#198754'],
        ['label'=>'Projects Estimated','value'=>formatMoney($totalProjectEstimated),'icon'=>'bi-currency-dollar','color'=>'#0dcaf0'],
        ['label'=>'Projects Remaining','value'=>formatMoney($totalProjectRemaining),'icon'=>'bi-wallet2','color'=>'#dc3545'],
    ];
    foreach($kpis as $kpi): ?>
    <div class="col-md-3 col-sm-6">
      <div class="card-modern p-3 text-center" style="background:<?= $kpi['color']?>10;">
        <h6><i class="bi <?= $kpi['icon']?> me-2" style="color:<?= $kpi['color']?>"></i><?= $kpi['label'] ?></h6>
        <h4><?= $kpi['value'] ?></h4>
      </div>
    </div>
    <?php endforeach; ?>
  </div>

  <!-- Goals Section -->
  <h5 class="mt-4">Goals Progress</h5>
  <div class="input-group mb-2 search-box">
      <span class="input-group-text"><i class="bi bi-search"></i></span>
      <input type="text" id="goalSearch" class="form-control form-control-sm" placeholder="Search Goals...">
  </div>
  <div class="row g-3" id="goalContainer">
  <?php foreach($goals as $goal):
      $target = $goal['target_amount'] ?? 0;
      $current = $goal['current_amount'] ?? 0;
      $progress = $target>0 ? round(($current/$target)*100,2) : 0;
      $color = progressColor($progress);
      $bg = cardColor($progress);
  ?>
      <div class="col-md-3 col-sm-6 goal-card">
        <div class="card-modern p-3 text-center" style="background:<?= $bg ?>">
          <h6 class="goal-title"><?= htmlspecialchars($goal['title']) ?></h6>
          <canvas id="goalChart<?= $goal['id'] ?>"></canvas>
          <small style="color:<?= $color ?>"><?= $progress ?>% Complete</small>
        </div>
      </div>
  <?php endforeach; ?>
  </div>

  <!-- Goal Pagination -->
  <div class="d-flex justify-content-center align-items-center mt-2 mb-4">
    <button class="btn btn-sm btn-outline-primary me-2" id="goalPrev">Previous</button>
    <span id="goalPageInfo">1 of 1</span>
    <button class="btn btn-sm btn-outline-primary ms-2" id="goalNext">Next</button>
  </div>

  <!-- Projects Section -->
  <h5 class="mt-4">Projects Progress</h5>
  <div class="input-group mb-2 search-box">
      <span class="input-group-text"><i class="bi bi-search"></i></span>
      <input type="text" id="projectSearch" class="form-control form-control-sm" placeholder="Search Projects...">
  </div>
  <div class="row g-3" id="projectContainer">
  <?php foreach($projects as $project):
      $target = $project['target_amount'] ?? 0;
      $current = $project['current_amount'] ?? 0;
      $progress = $target>0 ? round(($current/$target)*100,2) : 0;
      $color = progressColor($progress);
      $bg = cardColor($progress);
  ?>
      <div class="col-md-3 col-sm-6 project-card">
        <div class="card-modern p-3 text-center" style="background:<?= $bg ?>">
          <h6 class="project-title"><?= htmlspecialchars($project['title']) ?></h6>
          <canvas id="projectChart<?= $project['id'] ?>"></canvas>
          <small style="color:<?= $color ?>"><?= $progress ?>% Complete</small>
        </div>
      </div>
  <?php endforeach; ?>
  </div>

  <!-- Project Pagination -->
  <div class="d-flex justify-content-center align-items-center mt-2 mb-4">
    <button class="btn btn-sm btn-outline-success me-2" id="projectPrev">Previous</button>
    <span id="projectPageInfo">1 of 1</span>
    <button class="btn btn-sm btn-outline-success ms-2" id="projectNext">Next</button>
  </div>

  <!-- Recent Activity -->
  <div class="card-modern p-3 mt-4">
    <h5 class="mb-3">Recent Activity</h5>
    <div class="table-responsive">
      <table class="table table-hover align-middle">
        <thead>
          <tr>
            <th>Title</th>
            <th>Type</th>
            <th>Target</th>
            <th>Current</th>
            <th>Progress</th>
            <th>Status</th>
            <th>Action</th>
          </tr>
        </thead>
        <tbody id="recentBody">
        <?php foreach($recentItems as $item):
            $target = $item['target_amount'] ?? 0;
            $current = $item['current_amount'] ?? 0;
            $progress = $target>0 ? round(($current/$target)*100,2) : 0;
            $color = progressColor($progress);
            $status = $progress == 100 ? 'Complete' : $item['status'];
        ?>
          <tr id="<?= strtolower($item['type']).'-'.$item['id'] ?>">
            <td><?= htmlspecialchars($item['title']) ?></td>
            <td><span class="badge <?= $item['type']=='Goal'?'badge-goal':'badge-project' ?>"><?= $item['type'] ?></span></td>
            <td><?= formatMoney($target) ?></td>
            <td><?= formatMoney($current) ?></td>
            <td>
              <div class="progress" style="height:6px;">
                <div class="progress-bar" style="width: <?= $progress ?>%; background-color:<?= $color ?>;"></div>
              </div>
              <small style="color:<?= $color ?>"><?= $progress ?>%</small>
            </td>
            <td><?= $status ?></td>
            <td>
              <button class="btn btn-sm btn-primary btn-view" data-type="<?= $item['type'] ?>" data-id="<?= $item['id'] ?>"><i class="bi bi-eye"></i></button>
              <button class="btn btn-sm btn-warning btn-edit" data-type="<?= $item['type'] ?>" data-id="<?= $item['id'] ?>"><i class="bi bi-pencil"></i></button>
              <button class="btn btn-sm btn-danger btn-delete" data-type="<?= $item['type'] ?>" data-id="<?= $item['id'] ?>"><i class="bi bi-trash"></i></button>
            </td>
          </tr>
        <?php endforeach; ?>
        </tbody>
      </table>
    </div>
  </div>

  <!-- Footer -->
  <div class="footer">
    &copy; <?= date("Y") ?> Goal & Project Management System | Designed with <i class="bi bi-heart-fill text-danger"></i>
  </div>
</div>

<!-- Welcome Toast -->
<?php if($showWelcome): ?>
<div class="toast show welcome-toast">
  <div class="toast-header
    <strong class="me-auto">Welcome</strong>
    <button type="button" class="btn-close" data-bs-dismiss="toast"></button>
  </div>
  <div class="toast-body">
    Welcome back, <?= htmlspecialchars($userName) ?>!
  </div>
</div>
<?php endif; ?>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
<script src="https://code.jquery.com/jquery-3.7.0.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
// Dark Mode Toggle
const toggles = [document.getElementById('darkModeToggle'), document.getElementById('topDarkMode')];
toggles.forEach(btn => { 
    if(btn){ 
        btn.addEventListener('click', ()=>{
            document.body.classList.toggle('dark-mode'); 
            localStorage.setItem('darkMode', document.body.classList.contains('dark-mode')?'on':'off'); 
        }); 
    }
});
if(localStorage.getItem('darkMode')==='on'){ document.body.classList.add('dark-mode'); }

// Charts for Goals
<?php foreach($goals as $goal): ?>
new Chart(document.getElementById('goalChart<?= $goal['id'] ?>'), { 
    data:{
        labels:['Progress'], 
        datasets:[ 
            { type:'doughnut', data:[<?= $goal['current_amount'] ?? 0 ?>, <?= ($goal['target_amount'] ?? 0)-($goal['current_amount'] ?? 0) ?>], backgroundColor:['#0d6efd','#e9ecef'], borderWidth:0 }, 
            { type:'line', data:[<?= $goal['current_amount'] ?? 0 ?>], borderColor:'#0d6efd', fill:false, tension:0.3 } 
        ] 
    }, 
    options:{ plugins:{legend:{display:false}}, responsive:true } 
});
<?php endforeach; ?>

// Charts for Projects
<?php foreach($projects as $project): ?>
new Chart(document.getElementById('projectChart<?= $project['id'] ?>'), { 
    data:{
        labels:['Progress'], 
        datasets:[ 
            { type:'doughnut', data:[<?= $project['current_amount'] ?? 0 ?>, <?= ($project['target_amount'] ?? 0)-($project['current_amount'] ?? 0) ?>], backgroundColor:['#198754','#e9ecef'], borderWidth:0 }, 
            { type:'line', data:[<?= $project['current_amount'] ?? 0 ?>], borderColor:'#198754', fill:false, tension:0.3 } 
        ] 
    }, 
    options:{ plugins:{legend:{display:false}}, responsive:true } 
});
<?php endforeach; ?>

$(document).ready(function(){
    // Delete actions
    $('.btn-delete').click(function(){
        let type = $(this).data('type'); 
        let id = $(this).data('id');
        if(confirm('Are you sure?')){
            $.post('ajax/delete_item.php',{type,type,id},()=>$('#'+type.toLowerCase()+'-'+id).fadeOut());
        }
    });
    $('.btn-edit').click(function(){ 
        let type=$(this).data('type'),id=$(this).data('id'); 
        window.location.href=(type=='Goal'?'goals/edit_goal.php?id='+id:'projects/edit_project.php?id='+id); 
    });
    $('.btn-view').click(function(){ 
        let type=$(this).data('type'),id=$(this).data('id'); 
        window.location.href=(type=='Goal'?'goals/view_goal.php?id='+id:'projects/view_project.php?id='+id); 
    });

    // Notification delete
    $('.btn-delete-notif').click(function(){ 
        let type=$(this).data('type'),id=$(this).data('id');
        $.post('ajax/delete_notification.php',{type,id},function(){ 
            $('#notif-'+type+'-'+id).fadeOut(); 
            if($('#notifList li').length==1) $('#notifList').html('<li><span class="dropdown-item-text text-muted">No notifications</span></li>'); 
            $('#notifBadge').fadeOut(); 
        }); 
    });

    // Live search for Goals
    $('#goalSearch').on('input',function(){ 
        let query=$(this).val().toLowerCase(); 
        $('.goal-card').each(function(){ 
            $(this).toggle($(this).find('.goal-title').text().toLowerCase().includes(query)); 
        }); 
        paginateGoals();
    });

    // Live search for Projects
    $('#projectSearch').on('input',function(){ 
        let query=$(this).val().toLowerCase(); 
        $('.project-card').each(function(){ 
            $(this).toggle($(this).find('.project-title').text().toLowerCase().includes(query)); 
        }); 
        paginateProjects();
    });
});

// Pagination Function
function paginateCards(containerId, cardClass, prevBtnId, nextBtnId, pageInfoId, cardsPerPage=6){
    const container = document.getElementById(containerId);
    const cards = Array.from(container.getElementsByClassName(cardClass));
    let currentPage = 1;

    function showPage(page){
        const visibleCards = cards.filter(c=>c.style.display !== 'none');
        const totalPages = Math.ceil(visibleCards.length / cardsPerPage) || 1;
        if(page<1) page=1;
        if(page>totalPages) page=totalPages;
        currentPage=page;
        visibleCards.forEach((card,i)=>{
            card.style.display=(i>= (currentPage-1)*cardsPerPage && i< currentPage*cardsPerPage?'block':'none')
        });
        document.getElementById(pageInfoId).innerText = `${currentPage} of ${totalPages}`;
    }

    document.getElementById(prevBtnId).onclick = ()=> showPage(currentPage-1);
    document.getElementById(nextBtnId).onclick = ()=> showPage(currentPage+1);
    showPage(1);
    return showPage;
}

// Initialize pagination
const paginateGoals = paginateCards('goalContainer','goal-card','goalPrev','goalNext','goalPageInfo',6);
const paginateProjects = paginateCards('projectContainer','project-card','projectPrev','projectNext','projectPageInfo',6);
</script>
</body>
</html>
