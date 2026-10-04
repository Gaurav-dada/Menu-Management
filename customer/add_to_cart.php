<?php
session_start();
require_once "../config/database.php";

$id = (int)($_GET["id"] ?? 0);

$stmt = mysqli_prepare($conn, "SELECT id FROM menu_items WHERE id=? AND status='available'");
mysqli_stmt_bind_param($stmt, "i", $id);
mysqli_stmt_execute($stmt);
$item = mysqli_fetch_assoc(mysqli_stmt_get_result($stmt));

if ($item) {
    if (!isset($_SESSION["cart"])) $_SESSION["cart"] = [];
    $_SESSION["cart"][$id] = min(99, ($_SESSION["cart"][$id] ?? 0) + 1);
}
header("Location: cart.php");
exit;
?>
