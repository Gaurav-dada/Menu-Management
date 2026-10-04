<?php
session_start();
require_once "config/database.php";
require_once "config/helpers.php";

$error = "";
$name = "";
$email = "";

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $name = trim($_POST["name"] ?? "");
    $email = strtolower(trim($_POST["email"] ?? ""));
    $password = $_POST["password"] ?? "";

    $error = validate_text($name, "Name", 2, 100, PATTERN_PERSON, "Name can contain only letters, spaces, dots, apostrophes and hyphens.")
          ?? validate_email_value($email);

    if ($error === null) {
        if (strlen($password) < 6) {
            $error = "Password must be at least 6 characters.";
        } elseif (strlen($password) > 72) {
            $error = "Password must not exceed 72 characters.";
        } elseif (trim($password) === "") {
            $error = "Password cannot be only spaces.";
        }
    }

    if ($error === null) {
        try {
            $check = mysqli_prepare($conn, "SELECT id FROM users WHERE email=?");
            mysqli_stmt_bind_param($check, "s", $email);
            mysqli_stmt_execute($check);
            mysqli_stmt_store_result($check);

            if (mysqli_stmt_num_rows($check) > 0) {
                $error = "Email already exists.";
            } else {
                $hash = password_hash($password, PASSWORD_DEFAULT);
                $stmt = mysqli_prepare($conn, "INSERT INTO users(name,email,password) VALUES(?,?,?)");
                mysqli_stmt_bind_param($stmt, "sss", $name, $email, $hash);
                mysqli_stmt_execute($stmt);
                header("Location: login.php?registered=1");
                exit;
            }
        } catch (mysqli_sql_exception $e) {
            $error = ($e->getCode() == 1062) ? "Email already exists." : "Could not create the account. Please try again.";
        }
    }
    $error = $error ?? "";
}
$page_title = "Register";
include "includes/header.php";
include "includes/navbar.php";
?>
<div class="container">
<div class="form-card">
<h2>Create Account</h2>
<?php if ($error): ?><div class="alert error"><?= htmlspecialchars($error) ?></div><?php endif; ?>
<form method="POST">
<div class="form-group"><label>Name</label><input name="name" maxlength="100" value="<?= htmlspecialchars($name) ?>" required></div>
<div class="form-group"><label>Email</label><input type="email" name="email" maxlength="150" value="<?= htmlspecialchars($email) ?>" required></div>
<div class="form-group"><label>Password (6-72 characters)</label><input type="password" name="password" minlength="6" maxlength="72" required></div>
<button type="submit">Register</button>
</form>
</div></div>
<?php include "includes/footer.php"; ?>
