<?php
require_once __DIR__ . '/../includes/bootstrap.php';

$pdo = getConnection();

$perPage = 9;
$page    = max(1, (int) ($_GET['page'] ?? 1));
$offset  = ($page - 1) * $perPage;

$totalStmt = $pdo->query("SELECT COUNT(*) FROM news WHERE status = 'published'");
$totalNews = (int) $totalStmt->fetchColumn();
$totalPages = max(1, (int) ceil($totalNews / $perPage));

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

$pageTitle       = 'Berita & Pengumuman - ' . SITE_NAME;
$pageDescription = 'Kumpulan berita, pengumuman, dan kegiatan resmi dari Kecamatan Pauh, Kota Padang.';
require __DIR__ . '/../includes/site_header.php';
?>
        <section class="section-padding text-center" id="berita" aria-label="Semua berita Kecamatan Pauh" style="padding-top:150px">
            <div class="container">
                <div class="eyebrow">Informasi Terbaru</div>
                <h2 class="section-title">Berita & Pengumuman</h2>
                <p class="section-desc">Ikuti perkembangan terbaru, pengumuman penting, dan kegiatan di Kecamatan Pauh.</p>

                <?php if (empty($newsList)): ?>
                    <p style="color:var(--muted);font-size:14px;margin-top:44px">Belum ada berita yang dipublikasikan.</p>
                <?php else: ?>
                    <div class="berita-grid" role="list">
                        <?php foreach ($newsList as $item): ?>
                            <article class="berita-card reveal" role="listitem">
                                <img src="<?= $item['thumbnail'] ? e(UPLOAD_URL . $item['thumbnail']) : 'https://images.unsplash.com/photo-1552664730-d307ca884978?auto=format&fit=crop&w=600&q=80' ?>" alt="<?= e($item['title']) ?>" class="berita-img">
                                <div class="berita-meta">
                                    <span aria-label="Tanggal">📅 <?= e(formatTanggalIndo($item['published_at'])) ?></span>
                                    <?php if ($item['category_name']): ?>
                                        <span class="berita-badge"><?= e($item['category_name']) ?></span>
                                    <?php endif; ?>
                                </div>
                                <div class="berita-body">
                                    <h3><?= e($item['title']) ?></h3>
                                    <p><?= e($item['excerpt']) ?></p>
                                    <a href="<?= e(BASE_URL) ?>/news/detail.php?slug=<?= urlencode($item['slug']) ?>" class="berita-link">Baca Selengkapnya →</a>
                                </div>
                            </article>
                        <?php endforeach; ?>
                    </div>

                    <?php if ($totalPages > 1): ?>
                        <div style="margin-top:36px;display:flex;gap:8px;justify-content:center;flex-wrap:wrap">
                            <?php for ($p = 1; $p <= $totalPages; $p++): ?>
                                <a href="?page=<?= $p ?>"
                                   class="btn <?= $p === $page ? 'btn-main' : 'btn-sub' ?>"
                                   style="<?= $p === $page ? '' : 'background:#eef4fb;color:var(--primary);border-color:var(--line)' ?>;min-width:44px;padding:0 14px">
                                    <?= $p ?>
                                </a>
                            <?php endfor; ?>
                        </div>
                    <?php endif; ?>
                <?php endif; ?>
            </div>
        </section>

<?php require __DIR__ . '/../includes/site_footer.php'; ?>
