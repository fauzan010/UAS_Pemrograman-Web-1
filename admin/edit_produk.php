<?php
if (!isset($_COOKIE['user_id']) || $_COOKIE['role'] != 'admin') {
    header("Location: ../auth/login.php");
    exit;
}
include "../config/database.php";

$id = $_GET['id'];
$product = mysqli_fetch_assoc(mysqli_query($conn, "SELECT * FROM products WHERE id=$id"));
$categories = mysqli_query($conn, "SELECT * FROM categories");

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nama = $_POST['nama'];
    $harga = $_POST['harga'];
    $stok = $_POST['stok'];
    $deskripsi = $_POST['deskripsi'];
    $category_id = $_POST['category_id'];

    // Update data utama
    mysqli_query($conn, "UPDATE products SET
        nama_produk='$nama',
        harga='$harga',
        stok='$stok',
        deskripsi='$deskripsi',
        category_id='$category_id'
        WHERE id=$id");

    // === TAMBAHAN DI SINI ===
    // Jika admin upload gambar baru
    if (!empty($_FILES['gambar']['name'])) {
        $gambar = $_FILES['gambar']['name'];
        $tmp = $_FILES['gambar']['tmp_name'];

        $namaGambar = time() . "_" . $gambar;
        move_uploaded_file($tmp, "../assets/img/produk/" . $namaGambar);

        mysqli_query($conn, "UPDATE products SET gambar='$namaGambar' WHERE id=$id");
    }
    // === SAMPAI SINI ===

    if (function_exists('log_admin_action')) {
        log_admin_action($conn, "Edit produk: $nama (ID $id)");
    }

    header("Location: produk.php");
    exit;
}

?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Edit Produk</title>
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css"
          rel="stylesheet"
          integrity="sha384-QWTKZyjpPEjISv5WaRU9OFeRpok6YctnYmDr5pNlyT2bRjXh0JMhjY6hW+ALEwIH"
          crossorigin="anonymous">
    <link rel="stylesheet" href="../assets/css/style.css">
    <style>
    body {
        background: linear-gradient(120deg, #e0eafc 0%, #cfdef3 100%);
        min-height: 100vh;
    }
    .navbar {
        width: 100%;
        display: flex;
        align-items: center;
        padding: 18px 28px;
        background: #fff;
        box-shadow: 0 2px 18px rgba(44,62,80,0.07);
        position: sticky;
        top: 0;
        z-index: 110;
        gap: 16px;
    }
    .navbar .logo { font-size: 1.7rem; font-weight: 800; color: #3498db; letter-spacing: 1.5px; }
    .navbar ul { display: flex; gap: 16px; list-style: none; margin: 0; padding: 0; align-items: center; }
    .navbar ul li a { color: #2c3e50; text-decoration: none; font-size: 1rem; font-weight: 600; padding: 7px 16px; border-radius: 8px; transition: background 0.2s, color 0.2s; display: inline-block; }
    .navbar ul li a.active, .navbar ul li a:hover { background: #3498db; color: #fff; }
    .navbar ul li.nav-right { margin-left: auto; }
    .btn-logout { background: #e74c3c; color: #fff; padding: 8px 18px; border-radius: 8px; border: none; font-weight: 700; text-decoration: none; }
    .btn-logout:hover { background: #c0392b; color: #fff; }
    .dashboard-container {
        max-width: 600px;
        margin: 40px auto;
        padding: 30px 20px;
        background: #fff;
        border-radius: 18px;
        box-shadow: 0 8px 32px rgba(44,62,80,0.06);
    }
    h1 {
        color: #3498db;
        font-size: 1.5rem;
        margin-bottom: 24px;
        text-align: center;
    }
    .form-card {
        background: #f8fafc;
        padding: 25px;
        border-radius: 12px;
        box-shadow: 0 8px 20px rgba(52,152,219,0.07);
    }
    label {
        font-weight: 500;
        color: #217dbb;
        margin-bottom: 6px;
        display: block;
    }
    input[type="text"], input[type="number"], select, textarea {
        width: 100%;
        padding: 10px;
        margin: 8px 0 15px;
        border-radius: 6px;
        border: 1px solid #ddd;
    }
    input[type="file"] {
        margin-bottom: 15px;
    }
    .btn {
        background: #3498db;
        color: #fff;
        padding: 10px 18px;
        border-radius: 8px;
        text-decoration: none;
        font-weight: 500;
        border: none;
        margin-right: 8px;
        transition: background 0.2s;
        cursor: pointer;
    }
    .btn.danger {
        background: #e74c3c;
    }
    .btn:hover {
        background: #217dbb;
    }
    </style>
</head>
<body>
<nav class="navbar">
    <div class="logo">WorldBike Admin</div>
    <ul>
        <li><a href="dashboard.php">Dashboard</a></li>
        <li><a href="produk.php" class="active">Produk</a></li>
        <li><a href="kategori.php">Kategori</a></li>
        <li><a href="orders.php">Orders</a></li>
        <li><a href="users.php">Users</a></li>
        <li class="nav-right"><a href="../auth/logout.php" class="btn-logout">Logout</a></li>
    </ul>
</nav>
<div class="dashboard-container">
    <h1>Edit Produk</h1>
    <form method="POST" enctype="multipart/form-data" class="form-card">
        <label>Gambar Produk (kosongkan jika tidak diganti)</label>
        <input type="file" name="gambar" accept="image/*">
        <label>Nama Produk</label>
        <input type="text" name="nama" value="<?= $product['nama_produk'] ?>" required>
        <label>Kategori</label>
        <select name="category_id" required>
            <?php while($c = mysqli_fetch_assoc($categories)): ?>
                <option value="<?= $c['id'] ?>"
                    <?= $product['category_id'] == $c['id'] ? 'selected' : '' ?>>
                    <?= $c['nama_kategori'] ?>
                </option>
            <?php endwhile; ?>
        </select>
        <label>Harga</label>
        <input type="number" name="harga" value="<?= $product['harga'] ?>" required>
        <label>Stok</label>
        <input type="number" name="stok" value="<?= $product['stok'] ?>" required>
        <label>Deskripsi</label>
        <textarea name="deskripsi"><?= $product['deskripsi'] ?></textarea>
        <button class="btn">Update</button>
        <a href="produk.php" class="btn danger">Batal</a>
    </form>
</div>
<!-- Footer -->
<footer class="footer" style="text-align:center; padding:18px 0; background:#f4f6f8; color:#888; font-size:15px;">
    @Copyright by 23552011029_Fauzan Rizkika Kurnia_TIF RP 23 CNS B_UASWEB1
</footer>

<!-- Bootstrap JS -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"
        integrity="sha384-YvpcrYf0tY3lHB60NNkmXc5s9fDVZLESaAA55NDzOxhy9GkcIdslK1eN7N6jIeHz"
        crossorigin="anonymous"></script>
</body>
</html>
