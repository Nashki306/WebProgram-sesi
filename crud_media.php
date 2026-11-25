<?php
require_once 'auth.php';
require_once 'ui.php';
require_once 'koneksi.php';

$rows = [];
$queryError = null;
if ($conn) {
    try {
        $result = $conn->query('SELECT id, nim, nama, program_studi, email, foto, audio, dokumen FROM mahasiswa ORDER BY id DESC');
        $rows = $result->fetch_all(MYSQLI_ASSOC);
    } catch (Throwable $exception) {
        $queryError = 'Data belum dapat ditampilkan. Pastikan tabel mahasiswa sudah memiliki kolom foto, audio, dan dokumen.';
    }
} else {
    $queryError = $db_error;
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8"><meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="theme-color" content="#f7f8fc"><title>CRUD Media Mahasiswa · WebLab</title><link rel="stylesheet" href="style.css?v=<?= time() ?>">
    <style>
        .table-avatar-img, img.table-avatar-img {
            width: 44px !important;
            height: 44px !important;
            max-width: 44px !important;
            max-height: 44px !important;
            min-width: 44px !important;
            min-height: 44px !important;
            border-radius: 8px !important;
            object-fit: cover !important;
            display: block !important;
            margin: 0 auto !important;
            border: 1px solid #e1e3ed !important;
        }
        .col-foto-cell {
            width: 65px !important;
            max-width: 65px !important;
            text-align: center !important;
            padding: 8px !important;
        }
    </style>
</head>
<body class="app-body">
<div class="app-shell">
    <?php app_topbar('CRUD Media', 'Kelola data mahasiswa beserta upload Foto, Audio, dan Dokumen', 'crud_media'); ?>
    <div class="app-main">
        <main class="page-content">
            <section class="page-intro page-intro-row">
                <div>
                    <span class="eyebrow">SESI 10 &middot; MULTIMEDIA UPLOAD</span>
                    <h1>CRUD Media Mahasiswa</h1>
                    <p>Kelola data mahasiswa lengkap dengan upload dan pemutaran media (Foto, Audio, dan Dokumen PDF).</p>
                </div>
                <a class="button button-primary" href="form_media.php"><?= app_icon('plus') ?> Tambah Media Mahasiswa</a>
            </section>

            <?php if (isset($_GET['created'])): ?><div class="alert alert-success" role="status"><?= app_icon('check') ?><span>Data mahasiswa dan file media berhasil disimpan.</span></div><?php endif; ?>
            <?php if (isset($_GET['updated'])): ?><div class="alert alert-success" role="status"><?= app_icon('check') ?><span>Data mahasiswa dan file media berhasil diperbarui.</span></div><?php endif; ?>
            <?php if (isset($_GET['deleted'])): ?><div class="alert alert-success" role="status"><?= app_icon('check') ?><span>Data mahasiswa dan file media terkait berhasil dihapus dari server.</span></div><?php endif; ?>
            <?php if (isset($_GET['error'])): ?><div class="alert alert-error" role="alert"><?= app_icon('shield') ?><span>Terjadi kesalahan saat memproses data. Silakan coba kembali.</span></div><?php endif; ?>

            <?php if ($queryError): ?>
                <div class="alert alert-warning database-alert"><?= app_icon('database') ?><span><?= h($queryError) ?></span></div>
            <?php else: ?>
                <section class="panel-card table-panel">
                    <div class="table-toolbar">
                        <div>
                            <div class="table-title">Daftar Mahasiswa &amp; Media</div>
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
                                    <th style="width: 45px;">No.</th>
                                    <th style="width: 70px; text-align: center;">Foto</th>
                                    <th>NIM</th>
                                    <th>Nama Mahasiswa</th>
                                    <th>Program Studi</th>
                                    <th>Email</th>
                                    <th>Audio</th>
                                    <th>Dokumen (PDF)</th>
                                    <th class="table-action-col">Aksi</th>
                                </tr>
                            </thead>
                            <tbody>
                            <?php foreach ($rows as $index => $row): ?>
                                <tr>
                                    <td class="row-number"><?= $index + 1 ?></td>
                                    <td class="col-foto-cell">
                                        <?php if (!empty($row['foto']) && file_exists('uploads/images/' . $row['foto'])): ?>
                                            <a href="uploads/images/<?= h($row['foto']) ?>" target="_blank" title="Buka foto ukuran penuh" class="table-media-preview-link">
                                                <img src="uploads/images/<?= h($row['foto']) ?>" alt="Foto <?= h($row['nama']) ?>" class="table-avatar-img" width="44" height="44" style="width:44px!important;height:44px!important;max-width:44px!important;max-height:44px!important;object-fit:cover!important;border-radius:8px!important;">
                                            </a>
                                        <?php else: ?>
                                            <span class="media-placeholder-badge" title="Tidak ada foto">-</span>
                                        <?php endif; ?>
                                    </td>
                                    <td><span class="nim-value"><?= h($row['nim']) ?></span></td>
                                    <td>
                                        <div class="student-cell">
                                            <strong><?= h($row['nama']) ?></strong>
                                        </div>
                                    </td>
                                    <td><?= h($row['program_studi']) ?></td>
                                    <td><a class="table-email" href="mailto:<?= h($row['email']) ?>"><?= h($row['email']) ?></a></td>
                                    <td>
                                        <?php if (!empty($row['audio']) && file_exists('uploads/audio/' . $row['audio'])): ?>
                                            <div class="table-audio-player">
                                                <audio controls preload="none">
                                                    <source src="uploads/audio/<?= h($row['audio']) ?>">
                                                    Browser Anda tidak mendukung audio.
                                                </audio>
                                            </div>
                                        <?php else: ?>
                                            <span class="media-placeholder-badge" title="Tidak ada audio">-</span>
                                        <?php endif; ?>
                                    </td>
                                    <td>
                                        <?php if (!empty($row['dokumen']) && file_exists('uploads/documents/' . $row['dokumen'])): ?>
                                            <a class="button button-outline button-small btn-view-doc" href="uploads/documents/<?= h($row['dokumen']) ?>" target="_blank" title="Buka Dokumen PDF">
                                                <?= app_icon('file') ?><span>Buka PDF</span>
                                            </a>
                                        <?php else: ?>
                                            <span class="media-placeholder-badge" title="Tidak ada dokumen">-</span>
                                        <?php endif; ?>
                                    </td>
                                    <td class="table-action-cell">
                                        <div class="action-buttons">
                                            <a href="edit_media.php?id=<?= (int) $row['id'] ?>" class="btn-action btn-edit" title="Edit Data &amp; Media" aria-label="Edit <?= h($row['nama']) ?>">
                                                <?= app_icon('edit') ?>
                                            </a>
                                            <form method="POST" action="hapus_media.php" class="delete-form" onsubmit="return confirm('Apakah Anda yakin ingin menghapus data mahasiswa <?= h(addslashes($row['nama'])) ?> beserta seluruh file medianya?');">
                                                <input type="hidden" name="id" value="<?= (int) $row['id'] ?>">
                                                <input type="hidden" name="csrf_token" value="<?= h(csrf_token()) ?>">
                                                <button type="submit" class="btn-action btn-delete" title="Hapus Data &amp; Media" aria-label="Hapus <?= h($row['nama']) ?>">
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
                            <strong>Belum ada data mahasiswa dengan media</strong>
                            <p>Mulai dengan menambahkan data mahasiswa dan upload foto/audio/dokumen.</p>
                            <a href="form_media.php" class="button button-primary button-small"><?= app_icon('plus') ?> Tambah Data Media</a>
                        </div>
                        <div class="table-empty hidden" id="noSearchResults">
                            <span class="empty-icon"><?= app_icon('search') ?></span>
                            <strong>Tidak ada hasil pencarian</strong>
                            <p>Coba kata kunci lain atau periksa kembali ejaan.</p>
                        </div>
                    </div>
                    <div class="table-footer">
                        <span>Menampilkan <strong id="visibleRows"><?= count($rows) ?></strong> dari <?= count($rows) ?> data</span>
                        <span>Terhubung ke MySQL &amp; Uploads Folder <i class="status-dot"></i></span>
                    </div>
                </section>
            <?php endif; ?>

            <div class="table-tip">
                <?= app_icon('shield') ?>
                <p><strong>Info Skenario CRUD Media:</strong> Foto (JPG/PNG/WEBP maks 2 MB), Audio (MP3/WAV/OGG maks 5 MB), Dokumen (PDF maks 5 MB). File tersimpan terpisah di folder <code>uploads/images/</code>, <code>uploads/audio/</code>, dan <code>uploads/documents/</code>.</p>
            </div>
        </main>
        <?php app_footer(); ?>
    </div>
</div>
<script src="app.js"></script>
</body>
</html>
