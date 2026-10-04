<?php
require_once "../config/auth.php"; require_admin();
require_once "../config/database.php";
require_once "../config/helpers.php";

// Delete only works from the Delete button (POST + CSRF token)
if ($_SERVER["REQUEST_METHOD"] !== "POST" || !csrf_valid()) {
    flash_set("error", "Invalid request. Please use the Delete button.");
    header("Location: menu.php"); exit;
}

$id = (int)($_POST["id"] ?? 0);

try {
    $stmt = mysqli_prepare($conn, "SELECT id,image FROM menu_items WHERE id=?");
    mysqli_stmt_bind_param($stmt, "i", $id);
    mysqli_stmt_execute($stmt);
    $row = mysqli_fetch_assoc(mysqli_stmt_get_result($stmt));

    if (!$row) {
        flash_set("error", "Menu item not found.");
    } else {
        // If the item was ever ordered, deleting it would erase order history and
        // break reports (order_items has ON DELETE CASCADE). Mark it unavailable instead.
        $stmt = mysqli_prepare($conn, "SELECT COUNT(*) AS total FROM order_items WHERE menu_item_id=?");
        mysqli_stmt_bind_param($stmt, "i", $id);
        mysqli_stmt_execute($stmt);
        $used = mysqli_fetch_assoc(mysqli_stmt_get_result($stmt))["total"];

        if ($used > 0) {
            $stmt = mysqli_prepare($conn, "UPDATE menu_items SET status='unavailable' WHERE id=?");
            mysqli_stmt_bind_param($stmt, "i", $id);
            mysqli_stmt_execute($stmt);
            flash_set("success", "This item has past orders, so it was marked Unavailable instead of deleted.");
        } else {
            $stmt = mysqli_prepare($conn, "DELETE FROM menu_items WHERE id=?");
            mysqli_stmt_bind_param($stmt, "i", $id);
            mysqli_stmt_execute($stmt);
            if ($row["image"] && is_file("../assets/images/" . basename($row["image"]))) {
                @unlink("../assets/images/" . basename($row["image"]));
            }
            flash_set("success", "Menu item deleted.");
        }
    }
} catch (mysqli_sql_exception $e) {
    flash_set("error", "Could not delete the menu item.");
}
header("Location: menu.php"); exit;
?>
