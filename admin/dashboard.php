<?php
include "../config/database.php";

$q_produk = mysqli_query($conn, "SELECT COUNT(*) as total FROM products");
$total_produk = mysqli_fetch_assoc($q_produk)['total'];

$q_kategori = mysqli_query($conn, "SELECT COUNT(*) as total FROM categories");
$total_kategori = mysqli_fetch_assoc($q_kategori)['total'];

$q_user = mysqli_query($conn, "SELECT COUNT(*) as total FROM users");
$total_user = mysqli_fetch_assoc($q_user)['total'];

$q_orders = mysqli_query($conn, "SELECT COUNT(*) as total FROM orders");
$total_orders = mysqli_fetch_assoc($q_orders)['total'];

$produk_terbaru = mysqli_query($conn, "SELECT nama_produk, created_at FROM products ORDER BY created_at DESC LIMIT 5");

$produk_per_kategori = mysqli_query($conn, "
    SELECT categories.nama_kategori, COUNT(products.id) as jumlah
    FROM categories
    LEFT JOIN products ON products.category_id = categories.id
    GROUP BY categories.id
");

$aktivitas = [];
$q_aktivitas = mysqli_query($conn, "SELECT waktu, aksi FROM aktivitas_admin ORDER BY waktu DESC LIMIT 6");
while ($a = mysqli_fetch_assoc($q_aktivitas)) { $aktivitas[] = $a; }

$latest_orders = mysqli_query($conn, "
    SELECT o.id, o.tanggal, o.qty, o.harga, o.status, u.nama AS user_nama, p.nama_produk
    FROM orders o
    JOIN users u ON o.user_id = u.id
    JOIN products p ON o.product_id = p.id
    ORDER BY o.tanggal DESC
    LIMIT 6
");
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Dashboard Admin - WorldBike</title>
    <meta name="viewport" content="width=device-width, initial-scale=1">
            <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css"
                rel="stylesheet"
                integrity="sha384-QWTKZyjpPEjISv5WaRU9OFeRpok6YctnYmDr5pNlyT2bRjXh0JMhjY6hW+ALEwIH"
                crossorigin="anonymous">
    <link rel="stylesheet" href="../assets/css/style.css">
    <style>
    body {
        background: linear-gradient(120deg, #e0eafc 0%, #cfdef3 100%);
        min-height: 100vh;
        margin: 0;
        padding: 0;
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
    .navbar .logo { font-size: 1.7rem; font-weight: 800; color: #3498db; letter-spacing: 1.5px; margin: 0; }
    .btn-logout { background: #e74c3c; color: #fff; padding: 8px 18px; border-radius: 8px; border: none; font-weight: 700; text-decoration: none; }
    .btn-logout:hover { background: #c0392b; color: #fff; }

    .page { max-width: 1200px; margin: 26px auto 90px auto; padding: 0 16px; }
    .dashboard-card { background: #fff; border-radius: 18px; box-shadow: 0 12px 36px rgba(44,62,80,0.10); padding: 22px 20px; margin-bottom: 18px; position: relative; overflow: hidden; border: 1px solid #e8edf3; }
    .dashboard-header { display: flex; justify-content: space-between; gap: 14px; flex-wrap: wrap; align-items: center; }
    .eyebrow { font-size: 0.85rem; letter-spacing: 0.08em; text-transform: uppercase; color: #5f7082; font-weight: 800; }
    .title { font-size: 1.8rem; font-weight: 800; color: #1b3a57; margin: 4px 0; }
    .muted { color: #5f7082; margin: 0; }
    .quick-actions { display: flex; gap: 10px; flex-wrap: wrap; }
    .pill-btn { display: inline-flex; align-items: center; gap: 8px; padding: 10px 14px; border-radius: 12px; border: 1px solid #d7e4f2; background: #f4f8fc; color: #1b3a57; font-weight: 700; text-decoration: none; transition: transform 0.15s, box-shadow 0.2s, border 0.2s; }
    .pill-btn.primary { background: linear-gradient(135deg, #3498db, #217dbb); color: #fff; border: none; box-shadow: 0 12px 30px rgba(52,152,219,0.24); }
    .pill-btn:hover { transform: translateY(-1px); box-shadow: 0 10px 26px rgba(44,62,80,0.12); }

    .stats { display: grid; grid-template-columns: repeat(auto-fit, minmax(190px, 1fr)); gap: 14px; }
    .stat-card { position: relative; background: radial-gradient(circle at 20% 20%, #eaf6ff, #ffffff 55%); padding: 20px 18px; border-radius: 16px; border: 1px solid #e2ecf5; box-shadow: 0 10px 26px rgba(44,62,80,0.12); transition: transform 0.2s, box-shadow 0.2s; }
    .stat-card:hover { transform: translateY(-4px); box-shadow: 0 16px 34px rgba(44,62,80,0.16); }
    .stat-icon { width: 44px; height: 44px; border-radius: 50%; display: grid; place-items: center; background: linear-gradient(135deg, #3498db, #6dd5fa); color: #fff; font-weight: 800; margin-bottom: 8px; }
    .stat-label { color: #217dbb; font-weight: 700; margin: 0; }
    .stat-value { font-size: 1.8rem; font-weight: 900; color: #1b3a57; margin: 0; }

    .grid-two { display: grid; grid-template-columns: 1.4fr 1fr; gap: 14px; }
    @media (max-width: 960px) { .grid-two { grid-template-columns: 1fr; } }
    .panel-title { font-size: 1.12rem; font-weight: 800; color: #1b3a57; margin: 0 0 10px 0; }
    .mini-list ul, .activity-list ul { margin: 0; padding-left: 14px; }
    .mini-list li, .activity-list li { margin-bottom: 6px; color: #1f2d3d; font-weight: 600; }
    .mini-muted { color: #7a8694; font-size: 0.94rem; }

    .orders-table { width: 100%; border-collapse: collapse; }
    .orders-table th, .orders-table td { padding: 10px 8px; border-bottom: 1px solid #eef2f7; font-size: 0.98rem; text-align: left; }
    .orders-table th { background: #f8fafc; color: #217dbb; font-weight: 700; }
    .badge-status { padding: 6px 10px; border-radius: 999px; font-weight: 700; font-size: 0.9rem; }
    .st-pending { background: #fff4e5; color: #d35400; }
    .st-dikemas { background: #fff4e5; color: #d35400; }
    .st-dikirim { background: #eaf6fb; color: #217dbb; }
    .st-sampai, .st-selesai { background: #e8f8f0; color: #1e8a4b; }
    .st-dibatalkan { background: #ffecec; color: #c0392b; }

    .bar-chart-row { display: flex; align-items: center; gap: 10px; margin-bottom: 8px; }
    .bar-label { min-width: 120px; font-weight: 700; color: #217dbb; }
    .bar { height: 16px; border-radius: 10px; background: linear-gradient(90deg, #4fc3f7 0%, #29b6f6 100%); flex: 1; }
    .bar-value { min-width: 26px; text-align: right; font-weight: 700; color: #1b3a57; }

    .activity-list li span { color: #7a8694; font-size: 0.92rem; }
    .category-list { padding-left: 0; margin: 0; list-style: none; }
    .category-list li { display: flex; justify-content: space-between; padding: 10px 0; border-bottom: 1px solid #eef2f7; font-weight: 600; color: #1f2d3d; }
    .category-list li:last-child { border-bottom: none; }
    .badge-count { background: #eaf6fb; color: #217dbb; padding: 6px 10px; border-radius: 999px; font-weight: 700; min-width: 38px; text-align: center; }
    </style>
</head>
<body>
<nav class="navbar">
    <div class="logo">WorldBike Admin</div>
    <a href="../auth/logout.php" class="btn-logout">Logout</a>
</nav>
<div class="page">
    <div class="dashboard-card">
        <div class="dashboard-header">
            <div>
                <div class="eyebrow">Ringkasan</div>
                <div class="title">Dashboard Admin</div>
                <p class="muted">Pantau produk, pesanan, user, dan kategori WorldBike.</p>
            </div>
            <div class="quick-actions">
                <a class="pill-btn primary" href="produk.php">Kelola Produk</a>
                <a class="pill-btn" href="kategori.php">Kelola Kategori</a>
                <a class="pill-btn" href="orders.php">Lihat Pesanan</a>
                <a class="pill-btn" href="users.php">Data Pengguna</a>
                <a class="pill-btn" href="tambah_produk.php">+ Tambah Produk</a>
            </div>
        </div>
        <div class="stats">
            <div class="stat-card">
                <div class="stat-icon">📦</div>
                <p class="stat-label">Total Produk</p>
                <p class="stat-value"><?= $total_produk ?></p>
            </div>
            <div class="stat-card">
                <div class="stat-icon">🏷️</div>
                <p class="stat-label">Total Kategori</p>
                <p class="stat-value"><?= $total_kategori ?></p>
            </div>
            <div class="stat-card">
                <div class="stat-icon">👥</div>
                <p class="stat-label">Total User</p>
                <p class="stat-value"><?= $total_user ?></p>
            </div>
            <div class="stat-card">
                <div class="stat-icon">🧾</div>
                <p class="stat-label">Total Pesanan</p>
                <p class="stat-value"><?= $total_orders ?></p>
            </div>
        </div>
    </div>

    <div class="grid-two">
        <div class="dashboard-card">
            <div class="panel-title">Pesanan Terbaru</div>
            <table class="orders-table">
                <thead>
                    <tr>
                        <th>Tanggal</th>
                        <th>Produk</th>
                        <th>User</th>
                        <th>Qty</th>
                        <th>Status</th>
                    </tr>
                </thead>
                <tbody>
                <?php if (mysqli_num_rows($latest_orders) > 0): ?>
                    <?php while ($o = mysqli_fetch_assoc($latest_orders)): ?>
                    <?php $status = strtolower($o['status']); ?>
                    <tr>
                        <td><?= date('d/m H:i', strtotime($o['tanggal'])) ?></td>
                        <td><?= htmlspecialchars($o['nama_produk']) ?></td>
                        <td><?= htmlspecialchars($o['user_nama']) ?></td>
                        <td><?= $o['qty'] ?></td>
                        <td><span class="badge-status st-<?= $status ?>"><?= htmlspecialchars($o['status']) ?></span></td>
                    </tr>
                    <?php endwhile; ?>
                <?php else: ?>
                    <tr><td colspan="5" style="text-align:center;color:#7a8694;">Belum ada pesanan.</td></tr>
                <?php endif; ?>
                </tbody>
            </table>
        </div>
        <div class="dashboard-card mini-list">
            <div class="panel-title">Produk Terakhir Ditambahkan</div>
            <ul>
                <?php if (mysqli_num_rows($produk_terbaru) > 0): ?>
                    <?php while ($row = mysqli_fetch_assoc($produk_terbaru)): ?>
                        <li>
                            <?= htmlspecialchars($row['nama_produk']) ?>
                            <span class="mini-muted">(<?= date('d M Y H:i', strtotime($row['created_at'])) ?>)</span>
                        </li>
                    <?php endwhile; ?>
                <?php else: ?>
                    <li class="mini-muted">Belum ada produk terbaru.</li>
                <?php endif; ?>
            </ul>
        </div>
    </div>

    <div class="grid-two" style="grid-template-columns: 1fr 1fr;">
        <div class="dashboard-card">
            <div class="panel-title">Produk per Kategori</div>
            <?php
            $kategori_data = [];
            while ($row = mysqli_fetch_assoc($produk_per_kategori)) {
                $kategori_data[] = $row;
            }
            if (count($kategori_data) === 0): ?>
                <p class="muted">Belum ada kategori atau produk.</p>
            <?php else: ?>
                <ul class="category-list">
                    <?php foreach ($kategori_data as $row): ?>
                        <li>
                            <span><?= htmlspecialchars($row['nama_kategori']) ?></span>
                            <span class="badge-count"><?= $row['jumlah'] ?></span>
                        </li>
                    <?php endforeach; ?>
                </ul>
            <?php endif; ?>
        </div>
        <div class="dashboard-card activity-list">
            <div class="panel-title">Aktivitas Admin</div>
            <ul>
                <?php if (count($aktivitas) > 0): ?>
                    <?php foreach ($aktivitas as $a): ?>
                        <li><span><?= $a['waktu'] ?></span> - <?= htmlspecialchars($a['aksi']) ?></li>
                    <?php endforeach; ?>
                <?php else: ?>
                    <li class="mini-muted">Belum ada aktivitas.</li>
                <?php endif; ?>
            </ul>
        </div>
    </div>
</div>
<footer class="footer" style="text-align:center; padding:18px 0; background:#f4f6f8; color:#888; font-size:15px; position:fixed; left:0; right:0; bottom:0;">
    @Copyright by 23552011029_Fauzan Rizkika Kurnia_TIF RP 23 CNS B_UASWEB1
</footer>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"
        integrity="sha384-YvpcrYf0tY3lHB60NNkmXc5s9fDVZLESaAA55NDzOxhy9GkcIdslK1eN7N6jIeHz"
        crossorigin="anonymous"></script>
</body>
</html>
