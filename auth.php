<?php
// auth.php
require_once 'config/core.php';

// Handle Logout
if (isset($_GET['action']) && $_GET['action'] == 'logout') {
    $_SESSION = array();
    session_destroy();
    redirect('auth.php');
}

// Already logged in?
if (isLoggedIn() && !isset($_GET['action'])) {
    redirect('index.php');
}

$error = "";

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    verifyToken($_POST['csrf_token']);
    
    $database = new Database();
    $db = $database->getConnection();

    $stmt = $db->prepare("SELECT * FROM users WHERE username = ? LIMIT 1");
    $stmt->execute([trim($_POST['username'])]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($user && password_verify($_POST['password'], $user['password'])) {
        if ($user['status'] == 'archived') {
            $error = "This account is archived.";
        } else {
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['username'] = $user['username'];
            $_SESSION['role'] = $user['role'];
            redirect('index.php'); // index.php will handle role-based redirection
        }
    } else {
        $error = "Invalid username or password.";
    }
}
?>
<!DOCTYPE html>
<html lang="en" data-theme="<?php echo $theme; ?>">
<head>
    <meta charset="UTF-8"><title>Login - Inventory System</title>
    <link rel="stylesheet" href="assets/css/style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>
<body style="display: flex; align-items: center; justify-content: center; min-height: 100vh;">
    <div style="position: fixed; top: 2rem; right: 2rem; z-index: 1000;">
        <a href="?theme=<?php echo ($theme == 'dark') ? 'light' : 'dark'; ?>" class="theme-toggle" title="Toggle Light/Dark Mode" style="width: 3.5rem; height: 3.5rem; font-size: 1.25rem; box-shadow: var(--shadow-lg); background: var(--surface); border: 1px solid var(--border);">
            <i class="fas fa-<?php echo ($theme == 'dark') ? 'sun' : 'moon'; ?>"></i>
        </a>
    </div>
    <div class="card" style="width: 100%; max-width: 400px; padding: 2.5rem;">
        <h2 style="text-align: center; color: var(--primary); margin-bottom: 2rem;">Inventory Login</h2>
        <?php if($error): ?><div class="alert alert-danger" style="background:#fee2e2; color:#ef4444; padding:0.75rem; border-radius:0.5rem; margin-bottom:1rem; border:1px solid #fecaca;"><?php echo e($error); ?></div><?php endif; ?>
        <form method="POST">
            <input type="hidden" name="csrf_token" value="<?php echo generateToken(); ?>">
            <div class="form-group">
                <label>Username</label>
                <input type="text" name="username" class="form-control" required autofocus>
            </div>
            <div class="form-group">
                <label>Password</label>
                <input type="password" name="password" class="form-control" required>
            </div>
            <button type="submit" class="btn btn-primary" style="width: 100%; margin-top: 1rem;">Sign In</button>
        </form>
    </div>
</body>
</html>
