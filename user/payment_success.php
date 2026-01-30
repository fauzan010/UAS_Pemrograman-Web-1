<?php
session_start();
include "../config/database.php";

// Sinkronkan session dari cookie jika ada
if (!isset($_SESSION['user_id']) && isset($_COOKIE['user_id'])) {
    $_SESSION['user_id'] = $_COOKIE['user_id'];
    $_SESSION['role'] = $_COOKIE['role'] ?? 'user';
}

if (!isset($_SESSION['user_id'])) {
    header("Location: ../auth/login.php?redirect=user/payment_success.php");
    exit;
}

$uid = intval($_SESSION['user_id']);
$order_id = isset($_GET['order_id']) ? intval($_GET['order_id']) : 0;
$order = null;
if ($order_id > 0) {
    $order_q = mysqli_query($conn, "
        SELECT o.*, p.nama_produk, p.gambar, p.harga AS harga_produk
        FROM orders o
        JOIN products p ON o.product_id = p.id
        WHERE o.id = $order_id AND o.user_id = $uid
        LIMIT 1
    ");
    if ($order_q) {
        $order = mysqli_fetch_assoc($order_q);
    }
}

// Jika tidak ditemukan, arahkan ke marketplace
if (!$order) {
    header("Location: index.php");
    exit;
}

// Hitung ringkas
$total_bayar = $order['harga'];
$metode = strtoupper($order['metode_bayar']);
$date_fmt = date('d M Y H:i', strtotime($order['tanggal']));
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Pembayaran Berhasil | WorldBike</title>
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
    .navbar .logo {
        font-size: 2rem;
        font-weight: bold;
        color: #3498db;
        letter-spacing: 2px;
    }
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
    .navbar ul li a.profile-link { display: inline-flex; align-items: center; gap: 8px; padding: 6px 14px; background: #eaf6fb; color: #3498db; font-weight: 600; }

    .success-shell {
        max-width: 620px;
        margin: 40px auto 90px auto;
        background: #fff;
        border-radius: 18px;
        box-shadow: 0 12px 32px rgba(44,62,80,0.10);
        padding: 26px 22px;
        border: 1px solid #e6edf5;
    }
    .badge-soft { background: #eaf6fb; color: #217dbb; padding: 8px 12px; border-radius: 999px; font-weight: 800; border: 1px solid #d5e9f6; }
    .title { font-size: 1.5rem; font-weight: 800; color: #1b3a57; margin: 12px 0 6px 0; }
    .muted { color: #5f7082; }
    .card-info { background: #f8fafc; border: 1px solid #e6edf5; border-radius: 14px; padding: 14px 16px; margin-top: 14px; }
    .info-row { display: flex; justify-content: space-between; align-items: center; padding: 8px 0; border-bottom: 1px solid #eef2f7; }
    .info-row:last-child { border-bottom: none; }
    .info-label { color: #5b6b7c; font-weight: 700; }
    .info-value { color: #123069; font-weight: 800; }
    .cta-row { display: flex; gap: 10px; flex-wrap: wrap; margin-top: 16px; }
    .btn-pill { display: inline-flex; align-items: center; justify-content: center; gap: 8px; padding: 12px 14px; border-radius: 12px; font-weight: 700; text-decoration: none; border: none; transition: transform 0.15s, box-shadow 0.2s; }
    .btn-primary { background: linear-gradient(135deg, #3498db, #217dbb); color: #fff; box-shadow: 0 12px 30px rgba(52,152,219,0.25); }
    .btn-soft { background: #f4f7fb; color: #1b3a57; border: 1px solid #e1e7ef; }
    .btn-pill:hover { transform: translateY(-1px); box-shadow: 0 14px 34px rgba(44,62,80,0.16); }
    </style>
</head>
<body>
<nav class="navbar">
    <div class="logo">WorldBike</div>
    <ul>
        <li><a href="../index.php">Home</a></li>
        <li><a href="index.php">Marketplace</a></li>
        <li><a href="cart.php" class="cart-link">Keranjang</a></li>
        <li class="nav-right"><a href="profile.php" class="profile-link">Hi, <?= htmlspecialchars($order['nama_penerima']) ?></a></li>
    </ul>
</nav>
<div class="success-shell">
    <span class="badge-soft">Pembayaran Berhasil</span>
    <div class="title">Terima kasih! Pesananmu tercatat.</div>
    <p class="muted">Kami sedang memproses pesanan. Simpan rincian berikut sebagai bukti.</p>

    <div class="card-info">
        <div class="info-row"><span class="info-label">ID Pesanan</span><span class="info-value">#<?= $order['id'] ?></span></div>
        <div class="info-row"><span class="info-label">Produk</span><span class="info-value"><?= htmlspecialchars($order['nama_produk']) ?></span></div>
        <div class="info-row"><span class="info-label">Tanggal</span><span class="info-value"><?= $date_fmt ?></span></div>
        <div class="info-row"><span class="info-label">Metode</span><span class="info-value"><?= htmlspecialchars($metode) ?></span></div>
        <div class="info-row"><span class="info-label">Total</span><span class="info-value">Rp <?= number_format($total_bayar) ?></span></div>
        <div class="info-row"><span class="info-label">Status</span><span class="info-value"><?= htmlspecialchars($order['status']) ?></span></div>
    </div>

    <div class="cta-row">
        <a class="btn-pill btn-soft" href="export_payment_pdf.php?order_id=<?= intval($order['id']) ?>" target="_blank" rel="noopener">Unduh PDF</a>
        <a class="btn-pill btn-primary" href="profile.php">Lihat Pesanan Saya</a>
        <a class="btn-pill btn-soft" href="index.php">Kembali ke Marketplace</a>
    </div>
</div>
</body>
</html>
