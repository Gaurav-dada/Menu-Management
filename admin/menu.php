<?php
require_once "../config/auth.php"; require_admin();
require_once "../config/database.php";
require_once "../config/helpers.php";
$result = mysqli_query($conn, "SELECT m.*,c.name category_name FROM menu_items m JOIN categories c ON m.category_id=c.id ORDER BY m.id DESC");
$page_title = "Menu Management"; include "../includes/header.php"; include "../includes/navbar.php";
?>
<div class="container"><div class="actions"><h1 style="margin-right:auto">Menu Management</h1><a class="btn" href="menu_add.php">Add Menu Item</a></div>
<?php flash_show(); ?>
<div class="table-wrap"><table><tr><th>ID</th><th>Name</th><th>Category</th><th>Price</th><th>Status</th><th>Action</th></tr>
<?php while ($m = mysqli_fetch_assoc($result)): ?><tr>
<td><?= $m["id"] ?></td><td><?= htmlspecialchars($m["name"]) ?></td><td><?= htmlspecialchars($m["category_name"]) ?></td>
<td>Rs. <?= number_format($m["price"], 2) ?></td><td><span class="badge status-<?= $m["status"] ?>"><?= ucfirst($m["status"]) ?></span></td>
<td class="actions"><a class="btn" href="menu_edit.php?id=<?= $m["id"] ?>">Edit</a><form method="POST" action="menu_delete.php" class="inline-form"><?= csrf_field() ?><input type="hidden" name="id" value="<?= $m["id"] ?>"><button type="submit" class="btn-danger confirm-delete">Delete</button></form></td>
</tr><?php endwhile; ?></table></div></div>
<?php include "../includes/footer.php"; ?>
