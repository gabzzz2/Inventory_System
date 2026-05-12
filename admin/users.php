<?php
// admin/users.php
require_once '../config/core.php';
checkAccess('admin');

$database = new Database();
$db = $database->getConnection();

$action = isset($_GET['action']) ? $_GET['action'] : 'list';
$success = ""; $error = "";

// Handle POST
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    verifyToken($_POST['csrf_token']);
    
    if (isset($_POST['add_user'])) {
        $username = trim($_POST['username']);
        $password = password_hash($_POST['password'], PASSWORD_BCRYPT);
        try {
            $stmt = $db->prepare("INSERT INTO users (username, password, role) VALUES (?, ?, 'regular')");
            $stmt->execute([$username, $password]);
            $success = "User added successfully.";
            $action = 'list';
        } catch(PDOException $e) { $error = "Username already exists."; }
    }

    if (isset($_POST['reset_pwd'])) {
        $stmt = $db->prepare("UPDATE users SET password = ? WHERE id = ? AND role = 'regular'");
        $stmt->execute([password_hash($_POST['password'], PASSWORD_BCRYPT), $_POST['user_id']]);
        $success = "Password reset successfully.";
        $action = 'list';
    }
}

// Handle GET
if (isset($_GET['do']) && isset($_GET['uid'])) {
    $status = ($_GET['do'] == 'archive') ? 'archived' : 'active';
    $db->prepare("UPDATE users SET status = ? WHERE id = ? AND role = 'regular'")->execute([$status, $_GET['uid']]);
    $success = "User status updated.";
}

$base_path = '../';
require_once '../includes/header.php';

if ($action == 'add'): ?>
    <div class="card" style="max-width: 500px; margin: 0 auto;">
        <h2>Add Regular User</h2>
        <?php if($error): ?><div class="alert alert-danger"><?php echo e($error); ?></div><?php endif; ?>
        <form method="POST">
            <input type="hidden" name="csrf_token" value="<?php echo generateToken(); ?>">
            <input type="hidden" name="add_user" value="1">
            <div class="form-group"><label>Username</label><input type="text" name="username" class="form-control" required></div>
            <div class="form-group"><label>Password</label><input type="password" name="password" class="form-control" required minlength="6"></div>
            <button type="submit" class="btn btn-primary">Create User</button>
            <a href="users.php" style="display:block; text-align:center; margin-top:1rem;">Cancel</a>
        </form>
    </div>
<?php elseif ($action == 'reset'): ?>
    <div class="card" style="max-width: 500px; margin: 0 auto;">
        <h2>Reset User Password</h2>
        <form method="POST">
            <input type="hidden" name="csrf_token" value="<?php echo generateToken(); ?>">
            <input type="hidden" name="reset_pwd" value="1">
            <input type="hidden" name="user_id" value="<?php echo $_GET['uid']; ?>">
            <div class="form-group"><label>New Password</label><input type="password" name="password" class="form-control" required minlength="6"></div>
            <button type="submit" class="btn btn-primary">Update Password</button>
            <a href="users.php" style="display:block; text-align:center; margin-top:1rem;">Cancel</a>
        </form>
    </div>
<?php else: 
    $users = $db->query("SELECT * FROM users WHERE role='regular' ORDER BY status ASC, username ASC")->fetchAll();
?>
    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 2rem;">
        <h2>User Management</h2>
        <a href="users.php?action=add" class="btn btn-primary" style="width: auto;">Add User</a>
    </div>
    <?php if($success): ?><div class="alert alert-approved" style="background:#dcfce7; color:#166534; padding:1rem; border-radius:0.5rem; margin-bottom:1rem;"><?php echo e($success); ?></div><?php endif; ?>
    <div class="table-container">
        <table>
            <thead><tr><th>Username</th><th>Status</th><th>Actions</th></tr></thead>
            <tbody>
                <?php foreach($users as $u): ?>
                    <tr>
                        <td><strong><?php echo e($u['username']); ?></strong></td>
                        <td><span class="badge badge-<?php echo $u['status']=='active'?'approved':'archived'; ?>"><?php echo ucfirst($u['status']); ?></span></td>
                        <td>
                            <a href="users.php?action=reset&uid=<?php echo $u['id']; ?>">Reset</a> | 
                            <?php if($u['status']=='active'): ?><a href="users.php?do=archive&uid=<?php echo $u['id']; ?>" style="color:var(--danger)">Archive</a>
                            <?php else: ?><a href="users.php?do=restore&uid=<?php echo $u['id']; ?>" style="color:var(--success)">Restore</a><?php endif; ?>
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
