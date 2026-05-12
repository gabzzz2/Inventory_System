<?php
// index.php
require_once 'config/core.php';

if (!isLoggedIn()) {
    redirect('auth.php');
}

// Redirect to specific role folder
switch($_SESSION['role']) {
    case 'super_admin': redirect('superadmin/dashboard.php'); break;
    case 'admin':       redirect('admin/dashboard.php'); break;
    case 'regular':     redirect('regular/dashboard.php'); break;
    default:            redirect('auth.php'); break;
}
?>
