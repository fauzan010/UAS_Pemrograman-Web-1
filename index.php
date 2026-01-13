<?php
// Tidak perlu PHP logic di landing page ini
session_start();
$logged_in = isset($_COOKIE['user_id']);
$user_name = '';
if ($logged_in) {
    include "config/database.php";
    $uid = intval($_COOKIE['user_id']);
    $q = mysqli_query($conn, "SELECT nama FROM users WHERE id=$uid");
    $u = mysqli_fetch_assoc($q);
    $user_name = $u ? $u['nama'] : '';
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>WorldBike - Sepeda Masa Kini</title>
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link rel="stylesheet" href="assets/css/style.css">
    <style>
    /* --- Tambahan style khusus landing page --- */
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
        text-decoration: none; /* pastikan tidak ada underline */
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
    .navbar ul li a:hover, .navbar ul li a.active {
        background: #3498db;
        color: #fff;
        /* hapus semua properti underline/border/bottom */
        text-decoration: none;
        border-bottom: none;
        box-shadow: none;
        outline: none;
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
        .hero-title {
            font-size: 1.3rem;
        }
        .hero-tagline {
            font-size: 1rem;
        }
        .kategori-title {
            font-size: 1rem;
        }
        .about-title {
            font-size: 1rem;
        }
        .promo-title {
            font-size: 1rem;
        }
        .kategori-grid {
            gap: 12px;
        }
        .kategori-card h4 {
            font-size: 1rem;
        }
        .kategori-card p {
            font-size: 0.95rem;
        }
        .footer {
            font-size: 0.95rem;
        }
    }

    .hero {
        display: flex;
        flex-direction: column;
        align-items: center;
        justify-content: center;
        min-height: 60vh;
        text-align: center;
        padding: 60px 10vw 40px;
        background: linear-gradient(120deg, #f8fafc 60%, #e0eafc 100%);
        position: relative;
        overflow: hidden;
    }
    .hero-title {
        font-size: 2.8rem;
        font-weight: 800;
        color: #222;
        margin-bottom: 18px;
        letter-spacing: 1px;
        animation: fadeInDown 1s;
    }
    .hero-tagline {
        font-size: 1.3rem;
        color: #555;
        margin-bottom: 32px;
        animation: fadeIn 1.5s;
    }
    .hero-cta {
        padding: 16px 38px;
        font-size: 1.1rem;
        border: none;
        border-radius: 8px;
        background: #3498db;
        color: #fff;
        font-weight: bold;
        cursor: pointer;
        box-shadow: 0 4px 18px rgba(52,152,219,0.13);
        transition: background 0.2s, transform 0.2s;
        animation: fadeInUp 1.2s;
    }
    .hero-cta:hover {
        background: #217dbb;
        transform: translateY(-2px) scale(1.04);
    }

    /* Tentang */
    .about-section {
        display: flex;
        flex-wrap: wrap;
        align-items: center;
        gap: 40px;
        padding: 60px 7vw 40px;
        background: #fff;
        border-radius: 18px;
        margin: 40px auto 0;
        box-shadow: 0 8px 32px rgba(44,62,80,0.06);
        max-width: 1100px;
        opacity: 0;
        transform: translateY(40px);
        transition: all 0.7s;
    }
    .about-section.visible {
        opacity: 1;
        transform: none;
    }
    .about-text {
        flex: 1 1 320px;
    }
    .about-title {
        font-size: 2rem;
        font-weight: 700;
        color: #3498db;
        margin-bottom: 16px;
    }
    .about-desc {
        font-size: 1.1rem;
        color: #444;
        line-height: 1.7;
    }
    .about-img {
        flex: 1 1 260px;
        text-align: center;
    }
    .about-img img {
        width: 220px;
        max-width: 100%;
        border-radius: 16px;
        box-shadow: 0 8px 32px rgba(52,152,219,0.13);
        transition: transform 0.3s;
    }
    .about-img img:hover {
        transform: scale(1.04) rotate(-2deg);
    }

    /* Kategori Unggulan */
    .kategori-section {
        padding: 60px 7vw 40px;
        background: #f4f6f8;
        margin-top: 40px;
    }
    .kategori-title {
        font-size: 1.7rem;
        font-weight: 700;
        color: #2c3e50;
        margin-bottom: 32px;
        text-align: center;
    }
    .kategori-grid {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(220px, 1fr));
        gap: 28px;
        max-width: 1000px;
        margin: 0 auto;
    }
    .kategori-card {
        background: #fff;
        border-radius: 14px;
        box-shadow: 0 6px 24px rgba(44,62,80,0.07);
        padding: 28px 18px 22px;
        text-align: center;
        transition: transform 0.25s, box-shadow 0.25s;
        cursor: pointer;
        opacity: 0;
        transform: translateY(40px);
        transition: all 0.7s;
    }
    .kategori-card.visible {
        opacity: 1;
        transform: none;
    }
    .kategori-card:hover {
        transform: translateY(-8px) scale(1.04);
        box-shadow: 0 14px 32px rgba(52,152,219,0.13);
    }
    .kategori-card img {
        width: 70px;
        height: 70px;
        object-fit: contain;
        margin-bottom: 18px;
        border-radius: 50%;
        background: #eaf6fb;
        padding: 10px;
        box-shadow: 0 2px 8px rgba(52,152,219,0.08);
        transition: background 0.2s;
    }
    .kategori-card:hover img {
        background: #d0e8fa;
    }
    .kategori-card h4 {
        font-size: 1.15rem;
        color: #3498db;
        margin-bottom: 8px;
        font-weight: 700;
    }
    .kategori-card p {
        font-size: 0.98rem;
        color: #555;
        margin-bottom: 0;
    }

    /* Promo */
    .promo-section {
        padding: 60px 7vw 40px;
        background: linear-gradient(120deg, #e0eafc 0%, #f8fafc 100%);
        margin-top: 40px;
        text-align: center;
        border-radius: 18px;
        max-width: 1100px;
        margin-left: auto;
        margin-right: auto;
        box-shadow: 0 8px 32px rgba(44,62,80,0.06);
        opacity: 0;
        transform: translateY(40px);
        transition: all 0.7s;
    }
    .promo-section.visible {
        opacity: 1;
        transform: none;
    }
    .promo-title {
        font-size: 1.5rem;
        color: #217dbb;
        font-weight: 700;
        margin-bottom: 18px;
    }
    .promo-desc {
        font-size: 1.1rem;
        color: #444;
        margin-bottom: 22px;
    }
    .promo-highlight {
        display: inline-block;
        background: #3498db;
        color: #fff;
        padding: 12px 28px;
        border-radius: 8px;
        font-size: 1.1rem;
        font-weight: bold;
        box-shadow: 0 4px 18px rgba(52,152,219,0.13);
        margin-bottom: 10px;
        animation: fadeInUp 1.2s;
    }

    /* Footer */
    .footer {
        background: #fff;
        color: #030000;
        text-align: center;
        padding: 22px 0 18px;
        font-size: 1rem;
        margin-top: 60px;
        border-top: 1px solid #eaeaea;
        letter-spacing: 1px;
        position: relative;
        bottom: 0;
        width: 100%;
    }

    /* Animasi */
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

    /* --- Tambahan style untuk info sepeda dan aksesoris --- */
    .info-section {
        background: #fff;
        border-radius: 18px;
        box-shadow: 0 8px 32px rgba(44,62,80,0.06);
        max-width: 1100px;
        margin: 40px auto 0;
        padding: 50px 7vw 40px;
        display: flex;
        flex-wrap: wrap;
        gap: 40px;
        align-items: flex-start;
        opacity: 0;
        transform: translateY(40px);
        transition: all 0.7s;
    }
    .info-section.visible {
        opacity: 1;
        transform: none;
    }
    .info-main {
        flex: 2 1 340px;
        min-width: 280px;
    }
    .info-main h2 {
        font-size: 2rem;
        color: #3498db;
        margin-bottom: 18px;
        font-weight: 700;
    }
    .info-main p {
        font-size: 1.08rem;
        color: #444;
        margin-bottom: 18px;
        line-height: 1.7;
    }
    .info-list {
        margin-bottom: 18px;
    }
    .info-list h3 {
        font-size: 1.15rem;
        color: #217dbb;
        margin-bottom: 8px;
        font-weight: 600;
    }
    .info-list ul {
        margin-left: 0;
        padding-left: 20px;
        margin-bottom: 12px;
    }
    .info-list li {
        font-size: 1rem;
        color: #444;
        margin-bottom: 6px;
        list-style: disc;
    }
    .info-icons {
        display: flex;
        flex-direction: column;
        gap: 18px;
        margin-top: 10px;
    }
    .info-icons .icon-row {
        display: flex;
        align-items: flex-start;
        gap: 12px;
    }
    .info-icons .icon-row span {
        font-size: 1.5rem;
        width: 2.2em;
        display: inline-block;
        text-align: center;
    }
    .info-icons .icon-row .icon-title {
        font-weight: 600;
        color: #3498db;
        margin-bottom: 2px;
    }
    .info-icons .icon-row .icon-desc {
        color: #444;
        font-size: 1rem;
    }
    .info-side {
        flex: 1 1 260px;
        min-width: 220px;
        background: #f8fafc;
        border-radius: 14px;
        padding: 28px 18px;
        box-shadow: 0 4px 18px rgba(52,152,219,0.07);
        display: flex;
        flex-direction: column;
        gap: 18px;
    }
    .info-side h4 {
        color: #217dbb;
        font-size: 1.1rem;
        margin-bottom: 10px;
        font-weight: 700;
    }
    .info-side ul {
        padding-left: 18px;
        margin-bottom: 10px;
    }
    .info-side li {
        font-size: 0.98rem;
        color: #444;
        margin-bottom: 6px;
        list-style: disc;
    }
    .why-section {
        background: #eaf6fb;
        border-radius: 14px;
        padding: 28px 18px;
        margin-top: 18px;
        box-shadow: 0 2px 8px rgba(52,152,219,0.07);
    }
    .why-section h4 {
        color: #3498db;
        font-size: 1.08rem;
        margin-bottom: 8px;
        font-weight: 700;
    }
    .why-section ul {
        padding-left: 18px;
    }
    .why-section li {
        font-size: 0.98rem;
        color: #444;
        margin-bottom: 6px;
        list-style: disc;
    }
    /* Hero CTA baru */
    .hero-cta-box {
        background: #fff;
        border-radius: 16px;
        box-shadow: 0 8px 32px rgba(44,62,80,0.08);
        padding: 32px 24px 28px;
        margin-top: 32px;
        display: inline-block;
        animation: fadeInUp 1.2s;
        max-width: 480px;
    }
    .hero-cta-box .cta-title {
        font-size: 1.25rem;
        color: #222;
        font-weight: 600;
        margin-bottom: 10px;
    }
    .hero-cta-box .cta-desc {
        font-size: 1.08rem;
        color: #444;
        margin-bottom: 18px;
    }
    .hero-cta-box .hero-cta {
        margin-top: 0;
        width: 100%;
        font-size: 1.1rem;
        padding: 14px 0;
    }
    /* --- Modern List Styles --- */
    .modern-list {
        display: flex;
        flex-direction: column;
        gap: 18px;
        margin: 0 0 18px 0;
        padding: 0;
    }
    .modern-list .modern-list-item {
        background: linear-gradient(90deg, #e0eafc 0%, #f8fafc 100%);
        border-radius: 12px;
        box-shadow: 0 2px 10px rgba(52,152,219,0.06);
        padding: 18px 20px 16px 20px;
        display: flex;
        align-items: flex-start;
        gap: 16px;
        position: relative;
        border-left: 5px solid #3498db;
        transition: box-shadow 0.2s, border-color 0.2s;
    }
    .modern-list .modern-list-item:hover {
        box-shadow: 0 6px 24px rgba(52,152,219,0.13);
        border-left: 5px solid #217dbb;
    }
    .modern-list .modern-list-icon {
        font-size: 2rem;
        flex-shrink: 0;
        margin-top: 2px;
        color: #3498db;
        filter: drop-shadow(0 2px 6px rgba(52,152,219,0.10));
    }
    .modern-list .modern-list-content {
        flex: 1;
    }
    .modern-list .modern-list-title {
        font-weight: 600;
        color: #217dbb;
        margin-bottom: 2px;
        font-size: 1.08rem;
    }
    .modern-list .modern-list-desc {
        color: #444;
        font-size: 1rem;
        line-height: 1.6;
    }
    /* Modern checklist */
    .modern-checklist {
        display: flex;
        flex-direction: column;
        gap: 12px;
        margin: 0 0 10px 0;
        padding: 0;
    }
    .modern-checklist .check-item {
        display: flex;
        align-items: center;
        gap: 10px;
        background: #f8fafc;
        border-radius: 8px;
        padding: 10px 16px;
        font-size: 1rem;
        color: #217dbb;
        font-weight: 500;
        box-shadow: 0 1px 4px rgba(52,152,219,0.06);
    }
    .modern-checklist .check-icon {
        font-size: 1.2rem;
        color: #27ae60;
        margin-right: 2px;
    }
    /* Modern tips */
    .modern-tips {
        display: flex;
        flex-direction: column;
        gap: 10px;
        margin: 0;
        padding: 0;
    }
    .modern-tips .tip-item {
        display: flex;
        align-items: center;
        gap: 10px;
        background: #eaf6fb;
        border-radius: 8px;
        padding: 10px 16px;
        font-size: 0.98rem;
        color: #217dbb;
        font-weight: 500;
        box-shadow: 0 1px 4px rgba(52,152,219,0.06);
    }
    .modern-tips .tip-icon {
        font-size: 1.1rem;
        color: #3498db;
        margin-right: 2px;
    }
    @media (max-width: 900px) {
        .about-section {
            flex-direction: column;
            text-align: center;
            padding: 40px 4vw 30px;
        }
        .kategori-section, .promo-section {
            padding: 40px 4vw 30px;
        }
        .info-section {
            flex-direction: column;
            padding: 40px 4vw 30px;
        }
        .info-side {
            margin-top: 18px;
        }
    }
    @media (max-width: 600px) {
        .navbar {
            flex-direction: column;
            gap: 10px;
            padding: 16px 3vw;
        }
        .hero-title {
            font-size: 2rem;
        }
        .about-title {
            font-size: 1.3rem;
        }
        .kategori-title {
            font-size: 1.1rem;
        }
        .info-main h2 {
            font-size: 1.2rem;
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
        <li><a href="#" class="active" id="nav-home">Home</a></li>
        <li><a href="user/index.php">Marketplace</a></li>
        <?php if (!$logged_in): ?>
            <li><a href="auth/login.php" class="btn" style="margin-left:16px;">Login</a></li>
        <?php else: ?>
            <li>
                <span class="user-profile"><span style="font-size:1.2em;">👤</span><?= htmlspecialchars($user_name) ?></span>
            </li>
            <li>
                <a href="auth/logout.php" style="color:#2c3e50;font-weight:500;padding:6px 14px;border-radius:6px;text-decoration:none;">Logout</a>
            </li>
        <?php endif; ?>
    </ul>
</nav>

<!-- Hero Section -->
<section class="hero">
    <div class="hero-title">Temukan Sepeda Impianmu di WorldBike</div>
    <div class="hero-tagline">Marketplace sepeda modern, lengkap, dan terpercaya untuk semua kebutuhan bersepeda Anda.</div>
    <div class="hero-cta-box">
        <div class="cta-title">Temukan sepeda dan aksesoris terbaik sesuai kebutuhanmu.</div>
        <div class="cta-desc">
            Jelajahi koleksi kami sekarang dan mulai perjalanan sepedamu bersama WorldBike.
        </div>
        <a href="user/index.php">
            <button class="hero-cta">Masuk ke Marketplace</button>
        </a>
    </div>
</section>

<!-- Tentang -->
<section class="about-section fadein">
    <div class="about-text">
        <div class="about-title">Tentang WorldBike</div>
        <div class="about-desc">
            WorldBike adalah platform sepeda modern yang menghadirkan berbagai pilihan sepeda, aksesoris, dan perlengkapan terbaik. Kami berkomitmen memberikan pengalaman belanja sepeda yang mudah, aman, dan menyenangkan bagi semua kalangan, mulai dari pemula hingga profesional.
        </div>
    </div>
    <div class="about-img">
        <img src="assets/img/sepeda-about.png" alt="Tentang WorldBike" loading="lazy">
    </div>
</section>

<!-- Informasi Sepeda & Aksesoris -->
<section class="info-section fadein">
    <div class="info-main">
        <h2>🚴‍♂️ Tentang Sepeda</h2>
        <p>
            Sepeda adalah alat transportasi ramah lingkungan yang semakin populer karena praktis, menyehatkan, dan cocok untuk berbagai kebutuhan—mulai dari olahraga, rekreasi, hingga mobilitas harian.<br>
            Di WorldBike, kami menghadirkan beragam jenis sepeda berkualitas yang dirancang untuk kenyamanan, performa, dan gaya hidup modern.
        </p>
        <div class="info-list">
            <h3>🔥 Jenis-Jenis Sepeda</h3>
            <div class="modern-list">
                <div class="modern-list-item">
                    <span class="modern-list-icon">🚵‍♂️</span>
                    <div class="modern-list-content">
                        <div class="modern-list-title">Mountain Bike (MTB)</div>
                        <div class="modern-list-desc">
                            Dirancang untuk medan berat seperti tanah, bebatuan, dan jalur pegunungan.<br>
                            <b style="color:#217dbb;">Cocok untuk:</b> petualangan & olahraga ekstrem<br>
                            <b style="color:#217dbb;">Ciri khas:</b> ban tebal, suspensi kuat, rangka kokoh
                        </div>
                    </div>
                </div>
                <div class="modern-list-item">
                    <span class="modern-list-icon">🚴‍♀️</span>
                    <div class="modern-list-content">
                        <div class="modern-list-title">Road Bike</div>
                        <div class="modern-list-desc">
                            Sepeda ringan dengan ban tipis untuk kecepatan tinggi di jalan aspal.<br>
                            <b style="color:#217dbb;">Cocok untuk:</b> balap & olahraga jarak jauh<br>
                            <b style="color:#217dbb;">Ciri khas:</b> rangka aerodinamis, posisi berkendara menunduk
                        </div>
                    </div>
                </div>
                <div class="modern-list-item">
                    <span class="modern-list-icon">🚲</span>
                    <div class="modern-list-content">
                        <div class="modern-list-title">Sepeda Lipat</div>
                        <div class="modern-list-desc">
                            Solusi praktis untuk mobilitas perkotaan.<br>
                            <b style="color:#217dbb;">Cocok untuk:</b> kerja, commuting, travel<br>
                            <b style="color:#217dbb;">Ciri khas:</b> bisa dilipat, ringan, mudah disimpan
                        </div>
                    </div>
                </div>
                <div class="modern-list-item">
                    <span class="modern-list-icon">🚴</span>
                    <div class="modern-list-content">
                        <div class="modern-list-title">Sepeda Hybrid</div>
                        <div class="modern-list-desc">
                            Gabungan antara MTB dan Road Bike.<br>
                            <b style="color:#217dbb;">Cocok untuk:</b> penggunaan harian & rekreasi<br>
                            <b style="color:#217dbb;">Ciri khas:</b> nyaman, serbaguna, fleksibel di berbagai medan
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="info-list">
            <h3>🧢 Aksesoris Sepeda</h3>
            <div class="info-icons">
                <div class="icon-row">
                    <span>🪖</span>
                    <div>
                        <div class="icon-title">Helm</div>
                        <div class="icon-desc">Melindungi kepala dari benturan dan cedera. Wajib digunakan untuk keselamatan berkendara.</div>
                    </div>
                </div>
                <div class="icon-row">
                    <span>🔦</span>
                    <div>
                        <div class="icon-title">Lampu Sepeda</div>
                        <div class="icon-desc">Membantu visibilitas saat malam atau kondisi minim cahaya. Tersedia lampu depan & belakang.</div>
                    </div>
                </div>
                <div class="icon-row">
                    <span>🧤</span>
                    <div>
                        <div class="icon-title">Sarung Tangan</div>
                        <div class="icon-desc">Mengurangi getaran, meningkatkan grip, dan melindungi tangan.</div>
                    </div>
                </div>
                <div class="icon-row">
                    <span>🧰</span>
                    <div>
                        <div class="icon-title">Peralatan & Sparepart</div>
                        <div class="icon-desc">Pompa ban, kunci sepeda, ban & rantai, gear & rem. Semua tersedia untuk perawatan dan peningkatan performa sepeda.</div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="info-side">
        <h4>🌍 Kenapa Memilih WorldBike?</h4>
        <div class="modern-checklist">
            <div class="check-item"><span class="check-icon">✔</span>Produk berkualitas</div>
            <div class="check-item"><span class="check-icon">✔</span>Desain modern & kekinian</div>
            <div class="check-item"><span class="check-icon">✔</span>Cocok untuk pemula hingga profesional</div>
            <div class="check-item"><span class="check-icon">✔</span>Mendukung gaya hidup sehat & ramah lingkungan</div>
        </div>
        <div class="why-section">
            <h4>Tips Aman Bersepeda</h4>
            <div class="modern-tips">
                <div class="tip-item"><span class="tip-icon">🦺</span>Gunakan helm & perlengkapan keselamatan</div>
                <div class="tip-item"><span class="tip-icon">🔧</span>Periksa kondisi sepeda sebelum digunakan</div>
                <div class="tip-item"><span class="tip-icon">🚦</span>Patuhi rambu lalu lintas</div>
                <div class="tip-item"><span class="tip-icon">👀</span>Selalu waspada di jalan</div>
            </div>
        </div>
    </div>
</section>

<!-- Kategori Unggulan -->
<section class="kategori-section" id="kategori">
    <div class="kategori-title">Kategori Unggulan</div>
    <div class="kategori-grid">
        <a href="user/index.php?kategori=1" class="kategori-card fadein" style="text-decoration:none;">
            <img src="assets/img/mtb.png" alt="Sepeda Gunung" loading="lazy">
            <h4>Sepeda Gunung</h4>
            <p>Tangguh untuk segala medan, cocok untuk petualang sejati.</p>
        </a>
        <a href="user/index.php?kategori=2" class="kategori-card fadein" style="text-decoration:none;">
            <img src="assets/img/lipat.png" alt="Sepeda Lipat" loading="lazy">
            <h4>Sepeda Lipat</h4>
            <p>Praktis, ringan, dan mudah dibawa ke mana saja.</p>
        </a>
        <a href="user/index.php?kategori=3" class="kategori-card fadein" style="text-decoration:none;">
            <img src="assets/img/anak.png" alt="Sepeda Anak" loading="lazy">
            <h4>Sepeda Anak</h4>
            <p>Aman dan menyenangkan untuk buah hati Anda.</p>
        </a>
        <a href="user/index.php?kategori=4" class="kategori-card fadein" style="text-decoration:none;">
            <img src="assets/img/aksesoris.png" alt="Aksesoris" loading="lazy">
            <h4>Aksesoris</h4>
            <p>Lengkapi gaya dan keamanan bersepeda Anda.</p>
        </a>
    </div>
</section>

<!-- Promo / Highlight -->
<section class="promo-section fadein">
    <div class="promo-title">Promo & Highlight</div>
    <div class="promo-desc">
        Dapatkan penawaran spesial untuk pembelian sepeda dan aksesoris di WorldBike! <br>
        Nikmati diskon hingga <b>30%</b> untuk produk pilihan setiap bulannya.
    </div>
    <div class="promo-highlight">
        🚴‍♂️ Gratis Ongkir & Cicilan 0% untuk pembelian pertama!
    </div>
</section>

    <!-- Footer -->
    <footer class="footer" style="text-align:center; padding:18px 0; background:#f4f6f8; color:#888; font-size:15px; position:relative; bottom:0; width:100%;">
        @Copyright by 23552011029_Fauzan Rizkika Kurnia_TIF RP 23 CNS B_UASWEB1
    </footer>
</body>
</html>

<!-- Fade-in on scroll JS -->
<script>
document.addEventListener("DOMContentLoaded", function() {
    // Fade-in on scroll
    function revealOnScroll() {
        var elements = document.querySelectorAll('.fadein, .kategori-card, .about-section, .promo-section, .info-section');
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

    // Smooth scroll for navbar Home & Kategori + active state
    var navHome = document.getElementById('nav-home');
    var navKategori = document.getElementById('nav-kategori');
    var kategoriSection = document.getElementById('kategori');

    navHome.addEventListener('click', function(e) {
        e.preventDefault();
        window.scrollTo({ top: 0, behavior: 'smooth' });
        navHome.classList.add('active');
        navKategori.classList.remove('active');
    });

    navKategori.addEventListener('click', function(e) {
        e.preventDefault();
        if (kategoriSection) {
            kategoriSection.scrollIntoView({ behavior: 'smooth' });
        }
        navHome.classList.remove('active');
        navKategori.classList.add('active');
    });

    // Auto update active state on scroll
    window.addEventListener('scroll', function() {
        var kategoriTop = kategoriSection.getBoundingClientRect().top + window.scrollY - 80;
        if (window.scrollY >= kategoriTop - 10) {
            navHome.classList.remove('active');
            navKategori.classList.add('active');
        } else {
            navHome.classList.add('active');
            navKategori.classList.remove('active');
        }
    });

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
<?php
// Setelah login sukses
$redirect = isset($_GET['redirect']) ? $_GET['redirect'] : 'index.php';
header("Location: $redirect");
exit;
