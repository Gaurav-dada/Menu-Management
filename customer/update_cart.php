<?php
session_start();
require_once "../config/database.php";

$id = (int)($_GET["id"] ?? 0);
$action = $_GET["action"] ?? "";

if (isset($_SESSION["cart"][$id])) {
    if ($action === "plus") {
        $_SESSION["cart"][$id] = min(99, $_SESSION["cart"][$id] + 1);
    } elseif ($action === "minus") {
        $_SESSION["cart"][$id]--;
        if ($_SESSION["cart"][$id] <= 0) unset($_SESSION["cart"][$id]);
    } elseif ($action === "remove") {
        unset($_SESSION["cart"][$id]);
    }
}

header("Location: cart.php");
exit;
?>
