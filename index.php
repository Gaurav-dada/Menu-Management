<?php
session_start();
require_once "config/database.php";

$category = isset($_GET["category"]) ? (int)$_GET["category"] : 0;

$categories = mysqli_query($conn, "SELECT * FROM categories ORDER BY name");

if ($category > 0) {
    $stmt = mysqli_prepare($conn, "SELECT m.*, c.name category_name FROM menu_items m JOIN categories c ON m.category_id=c.id WHERE m.status='available' AND m.category_id=? ORDER BY m.id DESC");
    mysqli_stmt_bind_param($stmt, "i", $category);
    mysqli_stmt_execute($stmt);
    $items = mysqli_stmt_get_result($stmt);
} else {
    $items = mysqli_query($conn, "SELECT m.*, c.name category_name FROM menu_items m JOIN categories c ON m.category_id=c.id WHERE m.status='available' ORDER BY m.id DESC");
}
$page_title="Menu";
include "includes/header.php";
include "includes/navbar.php";
?>
<div class="container">
<section class="hero">
<h1>Our Menu</h1>
<p>Choose your favorite food and place your order.</p>
<div class="search-box"><input id="searchMenu" placeholder="Search menu item..."></div>
<div class="actions">
<a class="btn btn-secondary" href="index.php">All</a>
<?php while($cat=mysqli_fetch_assoc($categories)): ?>
<a class="btn" href="?category=<?= $cat['id'] ?>"><?= htmlspecialchars($cat['name']) ?></a>
<?php endwhile; ?>
</div>
</section>

<div class="grid">
<?php while($item=mysqli_fetch_assoc($items)): ?>
<div class="card menu-card">
<?php if($item["image"]): ?><img src="assets/images/<?= htmlspecialchars($item["image"]) ?>" alt=""><?php else: ?><div style="height:180px;background:#e5e7eb;border-radius:9px;display:grid;place-items:center">No Image</div><?php endif; ?>
<span class="badge"><?= htmlspecialchars($item["category_name"]) ?></span>
<h3><?= htmlspecialchars($item["name"]) ?></h3>
<p class="muted"><?= htmlspecialchars($item["description"]) ?></p>
<div class="price">Rs. <?= number_format($item["price"],2) ?></div>
<a class="btn" href="customer/add_to_cart.php?id=<?= $item['id'] ?>">Add to Cart</a>
</div>
<?php endwhile; ?>
</div>
</div>
<?php include "includes/footer.php"; ?>
