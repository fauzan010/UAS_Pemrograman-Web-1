<?php
session_start();
include "../config/database.php";

// Sinkronkan session dari cookie jika ada
if (!isset($_SESSION['user_id']) && isset($_COOKIE['user_id'])) {
    $_SESSION['user_id'] = $_COOKIE['user_id'];
    $_SESSION['role'] = $_COOKIE['role'] ?? 'user';
}

// Jika belum login, arahkan ke halaman login
if (!isset($_SESSION['user_id'])) {
    header("Location: ../auth/login.php?redirect=user/profile.php");
    exit;
}

$uid = intval($_SESSION['user_id']);
$user_q = mysqli_query($conn, "SELECT nama, email, role, photo FROM users WHERE id=$uid");
$user = mysqli_fetch_assoc($user_q);
if (!$user) {
    header("Location: ../auth/logout.php");
    exit;
}

// Hitung pesanan
$order_count = 0;
$order_q = mysqli_query($conn, "SELECT COUNT(*) AS total FROM orders WHERE user_id=$uid");
if ($order_q) {
    $row = mysqli_fetch_assoc($order_q);
    $order_count = intval($row['total']);
}

$order_list = mysqli_query($conn, "
    SELECT o.id, o.tanggal, o.status, o.qty, o.harga, o.metode_bayar, p.nama_produk
    FROM orders o
    JOIN products p ON o.product_id = p.id
    WHERE o.user_id = $uid
    ORDER BY o.tanggal DESC
");

$cart_count = !empty($_SESSION['cart']) ? array_sum(array_column($_SESSION['cart'], 'qty')) : 0;
$user_name = $user['nama'] ?? 'Pengguna';
$user_email = $user['email'] ?? '-';
$user_role = $user['role'] ?? 'user';
$user_photo = $user['photo'] ?? '';
$initial = strtoupper(substr(trim($user_name), 0, 1));
if ($initial === '') { $initial = 'U'; }
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Profil Saya | WorldBike</title>
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css"
          rel="stylesheet"
          integrity="sha384-QWTKZyjpPEjISv5WaRU9OFeRpok6YctnYmDr5pNlyT2bRjXh0JMhjY6hW+ALEwIH"
          crossorigin="anonymous">
    <link rel="stylesheet" href="../assets/css/style.css">
    <style>
    body {
        background: radial-gradient(circle at 12% 20%, rgba(52,152,219,0.12), transparent 30%),
                    radial-gradient(circle at 85% 10%, rgba(46,204,113,0.12), transparent 28%),
                    linear-gradient(120deg, #e0eafc 0%, #cfdef3 100%);
        min-height: 100vh;
        padding-top: 90px;
    }
    .navbar {
        display: flex;
        justify-content: space-between;
        align-items: center;
        padding: 22px 7vw;
        background: #fff;
        box-shadow: 0 2px 18px rgba(44,62,80,0.07);
        position: fixed;
        top: 0; left: 0; right: 0;
        z-index: 1000;
        width: 100%;
    }
    .navbar .logo { font-size: 2rem; font-weight: bold; color: #3498db; letter-spacing: 2px; }
    .navbar ul {
        display: flex;
        gap: 32px;
        list-style: none;
        margin: 0;
        padding: 0;
        align-items: center;
    }
    .navbar ul li a {
        font-size: 1.1rem;
        font-weight: 500;
        padding: 6px 14px;
        border-radius: 6px;
        color: #2c3e50;
        display: block;
        transition: background 0.2s, color 0.2s;
        text-decoration: none;
    }
    .navbar ul li a:hover, .navbar ul li a.active { background: #3498db; color: #fff; }
    .navbar ul li a.cart-link { display: inline-flex; align-items: center; gap: 8px; padding: 6px 14px; justify-content: center; min-width: 110px; }
    .navbar ul li.nav-right { margin-left: auto; }
    .profile-menu { position: relative; }
    .profile-trigger {
        display: inline-flex;
        align-items: center;
        gap: 8px;
        padding: 6px 14px;
        background: #eaf6fb;
        color: #3498db;
        font-weight: 600;
        border: none;
        border-radius: 6px;
        cursor: pointer;
        transition: background 0.2s, color 0.2s;
    }
    .profile-trigger:hover { background: #d9edf9; }
    .profile-dropdown {
        position: absolute;
        right: 0;
        top: 110%;
        background: #fff;
        border: 1px solid #e8edf3;
        border-radius: 10px;
        box-shadow: 0 10px 24px rgba(44,62,80,0.12);
        padding: 6px 0;
        min-width: 180px;
        display: none;
        z-index: 20;
    }
    .profile-dropdown.show { display: block; }
    .profile-dropdown a {
        display: block;
        padding: 10px 14px;
        color: #1f2d3d;
        text-decoration: none;
        font-weight: 600;
        transition: background 0.15s, color 0.15s;
    }
    .profile-dropdown a:hover { background: #f1f6ff; color: #217dbb; }
    .profile-dropdown a.logout { color: #b91c1c; }
    .profile-dropdown a.logout:hover { background: #fff1f2; }

    .profile-wrapper {
        max-width: 1080px;
        margin: 0 auto;
        padding: 0 7vw 90px;
        position: relative;
    }
    .blur-dot {
        position: absolute;
        width: 220px;
        height: 220px;
        filter: blur(60px);
        opacity: 0.35;
        z-index: 0;
    }
    .dot-a { top: 40px; left: -60px; background: #8ad4ff; }
    .dot-b { bottom: 20px; right: -70px; background: #a6f3ce; }

    .profile-banner {
        position: relative;
        background: linear-gradient(135deg, #2d82d8, #46c2ff);
        color: #fff;
        border-radius: 18px;
        box-shadow: 0 18px 44px rgba(45,130,216,0.28);
        padding: 22px 24px;
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 16px;
        overflow: hidden;
        z-index: 1;
    }
    .profile-banner::after {
        content: '';
        position: absolute;
        inset: 0;
        background: radial-gradient(circle at 90% 20%, rgba(255,255,255,0.18), transparent 45%);
        z-index: 0;
    }
    .banner-left { display: flex; align-items: center; gap: 14px; position: relative; z-index: 1; }
    .avatar-photo {
        width: 76px;
        height: 76px;
        border-radius: 50%;
        overflow: hidden;
        border: 2px solid rgba(255,255,255,0.6);
        box-shadow: 0 10px 28px rgba(0,0,0,0.18);
        background: rgba(255,255,255,0.16);
    }
    .avatar-photo img { width: 100%; height: 100%; object-fit: cover; display: block; }
    .avatar-circle {
        width: 68px;
        height: 68px;
        border-radius: 50%;
        background: rgba(255,255,255,0.18);
        border: 2px solid rgba(255,255,255,0.6);
        display: grid;
        place-items: center;
        font-weight: 800;
        font-size: 1.4rem;
        color: #fff;
        text-transform: uppercase;
    }
    .eyebrow { font-size: 0.85rem; letter-spacing: 0.08em; text-transform: uppercase; opacity: 0.85; font-weight: 700; }
    .banner-name { font-size: 1.6rem; font-weight: 800; margin: 2px 0; }
    .banner-email { opacity: 0.9; font-size: 1rem; }
    .chip-row { display: flex; flex-wrap: wrap; gap: 8px; margin-top: 8px; }
    .chip { background: rgba(255,255,255,0.18); padding: 6px 10px; border-radius: 999px; font-weight: 700; color: #fff; border: 1px solid rgba(255,255,255,0.25); }
    .chip.soft { background: rgba(255,255,255,0.08); }
    .banner-stats { display: flex; gap: 12px; position: relative; z-index: 1; }
    .stat-pill {
        min-width: 120px;
        background: rgba(255,255,255,0.15);
        border: 1px solid rgba(255,255,255,0.28);
        border-radius: 12px;
        padding: 12px 14px;
        box-shadow: 0 10px 24px rgba(0,0,0,0.12);
        backdrop-filter: blur(4px);
    }
    .stat-pill span { display: block; font-size: 0.8rem; letter-spacing: 0.05em; opacity: 0.9; text-transform: uppercase; }
    .stat-pill strong { display: block; font-size: 1.5rem; font-weight: 900; }

    .profile-grid {
        margin-top: 18px;
        display: grid;
        grid-template-columns: 1fr;
        gap: 16px;
        position: relative;
        z-index: 1;
    }
    .card-base {
        background: #fff;
        border-radius: 16px;
        box-shadow: 0 14px 36px rgba(44,62,80,0.1);
        padding: 20px 18px;
        position: relative;
        overflow: hidden;
        border: 1px solid #e8edf3;
    }
    .card-head { display: flex; justify-content: space-between; align-items: center; gap: 10px; }
    .card-head h3 { margin: 2px 0 0 0; font-size: 1.15rem; color: #1b3a57; font-weight: 800; }
    .muted { color: #5f7082; font-size: 0.95rem; }
    .info-list { display: flex; flex-direction: column; gap: 12px; margin-top: 14px; }
    .info-row {
        display: flex;
        justify-content: space-between;
        gap: 12px;
        align-items: center;
        background: #f8fafc;
        border-radius: 12px;
        padding: 12px 14px;
        border: 1px solid #eef2f7;
    }
    .info-row .label { color: #5b6b7c; font-weight: 700; }
    .info-row .value { color: #12243a; font-weight: 800; }
    .stat-grid { display: grid; grid-template-columns: repeat(2, minmax(0, 1fr)); gap: 10px; margin-top: 12px; }
    .mini-stat {
        background: linear-gradient(135deg, #f8fbff, #eef2f7);
        border-radius: 12px;
        padding: 12px;
        border: 1px solid #e6edf5;
    }
    .mini-stat h5 { margin: 0 0 4px 0; color: #217dbb; font-size: 0.95rem; font-weight: 800; }
    .mini-stat .num { font-size: 1.2rem; font-weight: 800; color: #0f6abf; }
    .mini-stat p { margin: 2px 0 0 0; color: #5f7082; font-size: 0.92rem; }

    .order-table {
        width: 100%;
        border-collapse: separate;
        border-spacing: 0;
        margin-top: 12px;
    }
    .order-table thead th {
        background: #f1f6ff;
        color: #1f3b57;
        font-weight: 800;
        font-size: 0.95rem;
        border-bottom: 1px solid #dde7f5;
        padding: 10px 10px;
        text-transform: uppercase;
        letter-spacing: 0.04em;
    }
    .order-table tbody td {
        padding: 10px 10px;
        border-bottom: 1px solid #eef2f7;
        font-weight: 600;
        color: #1f2d3d;
    }
    .order-table tbody tr:hover td { background: #f6faff; }
    .status-pill {
        display: inline-flex;
        align-items: center;
        gap: 6px;
        padding: 6px 10px;
        border-radius: 999px;
        font-weight: 700;
        font-size: 0.9rem;
        border: 1px solid transparent;
    }
    .st-dikemas { background: #fff7ed; color: #c2410c; border-color: #fed7aa; }
    .st-dikirim { background: #eef2ff; color: #4338ca; border-color: #e0e7ff; }
    .st-sampai, .st-selesai { background: #ecfdf3; color: #15803d; border-color: #bbf7d0; }
    .st-dibatalkan { background: #fff1f2; color: #b91c1c; border-color: #fecdd3; }
    .st-pending { background: #fff7ed; color: #c2410c; border-color: #fed7aa; }

    .action-card .action-buttons {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(180px, 1fr));
        gap: 10px;
        margin-top: 14px;
    }
    .cta-btn {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        gap: 8px;
        padding: 12px 14px;
        border-radius: 12px;
        text-decoration: none;
        font-weight: 700;
        border: none;
        transition: transform 0.15s, box-shadow 0.2s;
        cursor: pointer;
    }
    .cta-btn.primary { background: linear-gradient(135deg, #3498db, #217dbb); color: #fff; box-shadow: 0 12px 30px rgba(52,152,219,0.25); }
    .cta-btn.soft { background: #f4f7fb; color: #1b3a57; border: 1px solid #e1e7ef; }
    .cta-btn.danger { background: linear-gradient(135deg, #e74c3c, #c0392b); color: #fff; box-shadow: 0 12px 30px rgba(231,76,60,0.22); }
    .cta-btn:hover { transform: translateY(-1px); box-shadow: 0 14px 34px rgba(44,62,80,0.16); }

    .quick-note {
        margin-top: 12px;
        padding: 12px 14px;
        border-radius: 12px;
        background: #f8fafc;
        border: 1px solid #e8edf3;
        color: #4a5663;
        font-weight: 600;
    }

    .footer {
        text-align: center;
        padding: 18px 0;
        background: #f4f6f8;
        color: #030000;
        font-size: 15px;
        margin-top: 30px;
    }

    @media (max-width: 900px) {
        .profile-grid { grid-template-columns: 1fr; }
        .profile-banner { flex-direction: column; align-items: flex-start; }
        .banner-stats { width: 100%; }
    }
    @media (max-width: 600px) {
        .navbar { padding: 12px 3vw; }
        .navbar .logo { font-size: 1.4rem; }
        .navbar ul { gap: 12px; }
        .navbar ul li.nav-right { margin-left: 0; }
        .profile-wrapper { padding: 0 5vw 70px; }
        .profile-banner { padding: 18px; }
        .avatar-circle { width: 60px; height: 60px; }
        .profile-grid { gap: 12px; }
    }
    </style>
</head>
<body>
<nav class="navbar">
    <div class="logo">WorldBike</div>
    <ul>
        <li><a href="../index.php">Home</a></li>
        <li><a href="index.php">Marketplace</a></li>
        <li><a href="cart.php" class="cart-link">Keranjang</a></li>
        <li class="nav-right profile-menu">
            <button class="profile-trigger" id="profileMenuBtn">Hi, <?= htmlspecialchars($user_name) ?> ▾</button>
            <div class="profile-dropdown" id="profileDropdown">
                <a href="profile.php">Kunjungi Profil</a>
                <a href="../auth/logout.php" class="logout">Logout</a>
            </div>
        </li>
    </ul>
</nav>
<div class="profile-wrapper">
    <div class="blur-dot dot-a"></div>
    <div class="blur-dot dot-b"></div>

    <div class="profile-banner">
        <div class="banner-left">
            <?php if (!empty($user_photo)): ?>
                <div class="avatar-photo"><img src="../assets/img/avatars/<?= htmlspecialchars($user_photo) ?>" alt="Foto profil"></div>
            <?php else: ?>
                <div class="avatar-circle"><?= htmlspecialchars($initial) ?></div>
            <?php endif; ?>
            <div>
                <div class="eyebrow">Profil Pengguna</div>
                <div class="banner-name"><?= htmlspecialchars($user_name) ?></div>
                <div class="banner-email"><?= htmlspecialchars($user_email) ?></div>
                <div class="chip-row">
                    <span class="chip">Role: <?= htmlspecialchars($user_role) ?></span>
                    <span class="chip soft">ID #<?= $uid ?></span>
                </div>
            </div>
        </div>
        <div class="banner-stats">
            <div class="stat-pill"><span>Pesanan</span><strong><?= $order_count ?></strong></div>
            <div class="stat-pill"><span>Keranjang</span><strong><?= $cart_count ?></strong></div>
        </div>
    </div>

    <?php if (isset($_GET['updated'])): ?>
        <div class="alert alert-success" role="alert" style="margin-top:12px; font-weight:700;">
            Profil berhasil diperbarui.
        </div>
    <?php endif; ?>

    <div class="profile-grid">
        <div class="card-base info-card">
            <div class="card-head">
                <div>
                    <div class="eyebrow">Ringkasan</div>
                    <h3>Data Akun</h3>
                </div>
                <a href="edit_profile.php" class="cta-btn primary" style="font-size:0.95rem; padding:10px 14px;">Edit Profil</a>
            </div>
            <p class="muted">Detail singkat akunmu.</p>
            <div class="info-list">
                <div class="info-row"><span class="label">Nama</span><span class="value"><?= htmlspecialchars($user_name) ?></span></div>
                <div class="info-row"><span class="label">Email</span><span class="value"><?= htmlspecialchars($user_email) ?></span></div>
                <div class="info-row"><span class="label">Role</span><span class="value"><?= htmlspecialchars($user_role) ?></span></div>
                <div class="info-row"><span class="label">Foto</span><span class="value"><?= !empty($user_photo) ? 'Ada' : 'Belum ada' ?></span></div>
            </div>
            <div class="stat-grid">
                <div class="mini-stat">
                    <h5>Total Pesanan</h5>
                    <div class="num"><?= $order_count ?></div>
                    <p>Transaksi tercatat</p>
                </div>
                <div class="mini-stat">
                    <h5>Item Keranjang</h5>
                    <div class="num"><?= $cart_count ?></div>
                    <p>Siap dibayar</p>
                </div>
            </div>
        </div>
    </div>

    <div class="card-base" style="margin-top:14px;">
        <div class="card-head">
            <div>
                <div class="eyebrow">Pemesanan</div>
                <h3>Riwayat Pesanan Saya</h3>
            </div>
            <div style="display:flex; gap:10px; flex-wrap:wrap; align-items:center;">
                <a class="cta-btn primary" href="export_orders.php?format=pdf" target="_blank" rel="noopener">Export PDF</a>
                <a class="cta-btn soft" href="export_orders.php?format=excel" target="_blank" rel="noopener">Export Excel</a>
            </div>
        </div>
        <p class="muted">Lihat status pesanan yang pernah kamu buat.</p>
        <div class="table-wrapper">
            <table class="order-table">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Tanggal</th>
                        <th>Produk</th>
                        <th>Qty</th>
                        <th>Total</th>
                        <th>Metode</th>
                        <th>Status</th>
                    </tr>
                </thead>
                <tbody>
                <?php if ($order_list && mysqli_num_rows($order_list) > 0): ?>
                    <?php while ($o = mysqli_fetch_assoc($order_list)): ?>
                        <?php $stClass = 'st-' . strtolower(str_replace(' ', '', $o['status'])); ?>
                        <tr>
                            <td>#<?= $o['id'] ?></td>
                            <td><?= date('d/m/Y H:i', strtotime($o['tanggal'])) ?></td>
                            <td><?= htmlspecialchars($o['nama_produk']) ?></td>
                            <td><?= $o['qty'] ?></td>
                            <td>Rp <?= number_format($o['harga']) ?></td>
                            <td><?= htmlspecialchars(strtoupper($o['metode_bayar'])) ?></td>
                            <td><span class="status-pill <?= $stClass ?>"><?= htmlspecialchars($o['status']) ?></span></td>
                        </tr>
                    <?php endwhile; ?>
                <?php else: ?>
                    <tr><td colspan="7" style="text-align:center; color:#7a8694; padding:16px 0;">Belum ada pesanan.</td></tr>
                <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>
<footer class="footer" style="text-align:center; padding:18px 0; background:#f4f6f8; color:#888; font-size:15px; position:relative; bottom:0; width:100%;">
        @Copyright by 23552011029_Fauzan Rizkika Kurnia_TIF RP 23 CNS B_UASWEB1
    </footer>
<script>
document.addEventListener('DOMContentLoaded', function() {
    const btn = document.getElementById('profileMenuBtn');
    const menu = document.getElementById('profileDropdown');
    if (!btn || !menu) return;
    btn.addEventListener('click', function(e) {
        e.stopPropagation();
        menu.classList.toggle('show');
    });
    document.addEventListener('click', function(e) {
        if (!menu.contains(e.target) && e.target !== btn) {
            menu.classList.remove('show');
        }
    });
});
</script>
</body>
</html>
