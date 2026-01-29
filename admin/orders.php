<?php
date_default_timezone_set('Asia/Jakarta');

if (!isset($_COOKIE['user_id']) || $_COOKIE['role'] != 'admin') {
    header("Location: ../auth/login.php");
    exit;
}
include "../config/database.php";

// Proses hapus pesanan
if (isset($_GET['hapus'])) {
    $oid = intval($_GET['hapus']);
    mysqli_query($conn, "DELETE FROM orders WHERE id=$oid");
    header("Location: orders.php");
    exit;
}

$orders = mysqli_query($conn, "
    SELECT o.*, u.nama AS user_nama, p.nama_produk 
    FROM orders o
    JOIN users u ON o.user_id = u.id
    JOIN products p ON o.product_id = p.id
    ORDER BY o.tanggal DESC
");
$orderCount = mysqli_num_rows($orders);
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Riwayat Pemesanan - WorldBike Admin</title>
    <meta name="viewport" content="width=device-width, initial-scale=1">
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
        font-family: "Segoe UI", sans-serif;
        color: #1f2d3d;
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
        left: 0;
        right: 0;
        z-index: 100;
    }
    .navbar .logo {
        font-size: 1.6rem;
        font-weight: 800;
        color: #3498db;
        letter-spacing: 1px;
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
        font-size: 0.98rem;
        font-weight: 600;
        padding: 9px 16px;
        border-radius: 6px;
        transition: background 0.2s, color 0.2s;
        display: block;
    }
    .navbar ul li a.active, .navbar ul li a:hover {
        background: #3498db;
        color: #fff;
        box-shadow: none;
    }
    .navbar ul li.nav-right { margin-left: auto; }
    .btn-logout {
        background: #e74c3c;
        color: #fff;
        padding: 9px 16px;
        border-radius: 12px;
        text-decoration: none;
        font-size: 0.98rem;
        font-weight: 600;
        margin-left: 6px;
        transition: background 0.2s, transform 0.2s;
        border: none;
        cursor: pointer;
    }
    .btn-logout:hover { background: #c0392b; transform: translateY(-1px); }
    .page-shell {
        max-width: 1100px;
        margin: 32px auto 96px auto;
        padding: 0 18px;
        box-sizing: border-box;
    }
    .panel {
        background: #fff;
        border-radius: 16px;
        box-shadow: 0 12px 32px rgba(44,62,80,0.10);
        border: 1px solid #e6eef7;
        padding: 20px 22px;
        margin-bottom: 18px;
    }
    .page-header {
        display: flex;
        flex-direction: column;
        gap: 4px;
        margin-bottom: 8px;
    }
    .eyebrow {
        font-size: 0.82rem;
        text-transform: uppercase;
        letter-spacing: 0.08em;
        color: #607286;
        font-weight: 800;
    }
    .title-row {
        display: flex;
        justify-content: space-between;
        align-items: flex-end;
        flex-wrap: wrap;
        gap: 12px;
    }
    .title-row h1 {
        font-size: 1.8rem;
        color: #1b3a57;
        margin: 0;
        font-weight: 800;
        letter-spacing: -0.02em;
    }
    .title-row p {
        margin: 0;
        color: #607286;
    }
    .actions {
        display: flex;
        gap: 10px;
        align-items: center;
        flex-wrap: wrap;
    }
    .badge-soft {
        background: #eaf6fb;
        color: #217dbb;
        padding: 8px 12px;
        border-radius: 999px;
        font-weight: 700;
        font-size: 0.95rem;
        border: 1px solid #d5e9f6;
    }
    .table-wrapper { overflow-x: auto; }
    .orders-table {
        width: 100%;
        min-width: 780px;
        border-collapse: separate;
        border-spacing: 0;
    }
    .orders-table thead th {
        background: #f1f6ff;
        color: #1f3b57;
        font-weight: 800;
        font-size: 0.96rem;
        border-bottom: 1px solid #dde7f5;
        padding: 12px 12px;
        text-transform: uppercase;
        letter-spacing: 0.06em;
    }
    .orders-table tbody td {
        padding: 12px 12px;
        border-bottom: 1px solid #eef2f7;
        vertical-align: middle;
        font-weight: 600;
        color: #1f2d3d;
        background: #fff;
    }
    .orders-table tbody tr:hover td { background: #f6faff; }
    .status-pill {
        display: inline-flex;
        align-items: center;
        gap: 6px;
        padding: 6px 10px;
        border-radius: 999px;
        font-weight: 700;
        font-size: 0.92rem;
        border: 1px solid transparent;
    }
    .status-pending { background: #fff7ed; color: #c2410c; border-color: #fed7aa; }
    .status-selesai, .status-sampai { background: #ecfdf3; color: #15803d; border-color: #bbf7d0; }
    .status-dibatalkan { background: #fff1f2; color: #b91c1c; border-color: #fecdd3; }
    .status-dikemas { background: #fff7ed; color: #c2410c; border-color: #fed7aa; }
    .status-dikirim { background: #eef2ff; color: #4338ca; border-color: #e0e7ff; }
    .actions-col {
        display: flex;
        gap: 8px;
        flex-wrap: wrap;
    }
    .btn-ghost {
        padding: 7px 14px;
        border-radius: 8px;
        border: none;
        text-decoration: none;
        font-weight: 600;
        color: #fff;
        background: #3498db;
        transition: transform 0.15s, background 0.2s;
        display: inline-block;
    }
    .btn-ghost:hover { background: #217dbb; transform: translateY(-1px); }
    .btn-ghost.danger { background: #e74c3c; }
    .btn-ghost.danger:hover { background: #c0392b; }
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
        <li><a href="kategori.php">Kategori</a></li>
        <li><a href="orders.php" class="active">Pemesanan</a></li>
        <li><a href="users.php">Pengguna</a></li>
        <li class="nav-right"><a href="../auth/logout.php" class="btn-logout">Logout</a></li>
    </ul>
</nav>
<div class="page-shell">
    <div class="panel">
        <div class="page-header">
            <span class="eyebrow">Data</span>
            <div class="title-row">
                <div>
                    <h1>Kelola Pemesanan</h1>
                    <p>Lihat riwayat pesanan, status, dan aksi cepat.</p>
                </div>
                <div class="actions">
                    <span class="badge-soft">Total: <?= $orderCount ?></span>
                </div>
            </div>
        </div>
    </div>

    <div class="panel">
        <div class="table-wrapper">
            <table class="orders-table">
                <thead>
                    <tr>
                        <th>Tanggal</th>
                        <th>Pelanggan</th>
                        <th>Produk</th>
                        <th>Qty</th>
                        <th>Harga</th>
                        <th>Metode</th>
                        <th>Status</th>
                        <th>Aksi</th>
                    </tr>
                </thead>
                <tbody>
                <?php if ($orderCount > 0): ?>
                    <?php while($o = mysqli_fetch_assoc($orders)): ?>
                    <tr>
                        <td><?= date('d-m-Y H:i', strtotime($o['tanggal'])) ?></td>
                        <td><?= htmlspecialchars($o['user_nama']) ?></td>
                        <td><?= htmlspecialchars($o['nama_produk']) ?></td>
                        <td><?= $o['qty'] ?></td>
                        <td>Rp <?= number_format($o['harga']) ?></td>
                        <td><?= htmlspecialchars(ucwords($o['metode_bayar'])) ?></td>
                        <td>
                            <?php $statusClass = 'status-' . strtolower(str_replace(' ', '', $o['status'])); ?>
                            <span class="status-pill <?= $statusClass ?>"><?= htmlspecialchars($o['status']) ?></span>
                        </td>
                        <td>
                            <div class="actions-col">
                                <a href="orders.php?hapus=<?= $o['id'] ?>" class="btn-ghost danger" onclick="return confirm('Hapus pesanan ini?')">Hapus</a>
                            </div>
                        </td>
                    </tr>
                    <?php endwhile; ?>
                <?php else: ?>
                    <tr><td colspan="8" style="text-align:center;color:#888;">Belum ada pesanan.</td></tr>
                <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>
<!-- Footer -->
<footer class="footer">
    @Copyright by 23552011029_Fauzan Rizkika Kurnia_TIF RP 23 CNS B_UASWEB1
</footer>

<!-- Bootstrap JS -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"
        integrity="sha384-YvpcrYf0tY3lHB60NNkmXc5s9fDVZLESaAA55NDzOxhy9GkcIdslK1eN7N6jIeHz"
        crossorigin="anonymous"></script>
</body>
</html>
