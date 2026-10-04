<?php
require_once "../config/auth.php"; require_admin();
require_once "../config/database.php";
require_once "../config/helpers.php";

$error = "";
$old = ["name" => "", "description" => "", "category_id" => "", "price" => "", "status" => "available"];

if (isset($_POST["save"])) {
    $name = trim($_POST["name"] ?? "");
    $description = trim($_POST["description"] ?? "");
    $category = (int)($_POST["category_id"] ?? 0);
    $priceRaw = trim($_POST["price"] ?? "");
    $status = $_POST["status"] ?? "";
    $old = ["name" => $name, "description" => $description, "category_id" => $category, "price" => $priceRaw, "status" => $status];

    [$price, $priceError] = validate_price($priceRaw);

    if (!csrf_valid()) {
        $error = "Invalid request. Please reload the page and try again.";
    } else {
        $error = validate_text($name, "Item name", 2, 150, PATTERN_TITLE, "Item name can contain only letters, numbers, spaces and & ' ( ) , . / -")
              ?? validate_optional_text($description, "Description", 500);
        if ($error === null && !category_exists($conn, $category)) $error = "Please select a valid category.";
        if ($error === null) $error = $priceError;
        if ($error === null && !in_array($status, ["available", "unavailable"], true)) $error = "Invalid status.";
    }

    if ($error === null) {
        try {
            // duplicate name in the same category
            $stmt = mysqli_prepare($conn, "SELECT id FROM menu_items WHERE category_id=? AND LOWER(name)=LOWER(?)");
            mysqli_stmt_bind_param($stmt, "is", $category, $name);
            mysqli_stmt_execute($stmt);
            if (mysqli_fetch_assoc(mysqli_stmt_get_result($stmt))) {
                $error = "This item already exists in the selected category.";
            } else {
                [$image, $uploadError] = upload_menu_image($_FILES["image"] ?? ["name" => ""], "../assets/images/");
                if ($uploadError) {
                    $error = $uploadError;
                } else {
                    $stmt = mysqli_prepare($conn, "INSERT INTO menu_items(category_id,name,description,price,image,status) VALUES(?,?,?,?,?,?)");
                    mysqli_stmt_bind_param($stmt, "issdss", $category, $name, $description, $price, $image, $status);
                    mysqli_stmt_execute($stmt);
                    flash_set("success", "Menu item added.");
                    header("Location: menu.php"); exit;
                }
            }
        } catch (mysqli_sql_exception $e) {
            $error = "Could not save the menu item. Please try again.";
        }
    }
    $error = $error ?? "";
}
$categories = mysqli_query($conn, "SELECT * FROM categories ORDER BY name");
$page_title = "Add Menu"; include "../includes/header.php"; include "../includes/navbar.php";
?>
<div class="container"><div class="form-card"><h2>Add Menu Item</h2>
<?php if ($error): ?><div class="alert error"><?= htmlspecialchars($error) ?></div><?php endif; ?>
<form method="POST" enctype="multipart/form-data"><?= csrf_field() ?>
<div class="form-group"><label>Name</label><input name="name" maxlength="150" value="<?= htmlspecialchars($old["name"]) ?>" required></div>
<div class="form-group"><label>Category</label><select name="category_id" required><option value="">Select</option><?php while ($c = mysqli_fetch_assoc($categories)): ?><option value="<?= $c["id"] ?>" <?= $c["id"] == $old["category_id"] ? "selected" : "" ?>><?= htmlspecialchars($c["name"]) ?></option><?php endwhile; ?></select></div>
<div class="form-group"><label>Description (optional, max 500)</label><textarea name="description" maxlength="500"><?= htmlspecialchars($old["description"]) ?></textarea></div>
<div class="form-group"><label>Price (Rs.)</label><input type="number" step="0.01" min="0.01" max="99999999.99" name="price" value="<?= htmlspecialchars($old["price"]) ?>" required></div>
<div class="form-group"><label>Image (JPG, PNG, GIF, WEBP - max 2 MB)</label><input type="file" name="image" accept="image/*"></div>
<div class="form-group"><label>Status</label><select name="status"><option value="available" <?= $old["status"] === "available" ? "selected" : "" ?>>Available</option><option value="unavailable" <?= $old["status"] === "unavailable" ? "selected" : "" ?>>Unavailable</option></select></div>
<button name="save" value="1">Save Menu</button></form></div></div>
<?php include "../includes/footer.php"; ?>
