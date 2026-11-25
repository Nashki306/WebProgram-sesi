<?php
require_once 'auth.php';
require_once 'ui.php';
require_once 'koneksi.php';

$id = isset($_POST['id']) ? (int) $_POST['id'] : (isset($_GET['id']) ? (int) $_GET['id'] : 0);
$token = $_POST['csrf_token'] ?? ($_GET['csrf_token'] ?? null);

if ($id <= 0 || !csrf_is_valid($token)) {
    header('Location: data_mahasiswa.php?error=invalid');
    exit;
}

if (!$conn) {
    header('Location: data_mahasiswa.php?error=db');
    exit;
}

$ref = $_POST['ref'] ?? ($_GET['ref'] ?? '');
$target = ($ref === 'crud') ? 'crud_mahasiswa.php' : 'crud_mahasiswa.php';

try {
    $stmt = $conn->prepare('DELETE FROM mahasiswa WHERE id = ?');
    $stmt->bind_param('i', $id);
    $stmt->execute();
    $stmt->close();
    header('Location: ' . $target . '?deleted=1');
    exit;
} catch (Throwable $e) {
    header('Location: ' . $target . '?error=delete');
    exit;
}
