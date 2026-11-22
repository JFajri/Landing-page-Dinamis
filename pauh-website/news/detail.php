<?php
require_once __DIR__ . '/../includes/bootstrap.php';

$pdo  = getConnection();
$slug = clean($_GET['slug'] ?? '');

$news = null;
if ($slug !== '') {
    $stmt = $pdo->prepare("
        SELECT n.*, c.name AS category_name
        FROM news n
        LEFT JOIN categories c ON c.id = n.category_id
        WHERE n.slug = ? AND n.status = 'published'
        LIMIT 1
    ");
    $stmt->execute([$slug]);
    $news = $stmt->fetch();
}

if (!$news) {
    http_response_code(404);
}

$pageTitle       = $news ? $news['title'] . ' - ' . setting('site.name') : 'Berita Tidak Ditemukan - ' . setting('site.name');
$pageDescription = $news ? ($news['excerpt'] ?: makeExcerpt($news['content'], 155)) : 'Berita yang Anda cari tidak ditemukan atau belum dipublikasikan.';
$navActive       = 'news';
$noIndex         = !$news;
if ($news) {
    $ogType       = 'article';
    $canonicalUrl = BASE_URL . '/news/detail.php?slug=' . urlencode($news['slug']);
    if ($news['thumbnail']) {
        $ogImage = UPLOAD_URL . $news['thumbnail'];
    }

    // Data terstruktur NewsArticle agar berita dapat tampil sebagai hasil kaya di mesin pencari.
    $toIso = static function (?string $value): ?string {
        $ts = $value ? strtotime($value) : false;
        return $ts ? date('c', $ts) : null;
    };
    $articleLd = [
        '@context'         => 'https://schema.org',
        '@type'            => 'NewsArticle',
        'headline'         => mb_substr($news['title'], 0, 110),
        'description'      => $pageDescription,
        'mainEntityOfPage' => $canonicalUrl,
        'author'           => ['@type' => 'Organization', 'name' => setting('site.name')],
        'publisher'        => [
            '@type' => 'Organization',
            'name'  => setting('site.name'),
            'logo'  => ['@type' => 'ImageObject', 'url' => logoUrl()],
        ],
    ];
    if ($toIso($news['published_at'])) {
        $articleLd['datePublished'] = $toIso($news['published_at']);
    }
    if ($toIso($news['updated_at'] ?? null)) {
        $articleLd['dateModified'] = $toIso($news['updated_at']);
    }
    if ($news['thumbnail']) {
        $articleLd['image'] = [UPLOAD_URL . $news['thumbnail']];
    }
    $extraJsonLd = [$articleLd];
}
require __DIR__ . '/../includes/site_header.php';
?>
        <section class="section-padding page-top" id="berita-detail" aria-label="Detail berita">
            <div class="container container-narrow">

                <?php if (!$news): ?>
                    <div class="text-center">
                        <h2 class="section-title">Berita Tidak Ditemukan</h2>
                        <p class="section-desc section-desc-flush">Berita yang Anda cari mungkin sudah dihapus, belum dipublikasikan, atau alamat tidak valid.</p>
                        <a href="<?= e(BASE_URL) ?>/news/" class="btn btn-main">← Kembali ke Daftar Berita</a>
                    </div>
                <?php else: ?>
                    <div class="berita-meta berita-meta-detail">
                        <span><span aria-hidden="true">📅</span> <time datetime="<?= e(substr((string) $news['published_at'], 0, 10)) ?>"><?= e(formatTanggalIndo($news['published_at'])) ?></time></span>
                        <?php if ($news['category_name']): ?>
                            <span class="berita-badge"><?= e($news['category_name']) ?></span>
                        <?php endif; ?>
                    </div>
                    <h1 class="section-title text-center article-title"><?= e($news['title']) ?></h1>

                    <?php if ($news['thumbnail']): ?>
                        <img decoding="async" fetchpriority="high" src="<?= e(UPLOAD_URL . $news['thumbnail']) ?>" alt="<?= e($news['title']) ?>" class="article-cover">
                    <?php endif; ?>

                    <div class="article-content">
                        <?= nl2br(e($news['content'])) ?>
                    </div>

                    <div class="section-action section-action-lg">
                        <a href="<?= e(BASE_URL) ?>/news/" class="btn btn-solid">← Kembali ke Daftar Berita</a>
                    </div>
                <?php endif; ?>

            </div>
        </section>

<?php require __DIR__ . '/../includes/site_footer.php'; ?>
