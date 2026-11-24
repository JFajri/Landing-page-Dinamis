<?php
/**
 * layanan/detail.php?id=N — Halaman detail satu layanan: uraian, syarat/prosedur dari Admin
 * ("Isi Halaman Detail"), serta informasi pelayanan (alamat, jam operasional, kontak) yang
 * diambil dari pengaturan situs sehingga selalu sama dengan footer.
 */
require_once __DIR__ . '/../includes/bootstrap.php';
requireSectionOn('layanan', 'layanan');

$item = contentItem('layanan', (int) ($_GET['id'] ?? 0));
if (!$item) {
    renderNotFound('← Kembali ke Layanan', '/layanan/', 'layanan');
}

$others = array_values(array_filter(contentItems('layanan'), fn($row) => (int) $row['id'] !== (int) $item['id']));
$hours  = contentItems('hours');
$email  = setting('footer.email');
$phone  = setting('footer.phone');
$custom = trim((string) $item['link_url']);

$pageTitle       = $item['title'] . ' - ' . setting('site.name');
$pageDescription = trim((string) $item['description']) !== '' ? makeExcerpt((string) $item['description'], 155) : makeExcerpt((string) $item['content'], 155);
$navActive       = 'layanan';
$canonicalUrl    = contentDetailUrl('layanan', (int) $item['id']);
require __DIR__ . '/../includes/site_header.php';
?>
        <section class="section-padding page-top" id="layanan-detail" aria-labelledby="detail-title">
            <div class="container">
                <?php renderBreadcrumb([['Beranda', '/'], [setting('sec.layanan.title'), '/layanan/'], [$item['title'], null]]); ?>

                <div class="detail-layout">
                    <article class="detail-main">
                        <div class="eyebrow"><?= e(setting('sec.layanan.eyebrow')) ?></div>
                        <h1 class="detail-title" id="detail-title">
                            <?php if (!empty($item['icon'])): ?><span class="detail-icon" aria-hidden="true"><?= e($item['icon']) ?></span><?php endif; ?>
                            <?= e($item['title']) ?>
                        </h1>
                        <?php if (trim((string) $item['description']) !== ''): ?>
                            <p class="detail-lead"><?= e($item['description']) ?></p>
                        <?php endif; ?>
                        <?php if (trim((string) ($item['content'] ?? '')) !== ''): ?>
                            <div class="prose"><?= renderRichText($item['content']) ?></div>
                        <?php else: ?>
                            <p class="detail-note">Rincian persyaratan dan prosedur layanan ini dapat ditanyakan langsung kepada petugas di kantor kecamatan, atau lewat formulir aspirasi di situs ini.</p>
                        <?php endif; ?>

                        <p class="detail-actions">
                            <?php if ($custom !== ''): ?>
                                <a href="<?= e(resolveLink($custom)) ?>" class="btn btn-main"><?= e(trim((string) $item['value']) !== '' ? $item['value'] : 'Buka Layanan →') ?></a>
                            <?php endif; ?>
                            <?php if (sectionOn('aspirasi')): ?>
                                <a href="<?= e(BASE_URL) ?>/aspirasi/" class="btn <?= $custom !== '' ? 'btn-solid' : 'btn-main' ?>">Tanyakan lewat Aspirasi</a>
                            <?php endif; ?>
                        </p>
                    </article>

                    <aside class="detail-aside" aria-labelledby="info-title">
                        <h2 class="aside-title" id="info-title">Informasi Pelayanan</h2>
                        <p class="aside-text"><strong><?= e(setting('footer.office_title')) ?></strong><br><?= nl2br(e(setting('footer.address'))) ?></p>
                        <?php if ($hours): ?>
                            <ul class="aside-hours">
                                <?php foreach ($hours as $row): ?>
                                    <li><strong><?= e($row['title']) ?></strong><span><?= e($row['value']) ?></span></li>
                                <?php endforeach; ?>
                            </ul>
                        <?php endif; ?>
                        <?php if ($email !== '' || $phone !== ''): ?>
                            <p class="aside-text">
                                <?php if ($email !== ''): ?><a href="mailto:<?= e($email) ?>"><?= e($email) ?></a><?php endif; ?>
                                <?php if ($email !== '' && $phone !== ''): ?><br><?php endif; ?>
                                <?php if ($phone !== ''): ?><a href="tel:<?= e(preg_replace('/[^0-9+]/', '', $phone)) ?>"><?= e($phone) ?></a><?php endif; ?>
                            </p>
                        <?php endif; ?>

                        <?php if ($others): ?>
                            <h2 class="aside-title aside-title-sub" id="others-title">Layanan Lainnya</h2>
                            <ul class="aside-list" aria-labelledby="others-title">
                                <?php foreach ($others as $other): ?>
                                    <li><a href="<?= e(contentDetailUrl('layanan', (int) $other['id'])) ?>"><?= e($other['title']) ?></a></li>
                                <?php endforeach; ?>
                            </ul>
                        <?php endif; ?>
                    </aside>
                </div>

                <div class="section-action section-action-lg text-center">
                    <a href="<?= e(BASE_URL) ?>/layanan/" class="btn btn-solid">← <?= e(setting('sec.layanan.more')) ?></a>
                </div>
            </div>
        </section>

<?php require __DIR__ . '/../includes/site_footer.php'; ?>
