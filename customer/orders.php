<?php
session_start();
require_once "../config/database.php";
if (!isset($_SESSION["user_id"])) { header("Location: ../login.php"); exit; }
$user_id = $_SESSION["user_id"];
$stmt = mysqli_prepare($conn, "SELECT o.*,
    (SELECT GROUP_CONCAT(CONCAT(m.name, ' x ', oi.quantity) SEPARATOR ', ')
       FROM order_items oi JOIN menu_items m ON m.id = oi.menu_item_id
      WHERE oi.order_id = o.id) AS items
    FROM orders o WHERE o.user_id=? ORDER BY o.id DESC");
mysqli_stmt_bind_param($stmt, "i", $user_id);
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);
$page_title = "My Orders";
include "../includes/header.php";
include "../includes/navbar.php";
?>
<div class="container"><h1>My Orders</h1><div class="table-wrap">
<table><tr><th>ID</th><th>Items</th><th>Total</th><th>Status</th><th>Date</th></tr>
<?php if (mysqli_num_rows($result) === 0): ?><tr><td colspan="5">You have no orders yet.</td></tr><?php endif; ?>
<?php while ($o = mysqli_fetch_assoc($result)): ?>
<tr><td>#<?= $o["id"] ?></td><td><?= htmlspecialchars($o["items"] ?? "-") ?></td><td>Rs. <?= number_format($o["total_amount"], 2) ?></td><td><span class="badge status-<?= htmlspecialchars($o["status"]) ?>"><?= htmlspecialchars(ucfirst($o["status"])) ?></span></td><td><?= $o["created_at"] ?></td></tr>
<?php endwhile; ?>
</table></div></div>
<?php include "../includes/footer.php"; ?>
