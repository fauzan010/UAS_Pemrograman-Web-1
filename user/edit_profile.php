<?php
session_start();
include "../config/database.php";

// Sinkronkan session dari cookie jika ada
if (!isset($_SESSION['user_id']) && isset($_COOKIE['user_id'])) {
    $_SESSION['user_id'] = $_COOKIE['user_id'];
    $_SESSION['role'] = $_COOKIE['role'] ?? 'user';
}

// Jika belum login, arahkan ke halaman login
if (!isset($_SESSION['user_id'])) {
    header("Location: ../auth/login.php?redirect=user/edit_profile.php");
    exit;
}

$uid = intval($_SESSION['user_id']);
$user_q = mysqli_query($conn, "SELECT id, nama, email, role, photo FROM users WHERE id=$uid");
$user = mysqli_fetch_assoc($user_q);
if (!$user) {
    header("Location: ../auth/logout.php");
    exit;
}

$current_photo = $user['photo'] ?? '';
$user_name = $user['nama'] ?? '';
$user_email = $user['email'] ?? '';
$errors = [];

// Siapkan direktori avatar
$avatarDir = realpath(__DIR__ . '/../assets/img/avatars');
if ($avatarDir === false) {
    $avatarDir = __DIR__ . '/../assets/img/avatars';
}
if (!is_dir($avatarDir)) {
    mkdir($avatarDir, 0777, true);
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nama = trim($_POST['nama'] ?? '');
    $remove_photo = isset($_POST['remove_photo']);
    $photoToSave = $current_photo;

    if ($nama === '') {
        $errors[] = "Nama tidak boleh kosong.";
    }

    $hasUpload = isset($_FILES['photo']) && $_FILES['photo']['error'] !== UPLOAD_ERR_NO_FILE && $_FILES['photo']['name'] !== '';

    if ($hasUpload) {
        $file = $_FILES['photo'];
        if ($file['error'] !== UPLOAD_ERR_OK) {
            $errors[] = "Gagal mengunggah file.";
        } else {
            $ext = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
            $allowed = ['jpg','jpeg','png','webp'];
            if (!in_array($ext, $allowed, true)) {
                $errors[] = "Format gambar harus jpg, jpeg, png, atau webp.";
            }
            if ($file['size'] > 2 * 1024 * 1024) {
                $errors[] = "Ukuran gambar maksimal 2MB.";
            }
            if (empty($errors)) {
                $newName = 'user_' . $uid . '_' . time() . '.' . $ext;
                $targetPath = $avatarDir . '/' . $newName;
                if (!move_uploaded_file($file['tmp_name'], $targetPath)) {
                    $errors[] = "Gagal menyimpan file upload.";
                } else {
                    $photoToSave = $newName;
                }
            }
        }
    }

    if ($remove_photo) {
        $photoToSave = '';
    }

    if (empty($errors)) {
        $stmt = mysqli_prepare($conn, "UPDATE users SET nama = ?, photo = ? WHERE id = ?");
        $photoParam = $photoToSave !== '' ? $photoToSave : NULL;
        mysqli_stmt_bind_param($stmt, "ssi", $nama, $photoParam, $uid);
        $ok = mysqli_stmt_execute($stmt);

        if ($ok) {
            if ($current_photo && ($remove_photo || ($hasUpload && $photoToSave !== $current_photo))) {
                $oldPath = $avatarDir . '/' . $current_photo;
                if (is_file($oldPath)) {
                    unlink($oldPath);
                }
            }
            header("Location: profile.php?updated=1");
            exit;
        } else {
            $errors[] = "Gagal menyimpan perubahan: " . mysqli_error($conn);
        }
    }

    // Refresh data jika ada error
    $user_name = $nama;
    $current_photo = $photoToSave;
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Edit Profil | WorldBike</title>
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-QWTKZyjpPEjISv5WaRU9OFeRpok6YctnYmDr5pNlyT2bRjXh0JMhjY6hW+ALEwIH" crossorigin="anonymous">
    <link rel="stylesheet" href="../assets/css/style.css">
    <style>
    body {
        background: radial-gradient(circle at 12% 20%, rgba(52,152,219,0.12), transparent 30%),
                    radial-gradient(circle at 85% 10%, rgba(46,204,113,0.12), transparent 28%),
                    linear-gradient(120deg, #e0eafc 0%, #cfdef3 100%);
        min-height: 100vh;
        padding-top: 88px;
    }
    .navbar {
        display: flex;
        justify-content: space-between;
        align-items: center;
        padding: 22px 7vw;
        background: #fff;
        box-shadow: 0 2px 18px rgba(44,62,80,0.07);
        position: fixed;
        top: 0; left: 0; right: 0;
        z-index: 1000;
        width: 100%;
    }
    .navbar .logo { font-size: 2rem; font-weight: bold; color: #3498db; letter-spacing: 2px; }
    .navbar ul { display: flex; gap: 32px; list-style: none; margin: 0; padding: 0; align-items: center; }
    .navbar ul li a { font-size: 1.1rem; font-weight: 500; padding: 6px 14px; border-radius: 6px; color: #2c3e50; display: block; transition: background 0.2s, color 0.2s; text-decoration: none; }
    .navbar ul li a:hover, .navbar ul li a.active { background: #3498db; color: #fff; }
    .navbar ul li.nav-right { margin-left: auto; }
    .navbar ul li a.cart-link { display: inline-flex; align-items: center; gap: 8px; padding: 6px 14px; justify-content: center; min-width: 110px; }
    .profile-menu { position: relative; }
    .profile-trigger { display: inline-flex; align-items: center; gap: 8px; padding: 6px 14px; background: #eaf6fb; color: #3498db; font-weight: 600; border: none; border-radius: 6px; cursor: pointer; transition: background 0.2s, color 0.2s; }
    .profile-trigger:hover { background: #d9edf9; }
    .profile-dropdown { position: absolute; right: 0; top: 110%; background: #fff; border: 1px solid #e8edf3; border-radius: 10px; box-shadow: 0 10px 24px rgba(44,62,80,0.12); padding: 6px 0; min-width: 180px; display: none; z-index: 20; }
    .profile-dropdown.show { display: block; }
    .profile-dropdown a { display: block; padding: 10px 14px; color: #1f2d3d; text-decoration: none; font-weight: 600; transition: background 0.15s, color 0.15s; }
    .profile-dropdown a:hover { background: #f1f6ff; color: #217dbb; }
    .profile-dropdown a.logout { color: #b91c1c; }
    .profile-dropdown a.logout:hover { background: #fff1f2; }

    .shell { max-width: 760px; margin: 0 auto 110px auto; padding: 0 6vw; }
    .card-edit {
        background: #fff;
        border-radius: 16px;
        box-shadow: 0 14px 36px rgba(44,62,80,0.1);
        border: 1px solid #e3ebf5;
        padding: 20px 18px;
    }
    .header-row { display: flex; justify-content: space-between; align-items: center; gap: 10px; margin-bottom: 6px; }
    .header-row h1 { margin: 0; font-size: 1.35rem; color: #1b3a57; font-weight: 800; }
    .eyebrow { font-size: 0.82rem; text-transform: uppercase; letter-spacing: 0.08em; color: #607286; font-weight: 800; }
    label { font-weight: 700; color: #1f3b57; margin-top: 12px; display: block; }
    .form-control { border-radius: 10px; border: 1px solid #d7e2ef; padding: 11px 12px; background: #f8fbff; }
    .form-control:focus { border-color: #3498db; box-shadow: 0 0 0 0.15rem rgba(52,152,219,0.18); }
    .avatar-preview { display: flex; align-items: center; gap: 14px; margin-top: 8px; }
    .avatar-box { width: 92px; height: 92px; border-radius: 50%; overflow: hidden; background: #eaf2fb; display: grid; place-items: center; font-weight: 800; color: #217dbb; font-size: 1.6rem; border: 2px solid #d7e2ef; }
    .avatar-box img { width: 100%; height: 100%; object-fit: cover; }
    .note { color: #607286; font-size: 0.95rem; }
    .actions { display: flex; gap: 10px; margin-top: 18px; flex-wrap: wrap; }
    .btn-primary-custom { background: linear-gradient(135deg, #3498db, #217dbb); color: #fff; border: none; padding: 12px 16px; border-radius: 12px; font-weight: 800; box-shadow: 0 10px 26px rgba(52,152,219,0.22); }
    .btn-outline-custom { background: #f4f7fb; color: #1b3a57; border: 1px solid #e1e7ef; padding: 12px 16px; border-radius: 12px; font-weight: 800; }
    .checkbox-row { margin-top: 10px; display: flex; align-items: center; gap: 10px; }
    .alert { margin-top: 10px; }
    @media (max-width: 600px) {
        .navbar { padding: 12px 3vw; }
        .navbar .logo { font-size: 1.4rem; }
        .navbar ul { gap: 12px; }
        .navbar ul li.nav-right { margin-left: 0; }
        .shell { padding: 0 4vw; }
    }
    </style>
</head>
<body>
<nav class="navbar">
    <div class="logo">WorldBike</div>
    <ul>
        <li><a href="../index.php">Home</a></li>
        <li><a href="index.php">Marketplace</a></li>
        <li><a href="cart.php" class="cart-link">Keranjang</a></li>
        <li class="nav-right profile-menu">
            <button class="profile-trigger" id="profileMenuBtn">Hi, <?= htmlspecialchars($user_name ?: 'Pengguna') ?> ▾</button>
            <div class="profile-dropdown" id="profileDropdown">
                <a href="profile.php">Kembali ke Profil</a>
                <a href="../auth/logout.php" class="logout">Logout</a>
            </div>
        </li>
    </ul>
</nav>

<div class="shell">
    <div class="eyebrow">Pengaturan Akun</div>
    <div class="card-edit">
        <div class="header-row">
            <h1>Edit Profil</h1>
            <a href="profile.php" class="btn btn-outline-custom">Kembali</a>
        </div>
        <p class="note">Perbarui nama dan kelola foto profilmu.</p>

        <?php if (!empty($errors)): ?>
            <div class="alert alert-danger" role="alert">
                <?= implode('<br>', array_map('htmlspecialchars', $errors)) ?>
            </div>
        <?php endif; ?>

        <form method="post" enctype="multipart/form-data">
            <label>Nama Lengkap</label>
            <input type="text" name="nama" class="form-control" value="<?= htmlspecialchars($user_name) ?>" required>

            <label>Email</label>
            <input type="email" class="form-control" value="<?= htmlspecialchars($user_email) ?>" disabled>
            <div class="note" style="margin-top:4px;">Email tidak dapat diubah.</div>

            <label>Foto Profil</label>
            <div class="avatar-preview">
                <div class="avatar-box">
                    <?php if (!empty($current_photo)): ?>
                        <img src="../assets/img/avatars/<?= htmlspecialchars($current_photo) ?>" alt="Foto profil">
                    <?php else: ?>
                        <span><?= htmlspecialchars(strtoupper(substr($user_name ?: 'U', 0, 1))) ?></span>
                    <?php endif; ?>
                </div>
                <div style="flex:1;">
                    <input type="file" name="photo" accept="image/*" class="form-control">
                    <div class="note">Format: jpg, jpeg, png, webp. Maks 2MB.</div>
                    <div class="checkbox-row">
                        <input type="checkbox" id="remove_photo" name="remove_photo" <?= $current_photo ? '' : 'disabled' ?>>
                        <label for="remove_photo" style="margin:0; font-weight:600; color:#b91c1c;">Hapus foto profil</label>
                    </div>
                </div>
            </div>

            <div class="actions">
                <button type="submit" class="btn btn-primary-custom">Simpan Perubahan</button>
                <a href="profile.php" class="btn btn-outline-custom">Batal</a>
            </div>
        </form>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const btn = document.getElementById('profileMenuBtn');
    const menu = document.getElementById('profileDropdown');
    if (btn && menu) {
        btn.addEventListener('click', function(e) {
            e.stopPropagation();
            menu.classList.toggle('show');
        });
        document.addEventListener('click', function(e) {
            if (!menu.contains(e.target) && e.target !== btn) {
                menu.classList.remove('show');
            }
        });
    }
});
</script>
</body>
</html>
