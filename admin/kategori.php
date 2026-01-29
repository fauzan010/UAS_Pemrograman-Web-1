<?php
if (!isset($_COOKIE['user_id']) || $_COOKIE['role'] != 'admin') {
    header("Location: ../auth/login.php");
    exit;
}
include "../config/database.php";

$categories = mysqli_query($conn, "SELECT * FROM categories");
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Kelola Kategori</title>
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css"
          rel="stylesheet"
          integrity="sha384-QWTKZyjpPEjISv5WaRU9OFeRpok6YctnYmDr5pNlyT2bRjXh0JMhjY6hW+ALEwIH"
          crossorigin="anonymous">
    <link rel="stylesheet" href="../assets/css/style.css">
    <style>
    body {
        background: #eef5ff;
        min-height: 100vh;
        margin: 0;
        font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
    }
    .navbar {
        width: 100%;
        display: flex;
        justify-content: space-between;
        align-items: center;
        padding: 16px 28px;
        background: #fff;
        box-shadow: 0 2px 18px rgba(44,62,80,0.07);
        position: sticky;
        top: 0;
        z-index: 120;
    }
    .navbar .logo {
        font-size: 1.7rem;
        font-weight: bold;
        color: #3498db;
        letter-spacing: 2px;
    }
    .navbar .menu-toggle {
        display: none;
        font-size: 2rem;
        background: none;
        border: none;
        color: #3498db;
        cursor: pointer;
        margin-left: 12px;
    }
    .navbar ul {
        display: flex;
        gap: 18px;
        list-style: none;
        margin: 0;
        padding: 0;
        align-items: center;
    }
    .navbar ul li a {
        color: #2c3e50;
        text-decoration: none;
        font-size: 1rem;
        font-weight: 500;
        padding: 7px 18px;
        border-radius: 6px;
        transition: background 0.2s, color 0.2s;
        display: block;
    }
    .navbar ul li a.active, .navbar ul li a:hover {
        background: #3498db;
        color: #fff;
    }
    .navbar ul li.nav-right { margin-left: auto; }
    .btn-logout {
        background: #e74c3c;
        color: #fff;
        padding: 7px 18px;
        border-radius: 6px;
        text-decoration: none;
        font-size: 1rem;
        margin-left: 8px;
        transition: background 0.2s;
        border: none;
        cursor: pointer;
    }
    .btn-logout:hover {
        background: #c0392b;
    }
    .page {
        max-width: 1100px;
        margin: 32px auto 120px auto;
        padding: 0 16px;
    }
    .panel {
        background: #fff;
        border-radius: 16px;
        box-shadow: 0 12px 32px rgba(44,62,80,0.10);
        padding: 20px 18px;
        border: 1px solid #e6eef7;
    }
    .page-header {
        display: flex;
        justify-content: space-between;
        align-items: center;
        gap: 12px;
        margin-bottom: 14px;
    }
    .page-title { margin: 0; color: #1b3a57; font-weight: 800; font-size: 1.6rem; }
    .page-sub { margin: 0; color: #607286; font-size: 0.98rem; }
    .actions { display: flex; gap: 10px; flex-wrap: wrap; align-items: center; }
    .pill-btn { background: linear-gradient(135deg, #3498db, #217dbb); color: #fff; padding: 10px 14px; border-radius: 12px; text-decoration: none; font-weight: 700; box-shadow: 0 10px 24px rgba(52,152,219,0.25); }
    .pill-btn:hover { opacity: 0.93; }
    .badge-info { background: #eaf6fb; color: #217dbb; padding: 8px 12px; border-radius: 999px; font-weight: 700; }
    /* Tombol aksi kategori */
    .aksi-btn-group {
        display: flex;
        gap: 8px;
        flex-wrap: wrap;
    }
    .btn-sm {
        margin-right: 0;
    }
    .table-wrapper { overflow-x: auto; }
    .table {
        width: 100%;
        min-width: 380px;
        border-collapse: separate;
        border-spacing: 0;
    }
    .table thead tr th {
        background: #f1f6ff;
        color: #1f3b57;
        font-weight: 800;
        font-size: 0.98rem;
        border-bottom: 1px solid #dde7f5;
        padding: 12px 14px;
        text-transform: uppercase;
        letter-spacing: 0.04em;
    }
    .table tbody tr td {
        padding: 12px 14px;
        border-bottom: 1px solid #eef2f7;
        background: #fff;
        vertical-align: middle;
    }
    .table tbody tr:hover td { background: #f6faff; }
    .aksi-btn-group {
        display: flex;
        gap: 8px;
        flex-wrap: wrap;
    }
    .btn-sm {
        background: #3498db;
        color: #fff;
        padding: 7px 14px;
        border-radius: 8px;
        font-size: 0.95rem;
        text-decoration: none;
        border: none;
        transition: transform 0.15s, background 0.2s;
        font-weight: 600;
        cursor: pointer;
        margin-right: 0;
        outline: none;
        display: inline-block;
    }
    .btn-sm:hover, .btn-sm:focus { background: #217dbb; color: #fff; transform: translateY(-1px); }
    .btn-sm.danger { background: #e74c3c; }
    .btn-sm.danger:hover, .btn-sm.danger:focus { background: #c0392b; color: #fff; }
    .footer {
        background: #fff;
        color: #030000;
        text-align: center;
        padding: 18px 0;
        font-size: 15px;
        position: fixed;
        left: 0;
        right: 0;
        bottom: 0;
        z-index: 99;
        border-top: 1px solid #eaeaea;
    }
    </style>
</head>
<body>
<nav class="navbar">
    <div class="logo">WorldBike Admin</div>
    <ul>
        <li><a href="dashboard.php">Dashboard</a></li>
        <li><a href="produk.php">Produk</a></li>
        <li><a href="kategori.php" class="active">Kategori</a></li>
        <li><a href="orders.php">Pemesanan</a></li>
        <li><a href="users.php">Pengguna</a></li>
        <li class="nav-right"><a href="../auth/logout.php" class="btn-logout">Logout</a></li>
    </ul>
</nav>
<div class="page">
    <div class="panel" style="margin-bottom:16px;">
        <div class="page-header">
            <div>
                <p class="eyebrow" style="margin:0; text-transform:uppercase; letter-spacing:0.08em; color:#607286; font-weight:800; font-size:0.82rem;">Data</p>
                <h1 class="page-title">Kelola Kategori</h1>
                <p class="page-sub">Buat, ubah, dan hapus kategori produk dengan cepat.</p>
            </div>
            <div class="actions">
                <span class="badge-info">Total: <?= mysqli_num_rows($categories) ?></span>
                <a href="tambah_kategori.php" class="pill-btn">+ Tambah Kategori</a>
            </div>
        </div>
    </div>

    <div class="panel">
        <div class="table-wrapper">
            <table class="table">
                <thead>
                    <tr>
                        <th style="width:70%">Nama Kategori</th>
                        <th style="width:30%">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    <?php mysqli_data_seek($categories, 0); while($c = mysqli_fetch_assoc($categories)): ?>
                    <tr>
                        <td><?= htmlspecialchars($c['nama_kategori']) ?></td>
                        <td>
                            <div class="aksi-btn-group">
                                <a href="edit_kategori.php?id=<?= $c['id'] ?>" class="btn-sm">Edit</a>
                                <a href="hapus_kategori.php?id=<?= $c['id'] ?>" 
                                   class="btn-sm danger"
                                   onclick="return confirm('Hapus kategori ini?')">
                                   Hapus
                                </a>
                            </div>
                        </td>
                    </tr>
                    <?php endwhile; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>
<!-- Bootstrap JS -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"
        integrity="sha384-YvpcrYf0tY3lHB60NNkmXc5s9fDVZLESaAA55NDzOxhy9GkcIdslK1eN7N6jIeHz"
        crossorigin="anonymous"></script>
<!-- Footer -->
<footer class="footer">
    @Copyright by 23552011029_Fauzan Rizkika Kurnia_TIF RP 23 CNS B_UASWEB1
</footer>
</body>
</html>
