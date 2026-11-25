<?php
if (session_status() !== PHP_SESSION_ACTIVE) {
    session_start();
}

function h($value): string
{
    return htmlspecialchars((string) $value, ENT_QUOTES, 'UTF-8');
}

function csrf_token(): string
{
    if (empty($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['csrf_token'];
}

function csrf_is_valid($token): bool
{
    return is_string($token) && isset($_SESSION['csrf_token']) && hash_equals($_SESSION['csrf_token'], $token);
}

function app_icon(string $name, string $class = ''): string
{
    $paths = [
        'dashboard' => '<rect x="3" y="3" width="7" height="7" rx="1.5"/><rect x="14" y="3" width="7" height="5" rx="1.5"/><rect x="14" y="11" width="7" height="10" rx="1.5"/><rect x="3" y="13" width="7" height="8" rx="1.5"/>',
        'user' => '<circle cx="12" cy="8" r="3.5"/><path d="M5 20a7 7 0 0 1 14 0"/>',
        'layout' => '<rect x="3" y="4" width="18" height="16" rx="2"/><path d="M9 4v16M9 10h12"/>',
        'database' => '<ellipse cx="12" cy="5" rx="8" ry="3"/><path d="M4 5v14c0 1.7 3.6 3 8 3s8-1.3 8-3V5M4 12c0 1.7 3.6 3 8 3s8-1.3 8-3"/>',
        'plus' => '<path d="M12 5v14M5 12h14"/>',
        'logout' => '<path d="M10 17l5-5-5-5M15 12H3"/><path d="M12 3h6a2 2 0 0 1 2 2v14a2 2 0 0 1-2 2h-6"/>',
        'arrow' => '<path d="M5 12h14M13 6l6 6-6 6"/>',
        'book' => '<path d="M4 5.5A2.5 2.5 0 0 1 6.5 3H20v17H6.5A2.5 2.5 0 0 1 4 17.5z"/><path d="M4 17.5A2.5 2.5 0 0 1 6.5 15H20M8 7h7M8 10h7"/>',
        'target' => '<circle cx="12" cy="12" r="9"/><circle cx="12" cy="12" r="5"/><circle cx="12" cy="12" r="1.5"/>',
        'mail' => '<rect x="3" y="5" width="18" height="14" rx="2"/><path d="m4 7 8 6 8-6"/>',
        'search' => '<circle cx="10.8" cy="10.8" r="6.8"/><path d="m16 16 4.5 4.5"/>',
        'menu' => '<path d="M4 6h16M4 12h16M4 18h16"/>',
        'check' => '<path d="m5 12 4 4L19 6"/>',
        'shield' => '<path d="M12 22s8-4 8-11V5l-8-3-8 3v6c0 7 8 11 8 11Z"/><path d="m9 12 2 2 4-4"/>',
        'external' => '<path d="M14 3h7v7M10 14 21 3"/><path d="M18 13v6a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V8a2 2 0 0 1 2-2h6"/>',
        'calendar' => '<rect x="3" y="5" width="18" height="16" rx="2"/><path d="M16 3v4M8 3v4M3 10h18"/>',
        'edit' => '<path d="m14 4 6 6-9 9H5v-6l9-9z"/><path d="M5 21h14"/>',
        'trash' => '<path d="M4 7h16"/><path d="M9 7V4a1 1 0 0 1 1-1h4a1 1 0 0 1 1 1v3"/><path d="M6 7l1 13a2 2 0 0 0 2 2h6a2 2 0 0 0 2-2l1-13"/><line x1="10" y1="11" x2="10" y2="17"/><line x1="14" y1="11" x2="14" y2="17"/>',
        'image' => '<rect x="3" y="3" width="18" height="18" rx="2" ry="2"/><circle cx="8.5" cy="8.5" r="1.5"/><polyline points="21 15 16 10 5 21"/>',
        'music' => '<path d="M9 18V5l12-2v13"/><circle cx="6" cy="18" r="3"/><circle cx="18" cy="16" r="3"/>',
        'file' => '<path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/><polyline points="14 2 14 8 20 8"/>',
        'play' => '<polygon points="5 3 19 12 5 21 5 3"/>',
    ];
    $content = $paths[$name] ?? ($paths['layout'] ?? '');
    $classAttr = $class !== '' ? ' class="' . h($class) . '"' : '';
    return '<svg' . $classAttr . ' viewBox="0 0 24 24" width="20" height="20" fill="none" stroke="currentColor" stroke-width="1.7" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">' . $content . '</svg>';
}

function app_sidebar(string $active): void
{
    // Kompatibilitas jika dipanggil; navigasi kini berada di app_topbar
}

function app_topbar(string $title, string $subtitle, string $active = ''): void
{
    $items = [
        ['key' => 'dashboard', 'label' => 'Dashboard', 'href' => 'dashboard.php'],
        ['key' => 'biodata', 'label' => 'Biodata', 'href' => 'index.php'],
        ['key' => 'layout', 'label' => 'Layout Lab', 'href' => 'layout.php'],
        ['key' => 'mahasiswa', 'label' => 'Data Mahasiswa', 'href' => 'data_mahasiswa.php'],
        ['key' => 'tambah', 'label' => 'Tambah Mahasiswa', 'href' => 'form_mahasiswa.php'],
        ['key' => 'crud_mahasiswa', 'label' => 'CRUD Mahasiswa', 'href' => 'crud_mahasiswa.php'],
        ['key' => 'crud_media', 'label' => 'CRUD Media', 'href' => 'crud_media.php'],
    ];

    $currentPage = basename($_SERVER['SCRIPT_NAME'] ?? '');
    if ($active === '') {
        $pageMap = [
            'dashboard.php' => 'dashboard',
            'index.php' => 'biodata',
            'layout.php' => 'layout',
            'data_mahasiswa.php' => 'mahasiswa',
            'form_mahasiswa.php' => 'tambah',
            'crud_mahasiswa.php' => 'crud_mahasiswa',
            'crud_media.php' => 'crud_media',
            'form_media.php' => 'crud_media',
            'edit_media.php' => 'crud_media',
        ];
        $active = $pageMap[$currentPage] ?? '';
    }

    $nama = $_SESSION['nama_lengkap'] ?? 'Pengguna';
    $initial = function_exists('mb_substr') ? mb_strtoupper(mb_substr($nama, 0, 1, 'UTF-8'), 'UTF-8') : strtoupper(substr($nama, 0, 1));
    ?>
    <header class="topbar">
        <div class="topbar-container">
            <nav class="topbar-nav" id="topNav" aria-label="Navigasi utama">
                <?php foreach ($items as $item): ?>
                    <a class="top-nav-link <?= $active === $item['key'] ? 'active' : '' ?>" href="<?= h($item['href']) ?>" <?= $active === $item['key'] ? 'aria-current="page"' : '' ?>>
                        <?= h($item['label']) ?>
                    </a>
                <?php endforeach; ?>
            </nav>

            <div class="topbar-user-area">
                <a class="button-logout" href="logout.php" title="Keluar" aria-label="Keluar dari akun">
                    <?= app_icon('logout') ?>
                    <span>Keluar</span>
                </a>
                <button class="icon-button nav-toggle" data-nav-toggle aria-label="Menu navigasi" aria-expanded="false">
                    <?= app_icon('menu') ?>
                </button>
            </div>
        </div>
    </header>
    <?php
}

function app_footer(): void
{
    ?>
    <footer class="app-footer"><span>© <?= date('Y') ?> WebLab</span><span>Dirancang untuk belajar dan berkembang <span class="footer-dot">•</span></span></footer>
    <?php
}
?>
