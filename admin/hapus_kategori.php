<?php
if (!isset($_COOKIE['user_id']) || $_COOKIE['role'] != 'admin') {
    header("Location: ../auth/login.php");
    exit;
}
include "../config/database.php";

$id = $_GET['id'];

/* Optional: cegah hapus jika dipakai produk */
$cek = mysqli_query($conn, "SELECT COUNT(*) as total FROM products WHERE category_id=$id");
$data = mysqli_fetch_assoc($cek);

if ($data['total'] > 0) {
    echo "<script>
        alert('Kategori masih digunakan produk!');
        window.location='kategori.php';
    </script>";
    exit;
}

mysqli_query($conn, "DELETE FROM categories WHERE id=$id");
header("Location: kategori.php");
exit;
