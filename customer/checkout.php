<?php
session_start();
require_once "../config/database.php";
require_once "../config/helpers.php";

if (!isset($_SESSION["user_id"])) {
    header("Location: ../login.php");
    exit;
}
if (empty($_SESSION["cart"])) {
    header("Location: cart.php");
    exit;
}

$ids = array_map('intval', array_keys($_SESSION["cart"]));
$idList = implode(",", $ids);
$result = mysqli_query($conn, "SELECT id,name,price FROM menu_items WHERE id IN ($idList) AND status='available'");
$total = 0; $items = [];
while ($row = mysqli_fetch_assoc($result)) {
    $qty = max(1, min(99, (int)$_SESSION["cart"][$row["id"]]));
    $total += $row["price"] * $qty;
    $row["quantity"] = $qty;
    $items[] = $row;
}

// everything in the cart was deleted/unavailable
if (!$items) {
    $_SESSION["cart"] = [];
    header("Location: cart.php");
    exit;
}

$error = "";
if ($_SERVER["REQUEST_METHOD"] === "POST" && !csrf_valid()) {
    $error = "Invalid request. Please reload the page and try again.";
} elseif ($_SERVER["REQUEST_METHOD"] === "POST") {
    $user_id = $_SESSION["user_id"];
    try {
        // order + items saved together, or not at all
        mysqli_begin_transaction($conn);

        $stmt = mysqli_prepare($conn, "INSERT INTO orders(user_id,total_amount) VALUES(?,?)");
        mysqli_stmt_bind_param($stmt, "id", $user_id, $total);
        mysqli_stmt_execute($stmt);
        $order_id = mysqli_insert_id($conn);

        $itemStmt = mysqli_prepare($conn, "INSERT INTO order_items(order_id,menu_item_id,quantity,price) VALUES(?,?,?,?)");
        foreach ($items as $item) {
            mysqli_stmt_bind_param($itemStmt, "iiid", $order_id, $item["id"], $item["quantity"], $item["price"]);
            mysqli_stmt_execute($itemStmt);
        }
        mysqli_commit($conn);

        $_SESSION["cart"] = [];
        header("Location: order_success.php?id=" . $order_id);
        exit;
    } catch (Throwable $e) {
        mysqli_rollback($conn);
        $error = "Could not place your order. Please try again.";
    }
}
$page_title = "Checkout";
include "../includes/header.php";
include "../includes/navbar.php";
?>
<div class="container"><div class="form-card">
<h2>Checkout</h2>
<?php if ($error): ?><div class="alert error"><?= htmlspecialchars($error) ?></div><?php endif; ?>
<?php foreach ($items as $item): ?>
<p><?= htmlspecialchars($item["name"]) ?> &times; <?= $item["quantity"] ?> = Rs. <?= number_format($item["price"] * $item["quantity"], 2) ?></p>
<?php endforeach; ?>
<hr style="margin:15px 0">
<h2>Total: Rs. <?= number_format($total, 2) ?></h2>
<form method="POST" style="margin-top:20px"><?= csrf_field() ?>
<button class="btn-success">Place Order</button>
</form>
</div></div>
<?php include "../includes/footer.php"; ?>
