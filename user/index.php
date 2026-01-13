<?php
include "../config/database.php";

$logged_in = isset($_COOKIE['user_id']);
$user_name = '';
if ($logged_in) {
    $uid = intval($_COOKIE['user_id']);
    $q = mysqli_query($conn, "SELECT nama FROM users WHERE id=$uid");
    $u = mysqli_fetch_assoc($q);
    $user_name = $u ? $u['nama'] : '';
}

/* Ambil kategori */
$categories = mysqli_query($conn, "SELECT * FROM categories");

/* Filter kategori */
$where = "";
if (isset($_GET['kategori']) && $_GET['kategori'] != "") {
    $id = $_GET['kategori'];
    $where = "WHERE products.category_id = $id";
}

/* Ambil produk */
$products = mysqli_query($conn, "
    SELECT products.*, categories.nama_kategori
    FROM products
    LEFT JOIN categories ON products.category_id = categories.id
    $where
");
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>WorldBike - Marketplace Sepeda</title>
    <meta name="viewport" content="width=device-width, initial-scale=1">
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
        margin: 0 auto 30px auto;
        max-width: 400px;
        text-align: center;
        animation: fadeInUp 1.2s;
    }
    .marketplace-filter select {
        width: 100%;
        padding: 12px 14px;
        border-radius: 8px;
        border: 1px solid #ddd;
        font-size: 1.05rem;
        margin-top: 10px;
        margin-bottom: 0;
        background: #f8fafc;
        transition: border 0.2s;
    }
    .marketplace-filter select:focus {
        border: 1.5px solid #3498db;
        outline: none;
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
        border-radius: 14px;
        padding: 18px 16px 16px 16px;
        box-shadow: 0 8px 20px rgba(0,0,0,0.08);
        transition: transform 0.3s, box-shadow 0.3s;
        display: flex;
        flex-direction: column;
        align-items: center;
        min-height: 420px;
        position: relative;
        opacity: 0;
        transform: translateY(40px);
        transition: all 0.7s;
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
        max-width: 210px;
        height: 160px;
        object-fit: cover;
        border-radius: 10px;
        margin-bottom: 18px;
        background: #eaf6fb;
        box-shadow: 0 2px 8px rgba(52,152,219,0.08);
    }
    .menu-card h3 {
        font-size: 1.18rem;
        color: #3498db;
        margin-bottom: 8px;
        font-weight: 700;
        text-align: center;
    }
    .menu-card p {
        font-size: 1rem;
        color: #444;
        margin-bottom: 6px;
        text-align: center;
    }
    .menu-card .stok {
        font-size: 0.98rem;
        color: #888;
        margin-bottom: 0;
    }
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
        .marketplace-filter select {
            font-size: 0.98rem;
            padding: 10px 10px;
        }
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
        <?php if (!$logged_in): ?>
            <li><a href="../auth/login.php" class="btn" style="margin-left:16px;">Login</a></li>
        <?php else: ?>
            <li>
                <span class="user-profile"><span style="font-size:1.2em;">👤</span><?= htmlspecialchars($user_name) ?></span>
            </li>
            <li>
                <a href="../auth/logout.php" style="color:#2c3e50;font-weight:500;padding:6px 14px;border-radius:6px;text-decoration:none;">Logout</a>
            </li>
        <?php endif; ?>
    </ul>
</nav>

<!-- Hero Section Marketplace -->
<section class="marketplace-hero">
    <div class="marketplace-title">Marketplace Sepeda & Aksesoris</div>
    <div class="marketplace-tagline">
        Temukan berbagai pilihan sepeda dan aksesoris terbaik untuk kebutuhanmu.<br>
        Pilih kategori untuk filter produk sesuai keinginan.
    </div>
    <form method="GET" class="marketplace-filter">
        <select name="kategori" onchange="this.form.submit()">
            <option value="">Semua Kategori</option>
            <?php
            mysqli_data_seek($categories, 0);
            while($c = mysqli_fetch_assoc($categories)): ?>
                <option value="<?= $c['id'] ?>"
                    <?= (isset($_GET['kategori']) && $_GET['kategori'] == $c['id']) ? 'selected' : '' ?>>
                    <?= htmlspecialchars($c['nama_kategori']) ?>
                </option>
            <?php endwhile; ?>
        </select>
    </form>
</section>

<!-- LIST PRODUK -->
<div class="dashboard-menu">
    <?php if(mysqli_num_rows($products) > 0): ?>
        <?php while($p = mysqli_fetch_assoc($products)): ?>
            <a href="detail.php?id=<?= $p['id'] ?>" style="text-decoration:none;">
            <div class="menu-card fadein">
                <?php if(!empty($p['gambar'])): ?>
                    <img src="../assets/img/produk/<?= htmlspecialchars($p['gambar']) ?>"
                         alt="<?= htmlspecialchars($p['nama_produk']) ?>">
                <?php else: ?>
                    <img src="../assets/img/no-image.png" alt="No Image">
                <?php endif; ?>
                <h3><?= htmlspecialchars($p['nama_produk']) ?></h3>
                <p><strong>Kategori:</strong> <?= htmlspecialchars($p['nama_kategori']) ?></p>
                <p><strong>Harga:</strong> Rp <?= number_format($p['harga']) ?></p>
                <p class="stok"><strong>Stok:</strong> <?= $p['stok'] ?></p>
            </div>
            </a>
        <?php endwhile; ?>
    <?php else: ?>
        <div class="no-product">Tidak ada produk.</div>
    <?php endif; ?>
</div>

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
});
</script>
<!-- Footer -->
<footer class="footer" style="text-align:center; padding:18px 0; background:#f4f6f8; color:#030000; font-size:15px;">
    @Copyright by 23552011029_Fauzan Rizkika Kurnia_TIF RP 23 CNS B_UASWEB1
</footer>
</body>
</html>
