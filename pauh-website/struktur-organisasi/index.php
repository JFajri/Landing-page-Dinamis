<?php
/**
 * struktur-organisasi/index.php — Halaman statis Struktur Organisasi.
 * Bagan (gambar) & catatan: Admin → Tampilan Website → Halaman Profil. Daftar pejabat:
 * Admin → Konten Halaman → Struktur Organisasi (item pertama = pimpinan di puncak bagan).
 */
require_once __DIR__ . '/../includes/bootstrap.php';

$chart    = mediaUrl(setting('page.struktur.chart_image'));
$note     = setting('page.struktur.note');
$officials = contentItems('struktur');
$leader   = $officials ? $officials[0] : null;
$staff    = array_slice($officials, 1);

$pageTitle       = setting('page.struktur.title') . ' - ' . setting('site.name');
$pageDescription = setting('page.struktur.desc');
$navActive       = 'struktur-organisasi';
$canonicalUrl    = BASE_URL . '/struktur-organisasi/';
if ($chart !== '') {
    $ogImage = $chart;
}

/** Kartu satu pejabat. Foto kosong diganti inisial agar kartu tetap rapi. */
$renderOfficial = static function (array $row, bool $isLeader = false): void {
    $initial = mb_strtoupper(mb_substr(trim((string) $row['title']), 0, 1));
    ?>
    <li class="org-card<?= $isLeader ? ' org-leader' : '' ?> reveal">
        <?php if (!empty($row['image'])): ?>
            <img loading="lazy" decoding="async" width="160" height="160" src="<?= e(mediaUrl($row['image'])) ?>" alt="<?= e($row['title']) ?>" class="org-photo" referrerpolicy="no-referrer" data-fallback="<?= e(assetUrl('img/placeholder.svg')) ?>">
        <?php else: ?>
            <span class="org-photo org-initial" aria-hidden="true"><?= e($initial) ?></span>
        <?php endif; ?>
        <h3 class="org-name"><?= e($row['title']) ?></h3>
        <p class="org-role"><?= e($row['value']) ?></p>
        <?php if (trim((string) $row['description']) !== ''): ?>
            <p class="org-desc"><?= e($row['description']) ?></p>
        <?php endif; ?>
    </li>
    <?php
};
require __DIR__ . '/../includes/site_header.php';
?>
        <section class="section-padding text-center page-top" id="struktur" aria-labelledby="page-title">
            <div class="container">
                <?php renderPageHeading('struktur'); ?>
                <?php renderProfilTabs('struktur-organisasi'); ?>

                <?php if ($chart === '' && !$officials): ?>
                    <?php renderEmptyNote(); ?>
                <?php endif; ?>

                <?php if ($chart !== ''): ?>
                    <figure class="org-chart">
                        <a href="<?= e($chart) ?>" target="_blank" rel="noopener" aria-label="Buka bagan organisasi ukuran penuh di tab baru">
                            <img decoding="async" src="<?= e($chart) ?>" alt="Bagan struktur organisasi <?= e(setting('site.brand_title')) ?>" referrerpolicy="no-referrer" data-fallback="<?= e(assetUrl('img/placeholder.svg')) ?>">
                        </a>
                        <?php if (trim($note) !== ''): ?><figcaption><?= e($note) ?></figcaption><?php endif; ?>
                    </figure>
                <?php endif; ?>

                <?php if ($officials): ?>
                    <h2 class="subsection-title<?= $chart === '' ? ' sr-only' : '' ?>">Pejabat &amp; Perangkat Kecamatan</h2>
                    <ul class="org-tree">
                        <?php $renderOfficial($leader, true); ?>
                    </ul>
                    <?php if ($staff): ?>
                        <ul class="org-grid">
                            <?php foreach ($staff as $row): ?>
                                <?php $renderOfficial($row); ?>
                            <?php endforeach; ?>
                        </ul>
                    <?php endif; ?>
                <?php endif; ?>
            </div>
        </section>

<?php require __DIR__ . '/../includes/site_footer.php'; ?>
