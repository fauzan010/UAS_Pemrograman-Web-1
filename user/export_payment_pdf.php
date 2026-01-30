<?php
session_start();
include "../config/database.php";

// Sinkronkan session dari cookie jika ada
if (!isset($_SESSION['user_id']) && isset($_COOKIE['user_id'])) {
    $_SESSION['user_id'] = $_COOKIE['user_id'];
    $_SESSION['role'] = $_COOKIE['role'] ?? 'user';
}

if (!isset($_SESSION['user_id'])) {
    header("Location: ../auth/login.php?redirect=user/payment_success.php");
    exit;
}

$uid = intval($_SESSION['user_id']);
$order_id = isset($_GET['order_id']) ? intval($_GET['order_id']) : 0;
if ($order_id <= 0) {
    header("Location: index.php");
    exit;
}

$order_q = mysqli_query($conn, "
    SELECT o.*, p.nama_produk, p.harga AS harga_produk
    FROM orders o
    JOIN products p ON o.product_id = p.id
    WHERE o.id = $order_id AND o.user_id = $uid
    LIMIT 1
");
$order = $order_q ? mysqli_fetch_assoc($order_q) : null;

if (!$order) {
    header("Location: index.php");
    exit;
}

function escape_pdf_text(string $text): string {
    $text = str_replace(['\\', '(', ')'], ['\\\\', '\\(', '\\)'], $text);
    return preg_replace('/[\r\n]+/', ' ', $text);
}

function build_simple_pdf(array $lines): string {
    $pdf = "%PDF-1.4\n";
    $offsets = [];

    $content = "BT\n/F1 12 Tf\n";
    $y = 800;
    foreach ($lines as $line) {
        $safe = escape_pdf_text($line);
        $content .= sprintf("1 0 0 1 40 %.2f Tm\n(%s) Tj\n", $y, $safe);
        $y -= 18;
    }
    $content .= "ET\n";

    $len = strlen($content);

    $offsets[1] = strlen($pdf);
    $pdf .= "1 0 obj\n<< /Type /Catalog /Pages 2 0 R >>\nendobj\n";

    $offsets[2] = strlen($pdf);
    $pdf .= "2 0 obj\n<< /Type /Pages /Kids [3 0 R] /Count 1 >>\nendobj\n";

    $offsets[3] = strlen($pdf);
    $pdf .= "3 0 obj\n<< /Type /Page /Parent 2 0 R /MediaBox [0 0 595 842] /Contents 4 0 R /Resources << /Font << /F1 5 0 R >> >> >>\nendobj\n";

    $offsets[4] = strlen($pdf);
    $pdf .= "4 0 obj\n<< /Length $len >>\nstream\n$content\nendstream\nendobj\n";

    $offsets[5] = strlen($pdf);
    $pdf .= "5 0 obj\n<< /Type /Font /Subtype /Type1 /BaseFont /Courier >>\nendobj\n";

    $xrefPos = strlen($pdf);
    $pdf .= "xref\n0 6\n0000000000 65535 f \n";
    for ($i = 1; $i <= 5; $i++) {
        $pdf .= sprintf("%010d 00000 n \n", $offsets[$i]);
    }
    $pdf .= "trailer\n<< /Size 6 /Root 1 0 R >>\nstartxref\n$xrefPos\n%%EOF";

    return $pdf;
}

$lines = [];
$lines[] = 'WorldBike - Bukti Pembayaran';
$lines[] = 'ID Pesanan: #' . $order['id'];
$lines[] = 'Tanggal   : ' . date('d/m/Y H:i', strtotime($order['tanggal']));
$lines[] = 'Nama      : ' . ($order['nama_penerima'] ?: '-');
$lines[] = 'Produk    : ' . $order['nama_produk'];
$lines[] = 'Qty       : ' . $order['qty'];
$lines[] = 'Total     : Rp ' . number_format($order['harga']);
$lines[] = 'Metode    : ' . strtoupper($order['metode_bayar']);
$lines[] = 'Status    : ' . $order['status'];
$lines[] = 'Alamat    : ' . ($order['alamat'] ?: '-');
$lines[] = 'Telepon   : ' . ($order['telepon'] ?: '-');
$lines[] = 'Dicetak   : ' . date('d/m/Y H:i');

$pdf = build_simple_pdf($lines);
header('Content-Type: application/pdf');
header('Content-Disposition: attachment; filename="bukti_pembayaran_' . $order['id'] . '_' . date('Ymd_His') . '.pdf"');
header('Content-Length: ' . strlen($pdf));
echo $pdf;
exit;
