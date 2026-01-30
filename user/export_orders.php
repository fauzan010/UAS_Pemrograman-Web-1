<?php
session_start();
include "../config/database.php";

// Sinkronkan session dari cookie jika ada
if (!isset($_SESSION['user_id']) && isset($_COOKIE['user_id'])) {
    $_SESSION['user_id'] = $_COOKIE['user_id'];
    $_SESSION['role'] = $_COOKIE['role'] ?? 'user';
}

if (!isset($_SESSION['user_id'])) {
    header("Location: ../auth/login.php?redirect=user/profile.php");
    exit;
}

$uid = intval($_SESSION['user_id']);
$format = strtolower($_GET['format'] ?? 'pdf');

$user_q = mysqli_query($conn, "SELECT nama, email FROM users WHERE id=$uid");
$user = mysqli_fetch_assoc($user_q);
$user_name = $user['nama'] ?? 'Pengguna';
$user_email = $user['email'] ?? '-';

$rs = mysqli_query($conn, "
    SELECT o.id, o.tanggal, o.status, o.qty, o.harga, o.metode_bayar, p.nama_produk
    FROM orders o
    JOIN products p ON o.product_id = p.id
    WHERE o.user_id = $uid
    ORDER BY o.tanggal DESC
");

$rows = [];
if ($rs) {
    while ($r = mysqli_fetch_assoc($rs)) {
        $rows[] = $r;
    }
}

if ($format === 'excel') {
    header('Content-Type: application/vnd.ms-excel');
    header('Content-Disposition: attachment; filename="riwayat_pesanan_' . $uid . '_' . date('Ymd_His') . '.xls"');
    echo "<table border=\"1\" cellspacing=\"0\" cellpadding=\"6\">";
    echo "<thead><tr><th>ID</th><th>Tanggal</th><th>Produk</th><th>Qty</th><th>Total</th><th>Metode</th><th>Status</th></tr></thead><tbody>";
    if (empty($rows)) {
        echo "<tr><td colspan=\"7\">Tidak ada pesanan</td></tr>";
    } else {
        foreach ($rows as $r) {
            echo '<tr>';
            echo '<td>#' . htmlspecialchars($r['id']) . '</td>';
            echo '<td>' . htmlspecialchars(date('d/m/Y H:i', strtotime($r['tanggal']))) . '</td>';
            echo '<td>' . htmlspecialchars($r['nama_produk']) . '</td>';
            echo '<td>' . intval($r['qty']) . '</td>';
            echo '<td>' . htmlspecialchars('Rp ' . number_format($r['harga'])) . '</td>';
            echo '<td>' . htmlspecialchars(strtoupper($r['metode_bayar'])) . '</td>';
            echo '<td>' . htmlspecialchars($r['status']) . '</td>';
            echo '</tr>';
        }
    }
    echo '</tbody></table>';
    exit;
}

function escape_pdf_text(string $text): string {
    $text = str_replace(['\\', '(', ')'], ['\\\\', '\\(', '\\)'], $text);
    return preg_replace('/[\r\n]+/', ' ', $text);
}

function build_simple_pdf(array $lines): string {
    $pdf = "%PDF-1.4\n";
    $offsets = [];

    // Start text a bit closer to the left to fit 90+ chars
    $content = "BT\n/F1 10 Tf\n";
    $y = 820;
    foreach ($lines as $line) {
        $safe = escape_pdf_text($line);
        $content .= sprintf("1 0 0 1 28 %.2f Tm\n(%s) Tj\n", $y, $safe);
        $y -= 16;
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
$lines[] = 'WorldBike - Riwayat Pesanan';
$lines[] = 'Nama: ' . $user_name . ' | Email: ' . $user_email;
$lines[] = 'Dicetak: ' . date('d/m/Y H:i');
$lines[] = str_repeat('-', 90);
$lines[] = sprintf("%-5s %-16s %-22s %-4s %-11s %-18s %-12s", 'ID', 'Tanggal', 'Produk', 'Qty', 'Total', 'Metode', 'Status');
$lines[] = str_repeat('-', 90);

$printed = 0;
foreach ($rows as $r) {
    $line = sprintf(
        "#%-4s %-16s %-22s %-4s %-11s %-18s %-12s",
        $r['id'],
        date('d/m/y H:i', strtotime($r['tanggal'])),
        substr($r['nama_produk'], 0, 22),
        $r['qty'],
        'Rp ' . number_format($r['harga']),
        strtoupper(substr($r['metode_bayar'], 0, 18)),
        substr($r['status'], 0, 12)
    );
    $lines[] = $line;
    $printed++;
    if ($printed >= 42) {
        $lines[] = '...data dipotong agar muat di halaman';
        break;
    }
}

if ($printed === 0) {
    $lines[] = 'Tidak ada pesanan.';
}

$pdf = build_simple_pdf($lines);
header('Content-Type: application/pdf');
header('Content-Disposition: attachment; filename="riwayat_pesanan_' . $uid . '_' . date('Ymd_His') . '.pdf"');
header('Content-Length: ' . strlen($pdf));
echo $pdf;
exit;
