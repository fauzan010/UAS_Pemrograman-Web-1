<?php
if (!isset($_COOKIE['user_id']) || $_COOKIE['role'] != 'admin') {
    header("Location: ../auth/login.php");
    exit;
}
include "../config/database.php";

$users = mysqli_query($conn, "SELECT * FROM users ORDER BY id ASC");
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Data User - WorldBike Admin</title>
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link rel="stylesheet" href="../assets/css/style.css">
    <style>
    body {
        background: linear-gradient(120deg, #e0eafc 0%, #cfdef3 100%);
        min-height: 100vh;
        margin: 0;
    }
    .navbar {
        width: 100%;
        display: flex;
        justify-content: space-between;
        align-items: center;
        padding: 18px 32px;
        background: #fff;
        box-shadow: 0 2px 18px rgba(44,62,80,0.07);
        position: sticky;
        top: 0;
        left: 0;
        right: 0;
        z-index: 100;
    }
    .navbar .logo {
        font-size: 1.7rem;
        font-weight: bold;
        color: #3498db;
        letter-spacing: 2px;
    }
    .btn-logout {
        background: #e74c3c;
        color: #fff;
        padding: 7px 18px;
        border-radius: 6px;
        text-decoration: none;
        font-size: 1rem;
        margin-left: 8px;
        transition: background 0.2s;
        border: none;
        cursor: pointer;
    }
    .btn-logout:hover {
        background: #c0392b;
    }
    .users-container {
        max-width: 900px;
        width: 100%;
        margin: 48px auto 90px auto;
        padding: 0 16px;
        box-sizing: border-box;
        background: #fff;
        border-radius: 18px;
        box-shadow: 0 8px 32px rgba(44,62,80,0.06);
    }
    h1 {
        color: #3498db;
        font-size: 1.5rem;
        margin-bottom: 24px;
        text-align: center;
    }
    .users-table {
        width: 100%;
        border-collapse: collapse;
        margin-bottom: 10px;
    }
    .users-table th, .users-table td {
        padding: 10px 8px;
        border-bottom: 1px solid #eee;
        font-size: 1rem;
        text-align: left;
    }
    .users-table th {
        background: #f8fafc;
        color: #217dbb;
        font-weight: 600;
    }
    .users-table td.role-admin { color: #e67e22; font-weight: 600; }
    .users-table td.role-user { color: #3498db; font-weight: 600; }
    @media (max-width: 800px) {
        .users-container { padding: 10px 2vw; }
        .users-table th, .users-table td { font-size: 0.97rem; padding: 7px 4px; }
    }
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
        <li><a href="../auth/logout.php" class="btn-logout">Logout</a></li>
    </ul>
</nav>
<div class="users-container">
    <h1 style="color:#3498db; font-size:1.5rem; font-weight:700; text-align:center; margin-bottom:24px;">Data User & Admin</h1>
    <div style="display:flex;justify-content:flex-end;align-items:center;margin-bottom:18px;">
        <a href="dashboard.php" class="btn" style="background:#217dbb;">Kembali ke Dashboard</a>
    </div>
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
            <?php if (mysqli_num_rows($users) > 0): ?>
                <?php while($u = mysqli_fetch_assoc($users)): ?>
                <tr>
                    <td><?= $u['id'] ?></td>
                    <td><?= htmlspecialchars($u['nama']) ?></td>
                    <td><?= htmlspecialchars($u['email']) ?></td>
                    <td class="role-<?= htmlspecialchars($u['role']) ?>"><?= htmlspecialchars(ucfirst($u['role'])) ?></td>
                </tr>
                <?php endwhile; ?>
            <?php else: ?>
                <tr><td colspan="4" style="text-align:center;color:#888;">Belum ada user.</td></tr>
            <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>
<!-- Footer -->
<footer class="footer">
    @Copyright by 23552011029_Fauzan Rizkika Kurnia_TIF RP 23 CNS B_UASWEB1
</footer>
</body>
</html>
