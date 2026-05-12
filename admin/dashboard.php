<?php
// admin/dashboard.php
require_once '../config/core.php';
checkAccess('admin');

$database = new Database();
$db = $database->getConnection();

$stats = [
    'total' => $db->query("SELECT COUNT(*) FROM items WHERE status='approved'")->fetchColumn(),
    'pending' => $db->query("SELECT COUNT(*) FROM items WHERE status='pending'")->fetchColumn(),
    'users' => $db->query("SELECT COUNT(*) FROM users WHERE role='regular' AND status='active'")->fetchColumn()
];

$base_path = '../';
$pageTitle = "Admin Dashboard";
require_once '../includes/header.php';
?>

<h1>Admin Dashboard</h1>
<p style="color: var(--text-muted);">Manage inventory and regular users.</p>

<div class="stats-grid" style="margin-top: 2.5rem;">
    <div class="stat-card">
        <div class="stat-icon" style="background: rgba(99, 102, 241, 0.1); color: var(--primary);">
            <i class="fas fa-boxes"></i>
        </div>
        <div class="stat-info">
            <h3><?php echo $stats['total']; ?></h3>
            <p>Active Items</p>
        </div>
    </div>
    <div class="stat-card">
        <div class="stat-icon" style="background: rgba(245, 158, 11, 0.1); color: var(--warning);">
            <i class="fas fa-clock"></i>
        </div>
        <div class="stat-info">
            <h3><?php echo $stats['pending']; ?></h3>
            <p>Pending Approval</p>
        </div>
    </div>
    <div class="stat-card">
        <div class="stat-icon" style="background: rgba(16, 185, 129, 0.1); color: var(--success);">
            <i class="fas fa-users"></i>
        </div>
        <div class="stat-info">
            <h3><?php echo $stats['users']; ?></h3>
            <p>Regular Users</p>
        </div>
    </div>
</div>

<div class="card" style="margin-top: 2rem;">
    <h3>Actions</h3>
    <div style="display: flex; gap: 1rem; margin-top: 1rem;">
        <a href="inventory.php?action=add" class="btn btn-primary" style="width: auto;">Add Item</a>
        <a href="inventory.php?filter=pending" class="btn" style="background:var(--warning); color:white; width:auto;">Review Pending</a>
        <a href="users.php" class="btn" style="background:var(--success); color:white; width:auto;">Manage Users</a>
    </div>
</div>

    </div> <!-- end container -->
</body>
</html>
