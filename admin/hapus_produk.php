<?php
if (!isset($_COOKIE['user_id']) || $_COOKIE['role'] != 'admin') {
    header("Location: ../auth/login.php");
    exit;
}
include "../config/database.php";

$id = $_GET['id'];
mysqli_query($conn, "DELETE FROM products WHERE id=$id");

header("Location: produk.php");
exit;
