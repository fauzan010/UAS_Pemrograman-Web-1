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
    $q = mysqli_query($conn, "SELECT nama FROM users WHERE id=$uid");
    $u = mysqli_fetch_assoc($q);
    $user_name = $u ? $u['nama'] : '';
}

/* Ambil kategori */
$categories = mysqli_query($conn, "SELECT * FROM categories");

/* Filter dinamis */
$conditions = [];
if (isset($_GET['kategori']) && $_GET['kategori'] !== "") {
    $id = intval($_GET['kategori']);
    $conditions[] = "products.category_id = $id";
}
if (isset($_GET['q']) && trim($_GET['q']) !== '') {
    $search = mysqli_real_escape_string($conn, trim($_GET['q']));
    $conditions[] = "products.nama_produk LIKE '%$search%'";
}
if (isset($_GET['min']) && $_GET['min'] !== '' && is_numeric($_GET['min'])) {
    $min = intval($_GET['min']);
    $conditions[] = "products.harga >= $min";
}
if (isset($_GET['max']) && $_GET['max'] !== '' && is_numeric($_GET['max'])) {
    $max = intval($_GET['max']);
    $conditions[] = "products.harga <= $max";
}
$where = '';
if (count($conditions) > 0) {
    $where = 'WHERE ' . implode(' AND ', $conditions);
}

$orderBy = 'ORDER BY products.id DESC';
if (isset($_GET['sort'])) {
    switch ($_GET['sort']) {
        case 'harga_asc':
            $orderBy = 'ORDER BY products.harga ASC';
            break;
        case 'harga_desc':
            $orderBy = 'ORDER BY products.harga DESC';
            break;
        case 'stok':
            $orderBy = 'ORDER BY products.stok DESC';
            break;
        default:
            $orderBy = 'ORDER BY products.id DESC';
    }
}

