<?php
require_once 'auth.php';
require_once 'ui.php';
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8"><meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="theme-color" content="#f7f8fc"><title>Biodata Diri · WebLab</title><link rel="stylesheet" href="style.css">
</head>
<body class="app-body">
<div class="app-shell">
    <?php app_topbar('Biodata Diri', 'Profil pribadi dan perjalanan belajar'); ?>
    <div class="app-main">
        <main class="page-content">
            <section class="profile-hero">
                <div class="profile-photo-wrap"><img src="sendiri2.jpeg" alt="Foto profil Ikhsan Darmawan Saputra" class="profile-photo"><span class="profile-online" title="Profil mahasiswa"></span></div>
                <div class="profile-hero-copy"><span class="eyebrow">STUDENT PROFILE</span><h1>Ikhsan Darmawan Saputra</h1><p>Mahasiswa Informatika yang tertarik pada desain antarmuka, pengalaman pengguna, dan pengembangan web.</p><div class="profile-tags"><span class="tag tag-primary">Informatika</span><span class="tag">Universitas Horizon</span><span class="tag">Web Programming 1</span></div></div>
                <a class="button button-outline profile-social" href="https://www.instagram.com/iksaan.saputra_/" target="_blank" rel="noopener noreferrer">Instagram <?= app_icon('external') ?></a>
            </section>

            <div class="content-grid profile-grid">
                <section class="panel-card span-7">
                    <div class="panel-heading"><div class="panel-heading-icon"><?= app_icon('user') ?></div><div><h2>Informasi pribadi</h2><p>Ringkasan data diri</p></div></div>
                    <dl class="detail-list">
                        <div><dt>Nama lengkap</dt><dd>Ikhsan Darmawan Saputra</dd></div>
                        <div><dt>NIM</dt><dd>4112755201250154</dd></div>
                        <div><dt>Program studi</dt><dd>Informatika</dd></div>
                        <div><dt>Tempat, tanggal lahir</dt><dd>Karawang, 30 Oktober 2006</dd></div>
                        <div><dt>Alamat</dt><dd>Bubulak, RT/RW 006/008</dd></div>
                        <div><dt>Email</dt><dd><a href="mailto:Ikhsandarsan30@gmail.com">Ikhsandarsan30@gmail.com</a></dd></div>
                        <div><dt>Telepon</dt><dd>089676543456</dd></div>
                    </dl>
                </section>
                <section class="panel-card span-5">
                    <div class="panel-heading"><div class="panel-heading-icon icon-blue"><?= app_icon('user') ?></div><div><h2>Tentang saya</h2><p>Minat dan tujuan</p></div></div>
                    <p class="body-copy">Halo! Saya mahasiswa Informatika di Universitas Horizon. Saya tertarik pada UI/UX, desain, dan hal-hal yang berkaitan dengan kreativitas visual.</p>
                    <p class="body-copy">Saya senang mengeksplorasi bagaimana desain dapat membuat produk digital terasa lebih menarik, nyaman, dan mudah digunakan. Saat ini saya juga mempelajari dasar pengembangan website agar dapat memadukan desain dengan implementasi yang baik.</p>
                    <div class="mini-callout"><span><?= app_icon('target') ?></span><p><strong>Tujuan saya</strong><br>Mengembangkan kemampuan UI/UX dan web development secara bertahap.</p></div>
                </section>
                <section class="panel-card span-6">
                    <div class="panel-heading"><div class="panel-heading-icon icon-green"><?= app_icon('book') ?></div><div><h2>Riwayat pendidikan</h2><p>Perjalanan akademik</p></div></div>
                    <ol class="timeline-list">
                        <li><span class="timeline-dot"></span><div><strong>S1 Informatika</strong><small>Universitas Horizon</small></div><span class="timeline-current">Saat ini</span></li>
                        <li><span class="timeline-dot"></span><div><strong>SMA Negeri 3 Karawang</strong><small>Pendidikan menengah atas</small></div></li>
                        <li><span class="timeline-dot"></span><div><strong>SMP Negeri 6 Karawang</strong><small>Pendidikan menengah pertama</small></div></li>
                        <li><span class="timeline-dot"></span><div><strong>SDN KarangPawitan 1</strong><small>Pendidikan dasar</small></div></li>
                    </ol>
                </section>
                <section class="panel-card span-6">
                    <div class="panel-heading"><div class="panel-heading-icon icon-orange"><?= app_icon('target') ?></div><div><h2>Keahlian & minat</h2><p>Hal yang saya pelajari dan sukai</p></div></div>
                    <h3 class="small-section-title">Keahlian</h3><div class="chip-list"><span>Wireframing & Prototype</span><span>Microsoft Office</span><span>UI/UX Design</span><span>HTML & CSS</span></div>
                    <h3 class="small-section-title hobbies-title">Hobi</h3><div class="chip-list chip-muted"><span>Bermain game</span><span>Berenang</span><span>Badminton</span><span>Menggambar</span><span>Membaca</span><span>Tenis meja</span></div>
                    <div class="social-row"><span class="social-symbol">@</span><div><strong>@iksaan.saputra_</strong><small>Instagram</small></div><a href="https://www.instagram.com/iksaan.saputra_/" target="_blank" rel="noopener noreferrer" aria-label="Buka Instagram"><?= app_icon('external') ?></a></div>
                </section>
            </div>
        </main>
        <?php app_footer(); ?>
    </div>
</div>
<script src="app.js"></script>
</body>
</html>
