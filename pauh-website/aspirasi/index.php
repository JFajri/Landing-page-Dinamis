<?php
/**
 * aspirasi/index.php — Halaman khusus Aspirasi & Pengaduan: formulir lengkap + informasi
 * alur penanganan dan kontak kantor. (Laporan warga bersifat pribadi, jadi TIDAK ditampilkan
 * ke publik; hanya admin yang dapat melihatnya di Admin → Aspirasi & Pengaduan.)
 * Kirim lewat aspirasi/submit.php (fetch, tanpa memuat ulang halaman).
 */
require_once __DIR__ . '/../includes/bootstrap.php';
requireSectionOn('aspirasi', 'aspirasi');

$kelurahanItems     = contentItems('kelurahan');
$preselectKelurahan = clean($_GET['kelurahan'] ?? '');
$hours              = contentItems('hours');
$email              = setting('footer.email');
$phone              = setting('footer.phone');

$pageTitle       = setting('sec.aspirasi.title') . ' - ' . setting('site.name');
$pageDescription = setting('sec.aspirasi.desc');
$navActive       = 'aspirasi';
$canonicalUrl    = BASE_URL . '/aspirasi/';
require __DIR__ . '/../includes/site_header.php';
?>
        <section class="section-padding text-center page-top" id="aspirasi" aria-labelledby="aspirasi-title">
            <div class="container">
                <?php renderSectionHead('aspirasi', [], 'h1'); ?>

                <div class="aspirasi-layout">
                    <div class="aspirasi-form-col">
                        <?php require __DIR__ . '/../includes/aspirasi_form.php'; ?>
                    </div>

                    <aside class="detail-aside aspirasi-info" aria-labelledby="alur-title">
                        <h2 class="aside-title" id="alur-title">Alur Penanganan</h2>
                        <ol class="steps">
                            <li><strong>Kirim laporan</strong><span>Isi formulir dengan data diri dan uraian yang jelas.</span></li>
                            <li><strong>Dibaca petugas</strong><span>Petugas kecamatan meninjau laporan Anda.</span></li>
                            <li><strong>Diproses</strong><span>Laporan ditindaklanjuti atau diteruskan ke pihak terkait.</span></li>
                            <li><strong>Anda dihubungi</strong><span>Tanggapan disampaikan lewat email/WhatsApp yang Anda isi.</span></li>
                        </ol>

                        <h2 class="aside-title aside-title-sub" id="kontak-title">Datang Langsung</h2>
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
                    </aside>
                </div>
            </div>
        </section>

<?php require __DIR__ . '/../includes/site_footer.php'; ?>
