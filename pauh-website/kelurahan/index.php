<?php
/**
 * kelurahan/index.php — Halaman daftar SEMUA kelurahan (beranda hanya memuat beberapa).
 * Data dikelola di Admin → Konten Halaman → Kelurahan.
 */
require_once __DIR__ . '/../includes/bootstrap.php';
requireSectionOn('kelurahan', 'kelurahan');

$all     = contentItems('kelurahan');
$perPage = listPerPage();
[$page, $totalPages] = resolvePage(count($all), $perPage, '/kelurahan/');
$items   = contentItemsPage('kelurahan', $perPage, ($page - 1) * $perPage);
$vars    = ['count' => count($all)];

$pageTitle       = tpl(setting('sec.kelurahan.title'), $vars) . ' - ' . setting('site.name') . pageSuffix($page);
$pageDescription = tpl(setting('sec.kelurahan.desc'), $vars);
$navActive       = 'kelurahan';
$canonicalUrl    = BASE_URL . '/kelurahan/' . ($page > 1 ? '?page=' . $page : '');
require __DIR__ . '/../includes/site_header.php';
?>
        <section class="section-padding text-center page-top" id="kelurahan" aria-labelledby="kelurahan-title">
            <div class="container">
                <?php renderSectionHead('kelurahan', $vars, 'h1'); ?>

                <?php if (!$items): ?>
                    <?php renderEmptyNote('Belum ada data kelurahan yang dipublikasikan.'); ?>
                <?php else: ?>
                    <ul class="kelurahan-grid">
                        <?php foreach ($items as $kel): ?>
                            <?php require __DIR__ . '/../includes/item_kelurahan.php'; ?>
                        <?php endforeach; ?>
                    </ul>
                    <?php renderPagination($page, $totalPages, 'Halaman kelurahan'); ?>
                <?php endif; ?>
            </div>
        </section>

<?php require __DIR__ . '/../includes/site_footer.php'; ?>