/* Ambil produk */
$products = mysqli_query($conn, "
    SELECT products.*, categories.nama_kategori
    FROM products
    LEFT JOIN categories ON products.category_id = categories.id
    $where
    $orderBy
");
$product_count = $products ? mysqli_num_rows($products) : 0;
if ($products) {
    mysqli_data_seek($products, 0); // pastikan pointer di awal untuk loop
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>WorldBike - Marketplace Sepeda</title>
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css"
          rel="stylesheet"
          integrity="sha384-QWTKZyjpPEjISv5WaRU9OFeRpok6YctnYmDr5pNlyT2bRjXh0JMhjY6hW+ALEwIH"
          crossorigin="anonymous">
    <link rel="stylesheet" href="../assets/css/style.css">
    <style>
    body {
        background: linear-gradient(120deg, #e0eafc 0%, #cfdef3 100%);
        min-height: 100vh;
        scroll-behavior: smooth;
        padding-top: 80px; /* beri ruang agar konten tidak tertutup navbar */
    }
    .navbar {
        display: flex;
        justify-content: space-between;
        align-items: center;
        padding: 22px 7vw;
        background: #fff;
        box-shadow: 0 2px 18px rgba(44,62,80,0.07);
        position: fixed;
        top: 0;
        left: 0;
        right: 0;
        z-index: 1000;
        width: 100%;
    }
    .navbar .logo {
        font-size: 2rem;
        font-weight: bold;
        color: #3498db;
        letter-spacing: 2px;
        text-shadow: 0 2px 8px rgba(52,152,219,0.08);
    }
    .navbar .menu-toggle {
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
    .navbar ul {
        display: flex;
        gap: 32px;
        list-style: none;
        margin: 0;
        padding: 0;
        align-items: center;
    }
    .navbar ul li a, .navbar ul li span {
        font-size: 1.1rem;
        font-weight: 500;
        padding: 6px 14px;
        border-radius: 6px;
        color: #2c3e50;
        display: block;
        transition: background 0.2s, color 0.2s;
        vertical-align: middle;
        text-decoration: none;
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
    .navbar ul li span.user-profile {
        color: #3498db;
        background: #eaf6fb;
        font-weight: 600;
        display: flex;
        align-items: center;
        gap: 8px;
        padding: 6px 14px;
        border-radius: 6px;
        font-size: 1.1rem;
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
    .marketplace-hero {
        display: flex;
        flex-direction: column;
        align-items: center;
        justify-content: center;
        min-height: 40vh;
        text-align: center;
        padding: 50px 10vw 30px;
        background: linear-gradient(120deg, #f8fafc 60%, #e0eafc 100%);
        position: relative;
        overflow: hidden;
    }
    .marketplace-title {
        font-size: 2.3rem;
        font-weight: 800;
        color: #222;
        margin-bottom: 12px;
        letter-spacing: 1px;
        animation: fadeInDown 1s;
    }
    .marketplace-tagline {
        font-size: 1.15rem;
        color: #555;
        margin-bottom: 24px;
        animation: fadeIn 1.5s;
    }
    .marketplace-filter {
        margin: 0 auto 24px auto;
        max-width: 1000px;
        text-align: center;
        animation: fadeInUp 1.2s;
    }
    .filter-grid {
        display: grid;
        grid-template-columns: repeat(5, 1fr);
        gap: 12px;
        align-items: center;
    }
    @media (max-width: 992px) { .filter-grid { grid-template-columns: repeat(2, 1fr); } }
    @media (max-width: 640px) { .filter-grid { grid-template-columns: 1fr; } }
    .filter-grid .field {
        background: #fff;
        border-radius: 10px;
        padding: 10px 12px 6px;
        box-shadow: 0 6px 18px rgba(44,62,80,0.07);
        border: 1px solid #eef3f7;
        text-align: left;
    }
    .filter-grid label {
        display: block;
        font-size: 0.9rem;
        color: #6c7a89;
        margin-bottom: 6px;
        font-weight: 600;
    }
    .filter-grid input, .filter-grid select {
        width: 100%;
        padding: 10px 12px;
        border-radius: 8px;
        border: 1px solid #dce4ec;
        font-size: 1rem;
        background: #f8fafc;
        transition: border 0.2s, box-shadow 0.2s;
    }
    .filter-grid input:focus, .filter-grid select:focus {
        border: 1.5px solid #3498db;
        outline: none;
        box-shadow: 0 0 0 3px rgba(52,152,219,0.15);
    }
    .filter-actions {
        display: flex;
        gap: 10px;
        justify-content: center;
        margin-top: 14px;
    }
    .btn-filter {
        padding: 11px 18px;
        border: none;
        border-radius: 10px;
        font-weight: 700;
        cursor: pointer;
        background: linear-gradient(135deg, #3498db, #217dbb);
        color: #fff;
        box-shadow: 0 8px 24px rgba(52,152,219,0.2);
        transition: transform 0.15s, box-shadow 0.2s;
    }
    .btn-filter:hover { transform: translateY(-1px); box-shadow: 0 12px 28px rgba(52,152,219,0.25); }
    .btn-reset {
        padding: 11px 16px;
        border-radius: 10px;
        border: 1px solid #dce4ec;
        background: #fff;
        color: #2c3e50;
        font-weight: 600;
        cursor: pointer;
        transition: border 0.2s, color 0.2s;
    }
    .btn-reset:hover { border-color: #3498db; color: #217dbb; }
    .summary-chip {
        display: inline-block;
        margin-top: 12px;
        background: #eaf6fb;
        color: #217dbb;
        padding: 8px 14px;
        border-radius: 999px;
        font-weight: 600;
        font-size: 0.98rem;
    }
    .dashboard-menu {
        display: grid;
        grid-template-columns: repeat(4, 1fr);
        gap: 28px;
        margin: 0 auto 40px auto;
        max-width: 1400px;
        padding: 0 7vw;
        animation: fadeIn 1.2s;
    }
    .menu-card {
        background: #fff;
        border-radius: 16px;
        padding: 18px 16px 18px 16px;
        box-shadow: 0 10px 30px rgba(0,0,0,0.08);
        transition: transform 0.3s, box-shadow 0.3s;
        display: flex;
        flex-direction: column;
        align-items: stretch;
        min-height: 440px;
        position: relative;
        opacity: 0;
        transform: translateY(40px);
        transition: all 0.7s;
        overflow: hidden;
    }
    .menu-card.visible {
        opacity: 1;
        transform: none;
    }
    .menu-card:hover {
        transform: translateY(-8px) scale(1.03);
        box-shadow: 0 14px 32px rgba(52,152,219,0.13);
    }
    .menu-card img {
        width: 100%;
        max-width: 100%;
        height: 180px;
        object-fit: cover;
        border-radius: 12px;
        margin-bottom: 14px;
        background: #eaf6fb;
        box-shadow: 0 3px 10px rgba(52,152,219,0.08);
    }
    .menu-card h3 {
        font-size: 1.16rem;
        color: #1b3a57;
        margin-bottom: 6px;
        font-weight: 800;
        text-align: left;
    }
    .menu-card p {
        font-size: 0.98rem;
        color: #4a5560;
        margin-bottom: 6px;
        text-align: left;
    }
    .menu-card .stok {
        font-size: 0.95rem;
        color: #556;
        margin-bottom: 0;
    }
    .price-pill {
        display: inline-block;
        padding: 10px 12px;
        background: #eaf6fb;
        color: #0f6abf;
        border-radius: 12px;
        font-weight: 800;
        letter-spacing: 0.3px;
        margin-bottom: 6px;
        font-size: 1rem;
    }
    .category-tag {
        display: inline-block;
        background: #fef4e6;
        color: #d35400;
        padding: 6px 10px;
        border-radius: 999px;
        font-weight: 700;
        font-size: 0.9rem;
        margin-bottom: 8px;
    }
    .stock-badge {
        display: inline-block;
        padding: 6px 10px;
        border-radius: 999px;
        font-weight: 700;
        font-size: 0.9rem;
        margin-right: 6px;
    }
    .stock-ready { background: #e8f8f0; color: #1e8a4b; }
    .stock-low { background: #fff4e5; color: #c77400; }
    .stock-out { background: #ffecec; color: #c0392b; }
    .card-footer-actions {
        margin-top: auto;
        display: flex;
        justify-content: space-between;
        gap: 10px;
        align-items: center;
    }
    .btn-detail {
        flex: 1;
        padding: 10px 12px;
        border-radius: 10px;
        border: 1px solid #dce4ec;
        background: #fff;
        color: #1b3a57;
        font-weight: 700;
        text-align: center;
        text-decoration: none;
        transition: border 0.2s, color 0.2s;
    }
    .btn-detail:hover { border-color: #3498db; color: #217dbb; }
    .btn-cart {
        flex: 1;
        padding: 10px 12px;
        border: none;
        border-radius: 10px;
        background: linear-gradient(135deg, #2ecc71, #27ae60);
        color: #fff;
        font-weight: 800;
        cursor: pointer;
        text-decoration: none;
        text-align: center;
        box-shadow: 0 8px 20px rgba(46,204,113,0.25);
        transition: transform 0.2s, box-shadow 0.2s;
    }
    .btn-cart:hover { transform: translateY(-1px); box-shadow: 0 12px 28px rgba(46,204,113,0.3); }
    .no-product {
        text-align: center;
        color: #888;
        font-size: 1.1rem;
        margin: 40px 0 60px 0;
    }
    .btn-admin-login {
        display: inline-block;
        background: #e74c3c;
        color: #fff;
        padding: 10px 18px;
        border-radius: 6px;
        text-decoration: none;
        margin-top: 18px;
        transition: background 0.2s;
    }
    .btn-admin-login:hover {
        background: #c0392b;
    }
    @keyframes fadeInDown {
        from { opacity: 0; transform: translateY(-40px);}
        to { opacity: 1; transform: none;}
    }
    @keyframes fadeInUp {
        from { opacity: 0; transform: translateY(40px);}
        to { opacity: 1; transform: none;}
    }
    @keyframes fadeIn {
        from { opacity: 0;}
        to { opacity: 1;}
    }
    @media (max-width: 1200px) {
        .dashboard-menu {
            grid-template-columns: repeat(3, 1fr);
        }
    }
    @media (max-width: 900px) {
        .marketplace-title {
            font-size: 1.7rem;
        }
        .marketplace-hero {
            padding: 30px 4vw 20px;
        }
        .dashboard-menu {
            grid-template-columns: repeat(2, 1fr);
            padding: 0 4vw;
        }
    }
    @media (max-width: 600px) {
        html {
            font-size: 15px;
        }
        .navbar {
            padding: 12px 3vw;
        }
        .navbar .logo {
            font-size: 1.4rem;
        }
        .navbar .menu-toggle {
            display: block;
            position: absolute;
            right: 18px;
            top: 18px;
        }
        .navbar ul {
            flex-direction: column;
            gap: 0;
            align-items: flex-start;
            background: #fff;
            position: absolute;
            top: 100%;
            right: 0;
            left: 0;
            box-shadow: 0 2px 18px rgba(44,62,80,0.07);
            border-radius: 0 0 12px 12px;
            padding: 10px 0 10px 0;
            display: none;
            z-index: 20;
        }
        .navbar ul.show {
            display: flex;
        }
        .navbar ul li {
            width: 100%;
        }
        .navbar ul li a, .navbar ul li span {
            display: block;
            width: 100%;
            padding: 12px 18px;
            font-size: 1.08rem;
        }
        .navbar ul li.nav-right {
            margin-left: 0;
        }
        .marketplace-title {
            font-size: 1.1rem;
        }
        .marketplace-tagline {
            font-size: 0.98rem;
        }
        .dashboard-menu {
            grid-template-columns: 1fr;
            gap: 12px;
            padding: 0 2vw;
        }
        .menu-card {
            min-height: 220px;
            padding: 10px;
        }
        .menu-card img {
            height: 110px;
        }
        .menu-card h3 {
            font-size: 1.05rem;
        }
        .menu-card p, .menu-card .stok {
            font-size: 0.95rem;
        }
        .marketplace-hero {
            padding: 24px 2vw 16px;
        }
        .filter-grid { grid-template-columns: 1fr; }
        .filter-grid input, .filter-grid select { font-size: 0.98rem; padding: 10px 10px; }
    }
    </style>
</head>
<body>

<!-- Navbar -->
<nav class="navbar">
    <div class="logo">WorldBike</div>
    <button class="menu-toggle" id="menuToggle">&#9776;</button>
    <ul id="navbarMenu">
        <li><a href="../index.php">Home</a></li>
        <li><a href="#" class="active">Marketplace</a></li>
        <li><a href="cart.php" class="cart-link">Keranjang</a></li>
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

<!-- Hero Section Marketplace -->
<section class="marketplace-hero">
    <div class="marketplace-title">Marketplace Sepeda & Aksesoris</div>
    <div class="marketplace-tagline">
        Temukan berbagai pilihan sepeda dan aksesoris terbaik untuk kebutuhanmu.<br>
        Gunakan pencarian, harga, dan urutan untuk menemukan produk lebih cepat.
    </div>
    <form method="GET" class="marketplace-filter">
        <div class="filter-grid">
            <div class="field">
                <label>Cari produk</label>
                <input type="text" name="q" placeholder="Cari nama sepeda/aksesoris" value="<?= isset($_GET['q']) ? htmlspecialchars($_GET['q']) : '' ?>">
            </div>
            <div class="field">
                <label>Kategori</label>
                <select name="kategori">
                    <option value="">Semua Kategori</option>
                    <?php
                    mysqli_data_seek($categories, 0);
                    while($c = mysqli_fetch_assoc($categories)): ?>
                        <option value="<?= $c['id'] ?>" <?= (isset($_GET['kategori']) && $_GET['kategori'] == $c['id']) ? 'selected' : '' ?>>
                            <?= htmlspecialchars($c['nama_kategori']) ?>
                        </option>
                    <?php endwhile; ?>
                </select>
            </div>
            <div class="field">
                <label>Harga minimum</label>
                <input type="number" name="min" min="0" placeholder="0" value="<?= isset($_GET['min']) ? htmlspecialchars($_GET['min']) : '' ?>">
            </div>
            <div class="field">
                <label>Harga maksimum</label>
                <input type="number" name="max" min="0" placeholder="20000000" value="<?= isset($_GET['max']) ? htmlspecialchars($_GET['max']) : '' ?>">
            </div>
            <div class="field">
                <label>Urutkan</label>
                <select name="sort">
                    <option value="baru" <?= (!isset($_GET['sort']) || $_GET['sort'] === 'baru') ? 'selected' : '' ?>>Terbaru</option>
                    <option value="harga_asc" <?= (isset($_GET['sort']) && $_GET['sort'] === 'harga_asc') ? 'selected' : '' ?>>Harga terendah</option>
                    <option value="harga_desc" <?= (isset($_GET['sort']) && $_GET['sort'] === 'harga_desc') ? 'selected' : '' ?>>Harga tertinggi</option>
                    <option value="stok" <?= (isset($_GET['sort']) && $_GET['sort'] === 'stok') ? 'selected' : '' ?>>Stok terbanyak</option>
                </select>
            </div>
        </div>
        <div class="filter-actions">
            <button type="submit" class="btn-filter">Terapkan Filter</button>
            <a class="btn-reset" href="index.php">Reset</a>
        </div>
        <div class="summary-chip">Menampilkan <?= $product_count ?> produk</div>
    </form>
</section>

<!-- LIST PRODUK -->
<div class="dashboard-menu">
    <?php if(mysqli_num_rows($products) > 0): ?>
        <?php while($p = mysqli_fetch_assoc($products)): ?>
            <?php
                $stok = intval($p['stok']);
                $stockClass = $stok <= 0 ? 'stock-out' : ($stok <= 5 ? 'stock-low' : 'stock-ready');
                $stockLabel = $stok <= 0 ? 'Stok habis' : ($stok <= 5 ? 'Stok menipis' : 'Ready stock');
            ?>
            <div class="menu-card fadein">
                <?php if(!empty($p['gambar'])): ?>
                    <img src="../assets/img/produk/<?= htmlspecialchars($p['gambar']) ?>"
                         alt="<?= htmlspecialchars($p['nama_produk']) ?>">
                <?php else: ?>
                    <img src="../assets/img/no-image.png" alt="No Image">
                <?php endif; ?>
                <div class="category-tag"><?= htmlspecialchars($p['nama_kategori']) ?></div>
                <h3><?= htmlspecialchars($p['nama_produk']) ?></h3>
                <span class="price-pill">Rp <?= number_format($p['harga']) ?></span>
                <p class="stok"><span class="stock-badge <?= $stockClass ?>"><?= $stockLabel ?></span>Stok: <?= $stok ?></p>
                <p><strong>Deskripsi singkat:</strong> <?= isset($p['deskripsi']) ? htmlspecialchars(mb_strimwidth($p['deskripsi'], 0, 100, '...')) : 'Lihat detail produk untuk info lengkap.' ?></p>
                <div class="card-footer-actions">
                    <a class="btn-detail" href="detail.php?id=<?= $p['id'] ?>">Lihat Detail</a>
                </div>
            </div>
        <?php endwhile; ?>
    <?php else: ?>
        <div class="no-product">Tidak ada produk.</div>
    <?php endif; ?>
</div>

<!-- Bootstrap JS -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"
        integrity="sha384-YvpcrYf0tY3lHB60NNkmXc5s9fDVZLESaAA55NDzOxhy9GkcIdslK1eN7N6jIeHz"
        crossorigin="anonymous"></script>

<script>
document.addEventListener("DOMContentLoaded", function() {
    // Fade-in animasi produk
    function revealOnScroll() {
        var elements = document.querySelectorAll('.fadein, .menu-card');
        var windowHeight = window.innerHeight;
        elements.forEach(function(el) {
            var position = el.getBoundingClientRect().top;
            if (position < windowHeight - 80) {
                el.classList.add('visible');
            }
        });
    }
    window.addEventListener('scroll', revealOnScroll);
    revealOnScroll();

    // Navbar hamburger menu
    var menuToggle = document.getElementById('menuToggle');
    var navbarMenu = document.getElementById('navbarMenu');
    menuToggle.addEventListener('click', function(e) {
        e.stopPropagation();
        navbarMenu.classList.toggle('show');
    });
    // Close menu on link click (mobile)
    navbarMenu.querySelectorAll('a').forEach(function(link) {
        link.addEventListener('click', function() {
            if (window.innerWidth <= 600) navbarMenu.classList.remove('show');
        });
    });
    // Close menu if click outside (mobile)
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
<!-- Footer -->
<<footer class="footer" style="text-align:center; padding:18px 0; background:#f4f6f8; color:#888; font-size:15px; position:relative; bottom:0; width:100%;">
        @Copyright by 23552011029_Fauzan Rizkika Kurnia_TIF RP 23 CNS B_UASWEB1
    </footer>
</body>
</html>
