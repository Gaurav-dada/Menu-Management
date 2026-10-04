<?php
require_once "../config/auth.php"; require_admin();
require_once "../config/database.php";
require_once "../config/helpers.php";

$error = "";
$old = ["name" => "", "description" => ""];

/* ---------- Add ---------- */
if (isset($_POST["add"])) {
    $name = trim($_POST["name"] ?? "");
    $description = trim($_POST["description"] ?? "");
    $old = ["name" => $name, "description" => $description];

    if (!csrf_valid()) {
        $error = "Invalid request. Please reload the page and try again.";
    } else {
        $error = validate_text($name, "Category name", 2, 100, PATTERN_TITLE, "Category name can contain only letters, numbers, spaces and & ' ( ) , . / -")
              ?? validate_optional_text($description, "Description", 500);

        if ($error === null) {
            try {
                $stmt = mysqli_prepare($conn, "SELECT id FROM categories WHERE LOWER(name)=LOWER(?)");
                mysqli_stmt_bind_param($stmt, "s", $name);
                mysqli_stmt_execute($stmt);
                if (mysqli_fetch_assoc(mysqli_stmt_get_result($stmt))) {
                    $error = "Category already exists.";
                } else {
                    $stmt = mysqli_prepare($conn, "INSERT INTO categories(name,description) VALUES(?,?)");
                    mysqli_stmt_bind_param($stmt, "ss", $name, $description);
                    mysqli_stmt_execute($stmt);
                    flash_set("success", "Category added.");
                    header("Location: categories.php"); exit;
                }
            } catch (mysqli_sql_exception $e) {
                $error = "Could not save the category. Please try again.";
            }
        }
    }
}

/* ---------- Delete (POST + CSRF) ---------- */
if (isset($_POST["delete"])) {
    $id = (int)($_POST["id"] ?? 0);

    if (!csrf_valid()) {
        flash_set("error", "Invalid request. Please reload the page and try again.");
    } elseif (!category_exists($conn, $id)) {
        flash_set("error", "Category not found.");
    } else {
        try {
            // Deleting a category cascades to its menu items (and order history),
            // so only empty categories can be deleted.
            $stmt = mysqli_prepare($conn, "SELECT COUNT(*) AS total FROM menu_items WHERE category_id=?");
            mysqli_stmt_bind_param($stmt, "i", $id);
            mysqli_stmt_execute($stmt);
            $count = mysqli_fetch_assoc(mysqli_stmt_get_result($stmt))["total"];

            if ($count > 0) {
                flash_set("error", "Cannot delete: this category still has $count menu item(s). Move or delete them first.");
            } else {
                $stmt = mysqli_prepare($conn, "DELETE FROM categories WHERE id=?");
                mysqli_stmt_bind_param($stmt, "i", $id);
                mysqli_stmt_execute($stmt);
                flash_set("success", "Category deleted.");
            }
        } catch (mysqli_sql_exception $e) {
            flash_set("error", "Could not delete the category.");
        }
    }
    header("Location: categories.php"); exit;
}

$result = mysqli_query($conn, "SELECT * FROM categories ORDER BY id DESC");
$page_title = "Categories"; include "../includes/header.php"; include "../includes/navbar.php";
?>
<div class="container"><h1>Category Management</h1>
<?php flash_show(); ?>
<div class="form-card" style="margin-left:0">
<?php if ($error): ?><div class="alert error"><?= htmlspecialchars($error) ?></div><?php endif; ?>
<form method="POST"><?= csrf_field() ?>
<div class="form-group"><label>Name</label><input name="name" maxlength="100" value="<?= htmlspecialchars($old["name"]) ?>" required></div>
<div class="form-group"><label>Description (optional, max 500)</label><textarea name="description" maxlength="500"><?= htmlspecialchars($old["description"]) ?></textarea></div>
<button name="add" value="1">Add Category</button></form></div>
<div class="table-wrap"><table><tr><th>ID</th><th>Name</th><th>Description</th><th>Action</th></tr>
<?php while ($c = mysqli_fetch_assoc($result)): ?><tr>
<td><?= $c["id"] ?></td><td><?= htmlspecialchars($c["name"]) ?></td><td><?= htmlspecialchars($c["description"] ?? "") ?></td>
<td><form method="POST" class="inline-form"><?= csrf_field() ?><input type="hidden" name="id" value="<?= $c["id"] ?>"><button type="submit" name="delete" value="1" class="btn-danger confirm-delete">Delete</button></form></td>
</tr><?php endwhile; ?></table></div></div>
<?php include "../includes/footer.php"; ?>
