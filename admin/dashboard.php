<?php
require_once "../config/auth.php";
require_admin();
require_once "../config/database.php";

$users = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) total FROM users"))["total"];
$categories = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) total FROM categories"))["total"];
$menus = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) total FROM menu_items"))["total"];
$orders = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) total FROM orders"))["total"];
$pending = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) total FROM orders WHERE status='pending'"))["total"];
$sales = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COALESCE(SUM(total_amount),0) total FROM orders WHERE status<>'cancelled'"))["total"];

$page_title = "Admin Dashboard";
include "../includes/header.php";
include "../includes/navbar.php";
?>
<div class="container">
<h1>Admin Dashboard</h1>
<div class="dashboard-grid">
<div class="stat"><p>Users</p><h2><?= $users ?></h2></div>
<div class="stat"><p>Categories</p><h2><?= $categories ?></h2></div>
<div class="stat"><p>Menu Items</p><h2><?= $menus ?></h2></div>
<div class="stat"><p>Orders</p><h2><?= $orders ?></h2></div>
<div class="stat"><p>Pending Orders</p><h2><?= $pending ?></h2></div>
<div class="stat"><p>Total Sales</p><h2>Rs. <?= number_format($sales, 2) ?></h2></div>
</div>
<div class="actions">
<a class="btn" href="categories.php">Categories</a>
<a class="btn" href="menu.php">Menu</a>
<a class="btn" href="orders.php">Orders</a>
<a class="btn" href="reports.php">Reports</a>
</div>
</div>
<?php include "../includes/footer.php"; ?>
