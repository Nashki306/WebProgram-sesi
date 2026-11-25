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
    <meta name="theme-color" content="#f7f8fc"><title>CRUD Mahasiswa · WebLab</title><link rel="stylesheet" href="style.css?v=<?= time() ?>">
</head>
<body class="app-body">
<div class="app-shell">
    <?php app_topbar('CRUD Mahasiswa', 'Kelola data mahasiswa (Tambah, Edit, dan Hapus)', 'crud_mahasiswa'); ?>
    <div class="app-main">
        <main class="page-content">
            <section class="page-intro page-intro-row">
                <div>
                    <span class="eyebrow">CRUD DATA MANAGEMENT</span>
                    <h1>CRUD Mahasiswa</h1>
                    <p>Kelola data mahasiswa secara lengkap. Anda dapat menambah, memperbarui, atau menghapus data mahasiswa.</p>
                </div>
                <a class="button button-primary" href="form_mahasiswa.php"><?= app_icon('plus') ?> Tambah Mahasiswa</a>
            </section>

            <?php if (isset($_GET['created'])): ?><div class="alert alert-success" role="status"><?= app_icon('check') ?><span>Data mahasiswa berhasil ditambahkan.</span></div><?php endif; ?>
            <?php if (isset($_GET['updated'])): ?><div class="alert alert-success" role="status"><?= app_icon('check') ?><span>Data mahasiswa berhasil diperbarui.</span></div><?php endif; ?>
            <?php if (isset($_GET['deleted'])): ?><div class="alert alert-success" role="status"><?= app_icon('check') ?><span>Data mahasiswa berhasil dihapus.</span></div><?php endif; ?>
            <?php if (isset($_GET['error'])): ?><div class="alert alert-error" role="alert"><?= app_icon('shield') ?><span>Terjadi kesalahan saat memproses data. Silakan coba kembali.</span></div><?php endif; ?>

            <?php if ($queryError): ?>
                <div class="alert alert-warning database-alert"><?= app_icon('database') ?><span><?= h($queryError) ?></span></div>
            <?php else: ?>
                <section class="panel-card table-panel">
                    <div class="table-toolbar">
                        <div>
                            <div class="table-title">Daftar Mahasiswa (CRUD Aktif)</div>
                            <p><span class="count-badge"><?= count($rows) ?></span> data tercatat</p>
                        </div>
                        <label class="search-box">
                            <span><?= app_icon('search') ?></span>
                            <input type="search" id="tableSearch" placeholder="Cari nama, NIM, email..." aria-label="Cari data mahasiswa">
                        </label>
                    </div>

                    <div class="table-scroll">
                        <table class="data-table" id="studentTable">
                            <thead>
                                <tr>
                                    <th style="width: 50px;">No.</th>
                                    <th>NIM</th>
                                    <th>Nama Mahasiswa</th>
                                    <th>Program Studi</th>
                                    <th>Email</th>
                                    <th class="table-action-col">Aksi</th>
                                </tr>
                            </thead>
                            <tbody>
                            <?php foreach ($rows as $index => $row): ?>
                                <tr>
                                    <td class="row-number"><?= $index + 1 ?></td>
                                    <td><span class="nim-value"><?= h($row['nim']) ?></span></td>
                                    <td>
                                        <div class="student-cell">
                                            <span class="student-avatar"><?= h(function_exists('mb_substr') ? mb_strtoupper(mb_substr((string) $row['nama'], 0, 1, 'UTF-8'), 'UTF-8') : strtoupper(substr((string) $row['nama'], 0, 1))) ?></span>
                                            <strong><?= h($row['nama']) ?></strong>
                                        </div>
                                    </td>
                                    <td><?= h($row['program_studi']) ?></td>
                                    <td><a class="table-email" href="mailto:<?= h($row['email']) ?>"><?= h($row['email']) ?></a></td>
                                    <td class="table-action-cell">
                                        <div class="action-buttons">
                                            <a href="edit_mahasiswa.php?id=<?= (int) $row['id'] ?>&ref=crud" class="btn-action btn-edit" title="Edit Mahasiswa" aria-label="Edit <?= h($row['nama']) ?>">
                                                <?= app_icon('edit') ?>
                                            </a>
                                            <form method="POST" action="hapus_mahasiswa.php" class="delete-form" onsubmit="return confirm('Apakah Anda yakin ingin menghapus data mahasiswa <?= h(addslashes($row['nama'])) ?>?');">
                                                <input type="hidden" name="id" value="<?= (int) $row['id'] ?>">
                                                <input type="hidden" name="csrf_token" value="<?= h(csrf_token()) ?>">
                                                <input type="hidden" name="ref" value="crud">
                                                <button type="submit" class="btn-action btn-delete" title="Hapus Mahasiswa" aria-label="Hapus <?= h($row['nama']) ?>">
                                                    <?= app_icon('trash') ?>
                                                </button>
                                            </form>
                                        </div>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                            </tbody>
                        </table>

                        <div class="table-empty <?= count($rows) ? 'hidden' : '' ?>" id="tableEmpty">
                            <span class="empty-icon"><?= app_icon('database') ?></span>
                            <strong>Belum ada data mahasiswa</strong>
                            <p>Mulai dengan menambahkan data mahasiswa pertama.</p>
                            <a href="form_mahasiswa.php" class="button button-primary button-small"><?= app_icon('plus') ?> Tambah Mahasiswa</a>
                        </div>
                        <div class="table-empty hidden" id="noSearchResults">
                            <span class="empty-icon"><?= app_icon('search') ?></span>
                            <strong>Tidak ada hasil pencarian</strong>
                            <p>Coba kata kunci lain atau periksa kembali ejaan.</p>
                        </div>
                    </div>

                    <div class="table-footer">
                        <span>Menampilkan <strong id="visibleRows"><?= count($rows) ?></strong> dari <?= count($rows) ?> data</span>
                        <span>Terhubung ke MySQL <i class="status-dot"></i></span>
                    </div>
                </section>
            <?php endif; ?>

            <div class="table-tip">
                <?= app_icon('shield') ?>
                <p><strong>Info CRUD Mahasiswa:</strong> Halaman ini mengelola data teks mahasiswa dengan fungsi lengkap Tambah, Edit, dan Hapus.</p>
            </div>
        </main>
        <?php app_footer(); ?>
    </div>
</div>
<script src="app.js"></script>
</body>
</html>
