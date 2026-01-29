<?php
// config/database.php

$host = "localhost";
$user = "root";
$pass = "";
$db   = "worldbike_db";

$conn = mysqli_connect("localhost:3307", "root", "", "worldbike_db");

if (!$conn) {
    die("Koneksi database gagal: " . mysqli_connect_error());
}

// Pastikan kolom foto profil tersedia
if (!function_exists('ensure_user_photo_column')) {
    function ensure_user_photo_column(mysqli $conn): void {
        $check = mysqli_query($conn, "SHOW COLUMNS FROM users LIKE 'photo'");
        if ($check && mysqli_num_rows($check) === 0) {
            mysqli_query($conn, "ALTER TABLE users ADD photo VARCHAR(255) NULL AFTER nama");
        }
    }
}

ensure_user_photo_column($conn);

// Siapkan tabel aktivitas_admin bila belum ada
if (!function_exists('ensure_admin_activity_table')) {
    function ensure_admin_activity_table(mysqli $conn): void {
        $exists = mysqli_query($conn, "SHOW TABLES LIKE 'aktivitas_admin'");
        if ($exists && mysqli_num_rows($exists) === 0) {
            mysqli_query($conn, "
                CREATE TABLE aktivitas_admin (
                    id INT AUTO_INCREMENT PRIMARY KEY,
                    aksi TEXT NOT NULL,
                    waktu TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP
                ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
            ");
        }
        // Pastikan kolom waktu memakai CURRENT_TIMESTAMP agar tidak 0000-00-00
        mysqli_query($conn, "ALTER TABLE aktivitas_admin MODIFY waktu TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP");
    }
}

if (!function_exists('log_admin_action')) {
    function log_admin_action(mysqli $conn, string $aksi): void {
        ensure_admin_activity_table($conn);
        $aksiSafe = mysqli_real_escape_string($conn, $aksi);
        mysqli_query($conn, "INSERT INTO aktivitas_admin (aksi, waktu) VALUES ('$aksiSafe', NOW())");
    }
}

ensure_admin_activity_table($conn);

// Auto-update status pesanan berbasis waktu (dijalankan tiap request)
if (!function_exists('auto_update_order_status')) {
    function auto_update_order_status(mysqli $conn): void {
        date_default_timezone_set('Asia/Jakarta');

        // Dikemas -> Dikirim (>= 60 menit) dan Dikirim/Dikemas -> Sampai (>= 120 menit)
        mysqli_query($conn, "
            UPDATE orders
            SET status = 'Dikirim'
            WHERE status = 'Dikemas'
              AND TIMESTAMPDIFF(MINUTE, tanggal, NOW()) >= 60
              AND TIMESTAMPDIFF(MINUTE, tanggal, NOW()) < 120
        ");

        mysqli_query($conn, "
            UPDATE orders
            SET status = 'Sampai'
            WHERE (status = 'Dikirim' OR status = 'Dikemas')
              AND TIMESTAMPDIFF(MINUTE, tanggal, NOW()) >= 120
        ");
    }
}

auto_update_order_status($conn);
?>
