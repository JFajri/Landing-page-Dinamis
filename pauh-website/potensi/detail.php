<?php
/**
 * potensi/detail.php?id=N — Halaman detail satu Potensi Wilayah.
 * Isi: judul, gambar, deskripsi singkat, dan "Isi Halaman Detail" dari Admin.
 */
require_once __DIR__ . '/../includes/bootstrap.php';
requireSectionOn('potensi', 'potensi');

$item = contentItem('potensi', (int) ($_GET['id'] ?? 0));
if (!$item) {
    renderNotFound('← Kembali ke Potensi Wilayah', '/potensi/', 'potensi');
}

$others = array_slice(array_values(array_filter(contentItems('potensi'), fn($row) => (int) $row['id'] !== (int) $item['id'])), 0, 3);

$pageTitle       = $item['title'] . ' - ' . setting('site.name');
$pageDescription = trim((string) $item['description']) !== '' ? makeExcerpt((string) $item['description'], 155) : makeExcerpt((string) $item['content'], 155);
$navActive       = 'potensi';
$canonicalUrl    = contentDetailUrl('potensi', (int) $item['id']);
if (!empty($item['image'])) {
    $ogImage = mediaUrl($item['image']);
}
require __DIR__ . '/../includes/site_header.php';
?>
        <section class="section-padding page-top" id="potensi-detail" aria-labelledby="detail-title">
            <div class="container">
                <?php renderBreadcrumb([['Beranda', '/'], [setting('sec.potensi.title'), '/potensi/'], [$item['title'], null]]); ?>

                <div class="detail-layout">
                    <article class="detail-main">
                        <div class="eyebrow"><?= e(setting('sec.potensi.eyebrow')) ?></div>
                        <h1 class="detail-title" id="detail-title"><?= e($item['title']) ?></h1>
                        <?php if (!empty($item['image'])): ?>
                            <img decoding="async" fetchpriority="high" src="<?= e(mediaUrl($item['image'])) ?>" alt="<?= e($item['title']) ?>" class="article-cover" referrerpolicy="no-referrer" data-fallback="<?= e(assetUrl('img/placeholder.svg')) ?>">
                        <?php endif; ?>
                        <?php if (trim((string) $item['description']) !== ''): ?>
                            <p class="detail-lead"><?= e($item['description']) ?></p>
                        <?php endif; ?>
                        <?php if (trim((string) ($item['content'] ?? '')) !== ''): ?>
                            <div class="prose"><?= renderRichText($item['content']) ?></div>
                        <?php endif; ?>
                        <?php if (trim((string) $item['link_url']) !== ''): ?>
                            <p><a href="<?= e(resolveLink($item['link_url'])) ?>" class="btn btn-main">Informasi Selengkapnya →</a></p>
                        <?php endif; ?>
                    </article>

                    <?php if ($others): ?>
                    <aside class="detail-aside" aria-labelledby="others-title">
                        <h2 class="aside-title" id="others-title">Potensi Lainnya</h2>
                        <ul class="aside-list">
                            <?php foreach ($others as $other): ?>
                                <li><a href="<?= e(contentCardLink('potensi', $other)) ?>"><?= e($other['title']) ?></a></li>
                            <?php endforeach; ?>
                        </ul>
                    </aside>
                    <?php endif; ?>
                </div>

                <div class="section-action section-action-lg text-center">
                    <a href="<?= e(BASE_URL) ?>/potensi/" class="btn btn-solid">← <?= e(setting('sec.potensi.more')) ?></a>
                </div>
            </div>
        </section>

<?php require __DIR__ . '/../includes/site_footer.php'; ?>
