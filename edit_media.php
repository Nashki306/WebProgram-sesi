<?php
require_once 'auth.php';
require_once 'ui.php';
require_once 'koneksi.php';

$id = isset($_GET['id']) ? (int) $_GET['id'] : 0;
if ($id <= 0) {
    header('Location: crud_media.php');
    exit;
}

if (!$conn) {
    die('Koneksi database bermasalah.');
}

// Ambil data lama
$stmt = $conn->prepare('SELECT id, nim, nama, program_studi, email, foto, audio, dokumen FROM mahasiswa WHERE id = ?');
$stmt->bind_param('i', $id);
$stmt->execute();
$current = $stmt->get_result()->fetch_assoc();
$stmt->close();

if (!$current) {
    header('Location: crud_media.php');
    exit;
}

$values = $current;
$error = null;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    foreach (['nim', 'nama', 'program_studi', 'email'] as $key) {
        $values[$key] = trim((string) ($_POST[$key] ?? ''));
    }

    if (!csrf_is_valid($_POST['csrf_token'] ?? null)) {
        $error = 'Sesi formulir tidak valid. Muat ulang halaman lalu coba kembali.';
    } elseif (in_array('', $values, true)) {
        $error = 'Semua field teks wajib diisi.';
    } elseif (!filter_var($values['email'], FILTER_VALIDATE_EMAIL)) {
        $error = 'Format email belum valid.';
    } elseif (strlen($values['nim']) > 30 || strlen($values['nama']) > 120 || strlen($values['program_studi']) > 120 || strlen($values['email']) > 160) {
        $error = 'Ada data yang terlalu panjang.';
    } else {
        // Cek NIM duplikat
        $stmtCek = $conn->prepare('SELECT id FROM mahasiswa WHERE nim = ? AND id != ?');
        $stmtCek->bind_param('si', $values['nim'], $id);
        $stmtCek->execute();
        if ($stmtCek->get_result()->num_rows > 0) {
            $error = 'NIM sudah digunakan oleh mahasiswa lain.';
            $stmtCek->close();
        } else {
            $stmtCek->close();

            // Skenario 5 & 6: Update media
            // Default gunakan nama file lama
            $fotoNew = $current['foto'];
            $audioNew = $current['audio'];
            $dokumenNew = $current['dokumen'];

            $uploadErrors = [];
            $tempNewFoto = null;
            $tempNewAudio = null;
            $tempNewDokumen = null;

            // 1. Cek apakah ada FOTO baru diupload
            if (isset($_FILES['foto']) && $_FILES['foto']['error'] !== UPLOAD_ERR_NO_FILE) {
                if ($_FILES['foto']['error'] !== UPLOAD_ERR_OK) {
                    $uploadErrors[] = 'Gagal mengupload foto baru.';
                } elseif ($_FILES['foto']['size'] > 2 * 1024 * 1024) {
                    $uploadErrors[] = 'Ukuran foto baru melebihi 2 MB.';
                } else {
                    $finfo = finfo_open(FILEINFO_MIME_TYPE);
                    $mime = finfo_file($finfo, $_FILES['foto']['tmp_name']);
                    finfo_close($finfo);

                    $allowedMimes = ['image/jpeg', 'image/png', 'image/webp'];
                    $ext = strtolower(pathinfo($_FILES['foto']['name'], PATHINFO_EXTENSION));
                    $allowedExts = ['jpg', 'jpeg', 'png', 'webp'];

                    if (!in_array($mime, $allowedMimes, true) || !in_array($ext, $allowedExts, true)) {
                        $uploadErrors[] = 'Format foto baru harus JPG, PNG, atau WEBP.';
                    } else {
                        $tempNewFoto = 'img_' . time() . '_' . bin2hex(random_bytes(6)) . '.' . $ext;
                    }
                }
            }

            // 2. Cek apakah ada AUDIO baru diupload
            if (empty($uploadErrors) && isset($_FILES['audio']) && $_FILES['audio']['error'] !== UPLOAD_ERR_NO_FILE) {
                if ($_FILES['audio']['error'] !== UPLOAD_ERR_OK) {
                    $uploadErrors[] = 'Gagal mengupload file audio baru.';
                } elseif ($_FILES['audio']['size'] > 5 * 1024 * 1024) {
                    $uploadErrors[] = 'Ukuran file audio baru melebihi 5 MB.';
                } else {
                    $finfo = finfo_open(FILEINFO_MIME_TYPE);
                    $mime = finfo_file($finfo, $_FILES['audio']['tmp_name']);
                    finfo_close($finfo);

                    $allowedAudioMimes = ['audio/mpeg', 'audio/mp3', 'audio/wav', 'audio/x-wav', 'audio/ogg', 'application/ogg'];
                    $ext = strtolower(pathinfo($_FILES['audio']['name'], PATHINFO_EXTENSION));
                    $allowedExts = ['mp3', 'wav', 'ogg'];

                    if (!in_array($mime, $allowedAudioMimes, true) || !in_array($ext, $allowedExts, true)) {
                        $uploadErrors[] = 'Format audio baru harus MP3, WAV, atau OGG.';
                    } else {
                        $tempNewAudio = 'aud_' . time() . '_' . bin2hex(random_bytes(6)) . '.' . $ext;
                    }
                }
            }

            // 3. Cek apakah ada DOKUMEN baru diupload
            if (empty($uploadErrors) && isset($_FILES['dokumen']) && $_FILES['dokumen']['error'] !== UPLOAD_ERR_NO_FILE) {
                if ($_FILES['dokumen']['error'] !== UPLOAD_ERR_OK) {
                    $uploadErrors[] = 'Gagal mengupload dokumen baru.';
                } elseif ($_FILES['dokumen']['size'] > 5 * 1024 * 1024) {
                    $uploadErrors[] = 'Ukuran dokumen baru melebihi 5 MB.';
                } else {
                    $finfo = finfo_open(FILEINFO_MIME_TYPE);
                    $mime = finfo_file($finfo, $_FILES['dokumen']['tmp_name']);
                    finfo_close($finfo);

                    $ext = strtolower(pathinfo($_FILES['dokumen']['name'], PATHINFO_EXTENSION));

                    if ($mime !== 'application/pdf' || $ext !== 'pdf') {
                        $uploadErrors[] = 'Format dokumen baru harus PDF.';
                    } else {
                        $tempNewDokumen = 'doc_' . time() . '_' . bin2hex(random_bytes(6)) . '.pdf';
                    }
                }
            }

            if (!empty($uploadErrors)) {
                $error = implode(' ', $uploadErrors);
            } else {
                $uploadOk = true;
                // Move file baru jika ada
                if ($tempNewFoto) {
                    if (move_uploaded_file($_FILES['foto']['tmp_name'], 'uploads/images/' . $tempNewFoto)) {
                        // Skenario 6: Hapus file lama agar tidak tertinggal
                        if (!empty($current['foto']) && file_exists('uploads/images/' . $current['foto'])) {
                            unlink('uploads/images/' . $current['foto']);
                        }
                        $fotoNew = $tempNewFoto;
                    } else {
                        $uploadOk = false;
                        $error = 'Gagal menyimpan foto baru.';
                    }
                }

                if ($uploadOk && $tempNewAudio) {
                    if (move_uploaded_file($_FILES['audio']['tmp_name'], 'uploads/audio/' . $tempNewAudio)) {
                        if (!empty($current['audio']) && file_exists('uploads/audio/' . $current['audio'])) {
                            unlink('uploads/audio/' . $current['audio']);
                        }
                        $audioNew = $tempNewAudio;
                    } else {
                        $uploadOk = false;
                        $error = 'Gagal menyimpan audio baru.';
                    }
                }

                if ($uploadOk && $tempNewDokumen) {
                    if (move_uploaded_file($_FILES['dokumen']['tmp_name'], 'uploads/documents/' . $tempNewDokumen)) {
                        if (!empty($current['dokumen']) && file_exists('uploads/documents/' . $current['dokumen'])) {
                            unlink('uploads/documents/' . $current['dokumen']);
                        }
                        $dokumenNew = $tempNewDokumen;
                    } else {
                        $uploadOk = false;
                        $error = 'Gagal menyimpan dokumen baru.';
                    }
                }

                if ($uploadOk) {
                    try {
                        $stmtUpdate = $conn->prepare('UPDATE mahasiswa SET nim = ?, nama = ?, program_studi = ?, email = ?, foto = ?, audio = ?, dokumen = ? WHERE id = ?');
                        $stmtUpdate->bind_param('sssssssi', $values['nim'], $values['nama'], $values['program_studi'], $values['email'], $fotoNew, $audioNew, $dokumenNew, $id);
                        $stmtUpdate->execute();
                        $stmtUpdate->close();
                        header('Location: crud_media.php?updated=1');
                        exit;
                    } catch (Throwable $e) {
                        $error = 'Database gagal memperbarui data: ' . $e->getMessage();
                    }
                }
            }
        }
    }
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8"><meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="theme-color" content="#f7f8fc"><title>Edit Media Mahasiswa · WebLab</title><link rel="stylesheet" href="style.css">
</head>
<body class="app-body">
<div class="app-shell">
    <?php app_topbar('Edit Media', 'Perbarui data mahasiswa dan file multimedia', 'crud_media'); ?>
    <div class="app-main">
        <main class="page-content">
            <section class="page-intro">
                <span class="eyebrow">SESI 10 &middot; UPDATE MULTIMEDIA</span>
                <h1>Edit Mahasiswa &amp; Media</h1>
                <p>Ubah data teks atau pilih file baru untuk mengganti file multimedia lama. Jika tidak memilih file baru, media lama akan tetap dipertahankan.</p>
            </section>

            <div class="form-layout">
                <section class="panel-card student-form-panel">
                    <div class="panel-heading">
                        <div class="panel-heading-icon"><?= app_icon('edit') ?></div>
                        <div>
                            <h2>Formulir Perubahan Data &amp; Media</h2>
                            <p>Field bertanda (*) wajib diisi.</p>
                        </div>
                    </div>

                    <?php if ($error): ?>
                        <div class="alert alert-error form-alert" role="alert" id="serverAlert">
                            <?= app_icon('shield') ?><span><?= h($error) ?></span>
                        </div>
                    <?php endif; ?>

                    <div class="alert alert-error form-alert hidden" id="clientAlert" role="alert">
                        <?= app_icon('shield') ?><span id="clientAlertMsg"></span>
                    </div>

                    <form method="POST" action="edit_media.php?id=<?= $id ?>" enctype="multipart/form-data" class="student-form" id="formEditMedia" novalidate>
                        <input type="hidden" name="csrf_token" value="<?= h(csrf_token()) ?>">
                        
                        <div class="form-grid">
                            <div class="form-group">
                                <label for="nim">NIM <span>*</span></label>
                                <input id="nim" name="nim" type="text" maxlength="30" value="<?= h($values['nim']) ?>" placeholder="Contoh: 4112755201250001" required>
                                <small class="field-error" id="err-nim"></small>
                            </div>
                            <div class="form-group">
                                <label for="nama">Nama Lengkap <span>*</span></label>
                                <input id="nama" name="nama" type="text" maxlength="120" value="<?= h($values['nama']) ?>" placeholder="Masukkan nama lengkap" required>
                                <small class="field-error" id="err-nama"></small>
                            </div>
                            <div class="form-group">
                                <label for="program_studi">Program Studi <span>*</span></label>
                                <input id="program_studi" name="program_studi" type="text" maxlength="120" value="<?= h($values['program_studi']) ?>" placeholder="Contoh: Informatika" required>
                                <small class="field-error" id="err-program_studi"></small>
                            </div>
                            <div class="form-group">
                                <label for="email">Email <span>*</span></label>
                                <input id="email" name="email" type="email" maxlength="160" value="<?= h($values['email']) ?>" placeholder="nama@email.com" required>
                                <small class="field-error" id="err-email"></small>
                            </div>
                        </div>

                        <!-- SECTION MEDIA UPLOADS DENGAN STATUS FILE SAAT INI -->
                        <div class="media-upload-section">
                            <h3 class="media-upload-heading"><?= app_icon('image') ?> Kelola File Multimedia</h3>

                            <!-- 1. FOTO -->
                            <div class="media-upload-row">
                                <div class="form-group">
                                    <label for="foto">Foto Mahasiswa (Pilih jika ingin mengganti)</label>
                                    <input type="file" id="foto" name="foto" accept=".jpg,.jpeg,.png,.webp,image/jpeg,image/png,image/webp">
                                    <small class="field-error" id="err-foto"></small>
                                </div>
                                
                                <div class="media-current-and-preview">
                                    <?php if (!empty($current['foto']) && file_exists('uploads/images/' . $current['foto'])): ?>
                                        <div class="current-media-box" id="current-foto-box">
                                            <span class="preview-label">Foto Saat Ini:</span>
                                            <img src="uploads/images/<?= h($current['foto']) ?>" alt="Foto saat ini" class="preview-img-tag">
                                        </div>
                                    <?php endif; ?>
                                    <div class="media-preview-container" id="preview-foto-box" style="display:none;">
                                        <span class="preview-label">Foto Baru Terpilih:</span>
                                        <img id="img-preview" src="#" alt="Preview Foto Baru" class="preview-img-tag">
                                    </div>
                                </div>
                            </div>

                            <!-- 2. AUDIO -->
                            <div class="media-upload-row">
                                <div class="form-group">
                                    <label for="audio">File Audio (Pilih jika ingin mengganti)</label>
                                    <input type="file" id="audio" name="audio" accept=".mp3,.wav,.ogg,audio/*">
                                    <small class="field-error" id="err-audio"></small>
                                </div>
                                <div class="media-current-and-preview">
                                    <?php if (!empty($current['audio']) && file_exists('uploads/audio/' . $current['audio'])): ?>
                                        <div class="current-media-box" id="current-audio-box">
                                            <span class="preview-label">Audio Saat Ini:</span>
                                            <audio controls src="uploads/audio/<?= h($current['audio']) ?>" class="preview-audio-tag"></audio>
                                        </div>
                                    <?php endif; ?>
                                    <div class="media-preview-container" id="preview-audio-box" style="display:none;">
                                        <span class="preview-label">Audio Baru Terpilih:</span>
                                        <audio id="audio-preview" controls class="preview-audio-tag"></audio>
                                    </div>
                                </div>
                            </div>

                            <!-- 3. DOKUMEN PDF -->
                            <div class="media-upload-row">
                                <div class="form-group">
                                    <label for="dokumen">Dokumen PDF (Pilih jika ingin mengganti)</label>
                                    <input type="file" id="dokumen" name="dokumen" accept=".pdf,application/pdf">
                                    <small class="field-error" id="err-dokumen"></small>
                                </div>
                                <div class="media-current-and-preview">
                                    <?php if (!empty($current['dokumen']) && file_exists('uploads/documents/' . $current['dokumen'])): ?>
                                        <div class="current-media-box" id="current-dokumen-box">
                                            <span class="preview-label">Dokumen Saat Ini:</span>
                                            <div class="pdf-preview-box">
                                                <?= app_icon('file') ?>
                                                <span><?= h($current['dokumen']) ?></span>
                                                <a href="uploads/documents/<?= h($current['dokumen']) ?>" target="_blank" class="button button-outline button-small">Buka PDF Lama</a>
                                            </div>
                                        </div>
                                    <?php endif; ?>
                                    <div class="media-preview-container" id="preview-dokumen-box" style="display:none;">
                                        <span class="preview-label">Dokumen Baru Terpilih:</span>
                                        <div class="pdf-preview-box">
                                            <?= app_icon('file') ?>
                                            <span id="doc-filename"></span>
                                            <a id="doc-preview-link" href="#" target="_blank" class="button button-outline button-small">Buka Pratinjau Baru</a>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="form-actions">
                            <a class="button button-outline" href="crud_media.php">Batal</a>
                            <button class="button button-primary" type="submit" id="btnSubmit"><?= app_icon('check') ?> Simpan Perubahan</button>
                        </div>
                    </form>
                </section>

                <aside class="form-side-note">
                    <div class="note-illustration"><?= app_icon('shield') ?></div>
                    <h3>Aturan Skenario 5 &amp; 6</h3>
                    <p><strong>Skenario 5 (UPDATE tanpa file baru):</strong> Ubah data teks saja, biarkan input file kosong. Media lama tetap tersimpan utuh di database dan server.</p>
                    <p><strong>Skenario 6 (UPDATE dengan file baru):</strong> Pilih foto baru dan submit. Foto baru akan aktif dan foto lama di <code>uploads/images/</code> otomatis terhapus bersih.</p>
                </aside>
            </div>
        </main>
        <?php app_footer(); ?>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function () {
    const form = document.getElementById('formEditMedia');
    const clientAlert = document.getElementById('clientAlert');
    const clientAlertMsg = document.getElementById('clientAlertMsg');

    const inputFoto = document.getElementById('foto');
    const inputAudio = document.getElementById('audio');
    const inputDokumen = document.getElementById('dokumen');

    // 1. Preview Foto Baru
    inputFoto.addEventListener('change', function () {
        const file = this.files[0];
        const previewBox = document.getElementById('preview-foto-box');
        const img = document.getElementById('img-preview');
        const err = document.getElementById('err-foto');
        err.textContent = '';

        if (file) {
            const validTypes = ['image/jpeg', 'image/png', 'image/webp'];
            if (!validTypes.includes(file.type)) {
                err.textContent = 'Tipe foto harus JPG, PNG, atau WEBP.';
                this.value = '';
                previewBox.style.display = 'none';
                return;
            }
            if (file.size > 2 * 1024 * 1024) {
                err.textContent = 'Ukuran foto maksimal 2 MB.';
                this.value = '';
                previewBox.style.display = 'none';
                return;
            }
            const reader = new FileReader();
            reader.onload = function (e) {
                img.src = e.target.result;
                previewBox.style.display = 'block';
            };
            reader.readAsDataURL(file);
        } else {
            previewBox.style.display = 'none';
        }
    });

    // 2. Preview Audio Baru
    inputAudio.addEventListener('change', function () {
        const file = this.files[0];
        const previewBox = document.getElementById('preview-audio-box');
        const audio = document.getElementById('audio-preview');
        const err = document.getElementById('err-audio');
        err.textContent = '';

        if (file) {
            const ext = file.name.split('.').pop().toLowerCase();
            const validExts = ['mp3', 'wav', 'ogg'];
            if (!validExts.includes(ext) && !file.type.startsWith('audio/')) {
                err.textContent = 'Tipe audio harus MP3, WAV, atau OGG.';
                this.value = '';
                previewBox.style.display = 'none';
                return;
            }
            if (file.size > 5 * 1024 * 1024) {
                err.textContent = 'Ukuran audio maksimal 5 MB.';
                this.value = '';
                previewBox.style.display = 'none';
                return;
            }
            const url = URL.createObjectURL(file);
            audio.src = url;
            previewBox.style.display = 'block';
        } else {
            previewBox.style.display = 'none';
        }
    });

    // 3. Preview Dokumen Baru
    inputDokumen.addEventListener('change', function () {
        const file = this.files[0];
        const previewBox = document.getElementById('preview-dokumen-box');
        const docName = document.getElementById('doc-filename');
        const docLink = document.getElementById('doc-preview-link');
        const err = document.getElementById('err-dokumen');
        err.textContent = '';

        if (file) {
            if (file.type !== 'application/pdf' && !file.name.toLowerCase().endsWith('.pdf')) {
                err.textContent = 'File dokumen harus berformat PDF.';
                this.value = '';
                previewBox.style.display = 'none';
                return;
            }
            if (file.size > 5 * 1024 * 1024) {
                err.textContent = 'Ukuran dokumen maksimal 5 MB.';
                this.value = '';
                previewBox.style.display = 'none';
                return;
            }
            docName.textContent = file.name + ' (' + (file.size / 1024).toFixed(1) + ' KB)';
            const url = URL.createObjectURL(file);
            docLink.href = url;
            previewBox.style.display = 'block';
        } else {
            previewBox.style.display = 'none';
        }
    });

    // Validasi Client-side
    form.addEventListener('submit', function (e) {
        let hasError = false;
        let errorMessages = [];

        const nim = document.getElementById('nim').value.trim();
        const nama = document.getElementById('nama').value.trim();
        const prodi = document.getElementById('program_studi').value.trim();
        const email = document.getElementById('email').value.trim();

        document.querySelectorAll('.field-error').forEach(el => el.textContent = '');

        if (!nim) {
            document.getElementById('err-nim').textContent = 'NIM wajib diisi.';
            hasError = true;
            errorMessages.push('NIM tidak boleh kosong.');
        }
        if (!nama) {
            document.getElementById('err-nama').textContent = 'Nama lengkap wajib diisi.';
            hasError = true;
            errorMessages.push('Nama tidak boleh kosong.');
        }
        if (!prodi) {
            document.getElementById('err-program_studi').textContent = 'Program studi wajib diisi.';
            hasError = true;
            errorMessages.push('Program studi tidak boleh kosong.');
        }
        if (!email) {
            document.getElementById('err-email').textContent = 'Email wajib diisi.';
            hasError = true;
            errorMessages.push('Email tidak boleh kosong.');
        }

        const fileFoto = inputFoto.files[0];
        if (fileFoto && fileFoto.size > 2 * 1024 * 1024) {
            document.getElementById('err-foto').textContent = 'Foto > 2 MB.';
            hasError = true;
            errorMessages.push('Foto melebihi 2 MB.');
        }

        const fileAudio = inputAudio.files[0];
        if (fileAudio && fileAudio.size > 5 * 1024 * 1024) {
            document.getElementById('err-audio').textContent = 'Audio > 5 MB.';
            hasError = true;
            errorMessages.push('Audio melebihi 5 MB.');
        }

        const fileDokumen = inputDokumen.files[0];
        if (fileDokumen && fileDokumen.size > 5 * 1024 * 1024) {
            document.getElementById('err-dokumen').textContent = 'Dokumen > 5 MB.';
            hasError = true;
            errorMessages.push('Dokumen melebihi 5 MB.');
        }

        if (hasError) {
            e.preventDefault();
            clientAlertMsg.textContent = 'Periksa isian form: ' + errorMessages.join(' | ');
            clientAlert.classList.remove('hidden');
            window.scrollTo({ top: form.offsetTop - 80, behavior: 'smooth' });
        } else {
            clientAlert.classList.add('hidden');
        }
    });
});
</script>
<script src="app.js"></script>
</body>
</html>
