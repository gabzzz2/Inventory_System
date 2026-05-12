<?php
// superadmin/admins.php
require_once '../config/core.php';
checkAccess('super_admin');

$database = new Database();
$db = $database->getConnection();

$action = isset($_GET['action']) ? $_GET['action'] : 'list';
$error = ""; $success = "";

// Handle POST
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    verifyToken($_POST['csrf_token']);
    
    if (isset($_POST['add_admin'])) {
        $username = trim($_POST['username']);
        $password = password_hash($_POST['password'], PASSWORD_BCRYPT);
        try {
            $stmt = $db->prepare("INSERT INTO users (username, password, role) VALUES (?, ?, 'admin')");
            $stmt->execute([$username, $password]);
            $success = "Admin added successfully.";
            $action = 'list';
        } catch(PDOException $e) { $error = "Username already exists."; }
    }

    if (isset($_POST['reset_pwd'])) {
        $stmt = $db->prepare("UPDATE users SET password = ? WHERE id = ? AND role = 'admin'");
        $stmt->execute([password_hash($_POST['password'], PASSWORD_BCRYPT), $_POST['user_id']]);
        $success = "Password reset successfully.";
        $action = 'list';
    }
}

// Handle GET actions
if (isset($_GET['do']) && isset($_GET['uid'])) {
    $status = ($_GET['do'] == 'archive') ? 'archived' : 'active';
    $db->prepare("UPDATE users SET status = ? WHERE id = ? AND role = 'admin'")->execute([$status, $_GET['uid']]);
    $success = "Admin " . $_GET['do'] . "d.";
}

$base_path = '../';
require_once '../includes/header.php';

if ($action == 'add'): ?>
    <div class="card" style="max-width: 500px; margin: 0 auto;">
        <h2>Add New Admin</h2>
        <?php if($error): ?><div class="alert alert-danger"><?php echo e($error); ?></div><?php endif; ?>
        <form method="POST">
            <input type="hidden" name="csrf_token" value="<?php echo generateToken(); ?>">
            <input type="hidden" name="add_admin" value="1">
            <div class="form-group"><label>Username</label><input type="text" name="username" class="form-control" required></div>
            <div class="form-group"><label>Password</label><input type="password" name="password" class="form-control" required minlength="6"></div>
            <button type="submit" class="btn btn-primary">Create Admin</button>
            <a href="admins.php" style="display:block; text-align:center; margin-top:1rem;">Cancel</a>
        </form>
    </div>
<?php elseif ($action == 'reset'): ?>
    <div class="card" style="max-width: 500px; margin: 0 auto;">
        <h2>Reset Admin Password</h2>
        <form method="POST">
            <input type="hidden" name="csrf_token" value="<?php echo generateToken(); ?>">
            <input type="hidden" name="reset_pwd" value="1">
            <input type="hidden" name="user_id" value="<?php echo $_GET['uid']; ?>">
            <div class="form-group"><label>New Password</label><input type="password" name="password" class="form-control" required minlength="6"></div>
            <button type="submit" class="btn btn-primary">Update Password</button>
            <a href="admins.php" style="display:block; text-align:center; margin-top:1rem;">Cancel</a>
        </form>
    </div>
<?php else: 
    $admins = $db->query("SELECT * FROM users WHERE role='admin' ORDER BY status ASC, username ASC")->fetchAll();
?>
    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 2rem;">
        <h2>Admin Management</h2>
        <a href="admins.php?action=add" class="btn btn-primary" style="width: auto;">Add Admin</a>
    </div>
    <?php if($success): ?><div class="alert alert-approved" style="background:#dcfce7; color:#166534; padding:1rem; border-radius:0.5rem; margin-bottom:1rem;"><?php echo e($success); ?></div><?php endif; ?>
    <div class="table-container">
        <table>
            <thead><tr><th>Username</th><th>Status</th><th>Actions</th></tr></thead>
            <tbody>
                <?php foreach($admins as $a): ?>
                    <tr>
                        <td><strong><?php echo e($a['username']); ?></strong></td>
                        <td><span class="badge badge-<?php echo $a['status']=='active'?'approved':'archived'; ?>"><?php echo ucfirst($a['status']); ?></span></td>
                        <td>
                            <a href="admins.php?action=reset&uid=<?php echo $a['id']; ?>">Reset</a> | 
                            <?php if($a['status']=='active'): ?><a href="admins.php?do=archive&uid=<?php echo $a['id']; ?>" style="color:var(--danger)">Archive</a>
                            <?php else: ?><a href="admins.php?do=restore&uid=<?php echo $a['id']; ?>" style="color:var(--success)">Restore</a><?php endif; ?>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
<?php endif; ?>
    </div> <!-- end container -->
</body>
</html>
