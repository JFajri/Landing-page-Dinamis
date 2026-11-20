<?php
/**
 * includes/header.php
 * Layout header + sidebar untuk SEMUA halaman admin.
 * Wajib: requireLogin() sudah dipanggil, dan variabel berikut sudah di-set
 * oleh halaman pemanggil sebelum include file ini:
 *   $pageTitle   (string) judul halaman
 *   $activeMenu  (string) salah satu dari: dashboard, news, categories,
 *                gallery, calendar, complaints, settings-<grup>, content-<jenis>
 */
$pageTitle  = $pageTitle  ?? 'Admin';
$activeMenu = $activeMenu ?? '';
$admin = currentAdmin();

// Jumlah aspirasi/pengaduan berstatus "baru" untuk badge notifikasi di sidebar.
$newComplaintsCount = (int) getConnection()->query("SELECT COUNT(*) FROM complaints WHERE status = 'baru'")->fetchColumn();
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="robots" content="noindex, nofollow">
    <meta name="theme-color" content="#0d2542">
    <?= faviconLinkTag() ?>
    <title><?= e($pageTitle) ?> - Admin <?= e(SITE_NAME) ?></title>
    <link rel="stylesheet" href="<?= e(BASE_URL) ?>/assets/css/admin.css">
</head>
<body>
<div class="admin-layout">
    <div class="sidebar-backdrop" id="sidebar-backdrop"></div>

    <aside class="sidebar" id="admin-sidebar">
        <div class="brand">
            <img class="brand-logo" src="<?= e(logoUrl()) ?>" alt="" width="34" height="34">
            <div>
                <h1>ADMIN PAUH</h1>
                <p>Panel Pengelola Konten</p>
            </div>
        </div>
        <nav>
            <a href="<?= e(BASE_URL) ?>/admin/" class="<?= $activeMenu === 'dashboard' ? 'active' : '' ?>">📊 Dashboard</a>

            <div class="section-label">Portal Berita</div>
            <a href="<?= e(BASE_URL) ?>/admin/news/" class="<?= $activeMenu === 'news' ? 'active' : '' ?>">📰 Berita</a>
            <a href="<?= e(BASE_URL) ?>/admin/categories/" class="<?= $activeMenu === 'categories' ? 'active' : '' ?>">🏷️ Kategori</a>

            <div class="section-label">Tampilan Website</div>
            <?php foreach (settingsSchema() as $gKey => $g): ?>
                <a href="<?= e(BASE_URL) ?>/admin/settings/?group=<?= e($gKey) ?>" class="<?= $activeMenu === 'settings-' . $gKey ? 'active' : '' ?>"><?= e($g['icon']) ?> <?= e($g['label']) ?></a>
            <?php endforeach; ?>

            <div class="section-label">Konten Halaman</div>
            <?php foreach (contentSections() as $cKey => $c): ?>
                <a href="<?= e(BASE_URL) ?>/admin/content/?section=<?= e($cKey) ?>" class="<?= $activeMenu === 'content-' . $cKey ? 'active' : '' ?>"><?= e($c['icon']) ?> <?= e($c['label']) ?></a>
            <?php endforeach; ?>

            <div class="section-label">Modul Landing Page</div>
            <a href="<?= e(BASE_URL) ?>/admin/gallery/" class="<?= $activeMenu === 'gallery' ? 'active' : '' ?>">🖼️ Galeri</a>
            <a href="<?= e(BASE_URL) ?>/admin/calendar/" class="<?= $activeMenu === 'calendar' ? 'active' : '' ?>">📅 Kalender & Event</a>
            <a href="<?= e(BASE_URL) ?>/admin/complaints/" class="<?= $activeMenu === 'complaints' ? 'active' : '' ?>" style="display:flex;justify-content:space-between;align-items:center">
                <span>📨 Aspirasi & Pengaduan</span>
                <?php if ($newComplaintsCount > 0): ?>
                    <span style="background:var(--red, #c0392b);color:#fff;border-radius:99px;font-size:10.5px;font-weight:800;padding:2px 8px;line-height:1.4"><?= $newComplaintsCount ?></span>
                <?php endif; ?>
            </a>

            <div class="section-label">Website</div>
            <a href="<?= e(BASE_URL) ?>/" target="_blank">🌐 Lihat Website</a>
        </nav>
        <div class="logout-form">
            <form action="<?= e(BASE_URL) ?>/admin/logout.php" method="post">
                <?= csrfField() ?>
                <button type="submit">🚪 Logout</button>
            </form>
        </div>
    </aside>

    <div class="content">
        <div class="topbar">
            <div style="display:flex;align-items:center;gap:12px">
                <button class="menu-toggle-admin" id="sidebar-toggle" aria-label="Buka menu">☰</button>
                <h2><?= e($pageTitle) ?></h2>
            </div>
            <div class="admin-user">👤 <?= e($admin['name'] ?? '') ?></div>
        </div>
        <div class="page">
            <?php $flash = getFlash(); ?>
            <?php if ($flash): ?>
                <div class="alert alert-<?= $flash['type'] === 'success' ? 'success' : 'error' ?>" data-autohide>
                    <?= e($flash['message']) ?>
                </div>
            <?php endif; ?>
