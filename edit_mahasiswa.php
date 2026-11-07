<?php
require_once 'auth.php';
require_once 'ui.php';
require_once 'koneksi.php';

$id = isset($_GET['id']) ? (int) $_GET['id'] : 0;
if ($id <= 0) {
    header('Location: data_mahasiswa.php');
    exit;
}

if (!$conn) {
    die('Koneksi database bermasalah.');
}

$values = ['nim' => '', 'nama' => '', 'program_studi' => '', 'email' => ''];
$error = null;

// Ambil data mahasiswa yang akan diedit
$stmt = $conn->prepare('SELECT id, nim, nama, program_studi, email FROM mahasiswa WHERE id = ?');
$stmt->bind_param('i', $id);
$stmt->execute();
$result = $stmt->get_result();
$current = $result->fetch_assoc();
$stmt->close();

if (!$current) {
    header('Location: data_mahasiswa.php');
    exit;
}

$values = $current;

// Proses form update
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    foreach (['nim', 'nama', 'program_studi', 'email'] as $key) {
        $values[$key] = trim((string) ($_POST[$key] ?? ''));
    }

    if (!csrf_is_valid($_POST['csrf_token'] ?? null)) {
        $error = 'Sesi formulir tidak valid. Muat ulang halaman lalu coba kembali.';
    } elseif (in_array('', $values, true)) {
        $error = 'Semua kolom wajib diisi.';
    } elseif (!filter_var($values['email'], FILTER_VALIDATE_EMAIL)) {
        $error = 'Format email belum valid. Periksa kembali alamat email.';
    } elseif (strlen($values['nim']) > 30 || strlen($values['nama']) > 120 || strlen($values['program_studi']) > 120 || strlen($values['email']) > 160) {
        $error = 'Ada kolom yang terlalu panjang. Periksa kembali data yang dimasukkan.';
    } else {
        // Cek apakah NIM sudah dipakai mahasiswa lain
        $checkStmt = $conn->prepare('SELECT id FROM mahasiswa WHERE nim = ? AND id != ?');
        $checkStmt->bind_param('si', $values['nim'], $id);
        $checkStmt->execute();
        $checkResult = $checkStmt->get_result();
        if ($checkResult->num_rows > 0) {
            $error = 'NIM sudah digunakan oleh mahasiswa lain.';
            $checkStmt->close();
        } else {
            $checkStmt->close();
            try {
                $updateStmt = $conn->prepare('UPDATE mahasiswa SET nim = ?, nama = ?, program_studi = ?, email = ? WHERE id = ?');
                $updateStmt->bind_param('ssssi', $values['nim'], $values['nama'], $values['program_studi'], $values['email'], $id);
                $updateStmt->execute();
                $updateStmt->close();
                header('Location: data_mahasiswa.php?updated=1');
                exit;
            } catch (Throwable $exception) {
                $error = 'Data gagal diperbarui. Pastikan struktur tabel sesuai.';
            }
        }
    }
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="theme-color" content="#f7f8fc">
    <title>Edit Mahasiswa · WebLab</title>
    <link rel="stylesheet" href="style.css">
</head>
<body class="app-body">
<div class="app-shell">
    <?php app_topbar('Edit Mahasiswa', 'Perbarui data mahasiswa yang tersimpan di database'); ?>
    <div class="app-main">
        <main class="page-content">
            <section class="page-intro">
                <span class="eyebrow">UPDATE RECORD</span>
                <h1>Edit Mahasiswa</h1>
                <p>Perbarui informasi mahasiswa di bawah ini. Pastikan perubahan data sudah benar sebelum menyimpan.</p>
            </section>

            <div class="form-layout">
                <section class="panel-card student-form-panel">
                    <div class="panel-heading">
                        <div class="panel-heading-icon"><?= app_icon('edit') ?></div>
                        <div>
                            <h2>Perbarui Informasi Mahasiswa</h2>
                            <p>Kolom bertanda wajib diisi.</p>
                        </div>
                    </div>

                    <?php if ($error): ?>
                        <div class="alert alert-error form-alert" role="alert">
                            <?= app_icon('shield') ?><span><?= h($error) ?></span>
                        </div>
                    <?php endif; ?>

                    <form method="POST" action="edit_mahasiswa.php?id=<?= $id ?>" class="student-form">
                        <input type="hidden" name="csrf_token" value="<?= h(csrf_token()) ?>">
                        <div class="form-grid">
                            <div class="form-group">
                                <label for="nim">NIM <span>*</span></label>
                                <input id="nim" name="nim" type="text" maxlength="30" value="<?= h($values['nim']) ?>" placeholder="Contoh: 4112755201250001" required>
                            </div>
                            <div class="form-group">
                                <label for="nama">Nama lengkap <span>*</span></label>
                                <input id="nama" name="nama" type="text" maxlength="120" value="<?= h($values['nama']) ?>" placeholder="Masukkan nama lengkap" required>
                            </div>
                            <div class="form-group">
                                <label for="program_studi">Program studi <span>*</span></label>
                                <input id="program_studi" name="program_studi" type="text" maxlength="120" value="<?= h($values['program_studi']) ?>" placeholder="Contoh: Informatika" required>
                            </div>
                            <div class="form-group">
                                <label for="email">Email <span>*</span></label>
                                <input id="email" name="email" type="email" maxlength="160" value="<?= h($values['email']) ?>" placeholder="nama@email.com" required>
                            </div>
                        </div>
                        <div class="form-actions">
                            <a class="button button-outline" href="data_mahasiswa.php">Batal</a>
                            <button class="button button-primary" type="submit"><?= app_icon('check') ?> Simpan Perubahan</button>
                        </div>
                    </form>
                </section>

                <aside class="form-side-note">
                    <div class="note-illustration"><?= app_icon('shield') ?></div>
                    <h3>Perubahan Langsung Tersinkron</h3>
                    <p>Setelah formulir disimpan, data mahasiswa dalam database akan langsung diperbarui dan tampil pada tabel data mahasiswa.</p>
                    <ul>
                        <li><?= app_icon('check') ?> Pastikan NIM tetap unik.</li>
                        <li><?= app_icon('check') ?> Gunakan format email yang valid.</li>
                    </ul>
                </aside>
            </div>
        </main>
        <?php app_footer(); ?>
    </div>
</div>
<script src="app.js"></script>
</body>
</html>
