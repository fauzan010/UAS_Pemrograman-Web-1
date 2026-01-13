<?php
include "../config/database.php";

$email    = $_POST['email'];
$password = $_POST['password'];

// ambil data user berdasarkan email
$query = mysqli_query($conn, "SELECT * FROM users WHERE email='$email'");
$user  = mysqli_fetch_assoc($query);

// cek user ada atau tidak
if ($user) {
    // cek password
    if (password_verify($password, $user['password'])) {

        // SET COOKIE (login)
        setcookie("user_id", $user['id'], time() + (60 * 60), "/");
        setcookie("role", $user['role'], time() + (60 * 60), "/");

        // SET SESSION (agar $_SESSION['user_id'] tersedia)
        session_start();
        $_SESSION['user_id'] = $user['id'];
        $_SESSION['role'] = $user['role'];

        // Redirect ke halaman tujuan jika ada
        $redirect = isset($_GET['redirect']) ? $_GET['redirect'] : (isset($_POST['redirect']) ? $_POST['redirect'] : null);
        if ($redirect) {
            // Jika sudah diawali '/', gunakan langsung
            if (strpos($redirect, '/') === 0) {
                header("Location: $redirect");
            } else {
                // fallback: tambahkan slash di depan
                header("Location: /worldbike/$redirect");
            }
            exit;
        }

        // redirect sesuai role
        if ($user['role'] == 'admin') {
            header("Location: ../admin/dashboard.php");
        } else {
            header("Location: ../index.php");
        }
        exit;

    } else {
        echo "<script>alert('Password salah!');window.location='login.php';</script>";
        exit;
    }
} else {
    echo "<script>alert('Email belum terdaftar!');window.location='login.php';</script>";
    exit;
}
