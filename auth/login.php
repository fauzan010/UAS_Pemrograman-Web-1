<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Login - WorldBike</title>
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
    .login-container {
        background: #fff;
        border-radius: 18px;
        box-shadow: 0 8px 32px rgba(44,62,80,0.10);
        padding: 40px 32px 32px 32px;
        max-width: 370px;
        width: 100%;
        margin: 0 auto 40px auto;
        text-align: center;
        animation: fadeInDown 0.9s;
    }
    .login-container h2 {
        color: #3498db;
        font-size: 1.7rem;
        margin-bottom: 18px;
        font-weight: 700;
    }
    .login-container label {
        display: block;
        text-align: left;
        margin-bottom: 6px;
        color: #217dbb;
        font-weight: 500;
        margin-top: 16px;
    }
    .login-container input[type="email"],
    .login-container input[type="password"] {
        width: 100%;
        padding: 12px;
        border-radius: 8px;
        border: 1px solid #ddd;
        margin-bottom: 10px;
        font-size: 1rem;
        background: #f8fafc;
        transition: border 0.2s;
    }
    .login-container input[type="email"]:focus,
    .login-container input[type="password"]:focus {
        border: 1.5px solid #3498db;
        outline: none;
    }
    .login-container button {
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
    .login-container button:hover {
        background: #217dbb;
    }
    .login-container .register-link {
        margin-top: 18px;
        font-size: 1rem;
        color: #555;
    }
    .login-container .register-link a {
        color: #3498db;
        text-decoration: none;
        font-weight: 500;
        transition: color 0.2s;
    }
    .login-container .register-link a:hover {
        color: #217dbb;
        text-decoration: underline;
    }
    .login-container .success-msg {
        color: #27ae60;
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
        .login-container {
            max-width: 98vw;
            width: 98vw;
            padding: 24px 8vw 18px 8vw;
            margin: 0 0 24px 0;
            border-radius: 0;
            box-shadow: none;
        }
        .login-container h2 {
            font-size: 1.2rem;
        }
        .login-container label,
        .login-container input,
        .login-container button,
        .login-container .register-link {
            font-size: 1rem;
        }
    }
    </style>
</head>
<body>
<div class="navbar-login">
    <span class="logo">WorldBike</span>
</div>
<div class="login-container">
    <?php if (isset($_GET['register']) && $_GET['register'] == 'success'): ?>
        <div class="success-msg">Registrasi berhasil! Silakan login.</div>
    <?php endif; ?>
    <h2>Login WorldBike</h2>
    <form action="login_process.php" method="POST">
        <label>Email</label>
        <input type="email" name="email" required>
        <label>Password</label>
        <input type="password" name="password" required>
        <input type="hidden" name="redirect" value="<?= isset($_GET['redirect']) ? htmlspecialchars($_GET['redirect']) : '' ?>">
        <button type="submit">Login</button>
    </form>
    <div class="register-link">
        Belum punya akun? <a href="register.php">Daftar di sini</a>
    </div>
    <div style="margin-top:22px;">
        <div style="background:#f8fafc;border:1px solid #dbeafe;padding:14px 16px;border-radius:8px;color:#217dbb;font-size:0.98rem;text-align:left;">
            <b>Akun Admin:</b><br>
            Email: <span style="color:#222;">admin@worldbike.com</span><br>
            Password: <span style="color:#222;">admin123</span>
        </div>
        <div style="background:#f8fafc;border:1px solid #dbeafe;padding:14px 16px;border-radius:8px;color:#217dbb;font-size:0.98rem;text-align:left;">
            <b>Akun User:</b><br>
            Silahkan Register Terlebih Dahulu  <span style="color:#222;">
        </div>
    </div>
</div>
<!-- Footer -->
<footer class="footer" style="text-align:center; padding:18px 0; background:#f4f6f8; color:#030000; font-size:15px; position:fixed; left:0; right:0; bottom:0;">
    @Copyright by 23552011029_Fauzan Rizkika Kurnia_TIF RP 23 CNS B_UASWEB1
</footer>
</body>
</html>