<?php
/**
 * includes/site_header.php
 * Header publik (dipakai oleh index.php, news/index.php, news/detail.php).
 * Seluruh teks, warna, dan menu dibaca dari pengaturan yang dikelola di
 * Admin > Pengaturan Situs & Konten Halaman (lihat includes/settings.php).
 *
 * Variabel opsional yang bisa di-set sebelum include file ini:
 *   $pageTitle       (string) judul tab browser
 *   $pageDescription (string) meta description
 *   $navActive       (string) 'home' di beranda, atau slug menu aktif, mis. 'news'
 *   $canonicalUrl    (string) URL kanonis halaman (default: URL saat ini tanpa query)
 *   $ogImage         (string) gambar pratinjau sosial khusus halaman
 *   $ogType          (string) 'website' (default) atau 'article'
 *   $noIndex         (bool)   true untuk halaman 404 / yang tidak perlu diindeks
 *   $preloadImage    (string) URL gambar LCP yang perlu di-preload (hero di beranda)
 *   $extraJsonLd     (array)  daftar data terstruktur tambahan (mis. NewsArticle di detail berita)
 */
$pageTitle       = $pageTitle       ?? setting('site.home_title');
$pageDescription = $pageDescription ?? setting('site.description');
$navActive       = $navActive       ?? '';
$ogType          = $ogType          ?? 'website';
$noIndex         = $noIndex         ?? false;
$isHome          = ($navActive === 'home');

$origin = preg_replace('#^(https?://[^/]+).*$#i', '$1', BASE_URL);
if (!isset($canonicalUrl)) {
    $path = (string) parse_url($_SERVER['REQUEST_URI'] ?? '/', PHP_URL_PATH);
    $path = preg_replace('#/index\.php$#', '/', $path);
    $canonicalUrl = $origin . $path;
}
// Gambar pratinjau saat dibagikan: khusus halaman > OG image di Admin > gambar hero.
$ogImage = $ogImage ?? (mediaUrl(setting('site.og_image')) ?: mediaUrl(setting('hero.image')));

