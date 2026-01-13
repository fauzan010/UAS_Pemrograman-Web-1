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
        if ($product['stok'] > 0) {
            if (!isset($_SESSION['cart'])) $_SESSION['cart'] = [];
            if (isset($_SESSION['cart'][$id])) {
                $_SESSION['cart'][$id]['qty'] += 1;
            } else {
                $_SESSION['cart'][$id] = [
                    'id' => $product['id'],
                    'nama_produk' => $product['nama_produk'],
                    'harga' => $product['harga'],
                    'gambar' => $product['gambar'],
                    'qty' => 1
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
    <link rel="stylesheet" href="../assets/css/style.css">
    <style>
    body {
        background: linear-gradient(120deg, #e0eafc 0%, #cfdef3 100%);
        min-height: 100vh;
    }
    .navbar {
        display: flex;
        justify-content: space-between;
        align-items: center;
        padding: 22px 7vw;
        background: #fff;
        box-shadow: 0 2px 18px rgba(44,62,80,0.07);
        position: relative;
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
    .detail-container {
        max-width: 950px;
        margin: 40px auto;
        background: #fff;
        border-radius: 18px;
        box-shadow: 0 8px 32px rgba(44,62,80,0.08);
        display: flex;
        flex-wrap: wrap;
        gap: 40px;
        padding: 40px 30px;
        align-items: flex-start;
    }
    .detail-img {
        flex: 1 1 320px;
        min-width: 260px;
        text-align: center;
    }
    .detail-img img {
        width: 100%;
        max-width: 340px;
        border-radius: 16px;
        box-shadow: 0 8px 32px rgba(52,152,219,0.13);
        background: #eaf6fb;
    }
    .detail-info {
        flex: 2 1 340px;
        min-width: 260px;
        display: flex;
        flex-direction: column;
        gap: 18px;
    }
    .detail-title {
        font-size: 2rem;
        font-weight: 700;
        color: #3498db;
        margin-bottom: 6px;
    }
    .detail-kategori {
        color: #217dbb;
        font-size: 1.05rem;
        margin-bottom: 8px;
    }
    .detail-harga {
        font-size: 1.5rem;
        color: #222;
        font-weight: bold;
        margin-bottom: 8px;
    }
    .detail-stok {
        font-size: 1.05rem;
        color: #888;
        margin-bottom: 12px;
    }
    .detail-desc {
        font-size: 1.08rem;
        color: #444;
        line-height: 1.7;
        margin-bottom: 18px;
    }
    .btn-beli {
        background: #3498db;
        color: #fff;
        padding: 14px 32px;
        border-radius: 8px;
        font-size: 1.1rem;
        font-weight: 600;
        border: none;
        cursor: pointer;
        margin-top: 10px;
        transition: background 0.2s;
        box-shadow: 0 4px 18px rgba(52,152,219,0.13);
        width: 100%;
        max-width: 260px;
    }
    .btn-beli:hover {
        background: #217dbb;
    }
    .back-link {
        display: inline-block;
        margin-bottom: 18px;
        color: #3498db;
        text-decoration: none;
        font-weight: 500;
        font-size: 1rem;
        transition: color 0.2s;
    }
    .back-link:hover {
        color: #217dbb;
        text-decoration: underline;
    }
    .modal-login {
        display: none;
        position: fixed;
        z-index: 9999;
        left: 0; top: 0; width: 100vw; height: 100vh;
        background: rgba(44,62,80,0.18);
        align-items: center;
        justify-content: center;
    }
    .modal-login-content {
        background: #fff;
        padding: 32px 24px;
        border-radius: 12px;
        box-shadow: 0 8px 32px rgba(44,62,80,0.18);
        text-align: center;
        max-width: 340px;
    }
    .modal-login-content h3 {
        color: #3498db;
        margin-bottom: 14px;
        font-size: 1.18rem;
    }
    .modal-login-content p {
        color: #444;
        margin-bottom: 18px;
    }
    .modal-login-content .btn {
        background: #3498db;
        color: #fff;
        padding: 10px 18px;
        border-radius: 8px;
        font-weight: 500;
        border: none;
        cursor: pointer;
        font-size: 1.08rem;
        margin: 0 6px 8px 6px;
        display: inline-block;
    }
    .modal-login-content .btn-register {
        background: #2ecc71;
    }
    .modal-login-content .btn-register:hover {
        background: #27ae60;
    }
    .modal-login-content .btn-cancel {
        background: #888;
    }
    .modal-login-content .btn-cancel:hover {
        background: #666;
    }
    .modal-login-content .btn:hover {
        background: #217dbb;
    }
    @media (max-width: 900px) {
        .detail-container {
            flex-direction: column;
            padding: 30px 10px;
        }
        .detail-img, .detail-info {
            min-width: 0;
        }
    }
    @media (max-width: 600px) {
        html { font-size: 15px; }
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
        .detail-title { font-size: 1.2rem; }
        .detail-harga { font-size: 1.1rem; }
        .detail-desc { font-size: 0.98rem; }
        .btn-beli { font-size: 1rem; padding: 10px 0; }
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
        <li><a href="cart.php" style="color:#3498db;font-weight:600;">Keranjang
            <?php if (!empty($_SESSION['cart'])): ?>
                <span style="background:#e74c3c;color:#fff;border-radius:50%;padding:2px 8px;font-size:0.95rem;margin-left:4px;">
                    <?= array_sum(array_column($_SESSION['cart'], 'qty')) ?>
                </span>
            <?php endif; ?>
        </a></li>
        <?php if ($is_logged_in): ?>
            <li>
                <span class="user-profile" style="color:#3498db;background:#eaf6fb;font-weight:600;display:flex;align-items:center;gap:8px;padding:6px 14px;border-radius:6px;font-size:1.1rem;">
                    <span style="font-size:1.2em;">👤</span><?= htmlspecialchars($user_name) ?>
                </span>
            </li>
            <li>
                <a href="../auth/logout.php" style="color:#2c3e50;font-weight:500;padding:6px 14px;border-radius:6px;text-decoration:none;">Logout</a>
            </li>
        <?php else: ?>
            <li><a href="../auth/login.php" class="btn" style="margin-left:16px;">Login</a></li>
        <?php endif; ?>
    </ul>
</nav>
<div class="detail-container">
    <div class="detail-img">
        <img src="../assets/img/produk/<?= htmlspecialchars($product['gambar']) ?>" alt="<?= htmlspecialchars($product['nama_produk']) ?>">
    </div>
    <div class="detail-info">
        <a href="index.php" class="back-link">← Kembali ke Marketplace</a>
        <div class="detail-title"><?= htmlspecialchars($product['nama_produk']) ?></div>
        <div class="detail-kategori"><b>Kategori:</b> <?= htmlspecialchars($product['nama_kategori']) ?></div>
        <div class="detail-harga">Rp <?= number_format($product['harga']) ?></div>
        <div class="detail-stok"><b>Stok:</b> <?= $product['stok'] ?></div>
        <div class="detail-desc"><?= nl2br(htmlspecialchars($product['deskripsi'])) ?></div>
        <?php if (!empty($cart_msg)): ?>
            <div style="background:#eaf6fb;color:#217dbb;border-radius:8px;padding:10px 16px;margin-bottom:10px;font-weight:500;text-align:center;">
                <?= htmlspecialchars($cart_msg) ?>
            </div>
        <?php endif; ?>
        <form id="actionForm" method="post" style="display:flex;gap:12px;flex-wrap:wrap;">
            <?php if ($product['stok'] > 0): ?>
                <button type="submit" name="add_to_cart" class="btn-beli" style="background:#2ecc71;flex:1;">+ Keranjang</button>
                <button type="submit" name="beli_sekarang" class="btn-beli" style="text-align:center;text-decoration:none;flex:1;">Beli Sekarang</button>
            <?php else: ?>
                <button class="btn-beli" style="background:#ccc;cursor:not-allowed;flex:1;" disabled>Stok Habis</button>
            <?php endif; ?>
        </form>
        <a href="cart.php" style="display:inline-block;margin-top:14px;color:#3498db;text-decoration:underline;font-weight:500;">Lihat Keranjang &rarr;</a>
    </div>
</div>

<!-- Modal Login/Register -->
<div class="modal-login" id="modalLogin">
    <div class="modal-login-content">
        <h3>Anda harus login dulu</h3>
        <p>Belum punya akun? Silakan daftar terlebih dahulu.<br>Sudah punya akun? Silakan login untuk melanjutkan.</p>
        <button class="btn btn-register" onclick="window.location.href='/worldbike/auth/register.php?redirect=/worldbike/user/detail.php?id=<?= $product['id'] ?>'">Register</button>
        <button class="btn" onclick="window.location.href='/worldbike/auth/login.php?redirect=/worldbike/user/detail.php?id=<?= $product['id'] ?>'">Login</button>
        <button class="btn btn-cancel" onclick="closeModalLogin()">Batal</button>
    </div>
</div>

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
<!-- Footer -->
<footer class="footer" style="text-align:center; padding:18px 0; background:#f4f6f8; color:#030000; font-size:15px; position:fixed; left:0; right:0; bottom:0;">
    @Copyright by 23552011029_Fauzan Rizkika Kurnia_TIF RP 23 CNS B_UASWEB1
</footer>
</body>
</html>
