<?php
require_once __DIR__ . '/includes/bootstrap.php';

$pdo = getConnection();

// Ambil berita terbaru berstatus 'published' untuk beranda (jumlahnya diatur di Admin).
$homeNewsCount = max(1, min(12, (int) setting('news.home_count', '3')));
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

// Ambil foto galeri aktif (dikelola Admin > Galeri).
$galleryPhotos = $pdo->query("
    SELECT id, image, caption, description
    FROM gallery
    WHERE status = 'active'
    ORDER BY created_at DESC
")->fetchAll();

// Ambil seluruh event kalender aktif (dikelola Admin > Kalender & Event) untuk
// disuntikkan ke widget kalender vanilla JS (assets/js/main.js). Satu tanggal bisa
// punya beberapa kegiatan, jadi dikelompokkan menjadi daftar per tanggal.
// Semua field di-escape di sini supaya aman langsung dipakai lewat innerHTML di JS.
$calendarEventsRaw = $pdo->query("
    SELECT event_date, event_time, title, location, description
    FROM events
    WHERE status = 'active'
    ORDER BY event_date ASC, event_time ASC
")->fetchAll();

$calendarEventsForJs = [];
foreach ($calendarEventsRaw as $ev) {
    $calendarEventsForJs[$ev['event_date']][] = [ // key YYYY-MM-DD, sesuai widget kalender
        'title'    => e($ev['title']),
        'desc'     => e($ev['description'] ?? ''),
        'time'     => $ev['event_time'] ? e(substr($ev['event_time'], 0, 5)) : '',
        'location' => e($ev['location'] ?? ''),
    ];
}

// Data section yang dikelola dari Admin > Konten Halaman.
$statItems      = contentItems('stats');
$potensiItems   = contentItems('potensi');
$kelurahanItems = contentItems('kelurahan');
$layananItems   = contentItems('layanan');

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
        <!-- POTENSI & DESTINASI WILAYAH -->
        <section class="section-padding text-center" id="potensi" aria-labelledby="potensi-title">
            <div class="container">
                <?php renderSectionHead('potensi'); ?>

                <div class="grid-3">
                    <?php foreach ($potensiItems as $card): ?>
                        <?php $cardLink = trim((string) $card['link_url']); ?>
                        <article class="card reveal<?= $cardLink !== '' ? ' is-link' : '' ?>">
                            <?php if (!empty($card['image'])): ?>
                                <img loading="lazy" decoding="async" width="600" height="400" src="<?= e(mediaUrl($card['image'])) ?>" alt="<?= e($card['title']) ?>" class="card-img">
                            <?php endif; ?>
                            <div class="card-body">
                                <h3>
                                    <?php if ($cardLink !== ''): ?>
                                        <a href="<?= e(resolveLink($cardLink)) ?>" class="stretched-link"><?= e($card['title']) ?></a>
                                    <?php else: ?>
                                        <?= e($card['title']) ?>
                                    <?php endif; ?>
                                </h3>
                                <p><?= e($card['description']) ?></p>
                            </div>
                        </article>
                    <?php endforeach; ?>
                </div>
            </div>
        </section>
        <?php endif; ?>

        <?php if (sectionOn('kelurahan') && $kelurahanItems): ?>
        <!-- KELURAHAN -->
        <section class="section-padding text-center" id="kelurahan" aria-labelledby="kelurahan-title">
            <div class="container">
                <?php renderSectionHead('kelurahan', ['count' => count($kelurahanItems)]); ?>
                <ul class="kelurahan-grid">
                    <?php foreach ($kelurahanItems as $kel): ?>
                        <li class="kelurahan-item reveal"><?= e($kel['title']) ?></li>
                    <?php endforeach; ?>
                </ul>
            </div>
        </section>
        <?php endif; ?>

        <?php if (sectionOn('layanan') && $layananItems): ?>
        <!-- LAYANAN PUBLIK -->
        <section class="section-padding layanan-bg text-center" id="layanan" aria-labelledby="layanan-title">
            <div class="container">
                <?php renderSectionHead('layanan'); ?>
                <div class="grid-4">
                    <?php foreach ($layananItems as $svc): ?>
                        <?php $svcLink = trim((string) $svc['link_url']); ?>
                        <div class="service-box reveal<?= $svcLink !== '' ? ' is-link' : '' ?>">
                            <?php if (!empty($svc['icon'])): ?>
                                <div class="service-icon" aria-hidden="true"><?= e($svc['icon']) ?></div>
                            <?php endif; ?>
                            <h3><?= e($svc['title']) ?></h3>
                            <p><?= e($svc['description']) ?></p>
                            <?php if ($svcLink !== ''): ?>
                                <a href="<?= e(resolveLink($svcLink)) ?>" class="service-link stretched-link"><?= e($svc['value'] !== null && $svc['value'] !== '' ? $svc['value'] : $svc['title']) ?></a>
                            <?php endif; ?>
                        </div>
                    <?php endforeach; ?>
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
                            <button type="button" class="gallery-item reveal" data-full="<?= e(GALLERY_UPLOAD_URL . $photo['image']) ?>" data-caption="<?= e($photo['caption']) ?>" aria-label="Perbesar foto: <?= e($photo['caption']) ?>">
                                <img loading="lazy" decoding="async" src="<?= e(GALLERY_UPLOAD_URL . $photo['image']) ?>" alt="<?= e($photo['caption']) ?>">
                                <span class="gallery-overlay" aria-hidden="true">🔍</span>
                            </button>
                        <?php endforeach; ?>
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

                <script>window.CALENDAR_EVENTS = <?= json_encode($calendarEventsForJs, JSON_UNESCAPED_UNICODE | JSON_HEX_TAG | JSON_HEX_AMP) ?>;</script>

                <div class="kalender-wrapper">
                    <div class="kalender-container">
                        <div class="calendar-header">
                            <h3 id="calendar-month"><?= e(preg_replace('/^\d+\s+/', '', formatTanggalIndo(date('Y-m-d')))) ?></h3>
                            <div class="calendar-nav">
                                <button type="button" id="prev-month" aria-label="Bulan sebelumnya">&lt;</button>
                                <button type="button" id="next-month" aria-label="Bulan berikutnya">&gt;</button>
                            </div>
                        </div>
                        <div class="calendar-weekdays" aria-hidden="true">
                            <div class="calendar-weekday">Min</div>
                            <div class="calendar-weekday">Sen</div>
                            <div class="calendar-weekday">Sel</div>
                            <div class="calendar-weekday">Rab</div>
                            <div class="calendar-weekday">Kam</div>
                            <div class="calendar-weekday">Jum</div>
                            <div class="calendar-weekday">Sab</div>
                        </div>
                        <div class="calendar-days" id="calendar-days" aria-label="Kalender tanggal"></div>
                    </div>

                    <div class="events-list">
                        <h3><span aria-hidden="true">📋</span> <?= e(setting('calendar.upcoming_title')) ?></h3>
                        <div id="events-container"></div>
                    </div>
                </div>
            </div>
        </section>
        <?php endif; ?>

        <?php if (sectionOn('aspirasi')): ?>
        <!-- ASPIRASI / PENGADUAN -->
        <section class="section-padding text-center" id="aspirasi" aria-labelledby="aspirasi-title">
            <div class="container">
                <?php renderSectionHead('aspirasi'); ?>

                <div class="form-container">
                    <div class="alert" id="successAlert" role="alert">
                        ✅ <?= e(setting('aspirasi.success_message')) ?>
                    </div>
                    <form id="complaintForm">
                        <div class="form-group">
                            <label for="nama">Nama Lengkap <span aria-hidden="true">*</span></label>
                            <input type="text" id="nama" name="name" autocomplete="name" maxlength="100" placeholder="Masukkan nama Anda" required aria-required="true">
                        </div>
                        <div class="form-group">
                            <label for="email">Alamat Email / WhatsApp <span aria-hidden="true">*</span></label>
                            <input type="text" id="email" name="contact" autocomplete="email" maxlength="150" placeholder="Email atau Nomor WA aktif" required aria-required="true">
                        </div>
                        <div class="form-group">
                            <label for="kelurahanSelect">Pilih Kelurahan Domisili <span aria-hidden="true">*</span></label>
                            <select id="kelurahanSelect" name="kelurahan" required aria-required="true">
                                <option value="">-- Pilih Kelurahan --</option>
                                <?php foreach ($kelurahanItems as $kel): ?>
                                    <option value="<?= e($kel['title']) ?>"><?= e($kel['title']) ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="form-group">
                            <label for="pesan">Pesan Pengaduan / Aspirasi <span aria-hidden="true">*</span></label>
                            <textarea id="pesan" name="message" rows="4" placeholder="Tuliskan keluhan atau pengaduan Anda secara rinci..." required aria-required="true"></textarea>
                        </div>
                        <button type="submit" class="submit-btn"><?= e(setting('aspirasi.button_label')) ?></button>
                    </form>
                </div>
            </div>
        </section>
        <?php endif; ?>

<?php require __DIR__ . '/includes/site_footer.php'; ?>
