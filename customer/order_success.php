<?php
session_start();
require_once "../config/database.php";
if (!isset($_SESSION["user_id"])) { header("Location: ../login.php"); exit; }

$id = (int)($_GET["id"] ?? 0);

// only show orders that belong to the logged-in customer
$stmt = mysqli_prepare($conn, "SELECT id FROM orders WHERE id=? AND user_id=?");
mysqli_stmt_bind_param($stmt, "ii", $id, $_SESSION["user_id"]);
mysqli_stmt_execute($stmt);
if (!mysqli_fetch_assoc(mysqli_stmt_get_result($stmt))) {
    header("Location: orders.php"); exit;
}

$page_title = "Order Success";
include "../includes/header.php";
include "../includes/navbar.php";
?>
<div class="container"><div class="form-card">
<h2>Order Placed Successfully</h2>
<p>Your order #<?= $id ?> has been received.</p>
<a class="btn" href="orders.php">View My Orders</a>
</div></div>
<?php include "../includes/footer.php"; ?>
