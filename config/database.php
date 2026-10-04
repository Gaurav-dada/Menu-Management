<?php
$host = "localhost";
$username = "root";
$password = "";
$database = "menu_management_system";

$conn = mysqli_connect($host, $username, $password, $database);

if (!$conn) {
    die("Database connection failed: " . mysqli_connect_error());
}

mysqli_set_charset($conn, "utf8mb4");
?>
