<?php
// admin/inventory.php
require_once '../config/core.php';
checkAccess('admin');

$database = new Database();
$db = $database->getConnection();

$action = isset($_GET['action']) ? $_GET['action'] : 'list';
$success = ""; $error = "";

// POST Actions
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    verifyToken($_POST['csrf_token']);
    
    if (isset($_POST['save_item'])) {
        $pid = isset($_POST['public_id']) ? $_POST['public_id'] : bin2hex(random_bytes(16));
        if (isset($_POST['public_id'])) { // Update
            if ($_POST['hash'] !== hashID($pid)) die("Security Violation");
            $sql = "UPDATE items SET name=?, category=?, size=?, price=?, quantity=? WHERE public_id=?";
            $db->prepare($sql)->execute([$_POST['name'], $_POST['category'], $_POST['size'], $_POST['price'], $_POST['quantity'], $pid]);
            $success = "Item updated.";
        } else { // Create
            $sql = "INSERT INTO items (public_id, name, category, size, price, quantity, status, added_by) VALUES (?,?,?,?,?,?,'approved',?)";
            $db->prepare($sql)->execute([$pid, $_POST['name'], $_POST['category'], $_POST['size'], $_POST['price'], $_POST['quantity'], $_SESSION['user_id']]);
            $success = "Item added.";
        }
        $action = 'list';
    }
}

// GET Actions
if (isset($_GET['do']) && isset($_GET['id'])) {
    if ($_GET['hash'] !== hashID($_GET['id'])) die("Security Violation");
    if ($_GET['do'] == 'approve') $db->prepare("UPDATE items SET status='approved', approved_by=? WHERE public_id=?")->execute([$_SESSION['user_id'], $_GET['id']]);
    if ($_GET['do'] == 'archive') $db->prepare("UPDATE items SET status='archived' WHERE public_id=?")->execute([$_GET['id']]);
    if ($_GET['do'] == 'restore') $db->prepare("UPDATE items SET status='approved' WHERE public_id=?")->execute([$_GET['id']]);
    $success = "Action completed.";
}

$base_path = '../';
require_once '../includes/header.php';

if ($action == 'add' || $action == 'edit'):
    $item = ($action == 'edit') ? $db->query("SELECT * FROM items WHERE public_id='".$_GET['id']."'")->fetch() : null;
?>
    <div class="card" style="max-width:600px; margin:0 auto;">
        <h2><?php echo $item ? 'Edit' : 'Add'; ?> Item</h2>
        <form method="POST">
            <input type="hidden" name="csrf_token" value="<?php echo generateToken(); ?>">
            <input type="hidden" name="save_item" value="1">
            <?php if($item): ?>
                <input type="hidden" name="public_id" value="<?php echo $item['public_id']; ?>">
                <input type="hidden" name="hash" value="<?php echo hashID($item['public_id']); ?>">
            <?php endif; ?>
            <div class="form-group"><label>Name</label><input type="text" name="name" class="form-control" required value="<?php echo $item ? e($item['name']) : ''; ?>"></div>
            <div class="form-group"><label>Category</label><input type="text" name="category" class="form-control" value="<?php echo $item ? e($item['category']) : ''; ?>"></div>
            <div style="display:grid; grid-template-columns:1fr 1fr; gap:1rem;">
                <div class="form-group"><label>Price</label><input type="number" step="0.01" name="price" class="form-control" required value="<?php echo $item ? e($item['price']) : ''; ?>"></div>
                <div class="form-group"><label>Quantity</label><input type="number" name="quantity" class="form-control" required value="<?php echo $item ? e($item['quantity']) : ''; ?>"></div>
            </div>
            <div class="form-group"><label>Size</label><select name="size" class="form-control">
                <option value="S" <?php echo ($item && $item['size']=='S')?'selected':''; ?>>S</option>
                <option value="M" <?php echo ($item && $item['size']=='M')?'selected':''; ?>>M</option>
                <option value="L" <?php echo ($item && $item['size']=='L')?'selected':''; ?>>L</option>
            </select></div>
            <button type="submit" class="btn btn-primary">Save Item</button>
            <a href="inventory.php" style="display:block; text-align:center; margin-top:1rem;">Cancel</a>
        </form>
    </div>
<?php else: 
    $filter = isset($_GET['filter']) ? $_GET['filter'] : 'approved';
    $items = $db->query("SELECT * FROM items WHERE status='$filter' ORDER BY created_at DESC")->fetchAll();
?>
    <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:2rem;">
        <h2>Inventory (<?php echo ucfirst($filter); ?>)</h2>
        <div>
            <a href="inventory.php?filter=approved" class="btn" style="width:auto; background:#e2e8f0; color:#475569;">Approved</a>
            <a href="inventory.php?filter=pending" class="btn" style="width:auto; background:#fef3c7; color:#92400e;">Pending</a>
            <a href="inventory.php?filter=archived" class="btn" style="width:auto; background:#f1f5f9; color:#475569;">Archived</a>
            <a href="inventory.php?action=add" class="btn btn-primary" style="width:auto; margin-left:1rem;">Add New</a>
        </div>
    </div>
    <?php if($success): ?><div class="alert alert-approved" style="background:#dcfce7; color:#166534; padding:1rem; border-radius:0.5rem; margin-bottom:1rem;"><?php echo e($success); ?></div><?php endif; ?>
    <div class="table-container">
        <table>
            <thead><tr><th>Item</th><th>Price</th><th>Stock</th><th>Actions</th></tr></thead>
            <tbody>
                <?php foreach($items as $i): 
                    $hash = hashID($i['public_id']);
                ?>
                    <tr>
                        <td><strong><?php echo e($i['name']); ?></strong><br><small><?php echo e($i['category']); ?> (<?php echo e($i['size']); ?>)</small></td>
                        <td>$<?php echo number_format($i['price'],2); ?></td>
                        <td><?php echo $i['quantity']; ?></td>
                        <td>
                            <a href="inventory.php?action=edit&id=<?php echo $i['public_id']; ?>">Edit</a> | 
                            <?php if($i['status']=='pending'): ?>
                                <a href="inventory.php?do=approve&id=<?php echo $i['public_id']; ?>&hash=<?php echo $hash; ?>" style="color:var(--success)">Approve</a> |
                            <?php endif; ?>
                            <?php if($i['status']!='archived'): ?>
                                <a href="inventory.php?do=archive&id=<?php echo $i['public_id']; ?>&hash=<?php echo $hash; ?>" style="color:var(--danger)">Archive</a>
                            <?php else: ?>
                                <a href="inventory.php?do=restore&id=<?php echo $i['public_id']; ?>&hash=<?php echo $hash; ?>" style="color:var(--success)">Restore</a>
                            <?php endif; ?>
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
