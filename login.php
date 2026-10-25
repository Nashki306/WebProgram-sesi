<?php
if (session_status() !== PHP_SESSION_ACTIVE) {
    session_start();
}
if (!empty($_SESSION['user_id'])) {
    header('Location: dashboard.php');
    exit;
}
require_once 'ui.php';
$error = isset($_GET['error']) ? (int) $_GET['error'] : 0;
$loggedOut = isset($_GET['logout']);
$setupDone = isset($_GET['setup']);
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Silahkan Login</title>
    <link rel="stylesheet" href="style.css?v=<?= time() ?>">
    <style>
        /* Fallback inline style untuk memastikan tampilan 100% presisi dan tidak terhalang cache browser */
        body.box-login-body {
            min-height: 100vh;
            margin: 0;
            display: flex;
            align-items: center;
            justify-content: center;
            background-color: #d2eff4;
            background: #d2eff4;
            font-family: Arial, Helvetica, sans-serif;
            padding: 20px;
            box-sizing: border-box;
        }
        .box-login-card {
            background: #ffffff;
            width: 100%;
            max-width: 360px;
            padding: 40px 32px 35px;
            border-radius: 2px;
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.05);
            box-sizing: border-box;
        }
        .box-login-title {
            margin: 0 0 25px 0;
            font-size: 16px;
            font-weight: 700;
            color: #1e1e1e;
            text-align: center;
            letter-spacing: 0.5px;
        }
        .box-login-alert {
            padding: 9px 12px;
            margin-bottom: 16px;
            font-size: 13px;
            border-radius: 3px;
            text-align: center;
        }
        .box-login-alert.alert-danger {
            background: #fde8e8;
            color: #c81e1e;
            border: 1px solid #f8b4b4;
        }
        .box-login-alert.alert-success {
            background: #def7ec;
            color: #03543f;
            border: 1px solid #bcf0da;
        }
        .box-login-form {
            display: flex;
            flex-direction: column;
        }
        .box-form-group {
            margin-bottom: 16px;
            display: flex;
            flex-direction: column;
        }
        .box-form-group label {
            font-size: 13px;
            font-weight: 600;
            color: #333333;
            margin-bottom: 6px;
            text-align: left;
        }
        .box-form-group input {
            width: 100%;
            height: 42px;
            padding: 0 12px;
            border: 1px solid #e1e3e6;
            border-radius: 2px;
            font-size: 13px;
            color: #333333;
            outline: none;
            box-sizing: border-box;
            background: #ffffff;
        }
        .box-form-group input::placeholder {
            color: #9ea5b0;
            font-size: 13px;
        }
        .box-form-group input:focus {
            border-color: #32d04a;
        }
        .box-login-btn {
            width: 100%;
            height: 42px;
            background-color: #32d04a;
            color: #ffffff;
            border: none;
            border-radius: 3px;
            font-size: 14px;
            font-weight: 700;
            cursor: pointer;
            margin-top: 6px;
            letter-spacing: 0.5px;
            display: block;
        }
        .box-login-btn:hover {
            background-color: #2bc042;
        }
        .box-login-links {
            margin-top: 20px;
            text-align: center;
        }
        .box-link-back,
        .box-link-register {
            font-size: 13px;
            color: #333333;
            text-decoration: none;
        }
        .box-link-back:hover,
        .box-link-register:hover {
            text-decoration: underline;
        }
        .box-link-sep {
            color: #999999;
            margin: 0 5px;
        }
    </style>
</head>
<body class="box-login-body">
    <div class="box-login-card">
        <h2 class="box-login-title">SILAHKAN LOGIN</h2>

        <?php if ($error === 1): ?>
            <div class="box-login-alert alert-danger">Username atau password salah.</div>
        <?php elseif ($error === 2): ?>
            <div class="box-login-alert alert-danger">Koneksi database bermasalah.</div>
        <?php elseif ($error === 3): ?>
            <div class="box-login-alert alert-danger">Permintaan tidak valid. Silakan coba lagi.</div>
        <?php endif; ?>

        <?php if ($loggedOut): ?>
            <div class="box-login-alert alert-success">Anda telah berhasil keluar.</div>
        <?php elseif ($setupDone): ?>
            <div class="box-login-alert alert-success">Akun berhasil dibuat. Silakan login.</div>
        <?php endif; ?>

        <form action="proses_login.php" method="POST" class="box-login-form">
            <input type="hidden" name="csrf_token" value="<?= h(csrf_token()) ?>">

            <div class="box-form-group">
                <label for="username">Username</label>
                <input type="text" id="username" name="username" placeholder="Username atau email .." autocomplete="username" required autofocus>
            </div>

            <div class="box-form-group">
                <label for="password">Password</label>
                <input type="password" id="password" name="password" placeholder="Password .." autocomplete="current-password" required>
            </div>

            <button type="submit" class="box-login-btn">LOGIN</button>

            <div class="box-login-links">
                <a href="index.php" class="box-link-back">kembali</a>
                <span class="box-link-sep">•</span>
                <a href="buat_user.php" class="box-link-register">buat akun</a>
            </div>
        </form>
    </div>
</body>
</html>
