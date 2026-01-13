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
?>
