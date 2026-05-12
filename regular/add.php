<?php
// regular/add.php
require_once '../config/core.php';
checkAccess('regular');

$database = new Database();
$db = $database->getConnection();
$success = "";

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    verifyToken($_POST['csrf_token']);
    
    $pid = bin2hex(random_bytes(16));
    $sql = "INSERT INTO items (public_id, name, category, size, price, quantity, status, added_by) VALUES (?,?,?,?,?,?,'pending',?)";
    $db->prepare($sql)->execute([
        $pid, $_POST['name'], $_POST['category'], $_POST['size'], $_POST['price'], $_POST['quantity'], $_SESSION['user_id']
    ]);
    $success = "Item submitted for admin approval.";
}

$base_path = '../';
require_once '../includes/header.php';
?>

<div class="card" style="max-width: 600px; margin: 0 auto;">
    <h2>Submit New Item</h2>
    <?php if($success): ?><div class="alert alert-approved" style="background:#dcfce7; color:#166534; padding:1rem; border-radius:0.5rem; margin-bottom:1rem;"><?php echo e($success); ?></div><?php endif; ?>
    
    <form method="POST">
        <input type="hidden" name="csrf_token" value="<?php echo generateToken(); ?>">
        <div class="form-group"><label>Item Name</label><input type="text" name="name" class="form-control" required></div>
        <div class="form-group"><label>Category</label><input type="text" name="category" class="form-control" placeholder="e.g. Jeans"></div>
        <div style="display:grid; grid-template-columns:1fr 1fr; gap:1rem;">
            <div class="form-group"><label>Price ($)</label><input type="number" step="0.01" name="price" class="form-control" required></div>
            <div class="form-group"><label>Quantity</label><input type="number" name="quantity" class="form-control" required></div>
        </div>
        <div class="form-group"><label>Size</label><select name="size" class="form-control"><option value="S">Small</option><option value="M">Medium</option><option value="L">Large</option></select></div>
        <button type="submit" class="btn btn-primary">Submit for Approval</button>
        <a href="dashboard.php" style="display:block; text-align:center; margin-top:1rem;">Back to Dashboard</a>
    </form>
</div>

    </div> <!-- end container -->
</body>
</html>
