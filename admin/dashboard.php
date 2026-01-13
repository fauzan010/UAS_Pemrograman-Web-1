<?php
// proteksi admin
if (!isset($_COOKIE['user_id']) || $_COOKIE['role'] != 'admin') {
    header("Location: ../auth/login.php");
    exit;
}

include "../config/database.php";

// query total data
$total_produk   = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) AS total FROM products"))['total'] ?? 0;
$total_kategori = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) AS total FROM categories"))['total'] ?? 0;
$total_user     = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) AS total FROM users"))['total'] ?? 0;
$totalKategori = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) AS total FROM categories"))['total'] ?? 0;

// Query pesanan terbaru
$orders = mysqli_query($conn, "
    SELECT o.*, u.nama AS user_nama, p.nama_produk 
    FROM orders o
    JOIN users u ON o.user_id = u.id
    JOIN products p ON o.product_id = p.id
    ORDER BY o.tanggal DESC
    LIMIT 10
");

// Query total pesanan
$total_orders = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) AS total FROM orders"))['total'] ?? 0;
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Dashboard Admin - WorldBike</title>
    <meta name="viewport" content="width=device-width, initial-scale=1">
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
        max-width: 700px;
        width: 100%;
        margin: 48px auto 90px auto; /* margin-top lebih besar agar tidak nempel navbar */
        padding: 0 16px;
        box-sizing: border-box;
        background: #fff;
        border-radius: 18px;
        box-shadow: 0 8px 32px rgba(44,62,80,0.06);
    }
    .dashboard-header {
        display: flex;
        justify-content: space-between;
        align-items: center;
        margin-bottom: 30px;
    }
    .dashboard-header h1 {
        font-size: 2rem;
        color: #3498db;
        font-weight: 700;
    }
    .stats {
        display: grid;
        grid-template-columns: repeat(3, 1fr);
        gap: 24px;
        margin-bottom: 40px;
    }
    @media (max-width: 800px) {
        .stats { grid-template-columns: 1fr; }
    }
    .card {
        position: relative;
        overflow: hidden;
        background: radial-gradient(circle at top left, #eaf6ff 0%, #f8fafc 40%, #ffffff 100%);
        padding: 32px 18px 26px 18px;
        border-radius: 16px;
        box-shadow: 0 10px 26px rgba(44,62,80,0.12);
        text-align: center;
        transition: transform 0.25s ease, box-shadow 0.25s ease, background 0.25s ease;
        border: 1px solid rgba(52,152,219,0.12);
    }
    .card::before {
        content: "";
        position: absolute;
        top: -40px;
        right: -40px;
        width: 120px;
        height: 120px;
        background: radial-gradient(circle, rgba(52,152,219,0.18), transparent 65%);
        opacity: 0.7;
        pointer-events: none;
    }
    .card:hover {
        transform: translateY(-6px) scale(1.02);
        box-shadow: 0 18px 40px rgba(44,62,80,0.18);
        background: radial-gradient(circle at top left, #e0f1ff 0%, #f9fbff 40%, #ffffff 100%);
    }
    .card-icon {
        width: 52px;
        height: 52px;
        border-radius: 50%;
        margin: 0 auto 10px auto;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 1.6rem;
        background: linear-gradient(135deg, #3498db, #6dd5fa);
        color: #fff;
        box-shadow: 0 6px 18px rgba(52,152,219,0.35);
    }
    .card h3 {
        font-size: 1.05rem;
        color: #217dbb;
        margin-bottom: 8px;
        font-weight: 600;
    }
    .card p {
        font-size: 2rem;
        font-weight: 700;
        color: #1f3b57;
        margin: 0;
    }
    .actions {
        display: flex;
        gap: 18px;
        margin-bottom: 10px;
    }
    .btn {
        background: #3498db;
        color: #fff;
        padding: 12px 22px;
        border-radius: 8px;
        text-decoration: none;
        font-weight: 500;
        transition: background 0.2s;
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
    .orders-section {
        margin-top: 38px;
        background: #fff;
        border-radius: 18px;
        box-shadow: 0 8px 32px rgba(44,62,80,0.06);
        padding: 24px 18px 18px 18px;
    }
    .orders-section h2 {
        color: #3498db;
        font-size: 1.18rem;
        font-weight: 700;
        margin-bottom: 18px;
        text-align: left;
    }
    .orders-table {
        width: 100%;
        border-collapse: collapse;
        margin-bottom: 10px;
    }
    .orders-table th, .orders-table td {
        padding: 10px 8px;
        border-bottom: 1px solid #eee;
        font-size: 1rem;
        text-align: left;
    }
    .orders-table th {
        background: #f8fafc;
        color: #217dbb;
        font-weight: 600;
    }
    .orders-table td.status {
        font-weight: 600;
        text-transform: capitalize;
    }
    .orders-table td.status.pending { color: #e67e22; }
    .orders-table td.status.selesai { color: #27ae60; }
    .orders-table td.status.dibatalkan { color: #e74c3c; }
    @media (max-width: 800px) {
        .orders-section { padding: 10px 2vw; }
        .orders-table th, .orders-table td { font-size: 0.97rem; padding: 7px 4px; }
    }
    .stats .card-link {
        text-decoration: none;
        color: inherit;
        display: block;
        transition: box-shadow 0.2s, transform 0.2s;
    }
    .stats .card-link:focus .card,
    .stats .card-link:hover .card {
        box-shadow: 0 14px 32px rgba(52,152,219,0.13);
        transform: translateY(-6px) scale(1.03);
        background: #f0f8ff;
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
        <h1>Dashboard Admin</h1>
    </header>
    <section class="stats">
        <a href="produk.php" class="card-link" tabindex="0">
            <div class="card">
                <div class="card-icon">📦</div>
                <h3>Total Produk</h3>
                <p><?= $total_produk ?></p>
            </div>
        </a>
        <a href="kategori.php" class="card-link" tabindex="0">
            <div class="card">
                <div class="card-icon">🏷️</div>
                <h3>Total Kategori</h3>
                <p><?= $total_kategori ?></p>
            </div>
        </a>
        <a href="users.php" class="card-link" tabindex="0">
            <div class="card">
                <div class="card-icon">👥</div>
                <h3>Total User</h3>
                <p><?= $total_user ?></p>
            </div>
        </a>
        <a href="orders.php" class="card-link" tabindex="0">
            <div class="card">
                <div class="card-icon">🧾</div>
                <h3>Total Pesanan</h3>
                <p><?= $total_orders ?></p>
            </div>
        </a>
    </section>
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
