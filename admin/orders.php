<?php
if (!isset($_COOKIE['user_id']) || $_COOKIE['role'] != 'admin') {
    header("Location: ../auth/login.php");
    exit;
}
include "../config/database.php";

$orders = mysqli_query($conn, "
    SELECT o.*, u.nama AS user_nama, p.nama_produk 
    FROM orders o
    JOIN users u ON o.user_id = u.id
    JOIN products p ON o.product_id = p.id
    ORDER BY o.tanggal DESC
");
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Riwayat Pemesanan - WorldBike Admin</title>
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
    .orders-container {
        max-width: 900px;
        width: 100%;
        margin: 48px auto 90px auto;
        padding: 0 16px;
        box-sizing: border-box;
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
        .orders-container { padding: 10px 2vw; }
        .orders-table th, .orders-table td { font-size: 0.97rem; padding: 7px 4px; }
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
<div class="orders-container">
    <h1 style="color:#3498db; font-size:1.5rem; font-weight:700; text-align:center; margin-bottom:24px;">Riwayat Pemesanan</h1>
    <div style="display:flex;justify-content:flex-end;align-items:center;margin-bottom:18px;">
        <a href="dashboard.php" class="btn" style="background:#217dbb;">Kembali ke Dashboard</a>
    </div>
    <div class="table-wrapper">
        <table class="orders-table">
            <thead>
                <tr>
                    <th>Tanggal</th>
                    <th>User</th>
                    <th>Produk</th>
                    <th>Qty</th>
                    <th>Harga</th>
                    <th>Metode</th>
                    <th>Status</th>
                </tr>
            </thead>
            <tbody>
            <?php if (mysqli_num_rows($orders) > 0): ?>
                <?php while($o = mysqli_fetch_assoc($orders)): ?>
                <tr>
                    <td><?= date('d-m-Y H:i', strtotime($o['tanggal'])) ?></td>
                    <td><?= htmlspecialchars($o['user_nama']) ?></td>
                    <td><?= htmlspecialchars($o['nama_produk']) ?></td>
                    <td><?= $o['qty'] ?></td>
                    <td>Rp <?= number_format($o['harga']) ?></td>
                    <td><?= htmlspecialchars(ucwords($o['metode_bayar'])) ?></td>
                    <td class="status <?= strtolower($o['status']) ?>"><?= htmlspecialchars($o['status']) ?></td>
                </tr>
                <?php endwhile; ?>
            <?php else: ?>
                <tr><td colspan="7" style="text-align:center;color:#888;">Belum ada pesanan.</td></tr>
            <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>
<!-- Footer -->
<footer class="footer">
    @Copyright by 23552011029_Fauzan Rizkika Kurnia_TIF RP 23 CNS B_UASWEB1
</footer>
</body>
</html>
