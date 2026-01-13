<?php
include "../config/database.php";

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

// Proses checkout
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nama = trim($_POST['nama']);
    $alamat = trim($_POST['alamat']);
    $telepon = trim($_POST['telepon']);
    $metode = trim($_POST['metode']);

    // Validasi sederhana
    if ($product['stok'] <= 0) {
        echo "<script>alert('Stok habis, tidak bisa membeli produk ini.');window.location='detail.php?id=$id';</script>";
        exit;
    }
    if ($nama && $alamat && $telepon && $metode) {
        // Kurangi stok
        mysqli_query($conn, "UPDATE products SET stok = stok - 1 WHERE id = $id");

        // Simpan data pesanan ke tabel orders
        session_start();
        $user_id = $_SESSION['user_id'];
        $qty = 1;
        $harga = $product['harga'];
        $tanggal = date('Y-m-d H:i:s');
        $stmt = mysqli_prepare($conn, "INSERT INTO orders (user_id, product_id, qty, harga, metode_bayar, alamat, nama_penerima, telepon, tanggal) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)");
        mysqli_stmt_bind_param($stmt, "iiissssss", $user_id, $id, $qty, $harga, $metode, $alamat, $nama, $telepon, $tanggal);
        mysqli_stmt_execute($stmt);

        echo "<script>alert('Pesanan berhasil! Silakan lakukan pembayaran sesuai instruksi.');window.location='index.php';</script>";
        exit;
    } else {
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
    <link rel="stylesheet" href="../assets/css/style.css">
    <style>
    body {
        background: linear-gradient(120deg, #e0eafc 0%, #cfdef3 100%);
        min-height: 100vh;
        padding-top: 80px;
    }
    .checkout-container {
        max-width: 420px;
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
    .produk-info {
        background: #f8fafc;
        border-radius: 10px;
        padding: 14px 18px;
        margin-bottom: 18px;
        font-size: 1rem;
    }
    label {
        font-weight: 500;
        color: #217dbb;
        margin-bottom: 6px;
        display: block;
    }
    input[type="text"], textarea, select {
        width: 100%;
        padding: 10px;
        margin: 8px 0 15px;
        border-radius: 6px;
        border: 1px solid #ddd;
    }
    .btn {
        background: #3498db;
        color: #fff;
        padding: 10px 18px;
        border-radius: 8px;
        text-decoration: none;
        font-weight: 500;
        border: none;
        margin-right: 8px;
        transition: background 0.2s;
        cursor: pointer;
        width: 100%;
        font-size: 1.08rem;
    }
    .btn:hover {
        background: #217dbb;
    }
    .error-msg {
        color: #e74c3c;
        margin-bottom: 10px;
        font-size: 1rem;
        text-align: center;
    }
    </style>
</head>
<body>
<header>
    <nav class="navbar" style="position:fixed;top:0;left:0;right:0;z-index:1000;display:flex;align-items:center;justify-content:space-between;background:#fff;box-shadow:0 2px 8px rgba(44,62,80,0.06);padding:0 32px;height:64px;">
        <div class="logo" style="font-size:1.4rem;font-weight:700;color:#3498db;letter-spacing:1px;">WorldBike</div>
        <ul style="list-style:none;display:flex;gap:28px;margin:0;padding:0;">
            <li><a href="../index.php" style="color:#217dbb;text-decoration:none;font-weight:500;padding:8px 0;transition:color 0.2s;">Home</a></li>
            <li><a href="index.php" style="color:#217dbb;text-decoration:none;font-weight:500;padding:8px 0;transition:color 0.2s;">Marketplace</a></li>
        </ul>
    </nav>
</header>
<div class="checkout-container">
    <h2>Checkout Produk</h2>
    <div class="produk-info">
        <b><?= htmlspecialchars($product['nama_produk']) ?></b><br>
        Kategori: <?= htmlspecialchars($product['nama_kategori']) ?><br>
        Harga: <b>Rp <?= number_format($product['harga']) ?></b><br>
        Stok tersedia: <?= $product['stok'] ?>
    </div>
    <?php if (!empty($error)): ?>
        <div class="error-msg"><?= htmlspecialchars($error) ?></div>
    <?php endif; ?>
    <form method="post">
        <label>Nama Penerima</label>
        <input type="text" name="nama" required>
        <label>Alamat Pengiriman</label>
        <textarea name="alamat" required></textarea>
        <label>No. Telepon</label>
        <input type="text" name="telepon" required>
        <label>Metode Pembayaran</label>
        <select name="metode" required>
            <option value="">-- Pilih --</option>
            <option value="transfer">Transfer Bank</option>
            <option value="cod">COD (Bayar di Tempat)</option>
            <option value="ewallet">E-Wallet</option>
        </select>
        <button type="submit" class="btn">Konfirmasi & Bayar</button>
    </form>
    <div style="margin-top:18px;text-align:center;">
        <a href="detail.php?id=<?= $product['id'] ?>" style="color:#3498db;text-decoration:underline;">← Kembali ke Detail Produk</a>
    </div>
</div>
<footer class="footer" style="text-align:center; padding:18px 0; background:#f4f6f8; color:#030000; font-size:15px; position:fixed; left:0; right:0; bottom:0;">
    @Copyright by 23552011029_Fauzan Rizkika Kurnia_TIF RP 23 CNS B_UASWEB1
</footer>
</body>
</html>
