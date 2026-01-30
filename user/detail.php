<?php
include "../config/database.php";
session_start();

// Sinkronkan session jika user login via cookie
if (!isset($_SESSION['user_id']) && isset($_COOKIE['user_id'])) {
    $_SESSION['user_id'] = $_COOKIE['user_id'];
    $_SESSION['role'] = $_COOKIE['role'] ?? 'user';
}

$id = isset($_GET['id']) ? intval($_GET['id']) : 0;
if ($id <= 0) {
    header("Location: index.php");
    exit;
}

$q = mysqli_query($conn, "
    SELECT products.*, categories.nama_kategori
    FROM products
    LEFT JOIN categories ON products.category_id = categories.id
    WHERE products.id = $id
");
$product = mysqli_fetch_assoc($q);
if (!$product) {
    header("Location: index.php");
    exit;
}

$is_logged_in = isset($_SESSION['user_id']);
$user_name = '';
if ($is_logged_in) {
    $uid = intval($_SESSION['user_id']);
    $uq = mysqli_query($conn, "SELECT nama FROM users WHERE id=$uid");
    $u = mysqli_fetch_assoc($uq);
    $user_name = $u ? $u['nama'] : '';
}

$cart_msg = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!$is_logged_in) {
        $cart_msg = "Anda harus login untuk melanjutkan!";
        // Tampilkan modal login via JS
        echo "<script>window.addEventListener('DOMContentLoaded',function(){showModalLogin();});</script>";
    } elseif (isset($_POST['add_to_cart'])) {
        $qty = isset($_POST['qty']) ? max(1, intval($_POST['qty'])) : 1;
        if ($product['stok'] >= $qty) {
            if (!isset($_SESSION['cart'])) $_SESSION['cart'] = [];
            if (isset($_SESSION['cart'][$id])) {
                $_SESSION['cart'][$id]['qty'] += $qty;
            } else {
                $_SESSION['cart'][$id] = [
                    'id' => $product['id'],
                    'nama_produk' => $product['nama_produk'],
                    'harga' => $product['harga'],
                    'gambar' => $product['gambar'],
                    'qty' => $qty
                ];
            }
            $cart_msg = "Produk berhasil ditambahkan ke keranjang!";
        } else {
            $cart_msg = "Stok habis, tidak bisa menambah ke keranjang.";
        }
    } elseif (isset($_POST['beli_sekarang'])) {
        // Redirect ke checkout jika login
        header("Location: checkout.php?id=$id");
        exit;
    }
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title><?= htmlspecialchars($product['nama_produk']) ?> - Detail Produk | WorldBike</title>
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css"
          rel="stylesheet"
          integrity="sha384-QWTKZyjpPEjISv5WaRU9OFeRpok6YctnYmDr5pNlyT2bRjXh0JMhjY6hW+ALEwIH"
          crossorigin="anonymous">
    <link rel="stylesheet" href="../assets/css/style.css">
    <style>
    body {
        background: radial-gradient(circle at 20% 20%, rgba(52,152,219,0.12), transparent 35%),
                    radial-gradient(circle at 80% 0%, rgba(46,204,113,0.10), transparent 30%),
                    linear-gradient(120deg, #e0eafc 0%, #cfdef3 100%);
        min-height: 100vh;
        padding-top: 90px;
        padding-bottom: 32px;
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
        top: 0;
        left: 0;
        right: 0;
        z-index: 1000;
        width: 100%;
        border-radius: 0;
        margin: 0;
    }
    .navbar .logo {
        font-size: 2rem;
        font-weight: bold;
        color: #3498db;
        letter-spacing: 2px;
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
    .detail-wrap {
        max-width: 1080px;
        margin: 32px auto;
        padding: 0 18px;
        flex: 1;
        width: 100%;
    }
    .detail-container {
        background: #fff;
        border-radius: 20px;
        box-shadow: 0 20px 60px rgba(44,62,80,0.14);
        display: grid;
        grid-template-columns: 1.1fr 1fr;
        gap: 28px;
        padding: 30px 28px;
        position: relative;
        overflow: hidden;
    }
    .detail-container::before {
        content: '';
        position: absolute;
        inset: 0;
        background: linear-gradient(135deg, rgba(52,152,219,0.06), rgba(46,204,113,0.05));
        pointer-events: none;
    }
    .detail-container > * { position: relative; z-index: 1; }
    .detail-img {
        text-align: center;
    }
    .detail-img .img-frame {
        position: relative;
        border-radius: 18px;
        overflow: hidden;
        box-shadow: 0 20px 45px rgba(44,62,80,0.18);
        background: radial-gradient(circle at 20% 20%, rgba(52,152,219,0.12), rgba(234,246,251,0.9));
        transition: transform 0.35s ease, box-shadow 0.35s ease;
    }
    .detail-img .img-frame:hover {
        transform: translateY(-4px);
        box-shadow: 0 28px 60px rgba(44,62,80,0.18);
    }
    .detail-img img {
        width: 100%;
        max-width: 480px;
        display: block;
        margin: 0 auto;
        object-fit: cover;
    }
    .img-badge {
        position: absolute;
        top: 12px;
        left: 12px;
        background: rgba(255,255,255,0.9);
        color: #217dbb;
        padding: 8px 12px;
        border-radius: 12px;
        font-weight: 700;
        box-shadow: 0 6px 16px rgba(44,62,80,0.12);
    }
    .detail-info {
        display: flex;
        flex-direction: column;
        gap: 14px;
    }
    .detail-title {
        font-size: 2rem;
        font-weight: 800;
        color: #1b3a57;
        margin-bottom: 4px;
    }
    .pill {
        display: inline-block;
        padding: 8px 12px;
        border-radius: 999px;
        background: #eaf6fb;
        color: #217dbb;
        font-weight: 700;
        font-size: 0.98rem;
        margin-right: 6px;
    }
    .detail-harga {
        font-size: 1.8rem;
        color: #0f6abf;
        font-weight: 900;
        letter-spacing: 0.3px;
    }
    .detail-stok {
        font-size: 1rem;
        color: #444;
    }
    .badge-stock {
        display: inline-block;
        padding: 6px 10px;
        border-radius: 999px;
        font-weight: 700;
        font-size: 0.9rem;
        margin-left: 6px;
    }
    .stock-ready { background: #e8f8f0; color: #1e8a4b; }
    .stock-low { background: #fff4e5; color: #c77400; }
    .stock-out { background: #ffecec; color: #c0392b; }
    .detail-desc {
        font-size: 1.04rem;
        color: #3b4a5a;
        line-height: 1.7;
        background: #f8fafc;
        border-radius: 12px;
        padding: 14px 16px;
    }
    .info-grid {
        display: grid;
        grid-template-columns: repeat(3, 1fr);
        gap: 12px;
    }
    .info-card {
        background: #fff;
        border: 1px solid #eef2f5;
        border-radius: 12px;
        padding: 12px 14px;
        box-shadow: 0 6px 18px rgba(44,62,80,0.07);
        transition: transform 0.2s ease, box-shadow 0.2s ease;
    }
    .info-card:hover { transform: translateY(-3px); box-shadow: 0 12px 26px rgba(44,62,80,0.12); }
    .info-card h5 {
        margin: 0 0 4px 0;
        font-size: 0.95rem;
        color: #217dbb;
        font-weight: 700;
    }
    .info-card p {
        margin: 0;
        color: #444;
        font-size: 0.95rem;
    }
    .form-row {
        display: flex;
        gap: 12px;
        flex-wrap: wrap;
        align-items: center;
    }
    .qty-input {
        width: 110px;
        padding: 10px 12px;
        border-radius: 10px;
        border: 1px solid #dce4ec;
        background: #f8fafc;
        font-weight: 700;
    }
    .btn-beli {
        background: linear-gradient(135deg, #2ecc71, #27ae60);
        color: #fff;
        padding: 14px 16px;
        border-radius: 10px;
        font-size: 1.05rem;
        font-weight: 800;
        border: none;
        cursor: pointer;
        transition: transform 0.15s, box-shadow 0.2s;
        box-shadow: 0 10px 24px rgba(46,204,113,0.28);
        flex: 1;
        text-align: center;
    }
    .btn-beli.primary { background: linear-gradient(135deg, #3498db, #217dbb); box-shadow: 0 12px 28px rgba(52,152,219,0.25); }
    .btn-beli:hover { transform: translateY(-1px); }
    .badge-note {
        background: #eaf6fb;
        color: #217dbb;
        padding: 10px 12px;
        border-radius: 10px;
        font-weight: 600;
        font-size: 0.95rem;
    }
    .nav-actions {
        display: flex;
        gap: 10px;
        flex-wrap: wrap;
        margin-top: 10px;
    }
    .nav-btn {
        flex: 1 1 180px;
        padding: 12px 14px;
        border-radius: 10px;
        font-weight: 750;
        text-decoration: none;
        text-align: center;
        border: 1px solid #dce4ec;
        color: #1b3a57;
        background: #f8fafc;
        transition: transform 0.15s ease, box-shadow 0.2s ease, border 0.2s ease;
    }
    .nav-btn:hover {
        transform: translateY(-1px);
        box-shadow: 0 10px 24px rgba(44,62,80,0.1);
        border-color: #bcd2e6;
    }
    .nav-btn.primary {
        background: linear-gradient(135deg, #3498db, #217dbb);
        color: #fff;
        border: none;
        box-shadow: 0 10px 24px rgba(52,152,219,0.25);
    }
    .nav-btn.primary:hover {
        box-shadow: 0 14px 30px rgba(52,152,219,0.32);
    }
    .modal-login {
        display: none;
        position: fixed;
        z-index: 9999;
        inset: 0;
        width: 100vw;
        height: 100vh;
        background: rgba(26,39,53,0.35);
        backdrop-filter: blur(4px);
        align-items: center;
        justify-content: center;
        padding: 16px;
    }
    .modal-login-content {
        background: #ffffff;
        padding: 28px 24px;
        border-radius: 14px;
        box-shadow: 0 18px 40px rgba(15,50,82,0.25);
        text-align: center;
        max-width: 360px;
        width: 100%;
        border: 1px solid #e8eef4;
        animation: popIn 0.25s ease;
    }
    .modal-login-content h3 {
        color: #2d7bdc;
        margin-bottom: 10px;
        font-size: 1.2rem;
        font-weight: 800;
    }
    .modal-login-content p {
        color: #4a5568;
        margin-bottom: 18px;
        line-height: 1.5;
        font-size: 0.98rem;
    }
    .modal-actions {
        display: grid;
        grid-template-columns: repeat(2, minmax(0, 1fr));
        gap: 10px;
        margin-bottom: 10px;
    }
    .modal-login-content .btn {
        border: none;
        cursor: pointer;
        display: inline-block;
        width: 100%;
        padding: 12px 14px;
        border-radius: 10px;
        font-weight: 750;
        font-size: 1.02rem;
        color: #fff;
        box-shadow: 0 10px 24px rgba(52,152,219,0.25);
        transition: transform 0.15s ease, box-shadow 0.2s ease;
    }
    .modal-login-content .btn-register { background: linear-gradient(135deg, #2ecc71, #27ae60); }
    .modal-login-content .btn { background: linear-gradient(135deg, #3498db, #217dbb); }
    .modal-login-content .btn:hover { transform: translateY(-1px); box-shadow: 0 14px 30px rgba(52,152,219,0.32); }
    .modal-login-content .btn-cancel {
        background: #7a7f86;
        color: #fff;
        box-shadow: none;
        margin-top: 4px;
    }
    .modal-login-content .btn-cancel:hover { background: #5f646b; transform: none; }
    @keyframes popIn {
        from { opacity: 0; transform: scale(0.95); }
        to { opacity: 1; transform: scale(1); }
    }
    @media (max-width: 1024px) {
        .detail-container { grid-template-columns: 1fr; }
    }
    @media (max-width: 900px) {
        .detail-container { padding: 24px 18px; }
        .info-grid { grid-template-columns: repeat(2, 1fr); }
    }
    @media (max-width: 600px) {
        html { font-size: 15px; }
        .navbar { padding: 12px 3vw; margin: 12px 3vw 0; }
        .navbar .logo { font-size: 1.4rem; }
        .navbar .menu-toggle { display: block; position: absolute; right: 18px; top: 18px; }
        .navbar ul { flex-direction: column; gap: 0; align-items: flex-start; background: #fff; position: absolute; top: 100%; right: 0; left: 0; box-shadow: 0 2px 18px rgba(44,62,80,0.07); border-radius: 0 0 12px 12px; padding: 10px 0 10px 0; display: none; z-index: 20; }
        .navbar ul.show { display: flex; }
        .navbar ul li { width: 100%; }
        .navbar ul li a, .navbar ul li span { display: block; width: 100%; padding: 12px 18px; font-size: 1.08rem; }
        .navbar ul li.nav-right { margin-left: 0; }
        .detail-title { font-size: 1.3rem; }
        .detail-harga { font-size: 1.2rem; }
        .detail-desc { font-size: 0.98rem; }
        .btn-beli { font-size: 1rem; padding: 12px; }
        .info-grid { grid-template-columns: 1fr; }
    }
    .footer {
        text-align: center;
        padding: 18px 0;
        background: #f4f6f8;
        color: #888;
        font-size: 15px;
        width: 100vw;
        margin-left: calc(50% - 50vw);
        margin-right: calc(50% - 50vw);
        margin-top: auto;
        padding-left: 16px;
        padding-right: 16px;
    }
    </style>
</head>
<body>
<nav class="navbar">
    <div class="logo">WorldBike</div>
    <button class="menu-toggle" id="menuToggle">&#9776;</button>
    <ul id="navbarMenu">
        <li><a href="../index.php">Home</a></li>
        <li><a href="index.php" class="active">Marketplace</a></li>
        <li><a href="cart.php" class="cart-link">Keranjang</a></li>
        <?php if ($is_logged_in): ?>
            <li class="nav-right"><a href="profile.php" class="profile-link">Hi, <?= htmlspecialchars($user_name ?: 'Pengguna') ?></a></li>
        <?php else: ?>
            <li class="nav-right"><a href="../auth/login.php?redirect=<?= urlencode($_SERVER['REQUEST_URI']) ?>" class="login-link">Login</a></li>
        <?php endif; ?>
    </ul>
</nav>
<div class="detail-wrap">
    <div class="detail-container">
        <div class="detail-img">
            <div class="img-frame">
                <span class="img-badge">Kategori: <?= htmlspecialchars($product['nama_kategori']) ?></span>
                <img src="../assets/img/produk/<?= htmlspecialchars($product['gambar']) ?>" alt="<?= htmlspecialchars($product['nama_produk']) ?>">
            </div>
        </div>
        <div class="detail-info">
            <div class="detail-title"><?= htmlspecialchars($product['nama_produk']) ?></div>
            <div>
                <span class="pill">Kategori: <?= htmlspecialchars($product['nama_kategori']) ?></span>
                <span class="pill">ID #<?= $product['id'] ?></span>
            </div>
            <div class="detail-harga">Rp <?= number_format($product['harga']) ?></div>
            <?php
                $stok = intval($product['stok']);
                $stockClass = $stok <= 0 ? 'stock-out' : ($stok <= 5 ? 'stock-low' : 'stock-ready');
                $stockLabel = $stok <= 0 ? 'Stok habis' : ($stok <= 5 ? 'Stok menipis' : 'Ready stock');
            ?>
            <div class="detail-stok">Stok: <?= $stok ?> <span class="badge-stock <?= $stockClass ?>"><?= $stockLabel ?></span></div>
            <div class="detail-desc"><?= nl2br(htmlspecialchars($product['deskripsi'])) ?></div>

            <div class="info-grid">
                <div class="info-card">
                    <h5>Pengiriman</h5>
                    <p>Estimasi 1-3 hari kerja, gratis ongkir promo tertentu.</p>
                </div>
                <div class="info-card">
                    <h5>Garansi</h5>
                    <p>Garansi toko 14 hari untuk cacat produksi.</p>
                </div>
                <div class="info-card">
                    <h5>Retur</h5>
                    <p>Retur mudah, hubungi CS kami dalam 3 hari.</p>
                </div>
            </div>

            <?php if (!empty($cart_msg)): ?>
                <div class="badge-note" style="margin-top:4px;"><?= htmlspecialchars($cart_msg) ?></div>
            <?php endif; ?>

            <form id="actionForm" method="post" style="display:flex;flex-direction:column;gap:12px;">
                <?php if ($product['stok'] > 0): ?>
                    <div class="form-row">
                        <label for="qty">Jumlah</label>
                        <input class="qty-input" type="number" min="1" max="<?= $stok ?>" name="qty" id="qty" value="1">
                    </div>
                    <div class="form-row">
                        <button type="submit" name="add_to_cart" class="btn-beli">+ Keranjang</button>
                        <button type="submit" name="beli_sekarang" class="btn-beli primary">Beli Sekarang</button>
                    </div>
                <?php else: ?>
                    <div class="badge-note">Stok habis, produk tidak dapat dibeli saat ini.</div>
                <?php endif; ?>
            </form>
        </div>
    </div>
    
<!-- Modal Login/Register -->
<div class="modal-login" id="modalLogin">
    <div class="modal-login-content">
        <h3>Anda harus login dulu</h3>
        <p>Belum punya akun? Silakan daftar terlebih dahulu.<br>Sudah punya akun? Silakan login untuk melanjutkan.</p>
        <div class="modal-actions">
            <button class="btn btn-register" onclick="window.location.href='/worldbike/auth/register.php?redirect=/worldbike/user/detail.php?id=<?= $product['id'] ?>'">Register</button>
            <button class="btn" onclick="window.location.href='/worldbike/auth/login.php?redirect=/worldbike/user/detail.php?id=<?= $product['id'] ?>'">Login</button>
        </div>
        <button class="btn btn-cancel" onclick="closeModalLogin()">Batal</button>
    </div>
</div>

<footer class="footer">
    @Copyright by 23552011029_Fauzan Rizkika Kurnia_TIF RP 23 CNS B_UASWEB1
</footer>

<script>
function isLoggedIn() {
    return <?= $is_logged_in ? 'true' : 'false' ?>;
}
function showModalLogin() {
    document.getElementById('modalLogin').style.display = 'flex';
}
function closeModalLogin() {
    document.getElementById('modalLogin').style.display = 'none';
}

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
    // JS validasi login (prevent submit jika belum login)
    var actionForm = document.getElementById('actionForm');
    if (actionForm) {
        actionForm.addEventListener('submit', function(e) {
            if (!isLoggedIn()) {
                e.preventDefault();
                showModalLogin();
            }
        });
    }
});
</script>

<!-- Bootstrap JS -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"
    integrity="sha384-YvpcrYf0tY3lHB60NNkmXc5s9fDVZLESaAA55NDzOxhy9GkcIdslK1eN7N6jIeHz"
    crossorigin="anonymous"></script>

</body>
</html>
