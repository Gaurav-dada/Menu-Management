<?php
session_start();
require_once "config/database.php";
require_once "config/helpers.php";

$error = "";
$email = "";
if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $email = strtolower(trim($_POST["email"] ?? ""));
    $password = $_POST["password"] ?? "";

    if ($email === "" || $password === "") {
        $error = "Email and password are required.";
    } elseif (validate_email_value($email) !== null || strlen($password) > 72) {
        $error = "Invalid email or password.";
    } else {
        try {
            $stmt = mysqli_prepare($conn, "SELECT id,name,password,role FROM users WHERE email=?");
            mysqli_stmt_bind_param($stmt, "s", $email);
            mysqli_stmt_execute($stmt);
            $user = mysqli_fetch_assoc(mysqli_stmt_get_result($stmt));

            if ($user && password_verify($password, $user["password"])) {
                session_regenerate_id(true); // cart is kept, session id is renewed
                $_SESSION["user_id"] = $user["id"];
                $_SESSION["name"] = $user["name"];
                $_SESSION["role"] = $user["role"];
                header("Location: " . ($user["role"] === "admin" ? "admin/dashboard.php" : "index.php"));
                exit;
            }
            $error = "Invalid email or password.";
        } catch (mysqli_sql_exception $e) {
            $error = "Something went wrong. Please try again.";
        }
    }
}
$page_title = "Login";
include "includes/header.php";
include "includes/navbar.php";
?>
<div class="container"><div class="form-card">
<h2>Login</h2>
<?php if (isset($_GET["registered"])): ?><div class="alert success">Registration successful. Please login.</div><?php endif; ?>
<?php if ($error): ?><div class="alert error"><?= htmlspecialchars($error) ?></div><?php endif; ?>
<form method="POST">
<div class="form-group"><label>Email</label><input type="email" name="email" maxlength="150" value="<?= htmlspecialchars($email) ?>" required></div>
<div class="form-group"><label>Password</label><input type="password" name="password" maxlength="72" required></div>
<button>Login</button>
</form>
</div></div>
<?php include "includes/footer.php"; ?>
