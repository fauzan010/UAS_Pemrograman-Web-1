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
    if (function_exists('log_admin_action')) {
        log_admin_action($conn, "Edit kategori: $nama (ID $id)");
    }
    header("Location: kategori.php");
    exit;
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Edit Kategori</title>
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css"
          rel="stylesheet"
          integrity="sha384-QWTKZyjpPEjISv5WaRU9OFeRpok6YctnYmDr5pNlyT2bRjXh0JMhjY6hW+ALEwIH"
          crossorigin="anonymous">
    <link rel="stylesheet" href="../assets/css/style.css">
    <style>
    body { background: linear-gradient(120deg, #e0eafc 0%, #cfdef3 100%); min-height: 100vh; margin: 0; }
    .navbar { width: 100%; display: flex; align-items: center; padding: 18px 28px; background: #fff; box-shadow: 0 2px 18px rgba(44,62,80,0.07); position: sticky; top: 0; z-index: 110; gap: 16px; }
    .navbar .logo { font-size: 1.7rem; font-weight: 800; color: #3498db; letter-spacing: 1.5px; }
    .navbar ul { display: flex; gap: 16px; list-style: none; margin: 0; padding: 0; align-items: center; }
    .navbar ul li a { color: #2c3e50; text-decoration: none; font-size: 1rem; font-weight: 600; padding: 7px 16px; border-radius: 8px; transition: background 0.2s, color 0.2s; display: inline-block; }
    .navbar ul li a.active, .navbar ul li a:hover { background: #3498db; color: #fff; }
    .navbar ul li.nav-right { margin-left: auto; }
    .btn-logout { background: #e74c3c; color: #fff; padding: 8px 18px; border-radius: 8px; border: none; font-weight: 700; text-decoration: none; }
    .btn-logout:hover { background: #c0392b; color: #fff; }
    .dashboard-container { max-width: 480px; margin: 40px auto; padding: 0 16px; }
    h1 { color: #3498db; font-size: 1.5rem; margin-bottom: 18px; text-align: center; }
    .form-card { background: #fff; padding: 24px; border-radius: 14px; box-shadow: 0 8px 24px rgba(44,62,80,0.08); }
    label { font-weight: 600; color: #217dbb; margin-bottom: 6px; display: block; }
    input[type="text"] { width: 100%; padding: 10px; margin: 8px 0 16px; border-radius: 8px; border: 1px solid #ddd; }
    .btn { background: #3498db; color: #fff; padding: 10px 18px; border-radius: 8px; text-decoration: none; font-weight: 600; border: none; margin-right: 8px; transition: background 0.2s; cursor: pointer; }
    .btn.danger { background: #e74c3c; }
    .btn:hover { background: #217dbb; }
    </style>
</head>
<body>
<nav class="navbar">
    <div class="logo">WorldBike Admin</div>
    <ul>
        <li><a href="dashboard.php">Dashboard</a></li>
        <li><a href="produk.php">Produk</a></li>
        <li><a href="kategori.php" class="active">Kategori</a></li>
        <li><a href="orders.php">Orders</a></li>
        <li><a href="users.php">Users</a></li>
        <li class="nav-right"><a href="../auth/logout.php" class="btn-logout">Logout</a></li>
    </ul>
</nav>

<div class="dashboard-container">
    <h1>Edit Kategori</h1>

    <form method="POST" class="form-card">
        <label>Nama Kategori</label>
        <input type="text" name="nama" value="<?= $kategori['nama_kategori'] ?>" required>

        <button class="btn">Update</button>
        <a href="kategori.php" class="btn danger">Batal</a>
    </form>
</div>

<!-- Bootstrap JS -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"
        integrity="sha384-YvpcrYf0tY3lHB60NNkmXc5s9fDVZLESaAA55NDzOxhy9GkcIdslK1eN7N6jIeHz"
        crossorigin="anonymous"></script>
</body>
</html>