$jsonLd = [
    '@context' => 'https://schema.org',
    '@type'    => 'GovernmentOrganization',
    'name'     => setting('site.name'),
    'url'      => BASE_URL . '/',
    'address'  => setting('footer.address'),
];
if (setting('footer.email') !== '') {
    $jsonLd['email'] = setting('footer.email');
}
if (setting('footer.phone') !== '') {
    $jsonLd['telephone'] = setting('footer.phone');
}
$jsonLd['logo'] = logoUrl();
if ($ogImage !== '') {
    $jsonLd['image'] = $ogImage;
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= e($pageTitle) ?></title>
    <meta name="description" content="<?= e($pageDescription) ?>">
    <meta name="keywords" content="<?= e(setting('site.keywords')) ?>">
    <meta name="author" content="<?= e(setting('site.author')) ?>">
    <meta name="robots" content="<?= $noIndex ? 'noindex, follow' : 'index, follow' ?>">
    <meta name="theme-color" content="<?= e(settingColor('color.primary')) ?>">
    <link rel="canonical" href="<?= e($canonicalUrl) ?>">
    <?= faviconLinkTag() ?>

    <meta property="og:site_name" content="<?= e(setting('site.name')) ?>">
    <meta property="og:locale" content="id_ID">
    <meta property="og:type" content="<?= e($ogType) ?>">
    <meta property="og:title" content="<?= e($pageTitle) ?>">
    <meta property="og:description" content="<?= e($pageDescription) ?>">
    <meta property="og:url" content="<?= e($canonicalUrl) ?>">
    <?php if ($ogImage !== ''): ?>
    <meta property="og:image" content="<?= e($ogImage) ?>">
    <?php endif; ?>
    <meta name="twitter:card" content="<?= $ogImage !== '' ? 'summary_large_image' : 'summary' ?>">
    <meta name="twitter:title" content="<?= e($pageTitle) ?>">
    <meta name="twitter:description" content="<?= e($pageDescription) ?>">
    <?php if ($ogImage !== ''): ?>
    <meta name="twitter:image" content="<?= e($ogImage) ?>">
    <?php endif; ?>

    <?php if (!empty($preloadImage)): ?>
    <link rel="preload" as="image" href="<?= e($preloadImage) ?>" fetchpriority="high">
    <?php endif; ?>

    <script>document.documentElement.classList.add('js');</script>
    <link rel="stylesheet" href="<?= e(assetUrl('css/style.css')) ?>">
    <?php /* Harus SETELAH style.css agar warna dari pengaturan menimpa nilai bawaan di :root */ ?>
    <style>:root{--primary:<?= settingColor('color.primary') ?>;--primary-2:<?= settingColor('color.primary2') ?>;--accent:<?= settingColor('color.accent') ?>;--on-accent:<?= readableTextOn(settingColor('color.accent')) ?>}</style>
    <script type="application/ld+json"><?= json_encode($jsonLd, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_HEX_TAG | JSON_HEX_AMP) ?></script>
    <?php foreach (($extraJsonLd ?? []) as $extraLd): ?>
    <script type="application/ld+json"><?= json_encode($extraLd, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_HEX_TAG | JSON_HEX_AMP) ?></script>
    <?php endforeach; ?>
</head>
<body>

    <!-- Skip to main content link -->
    <a href="#main" class="skip-link">Lompat ke konten utama</a>

    <!-- NAVBAR -->
    <header class="navbar" id="navbar">
        <nav class="container nav-container" aria-label="Navigasi utama">
            <a class="logo-area" href="<?= e(BASE_URL) ?>/" data-home-link aria-label="<?= e(setting('site.name')) ?> — ke beranda">
                <span class="logo-icon" aria-hidden="true"><img src="<?= e(logoUrl()) ?>" alt="" width="42" height="42"></span>
                <span class="logo-text">
                    <span class="brand-title"><?= e(setting('site.brand_title')) ?></span>
                    <span class="brand-subtitle"><?= e(setting('site.brand_subtitle')) ?></span>
                </span>
            </a>
            <button class="menu-toggle" id="mobile-menu" type="button" aria-label="Buka menu navigasi" aria-expanded="false" aria-controls="nav-menu">☰</button>
            <ul class="nav-links" id="nav-menu">
                <?php
                /**
                 * Satu menu (tautan biasa). Mengembalikan HTML <a>, atau '' bila menuju section yang
                 * sedang dinonaktifkan admin. $isSub = item di dalam dropdown.
                 */
                $renderNavLink = function (array $navItem, bool $isSub) use ($isHome, $navActive): string {
                    $rawLink = trim((string) ($navItem['link_url'] ?? ''));
                    $target  = navTargetSection($rawLink);
                    if ($target !== '' && !sectionOn($target)) {
                        return ''; // sembunyikan menu yang menuju section yang dinonaktifkan
                    }
                    $anchor      = ($rawLink !== '' && $rawLink[0] === '#') ? substr($rawLink, 1) : '';
                    $isCta       = !$isSub && ($navItem['value'] ?? '') === 'cta';
                    $isHomeLink  = ($rawLink === '' || $rawLink === '/');
                    $isPageMatch = !$isHomeLink && $anchor === '' && $navActive !== '' && trim($rawLink, '/') === $navActive;

                    $attrs = [];
                    $classes = [];
                    if ($isCta) {
                        $classes[] = 'btn-cta';
                    }
                    if ($isHomeLink) {
                        $attrs[] = 'data-home-link';
                    }
                    if ($isHome && ($isHomeLink || $anchor !== '') && !$isCta && !$isSub) {
                        // Dipakai scroll-spy di main.js untuk menandai menu aktif.
                        $attrs[] = 'data-spy="' . e($isHomeLink ? 'beranda' : $anchor) . '"';
                    }
                    if ($isHome && $isHomeLink && !$isSub) {
                        $classes[] = 'active'; // status awal; selanjutnya diatur scroll-spy
                    }
                    if ($isPageMatch) {
                        $classes[] = 'active';
                        $attrs[]   = 'aria-current="page"';
                    }
                    return '<a href="' . e(resolveLink($rawLink)) . '"' . ($classes ? ' class="' . e(implode(' ', $classes)) . '"' : '') . ($attrs ? ' ' . implode(' ', $attrs) : '') . '>' . e($navItem['title']) . '</a>';
                };

                foreach (navTree(contentItems('nav')) as $navItem):
                    if (!$navItem['children']) {
                        $link = $renderNavLink($navItem, false);
                        if ($link !== '') {
                            echo '<li>' . $link . '</li>' . "\n";
                        }
                        continue;
                    }

                    // Menu dengan submenu: tombol pembuka dropdown + daftar tautan anak.
                    $subLinks = [];
                    $groupActive = false;
                    foreach ($navItem['children'] as $child) {
                        $html = $renderNavLink($child, true);
                        if ($html !== '') {
                            $subLinks[] = $html;
                            $groupActive = $groupActive || str_contains($html, 'aria-current="page"');
                        }
                    }
                    if (!$subLinks) {
                        continue;
                    }
                    $subId = 'submenu-' . (int) $navItem['id'];
                ?>
                <li class="has-sub">
                    <button type="button" class="nav-sub-toggle<?= $groupActive ? ' active' : '' ?>" aria-expanded="false" aria-controls="<?= e($subId) ?>"><?= e($navItem['title']) ?> <span class="caret" aria-hidden="true">▾</span></button>
                    <ul class="sub-menu" id="<?= e($subId) ?>">
                        <?php foreach ($subLinks as $html): ?>
                            <li><?= $html ?></li>
                        <?php endforeach; ?>
                    </ul>
                </li>
                <?php endforeach; ?>
            </ul>
        </nav>
    </header>

    <main id="main">
