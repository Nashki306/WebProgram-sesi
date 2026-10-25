<?php
$host = 'localhost';
$user = 'root';
$password = '';
$database = 'web_programming_1';
$conn = null;
$db_error = null;

if (!class_exists('mysqli')) {
    $db_error = 'Ekstensi MySQLi pada PHP belum aktif. Aktifkan MySQLi lalu muat ulang halaman.';
} else {
    mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);
    try {
        $conn = new mysqli($host, $user, $password, $database);
        $conn->set_charset('utf8mb4');
    } catch (Throwable $exception) {
        $db_error = 'Database belum dapat dihubungkan. Periksa layanan MySQL dan konfigurasi koneksi.';
    }
}
