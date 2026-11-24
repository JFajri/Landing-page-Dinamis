<?php
/**
 * visi-misi/index.php — Halaman statis Visi & Misi.
 * Visi: Admin → Tampilan Website → Halaman Profil. Misi (daftar bernomor): Admin → Konten
 * Halaman → Misi.
 */
require_once __DIR__ . '/../includes/bootstrap.php';

$vision = setting('page.visi.vision');
$misi   = contentItems('misi');

$pageTitle       = setting('page.visi.title') . ' - ' . setting('site.name');
$pageDescription = setting('page.visi.desc');
$navActive       = 'visi-misi';
$canonicalUrl    = BASE_URL . '/visi-misi/';
require __DIR__ . '/../includes/site_header.php';
?>
        <section class="section-padding text-center page-top" id="visi-misi" aria-labelledby="page-title">
            <div class="container">
                <?php renderPageHeading('visi'); ?>
                <?php renderProfilTabs('visi-misi'); ?>

                <?php if ($vision === '' && !$misi): ?>
                    <?php renderEmptyNote(); ?>
                <?php endif; ?>

                <?php if ($vision !== ''): ?>
                    <div class="vision-card">
                        <h2 class="vision-label"><?= e(setting('page.visi.vision_title')) ?></h2>
                        <p class="vision-text"><?= nl2br(e($vision)) ?></p>
                    </div>
                <?php endif; ?>

                <?php if ($misi): ?>
                    <h2 class="subsection-title"><?= e(setting('page.visi.misi_title')) ?></h2>
                    <ol class="misi-list">
                        <?php foreach ($misi as $i => $row): ?>
                            <li class="misi-item reveal">
                                <span class="misi-no" aria-hidden="true"><?= $i + 1 ?></span>
                                <div>
                                    <h3><?= e($row['title']) ?></h3>
                                    <?php if (trim((string) $row['description']) !== ''): ?>
                                        <p><?= e($row['description']) ?></p>
                                    <?php endif; ?>
                                </div>
                            </li>
                        <?php endforeach; ?>
                    </ol>
                <?php endif; ?>
            </div>
        </section>

<?php require __DIR__ . '/../includes/site_footer.php'; ?>
