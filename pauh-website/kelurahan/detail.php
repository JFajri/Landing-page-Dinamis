<?php
/**
 * kelurahan/detail.php?id=N — Halaman detail satu kelurahan: ringkasan, foto, isi, daftar
 * kelurahan lain, dan tautan untuk mengirim aspirasi dengan kelurahan terpilih otomatis.
 */
require_once __DIR__ . '/../includes/bootstrap.php';
requireSectionOn('kelurahan', 'kelurahan');

$item = contentItem('kelurahan', (int) ($_GET['id'] ?? 0));
if (!$item) {
    renderNotFound('← Kembali ke Daftar Kelurahan', '/kelurahan/', 'kelurahan');
}

$all    = contentItems('kelurahan');
$others = array_values(array_filter($all, fn($row) => (int) $row['id'] !== (int) $item['id']));
$lead   = trim((string) $item['description']) !== ''
    ? (string) $item['description']
    : 'Kelurahan ' . $item['title'] . ' merupakan salah satu dari ' . count($all) . ' kelurahan di wilayah administratif kecamatan.';

$pageTitle       = 'Kelurahan ' . $item['title'] . ' - ' . setting('site.name');
$pageDescription = makeExcerpt($lead, 155);
$navActive       = 'kelurahan';
$canonicalUrl    = contentDetailUrl('kelurahan', (int) $item['id']);
if (!empty($item['image'])) {
    $ogImage = mediaUrl($item['image']);
}
require __DIR__ . '/../includes/site_header.php';
?>
        <section class="section-padding page-top" id="kelurahan-detail" aria-labelledby="detail-title">
            <div class="container">
                <?php renderBreadcrumb([['Beranda', '/'], ['Kelurahan', '/kelurahan/'], [$item['title'], null]]); ?>

                <div class="detail-layout">
                    <article class="detail-main">
                        <div class="eyebrow"><?= e(setting('sec.kelurahan.eyebrow')) ?></div>
                        <h1 class="detail-title" id="detail-title">Kelurahan <?= e($item['title']) ?></h1>
                        <?php if (!empty($item['image'])): ?>
                            <img decoding="async" fetchpriority="high" src="<?= e(mediaUrl($item['image'])) ?>" alt="<?= e('Kelurahan ' . $item['title']) ?>" class="article-cover" referrerpolicy="no-referrer" data-fallback="<?= e(assetUrl('img/placeholder.svg')) ?>">
                        <?php endif; ?>
                        <p class="detail-lead"><?= e($lead) ?></p>
                        <?php if (trim((string) ($item['content'] ?? '')) !== ''): ?>
                            <div class="prose"><?= renderRichText($item['content']) ?></div>
                        <?php endif; ?>

                        <?php if (sectionOn('aspirasi')): ?>
                            <p><a href="<?= e(BASE_URL) ?>/aspirasi/?kelurahan=<?= e(rawurlencode($item['title'])) ?>" class="btn btn-main">Sampaikan Aspirasi dari Kelurahan Ini →</a></p>
                        <?php endif; ?>
                    </article>

                    <?php if ($others): ?>
                    <aside class="detail-aside" aria-labelledby="others-title">
                        <h2 class="aside-title" id="others-title">Kelurahan Lainnya</h2>
                        <ul class="aside-list">
                            <?php foreach ($others as $other): ?>
                                <li><a href="<?= e(contentDetailUrl('kelurahan', (int) $other['id'])) ?>"><?= e($other['title']) ?></a></li>
                            <?php endforeach; ?>
                        </ul>
                    </aside>
                    <?php endif; ?>
                </div>

                <div class="section-action section-action-lg text-center">
                    <a href="<?= e(BASE_URL) ?>/kelurahan/" class="btn btn-solid">← <?= e(setting('sec.kelurahan.more')) ?></a>
                </div>
            </div>
        </section>

<?php require __DIR__ . '/../includes/site_footer.php'; ?>
