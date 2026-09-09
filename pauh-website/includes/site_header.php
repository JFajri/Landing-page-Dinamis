<?php
/**
 * includes/site_header.php
 * Header publik (dipakai oleh index.php, news/index.php, news/detail.php).
 * Desain navbar 100% dipertahankan dari landing_page.html asli, hanya
 * dipindahkan ke file terpisah agar bisa dipakai ulang & path aset memakai BASE_URL.
 *
 * Variabel opsional yang bisa di-set sebelum include file ini:
 *   $pageTitle       (string) judul tab browser
 *   $pageDescription (string) meta description
 */
$pageTitle       = $pageTitle       ?? SITE_NAME . ' - Kota Padang | Layanan Publik Terpercaya';
$pageDescription = $pageDescription ?? 'Portal Resmi Kecamatan Pauh, Kota Padang. Pusat pendidikan tinggi dengan layanan administrasi warga yang transparan dan mudah diakses.';
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description" content="<?= e($pageDescription) ?>">
    <meta name="keywords" content="Kecamatan Pauh, Padang, administrasi, layanan publik, kelurahan, Unand">
    <meta name="author" content="Pemerintah Kecamatan Pauh, Kota Padang">
    <meta name="robots" content="index, follow">
    <meta property="og:title" content="<?= e($pageTitle) ?>">
    <meta property="og:description" content="<?= e($pageDescription) ?>">
    <meta property="og:type" content="website">
    <meta property="og:url" content="<?= e(BASE_URL) ?>">
    <meta name="twitter:card" content="summary_large_image">
    <meta name="twitter:title" content="<?= e($pageTitle) ?>">
    <meta name="twitter:description" content="<?= e($pageDescription) ?>">
    <link rel="canonical" href="<?= e(BASE_URL) ?>">
    <link rel="icon" type="image/svg+xml" href="<?= e(BASE_URL) ?>/assets/favicon.svg">
    <meta name="theme-color" content="#123b6d">
    <title><?= e($pageTitle) ?></title>
    <link rel="stylesheet" href="<?= e(BASE_URL) ?>/assets/css/style.css">
</head>
<body>

    <!-- Skip to main content link -->
    <a href="#main" class="skip-link">Lompat ke konten utama</a>

    <!-- NAVBAR -->
    <nav class="navbar" role="navigation" aria-label="Navigasi utama">
        <div class="container nav-container">
            <div class="logo-area">
                <div class="logo-icon" aria-hidden="true">🏛️</div>
                <div class="logo-text">
                    <h1>KECAMATAN PAUH</h1>
                    <p>Kota Padang, Sumatra Barat</p>
                </div>
            </div>
            <button class="menu-toggle" id="mobile-menu" aria-label="Buka menu navigasi" aria-expanded="false">☰</button>
            <ul class="nav-links" id="nav-menu" role="menubar">
                <li role="none"><a href="<?= e(BASE_URL) ?>/#beranda" role="menuitem">Beranda</a></li>
                <li role="none"><a href="<?= e(BASE_URL) ?>/#potensi" role="menuitem">Potensi Wilayah</a></li>
                <li role="none"><a href="<?= e(BASE_URL) ?>/#kelurahan" role="menuitem">Kelurahan</a></li>
                <li role="none"><a href="<?= e(BASE_URL) ?>/#layanan" role="menuitem">Layanan</a></li>
                <li role="none"><a href="<?= e(BASE_URL) ?>/news" role="menuitem">Berita</a></li>
                <li role="none"><a href="<?= e(BASE_URL) ?>/#galeri" role="menuitem">Galeri</a></li>
                <li role="none"><a href="<?= e(BASE_URL) ?>/#kalender" role="menuitem">Kalender</a></li>
                <li role="none"><a href="<?= e(BASE_URL) ?>/#aspirasi" role="menuitem">Aspirasi</a></li>
                <li role="none"><a href="<?= e(BASE_URL) ?>/#kontak" class="btn-cta" role="menuitem">Kontak</a></li>
            </ul>
        </div>
    </nav>

    <main id="main">
