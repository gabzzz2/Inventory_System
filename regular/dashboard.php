<?php
// regular/dashboard.php
require_once '../config/core.php';
checkAccess('regular');

$database = new Database();
$db = $database->getConnection();

$total_approved = $db->query("SELECT COUNT(*) FROM items WHERE status='approved'")->fetchColumn();
$my_pending = $db->prepare("SELECT COUNT(*) FROM items WHERE added_by=? AND status='pending'");
$my_pending->execute([$_SESSION['user_id']]);
$pending_count = $my_pending->fetchColumn();

$base_path = '../';
$pageTitle = "User Dashboard";
require_once '../includes/header.php';
?>

<h1>User Dashboard</h1>
<p style="color: var(--text-muted);">Welcome to the Clothes Inventory.</p>

<div class="stats-grid" style="margin-top: 2.5rem;">
    <div class="stat-card">
        <div class="stat-icon" style="background: rgba(99, 102, 241, 0.1); color: var(--primary);">
            <i class="fas fa-boxes"></i>
        </div>
        <div class="stat-info">
            <h3><?php echo $total_approved; ?></h3>
            <p>Available Items</p>
        </div>
    </div>
    <div class="stat-card">
        <div class="stat-icon" style="background: rgba(245, 158, 11, 0.1); color: var(--warning);">
            <i class="fas fa-clock"></i>
        </div>
        <div class="stat-info">
            <h3><?php echo $pending_count; ?></h3>
            <p>My Pending Submissions</p>
        </div>
    </div>
</div>

<div class="card" style="margin-top: 2rem;">
    <h3>Quick Actions</h3>
    <div style="display: flex; gap: 1rem; margin-top: 1rem;">
        <a href="inventory.php" class="btn btn-primary" style="width: auto;">View Inventory</a>
        <a href="add.php" class="btn" style="background:var(--success); color:white; width:auto;">Add New Item</a>
    </div>
</div>

    </div> <!-- end container -->
</body>
</html>
