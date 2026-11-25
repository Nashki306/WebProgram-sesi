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
        $error = 'Semua field teks (NIM, Nama, Program Studi, Email) wajib diisi.';
    } elseif (!filter_var($values['email'], FILTER_VALIDATE_EMAIL)) {
        $error = 'Format email belum valid. Periksa kembali alamat email.';
    } elseif (strlen($values['nim']) > 30 || strlen($values['nama']) > 120 || strlen($values['program_studi']) > 120 || strlen($values['email']) > 160) {
        $error = 'Ada data yang terlalu panjang. Periksa kembali isian Anda.';
    } else {
        // Cek keunikan NIM
        $stmtCek = $conn->prepare('SELECT id FROM mahasiswa WHERE nim = ?');
        $stmtCek->bind_param('s', $values['nim']);
        $stmtCek->execute();
        $resCek = $stmtCek->get_result();
        if ($resCek->num_rows > 0) {
            $error = 'NIM ' . h($values['nim']) . ' sudah terdaftar. Gunakan NIM lain.';
            $stmtCek->close();
        } else {
            $stmtCek->close();

            // Skenario 4: Server-side validation untuk file upload
            $fotoFileName = null;
            $audioFileName = null;
            $dokumenFileName = null;
            $uploadErrors = [];

            // 1. Validasi & Proses Foto (JPG/PNG/WEBP <= 2 MB)
            if (isset($_FILES['foto']) && $_FILES['foto']['error'] !== UPLOAD_ERR_NO_FILE) {
                if ($_FILES['foto']['error'] !== UPLOAD_ERR_OK) {
                    $uploadErrors[] = 'Gagal mengupload foto (kode error: ' . $_FILES['foto']['error'] . ').';
                } elseif ($_FILES['foto']['size'] > 2 * 1024 * 1024) {
                    $uploadErrors[] = 'Ukuran file foto melebihi batas maksimal 2 MB.';
                } else {
                    $finfo = finfo_open(FILEINFO_MIME_TYPE);
                    $mime = finfo_file($finfo, $_FILES['foto']['tmp_name']);
                    finfo_close($finfo);

                    $allowedMimes = ['image/jpeg', 'image/png', 'image/webp'];
                    $ext = strtolower(pathinfo($_FILES['foto']['name'], PATHINFO_EXTENSION));
                    $allowedExts = ['jpg', 'jpeg', 'png', 'webp'];

                    if (!in_array($mime, $allowedMimes, true) || !in_array($ext, $allowedExts, true)) {
                        $uploadErrors[] = 'Format foto tidak valid. Hanya diperbolehkan JPG, PNG, atau WEBP.';
                    } else {
                        $fotoFileName = 'img_' . time() . '_' . bin2hex(random_bytes(6)) . '.' . $ext;
                    }
                }
            }

            // 2. Validasi & Proses Audio (MP3/WAV/OGG <= 5 MB)
            if (empty($uploadErrors) && isset($_FILES['audio']) && $_FILES['audio']['error'] !== UPLOAD_ERR_NO_FILE) {
                if ($_FILES['audio']['error'] !== UPLOAD_ERR_OK) {
                    $uploadErrors[] = 'Gagal mengupload file audio.';
                } elseif ($_FILES['audio']['size'] > 5 * 1024 * 1024) {
                    $uploadErrors[] = 'Ukuran file audio melebihi batas maksimal 5 MB.';
                } else {
                    $finfo = finfo_open(FILEINFO_MIME_TYPE);
                    $mime = finfo_file($finfo, $_FILES['audio']['tmp_name']);
                    finfo_close($finfo);

                    $allowedAudioMimes = ['audio/mpeg', 'audio/mp3', 'audio/wav', 'audio/x-wav', 'audio/ogg', 'application/ogg'];
                    $ext = strtolower(pathinfo($_FILES['audio']['name'], PATHINFO_EXTENSION));
                    $allowedExts = ['mp3', 'wav', 'ogg'];

                    if (!in_array($mime, $allowedAudioMimes, true) || !in_array($ext, $allowedExts, true)) {
                        $uploadErrors[] = 'Format audio tidak valid. Hanya diperbolehkan file audio MP3, WAV, atau OGG.';
                    } else {
                        $audioFileName = 'aud_' . time() . '_' . bin2hex(random_bytes(6)) . '.' . $ext;
                    }
                }
            }

            // 3. Validasi & Proses Dokumen (PDF <= 5 MB)
            if (empty($uploadErrors) && isset($_FILES['dokumen']) && $_FILES['dokumen']['error'] !== UPLOAD_ERR_NO_FILE) {
                if ($_FILES['dokumen']['error'] !== UPLOAD_ERR_OK) {
                    $uploadErrors[] = 'Gagal mengupload dokumen.';
                } elseif ($_FILES['dokumen']['size'] > 5 * 1024 * 1024) {
                    $uploadErrors[] = 'Ukuran file dokumen melebihi batas maksimal 5 MB.';
                } else {
                    $finfo = finfo_open(FILEINFO_MIME_TYPE);
                    $mime = finfo_file($finfo, $_FILES['dokumen']['tmp_name']);
                    finfo_close($finfo);

                    $ext = strtolower(pathinfo($_FILES['dokumen']['name'], PATHINFO_EXTENSION));

                    if ($mime !== 'application/pdf' || $ext !== 'pdf') {
                        $uploadErrors[] = 'Format dokumen tidak valid. Hanya diperbolehkan file PDF.';
                    } else {
                        $dokumenFileName = 'doc_' . time() . '_' . bin2hex(random_bytes(6)) . '.pdf';
                    }
                }
            }

            if (!empty($uploadErrors)) {
                $error = implode(' ', $uploadErrors);
            } else {
                // Pindahkan file ke target directory
                $uploadSuccess = true;
                if ($fotoFileName && !move_uploaded_file($_FILES['foto']['tmp_name'], 'uploads/images/' . $fotoFileName)) {
                    $uploadSuccess = false;
                    $error = 'Gagal memindahkan file foto ke direktori uploads/images/.';
                }
                if ($uploadSuccess && $audioFileName && !move_uploaded_file($_FILES['audio']['tmp_name'], 'uploads/audio/' . $audioFileName)) {
                    $uploadSuccess = false;
                    $error = 'Gagal memindahkan file audio ke direktori uploads/audio/.';
                    if ($fotoFileName && file_exists('uploads/images/' . $fotoFileName)) unlink('uploads/images/' . $fotoFileName);
                }
                if ($uploadSuccess && $dokumenFileName && !move_uploaded_file($_FILES['dokumen']['tmp_name'], 'uploads/documents/' . $dokumenFileName)) {
                    $uploadSuccess = false;
                    $error = 'Gagal memindahkan file dokumen ke direktori uploads/documents/.';
                    if ($fotoFileName && file_exists('uploads/images/' . $fotoFileName)) unlink('uploads/images/' . $fotoFileName);
                    if ($audioFileName && file_exists('uploads/audio/' . $audioFileName)) unlink('uploads/audio/' . $audioFileName);
                }

                if ($uploadSuccess) {
                    try {
                        $stmt = $conn->prepare('INSERT INTO mahasiswa (nim, nama, program_studi, email, foto, audio, dokumen) VALUES (?, ?, ?, ?, ?, ?, ?)');
                        $stmt->bind_param('sssssss', $values['nim'], $values['nama'], $values['program_studi'], $values['email'], $fotoFileName, $audioFileName, $dokumenFileName);
                        $stmt->execute();
                        $stmt->close();
                        header('Location: crud_media.php?created=1');
                        exit;
                    } catch (Throwable $e) {
                        // Bersihkan file upload jika insert database gagal
                        if ($fotoFileName && file_exists('uploads/images/' . $fotoFileName)) unlink('uploads/images/' . $fotoFileName);
                        if ($audioFileName && file_exists('uploads/audio/' . $audioFileName)) unlink('uploads/audio/' . $audioFileName);
                        if ($dokumenFileName && file_exists('uploads/documents/' . $dokumenFileName)) unlink('uploads/documents/' . $dokumenFileName);
                        $error = 'Database gagal menyimpan data: ' . $e->getMessage();
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
    <meta name="theme-color" content="#f7f8fc"><title>Tambah Media Mahasiswa · WebLab</title><link rel="stylesheet" href="style.css">
</head>
<body class="app-body">
<div class="app-shell">
    <?php app_topbar('Tambah Media', 'Input data mahasiswa lengkap dengan upload foto, audio, dan dokumen', 'crud_media'); ?>
    <div class="app-main">
        <main class="page-content">
            <section class="page-intro">
                <span class="eyebrow">SESI 10 &middot; CREATE MULTIMEDIA</span>
                <h1>Tambah Mahasiswa &amp; Media</h1>
                <p>Isi data teks mahasiswa dan pilih file multimedia (Foto, Audio, PDF). Tinjau preview media sebelum menekan Simpan.</p>
            </section>

            <div class="form-layout">
                <section class="panel-card student-form-panel">
                    <div class="panel-heading">
                        <div class="panel-heading-icon"><?= app_icon('plus') ?></div>
                        <div>
                            <h2>Formulir Data &amp; Multimedia</h2>
                            <p>Field bertanda (*) wajib diisi. Upload file opsional sesuai skenario.</p>
                        </div>
                    </div>

                    <?php if ($error): ?>
                        <div class="alert alert-error form-alert" role="alert" id="serverAlert">
                            <?= app_icon('shield') ?><span><?= h($error) ?></span>
                        </div>
                    <?php endif; ?>

                    <!-- Tempat alert validasi client-side JavaScript -->
                    <div class="alert alert-error form-alert hidden" id="clientAlert" role="alert">
                        <?= app_icon('shield') ?><span id="clientAlertMsg"></span>
                    </div>

                    <form method="POST" action="form_media.php" enctype="multipart/form-data" class="student-form" id="formMedia" novalidate>
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

                        <!-- SECTION MEDIA UPLOADS -->
                        <div class="media-upload-section">
                            <h3 class="media-upload-heading"><?= app_icon('image') ?> Upload File Multimedia</h3>
                            
                            <!-- 1. FOTO -->
                            <div class="media-upload-row">
                                <div class="form-group">
                                    <label for="foto">Foto Mahasiswa (JPG, PNG, WEBP &le; 2 MB)</label>
                                    <input type="file" id="foto" name="foto" accept=".jpg,.jpeg,.png,.webp,image/jpeg,image/png,image/webp">
                                    <small class="field-error" id="err-foto"></small>
                                </div>
                                <div class="media-preview-container" id="preview-foto-box" style="display:none;">
                                    <span class="preview-label">Preview Foto:</span>
                                    <img id="img-preview" src="#" alt="Preview Foto" class="preview-img-tag">
                                </div>
                            </div>

                            <!-- 2. AUDIO -->
                            <div class="media-upload-row">
                                <div class="form-group">
                                    <label for="audio">File Audio (MP3, WAV, OGG &le; 5 MB)</label>
                                    <input type="file" id="audio" name="audio" accept=".mp3,.wav,.ogg,audio/*">
                                    <small class="field-error" id="err-audio"></small>
                                </div>
                                <div class="media-preview-container" id="preview-audio-box" style="display:none;">
                                    <span class="preview-label">Preview Audio:</span>
                                    <audio id="audio-preview" controls class="preview-audio-tag"></audio>
                                </div>
                            </div>

                            <!-- 3. DOKUMEN PDF -->
                            <div class="media-upload-row">
                                <div class="form-group">
                                    <label for="dokumen">Dokumen PDF (PDF &le; 5 MB)</label>
                                    <input type="file" id="dokumen" name="dokumen" accept=".pdf,application/pdf">
                                    <small class="field-error" id="err-dokumen"></small>
                                </div>
                                <div class="media-preview-container" id="preview-dokumen-box" style="display:none;">
                                    <span class="preview-label">Preview Dokumen:</span>
                                    <div class="pdf-preview-box">
                                        <?= app_icon('file') ?>
                                        <span id="doc-filename"></span>
                                        <a id="doc-preview-link" href="#" target="_blank" class="button button-outline button-small">Buka Pratinjau</a>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="form-actions">
                            <a class="button button-outline" href="crud_media.php">Batal</a>
                            <button class="button button-primary" type="submit" id="btnSubmit"><?= app_icon('check') ?> Simpan Data &amp; Media</button>
                        </div>
                    </form>
                </section>

                <aside class="form-side-note">
                    <div class="note-illustration"><?= app_icon('shield') ?></div>
                    <h3>Panduan Skenario Praktikum</h3>
                    <p>Sistem ini mengimplementasikan validasi ganda (Client-Side &amp; Server-Side):</p>
                    <ul>
                        <li><strong>Skenario 1 (CREATE Minimal):</strong> Isi teks + Foto &le; 2 MB. Audio &amp; Dokumen boleh kosong.</li>
                        <li><strong>Skenario 2 (CREATE Lengkap):</strong> Isi teks + Foto + Audio + PDF. Cek preview sebelum simpan.</li>
                        <li><strong>Skenario 3 (Client-side):</strong> JavaScript langsung mencegat jika field kosong atau file &gt; batas ukuran.</li>
                        <li><strong>Skenario 4 (Server-side):</strong> Jika JS dimatikan, PHP tetap memvalidasi MIME type, ekstensi &amp; ukuran file.</li>
                    </ul>
                </aside>
            </div>
        </main>
        <?php app_footer(); ?>
    </div>
</div>

<script>
// Skenario 2 & 3: Client-side preview & validation
document.addEventListener('DOMContentLoaded', function () {
    const form = document.getElementById('formMedia');
    const clientAlert = document.getElementById('clientAlert');
    const clientAlertMsg = document.getElementById('clientAlertMsg');

    const inputFoto = document.getElementById('foto');
    const inputAudio = document.getElementById('audio');
    const inputDokumen = document.getElementById('dokumen');

    // 1. Live Preview Foto
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
                err.textContent = 'Ukuran foto maksimal 2 MB (file Anda: ' + (file.size / (1024*1024)).toFixed(2) + ' MB).';
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

    // 2. Live Preview Audio
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
                err.textContent = 'Ukuran file audio maksimal 5 MB (file Anda: ' + (file.size / (1024*1024)).toFixed(2) + ' MB).';
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

    // 3. Live Preview Dokumen PDF
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
                err.textContent = 'Ukuran file dokumen maksimal 5 MB (file Anda: ' + (file.size / (1024*1024)).toFixed(2) + ' MB).';
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

    // Skenario 3: Client-side validation saat submit
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
        } else {
            const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
            if (!emailRegex.test(email)) {
                document.getElementById('err-email').textContent = 'Format email tidak valid.';
                hasError = true;
                errorMessages.push('Format email salah.');
            }
        }

        // Cek file foto jika dipilih
        const fileFoto = inputFoto.files[0];
        if (fileFoto && fileFoto.size > 2 * 1024 * 1024) {
            document.getElementById('err-foto').textContent = 'Ukuran foto melebihi 2 MB.';
            hasError = true;
            errorMessages.push('Foto terlalu besar (> 2 MB).');
        }

        // Cek file audio jika dipilih
        const fileAudio = inputAudio.files[0];
        if (fileAudio && fileAudio.size > 5 * 1024 * 1024) {
            document.getElementById('err-audio').textContent = 'Ukuran audio melebihi 5 MB.';
            hasError = true;
            errorMessages.push('Audio terlalu besar (> 5 MB).');
        }

        // Cek file dokumen jika dipilih
        const fileDokumen = inputDokumen.files[0];
        if (fileDokumen && fileDokumen.size > 5 * 1024 * 1024) {
            document.getElementById('err-dokumen').textContent = 'Ukuran dokumen melebihi 5 MB.';
            hasError = true;
            errorMessages.push('Dokumen terlalu besar (> 5 MB).');
        }

        if (hasError) {
            e.preventDefault();
            clientAlertMsg.textContent = 'Gagal mengirim form: ' + errorMessages.join(' | ');
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
