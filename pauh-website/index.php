<?php
require_once __DIR__ . '/includes/bootstrap.php';

$pdo = getConnection();

// Ambil 3 berita terbaru berstatus 'published' untuk ditampilkan di beranda.
$stmt = $pdo->query("
    SELECT n.id, n.title, n.slug, n.excerpt, n.thumbnail, n.published_at, c.name AS category_name
    FROM news n
    LEFT JOIN categories c ON c.id = n.category_id
    WHERE n.status = 'published'
    ORDER BY n.published_at DESC
    LIMIT 3
");
$latestNews = $stmt->fetchAll();

// Ambil foto galeri aktif (dikelola Admin > Galeri).
$galleryPhotos = $pdo->query("
    SELECT id, image, caption, description
    FROM gallery
    WHERE status = 'active'
    ORDER BY created_at DESC
")->fetchAll();

// Ambil seluruh event kalender aktif (dikelola Admin > Kalender & Event) untuk
// disuntikkan ke widget kalender vanilla JS yang sudah ada (assets/js/main.js).
// Semua field di-escape di sini supaya aman langsung dipakai lewat innerHTML di JS.
$calendarEventsRaw = $pdo->query("
    SELECT event_date, event_time, title, location, description
    FROM events
    WHERE status = 'active'
")->fetchAll();

$calendarEventsForJs = [];
foreach ($calendarEventsRaw as $ev) {
    $dateKey = $ev['event_date']; // format YYYY-MM-DD, sesuai key yang dipakai widget kalender
    $calendarEventsForJs[$dateKey] = [
        'title'    => e($ev['title']),
        'desc'     => e($ev['description'] ?? ''),
        'time'     => $ev['event_time'] ? e(substr($ev['event_time'], 0, 5)) : '',
        'location' => e($ev['location'] ?? ''),
    ];
}

$pageTitle       = 'Portal Resmi Kecamatan Pauh - Kota Padang | Layanan Publik Terpercaya';
$pageDescription = 'Portal Resmi Kecamatan Pauh, Kota Padang. Pusat pendidikan tinggi dengan layanan administrasi warga yang transparan dan mudah diakses.';
require __DIR__ . '/includes/site_header.php';
?>

        <!-- HERO SECTION -->
        <section class="hero" id="beranda" aria-label="Bagian pengenalan Kecamatan Pauh">
            <div class="container">
                <div class="hero-content">
                    <span class="hero-badge">Portal Resmi Pelayanan Publik</span>
                    <h1>Selamat Datang di <span>Kecamatan Pauh</span></h1>
                    <p>Pusat Pendidikan Tinggi, Keasrian Alam Bukit Barisan, dan pelayanan masyarakat yang mudah, informatif, dan transparan.</p>
                    <div class="hero-btns">
                        <a href="#layanan" class="btn btn-main">Jelajahi Layanan</a>
                        <a href="#aspirasi" class="btn btn-sub">Kirim Pengaduan</a>
                    </div>
                </div>
            </div>
        </section>

        <!-- STATISTIK PAUH -->
        <section class="stats reveal" aria-label="Statistik Kecamatan Pauh">
            <div class="container">
                <div class="stats-grid" role="region" aria-label="Ringkas statistik">
                    <div class="stat-item">
                        <h3>~60K</h3>
                        <p>Estimasi Penduduk</p>
                    </div>
                    <div class="stat-item">
                        <h3>9</h3>
                        <p>Kelurahan</p>
                    </div>
                    <div class="stat-item">
                        <h3>146.43 ㎢</h3>
                        <p>Luas Wilayah</p>
                    </div>
                    <div class="stat-item">
                        <h3>1</h3>
                        <p>Kampus Utama Unand</p>
                    </div>
                </div>
            </div>
        </section>

        <!-- POTENSI & DESTINASI WILAYAH -->
        <section class="section-padding text-center" id="potensi" aria-label="Potensi dan destinasi wisata Pauh">
            <div class="container">
                <div class="eyebrow">Mengenal Pauh</div>
                <h2 class="section-title">Pusat Pendidikan & Destinasi Asri</h2>
                <p class="section-desc">Mengenal lebih dekat kawasan ikonik, wisata lokal, dan infrastruktur pendidikan yang ada di Kecamatan Pauh.</p>

                <div class="grid-3">
                    <article class="card reveal">
                        <img src="https://fk.unand.ac.id/wp-content/uploads/2023/12/AERIAL-VIEW-FK-UNAND-MEDIUM.jpg" alt="Kampus Universitas Andalas (Unand) di Pauh" class="card-img">
                        <div class="card-body">
                            <h3>Universitas Andalas (Unand)</h3>
                            <p>Kawasan perguruan tinggi tertua di luar Pulau Jawa yang berlokasi di perbukitan Limau Manis dengan lanskap pemandangan indah Kota Padang.</p>
                        </div>
                    </article>

                    <article class="card reveal">
                        <img src="https://blogger.googleusercontent.com/img/b/R29vZ2xl/AVvXsEgYvzCjZNOMuFHaJlUezNN5kzBA31cbVXwg9-YqlWEw2tArwcf84_qid_N54NXbSdZBgMnpwMfzU-b0GefbjbwOYH7aTEoCfoqht8ra1Uq4NYIoQfIrJnBW_bEr6LpDq-JaUaA_wAgLU_NUOO0ufKvSf7GsCzHS-CF27PXCQIAJ-K2Yh-R_1E4HEN5iqxaZ/s1556/490743928_1388968985622464_7085951840122729520_n.jpg" alt="Wisata alam dan jembatan gantung Batu Busuk" class="card-img">
                        <div class="card-body">
                            <h3>Wisata Alam & Sungai Batu Busuk</h3>
                            <p>Destinasi pemandian alam dan jembatan gantung ikonik dengan aliran sungai jernih di kaki bukit wilayah Kelurahan Lambung Bukit.</p>
                        </div>
                    </article>

                    <article class="card reveal">
                        <img src="https://i0.wp.com/padangkita.com/wp-content/uploads/2023/01/IMG_9469.jpg?resize=750%2C487&ssl=1" alt="Agrowisata dan perkebunan di Kecamatan Pauh" class="card-img">
                        <div class="card-body">
                            <h3>Agrowisata & Lingkungan Asri</h3>
                            <p>Kecamatan Pauh kaya akan komoditas buah lokal seperti durian pauh, perbukitan hijau, serta aliran sungai penopang irigasi daerah.</p>
                        </div>
                    </article>
                </div>
            </div>
        </section>

        <!-- KELURAHAN -->
        <section class="section-padding text-center" id="kelurahan" aria-label="Daftar kelurahan di Kecamatan Pauh">
            <div class="container">
                <div class="eyebrow">Wilayah Administratif</div>
                <h2 class="section-title">9 Kelurahan di Kecamatan Pauh</h2>
                <p class="section-desc">Kecamatan Pauh membawahi 9 wilayah kelurahan administratif Kota Padang.</p>
                <div class="kelurahan-grid" role="list">
                    <div class="kelurahan-item reveal" role="listitem" tabindex="0">Limau Manis</div>
                    <div class="kelurahan-item reveal" role="listitem" tabindex="0">Limau Manis Selatan</div>
                    <div class="kelurahan-item reveal" role="listitem" tabindex="0">Kapalo Koto</div>
                    <div class="kelurahan-item reveal" role="listitem" tabindex="0">Koto Luar</div>
                    <div class="kelurahan-item reveal" role="listitem" tabindex="0">Lambung Bukit</div>
                    <div class="kelurahan-item reveal" role="listitem" tabindex="0">Piai Tangah</div>
                    <div class="kelurahan-item reveal" role="listitem" tabindex="0">Bintungan / Bitinggi Taba</div>
                    <div class="kelurahan-item reveal" role="listitem" tabindex="0">Cupak Tangah</div>
                    <div class="kelurahan-item reveal" role="listitem" tabindex="0">Pisang</div>
                </div>
            </div>
        </section>

        <!-- LAYANAN PUBLIK -->
        <section class="section-padding layanan-bg text-center" id="layanan" aria-label="Layanan administrasi publik">
            <div class="container">
                <div class="eyebrow">Layanan Utama</div>
                <h2 class="section-title">Layanan Administrasi Warga</h2>
                <p class="section-desc">Kemudahan akses administrasi kependudukan dan perizinan bagi warga Kecamatan Pauh.</p>
                <div class="grid-4">
                    <div class="service-box reveal">
                        <div class="service-icon" aria-hidden="true">🪪</div>
                        <h3>KTP-el & KIA</h3>
                        <p>Perekaman baru, penggantian rusak/hilang, dan pengurusan Kartu Identitas Anak.</p>
                        <a href="#aspirasi" class="service-link">Konsultasi Syarat →</a>
                    </div>
                    <div class="service-box reveal">
                        <div class="service-icon" aria-hidden="true">📜</div>
                        <h3>Kartu Keluarga</h3>
                        <p>Pembaruan data KK, penambahan anggota keluarga, atau pemisahan KK baru.</p>
                        <a href="#aspirasi" class="service-link">Konsultasi Syarat →</a>
                    </div>
                    <div class="service-box reveal">
                        <div class="service-icon" aria-hidden="true">✉️</div>
                        <h3>Surat Pindah Domisili</h3>
                        <p>Pengurusan surat pengantar pindah masuk atau keluar wilayah Pauh.</p>
                        <a href="#aspirasi" class="service-link">Konsultasi Syarat →</a>
                    </div>
                    <div class="service-box reveal">
                        <div class="service-icon" aria-hidden="true">🤝</div>
                        <h3>Rekomendasi UMKM</h3>
                        <p>Surat keterangan usaha (SKU) untuk legalitas usaha mikro & kecil lokal.</p>
                        <a href="#aspirasi" class="service-link">Konsultasi Syarat →</a>
                    </div>
                </div>
            </div>
        </section>

        <!-- BERITA SECTION (DINAMIS DARI DATABASE) -->
        <section class="section-padding text-center" id="berita" aria-label="Berita terkini Kecamatan Pauh">
            <div class="container">
                <div class="eyebrow">Informasi Terbaru</div>
                <h2 class="section-title">Berita & Pengumuman</h2>
                <p class="section-desc">Ikuti perkembangan terbaru, pengumuman penting, dan kegiatan di Kecamatan Pauh.</p>

                <?php if (empty($latestNews)): ?>
                    <p style="color:var(--muted);font-size:14px;margin-top:44px">Belum ada berita yang dipublikasikan.</p>
                <?php else: ?>
                    <div class="berita-grid" role="list">
                        <?php foreach ($latestNews as $item): ?>
                            <article class="berita-card reveal" role="listitem">
                                <img src="<?= $item['thumbnail'] ? e(UPLOAD_URL . $item['thumbnail']) : 'https://images.unsplash.com/photo-1552664730-d307ca884978?auto=format&fit=crop&w=600&q=80' ?>" alt="<?= e($item['title']) ?>" class="berita-img">
                                <div class="berita-meta">
                                    <span aria-label="Tanggal">📅 <?= e(formatTanggalIndo($item['published_at'])) ?></span>
                                    <?php if ($item['category_name']): ?>
                                        <span class="berita-badge"><?= e($item['category_name']) ?></span>
                                    <?php endif; ?>
                                </div>
                                <div class="berita-body">
                                    <h3><?= e($item['title']) ?></h3>
                                    <p><?= e($item['excerpt']) ?></p>
                                    <a href="<?= e(BASE_URL) ?>/news/detail.php?slug=<?= urlencode($item['slug']) ?>" class="berita-link">Baca Selengkapnya →</a>
                                </div>
                            </article>
                        <?php endforeach; ?>
                    </div>
                    <div style="margin-top:36px">
                        <a href="<?= e(BASE_URL) ?>/news/" class="btn btn-sub" style="background:var(--primary-2);color:#fff">Lihat Semua Berita</a>
                    </div>
                <?php endif; ?>
            </div>
        </section>

        <!-- GALERI SECTION -->
        <section class="section-padding layanan-bg text-center" id="galeri" aria-label="Galeri foto kegiatan Kecamatan Pauh">
            <div class="container">
                <div class="eyebrow">Visual Dokumentasi</div>
                <h2 class="section-title">Galeri Kegiatan</h2>
                <p class="section-desc">Jelajahi dokumentasi foto kegiatan sosial, pembangunan infrastruktur, dan acara-acara penting di Kecamatan Pauh.</p>

                <?php if (empty($galleryPhotos)): ?>
                    <p style="color:var(--muted);font-size:14px;margin-top:44px">Belum ada foto galeri yang ditampilkan.</p>
                <?php else: ?>
                    <div class="gallery-grid" role="region" aria-label="Galeri foto">
                        <?php foreach ($galleryPhotos as $photo): ?>
                            <div class="gallery-item reveal" tabindex="0" role="button" aria-label="<?= e($photo['caption']) ?>">
                                <img src="<?= e(GALLERY_UPLOAD_URL . $photo['image']) ?>" alt="<?= e($photo['caption']) ?>">
                                <div class="gallery-overlay" aria-hidden="true">🔍</div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>
            </div>
        </section>

        <!-- KALENDER SECTION -->
        <section class="section-padding text-center" id="kalender" aria-label="Kalender kegiatan dan acara Kecamatan Pauh">
            <div class="container">
                <div class="eyebrow">Jadwal Kegiatan</div>
                <h2 class="section-title">Kalender Event & Kegiatan</h2>
                <p class="section-desc">Pantau jadwal kegiatan publik, acara sosial, dan event penting di Kecamatan Pauh untuk tahun ini.</p>

                <script>window.CALENDAR_EVENTS = <?= json_encode($calendarEventsForJs, JSON_UNESCAPED_UNICODE) ?>;</script>

                <div class="kalender-wrapper">
                    <div class="kalender-container">
                        <div class="calendar-header">
                            <h3 id="calendar-month">Agustus 2026</h3>
                            <div class="calendar-nav">
                                <button id="prev-month" aria-label="Bulan sebelumnya">&lt;</button>
                                <button id="next-month" aria-label="Bulan berikutnya">&gt;</button>
                            </div>
                        </div>
                        <div class="calendar-weekdays" role="presentation">
                            <div class="calendar-weekday">Minggu</div>
                            <div class="calendar-weekday">Senin</div>
                            <div class="calendar-weekday">Selasa</div>
                            <div class="calendar-weekday">Rabu</div>
                            <div class="calendar-weekday">Kamis</div>
                            <div class="calendar-weekday">Jumat</div>
                            <div class="calendar-weekday">Sabtu</div>
                        </div>
                        <div class="calendar-days" id="calendar-days" role="grid" aria-label="Kalender tanggal"></div>
                    </div>

                    <div class="events-list">
                        <h3>📋 Acara Mendatang</h3>
                        <div id="events-container" role="list"></div>
                    </div>
                </div>
            </div>
        </section>

        <!-- ASPIRASI / PENGADUAN -->
        <section class="section-padding text-center" id="aspirasi" aria-label="Form aspirasi dan pengaduan warga">
            <div class="container">
                <div class="eyebrow">Suara Warga</div>
                <h2 class="section-title">Aspirasi & Pengaduan Warga Pauh</h2>
                <p class="section-desc">Sampaikan laporan fasilitas publik, saran, atau keluhan Anda langsung ke pihak kecamatan.</p>

                <div class="form-container">
                    <div class="alert" id="successAlert" role="alert">
                        ✅ Terima kasih! Laporan Anda telah berhasil terkirim dan akan segera ditindaklanjuti oleh staf Kantor Kecamatan Pauh.
                    </div>
                    <form id="complaintForm">
                        <div class="form-group">
                            <label for="nama">Nama Lengkap <span aria-label="wajib diisi">*</span></label>
                            <input type="text" id="nama" placeholder="Masukkan nama Anda" required aria-required="true">
                        </div>
                        <div class="form-group">
                            <label for="email">Alamat Email / Whatsapp <span aria-label="wajib diisi">*</span></label>
                            <input type="text" id="email" placeholder="Email atau Nomor WA aktif" required aria-required="true">
                        </div>
                        <div class="form-group">
                            <label for="kelurahanSelect">Pilih Kelurahan Domisili <span aria-label="wajib diisi">*</span></label>
                            <select id="kelurahanSelect" required aria-required="true">
                                <option value="">-- Pilih Kelurahan --</option>
                                <option value="Limau Manis">Limau Manis</option>
                                <option value="Limau Manis Selatan">Limau Manis Selatan</option>
                                <option value="Kapalo Koto">Kapalo Koto</option>
                                <option value="Koto Luar">Koto Luar</option>
                                <option value="Lambung Bukit">Lambung Bukit</option>
                                <option value="Piai Tangah">Piai Tangah</option>
                                <option value="Bitinggi Taba">Bitinggi Taba</option>
                                <option value="Cupak Tangah">Cupak Tangah</option>
                                <option value="Pisang">Pisang</option>
                            </select>
                        </div>
                        <div class="form-group">
                            <label for="pesan">Pesan Pengaduan / Aspirasi <span aria-label="wajib diisi">*</span></label>
                            <textarea id="pesan" rows="4" placeholder="Tuliskan keluhan atau pengaduan Anda secara rinci..." required aria-required="true"></textarea>
                        </div>
                        <button type="submit" class="submit-btn">Kirim Laporan Warga</button>
                    </form>
                </div>
            </div>
        </section>

<?php require __DIR__ . '/includes/site_footer.php'; ?>
