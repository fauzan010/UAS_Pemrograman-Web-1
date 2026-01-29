<?php
if (!isset($_COOKIE['user_id']) || $_COOKIE['role'] != 'admin') {
    header("Location: ../auth/login.php");
    exit;
}
include "../config/database.php";

$products = mysqli_query($conn, "
    SELECT products.*, categories.nama_kategori 
    FROM products 
    LEFT JOIN categories ON products.category_id = categories.id
");
$productCount = mysqli_num_rows($products);

$alertMessage = '';
$alertType = '';
if (isset($_GET['error'])) {
    if ($_GET['error'] === 'has_orders') {
        $alertMessage = 'Produk tidak dapat dihapus karena masih memiliki pemesanan terkait.';
        $alertType = 'error';
    } elseif ($_GET['error'] === 'invalid') {
        $alertMessage = 'Permintaan tidak valid.';
        $alertType = 'error';
    }
} elseif (isset($_GET['success']) && $_GET['success'] === 'deleted') {
    $alertMessage = 'Produk berhasil dihapus.';
    $alertType = 'success';
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Kelola Produk - WorldBike</title>
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css"
          rel="stylesheet"
          integrity="sha384-QWTKZyjpPEjISv5WaRU9OFeRpok6YctnYmDr5pNlyT2bRjXh0JMhjY6hW+ALEwIH"
          crossorigin="anonymous">
    <link rel="stylesheet" href="../assets/css/style.css">
    <style>
    body {
        background: #eef5ff;
        min-height: 100vh;
        margin: 0;
        font-family: "Segoe UI", sans-serif;
        color: #1f2d3d;
    }
    .navbar {
        width: 100%;
        display: flex;
        justify-content: space-between;
        align-items: center;
        padding: 16px 28px;
        background: #fff;
        box-shadow: 0 2px 18px rgba(44,62,80,0.07);
        position: sticky;
        top: 0;
        left: 0;
        right: 0;
        z-index: 100;
    }
    .navbar .logo {
        font-size: 1.6rem;
        font-weight: 800;
        color: #3498db;
        letter-spacing: 1px;
    }
    .navbar ul {
        display: flex;
        gap: 12px;
        list-style: none;
        margin: 0;
        padding: 0;
        align-items: center;
    }
    .navbar ul li a {
        color: #2c3e50;
        text-decoration: none;
        font-size: 0.98rem;
        font-weight: 600;
        padding: 9px 16px;
        border-radius: 6px;
        transition: background 0.2s, color 0.2s;
        display: block;
    }
    .navbar ul li a.active, .navbar ul li a:hover {
        background: #3498db;
        color: #fff;
        box-shadow: none;
    }
    .navbar ul li.nav-right { margin-left: auto; }
    .btn-logout {
        background: #ef4444;
        color: #fff;
        padding: 9px 16px;
        border-radius: 12px;
        text-decoration: none;
        font-size: 0.98rem;
        font-weight: 600;
        margin-left: 6px;
        transition: background 0.2s, transform 0.2s;
        border: none;
        cursor: pointer;
    }
    .btn-logout:hover {
        background: #dc2626;
        transform: translateY(-1px);
    }
    .page-shell {
        max-width: 1100px;
        margin: 32px auto 96px auto;
        padding: 0 18px;
        box-sizing: border-box;
    }
    .panel {
        background: #fff;
        border-radius: 16px;
        box-shadow: 0 12px 32px rgba(44,62,80,0.10);
        border: 1px solid #e6eef7;
        padding: 20px 22px;
        margin-bottom: 18px;
    }
    .page-header {
        display: flex;
        flex-direction: column;
        gap: 4px;
        margin-bottom: 8px;
    }
    .eyebrow {
        font-size: 0.82rem;
        text-transform: uppercase;
        letter-spacing: 0.08em;
        color: #607286;
        font-weight: 800;
    }
    .title-row {
        display: flex;
        justify-content: space-between;
        align-items: flex-end;
        flex-wrap: wrap;
        gap: 12px;
    }
    .title-row h1 {
        font-size: 1.8rem;
        color: #1b3a57;
        margin: 0;
        font-weight: 800;
        letter-spacing: -0.02em;
    }
    .title-row p {
        margin: 0;
        color: #607286;
    }
    .actions {
        display: flex;
        gap: 10px;
        align-items: center;
        flex-wrap: wrap;
    }
    .pill-btn {
        display: inline-flex;
        align-items: center;
        gap: 8px;
        background: linear-gradient(135deg, #3498db, #217dbb);
        color: #fff;
        padding: 10px 14px;
        border-radius: 12px;
        text-decoration: none;
        font-weight: 700;
        box-shadow: 0 10px 24px rgba(52,152,219,0.25);
        transition: opacity 0.2s, transform 0.2s;
    }
    .pill-btn:hover { opacity: 0.93; transform: translateY(-1px); }
    .badge-soft {
        background: #eaf6fb;
        color: #217dbb;
        padding: 8px 12px;
        border-radius: 999px;
        font-weight: 700;
        font-size: 0.95rem;
        border: 1px solid #d5e9f6;
    }
    .alert-panel {
        display: flex;
        align-items: center;
        gap: 10px;
        padding: 12px 14px;
        border-radius: 12px;
        margin-bottom: 12px;
        font-weight: 700;
        border: 1px solid transparent;
    }
    .alert-error { background: #fff1f2; color: #b91c1c; border-color: #fecdd3; }
    .alert-success { background: #ecfdf3; color: #15803d; border-color: #bbf7d0; }
    .table-wrapper { overflow-x: auto; }
    .table {
        width: 100%;
        min-width: 720px;
        border-collapse: separate;
        border-spacing: 0;
    }
    .table thead th {
        background: #f1f6ff;
        color: #1f3b57;
        font-weight: 800;
        font-size: 0.96rem;
        border-bottom: 1px solid #dde7f5;
        padding: 14px 12px;
        text-transform: uppercase;
        letter-spacing: 0.06em;
    }
    .table tbody td {
        padding: 14px 12px;
        border-bottom: 1px solid #eef2f7;
        vertical-align: middle;
        font-weight: 600;
        color: #1f2d3d;
        background: #fff;
    }
    .table tbody tr:hover td { background: #f6faff; }
    .name-col { font-weight: 800; color: #1b3a57; }
    .category-chip {
        display: inline-flex;
        align-items: center;
        gap: 6px;
        padding: 7px 12px;
        background: #eaf6fb;
        color: #217dbb;
        border-radius: 10px;
        font-weight: 700;
        border: 1px solid #d5e9f6;
    }
    .price {
        font-variant-numeric: tabular-nums;
        color: #1b3a57;
        font-weight: 800;
    }
    .stock {
        display: inline-flex;
        align-items: center;
        gap: 6px;
        padding: 6px 10px;
        border-radius: 10px;
        background: #eaf6fb;
        color: #1f3b57;
        font-weight: 700;
        border: 1px solid #d5e9f6;
    }
    .actions-col {
        display: flex;
        gap: 8px;
        flex-wrap: wrap;
    }
    .btn-ghost {
        padding: 7px 14px;
        border-radius: 8px;
        border: none;
        text-decoration: none;
        font-weight: 600;
        color: #fff;
        background: #3498db;
        transition: transform 0.15s, background 0.2s;
        display: inline-block;
    }
    .btn-ghost:hover { background: #217dbb; transform: translateY(-1px); }
    .btn-ghost.danger { background: #e74c3c; }
    .btn-ghost.danger:hover { background: #c0392b; }
    .footer {
        background: #fff;
        color: #030000;
        text-align: center;
        padding: 18px 0;
        font-size: 15px;
        position: fixed;
        left: 0;
        right: 0;
        bottom: 0;
        z-index: 99;
        border-top: 1px solid #eaeaea;
    }
    </style>
</head>
<body>
<nav class="navbar">
    <div class="logo">WorldBike Admin</div>
    <ul>
        <li><a href="dashboard.php">Dashboard</a></li>
        <li><a href="produk.php" class="active">Produk</a></li>
        <li><a href="kategori.php">Kategori</a></li>
        <li><a href="orders.php">Pemesanan</a></li>
        <li><a href="users.php">Pengguna</a></li>
        <li class="nav-right"><a href="../auth/logout.php" class="btn-logout">Logout</a></li>
    </ul>
</nav>
<div class="page-shell">
    <div class="panel">
        <div class="page-header">
            <span class="eyebrow">Produk</span>
            <div class="title-row">
                <div>
                    <h1>Kelola Katalog Produk</h1>
                    <p>Perbarui foto, harga, stok, dan kategori dalam satu tempat.</p>
                </div>
                <div class="actions">
                    <span class="badge-soft">Total produk: <?= $productCount ?></span>
                    <a href="tambah_produk.php" class="pill-btn">+ Tambah Produk</a>
                </div>
            </div>
        </div>
        <?php if ($alertMessage): ?>
            <div class="alert-panel <?= $alertType === 'error' ? 'alert-error' : 'alert-success' ?>">
                <?= htmlspecialchars($alertMessage) ?>
            </div>
        <?php endif; ?>
        <div class="table-wrapper">
            <table class="table">
                <thead>
                    <tr>
                        <th>Gambar</th>
                        <th>Nama</th>
                        <th>Kategori</th>
                        <th>Harga</th>
                        <th>Stok</th>
                        <th>Aksi</th>
                    </tr>
                </thead>
                <tbody>
                <?php while($p = mysqli_fetch_assoc($products)): ?>
                    <tr>
                        <td><img src="../assets/img/produk/<?= $p['gambar'] ?>" width="70" height="70" style="object-fit:cover; border-radius:12px; border:1px solid #e5e7eb;"></td>
                        <td class="name-col"><?= htmlspecialchars($p['nama_produk']) ?></td>
                        <td><span class="category-chip"><?= htmlspecialchars($p['nama_kategori'] ?? 'Tanpa kategori') ?></span></td>
                        <td class="price">Rp <?= number_format($p['harga']) ?></td>
                        <td><span class="stock"><?= $p['stok'] ?> pcs</span></td>
                        <td>
                            <div class="actions-col">
                                <a href="edit_produk.php?id=<?= $p['id'] ?>" class="btn-ghost">Edit</a>
                                <a href="hapus_produk.php?id=<?= $p['id'] ?>" class="btn-ghost danger" onclick="return confirm('Hapus produk ini?')">Hapus</a>
                            </div>
                        </td>
                    </tr>
                <?php endwhile; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>
<!-- Bootstrap JS -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"
        integrity="sha384-YvpcrYf0tY3lHB60NNkmXc5s9fDVZLESaAA55NDzOxhy9GkcIdslK1eN7N6jIeHz"
        crossorigin="anonymous"></script>
<!-- Footer -->
<footer class="footer">
    @Copyright by 23552011029_Fauzan Rizkika Kurnia_TIF RP 23 CNS B_UASWEB1
</footer>
</body>
</html>
