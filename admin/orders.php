<?php
require_once "../config/auth.php"; require_admin();
require_once "../config/database.php";
require_once "../config/helpers.php";

$allowed = ["pending", "confirmed", "preparing", "completed", "cancelled"];

/* ---------- Update order status ---------- */
if ($_SERVER["REQUEST_METHOD"] === "POST" && isset($_POST["update_status"])) {
    $id     = (int)($_POST["id"] ?? 0);
    $status = $_POST["new_status"] ?? "";

    if (!csrf_valid()) {
        flash_set("error", "Invalid request. Please reload the page and try again.");
    } elseif ($id <= 0 || !in_array($status, $allowed, true)) {
        flash_set("error", "Invalid order or status.");
    } else {
        try {
            $stmt = mysqli_prepare($conn, "SELECT status FROM orders WHERE id=?");
            mysqli_stmt_bind_param($stmt, "i", $id);
            mysqli_stmt_execute($stmt);
            $current = mysqli_fetch_assoc(mysqli_stmt_get_result($stmt));

            if (!$current) {
                flash_set("error", "Order #$id not found.");
            } elseif ($current["status"] === $status) {
                flash_set("error", "Order #$id is already " . ucfirst($status) . ".");
            } else {
                $stmt = mysqli_prepare($conn, "UPDATE orders SET status=? WHERE id=?");
                mysqli_stmt_bind_param($stmt, "si", $status, $id);
                mysqli_stmt_execute($stmt);
                flash_set("success", "Order #$id status updated to " . ucfirst($status) . ".");
            }
        } catch (mysqli_sql_exception $e) {
            flash_set("error", "Could not update order #$id. Please try again.");
        }
    }

    // Redirect keeps the current filter and prevents duplicate submit on refresh
    $back = (isset($_POST["filter"]) && in_array($_POST["filter"], $allowed, true)) ? "?status=" . $_POST["filter"] : "";
    header("Location: orders.php" . $back);
    exit;
}

/* ---------- List orders (optional status filter) ---------- */
$filter = (isset($_GET["status"]) && in_array($_GET["status"], $allowed, true)) ? $_GET["status"] : "";

$sql = "SELECT o.*, u.name AS user_name, u.email,
        (SELECT GROUP_CONCAT(CONCAT(m.name, ' x ', oi.quantity) SEPARATOR ', ')
           FROM order_items oi JOIN menu_items m ON m.id = oi.menu_item_id
          WHERE oi.order_id = o.id) AS items
        FROM orders o JOIN users u ON o.user_id = u.id";
if ($filter !== "") {
    $stmt = mysqli_prepare($conn, $sql . " WHERE o.status=? ORDER BY o.id DESC");
    mysqli_stmt_bind_param($stmt, "s", $filter);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);
} else {
    $result = mysqli_query($conn, $sql . " ORDER BY o.id DESC");
}

$page_title = "Orders";
include "../includes/header.php";
include "../includes/navbar.php";
?>
<div class="container">
<h1>Order Management</h1>
<?php flash_show(); ?>

<div class="actions" style="margin-top:15px">
    <a class="btn <?= $filter === "" ? "" : "btn-secondary" ?>" href="orders.php">All</a>
    <?php foreach ($allowed as $s): ?>
        <a class="btn <?= $filter === $s ? "" : "btn-secondary" ?>" href="?status=<?= $s ?>"><?= ucfirst($s) ?></a>
    <?php endforeach; ?>
</div>

<div class="table-wrap"><table>
<tr><th>ID</th><th>Customer</th><th>Items</th><th>Total</th><th>Status</th><th>Date</th><th>Update</th></tr>
<?php if (mysqli_num_rows($result) === 0): ?>
<tr><td colspan="7">No orders found.</td></tr>
<?php endif; ?>
<?php while ($o = mysqli_fetch_assoc($result)): ?>
<tr>
    <td>#<?= $o["id"] ?></td>
    <td><?= htmlspecialchars($o["user_name"]) ?><br><span class="muted"><?= htmlspecialchars($o["email"]) ?></span></td>
    <td><?= htmlspecialchars($o["items"] ?? "-") ?></td>
    <td>Rs. <?= number_format($o["total_amount"], 2) ?></td>
    <td><span class="badge status-<?= htmlspecialchars($o["status"]) ?>"><?= htmlspecialchars(ucfirst($o["status"])) ?></span></td>
    <td><?= $o["created_at"] ?></td>
    <td>
        <form method="POST" class="actions">
            <?= csrf_field() ?>
            <input type="hidden" name="id" value="<?= $o["id"] ?>">
            <input type="hidden" name="filter" value="<?= htmlspecialchars($filter) ?>">
            <select name="new_status">
                <?php foreach ($allowed as $s): ?>
                    <option value="<?= $s ?>" <?= $o["status"] === $s ? "selected" : "" ?>><?= ucfirst($s) ?></option>
                <?php endforeach; ?>
            </select>
            <button type="submit" name="update_status" value="1">Update</button>
        </form>
    </td>
</tr>
<?php endwhile; ?>
</table></div></div>
<?php include "../includes/footer.php"; ?>
