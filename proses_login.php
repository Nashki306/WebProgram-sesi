<?php
if (session_status() !== PHP_SESSION_ACTIVE) {
    session_start();
}
require_once 'koneksi.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: login.php');
    exit;
}
if (!isset($_SESSION['csrf_token']) || !is_string($_POST['csrf_token'] ?? null) || !hash_equals($_SESSION['csrf_token'], $_POST['csrf_token'])) {
    header('Location: login.php?error=3');
    exit;
}
if (!$conn) {
    header('Location: login.php?error=2');
    exit;
}

$username = trim((string) ($_POST['username'] ?? ''));
$password = (string) ($_POST['password'] ?? '');

try {
    $stmt = $conn->prepare('SELECT id, username, password_hash, nama_lengkap FROM users WHERE username = ? LIMIT 1');
    $stmt->bind_param('s', $username);
    $stmt->execute();
    $user = $stmt->get_result()->fetch_assoc();
    $stmt->close();
    if ($user && password_verify($password, $user['password_hash'])) {
        session_regenerate_id(true);
        $_SESSION['user_id'] = $user['id'];
        $_SESSION['username'] = $user['username'];
        $_SESSION['nama_lengkap'] = $user['nama_lengkap'];
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
        header('Location: dashboard.php');
        exit;
    }
} catch (Throwable $exception) {
    header('Location: login.php?error=2');
    exit;
}

header('Location: login.php?error=1');
exit;
