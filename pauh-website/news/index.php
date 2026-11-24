<?php
require_once __DIR__ . '/../includes/bootstrap.php';

$pdo = getConnection();

$perPage = max(3, min(24, (int) setting('news.per_page', '9')));
$page    = max(1, (int) ($_GET['page'] ?? 1));
$offset  = ($page - 1) * $perPage;

$totalStmt = $pdo->query("SELECT COUNT(*) FROM news WHERE status = 'published'");
$totalNews = (int) $totalStmt->fetchColumn();
$totalPages = max(1, (int) ceil($totalNews / $perPage));

// Nomor halaman di luar rentang (mis. berita dihapus, atau tautan lama): arahkan ke halaman
// terakhir yang ada, bukan menampilkan daftar kosong yang bisa ikut terindeks.
if ($page > $totalPages) {
    header('Location: ' . BASE_URL . '/news/' . ($totalPages > 1 ? '?page=' . $totalPages : ''), true, 302);
    exit;
}

$stmt = $pdo->prepare("
    SELECT n.id, n.title, n.slug, n.excerpt, n.thumbnail, n.published_at, c.name AS category_name
    FROM news n
    LEFT JOIN categories c ON c.id = n.category_id
    WHERE n.status = 'published'
    ORDER BY n.published_at DESC
    LIMIT :limit OFFSET :offset
");
$stmt->bindValue(':limit', $perPage, PDO::PARAM_INT);
$stmt->bindValue(':offset', $offset, PDO::PARAM_INT);
$stmt->execute();
$newsList = $stmt->fetchAll();

$pageTitle       = setting('sec.berita.title') . ' - ' . setting('site.name') . ($page > 1 ? ' (Halaman ' . $page . ')' : '');
$pageDescription = 'Kumpulan berita, pengumuman, dan kegiatan resmi dari ' . setting('site.brand_title') . ', ' . setting('site.brand_subtitle') . '.';
$navActive       = 'news';
$canonicalUrl    = BASE_URL . '/news/' . ($page > 1 ? '?page=' . $page : '');
require __DIR__ . '/../includes/site_header.php';
?>
        <section class="section-padding text-center page-top" id="berita" aria-labelledby="berita-title">
            <div class="container">
                <?php renderSectionHead('berita', [], 'h1'); ?>

                <?php if (empty($newsList)): ?>
                    <p class="empty-note"><?= e(setting('news.empty_text')) ?></p>
                <?php else: ?>
                    <div class="berita-grid">
                        <?php foreach ($newsList as $item): ?>
                            <?php $hTag = 'h2'; require __DIR__ . '/../includes/news_card.php'; ?>
                        <?php endforeach; ?>
                    </div>

                    <?php if ($totalPages > 1): ?>
                        <nav class="pagination" aria-label="Halaman berita">
                            <?php for ($p = 1; $p <= $totalPages; $p++): ?>
                                <a href="?page=<?= $p ?>"
                                   class="page-link<?= $p === $page ? ' is-current' : '' ?>"
                                   <?= $p === $page ? 'aria-current="page"' : '' ?>
                                   aria-label="Halaman <?= $p ?>"><?= $p ?></a>
                            <?php endfor; ?>
                        </nav>
                    <?php endif; ?>
                <?php endif; ?>
            </div>
        </section>

<?php require __DIR__ . '/../includes/site_footer.php'; ?>
