<?php
session_start();

// Hapus semua session
$_SESSION = [];
session_unset();
session_destroy();

// Hapus cookie login
setcookie("user_id", "", time() - 3600, "/");
setcookie("role", "", time() - 3600, "/");

// Redirect ke halaman utama
header("Location: /worldbike/index.php");
exit;
