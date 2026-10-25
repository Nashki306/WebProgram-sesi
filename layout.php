<?php
require_once 'auth.php';
require_once 'ui.php';
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8"><meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="theme-color" content="#f7f8fc"><title>Layout Lab · WebLab</title><link rel="stylesheet" href="style.css">
</head>
<body class="app-body">
<div class="app-shell">
    <?php app_topbar('Layout Lab', 'Eksplorasi struktur halaman dan antarmuka'); ?>
    <div class="app-main">
        <main class="page-content">
            <section class="page-intro"><span class="eyebrow">DESIGN & DEVELOPMENT</span><h1>Layout Lab</h1><p>Area eksperimen untuk memahami bagaimana struktur, jarak, tipografi, dan komponen visual bekerja bersama untuk menciptakan halaman yang nyaman digunakan.</p></section>
            <div class="section-heading section-heading-spaced"><div><span class="eyebrow">CORE SKILLS</span><h2>Hal yang sedang dikembangkan</h2><p>Dasar-dasar untuk membangun antarmuka yang terstruktur.</p></div></div>
            <section class="learning-grid">
                <article class="learning-card"><span class="learning-icon icon-purple"><?= app_icon('layout') ?></span><h3>HTML terstruktur</h3><p>Mengatur konten dengan elemen yang sesuai agar mudah dipahami dan dirawat.</p><div class="learning-progress"><span style="width:72%"></span></div><small>Struktur halaman</small></article>
                <article class="learning-card"><span class="learning-icon icon-blue"><?= app_icon('layout') ?></span><h3>CSS & visual design</h3><p>Menerapkan warna, tipografi, jarak, dan komponen yang konsisten.</p><div class="learning-progress"><span style="width:62%"></span></div><small>Styling antarmuka</small></article>
                <article class="learning-card"><span class="learning-icon icon-green"><?= app_icon('target') ?></span><h3>Responsive layout</h3><p>Menyesuaikan tampilan agar tetap nyaman di desktop, tablet, maupun ponsel.</p><div class="learning-progress"><span style="width:48%"></span></div><small>Adaptasi berbagai layar</small></article>
            </section>
            <section class="panel-card principle-panel"><div class="panel-heading"><div class="panel-heading-icon icon-orange"><?= app_icon('check') ?></div><div><h2>Prinsip layout yang digunakan</h2><p>Pedoman sederhana untuk menjaga kualitas tampilan</p></div></div><div class="principle-grid"><div><strong>01 · Konsistensi</strong><p>Gunakan pola navigasi, warna, dan komponen yang sama pada seluruh halaman.</p></div><div><strong>02 · Hierarki visual</strong><p>Bedakan judul, deskripsi, dan aksi utama agar mudah dipindai.</p></div><div><strong>03 · Ruang yang cukup</strong><p>Atur padding dan jarak agar konten tidak terasa sesak atau berantakan.</p></div><div><strong>04 · Responsif</strong><p>Pastikan konten dan navigasi tetap bisa diakses pada layar kecil.</p></div></div></section>
        </main>
        <?php app_footer(); ?>
    </div>
</div>
<script src="app.js"></script>
</body>
</html>
