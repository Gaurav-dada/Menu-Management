<?php
require_once "../config/auth.php"; require_admin();
require_once "../config/database.php";

$sales = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COALESCE(SUM(total_amount),0) total FROM orders WHERE status<>'cancelled'"))["total"];
$orderCount = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) total FROM orders WHERE status<>'cancelled'"))["total"];
$byStatus = mysqli_query($conn, "SELECT status, COUNT(*) total FROM orders GROUP BY status ORDER BY total DESC");
$popular = mysqli_query($conn, "SELECT m.name, SUM(oi.quantity) quantity FROM order_items oi JOIN menu_items m ON oi.menu_item_id=m.id JOIN orders o ON oi.order_id=o.id WHERE o.status<>'cancelled' GROUP BY m.id, m.name ORDER BY quantity DESC LIMIT 10");

$page_title = "Reports"; include "../includes/header.php"; include "../includes/navbar.php";
?>
<div class="container"><h1>Reports</h1><div class="dashboard-grid">
<div class="stat"><p>Total Orders (excl. cancelled)</p><h2><?= $orderCount ?></h2></div>
<div class="stat"><p>Total Sales</p><h2>Rs. <?= number_format($sales, 2) ?></h2></div>
</div>
<h2>Orders by Status</h2><div class="table-wrap"><table><tr><th>Status</th><th>Orders</th></tr>
<?php while ($s = mysqli_fetch_assoc($byStatus)): ?><tr><td><span class="badge status-<?= htmlspecialchars($s["status"]) ?>"><?= htmlspecialchars(ucfirst($s["status"])) ?></span></td><td><?= $s["total"] ?></td></tr><?php endwhile; ?>
</table></div>
<h2 style="margin-top:25px">Popular Menu Items</h2><div class="table-wrap"><table><tr><th>Item</th><th>Quantity Sold</th></tr>
<?php while ($p = mysqli_fetch_assoc($popular)): ?><tr><td><?= htmlspecialchars($p["name"]) ?></td><td><?= $p["quantity"] ?></td></tr><?php endwhile; ?>
</table></div></div>
<?php include "../includes/footer.php"; ?>
