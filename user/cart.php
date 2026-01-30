<?php
session_start();
include "../config/database.php";

// Sinkronkan session dari cookie jika ada
if (!isset($_SESSION['user_id']) && isset($_COOKIE['user_id'])) {
    $_SESSION['user_id'] = $_COOKIE['user_id'];
    $_SESSION['role'] = $_COOKIE['role'] ?? 'user';
}

$logged_in = isset($_SESSION['user_id']);
$user_name = '';
if ($logged_in) {
    $uid = intval($_SESSION['user_id']);
    $uq = mysqli_query($conn, "SELECT nama FROM users WHERE id=$uid");
    $u = mysqli_fetch_assoc($uq);
    $user_name = $u ? $u['nama'] : '';
}

// Hapus produk dari keranjang
if (isset($_GET['remove'])) {
    $rid = intval($_GET['remove']);
    unset($_SESSION['cart'][$rid]);
    header("Location: cart.php");
    exit;
}

// Checkout semua produk (redirect ke checkout mode cart)
if (isset($_POST['checkout_all']) && !empty($_SESSION['cart'])) {
    header("Location: checkout.php?mode=cart");
    exit;
}

$cart = isset($_SESSION['cart']) ? $_SESSION['cart'] : [];
$total = 0;
$item_count = 0;
foreach ($cart as $item) {
    $total += $item['harga'] * $item['qty'];
    $item_count += $item['qty'];
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Keranjang | WorldBike</title>
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <!-- Bootstrap CSS -->
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
        padding-top: 88px;
        display: flex;
        flex-direction: column;
    }
    .navbar {
        display: flex;
        justify-content: space-between;
        align-items: center;
        padding: 22px 7vw;
        background: #fff;
        box-shadow: 0 2px 18px rgba(44,62,80,0.07);
        position: fixed;
        top: 0; left: 0; right: 0; z-index: 1000;
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
    .navbar ul li a.cart-link {
        display: inline-flex;
        align-items: center;
        gap: 8px;
        padding: 6px 14px;
        justify-content: center;
        min-width: 110px;
    }
    .navbar ul li.nav-right {
        margin-left: auto;
    }
    .navbar ul li a.profile-link {
        display: inline-flex;
        align-items: center;
        gap: 8px;
        padding: 6px 14px;
        background: #eaf6fb;
        color: #3498db;
        font-weight: 600;
    }
    .cart-badge {
        position: static;
        background: #eef2f7;
        color: #2c3e50;
        border-radius: 999px;
        padding: 2px 8px;
        font-size: 0.78rem;
        font-weight: 700;
        box-shadow: none;
        line-height: 1;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        min-width: 20px;
        height: 20px;
    }
    .menu-toggle {
        display: none;
        font-size: 2.2rem;
        background: none;
        border: none;
        color: #3498db;
        cursor: pointer;
        margin-left: auto;
        margin-right: 0;
        z-index: 21;
        align-self: flex-end;
    }
    .navbar ul li a {
        color: #2c3e50;
        text-decoration: none;
        font-size: 1.1rem;
        font-weight: 500;
        padding: 6px 14px;
        border-radius: 6px;
        transition: background 0.2s, color 0.2s;
    }
    .navbar ul li a:hover, .navbar ul li a.active {
        background: #3498db;
        color: #fff;
    }
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
    .page-shell {
        max-width: 1080px;
        margin: 24px auto 40px auto;
        padding: 0 6vw;
        flex: 1;
        width: 100%;
    }
    .cart-hero {
        background: linear-gradient(135deg, #2d82d8, #46c2ff);
        color: #fff;
        border-radius: 18px;
        padding: 18px 22px;
        box-shadow: 0 14px 34px rgba(45,130,216,0.28);
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 12px;
        margin-bottom: 16px;
    }
    .cart-hero h1 { margin: 0; font-size: 1.4rem; font-weight: 800; }
    .cart-hero p { margin: 2px 0 0 0; opacity: 0.92; font-weight: 600; }
    .cart-badges { display: flex; gap: 10px; flex-wrap: wrap; }
    .pill {
        background: rgba(255,255,255,0.18);
        border: 1px solid rgba(255,255,255,0.3);
        color: #fff;
        padding: 8px 12px;
        border-radius: 12px;
        font-weight: 800;
        font-size: 0.98rem;
    }
    .cart-panel {
        background: #fff;
        border-radius: 16px;
        box-shadow: 0 12px 30px rgba(44,62,80,0.12);
        border: 1px solid #e3ebf5;
        padding: 18px 18px;
    }
    .table-wrapper { overflow-x: auto; }
    table {
        width: 100%;
        border-collapse: separate;
        border-spacing: 0;
    }
    thead th {
        background: #f1f6ff;
        color: #1f3b57;
        font-weight: 800;
        padding: 12px 10px;
        text-transform: uppercase;
        letter-spacing: 0.04em;
        font-size: 0.95rem;
        border-bottom: 1px solid #dde7f5;
        position: sticky;
        top: 0;
        z-index: 1;
    }
    tbody td {
        padding: 12px 10px;
        border-bottom: 1px solid #eef2f7;
        vertical-align: middle;
        font-weight: 600;
        color: #1f2d3d;
        background: #fff;
    }
    tbody tr:hover td { background: #f6faff; }
    td img {
        width: 64px;
        height: 64px;
        object-fit: cover;
        border-radius: 12px;
        box-shadow: 0 4px 12px rgba(52,152,219,0.16);
        background: #eaf6fb;
    }
    .price { color: #0f6abf; font-weight: 800; }
    .qty-pill {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        min-width: 36px;
        padding: 6px 10px;
        background: #eef4fb;
        border-radius: 10px;
        font-weight: 800;
        color: #1f3b57;
    }
    .cart-actions {
        display: flex;
        justify-content: flex-end;
        gap: 10px;
        margin-top: 16px;
        flex-wrap: wrap;
    }
    .btn {
        background: linear-gradient(135deg, #3498db, #217dbb);
        color: #fff;
        padding: 11px 18px;
        border-radius: 12px;
        text-decoration: none;
        font-weight: 700;
        border: none;
        transition: transform 0.15s, box-shadow 0.2s;
        cursor: pointer;
        font-size: 1.02rem;
        box-shadow: 0 10px 24px rgba(52,152,219,0.25);
    }
    .btn:hover { transform: translateY(-1px); box-shadow: 0 12px 30px rgba(33,125,187,0.25); }
    .btn.soft {
        background: #f4f7fb;
        color: #1b3a57;
        border: 1px solid #e1e7ef;
        box-shadow: none;
    }
    .btn.danger { background: linear-gradient(135deg, #e74c3c, #c0392b); box-shadow: 0 10px 24px rgba(231,76,60,0.22); }
    .btn.success { background: linear-gradient(135deg, #2ecc71, #1ea95a); box-shadow: 0 10px 24px rgba(46,204,113,0.22); }
    .empty-cart {
        text-align: center;
        color: #5f7082;
        font-size: 1.05rem;
        margin: 32px 0;
        background: #f8fafc;
        border: 1px solid #e3ebf5;
        border-radius: 14px;
        padding: 18px;
        font-weight: 600;
    }
    .cart-total-bar {
        margin-top: 14px;
        background: linear-gradient(135deg, #f8fbff, #eef2f7);
        border: 1px solid #e1e7f0;
        border-radius: 12px;
        padding: 12px 14px;
        display: flex;
        justify-content: space-between;
        align-items: center;
        gap: 10px;
        font-weight: 800;
        color: #1b3a57;
    }
    .cart-total-bar .total { color: #0f6abf; font-size: 1.2rem; }
    @media (max-width: 720px) {
        .page-shell { padding: 0 4vw; }
        .cart-hero { flex-direction: column; align-items: flex-start; }
        thead { display: none; }
        table, tbody, tr, td { display: block; width: 100%; }
        tr { border: 1px solid #e7eef7; border-radius: 14px; margin-bottom: 12px; padding: 12px 10px; box-shadow: 0 10px 20px rgba(44,62,80,0.08); background: #fff; }
        td { border: none; padding: 6px 0; }
        td::before { content: attr(data-label); font-weight: 700; color: #607286; display: block; margin-bottom: 4px; text-transform: uppercase; letter-spacing: 0.04em; font-size: 0.82rem; }
        td img { margin-bottom: 8px; }
        .cart-actions { justify-content: center; }
    }
    @media (max-width: 600px) {
        .navbar { padding: 12px 3vw; }
        .navbar .logo { font-size: 1.4rem; }
        .menu-toggle { display: block; position: absolute; right: 18px; top: 18px; }
        .navbar ul { flex-direction: column; gap: 0; align-items: flex-start; background: #fff; position: absolute; top: 100%; right: 0; left: 0; box-shadow: 0 2px 18px rgba(44,62,80,0.07); border-radius: 0 0 12px 12px; padding: 10px 0; display: none; z-index: 20; }
        .navbar ul.show { display: flex; }
        .navbar ul li { width: 100%; }
        .navbar ul li a { display: block; width: 100%; padding: 12px 18px; font-size: 1.08rem; }
        .navbar ul li.nav-right { margin-left: 0; }
        .page-shell { margin-top: 10px; }
    }
    .footer {
        text-align: center;
        padding: 18px 0;
        background: #f4f6f8;
        color: #888;
        font-size: 15px;
        width: 100%;
        margin-top: auto;
    }
    </style>
</head>
<body>
<nav class="navbar">
    <div class="logo">WorldBike</div>
        <button class="menu-toggle" id="menuToggle">&#9776;</button>
        <ul id="navbarMenu">
            <li><a href="../index.php">Home</a></li>
            <li><a href="index.php">Marketplace</a></li>
            <li><a href="cart.php" class="active cart-link">Keranjang
                <?php if (!empty($_SESSION['cart'])): ?>
                    <span class="cart-badge"><?= array_sum(array_column($_SESSION['cart'], 'qty')) ?></span>
                <?php endif; ?>
            </a></li>
            <?php if ($logged_in): ?>
                <li class="nav-right profile-menu">
                    <button class="profile-trigger" id="profileMenuBtn">Hi, <?= htmlspecialchars($user_name ?: 'Pengguna') ?> ▾</button>
                    <div class="profile-dropdown" id="profileDropdown">
                        <a href="profile.php">Kunjungi Profil</a>
                        <a href="../auth/logout.php" class="logout">Logout</a>
                    </div>
                </li>
            <?php else: ?>
                <li class="nav-right"><a href="../auth/login.php?redirect=<?= urlencode($_SERVER['REQUEST_URI']) ?>" class="login-link">Login</a></li>
            <?php endif; ?>
        </ul>
</nav>
<div class="page-shell">
    <div class="cart-hero">
        <div>
            <h1>Keranjang Belanja</h1>
            <p>Kumpulkan itemmu lalu lanjutkan ke checkout.</p>
        </div>
        <div class="cart-badges">
            <span class="pill">Item: <?= $item_count ?></span>
            <span class="pill">Subtotal: Rp <?= number_format($total) ?></span>
        </div>
    </div>

    <div class="cart-panel">
        <?php if (empty($cart)): ?>
            <div class="empty-cart">Keranjang Anda kosong.<br><a href="index.php" style="color:#3498db;text-decoration:underline;">← Belanja Sekarang</a></div>
        <?php else: ?>
            <form method="post">
            <div class="table-wrapper">
                <table>
                    <thead>
                        <tr>
                            <th>Produk</th>
                            <th>Nama</th>
                            <th>Harga</th>
                            <th>Qty</th>
                            <th>Total</th>
                            <th>Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($cart as $item): ?>
                        <tr>
                            <td data-label="Produk"><img src="../assets/img/produk/<?= htmlspecialchars($item['gambar']) ?>" alt="<?= htmlspecialchars($item['nama_produk']) ?>"></td>
                            <td data-label="Nama"><?= htmlspecialchars($item['nama_produk']) ?></td>
                            <td data-label="Harga" class="price">Rp <?= number_format($item['harga']) ?></td>
                            <td data-label="Qty"><span class="qty-pill"><?= $item['qty'] ?></span></td>
                            <td data-label="Total" class="price">Rp <?= number_format($item['harga'] * $item['qty']) ?></td>
                            <td data-label="Aksi">
                                <a href="cart.php?remove=<?= $item['id'] ?>" class="btn danger" onclick="return confirm('Hapus produk dari keranjang?')">Hapus</a>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
            <div class="cart-total-bar">
                <span>Total Keranjang</span>
                <span class="total">Rp <?= number_format($total) ?></span>
            </div>
            <div class="cart-actions">
                <a href="index.php" class="btn soft">← Lanjut Belanja</a>
                <button type="submit" name="checkout_all" class="btn success">Checkout Semua</button>
            </div>
            </form>
        <?php endif; ?>
    </div>
</div>

<footer class="footer">
    @Copyright by 23552011029_Fauzan Rizkika Kurnia_TIF RP 23 CNS B_UASWEB1
    </footer>

<!-- Bootstrap JS -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"
    integrity="sha384-YvpcrYf0tY3lHB60NNkmXc5s9fDVZLESaAA55NDzOxhy9GkcIdslK1eN7N6jIeHz"
    crossorigin="anonymous"></script>

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

    var profileBtn = document.getElementById('profileMenuBtn');
    var profileDropdown = document.getElementById('profileDropdown');
    if (profileBtn && profileDropdown) {
        profileBtn.addEventListener('click', function(ev) {
            ev.stopPropagation();
            profileDropdown.classList.toggle('show');
        });
        document.addEventListener('click', function(ev) {
            if (!profileDropdown.contains(ev.target) && ev.target !== profileBtn) {
                profileDropdown.classList.remove('show');
            }
        });
    }
});
</script>
</body>
</html>
