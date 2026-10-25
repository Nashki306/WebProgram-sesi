<?php
require_once 'auth.php';
require_once 'ui.php';
require_once 'koneksi.php';

$values = ['nim' => '', 'nama' => '', 'program_studi' => '', 'email' => ''];
$error = null;
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    foreach ($values as $key => $unused) {
        $values[$key] = trim((string) ($_POST[$key] ?? ''));
    }
    if (!csrf_is_valid($_POST['csrf_token'] ?? null)) {
        $error = 'Sesi formulir tidak valid. Muat ulang halaman lalu coba kembali.';
    } elseif (!$conn) {
        $error = $db_error;
    } elseif (in_array('', $values, true)) {
        $error = 'Semua kolom wajib diisi.';
    } elseif (!filter_var($values['email'], FILTER_VALIDATE_EMAIL)) {
        $error = 'Format email belum valid. Periksa kembali alamat email.';
    } elseif (strlen($values['nim']) > 30 || strlen($values['nama']) > 120 || strlen($values['program_studi']) > 120 || strlen($values['email']) > 160) {
        $error = 'Ada kolom yang terlalu panjang. Periksa kembali data yang dimasukkan.';
    } else {
        try {
            $stmt = $conn->prepare('INSERT INTO mahasiswa (nim, nama, program_studi, email) VALUES (?, ?, ?, ?)');
            $stmt->bind_param('ssss', $values['nim'], $values['nama'], $values['program_studi'], $values['email']);
            $stmt->execute();
            $stmt->close();
            header('Location: data_mahasiswa.php?created=1');
            exit;
        } catch (Throwable $exception) {
            $error = 'Data gagal disimpan. Pastikan NIM belum terdaftar dan struktur tabel mahasiswa sudah sesuai.';
        }
    }
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8"><meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="theme-color" content="#f7f8fc"><title>Tambah Mahasiswa · WebLab</title><link rel="stylesheet" href="style.css">
</head>
<body class="app-body">
<div class="app-shell">
    <?php app_topbar('Tambah Mahasiswa', 'Tambahkan data baru ke dalam database'); ?>
    <div class="app-main">
        <main class="page-content">
            <section class="page-intro"><span class="eyebrow">NEW RECORD</span><h1>Tambah mahasiswa</h1><p>Lengkapi informasi di bawah ini. Pastikan NIM dan alamat email benar sebelum menyimpan data.</p></section>
            <div class="form-layout">
                <section class="panel-card student-form-panel">
                    <div class="panel-heading"><div class="panel-heading-icon"><?= app_icon('plus') ?></div><div><h2>Informasi mahasiswa</h2><p>Kolom bertanda wajib diisi.</p></div></div>
                    <?php if ($error): ?><div class="alert alert-error form-alert" role="alert"><?= app_icon('shield') ?><span><?= h($error) ?></span></div><?php endif; ?>
                    <form method="POST" action="form_mahasiswa.php" class="student-form">
                        <input type="hidden" name="csrf_token" value="<?= h(csrf_token()) ?>">
                        <div class="form-grid">
                            <div class="form-group"><label for="nim">NIM <span>*</span></label><input id="nim" name="nim" type="text" maxlength="30" value="<?= h($values['nim']) ?>" placeholder="Contoh: 4112755201250001" required></div>
                            <div class="form-group"><label for="nama">Nama lengkap <span>*</span></label><input id="nama" name="nama" type="text" maxlength="120" value="<?= h($values['nama']) ?>" placeholder="Masukkan nama lengkap" required></div>
                            <div class="form-group"><label for="program_studi">Program studi <span>*</span></label><input id="program_studi" name="program_studi" type="text" maxlength="120" value="<?= h($values['program_studi']) ?>" placeholder="Contoh: Informatika" required></div>
                            <div class="form-group"><label for="email">Email <span>*</span></label><input id="email" name="email" type="email" maxlength="160" value="<?= h($values['email']) ?>" placeholder="nama@email.com" required></div>
                        </div>
                        <div class="form-actions"><a class="button button-outline" href="data_mahasiswa.php">Batal</a><button class="button button-primary" type="submit"><?= app_icon('check') ?> Simpan data</button></div>
                    </form>
                </section>
                <aside class="form-side-note"><div class="note-illustration"><?= app_icon('shield') ?></div><h3>Data yang rapi dimulai dari input yang benar.</h3><p>Periksa kembali NIM, nama, program studi, dan email. Data akan tersimpan ke database setelah formulir berhasil divalidasi.</p><ul><li><?= app_icon('check') ?> Gunakan NIM yang unik.</li><li><?= app_icon('check') ?> Pastikan format email valid.</li><li><?= app_icon('check') ?> Hindari spasi yang tidak diperlukan.</li></ul></aside>
            </div>
        </main>
        <?php app_footer(); ?>
    </div>
</div>
<script src="app.js"></script>
</body>
</html>
