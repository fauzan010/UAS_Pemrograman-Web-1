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
    <link rel="stylesheet" href="../assets/css/style.css">
    <style>
    body {
        background: linear-gradient(120deg, #e0eafc 0%, #cfdef3 100%);
        min-height: 100vh;
        margin: 0;
    }
    .navbar {
        width: 100%;
        display: flex;
        justify-content: space-between;
        align-items: center;
        padding: 18px 32px;
        background: #fff;
        box-shadow: 0 2px 18px rgba(44,62,80,0.07);
        position: sticky;
        top: 0;
        left: 0;
        right: 0;
        z-index: 100;
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
    .dashboard-container {
        max-width: 420px;
        width: 100%;
        margin: 48px auto 90px auto;
        padding: 0 16px;
        box-sizing: border-box;
        background: #fff;
        border-radius: 18px;
        box-shadow: 0 8px 32px rgba(44,62,80,0.06);
    }
    /* Tombol aksi kategori */
    .aksi-btn-group {
        display: flex;
        gap: 8px;
        flex-wrap: wrap;
    }
    .btn-sm {
        margin-right: 0;
    }
    .table-wrapper {
        background: #f8fafc;
        border-radius: 12px;
        padding: 8px;
        box-shadow: 0 8px 20px rgba(52,152,219,0.07);
        overflow-x: auto;
    }
    .table {
        width: 100%;
        min-width: 320px;
        border-collapse: collapse;
    }
    .table th, .table td {
        padding: 12px;
        border-bottom: 1px solid #eee;
    }
    .table th {
        text-align: left;
        color: #217dbb;
        font-weight: 600;
    }
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
        <li><a href="../auth/logout.php" class="btn-logout">Logout</a></li>
    </ul>
</nav>
<div class="dashboard-container">
    <header class="dashboard-header">
        <h1 style="color:#3498db; font-size:1.5rem; font-weight:700; text-align:center; margin-bottom:24px; width:100%;">Kelola Kategori</h1>
    </header>
    <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:15px;">
        <a href="tambah_kategori.php" class="btn">+ Tambah Kategori</a>
        <a href="dashboard.php" class="btn" style="background:#217dbb;">Kembali ke Dashboard</a>
    </div>
    <div class="table-wrapper">
        <table class="table">
            <thead>
                <tr>
                    <th>Nama Kategori</th>
                    <th>Aksi</th>
                </tr>
            </thead>
            <tbody>
                <?php while($c = mysqli_fetch_assoc($categories)): ?>
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
<script>
document.addEventListener("DOMContentLoaded", function() {
    var menuToggle = document.getElementById('menuToggle');
    var navbarMenu = document.getElementById('navbarMenu');
    menuToggle.addEventListener('click', function(e) {
        e.stopPropagation();
        navbarMenu.classList.toggle('show');
    });
    navbarMenu.querySelectorAll('a').forEach(function(link) {
        link.addEventListener('click', function() {
            if (window.innerWidth <= 600) navbarMenu.classList.remove('show');
        });
    });
    document.addEventListener('click', function(e) {
        if (window.innerWidth <= 600 && !navbarMenu.contains(e.target) && e.target !== menuToggle) {
            navbarMenu.classList.remove('show');
        }
    });
});
</script>
<!-- Footer -->
<footer class="footer">
    @Copyright by 23552011029_Fauzan Rizkika Kurnia_TIF RP 23 CNS B_UASWEB1
</footer>
</body>
</html>
