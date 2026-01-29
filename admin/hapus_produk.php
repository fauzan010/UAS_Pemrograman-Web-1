<?php
if (!isset($_COOKIE['user_id']) || $_COOKIE['role'] != 'admin') {
    header("Location: ../auth/login.php");
    exit;
}
include "../config/database.php";

$id = isset($_GET['id']) ? intval($_GET['id']) : 0;
if ($id <= 0) {
    header("Location: produk.php?error=invalid");
    exit;
}

$cek_orders = mysqli_query($conn, "SELECT COUNT(*) AS total FROM orders WHERE product_id=$id");
$orders_row = mysqli_fetch_assoc($cek_orders);
if ($orders_row && $orders_row['total'] > 0) {
    header("Location: produk.php?error=has_orders");
    exit;
}

mysqli_query($conn, "DELETE FROM products WHERE id=$id");

if (function_exists('log_admin_action')) {
    log_admin_action($conn, "Hapus produk ID $id");
}

header("Location: produk.php?success=deleted");
exit;
