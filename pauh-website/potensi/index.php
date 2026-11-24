<?php
/**
 * potensi/index.php — Halaman daftar SEMUA Potensi Wilayah (beranda hanya memuat beberapa).
 * Data dikelola di Admin → Konten Halaman → Potensi Wilayah.
 */
require_once __DIR__ . '/../includes/bootstrap.php';
requireSectionOn('potensi', 'potensi');

$all     = contentItems('potensi');
$perPage = listPerPage();
[$page, $totalPages] = resolvePage(count($all), $perPage, '/potensi/');
$items   = contentItemsPage('potensi', $perPage, ($page - 1) * $perPage);

$pageTitle       = setting('sec.potensi.title') . ' - ' . setting('site.name') . pageSuffix($page);
$pageDescription = setting('sec.potensi.desc');
$navActive       = 'potensi';
$canonicalUrl    = BASE_URL . '/potensi/' . ($page > 1 ? '?page=' . $page : '');
$hTag            = 'h2';
require __DIR__ . '/../includes/site_header.php';
?>
        <section class="section-padding text-center page-top" id="potensi" aria-labelledby="potensi-title">
            <div class="container">
                <?php renderSectionHead('potensi', [], 'h1'); ?>

                <?php if (!$items): ?>
                    <?php renderEmptyNote('Belum ada data potensi wilayah yang dipublikasikan.'); ?>
                <?php else: ?>
                    <div class="grid-3">
                        <?php foreach ($items as $card): ?>
                            <?php require __DIR__ . '/../includes/card_potensi.php'; ?>
                        <?php endforeach; ?>
                    </div>
                    <?php renderPagination($page, $totalPages, 'Halaman potensi wilayah'); ?>
                <?php endif; ?>
            </div>
        </section>

<?php require __DIR__ . '/../includes/site_footer.php'; ?>
