<?php
if (!isset($_COOKIE['user_id']) || $_COOKIE['role'] != 'admin') {
    header("Location: ../auth/login.php");
    exit;
}
include "../config/database.php";

$id = $_GET['id'];
$kategori = mysqli_fetch_assoc(
    mysqli_query($conn, "SELECT * FROM categories WHERE id=$id")
);

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nama = $_POST['nama'];

    mysqli_query($conn, "UPDATE categories SET nama_kategori='$nama' WHERE id=$id");
    header("Location: kategori.php");
    exit;
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Edit Kategori</title>
    <link rel="stylesheet" href="../assets/css/style.css">
</head>
<body>

<div class="dashboard-container">
    <h1>Edit Kategori</h1>

    <form method="POST" class="form-card">
        <label>Nama Kategori</label>
        <input type="text" name="nama" value="<?= $kategori['nama_kategori'] ?>" required>

        <button class="btn">Update</button>
        <a href="kategori.php" class="btn danger">Batal</a>
    </form>
</div>

</body>
</html>
