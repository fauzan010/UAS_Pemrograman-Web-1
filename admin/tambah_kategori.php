<?php
if (!isset($_COOKIE['user_id']) || $_COOKIE['role'] != 'admin') {
    header("Location: ../auth/login.php");
    exit;
}
include "../config/database.php";

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nama = $_POST['nama'];

    mysqli_query($conn, "INSERT INTO categories (nama_kategori) VALUES ('$nama')");
    header("Location: kategori.php");
    exit;
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Tambah Kategori</title>
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
    .form-card {
        background: #f8fafc;
        padding: 16px;
        border-radius: 12px;
        box-shadow: 0 8px 20px rgba(52,152,219,0.07);
    }
    label {
        font-weight: 500;
        color: #217dbb;
        margin-bottom: 6px;
        display: block;
    }
    input[type="text"] {
        width: 100%;
        padding: 10px;
        margin: 8px 0 15px;
        border-radius: 6px;
        border: 1px solid #ddd;
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
    @media (max-width: 600px) {
        .navbar {
            flex-direction: column;
            gap: 8px;
            padding: 10px 2vw;
        }
        .dashboard-container {
            max-width: 98vw;
            width: 100vw;
            padding: 0 1vw;
            margin: 10px auto 90px auto;
        }
        .navbar ul {
            flex-direction: column;
            gap: 4px;
            width: 100%;
        }
        .navbar ul li {
            width: 100%;
        }
        .navbar ul li a, .btn-logout {
            width: 100%;
            text-align: left;
        }
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
        <li><a href="../auth/logout.php" class="btn-logout">Logout</a></li>
    </ul>
</nav>
<div class="dashboard-container">
    <h1>Tambah Kategori</h1>
    <form method="POST" class="form-card">
        <label>Nama Kategori</label>
        <input type="text" name="nama" required>
        <button class="btn">Simpan</button>
        <a href="kategori.php" class="btn danger">Batal</a>
    </form>
</div>
<!-- Footer -->
<footer class="footer">
    @Copyright by 23552011029_Fauzan Rizkika Kurnia_TIF RP 23 CNS B_UASWEB1
</footer>
</body>
</html>
