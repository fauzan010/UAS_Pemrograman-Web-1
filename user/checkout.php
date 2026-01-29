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

$mode_cart = isset($_GET['mode']) && $_GET['mode'] === 'cart';
$items = [];

if ($mode_cart) {
    $cart = isset($_SESSION['cart']) ? $_SESSION['cart'] : [];
    if (empty($cart)) {
        header("Location: cart.php");
        exit;
    }
    foreach ($cart as $c) {
        $pid = intval($c['id']);
        $res = mysqli_query($conn, "
            SELECT products.*, categories.nama_kategori
            FROM products
            LEFT JOIN categories ON products.category_id = categories.id
            WHERE products.id = $pid
        ");
        if ($prod = mysqli_fetch_assoc($res)) {
            $items[] = [
                'id' => $prod['id'],
                'nama_produk' => $prod['nama_produk'],
                'nama_kategori' => $prod['nama_kategori'],
                'harga' => $prod['harga'],
                'stok' => $prod['stok'],
                'gambar' => $prod['gambar'],
                'qty' => intval($c['qty']) > 0 ? intval($c['qty']) : 1,
            ];
        }
    }
    if (empty($items)) {
        header("Location: cart.php");
        exit;
    }
} else {
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
    $items[] = [
        'id' => $product['id'],
        'nama_produk' => $product['nama_produk'],
        'nama_kategori' => $product['nama_kategori'],
        'harga' => $product['harga'],
        'stok' => $product['stok'],
        'gambar' => $product['gambar'],
        'qty' => 1,
    ];
}

$total_harga = 0;
foreach ($items as $it) {
    $total_harga += $it['harga'] * $it['qty'];
}

// Proses checkout
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nama = trim($_POST['nama']);
    $alamat = trim($_POST['alamat']);
    $telepon = trim($_POST['telepon']);
    $metode = trim($_POST['metode'] ?? '');
    $bank = trim($_POST['bank'] ?? '');
    $ewallet = trim($_POST['ewallet'] ?? '');
    $metode_final = $metode;

    if ($metode === 'transfer') {
        if (!$bank) {
            $error = "Pilih bank terlebih dahulu.";
        } else {
            $metode_final = 'Transfer - ' . $bank;
        }
    } elseif ($metode === 'ewallet') {
        if (!$ewallet) {
            $error = "Pilih e-wallet terlebih dahulu.";
        } else {
            $metode_final = 'E-Wallet - ' . $ewallet;
        }
    } elseif ($metode === 'cod') {
        $metode_final = 'COD';
    }

    // Validasi stok semua item
    foreach ($items as $it) {
        if ($it['stok'] < $it['qty']) {
            $error = "Stok produk " . $it['nama_produk'] . " tidak mencukupi.";
            break;
        }
    }

    if ($nama && $alamat && $telepon && $metode && empty($error)) {
        $user_id = $_SESSION['user_id'];
        date_default_timezone_set('Asia/Jakarta');
        $tanggal = date('Y-m-d H:i:s');
        $status = 'Dikemas';
        $order_id_first = null;

        foreach ($items as $it) {
            mysqli_query($conn, "UPDATE products SET stok = stok - " . intval($it['qty']) . " WHERE id = " . intval($it['id']));
            $stmt = mysqli_prepare($conn, "INSERT INTO orders (user_id, product_id, qty, harga, metode_bayar, alamat, nama_penerima, telepon, tanggal, status) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
            $harga_satuan = $it['harga'];
            mysqli_stmt_bind_param($stmt, "iiisssssss", $user_id, $it['id'], $it['qty'], $harga_satuan, $metode_final, $alamat, $nama, $telepon, $tanggal, $status);
            mysqli_stmt_execute($stmt);
            if ($order_id_first === null) {
                $order_id_first = mysqli_insert_id($conn);
            }
        }

        if ($mode_cart) {
            unset($_SESSION['cart']);
        }

        header("Location: payment_success.php?order_id=" . intval($order_id_first));
        exit;
    } elseif (empty($error)) {
        $error = "Semua field wajib diisi!";
    }
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Checkout - <?= htmlspecialchars($product['nama_produk']) ?> | WorldBike</title>
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
        padding-top: 80px;
    }
    .navbar {
        display: flex;
        justify-content: space-between;
        align-items: center;
        padding: 22px 7vw;
        background: #ffffff;
        box-shadow: 0 2px 18px rgba(44,62,80,0.06);
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
        color: #2d8fe6;
        letter-spacing: 2px;
        text-shadow: 0 2px 8px rgba(45,143,230,0.1);
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
    .navbar ul li a {
        font-size: 1.05rem;
        font-weight: 600;
        padding: 6px 14px;
        border-radius: 8px;
        color: #1f2d3d;
        display: block;
        transition: background 0.2s, color 0.2s;
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
    .navbar ul li.nav-right { margin-left: auto; }
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
    .navbar ul li a:hover, .navbar ul li a.active {
        background: #64b5f6;
        color: #0f172a;
    }
    .profile-menu { position: relative; }
    .profile-trigger {
        display: inline-flex;
        align-items: center;
        gap: 8px;
        padding: 6px 14px;
        background: #eaf4ff;
        color: #2d8fe6;
        font-weight: 700;
        border: none;
        border-radius: 10px;
        cursor: pointer;
        transition: background 0.2s, color 0.2s;
    }
    .profile-trigger:hover { background: #d7eafe; }
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
    .profile-dropdown a:hover { background: #f1f7ff; color: #217dbb; }
    .profile-dropdown a.logout { color: #b91c1c; }
    .profile-dropdown a.logout:hover { background: #fff1f2; }
    @media (max-width: 600px) {
        .navbar { padding: 12px 3vw; }
        .navbar .logo { font-size: 1.4rem; }
        .navbar .menu-toggle { display: block; position: absolute; right: 18px; top: 18px; }
        .navbar ul { flex-direction: column; gap: 0; align-items: flex-start; background: #fff; position: absolute; top: 100%; right: 0; left: 0; box-shadow: 0 2px 18px rgba(44,62,80,0.07); border-radius: 0 0 12px 12px; padding: 10px 0; display: none; z-index: 20; }
        .navbar ul.show { display: flex; }
        .navbar ul li { width: 100%; }
        .navbar ul li a { display: block; width: 100%; padding: 12px 18px; font-size: 1.08rem; }
        .navbar ul li.nav-right { margin-left: 0; }
    }
    .checkout-wrapper {
        max-width: 1200px;
        margin: 28px auto 46px auto;
        padding: 0 18px;
    }
    .checkout-header {
        text-align: center;
        margin-bottom: 18px;
    }
    .checkout-header h2 {
        color: #0f6abf;
        font-weight: 800;
        font-size: 1.8rem;
        margin-bottom: 8px;
    }
    .checkout-header p { color: #4a5560; margin: 0; }
    .content-shell {
        background: #ffffff;
        border: 1px solid #e5eefb;
        border-radius: 22px;
        box-shadow: 0 16px 36px rgba(44,62,80,0.06);
        padding: 18px;
    }
    .checkout-layout {
        display: flex;
        flex-direction: column;
        gap: 14px;
    }
    .card-box {
        background: #ffffff;
        border-radius: 20px;
        box-shadow: 0 12px 28px rgba(44,62,80,0.07);
        padding: 20px 20px 22px;
        position: relative;
        overflow: hidden;
        border: 1px solid #e8f1fb;
    }
    .card-box:before {
        content: "";
        position: absolute;
        inset: 0;
        background: linear-gradient(135deg, rgba(100,181,246,0.14), rgba(45,143,230,0.08));
        opacity: 0.55;
        pointer-events: none;
    }
    .card-content { position: relative; z-index: 1; }
    .item-list {
        display: flex;
        flex-direction: column;
        gap: 12px;
        margin-bottom: 10px;
    }
    .item-row {
        display: grid;
        grid-template-columns: 70px 1fr 120px 90px;
        gap: 12px;
        align-items: center;
        padding: 12px;
        border: 1px solid #e9f0fb;
        border-radius: 14px;
        background: #f9fbff;
    }
    .item-row img {
        width: 70px;
        height: 70px;
        object-fit: cover;
        border-radius: 10px;
        background: #eaf6fb;
        box-shadow: 0 4px 10px rgba(52,152,219,0.12);
    }
    .item-name { font-weight: 800; color: #15314b; margin-bottom: 4px; }
    .item-meta { color: #4f6074; font-size: 0.95rem; }
    .badge-row { display: flex; gap: 8px; flex-wrap: wrap; align-items: center; }
    .price-badge { display: inline-block; background: #e7f2ff; color: #0f6abf; padding: 8px 12px; border-radius: 12px; font-weight: 800; }
    .qty-chip { background: #eef6ff; color: #217dbb; padding: 6px 10px; border-radius: 10px; font-weight: 700; }
    .subtotal { font-weight: 800; color: #0f6abf; text-align: right; }
    .total-row {
        display: flex;
        justify-content: space-between;
        align-items: center;
        padding: 12px 14px;
        border-radius: 14px;
        background: #eaf4ff;
        font-weight: 800;
        color: #0f6abf;
        margin-bottom: 14px;
        border: 1px solid #d9e8fb;
    }
    form label { font-weight: 700; color: #1f2d3d; margin-bottom: 6px; display: block; }
    form input[type="text"], form textarea, form select {
        width: 100%;
        padding: 12px 12px;
        margin: 6px 0 16px;
        border-radius: 12px;
        border: 1px solid #d6e5f5;
        background: #f7fbff;
        font-size: 1rem;
        transition: border 0.2s, box-shadow 0.2s;
    }
    form input[type="text"]:focus, form textarea:focus, form select:focus {
        border: 1.5px solid #64b5f6;
        box-shadow: 0 0 0 3px rgba(100,181,246,0.18);
        outline: none;
    }
    textarea { min-height: 110px; resize: vertical; }
    .btn-primary {
        background: linear-gradient(135deg, #64b5f6, #3d8bdf);
        color: #0b1a2e;
        padding: 12px 18px;
        border-radius: 14px;
        border: none;
        font-weight: 800;
        width: 100%;
        font-size: 1.06rem;
        cursor: pointer;
        box-shadow: 0 10px 26px rgba(61,139,223,0.24);
        transition: transform 0.15s, box-shadow 0.2s;
    }
    .btn-primary:hover { transform: translateY(-1px); box-shadow: 0 14px 30px rgba(61,139,223,0.28); }
    .subtle-link { color: #0f6abf; text-decoration: underline; font-weight: 600; }
    .error-msg {
        color: #e74c3c;
        margin-bottom: 10px;
        font-size: 1rem;
        text-align: center;
        background: #fff3f3;
        border: 1px solid #ffc5c5;
        border-radius: 10px;
        padding: 10px 12px;
    }
    .payment-grid {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(210px, 1fr));
        gap: 12px;
    }
    .method-card {
        position: relative;
        border: 1px solid #dbe9fa;
        border-radius: 14px;
        padding: 12px 12px 12px 44px;
        background: #f9fbff;
        cursor: pointer;
        transition: border 0.2s, box-shadow 0.2s, background 0.2s;
    }
    .method-card input[type="radio"] {
        position: absolute;
        left: 14px;
        top: 14px;
        transform: scale(1.2);
    }
    .method-card strong { color: #15314b; display: block; margin-bottom: 4px; }
    .method-card span { color: #516070; font-size: 0.95rem; }
    .method-card.selected { border-color: #64b5f6; box-shadow: 0 10px 22px rgba(100,181,246,0.18); background: #eaf4ff; }
    .sub-select {
        margin-top: 8px;
        padding: 10px 12px;
        border: 1px dashed #c5d7ea;
        border-radius: 12px;
        background: #f8fbff;
        display: none;
    }
    .sub-select.show { display: block; }
    @media (max-width: 900px) {
        .item-row { grid-template-columns: 60px 1fr 90px 90px; }
    }
    footer.footer {
        text-align: center;
        padding: 18px 0;
        background: #eef4fb;
        color: #4a5560;
        font-size: 15px;
        position: relative;
        left: 0;
        right: 0;
        bottom: 0;
        margin-top: 32px;
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
<div class="checkout-wrapper">
    <div class="checkout-header">
        <h2>Checkout Produk</h2>
        <p>Lengkapi data penerima dan pilih metode pembayaran favoritmu.</p>
    </div>
    <div class="content-shell">
        <div class="checkout-layout">
            <div class="card-box">
                <div class="card-content">
                    <div class="item-list">
                        <?php foreach ($items as $it): ?>
                            <div class="item-row">
                                <?php if(!empty($it['gambar'])): ?>
                                    <img src="../assets/img/produk/<?= htmlspecialchars($it['gambar']) ?>" alt="<?= htmlspecialchars($it['nama_produk']) ?>">
                                <?php else: ?>
                                    <img src="../assets/img/no-image.png" alt="No Image">
                                <?php endif; ?>
                                <div>
                                    <div class="item-name"><?= htmlspecialchars($it['nama_produk']) ?></div>
                                    <div class="item-meta">Kategori: <?= htmlspecialchars($it['nama_kategori']) ?></div>
                                    <div class="badge-row">
                                        <div class="price-badge">Rp <?= number_format($it['harga']) ?></div>
                                        <div class="qty-chip">Qty: <?= $it['qty'] ?></div>
                                    </div>
                                </div>
                                <div class="subtotal">Rp <?= number_format($it['harga'] * $it['qty']) ?></div>
                                <div class="stock-chip">Stok: <?= $it['stok'] ?></div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                    <div class="total-row">
                        <span>Total Pembayaran</span>
                        <span>Rp <?= number_format($total_harga) ?></span>
                    </div>

                    <?php if (!empty($error)): ?>
                        <div class="error-msg"><?= htmlspecialchars($error) ?></div>
                    <?php endif; ?>
                    <form method="post">
                        <label>Nama Penerima</label>
                        <input type="text" name="nama" required>
                        <label>Alamat Pengiriman</label>
                        <textarea name="alamat" required placeholder="Tulis alamat lengkap beserta kecamatan/kota"></textarea>
                        <label>No. Telepon</label>
                        <input type="text" name="telepon" required placeholder="08xxxxxxxxxx">

                        <label>Metode Pembayaran</label>
                        <div class="payment-grid" id="paymentGrid">
                            <label class="method-card">
                                <input type="radio" name="metode" value="transfer" required>
                                <strong>Transfer Bank</strong>
                                <span>BNI, Mandiri, BRI, BCA</span>
                            </label>
                            <label class="method-card">
                                <input type="radio" name="metode" value="ewallet" required>
                                <strong>E-Wallet</strong>
                                <span>DANA, OVO, GoPay, ShopeePay</span>
                            </label>
                            <label class="method-card">
                                <input type="radio" name="metode" value="cod" required>
                                <strong>COD (Bayar di Tempat)</strong>
                                <span>Bayar langsung saat barang diterima</span>
                            </label>
                        </div>
                        <div class="sub-select" id="bankOptions">
                            <label>Pilih Bank</label>
                            <select name="bank">
                                <option value="">-- Pilih Bank --</option>
                                <option value="BNI">BNI</option>
                                <option value="Mandiri">Mandiri</option>
                                <option value="BRI">BRI</option>
                                <option value="BCA">BCA</option>
                            </select>
                        </div>
                        <div class="sub-select" id="ewalletOptions">
                            <label>Pilih E-Wallet</label>
                            <select name="ewallet">
                                <option value="">-- Pilih E-Wallet --</option>
                                <option value="DANA">DANA</option>
                                <option value="OVO">OVO</option>
                                <option value="GoPay">GoPay</option>
                                <option value="ShopeePay">ShopeePay</option>
                            </select>
                        </div>

                        <button type="submit" class="btn-primary">Konfirmasi & Bayar</button>
                    </form>                    
                </div>
            </div>
        </div>
    </div>
</div>

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

    var paymentGrid = document.getElementById('paymentGrid');
    var bankOptions = document.getElementById('bankOptions');
    var ewalletOptions = document.getElementById('ewalletOptions');
    if (paymentGrid) {
        paymentGrid.querySelectorAll('input[type="radio"]').forEach(function(radio) {
            radio.addEventListener('change', function() {
                paymentGrid.querySelectorAll('.method-card').forEach(function(card) { card.classList.remove('selected'); });
                radio.closest('.method-card').classList.add('selected');
                if (radio.value === 'transfer') {
                    bankOptions.classList.add('show');
                    ewalletOptions.classList.remove('show');
                } else if (radio.value === 'ewallet') {
                    ewalletOptions.classList.add('show');
                    bankOptions.classList.remove('show');
                } else {
                    bankOptions.classList.remove('show');
                    ewalletOptions.classList.remove('show');
                }
            });
        });
    }
});
</script>

<footer class="footer">
    @Copyright by 23552011029_Fauzan Rizkika Kurnia_TIF RP 23 CNS B_UASWEB1
</footer>
</body>
</html>
