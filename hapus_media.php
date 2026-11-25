<?php
require_once 'auth.php';
require_once 'ui.php';
require_once 'koneksi.php';

$id = isset($_POST['id']) ? (int) $_POST['id'] : (isset($_GET['id']) ? (int) $_GET['id'] : 0);
$token = $_POST['csrf_token'] ?? ($_GET['csrf_token'] ?? null);

if ($id <= 0 || !csrf_is_valid($token)) {
    header('Location: crud_media.php?error=invalid');
    exit;
}

if (!$conn) {
    header('Location: crud_media.php?error=db');
    exit;
}

// Skenario 7 - DELETE: Ambil nama file terkait untuk dihapus fisiknya dari server
$stmtSelect = $conn->prepare('SELECT foto, audio, dokumen FROM mahasiswa WHERE id = ?');
$stmtSelect->bind_param('i', $id);
$stmtSelect->execute();
$media = $stmtSelect->get_result()->fetch_assoc();
$stmtSelect->close();

if ($media) {
    // 1. Hapus record dari database
    try {
        $stmtDelete = $conn->prepare('DELETE FROM mahasiswa WHERE id = ?');
        $stmtDelete->bind_param('i', $id);
        $stmtDelete->execute();
        $stmtDelete->close();

        // 2. Pastikan file image/audio/document terkait ikut terhapus dari folder upload
        if (!empty($media['foto']) && file_exists('uploads/images/' . $media['foto'])) {
            unlink('uploads/images/' . $media['foto']);
        }
        if (!empty($media['audio']) && file_exists('uploads/audio/' . $media['audio'])) {
            unlink('uploads/audio/' . $media['audio']);
        }
        if (!empty($media['dokumen']) && file_exists('uploads/documents/' . $media['dokumen'])) {
            unlink('uploads/documents/' . $media['dokumen']);
        }

        header('Location: crud_media.php?deleted=1');
        exit;
    } catch (Throwable $e) {
        header('Location: crud_media.php?error=delete');
        exit;
    }
} else {
    header('Location: crud_media.php?error=notfound');
    exit;
}
