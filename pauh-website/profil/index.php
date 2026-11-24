<?php
/**
 * profil/index.php — Halaman statis Profil Kecamatan.
 * Teks & gambar: Admin → Tampilan Website → Halaman Profil. Angka ringkas memakai data
 * Statistik (Admin → Konten Halaman → Statistik), daftar wilayah memakai data Kelurahan.
 */
require_once __DIR__ . '/../includes/bootstrap.php';

$content   = setting('page.profil.content');
$image     = mediaUrl(setting('page.profil.image'));
$stats     = sectionOn('stats') ? contentItems('stats') : [];
$kelurahan = sectionOn('kelurahan') ? contentItems('kelurahan') : [];

$pageTitle       = setting('page.profil.title') . ' - ' . setting('site.name');
$pageDescription = setting('page.profil.desc');
$navActive       = 'profil';
$canonicalUrl    = BASE_URL . '/profil/';
if ($image !== '') {
    $ogImage = $image;
}
require __DIR__ . '/../includes/site_header.php';
?>
        <section class="section-padding text-center page-top" id="profil" aria-labelledby="page-title">
            <div class="container">
                <?php renderPageHeading('profil'); ?>
                <?php renderProfilTabs('profil'); ?>

                <?php if (trim($content) === '' && $image === ''): ?>
                    <?php renderEmptyNote(); ?>
                <?php else: ?>
                    <div class="static-layout<?= $image !== '' ? ' has-image' : '' ?>">
                        <?php if ($image !== ''): ?>
                            <img decoding="async" fetchpriority="high" src="<?= e($image) ?>" alt="<?= e(setting('page.profil.title')) ?>" class="static-image" referrerpolicy="no-referrer" data-fallback="<?= e(assetUrl('img/placeholder.svg')) ?>">
                        <?php endif; ?>
                        <div class="prose"><?= renderRichText($content) ?></div>
                    </div>
                <?php endif; ?>

                <?php if ($stats): ?>
                    <ul class="stats-grid stats-grid-flat" aria-label="Data ringkas wilayah">
                        <?php foreach ($stats as $stat): ?>
                            <li class="stat-item">
                                <p class="stat-value"><?= e($stat['value']) ?></p>
                                <p class="stat-label"><?= e($stat['title']) ?></p>
                            </li>
                        <?php endforeach; ?>
                    </ul>
                <?php endif; ?>

                <?php if ($kelurahan): ?>
                    <h2 class="subsection-title"><?= e(tpl(setting('sec.kelurahan.title'), ['count' => count($kelurahan)])) ?></h2>
                    <ul class="kelurahan-grid">
                        <?php foreach (array_slice($kelurahan, 0, 12) as $kel): ?>
                            <?php require __DIR__ . '/../includes/item_kelurahan.php'; ?>
                        <?php endforeach; ?>
                    </ul>
                    <?php if (count($kelurahan) > 12): ?>
                        <div class="section-action"><a href="<?= e(BASE_URL) ?>/kelurahan/" class="btn btn-solid"><?= e(setting('sec.kelurahan.more')) ?></a></div>
                    <?php endif; ?>
                <?php endif; ?>
            </div>
        </section>

<?php require __DIR__ . '/../includes/site_footer.php'; ?>
