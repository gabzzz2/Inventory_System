<?php
// superadmin/dashboard.php
require_once '../config/core.php';
checkAccess('super_admin');

$database = new Database();
$db = $database->getConnection();

$total_admins = $db->query("SELECT COUNT(*) FROM users WHERE role='admin' AND status='active'")->fetchColumn();
$total_items = $db->query("SELECT COUNT(*) FROM items WHERE status='approved'")->fetchColumn();

$base_path = '../';
$pageTitle = "SuperAdmin Dashboard";
require_once '../includes/header.php';
?>

<h1>Super Admin Dashboard</h1>
<p style="color: var(--text-muted);">Welcome to the master control panel.</p>

<div class="stats-grid" style="margin-top: 2.5rem;">
    <div class="stat-card">
        <div class="stat-icon" style="background: rgba(99, 102, 241, 0.1); color: var(--primary);">
            <i class="fas fa-user-shield"></i>
        </div>
        <div class="stat-info">
            <h3><?php echo $total_admins; ?></h3>
            <p>Active Admins</p>
        </div>
    </div>
    <div class="stat-card">
        <div class="stat-icon" style="background: rgba(16, 185, 129, 0.1); color: var(--success);">
            <i class="fas fa-boxes"></i>
        </div>
        <div class="stat-info">
            <h3><?php echo $total_items; ?></h3>
            <p>Total Items</p>
        </div>
    </div>
</div>

<div class="card" style="margin-top: 2rem;">
    <h3>Quick Links</h3>
    <div style="display: flex; gap: 1rem; margin-top: 1rem;">
        <a href="admins.php" class="btn btn-primary" style="width: auto;">Manage Admins</a>
    </div>
</div>

    </div> <!-- end container -->
</body>
</html>
