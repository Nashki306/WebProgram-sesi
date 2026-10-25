<?php
if (session_status() !== PHP_SESSION_ACTIVE) {
    session_start();
}
require_once 'ui.php';
require_once 'koneksi.php';

if (!empty($_SESSION['user_id'])) {
    header('Location: dashboard.php');
    exit;
}
$existingUsers = 0;
$setupError = null;
$values = ['username' => '', 'nama_lengkap' => ''];
if (!$conn) {
    $setupError = $db_error;
} else {
    try {
        $count = $conn->query('SELECT COUNT(*) AS total FROM users');
        $existingUsers = (int) ($count->fetch_assoc()['total'] ?? 0);
    } catch (Throwable $exception) {
        $setupError = 'Tabel users belum tersedia. Impor file database.sql terlebih dahulu.';
    }
}
if (($_SERVER['REQUEST_METHOD'] ?? '') === 'POST' && !$setupError) {
    $values['username'] = trim((string) ($_POST['username'] ?? ''));
    $values['nama_lengkap'] = trim((string) ($_POST['nama_lengkap'] ?? ''));
    $password = (string) ($_POST['password'] ?? '');
    $confirmPassword = (string) ($_POST['confirm_password'] ?? '');
    if (!csrf_is_valid($_POST['csrf_token'] ?? null)) {
        $setupError = 'Sesi formulir tidak valid. Muat ulang halaman lalu coba kembali.';
    } elseif (!preg_match('/^[A-Za-z0-9_.-]{4,40}$/', $values['username'])) {
        $setupError = 'Username harus 4–40 karakter dan hanya boleh berisi huruf, angka, titik, garis bawah, atau tanda hubung.';
    } elseif (strlen($values['nama_lengkap']) < 2 || strlen($values['nama_lengkap']) > 100) {
        $setupError = 'Nama lengkap wajib diisi (2–100 karakter).';
    } elseif (strlen($password) < 8) {
        $setupError = 'Password minimal 8 karakter.';
    } elseif ($password !== $confirmPassword) {
        $setupError = 'Konfirmasi password tidak sama.';
    } else {
        try {
            $hash = password_hash($password, PASSWORD_DEFAULT);
            $stmt = $conn->prepare('INSERT INTO users (username, password_hash, nama_lengkap) VALUES (?, ?, ?)');
            $stmt->bind_param('sss', $values['username'], $hash, $values['nama_lengkap']);
            $stmt->execute();
            $stmt->close();
            header('Location: login.php?setup=1');
            exit;
        } catch (Throwable $exception) {
            $setupError = 'Akun tidak dapat dibuat. Periksa database dan pastikan username belum digunakan.';
        }
    }
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8"><meta name="viewport" content="width=device-width, initial-scale=1.0"><meta name="theme-color" content="#17172f">
    <title>Siapkan Akun · WebLab</title><link rel="stylesheet" href="style.css">
</head>
<body class="auth-body setup-body">
<main class="setup-wrap">
    <section class="setup-card">
        <div class="setup-icon"><?= app_icon('shield') ?></div>
        <span class="eyebrow">PENDAFTARAN AKUN</span><h1>Buat Akun Pengguna</h1><p class="setup-description">Daftarkan akun untuk dapat mengakses workspace.</p>
        <?php if ($setupError): ?><div class="alert alert-error" role="alert"><span><?= h($setupError) ?></span></div><?php endif; ?>
        <?php if (!$setupError || $setupError): ?>
        <form class="auth-form setup-form" method="POST" action="buat_user.php">
            <input type="hidden" name="csrf_token" value="<?= h(csrf_token()) ?>">
            <div class="form-group"><label for="nama_lengkap">Nama lengkap</label><input class="standalone-input" id="nama_lengkap" name="nama_lengkap" type="text" maxlength="100" value="<?= h($values['nama_lengkap']) ?>" placeholder="Nama lengkap" required></div>
            <div class="form-group"><label for="username">Username</label><input class="standalone-input" id="username" name="username" type="text" minlength="4" maxlength="40" value="<?= h($values['username']) ?>" placeholder="Contoh: user.web" autocomplete="username" required></div>
            <div class="form-group"><label for="password">Password</label><div class="input-wrap"><input id="password" name="password" type="password" minlength="8" placeholder="Minimal 8 karakter" autocomplete="new-password" required><button class="password-toggle" type="button" data-toggle-password="password" aria-label="Tampilkan password">Lihat</button></div></div>
            <div class="form-group"><label for="confirm_password">Konfirmasi password</label><div class="input-wrap"><input id="confirm_password" name="confirm_password" type="password" minlength="8" placeholder="Ulangi password" autocomplete="new-password" required><button class="password-toggle" type="button" data-toggle-password="confirm_password" aria-label="Tampilkan konfirmasi password">Lihat</button></div></div>
            <button class="button button-primary button-full" type="submit">Daftar Sekarang <?= app_icon('arrow') ?></button>
        </form>
        <?php endif; ?>
        <p class="auth-help setup-help" style="margin-top: 15px; text-align: center;"><a href="login.php" style="color: #6258e8; text-decoration: none; font-size: 13px;">Sudah punya akun? Kembali ke login</a></p>
    </section>
    <p class="setup-footnote">© <?= date('Y') ?> WebLab · Setup akun awal</p>
</main>
<script src="app.js"></script>
</body>
</html>
