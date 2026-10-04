<?php
session_start();
require_once "../config/database.php";

if (!isset($_SESSION["cart"])) $_SESSION["cart"] = [];
$cart = $_SESSION["cart"];
$items = [];
$total = 0;

if ($cart) {
    $ids = array_map('intval', array_keys($cart));
    $idList = implode(",", $ids);
    // only items that still exist and are available
    $result = mysqli_query($conn, "SELECT id,name,price FROM menu_items WHERE id IN ($idList) AND status='available'");
    $validCart = [];
    while ($row = mysqli_fetch_assoc($result)) {
        $row["quantity"] = $cart[$row["id"]];
        $row["subtotal"] = $row["price"] * $row["quantity"];
        $total += $row["subtotal"];
        $items[] = $row;
        $validCart[$row["id"]] = $row["quantity"];
    }
    $_SESSION["cart"] = $validCart; // drop deleted/unavailable items
}
$page_title = "Cart";
include "../includes/header.php";
include "../includes/navbar.php";
?>
<div class="container">
<h1>Shopping Cart</h1>
<?php if (!$items): ?>
<div class="card" style="margin-top:20px"><p>Your cart is empty.</p><a class="btn" href="../index.php">Browse Menu</a></div>
<?php else: ?>
<div class="card" style="margin-top:20px">
<?php foreach ($items as $item): ?>
<div class="cart-row">
<div><strong><?= htmlspecialchars($item["name"]) ?></strong><br>Rs. <?= number_format($item["price"], 2) ?></div>
<div>Qty: <?= $item["quantity"] ?></div>
<div>Rs. <?= number_format($item["subtotal"], 2) ?></div>
<div class="actions">
<a class="btn" href="update_cart.php?id=<?= $item['id'] ?>&action=plus">+</a>
<a class="btn btn-secondary" href="update_cart.php?id=<?= $item['id'] ?>&action=minus">-</a>
</div>
<a class="btn btn-danger" href="update_cart.php?id=<?= $item['id'] ?>&action=remove">Remove</a>
</div>
<?php endforeach; ?>
<h2 style="margin-top:20px">Total: Rs. <?= number_format($total, 2) ?></h2>
<div style="margin-top:15px"><a class="btn btn-success" href="checkout.php">Checkout</a></div>
</div>
<?php endif; ?>
</div>
<?php include "../includes/footer.php"; ?>
