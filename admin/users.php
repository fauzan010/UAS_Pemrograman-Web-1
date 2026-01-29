<?php
if (!isset($_COOKIE['user_id']) || $_COOKIE['role'] != 'admin') {
    header("Location: ../auth/login.php");
    exit;
}
include "../config/database.php";

$users = mysqli_query($conn, "SELECT * FROM users ORDER BY id ASC");
$userCount = mysqli_num_rows($users);
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Data User - WorldBike Admin</title>
    <meta name="viewport" content="width=device-width, initial-scale=1">
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
    .navbar ul { display: flex; gap: 18px; list-style: none; margin: 0; padding: 0; align-items: center; }
    .navbar ul li a { color: #2c3e50; text-decoration: none; font-size: 0.98rem; font-weight: 600; padding: 9px 16px; border-radius: 6px; transition: background 0.2s, color 0.2s; display: block; }
    .navbar ul li a.active, .navbar ul li a:hover { background: #3498db; color: #fff; box-shadow: none; }
    .navbar ul li.nav-right { margin-left: auto; }
    .btn-logout { background: #e74c3c; color: #fff; padding: 9px 16px; border-radius: 12px; text-decoration: none; font-size: 0.98rem; font-weight: 600; margin-left: 6px; transition: background 0.2s, transform 0.2s; border: none; cursor: pointer; }
    .btn-logout:hover { background: #c0392b; transform: translateY(-1px); }
    .page-shell { max-width: 1100px; margin: 32px auto 96px auto; padding: 0 18px; box-sizing: border-box; }
    .panel { background: #fff; border-radius: 16px; box-shadow: 0 12px 32px rgba(44,62,80,0.10); border: 1px solid #e6eef7; padding: 20px 22px; margin-bottom: 18px; }
    .page-header { display: flex; flex-direction: column; gap: 4px; margin-bottom: 8px; }
    .eyebrow { font-size: 0.82rem; text-transform: uppercase; letter-spacing: 0.08em; color: #607286; font-weight: 800; }
    .title-row { display: flex; justify-content: space-between; align-items: flex-end; flex-wrap: wrap; gap: 12px; }
    .title-row h1 { font-size: 1.8rem; color: #1b3a57; margin: 0; font-weight: 800; letter-spacing: -0.02em; }
    .title-row p { margin: 0; color: #607286; }
    .actions { display: flex; gap: 10px; align-items: center; flex-wrap: wrap; }
    .badge-soft { background: #eaf6fb; color: #217dbb; padding: 8px 12px; border-radius: 999px; font-weight: 700; font-size: 0.95rem; border: 1px solid #d5e9f6; }
    .table-wrapper { overflow-x: auto; }
    .users-table { width: 100%; min-width: 640px; border-collapse: separate; border-spacing: 0; }
    .users-table thead th { background: #f1f6ff; color: #1f3b57; font-weight: 800; font-size: 0.96rem; border-bottom: 1px solid #dde7f5; padding: 12px 12px; text-transform: uppercase; letter-spacing: 0.06em; }
    .users-table tbody td { padding: 12px 12px; border-bottom: 1px solid #eef2f7; vertical-align: middle; font-weight: 600; color: #1f2d3d; background: #fff; }
    .users-table tbody tr:hover td { background: #f6faff; }
    .role-chip { display: inline-flex; align-items: center; gap: 6px; padding: 7px 12px; border-radius: 10px; font-weight: 700; border: 1px solid #d5e9f6; background: #eaf6fb; color: #217dbb; }
    .role-admin { background: #fff7ed; color: #c2410c; border-color: #fed7aa; }
    .role-user { background: #eaf6fb; color: #217dbb; border-color: #d5e9f6; }
    .footer { background: #fff; color: #030000; text-align: center; padding: 18px 0; font-size: 15px; position: fixed; left: 0; right: 0; bottom: 0; z-index: 99; border-top: 1px solid #eaeaea; }
    </style>
</head>
<body>
<nav class="navbar">
    <div class="logo">WorldBike Admin</div>
    <ul>
        <li><a href="dashboard.php">Dashboard</a></li>
        <li><a href="produk.php">Produk</a></li>
        <li><a href="kategori.php">Kategori</a></li>
        <li><a href="orders.php">Pemesanan</a></li>
        <li><a href="users.php" class="active">Pengguna</a></li>
        <li class="nav-right"><a href="../auth/logout.php" class="btn-logout">Logout</a></li>
    </ul>
</nav>
<div class="page-shell">
    <div class="panel">
        <div class="page-header">
            <span class="eyebrow">Data</span>
            <div class="title-row">
                <div>
                    <h1>Kelola Pengguna</h1>
                    <p>Lihat daftar user dan admin yang terdaftar.</p>
                </div>
                <div class="actions">
                    <span class="badge-soft">Total: <?= $userCount ?></span>
                </div>
            </div>
        </div>
    </div>

    <div class="panel">
        <div class="table-wrapper">
            <table class="users-table">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Nama</th>
                        <th>Email</th>
                        <th>Role</th>
                    </tr>
                </thead>
                <tbody>
                <?php if ($userCount > 0): ?>
                    <?php while($u = mysqli_fetch_assoc($users)): ?>
                    <tr>
                        <td><?= $u['id'] ?></td>
                        <td><?= htmlspecialchars($u['nama']) ?></td>
                        <td><?= htmlspecialchars($u['email']) ?></td>
                        <td>
                            <?php $roleClass = strtolower($u['role']) === 'admin' ? 'role-admin' : 'role-user'; ?>
                            <span class="role-chip <?= $roleClass ?>"><?= htmlspecialchars(ucfirst($u['role'])) ?></span>
                        </td>
                    </tr>
                    <?php endwhile; ?>
                <?php else: ?>
                    <tr><td colspan="4" style="text-align:center;color:#888;">Belum ada pengguna.</td></tr>
                <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>
<!-- Footer -->
<footer class="footer">
    @Copyright by 23552011029_Fauzan Rizkika Kurnia_TIF RP 23 CNS B_UASWEB1
</footer>

<!-- Bootstrap JS -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"
        integrity="sha384-YvpcrYf0tY3lHB60NNkmXc5s9fDVZLESaAA55NDzOxhy9GkcIdslK1eN7N6jIeHz"
        crossorigin="anonymous"></script>
</body>
</html>
