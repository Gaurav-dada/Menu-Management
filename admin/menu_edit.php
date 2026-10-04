<?php
require_once "../config/auth.php"; require_admin();
require_once "../config/database.php";
require_once "../config/helpers.php";

$id = (int)($_GET["id"] ?? $_POST["id"] ?? 0);
$stmt = mysqli_prepare($conn, "SELECT * FROM menu_items WHERE id=?");
mysqli_stmt_bind_param($stmt, "i", $id);
mysqli_stmt_execute($stmt);
$item = mysqli_fetch_assoc(mysqli_stmt_get_result($stmt));
if (!$item) {
    flash_set("error", "Menu item not found.");
    header("Location: menu.php"); exit;
}

$error = "";

if (isset($_POST["update"])) {
    $name = trim($_POST["name"] ?? "");
    $description = trim($_POST["description"] ?? "");
    $category = (int)($_POST["category_id"] ?? 0);
    $priceRaw = trim($_POST["price"] ?? "");
    $status = $_POST["status"] ?? "";
    $image = $item["image"];

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
            // duplicate name in the same category (ignore this item itself)
            $stmt = mysqli_prepare($conn, "SELECT id FROM menu_items WHERE category_id=? AND LOWER(name)=LOWER(?) AND id<>?");
            mysqli_stmt_bind_param($stmt, "isi", $category, $name, $id);
            mysqli_stmt_execute($stmt);
            if (mysqli_fetch_assoc(mysqli_stmt_get_result($stmt))) {
                $error = "Another item with this name already exists in the selected category.";
            } else {
                [$newImage, $uploadError] = upload_menu_image($_FILES["image"] ?? ["name" => ""], "../assets/images/");
                if ($uploadError) {
                    $error = $uploadError;
                } else {
                    if ($newImage !== "") {
                        if ($image && is_file("../assets/images/" . basename($image))) {
                            @unlink("../assets/images/" . basename($image));
                        }
                        $image = $newImage;
                    }
                    $stmt = mysqli_prepare($conn, "UPDATE menu_items SET category_id=?,name=?,description=?,price=?,image=?,status=? WHERE id=?");
                    mysqli_stmt_bind_param($stmt, "issdssi", $category, $name, $description, $price, $image, $status, $id);
                    mysqli_stmt_execute($stmt);
                    flash_set("success", "Menu item updated.");
                    header("Location: menu.php"); exit;
                }
            }
        } catch (mysqli_sql_exception $e) {
            $error = "Could not update the menu item. Please try again.";
        }
    }
    $error = $error ?? "";
    // keep what the admin typed if validation failed
    $item["name"] = $name; $item["description"] = $description; $item["category_id"] = $category;
    $item["price"] = $priceRaw; $item["status"] = $status;
}
$categories = mysqli_query($conn, "SELECT * FROM categories ORDER BY name");
$page_title = "Edit Menu"; include "../includes/header.php"; include "../includes/navbar.php";
?>
<div class="container"><div class="form-card"><h2>Edit Menu Item</h2>
<?php if ($error): ?><div class="alert error"><?= htmlspecialchars($error) ?></div><?php endif; ?>
<form method="POST" enctype="multipart/form-data"><?= csrf_field() ?>
<input type="hidden" name="id" value="<?= $id ?>">
<div class="form-group"><label>Name</label><input name="name" maxlength="150" value="<?= htmlspecialchars($item["name"]) ?>" required></div>
<div class="form-group"><label>Category</label><select name="category_id" required><?php while ($c = mysqli_fetch_assoc($categories)): ?><option value="<?= $c["id"] ?>" <?= $c["id"] == $item["category_id"] ? "selected" : "" ?>><?= htmlspecialchars($c["name"]) ?></option><?php endwhile; ?></select></div>
<div class="form-group"><label>Description (optional, max 500)</label><textarea name="description" maxlength="500"><?= htmlspecialchars($item["description"] ?? "") ?></textarea></div>
<div class="form-group"><label>Price (Rs.)</label><input type="number" step="0.01" min="0.01" max="99999999.99" name="price" value="<?= htmlspecialchars($item["price"]) ?>" required></div>
<div class="form-group"><label>Image (leave empty to keep current)</label>
<?php if ($item["image"]): ?><img src="../assets/images/<?= htmlspecialchars($item["image"]) ?>" alt="" style="width:120px;border-radius:8px;display:block;margin-bottom:8px"><?php endif; ?>
<input type="file" name="image" accept="image/*"></div>
<div class="form-group"><label>Status</label><select name="status"><option value="available" <?= $item["status"] == "available" ? "selected" : "" ?>>Available</option><option value="unavailable" <?= $item["status"] == "unavailable" ? "selected" : "" ?>>Unavailable</option></select></div>
<button name="update" value="1">Update</button></form></div></div>
<?php include "../includes/footer.php"; ?>
