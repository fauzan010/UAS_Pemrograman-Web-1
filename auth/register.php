<?php
include "../config/database.php";

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nama     = trim($_POST['nama']);
    $email    = trim($_POST['email']);
    $password = $_POST['password'];
    $role     = 'user';

    // Validasi sederhana
    if (empty($nama) || empty($email) || empty($password)) {
        $error = "Semua field wajib diisi!";
    } else {
        // Cek email sudah terdaftar
        $cek = mysqli_query($conn, "SELECT id FROM users WHERE email = '$email'");
        if (mysqli_num_rows($cek) > 0) {
            echo "<script>alert('Email sudah terdaftar! Silakan gunakan email lain.'); window.location='register.php';</script>";
            exit;
        } else {
            // Hash password
            $hash = password_hash($password, PASSWORD_DEFAULT);

            // Simpan ke database
            $stmt = mysqli_prepare($conn, "INSERT INTO users (nama, email, password, role) VALUES (?, ?, ?, ?)");
            mysqli_stmt_bind_param($stmt, "ssss", $nama, $email, $hash, $role);

            if (mysqli_stmt_execute($stmt)) {
                // Berhasil register
                $redirect = isset($_GET['redirect']) ? $_GET['redirect'] : '';
                $redirectParam = $redirect ? '&redirect=' . urlencode($redirect) : '';
                header("Location: login.php?register=success$redirectParam");
                exit;
            } else {
                $error = "Registrasi gagal: ".mysqli_error($conn);
            }
        }
    }
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Register - WorldBike</title>
    <link rel="stylesheet" href="../assets/css/style.css">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <style>
    body {
        background: linear-gradient(120deg, #e0eafc 0%, #cfdef3 100%);
        min-height: 100vh;
        display: flex;
        flex-direction: column;
        align-items: center;
        justify-content: flex-start;
    }
    .navbar-login {
        width: 100%;
        background: #fff;
        box-shadow: 0 2px 18px rgba(44,62,80,0.07);
        padding: 18px 0;
        display: flex;
        justify-content: center;
        align-items: center;
        margin-bottom: 32px;
    }
    .navbar-login .logo {
        font-size: 2rem;
        font-weight: bold;
        color: #3498db;
        letter-spacing: 2px;
        text-shadow: 0 2px 8px rgba(52,152,219,0.08);
    }
    .register-container {
        background: #fff;
        border-radius: 18px;
        box-shadow: 0 8px 32px rgba(44,62,80,0.10);
        padding: 40px 32px 32px 32px;
        max-width: 400px;
        width: 100%;
        margin: 0 auto 40px auto;
        text-align: center;
        animation: fadeInDown 0.9s;
    }
    .register-container h2 {
        color: #3498db;
        font-size: 1.7rem;
        margin-bottom: 18px;
        font-weight: 700;
    }
    .register-container label {
        display: block;
        text-align: left;
        margin-bottom: 6px;
        color: #217dbb;
        font-weight: 500;
        margin-top: 16px;
    }
    .register-container input[type="text"],
    .register-container input[type="email"],
    .register-container input[type="password"] {
        width: 100%;
        padding: 12px;
        border-radius: 8px;
        border: 1px solid #ddd;
        margin-bottom: 10px;
        font-size: 1rem;
        background: #f8fafc;
        transition: border 0.2s;
    }
    .register-container input:focus {
        border: 1.5px solid #3498db;
        outline: none;
    }
    .register-container button {
        width: 100%;
        background: #3498db;
        color: #fff;
        border: none;
        border-radius: 8px;
        padding: 12px 0;
        font-size: 1.08rem;
        font-weight: 600;
        margin-top: 18px;
        cursor: pointer;
        transition: background 0.2s;
    }
    .register-container button:hover {
        background: #217dbb;
    }
    .register-container .login-link {
        margin-top: 18px;
        font-size: 1rem;
        color: #555;
    }
    .register-container .login-link a {
        color: #3498db;
        text-decoration: none;
        font-weight: 500;
        transition: color 0.2s;
    }
    .register-container .login-link a:hover {
        color: #217dbb;
        text-decoration: underline;
    }
    .register-container .error-msg {
        color: #e74c3c;
        margin-bottom: 10px;
        font-size: 1rem;
    }
    @keyframes fadeInDown {
        from { opacity: 0; transform: translateY(-40px);}
        to { opacity: 1; transform: none;}
    }
    @media (max-width: 600px) {
        body {
            padding: 0;
            min-height: 100vh;
        }
        .navbar-login {
            padding: 12px 0;
        }
        .navbar-login .logo {
            font-size: 1.3rem;
        }
        .register-container {
            max-width: 98vw;
            width: 98vw;
            padding: 24px 8vw 18px 8vw;
            margin: 0 0 24px 0;
            border-radius: 0;
            box-shadow: none;
        }
        .register-container h2 {
            font-size: 1.2rem;
        }
        .register-container label,
        .register-container input,
        .register-container button,
        .register-container .login-link {
            font-size: 1rem;
        }
    }
    </style>
</head>
<body>
<div class="navbar-login">
    <span class="logo">WorldBike</span>
</div>
<div class="register-container">
    <?php
    if (isset($_GET['error'])) {
        echo '<div class="error-msg">'.htmlspecialchars($_GET['error']).'</div>';
    }
    ?>
    <h2>Daftar Akun WorldBike</h2>
    <form action="register.php<?= isset($_GET['redirect']) ? '?redirect=' . urlencode($_GET['redirect']) : '' ?>" method="POST">
        <label>Nama Lengkap</label>
        <input type="text" name="nama" required>
        <label>Email</label>
        <input type="email" name="email" required>
        <label>Password</label>
        <input type="password" name="password" required>
        <input type="hidden" name="redirect" value="<?= isset($_GET['redirect']) ? htmlspecialchars($_GET['redirect']) : '' ?>">
        <button type="submit">Daftar</button>
    </form>
    <div class="login-link">
        Sudah punya akun? <a href="login.php<?= isset($_GET['redirect']) ? '?redirect=' . urlencode($_GET['redirect']) : '' ?>">Login di sini</a>
    </div>
</div>
<!-- Footer -->
<footer class="footer" style="text-align:center; padding:18px 0; background:#f4f6f8; color:#030000; font-size:15px; position:fixed; left:0; right:0; bottom:0;">
    @Copyright by 23552011029_Fauzan Rizkika Kurnia_TIF RP 23 CNS B_UASWEB1
</footer>
</body>
</html>