<?php
// includes/header.php
// $base_path should be '../' for role folders
$base = isset($base_path) ? $base_path : './';
?>
<!DOCTYPE html>
<html lang="en" data-theme="<?php echo $theme; ?>">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo isset($pageTitle) ? $pageTitle : 'Inventory System'; ?></title>
    <link rel="stylesheet" href="<?php echo $base; ?>assets/css/style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>
<body>
    <nav class="navbar">
        <a href="dashboard.php" class="nav-brand">ClothesInventory</a>
        <div class="nav-links">
            <a href="dashboard.php"><i class="fas fa-home"></i> Dashboard</a>
            
            <?php if ($_SESSION['role'] == 'super_admin'): ?>
                <a href="admins.php"><i class="fas fa-user-shield"></i> Admins</a>
            <?php elseif ($_SESSION['role'] == 'admin'): ?>
                <a href="inventory.php"><i class="fas fa-tshirt"></i> Inventory</a>
                <a href="users.php"><i class="fas fa-users"></i> Users</a>
            <?php else: ?>
                <a href="inventory.php"><i class="fas fa-search"></i> View Inventory</a>
                <a href="add.php"><i class="fas fa-plus"></i> Add Item</a>
            <?php endif; ?>

            <a href="?theme=<?php echo ($theme == 'dark') ? 'light' : 'dark'; ?>" class="theme-toggle" title="Toggle Light/Dark Mode">
                <i class="fas fa-<?php echo ($theme == 'dark') ? 'sun' : 'moon'; ?>"></i>
            </a>
            
            <span style="color:var(--text-muted); font-size:0.875rem; margin-left:1rem;">
                <i class="fas fa-user-circle"></i> <?php echo e($_SESSION['username']); ?>
            </span>
            <a href="<?php echo $base; ?>auth.php?action=logout" style="color:var(--danger);"><i class="fas fa-sign-out-alt"></i> Logout</a>
        </div>
    </nav>
    <div class="container" style="padding-top: 2rem;">
