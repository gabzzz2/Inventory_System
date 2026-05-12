<?php
// regular/inventory.php
require_once '../config/core.php';
checkAccess('regular');

$database = new Database();
$db = $database->getConnection();

$q = isset($_GET['q']) ? $_GET['q'] : '';
$sql = "SELECT * FROM items WHERE status='approved'";
if ($q) $sql .= " AND (name LIKE ? OR category LIKE ?)";
$sql .= " ORDER BY created_at DESC";

$stmt = $db->prepare($sql);
if ($q) {
    $search = "%$q%";
    $stmt->execute([$search, $search]);
} else {
    $stmt->execute();
}
$items = $stmt->fetchAll(PDO::FETCH_ASSOC);

$base_path = '../';
require_once '../includes/header.php';
?>

<div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:2rem;">
    <h2>Clothing Inventory</h2>
    <a href="add.php" class="btn btn-primary" style="width: auto;">Add New Item</a>
</div>

<div class="card" style="margin-bottom: 2rem;">
    <form method="GET" style="display:flex; gap:1rem;">
        <input type="text" name="q" class="form-control" placeholder="Search clothes..." value="<?php echo e($q); ?>">
        <button type="submit" class="btn btn-primary" style="width:auto;">Search</button>
    </form>
</div>

<div class="table-container">
    <table>
        <thead><tr><th>Item</th><th>Category</th><th>Size</th><th>Price</th><th>Stock</th></tr></thead>
        <tbody>
            <?php foreach($items as $i): ?>
                <tr>
                    <td><strong><?php echo e($i['name']); ?></strong></td>
                    <td><?php echo e($i['category']); ?></td>
                    <td><?php echo e($i['size']); ?></td>
                    <td>$<?php echo number_format($i['price'], 2); ?></td>
                    <td><?php echo $i['quantity']; ?></td>
                </tr>
            <?php endforeach; ?>
            <?php if(empty($items)): ?><tr><td colspan="5" style="text-align:center;">No items found.</td></tr><?php endif; ?>
        </tbody>
    </table>
</div>

    </div> <!-- end container -->
</body>
</html>
