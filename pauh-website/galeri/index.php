<?php
/**
 * galeri/index.php — Halaman daftar SEMUA foto galeri (beranda hanya memuat beberapa).
 * Klik foto membuka lightbox (assets/js/main.js). Data dikelola di Admin → Galeri.
 */
require_once __DIR__ . '/../includes/bootstrap.php';
requireSectionOn('galeri', 'galeri');

$pdo     = getConnection();
$perPage = listPerPage();
$total   = (int) $pdo->query("SELECT COUNT(*) FROM gallery WHERE status = 'active'")->fetchColumn();
[$page, $totalPages] = resolvePage($total, $perPage, '/galeri/');

$stmt = $pdo->prepare("
    SELECT id, image, caption, description
    FROM gallery
    WHERE status = 'active'
    ORDER BY created_at DESC, id DESC
    LIMIT :limit OFFSET :offset
");
$stmt->bindValue(':limit', $perPage, PDO::PARAM_INT);
$stmt->bindValue(':offset', ($page - 1) * $perPage, PDO::PARAM_INT);
$stmt->execute();
$photos = $stmt->fetchAll();

$pageTitle       = setting('sec.galeri.title') . ' - ' . setting('site.name') . pageSuffix($page);
$pageDescription = setting('sec.galeri.desc');
$navActive       = 'galeri';
$canonicalUrl    = BASE_URL . '/galeri/' . ($page > 1 ? '?page=' . $page : '');
require __DIR__ . '/../includes/site_header.php';
?>
        <section class="section-padding layanan-bg text-center page-top" id="galeri" aria-labelledby="galeri-title">
            <div class="container">
                <?php renderSectionHead('galeri', [], 'h1'); ?>

                <?php if (!$photos): ?>
                    <p class="empty-note"><?= e(setting('gallery.empty_text')) ?></p>
                <?php else: ?>
                    <div class="gallery-grid">
                        <?php foreach ($photos as $photo): ?>
                            <?php require __DIR__ . '/../includes/item_gallery.php'; ?>
                        <?php endforeach; ?>
                    </div>
                    <?php renderPagination($page, $totalPages, 'Halaman galeri'); ?>
                <?php endif; ?>
            </div>
        </section>

<?php require __DIR__ . '/../includes/site_footer.php'; ?>
