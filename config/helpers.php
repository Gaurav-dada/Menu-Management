<?php
// Shared helpers: flash messages, CSRF, validation, safe image upload

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

/* Allowed characters */
const PATTERN_PERSON = '/^[\p{L}\p{M}][\p{L}\p{M} .\'-]*$/u';
const PATTERN_TITLE  = '/^[\p{L}\p{M}\p{N}][\p{L}\p{M}\p{N} &\'(),.\/-]*$/u';

/* ---------- Flash messages ---------- */
function flash_set($type, $message) {
    $_SESSION["flash"] = ["type" => $type, "message" => $message];
}

function flash_show() {
    if (!empty($_SESSION["flash"])) {
        $f = $_SESSION["flash"];
        unset($_SESSION["flash"]);
        $class = $f["type"] === "error" ? "alert error" : "alert success";
        echo '<div class="' . $class . '">' . htmlspecialchars($f["message"]) . '</div>';
    }
}

/* ---------- CSRF protection ---------- */
function csrf_token() {
    if (empty($_SESSION["csrf"])) {
        $_SESSION["csrf"] = bin2hex(random_bytes(32));
    }
    return $_SESSION["csrf"];
}

function csrf_field() {
    return '<input type="hidden" name="csrf" value="' . htmlspecialchars(csrf_token()) . '">';
}

function csrf_valid() {
    return isset($_POST["csrf"], $_SESSION["csrf"]) && hash_equals($_SESSION["csrf"], (string)$_POST["csrf"]);
}

/* ---------- Validation (each returns an error string, or null when valid) ---------- */
function str_len($s) {
    return function_exists("mb_strlen") ? mb_strlen($s, "UTF-8") : strlen($s);
}

function validate_text($value, $label, $min, $max, $pattern = null, $patternMsg = "") {
    if ($value === "") return "$label is required.";
    $len = str_len($value);
    if ($len < $min) return "$label must be at least $min characters.";
    if ($len > $max) return "$label must not exceed $max characters.";
    if ($pattern !== null && !preg_match($pattern, $value)) {
        return $patternMsg !== "" ? $patternMsg : "$label contains invalid characters.";
    }
    return null;
}

function validate_optional_text($value, $label, $max) {
    if (str_len($value) > $max) return "$label must not exceed $max characters.";
    return null;
}

function validate_email_value($email) {
    if ($email === "") return "Email is required.";
    if (str_len($email) > 150) return "Email must not exceed 150 characters.";
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) return "Enter a valid email address.";
    return null;
}

/** Returns [float price, error]. Max 8 digits before the decimal point, 2 after. */
function validate_price($raw) {
    $raw = trim((string)$raw);
    if ($raw === "") return [null, "Price is required."];
    if (!preg_match('/^\d{1,8}(\.\d{1,2})?$/', $raw)) {
        return [null, "Price must be a number with up to 2 decimal places (max 99999999.99)."];
    }
    $price = (float)$raw;
    if ($price <= 0) return [null, "Price must be greater than 0."];
    return [$price, null];
}

function category_exists($conn, $id) {
    if ($id <= 0) return false;
    $stmt = mysqli_prepare($conn, "SELECT id FROM categories WHERE id=?");
    mysqli_stmt_bind_param($stmt, "i", $id);
    mysqli_stmt_execute($stmt);
    return (bool)mysqli_fetch_assoc(mysqli_stmt_get_result($stmt));
}

/* ---------- Safe image upload ---------- */
/** Returns [filename, error]. filename is "" when no file was chosen. */
function upload_menu_image($file, $dir) {
    if (empty($file["name"])) {
        return ["", null];
    }
    if ($file["error"] === UPLOAD_ERR_INI_SIZE || $file["error"] === UPLOAD_ERR_FORM_SIZE) {
        return [null, "Image is too large (max 2 MB)."];
    }
    if ($file["error"] !== UPLOAD_ERR_OK) {
        return [null, "Image upload failed. Please try again."];
    }
    if ($file["size"] > 2 * 1024 * 1024) {
        return [null, "Image must be smaller than 2 MB."];
    }

    $info = @getimagesize($file["tmp_name"]);
    $types = [
        IMAGETYPE_JPEG => "jpg",
        IMAGETYPE_PNG  => "png",
        IMAGETYPE_GIF  => "gif",
        IMAGETYPE_WEBP => "webp",
    ];
    if (!$info || !isset($types[$info[2]])) {
        return [null, "Only JPG, PNG, GIF or WEBP images are allowed."];
    }

    if (!is_dir($dir)) {
        mkdir($dir, 0777, true);
    }
    $name = bin2hex(random_bytes(8)) . "." . $types[$info[2]];
    if (!move_uploaded_file($file["tmp_name"], rtrim($dir, "/") . "/" . $name)) {
        return [null, "Could not save the image. Check folder permissions."];
    }
    return [$name, null];
}
?>
