<?php
require_once __DIR__ . '/includes/bootstrap.php';

$pdo = getConnection();

// Ambil berita terbaru berstatus 'published' untuk beranda (jumlahnya diatur di Admin).
$homeNewsCount = homeCount('news.home_count');
$stmt = $pdo->prepare("
    SELECT n.id, n.title, n.slug, n.excerpt, n.thumbnail, n.published_at, c.name AS category_name
    FROM news n
    LEFT JOIN categories c ON c.id = n.category_id
    WHERE n.status = 'published'
    ORDER BY n.published_at DESC
    LIMIT :limit
");
$stmt->bindValue(':limit', $homeNewsCount, PDO::PARAM_INT);
$stmt->execute();
$latestNews = $stmt->fetchAll();

// Ambil beberapa foto galeri aktif terbaru (dikelola Admin > Galeri). Semua foto ada di /galeri/.
$stmt = $pdo->prepare("
    SELECT id, image, caption, description
    FROM gallery
    WHERE status = 'active'
    ORDER BY created_at DESC, id DESC
    LIMIT :limit
");
$stmt->bindValue(':limit', homeCount('home.galeri_count', 4), PDO::PARAM_INT);
$stmt->execute();
$galleryPhotos = $stmt->fetchAll();

// Seluruh agenda aktif disuntikkan ke widget kalender (assets/js/main.js) agar tanggal ber-event
// tetap bertanda di kalender; DAFTAR agenda di sebelahnya dibatasi $calendarLimit (3–4 item) dan
// sisanya ada di /kalender/. Semua field di-escape di calendarEventsForJs().
$calendarEventsForJs = calendarEventsForJs($pdo);
$calendarLimit       = homeCount('home.kalender_count', 4);

// Data section yang dikelola dari Admin > Konten Halaman.
$statItems      = contentItems('stats');
$potensiAll     = contentItems('potensi');
$kelurahanItems = contentItems('kelurahan'); // lengkap: juga untuk dropdown form aspirasi
$layananAll     = contentItems('layanan');

// Beranda hanya memuat 3–4 item per kategori (jumlahnya diatur di Admin → Modul & Jumlah Item);
// seluruh isinya ada di halaman daftar masing-masing.
$potensiItems     = array_slice($potensiAll, 0, homeCount('home.potensi_count', 3));
$kelurahanPreview = array_slice($kelurahanItems, 0, homeCount('home.kelurahan_count', 3));
$layananItems     = array_slice($layananAll, 0, homeCount('home.layanan_count', 4));

$heroImage = mediaUrl(setting('hero.image'));

$navActive       = 'home';
$preloadImage    = $heroImage;
require __DIR__ . '/includes/site_header.php';
?>

        <!-- HERO SECTION -->
        <section class="hero" id="beranda" aria-labelledby="hero-title"<?= $heroImage !== '' ? ' style="--hero-image:url(\'' . e(cssUrl($heroImage)) . '\')"' : '' ?>>
            <div class="container">
                <div class="hero-content">
                    <?php if (setting('hero.badge') !== ''): ?>
                        <span class="hero-badge"><?= e(setting('hero.badge')) ?></span>
                    <?php endif; ?>
                    <h1 id="hero-title"><?= e(setting('hero.title')) ?> <span><?= e(setting('hero.highlight')) ?></span></h1>
                    <p><?= e(setting('hero.description')) ?></p>
                    <div class="hero-btns">
                        <?php if (setting('hero.btn1_label') !== ''): ?>
                            <a href="<?= e(resolveLink(setting('hero.btn1_link'))) ?>" class="btn btn-main"><?= e(setting('hero.btn1_label')) ?></a>
                        <?php endif; ?>
                        <?php if (setting('hero.btn2_label') !== ''): ?>
                            <a href="<?= e(resolveLink(setting('hero.btn2_link'))) ?>" class="btn btn-sub"><?= e(setting('hero.btn2_label')) ?></a>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </section>

        <?php if (sectionOn('stats') && $statItems): ?>
        <!-- STATISTIK -->
        <section class="stats" aria-label="Statistik wilayah">
            <div class="container">
                <ul class="stats-grid">
                    <?php foreach ($statItems as $stat): ?>
                        <li class="stat-item">
                            <p class="stat-value"><?= e($stat['value']) ?></p>
                            <p class="stat-label"><?= e($stat['title']) ?></p>
                        </li>
                    <?php endforeach; ?>
                </ul>
            </div>
        </section>
        <?php endif; ?>

        <?php if (sectionOn('potensi') && $potensiItems): ?>
        <!-- POTENSI & DESTINASI WILAYAH (maks. 3–4 kartu; selengkapnya di /potensi/) -->
        <section class="section-padding text-center" id="potensi" aria-labelledby="potensi-title">
            <div class="container">
                <?php renderSectionHead('potensi'); ?>

                <div class="grid-3">
                    <?php foreach ($potensiItems as $card): ?>
                        <?php require __DIR__ . '/includes/card_potensi.php'; ?>
                    <?php endforeach; ?>
                </div>
                <div class="section-action">
                    <a href="<?= e(BASE_URL) ?>/potensi/" class="btn btn-solid"><?= e(setting('sec.potensi.more')) ?></a>
                </div>
            </div>
        </section>
        <?php endif; ?>

        <?php if (sectionOn('kelurahan') && $kelurahanItems): ?>
        <!-- KELURAHAN (maks. 3–4; selengkapnya di /kelurahan/) -->
        <section class="section-padding text-center" id="kelurahan" aria-labelledby="kelurahan-title">
            <div class="container">
                <?php renderSectionHead('kelurahan', ['count' => count($kelurahanItems)]); ?>
                <ul class="kelurahan-grid">
                    <?php foreach ($kelurahanPreview as $kel): ?>
                        <?php require __DIR__ . '/includes/item_kelurahan.php'; ?>
                    <?php endforeach; ?>
                </ul>
                <div class="section-action">
                    <a href="<?= e(BASE_URL) ?>/kelurahan/" class="btn btn-solid"><?= e(setting('sec.kelurahan.more')) ?></a>
                </div>
            </div>
        </section>
        <?php endif; ?>

        <?php if (sectionOn('layanan') && $layananItems): ?>
        <!-- LAYANAN PUBLIK (maks. 4 kartu; selengkapnya di /layanan/) -->
        <section class="section-padding layanan-bg text-center" id="layanan" aria-labelledby="layanan-title">
            <div class="container">
                <?php renderSectionHead('layanan'); ?>
                <div class="grid-4">
                    <?php foreach ($layananItems as $svc): ?>
                        <?php require __DIR__ . '/includes/card_layanan.php'; ?>
                    <?php endforeach; ?>
                </div>
                <div class="section-action">
                    <a href="<?= e(BASE_URL) ?>/layanan/" class="btn btn-solid"><?= e(setting('sec.layanan.more')) ?></a>
                </div>
            </div>
        </section>
        <?php endif; ?>

        <?php if (sectionOn('berita')): ?>
        <!-- BERITA SECTION (DINAMIS DARI DATABASE) -->
        <section class="section-padding text-center" id="berita" aria-labelledby="berita-title">
            <div class="container">
                <?php renderSectionHead('berita'); ?>

                <?php if (empty($latestNews)): ?>
                    <p class="empty-note"><?= e(setting('news.empty_text')) ?></p>
                <?php else: ?>
                    <div class="berita-grid">
                        <?php foreach ($latestNews as $item): ?>
                            <?php require __DIR__ . '/includes/news_card.php'; ?>
                        <?php endforeach; ?>
                    </div>
                    <div class="section-action">
                        <a href="<?= e(BASE_URL) ?>/news/" class="btn btn-solid"><?= e(setting('news.button_label')) ?></a>
                    </div>
                <?php endif; ?>
            </div>
        </section>
        <?php endif; ?>

        <?php if (sectionOn('galeri')): ?>
        <!-- GALERI SECTION -->
        <section class="section-padding layanan-bg text-center" id="galeri" aria-labelledby="galeri-title">
            <div class="container">
                <?php renderSectionHead('galeri'); ?>

                <?php if (empty($galleryPhotos)): ?>
                    <p class="empty-note"><?= e(setting('gallery.empty_text')) ?></p>
                <?php else: ?>
                    <div class="gallery-grid">
                        <?php foreach ($galleryPhotos as $photo): ?>
                            <?php require __DIR__ . '/includes/item_gallery.php'; ?>
                        <?php endforeach; ?>
                    </div>
                    <div class="section-action">
                        <a href="<?= e(BASE_URL) ?>/galeri/" class="btn btn-solid"><?= e(setting('sec.galeri.more')) ?></a>
                    </div>
                <?php endif; ?>
            </div>
        </section>
        <?php endif; ?>

        <?php if (sectionOn('kalender')): ?>
        <!-- KALENDER SECTION -->
        <section class="section-padding text-center" id="kalender" aria-labelledby="kalender-title">
            <div class="container">
                <?php renderSectionHead('kalender'); ?>

                <?php require __DIR__ . '/includes/calendar_widget.php'; ?>

                <div class="section-action">
                    <a href="<?= e(BASE_URL) ?>/kalender/" class="btn btn-solid"><?= e(setting('sec.kalender.more')) ?></a>
                </div>
            </div>
        </section>
        <?php endif; ?>

        <?php if (sectionOn('aspirasi')): ?>
        <!-- ASPIRASI / PENGADUAN -->
        <section class="section-padding text-center" id="aspirasi" aria-labelledby="aspirasi-title">
            <div class="container">
                <?php renderSectionHead('aspirasi'); ?>

                <?php require __DIR__ . '/includes/aspirasi_form.php'; ?>
            </div>
        </section>
        <?php endif; ?>

<?php require __DIR__ . '/includes/site_footer.php'; ?>
