<?php
require_once 'auth.php';
require_once 'ui.php';
require_once 'koneksi.php';

$jumlahMahasiswa = 0;
$dbReady = (bool) $conn;
if ($conn) {
    try {
        $countResult = $conn->query('SELECT COUNT(*) AS total FROM mahasiswa');
        $jumlahMahasiswa = (int) ($countResult->fetch_assoc()['total'] ?? 0);
    } catch (Throwable $exception) {
        $dbReady = false;
    }
}
$nama = $_SESSION['nama_lengkap'] ?? 'Mahasiswa';
$namaDepan = preg_split('/\s+/', trim($nama))[0] ?: $nama;
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8"><meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="theme-color" content="#f7f8fc"><title>Dashboard · WebLab</title><link rel="stylesheet" href="style.css">
</head>
<body class="app-body">
<div class="app-shell">
    <?php app_topbar('Dashboard', 'Ringkasan workspace pembelajaran Anda'); ?>
    <div class="app-main">
        <main class="page-content">
            <section class="welcome-banner">
                <div class="welcome-copy">
                    <span class="eyebrow eyebrow-light">Selamat Datang di Website Pribadi Saya</span>
                    <h1>About Me & My Collage Assignments.</h1>
                    <p>Terus belajar, berkembang, dan mencoba hal baru untuk menjadi pribadi yang lebih baik setiap hariny"</p>
                    <a class="button button-white" href="index.php">Lihat profil saya <?= app_icon('arrow') ?></a>
                </div>
                <div class="welcome-art" aria-hidden="true">
                    <div class="art-window"><div class="art-window-bar"><i></i><i></i><i></i></div><div class="art-code"><span></span><span></span><span></span><span></span><span></span></div><div class="art-card"><b></b><span></span><span></span></div></div>
                    <div class="art-orbit orbit-a"></div><div class="art-orbit orbit-b"></div>
                </div>
            </section>

            <div class="section-heading"><div><span class="eyebrow">OVERVIEW</span><h2>Ringkasan</h2><p>Informasi singkat.</p></div></div>
            <section class="stats-grid" aria-label="Ringkasan data">
                <article class="stat-card"><div class="stat-icon stat-purple"><?= app_icon('database') ?></div><div class="stat-label">Total mahasiswa</div><div class="stat-number"><?= number_format($jumlahMahasiswa, 0, ',', '.') ?></div><div class="stat-foot"><?= $dbReady ? 'Data yang tersimpan di database' : 'Database belum siap atau tabel belum tersedia' ?></div></article>
                <article class="stat-card"><div class="stat-icon stat-blue"><?= app_icon('layout') ?></div><div class="stat-label">Area pembelajaran</div><div class="stat-number">03</div><div class="stat-foot">Biodata, layout, dan pengelolaan data</div></article>
                <article class="stat-card"><div class="stat-icon stat-green"><?= app_icon('shield') ?></div><div class="stat-label">Status akun</div><div class="stat-number stat-number-label">Aktif</div><div class="stat-foot"><span class="status-dot"></span> Login berhasil dan sesi terlindungi</div></article>
            </section>

            <div class="section-heading section-heading-spaced"><div><span class="eyebrow">QUICK ACCESS</span><h2>Tentang Saya</h2><p>Pilih halaman sesuai kebutuhan.</p></div></div>
            <section class="quick-grid">
                <a class="quick-card" href="index.php"><span class="quick-icon quick-purple"><?= app_icon('user') ?></span><span class="quick-copy"><strong>Biodata Diri</strong><small>Profil, pendidikan, hobi, dan keahlian.</small><em>Buka halaman <?= app_icon('arrow') ?></em></span></a>
                <a class="quick-card" href="layout.php"><span class="quick-icon quick-blue"><?= app_icon('layout') ?></span><span class="quick-copy"><strong>Layout Lab</strong><small>Eksplorasi struktur halaman dan komponen UI.</small><em>Buka halaman <?= app_icon('arrow') ?></em></span></a>
                <a class="quick-card" href="data_mahasiswa.php"><span class="quick-icon quick-green"><?= app_icon('database') ?></span><span class="quick-copy"><strong>Data Mahasiswa</strong><small>Lihat dan cari data yang tersimpan.</small><em>Buka halaman <?= app_icon('arrow') ?></em></span></a>
                <a class="quick-card" href="form_mahasiswa.php"><span class="quick-icon quick-orange"><?= app_icon('plus') ?></span><span class="quick-copy"><strong>Tambah Mahasiswa</strong><small>Masukkan data baru melalui formulir.</small><em>Tambah data <?= app_icon('arrow') ?></em></span></a>
            </section>
            <?php if (!$dbReady): ?><div class="alert alert-warning dashboard-alert"><?= app_icon('database') ?><span>Database belum siap. Pastikan MySQL aktif dan database <strong>web_programming_1</strong> beserta tabelnya sudah dibuat.</span></div><?php endif; ?>
        </main>
        <?php app_footer(); ?>
    </div>
</div>
<script src="app.js"></script>
</body>
</html>
