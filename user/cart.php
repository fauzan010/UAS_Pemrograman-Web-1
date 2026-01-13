<?php
session_start();
include "../config/database.php";

// Hapus produk dari keranjang
if (isset($_GET['remove'])) {
    $rid = intval($_GET['remove']);
    unset($_SESSION['cart'][$rid]);
    header("Location: cart.php");
    exit;
}

// Checkout semua produk (redirect ke checkout dengan id produk pertama, atau bisa dikembangkan)
if (isset($_POST['checkout_all']) && !empty($_SESSION['cart'])) {
    // Simulasi: redirect ke checkout.php dengan id produk pertama
    $first_id = reset($_SESSION['cart'])['id'];
    header("Location: checkout.php?id=$first_id");
    exit;
}

$cart = isset($_SESSION['cart']) ? $_SESSION['cart'] : [];
$total = 0;
foreach ($cart as $item) {
    $total += $item['harga'] * $item['qty'];
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Keranjang | WorldBike</title>
    <meta name="viewport" content="width=device-width, initial-scale=1">
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
    .cart-container {
        max-width: 800px;
        margin: 40px auto 90px auto;
        background: #fff;
        border-radius: 18px;
        box-shadow: 0 8px 32px rgba(44,62,80,0.08);
        padding: 32px 24px 24px 24px;
    }
    h2 {
        color: #3498db;
        font-size: 1.3rem;
        margin-bottom: 18px;
        font-weight: 700;
        text-align: center;
    }
    table {
        width: 100%;
        border-collapse: collapse;
        margin-bottom: 18px;
    }
    th, td {
        padding: 12px 8px;
        text-align: left;
        border-bottom: 1px solid #eee;
    }
    th {
        background: #f8fafc;
        color: #217dbb;
        font-weight: 600;
    }
    td img {
        width: 60px;
        border-radius: 8px;
        box-shadow: 0 2px 8px rgba(52,152,219,0.13);
        background: #eaf6fb;
    }
    .cart-actions {
        display: flex;
        justify-content: flex-end;
        gap: 12px;
        margin-top: 18px;
    }
    .btn {
        background: #3498db;
        color: #fff;
        padding: 10px 18px;
        border-radius: 8px;
        text-decoration: none;
        font-weight: 500;
        border: none;
        transition: background 0.2s;
        cursor: pointer;
        font-size: 1.08rem;
    }
    .btn-danger {
        background: #e74c3c;
    }
    .btn-success {
        background: #2ecc71;
    }
    .btn:hover {
        background: #217dbb;
    }
    .btn-danger:hover {
        background: #c0392b;
    }
    .btn-success:hover {
        background: #27ae60;
    }
    .empty-cart {
        text-align: center;
        color: #888;
        font-size: 1.08rem;
        margin: 32px 0;
    }
    .cart-total {
        text-align: right;
        font-size: 1.15rem;
        font-weight: 600;
        color: #217dbb;
        margin-top: 10px;
    }
    @media (max-width: 600px) {
        .navbar { padding: 12px 3vw; }
        .navbar .logo { font-size: 1.4rem; }
        .cart-container { padding: 18px 6px; }
        th, td { font-size: 0.98rem; padding: 8px 4px; }
    }
    </style>
</head>
<body>
<nav class="navbar">
    <div class="logo">WorldBike</div>
    <ul>
        <li><a href="../index.php">Home</a></li>
        <li><a href="index.php">Marketplace</a></li>
        <li><a href="cart.php" class="active" style="color:#3498db;font-weight:600;">Keranjang
            <?php if (!empty($cart)): ?>
                <span style="background:#e74c3c;color:#fff;border-radius:50%;padding:2px 8px;font-size:0.95rem;margin-left:4px;">
                    <?= array_sum(array_column($cart, 'qty')) ?>
                </span>
            <?php endif; ?>
        </a></li>
    </ul>
</nav>
<div class="cart-container">
    <h2>Keranjang Belanja</h2>
    <?php if (empty($cart)): ?>
        <div class="empty-cart">Keranjang Anda kosong.<br><a href="index.php" style="color:#3498db;text-decoration:underline;">← Belanja Sekarang</a></div>
    <?php else: ?>
        <form method="post">
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
                    <td><img src="../assets/img/produk/<?= htmlspecialchars($item['gambar']) ?>" alt="<?= htmlspecialchars($item['nama_produk']) ?>"></td>
                    <td><?= htmlspecialchars($item['nama_produk']) ?></td>
                    <td>Rp <?= number_format($item['harga']) ?></td>
                    <td><?= $item['qty'] ?></td>
                    <td>Rp <?= number_format($item['harga'] * $item['qty']) ?></td>
                    <td>
                        <a href="cart.php?remove=<?= $item['id'] ?>" class="btn btn-danger" onclick="return confirm('Hapus produk dari keranjang?')">Hapus</a>
                    </td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
        <div class="cart-total">Total: <span>Rp <?= number_format($total) ?></span></div>
        <div class="cart-actions">
            <a href="index.php" class="btn">← Lanjut Belanja</a>
            <button type="submit" name="checkout_all" class="btn btn-success">Checkout Semua</button>
        </div>
        </form>
    <?php endif; ?>
</div>
<footer class="footer" style="text-align:center; padding:18px 0; background:#f4f6f8; color:#030000; font-size:15px; position:fixed; left:0; right:0; bottom:0;">
    @Copyright by 23552011029_Fauzan Rizkika Kurnia_TIF RP 23 CNS B_UASWEB1
</footer>
</body>
</html>
