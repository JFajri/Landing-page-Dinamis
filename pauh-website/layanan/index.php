<?php
/**
 * layanan/index.php — Halaman daftar SEMUA layanan administrasi (beranda hanya memuat beberapa).
 * Data dikelola di Admin → Konten Halaman → Layanan Publik.
 */
require_once __DIR__ . '/../includes/bootstrap.php';
requireSectionOn('layanan', 'layanan');

$all     = contentItems('layanan');
$perPage = listPerPage();
[$page, $totalPages] = resolvePage(count($all), $perPage, '/layanan/');
$items   = contentItemsPage('layanan', $perPage, ($page - 1) * $perPage);

$pageTitle       = setting('sec.layanan.title') . ' - ' . setting('site.name') . pageSuffix($page);
$pageDescription = setting('sec.layanan.desc');
$navActive       = 'layanan';
$canonicalUrl    = BASE_URL . '/layanan/' . ($page > 1 ? '?page=' . $page : '');
$hTag            = 'h2';
require __DIR__ . '/../includes/site_header.php';
?>
        <section class="section-padding layanan-bg text-center page-top" id="layanan" aria-labelledby="layanan-title">
            <div class="container">
                <?php renderSectionHead('layanan', [], 'h1'); ?>

                <?php if (!$items): ?>
                    <?php renderEmptyNote('Belum ada layanan yang dipublikasikan.'); ?>
                <?php else: ?>
                    <div class="grid-4">
                        <?php foreach ($items as $svc): ?>
                            <?php require __DIR__ . '/../includes/card_layanan.php'; ?>
                        <?php endforeach; ?>
                    </div>
                    <?php renderPagination($page, $totalPages, 'Halaman layanan'); ?>
                <?php endif; ?>
            </div>
        </section>

<?php require __DIR__ . '/../includes/site_footer.php'; ?>
