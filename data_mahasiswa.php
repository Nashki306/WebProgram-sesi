<?php
require_once 'auth.php';
require_once 'ui.php';
require_once 'koneksi.php';

$rows = [];
$queryError = null;
if ($conn) {
    try {
        $result = $conn->query('SELECT id, nim, nama, program_studi, email FROM mahasiswa ORDER BY id DESC');
        $rows = $result->fetch_all(MYSQLI_ASSOC);
    } catch (Throwable $exception) {
        $queryError = 'Data belum dapat ditampilkan. Pastikan tabel mahasiswa sudah tersedia di database.';
    }
} else {
    $queryError = $db_error;
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8"><meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="theme-color" content="#f7f8fc"><title>Data Mahasiswa · WebLab</title><link rel="stylesheet" href="style.css">
</head>
<body class="app-body">
<div class="app-shell">
    <?php app_topbar('Data Mahasiswa', 'Kelola dan telusuri data mahasiswa'); ?>
    <div class="app-main">
        <main class="page-content">
            <section class="page-intro"><div><span class="eyebrow">DATA MANAGEMENT</span><h1>Data Mahasiswa</h1><p>Daftar data mahasiswa yang tersimpan di database. Gunakan pencarian untuk menemukan data dengan cepat.</p></div></section>
            <?php if ($queryError): ?>
                <div class="alert alert-warning database-alert"><?= app_icon('database') ?><span><?= h($queryError) ?> Jika database belum dibuat, impor file <strong>database.sql</strong> yang disertakan dalam paket.</span></div>
            <?php else: ?>
                <section class="panel-card table-panel">
                    <div class="table-toolbar"><div><div class="table-title">Daftar mahasiswa</div><p><span class="count-badge"><?= count($rows) ?></span> data tercatat</p></div><label class="search-box"><span><?= app_icon('search') ?></span><input type="search" id="tableSearch" placeholder="Cari nama, NIM, email..." aria-label="Cari data mahasiswa"></label></div>
                    <div class="table-scroll"><table class="data-table" id="studentTable"><thead><tr><th>No.</th><th>NIM</th><th>Nama mahasiswa</th><th>Program studi</th><th>Email</th></tr></thead><tbody>
                    <?php foreach ($rows as $index => $row): ?>
                        <tr>
                            <td class="row-number"><?= $index + 1 ?></td>
                            <td><span class="nim-value"><?= h($row['nim']) ?></span></td>
                            <td><div class="student-cell"><span class="student-avatar"><?= h(function_exists('mb_substr') ? mb_strtoupper(mb_substr((string) $row['nama'], 0, 1, 'UTF-8'), 'UTF-8') : strtoupper(substr((string) $row['nama'], 0, 1))) ?></span><strong><?= h($row['nama']) ?></strong></div></td>
                            <td><?= h($row['program_studi']) ?></td>
                            <td><a class="table-email" href="mailto:<?= h($row['email']) ?>"><?= h($row['email']) ?></a></td>
                        </tr>
                    <?php endforeach; ?>
                    </tbody></table>
                    <div class="table-empty <?= count($rows) ? 'hidden' : '' ?>" id="tableEmpty"><span class="empty-icon"><?= app_icon('database') ?></span><strong><?= count($rows) ? 'Data tidak ditemukan' : 'Belum ada data mahasiswa' ?></strong><p><?= count($rows) ? 'Coba kata kunci lain.' : 'Belum ada data mahasiswa tercatat.' ?></p></div>
                    <div class="table-empty hidden" id="noSearchResults"><span class="empty-icon"><?= app_icon('search') ?></span><strong>Tidak ada hasil pencarian</strong><p>Coba kata kunci lain atau periksa kembali ejaan.</p></div>
                    </div>
                    <div class="table-footer"><span>Menampilkan <strong id="visibleRows"><?= count($rows) ?></strong> dari <?= count($rows) ?> data</span><span>Terhubung ke MySQL <i class="status-dot"></i></span></div>
                </section>
            <?php endif; ?>
            <div class="table-tip"><?= app_icon('shield') ?><p><strong>Catatan:</strong> Data ditampilkan sesuai isi database. Untuk menambahkan entri baru, gunakan formulir agar validasi data tetap konsisten.</p></div>
        </main>
        <?php app_footer(); ?>
    </div>
</div>
<script src="app.js"></script>
</body>
</html>
