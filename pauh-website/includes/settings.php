<?php
/**
 * includes/settings.php
 * Lapisan "konten yang dapat dikelola" (tidak hardcode) untuk seluruh situs.
 *
 * Dua sumber data, keduanya dikelola dari Admin:
 *   1. settings       -> pasangan key/value (identitas, warna, hero, judul section, footer, dst)
 *   2. content_items  -> daftar berurutan (menu, statistik, potensi, kelurahan, layanan, jam kantor)
 *
 * Semua teks default ada di siteDefaults() / defaultContentItems(). Jadi situs tetap
 * tampil utuh walau tabel masih kosong, dan untuk menduplikasi situs ke instansi lain
 * cukup ubah nilai di Admin > Pengaturan Situs & Konten Halaman (tanpa menyentuh kode).
 *
 * Menambah pengaturan baru: tambahkan satu baris di siteDefaults() dan satu field di
 * settingsSchema() — form admin dibuat otomatis dari skema tersebut.
 */

/* =========================================================
 * DEFAULT VALUES
 * ========================================================= */

function siteDefaults(): array
{
    return [
        // Identitas
        'site.name'           => 'Portal Resmi Kecamatan Pauh',
        'site.brand_title'    => 'KECAMATAN PAUH',
        'site.brand_subtitle' => 'Kota Padang, Sumatra Barat',
        'site.logo'           => '', // kosong = pakai assets/favicon.svg
        'site.home_title'     => 'Portal Resmi Kecamatan Pauh - Kota Padang | Layanan Publik Terpercaya',
        'site.description'    => 'Portal Resmi Kecamatan Pauh, Kota Padang. Pusat pendidikan tinggi dengan layanan administrasi warga yang transparan dan mudah diakses.',
        'site.keywords'       => 'Kecamatan Pauh, Padang, administrasi, layanan publik, kelurahan, Unand',
        'site.author'         => 'Pemerintah Kecamatan Pauh, Kota Padang',
        'site.og_image'       => '',

        // Warna merek (menjadi CSS variable di seluruh halaman publik)
        'color.primary'  => '#123b6d',
        'color.primary2' => '#1d5a96',
        'color.accent'   => '#c9972b',

        // Hero
        'hero.badge'       => 'Portal Resmi Pelayanan Publik',
        'hero.title'       => 'Selamat Datang di',
        'hero.highlight'   => 'Kecamatan Pauh',
        'hero.description' => 'Pusat Pendidikan Tinggi, Keasrian Alam Bukit Barisan, dan pelayanan masyarakat yang mudah, informatif, dan transparan.',
        'hero.btn1_label'  => 'Jelajahi Layanan',
        'hero.btn1_link'   => '#layanan',
        'hero.btn2_label'  => 'Kirim Pengaduan',
        'hero.btn2_link'   => '#aspirasi',
        'hero.image'       => 'https://farm9.staticflickr.com/8600/16495653639_4bfeb53836_c.jpg',

        // Section (aktif/nonaktif + judul). {count} diganti jumlah item aktif.
        'sec.stats.enabled'     => '1',

        'sec.potensi.enabled'   => '1',
        'sec.potensi.eyebrow'   => 'Mengenal Pauh',
        'sec.potensi.title'     => 'Pusat Pendidikan & Destinasi Asri',
        'sec.potensi.desc'      => 'Mengenal lebih dekat kawasan ikonik, wisata lokal, dan infrastruktur pendidikan yang ada di Kecamatan Pauh.',

        'sec.kelurahan.enabled' => '1',
        'sec.kelurahan.eyebrow' => 'Wilayah Administratif',
        'sec.kelurahan.title'   => '{count} Kelurahan di Kecamatan Pauh',
        'sec.kelurahan.desc'    => 'Kecamatan Pauh membawahi {count} wilayah kelurahan administratif Kota Padang.',

        'sec.layanan.enabled'   => '1',
        'sec.layanan.eyebrow'   => 'Layanan Utama',
        'sec.layanan.title'     => 'Layanan Administrasi Warga',
        'sec.layanan.desc'      => 'Kemudahan akses administrasi kependudukan dan perizinan bagi warga Kecamatan Pauh.',

        'sec.berita.enabled'    => '1',
        'sec.berita.eyebrow'    => 'Informasi Terbaru',
        'sec.berita.title'      => 'Berita & Pengumuman',
        'sec.berita.desc'       => 'Ikuti perkembangan terbaru, pengumuman penting, dan kegiatan di Kecamatan Pauh.',

        'sec.galeri.enabled'    => '1',
        'sec.galeri.eyebrow'    => 'Visual Dokumentasi',
        'sec.galeri.title'      => 'Galeri Kegiatan',
        'sec.galeri.desc'       => 'Jelajahi dokumentasi foto kegiatan sosial, pembangunan infrastruktur, dan acara-acara penting di Kecamatan Pauh.',

        'sec.kalender.enabled'  => '1',
        'sec.kalender.eyebrow'  => 'Jadwal Kegiatan',
        'sec.kalender.title'    => 'Kalender Event & Kegiatan',
        'sec.kalender.desc'     => 'Pantau jadwal kegiatan publik, acara sosial, dan event penting di Kecamatan Pauh untuk tahun ini.',

        'sec.aspirasi.enabled'  => '1',
        'sec.aspirasi.eyebrow'  => 'Suara Warga',
        'sec.aspirasi.title'    => 'Aspirasi & Pengaduan Warga Pauh',
        'sec.aspirasi.desc'     => 'Sampaikan laporan fasilitas publik, saran, atau keluhan Anda langsung ke pihak kecamatan.',

        // Berita, galeri, kalender, aspirasi
        'news.home_count'     => '3',
        'news.per_page'       => '9',
        'news.link_label'     => 'Baca Selengkapnya →',
        'news.button_label'   => 'Lihat Semua Berita',
        'news.empty_text'     => 'Belum ada berita yang dipublikasikan.',
        'news.fallback_image' => 'https://images.unsplash.com/photo-1552664730-d307ca884978?auto=format&fit=crop&w=600&q=80',
        'gallery.empty_text'  => 'Belum ada foto galeri yang ditampilkan.',
        'calendar.upcoming_title' => 'Acara Mendatang',
        'calendar.empty_text'     => 'Tidak ada acara bulan ini',
        'aspirasi.success_message' => 'Terima kasih! Laporan Anda telah berhasil terkirim dan akan segera ditindaklanjuti oleh staf Kantor Kecamatan Pauh.',
        'aspirasi.button_label'    => 'Kirim Laporan Warga',

        // Footer & kontak
        'footer.office_title'  => 'Kantor Camat Pauh',
        'footer.address'       => 'Jl. Raya Pasar Baru - Unand, Pasar Baru, Kec. Pauh, Kota Padang, Sumatra Barat 25161.',
        'footer.hours_title'   => 'Jam Operasional Kantor',
        'footer.contact_title' => 'Kontak & Info',
        'footer.location'      => 'Kota Padang, Sumatra Barat',
        'footer.email'         => 'kec.pauh@padang.go.id',
        'footer.phone'         => '',
        'footer.copyright'     => 'Pemerintah Kecamatan Pauh - Kota Padang. Hak Cipta Dilindungi.',

        // Penanda internal (bukan untuk diedit)
        'content_seeded' => '0',
    ];
}

/* =========================================================
 * SETTINGS: LOAD / READ / SAVE
 * ========================================================= */

/** Gabungan default + nilai dari database. Di-cache per request. */
function loadSettings(bool $refresh = false): array
{
    static $cache = null;
    if ($cache !== null && !$refresh) {
        return $cache;
    }
    $cache = siteDefaults();
    try {
        $rows = getConnection()->query('SELECT setting_key, setting_value FROM settings')->fetchAll();
        foreach ($rows as $row) {
            if ($row['setting_value'] !== null) {
                $cache[$row['setting_key']] = $row['setting_value'];
            }
        }
    } catch (Throwable $e) {
        error_log('loadSettings error: ' . $e->getMessage());
    }
    return $cache;
}

function setting(string $key, string $default = ''): string
{
    $all = loadSettings();
    return array_key_exists($key, $all) ? (string) $all[$key] : $default;
}

function settingOn(string $key): bool
{
    return setting($key, '0') === '1';
}

/** Section dengan pengaturan .enabled dianggap aktif; section tanpa pengaturan selalu aktif. */
function sectionOn(string $section): bool
{
    $key = 'sec.' . $section . '.enabled';
    return array_key_exists($key, siteDefaults()) ? settingOn($key) : true;
}

/** Simpan banyak pengaturan sekaligus (REPLACE INTO kompatibel MySQL & MariaDB). */
function saveSettings(PDO $pdo, array $values): void
{
    $stmt = $pdo->prepare('REPLACE INTO settings (setting_key, setting_value) VALUES (?, ?)');
    foreach ($values as $key => $value) {
        $stmt->execute([$key, (string) $value]);
    }
    loadSettings(true);
}

/** Cetak eyebrow + judul + deskripsi sebuah section dari pengaturan (mendukung {count}). */
function renderSectionHead(string $section, array $vars = []): void
{
    $eyebrow = tpl(setting("sec.$section.eyebrow"), $vars);
    $title   = tpl(setting("sec.$section.title"), $vars);
    $desc    = tpl(setting("sec.$section.desc"), $vars);
    if ($eyebrow !== '') {
        echo '<div class="eyebrow">' . e($eyebrow) . '</div>' . "\n";
    }
    if ($title !== '') {
        echo '<h2 class="section-title" id="' . e($section) . '-title">' . e($title) . '</h2>' . "\n";
    }
    if ($desc !== '') {
        echo '<p class="section-desc">' . e($desc) . '</p>' . "\n";
    }
}

/** Ganti placeholder {count} dst. pada teks judul/deskripsi section. */
function tpl(string $text, array $vars = []): string
{
    foreach ($vars as $name => $value) {
        $text = str_replace('{' . $name . '}', (string) $value, $text);
    }
    return $text;
}

/* =========================================================
 * URL, MEDIA & ASSET HELPERS
 * ========================================================= */

/**
 * Ubah tautan buatan admin menjadi URL final yang aman.
 *   '/'        -> beranda  (BASE_URL/)  — tanpa hash, tanpa "#beranda"
 *   '#potensi' -> BASE_URL/#potensi     (tetap bekerja dari halaman lain)
 *   '/news'    -> BASE_URL/news
 *   'https://…', 'mailto:…', 'tel:…' dipakai apa adanya
 * Skema lain (mis. javascript:) ditolak dan diganti '#'.
 */
function resolveLink(?string $url): string
{
    $url = trim((string) $url);
    if ($url === '') {
        return BASE_URL . '/';
    }
    if (preg_match('/^[a-z][a-z0-9+.\-]*:/i', $url)) {
        return preg_match('/^(https?|mailto|tel):/i', $url) ? $url : '#';
    }
    if (str_starts_with($url, '//')) {
        return '#';
    }
    if ($url === '/') {
        return BASE_URL . '/';
    }
    if ($url[0] === '#') {
        return BASE_URL . '/' . $url;
    }
    return BASE_URL . '/' . ltrim($url, '/');
}

/** Validasi tautan yang akan disimpan admin. Kembalikan null jika tidak valid. */
function normalizeLinkInput(string $url): ?string
{
    $url = trim($url);
    if ($url === '') {
        return '';
    }
    if (preg_match('/^[a-z][a-z0-9+.\-]*:/i', $url)) {
        return preg_match('/^(https?|mailto|tel):/i', $url) ? $url : null;
    }
    if (str_starts_with($url, '//') || preg_match('/\s/', $url)) {
        return null;
    }
    return $url;
}

/** URL gambar: tautan eksternal dipakai langsung, nama file mengarah ke uploads/site/. */
function mediaUrl(?string $value): string
{
    $value = trim((string) $value);
    if ($value === '') {
        return '';
    }
    if (preg_match('#^https?://#i', $value)) {
        return $value;
    }
    return SITE_UPLOAD_URL . rawurlencode($value);
}

/**
 * URL logo situs. Logo resmi yang diunggah di Admin > Identitas & SEO dipakai di navbar,
 * panel admin, dan favicon browser; jika kosong, dipakai assets/favicon.svg bawaan.
 * Untuk mengganti secara permanen lewat file, cukup timpa assets/favicon.svg.
 */
function logoUrl(): string
{
    $custom = mediaUrl(setting('site.logo'));
    return $custom !== '' ? $custom : assetUrl('favicon.svg');
}

/** Tag <link rel="icon"> dengan type sesuai ekstensi logo. */
function faviconLinkTag(): string
{
    $url  = logoUrl();
    $ext  = strtolower(pathinfo((string) parse_url($url, PHP_URL_PATH), PATHINFO_EXTENSION));
    $mime = ['svg' => 'image/svg+xml', 'png' => 'image/png', 'webp' => 'image/webp', 'jpg' => 'image/jpeg', 'jpeg' => 'image/jpeg'][$ext] ?? '';
    return '<link rel="icon"' . ($mime !== '' ? ' type="' . $mime . '"' : '') . ' href="' . e($url) . '">';
}

/** Aman-kan URL agar bisa disisipkan di dalam url('…') pada atribut style. */
function cssUrl(string $url): string
{
    return str_replace(["'", '"', '(', ')', '\\', "\n", "\r", ' '], ['%27', '%22', '%28', '%29', '%5C', '', '', '%20'], $url);
}

/** URL aset statis + cache-busting otomatis berdasarkan waktu ubah file. */
function assetUrl(string $path): string
{
    $file    = __DIR__ . '/../assets/' . ltrim($path, '/');
    $version = is_file($file) ? filemtime($file) : 1;
    return BASE_URL . '/assets/' . ltrim($path, '/') . '?v=' . $version;
}

/** Validasi warna hex #rrggbb dari pengaturan; fallback ke default jika tidak valid. */
function settingColor(string $key): string
{
    $value = setting($key);
    if (preg_match('/^#[0-9a-fA-F]{6}$/', $value)) {
        return $value;
    }
    return (string) (siteDefaults()[$key] ?? '#000000');
}

function handleSiteUpload(array $file): array
{
    return handleImageUpload($file, SITE_UPLOAD_DIR);
}

/** Hapus file di uploads/site/ hanya jika nilainya nama file lokal (bukan URL eksternal). */
function deleteSiteImage(?string $value): void
{
    $value = trim((string) $value);
    if ($value !== '' && !preg_match('#^https?://#i', $value)) {
        deleteImageFile(SITE_UPLOAD_DIR, basename($value));
    }
}

/* =========================================================
 * CONTENT ITEMS (daftar berurutan yang dikelola admin)
 * ========================================================= */

/**
 * Registri section. Dipakai halaman admin (form & daftar dibuat otomatis) dan
 * halaman publik. Menambah jenis daftar baru = tambah satu entri di sini.
 * Tipe field: text | textarea | emoji | link | image | select
 */
function contentSections(): array
{
    return [
        'nav' => [
            'label' => 'Menu Navigasi', 'icon' => '🧭', 'singular' => 'Menu',
            'intro' => 'Urutan & isi menu di bagian atas situs. Tautan "/" = beranda (tanpa #), "#layanan" = menuju section, "/news" = halaman berita, atau URL lengkap.',
            'list'  => ['title' => 'Menu', 'link_url' => 'Tautan', 'value' => 'Gaya'],
            'fields' => [
                ['key' => 'title',    'label' => 'Teks Menu', 'type' => 'text', 'required' => true, 'max' => 60],
                ['key' => 'link_url', 'label' => 'Tautan',    'type' => 'link', 'required' => true, 'max' => 255,
                 'hint' => 'Contoh: / (beranda), #layanan, /news, https://contoh.go.id'],
                ['key' => 'value',    'label' => 'Gaya',      'type' => 'select', 'options' => ['' => 'Menu biasa', 'cta' => 'Tombol sorot (CTA)']],
            ],
        ],
        'stats' => [
            'label' => 'Statistik', 'icon' => '📈', 'singular' => 'Statistik',
            'intro' => 'Angka ringkas yang tampil tepat di bawah hero. Disarankan 3–4 item agar tampilan rapi.',
            'list'  => ['value' => 'Angka', 'title' => 'Keterangan'],
            'fields' => [
                ['key' => 'value', 'label' => 'Angka / Nilai', 'type' => 'text', 'required' => true, 'max' => 40, 'hint' => 'Contoh: ~60K, 9, 146.43 ㎢'],
                ['key' => 'title', 'label' => 'Keterangan',    'type' => 'text', 'required' => true, 'max' => 80],
            ],
        ],
        'potensi' => [
            'label' => 'Potensi Wilayah', 'icon' => '🏞️', 'singular' => 'Potensi',
            'intro' => 'Kartu pada section "Potensi Wilayah". Isi tautan (opsional) agar seluruh kartu bisa diklik.',
            'list'  => ['image' => 'Gambar', 'title' => 'Judul'],
            'fields' => [
                ['key' => 'title',       'label' => 'Judul',     'type' => 'text', 'required' => true, 'max' => 150],
                ['key' => 'description', 'label' => 'Deskripsi', 'type' => 'textarea', 'max' => 500],
                ['key' => 'image',       'label' => 'Gambar',    'type' => 'image', 'hint' => 'Upload (JPG/PNG/WEBP, maks 2MB) atau tempel URL gambar.'],
                ['key' => 'link_url',    'label' => 'Tautan (opsional)', 'type' => 'link', 'max' => 255],
            ],
        ],
        'kelurahan' => [
            'label' => 'Kelurahan', 'icon' => '🏘️', 'singular' => 'Kelurahan',
            'intro' => 'Daftar kelurahan. Otomatis menjadi pilihan pada dropdown form Aspirasi & Pengaduan.',
            'list'  => ['title' => 'Nama Kelurahan'],
            'fields' => [
                ['key' => 'title', 'label' => 'Nama Kelurahan', 'type' => 'text', 'required' => true, 'max' => 100],
            ],
        ],
        'layanan' => [
            'label' => 'Layanan Publik', 'icon' => '🪪', 'singular' => 'Layanan',
            'intro' => 'Kartu pada section "Layanan Administrasi Warga". Seluruh kartu dapat diklik menuju tautan yang diisi.',
            'list'  => ['icon' => 'Ikon', 'title' => 'Layanan', 'link_url' => 'Tautan'],
            'fields' => [
                ['key' => 'icon',        'label' => 'Ikon (emoji)', 'type' => 'emoji', 'max' => 8],
                ['key' => 'title',       'label' => 'Nama Layanan', 'type' => 'text', 'required' => true, 'max' => 150],
                ['key' => 'description', 'label' => 'Deskripsi',    'type' => 'textarea', 'max' => 500],
                ['key' => 'value',       'label' => 'Teks Tautan',  'type' => 'text', 'max' => 60, 'hint' => 'Contoh: Konsultasi Syarat →'],
                ['key' => 'link_url',    'label' => 'Tautan',       'type' => 'link', 'max' => 255, 'hint' => 'Contoh: #aspirasi atau https://…'],
            ],
        ],
        'hours' => [
            'label' => 'Jam Operasional', 'icon' => '🕒', 'singular' => 'Jam Operasional',
            'intro' => 'Baris jam kerja kantor yang tampil di footer.',
            'list'  => ['title' => 'Hari', 'value' => 'Jam'],
            'fields' => [
                ['key' => 'title', 'label' => 'Hari',  'type' => 'text', 'required' => true, 'max' => 80, 'hint' => 'Contoh: Senin - Kamis'],
                ['key' => 'value', 'label' => 'Jam',   'type' => 'text', 'required' => true, 'max' => 80, 'hint' => 'Contoh: 07:30 - 16:00 WIB'],
            ],
        ],
    ];
}

/** Ambil daftar item satu section, terurut. Di-cache per request. */
function contentItems(string $section, bool $onlyActive = true): array
{
    static $cache = [];
    $cacheKey = $section . ($onlyActive ? ':a' : ':all');
    if (isset($cache[$cacheKey])) {
        return $cache[$cacheKey];
    }
    try {
        $sql = 'SELECT * FROM content_items WHERE section = ?' . ($onlyActive ? " AND status = 'active'" : '') . ' ORDER BY sort_order ASC, id ASC';
        $stmt = getConnection()->prepare($sql);
        $stmt->execute([$section]);
        $cache[$cacheKey] = $stmt->fetchAll();
    } catch (Throwable $e) {
        error_log('contentItems error: ' . $e->getMessage());
        $cache[$cacheKey] = [];
    }
    return $cache[$cacheKey];
}

/** Nilai awal daftar konten — identik dengan isi landing page sebelum dibuat dinamis. */
function defaultContentItems(): array
{
    return [
        'nav' => [
            ['title' => 'Beranda',         'link_url' => '/'],
            ['title' => 'Potensi Wilayah', 'link_url' => '#potensi'],
            ['title' => 'Kelurahan',       'link_url' => '#kelurahan'],
            ['title' => 'Layanan',         'link_url' => '#layanan'],
            ['title' => 'Berita',          'link_url' => '/news'],
            ['title' => 'Galeri',          'link_url' => '#galeri'],
            ['title' => 'Kalender',        'link_url' => '#kalender'],
            ['title' => 'Aspirasi',        'link_url' => '#aspirasi'],
            ['title' => 'Kontak',          'link_url' => '#kontak', 'value' => 'cta'],
        ],
        'stats' => [
            ['value' => '~60K',      'title' => 'Estimasi Penduduk'],
            ['value' => '9',         'title' => 'Kelurahan'],
            ['value' => '146.43 ㎢', 'title' => 'Luas Wilayah'],
            ['value' => '1',         'title' => 'Kampus Utama Unand'],
        ],
        'potensi' => [
            [
                'title' => 'Universitas Andalas (Unand)',
                'description' => 'Kawasan perguruan tinggi tertua di luar Pulau Jawa yang berlokasi di perbukitan Limau Manis dengan lanskap pemandangan indah Kota Padang.',
                'image' => 'https://fk.unand.ac.id/wp-content/uploads/2023/12/AERIAL-VIEW-FK-UNAND-MEDIUM.jpg',
            ],
            [
                'title' => 'Wisata Alam & Sungai Batu Busuk',
                'description' => 'Destinasi pemandian alam dan jembatan gantung ikonik dengan aliran sungai jernih di kaki bukit wilayah Kelurahan Lambung Bukit.',
                'image' => 'https://blogger.googleusercontent.com/img/b/R29vZ2xl/AVvXsEgYvzCjZNOMuFHaJlUezNN5kzBA31cbVXwg9-YqlWEw2tArwcf84_qid_N54NXbSdZBgMnpwMfzU-b0GefbjbwOYH7aTEoCfoqht8ra1Uq4NYIoQfIrJnBW_bEr6LpDq-JaUaA_wAgLU_NUOO0ufKvSf7GsCzHS-CF27PXCQIAJ-K2Yh-R_1E4HEN5iqxaZ/s1556/490743928_1388968985622464_7085951840122729520_n.jpg',
            ],
            [
                'title' => 'Agrowisata & Lingkungan Asri',
                'description' => 'Kecamatan Pauh kaya akan komoditas buah lokal seperti durian pauh, perbukitan hijau, serta aliran sungai penopang irigasi daerah.',
                'image' => 'https://i0.wp.com/padangkita.com/wp-content/uploads/2023/01/IMG_9469.jpg?resize=750%2C487&ssl=1',
            ],
        ],
        'kelurahan' => [
            ['title' => 'Limau Manis'],
            ['title' => 'Limau Manis Selatan'],
            ['title' => 'Kapalo Koto'],
            ['title' => 'Koto Luar'],
            ['title' => 'Lambung Bukit'],
            ['title' => 'Piai Tangah'],
            ['title' => 'Bitinggi Taba'],
            ['title' => 'Cupak Tangah'],
            ['title' => 'Pisang'],
        ],
        'layanan' => [
            ['icon' => '🪪', 'title' => 'KTP-el & KIA', 'description' => 'Perekaman baru, penggantian rusak/hilang, dan pengurusan Kartu Identitas Anak.', 'value' => 'Konsultasi Syarat →', 'link_url' => '#aspirasi'],
            ['icon' => '📜', 'title' => 'Kartu Keluarga', 'description' => 'Pembaruan data KK, penambahan anggota keluarga, atau pemisahan KK baru.', 'value' => 'Konsultasi Syarat →', 'link_url' => '#aspirasi'],
            ['icon' => '✉️', 'title' => 'Surat Pindah Domisili', 'description' => 'Pengurusan surat pengantar pindah masuk atau keluar wilayah Pauh.', 'value' => 'Konsultasi Syarat →', 'link_url' => '#aspirasi'],
            ['icon' => '🤝', 'title' => 'Rekomendasi UMKM', 'description' => 'Surat keterangan usaha (SKU) untuk legalitas usaha mikro & kecil lokal.', 'value' => 'Konsultasi Syarat →', 'link_url' => '#aspirasi'],
        ],
        'hours' => [
            ['title' => 'Senin - Kamis',    'value' => '07:30 - 16:00 WIB'],
            ['title' => 'Jumat',            'value' => '07:30 - 16:30 WIB'],
            ['title' => 'Sabtu - Minggu',   'value' => 'Libur (Tutup)'],
        ],
    ];
}

/**
 * Isi konten awal satu kali saja (ditandai settings.content_seeded). Hanya mengisi
 * section yang masih kosong, sehingga aman dipanggil di setiap request dan tidak
 * pernah menimpa data yang sudah diubah admin.
 */
function seedDefaultContent(PDO $pdo): void
{
    if (settingOn('content_seeded')) {
        return;
    }
    try {
        $count  = $pdo->prepare('SELECT COUNT(*) FROM content_items WHERE section = ?');
        $insert = $pdo->prepare('
            INSERT INTO content_items (section, title, value, description, icon, image, link_url, sort_order, status)
            VALUES (?, ?, ?, ?, ?, ?, ?, ?, \'active\')
        ');
        foreach (defaultContentItems() as $section => $items) {
            $count->execute([$section]);
            if ((int) $count->fetchColumn() > 0) {
                continue;
            }
            foreach ($items as $i => $item) {
                $insert->execute([
                    $section,
                    $item['title'],
                    $item['value'] ?? null,
                    $item['description'] ?? null,
                    $item['icon'] ?? null,
                    $item['image'] ?? null,
                    $item['link_url'] ?? null,
                    ($i + 1) * 10,
                ]);
            }
        }
        saveSettings($pdo, ['content_seeded' => '1']);
    } catch (Throwable $e) {
        error_log('seedDefaultContent error: ' . $e->getMessage());
    }
}

/* =========================================================
 * SKEMA FORM PENGATURAN (halaman Admin > Pengaturan Situs)
 * ========================================================= */

/**
 * Tipe field: text | textarea | color | image | toggle | number | link
 * Form admin, validasi, dan penyimpanan dibuat otomatis dari skema ini.
 */
function settingsSchema(): array
{
    $sectionFields = function (string $s, string $label): array {
        return [
            ['key' => "sec.$s.enabled", 'label' => "Tampilkan section $label", 'type' => 'toggle'],
            ['key' => "sec.$s.eyebrow", 'label' => "$label — teks kecil di atas judul", 'type' => 'text', 'max' => 60],
            ['key' => "sec.$s.title",   'label' => "$label — judul", 'type' => 'text', 'max' => 150],
            ['key' => "sec.$s.desc",    'label' => "$label — deskripsi", 'type' => 'textarea', 'max' => 300],
        ];
    };

    return [
        'identitas' => [
            'label' => 'Identitas & SEO', 'icon' => '🏷️',
            'intro' => 'Nama, logo, dan metadata yang dibaca mesin pencari serta saat tautan dibagikan di media sosial.',
            'fields' => [
                ['key' => 'site.name',           'label' => 'Nama situs', 'type' => 'text', 'max' => 100],
                ['key' => 'site.brand_title',    'label' => 'Judul brand di navbar', 'type' => 'text', 'max' => 60],
                ['key' => 'site.brand_subtitle', 'label' => 'Sub-judul brand di navbar', 'type' => 'text', 'max' => 80],
                ['key' => 'site.logo',           'label' => 'Logo resmi (juga dipakai sebagai favicon)', 'type' => 'image', 'hint' => 'Disarankan persegi, PNG/WEBP latar transparan minimal 256×256 px. Kosongkan untuk memakai logo bawaan (assets/favicon.svg).'],
                ['key' => 'site.home_title',     'label' => 'Judul tab browser beranda', 'type' => 'text', 'max' => 120, 'hint' => 'Ideal 50–60 karakter agar tidak terpotong di hasil pencarian.'],
                ['key' => 'site.description',    'label' => 'Deskripsi situs (meta description)', 'type' => 'textarea', 'max' => 300, 'hint' => 'Ideal 120–160 karakter.'],
                ['key' => 'site.keywords',       'label' => 'Kata kunci', 'type' => 'text', 'max' => 250],
                ['key' => 'site.author',         'label' => 'Pemilik/penulis situs', 'type' => 'text', 'max' => 120],
                ['key' => 'site.og_image',       'label' => 'Gambar pratinjau saat dibagikan (1200×630 px)', 'type' => 'image'],
            ],
        ],
        'tampilan' => [
            'label' => 'Warna', 'icon' => '🎨',
            'intro' => 'Warna merek diterapkan ke seluruh halaman publik. Pilih kontras yang cukup antara teks putih dan warna utama.',
            'fields' => [
                ['key' => 'color.primary',  'label' => 'Warna utama (gelap)', 'type' => 'color'],
                ['key' => 'color.primary2', 'label' => 'Warna sekunder', 'type' => 'color'],
                ['key' => 'color.accent',   'label' => 'Warna aksen (tombol & sorotan)', 'type' => 'color'],
            ],
        ],
        'hero' => [
            'label' => 'Hero (Bagian Atas)', 'icon' => '🌄',
            'intro' => 'Tampilan pertama yang dilihat pengunjung.',
            'fields' => [
                ['key' => 'hero.badge',       'label' => 'Label kecil', 'type' => 'text', 'max' => 60],
                ['key' => 'hero.title',       'label' => 'Judul (baris biasa)', 'type' => 'text', 'max' => 80],
                ['key' => 'hero.highlight',   'label' => 'Judul (bagian berwarna emas)', 'type' => 'text', 'max' => 80],
                ['key' => 'hero.description', 'label' => 'Deskripsi', 'type' => 'textarea', 'max' => 300],
                ['key' => 'hero.btn1_label',  'label' => 'Tombol utama — teks', 'type' => 'text', 'max' => 40],
                ['key' => 'hero.btn1_link',   'label' => 'Tombol utama — tautan', 'type' => 'link', 'max' => 255, 'hint' => 'Contoh: #layanan'],
                ['key' => 'hero.btn2_label',  'label' => 'Tombol kedua — teks (kosongkan untuk menyembunyikan)', 'type' => 'text', 'max' => 40],
                ['key' => 'hero.btn2_link',   'label' => 'Tombol kedua — tautan', 'type' => 'link', 'max' => 255],
                ['key' => 'hero.image',       'label' => 'Gambar latar', 'type' => 'image', 'hint' => 'Disarankan landscape minimal 1600 px lebar.'],
            ],
        ],
        'section' => [
            'label' => 'Judul Section', 'icon' => '🧱',
            'intro' => 'Aktifkan/nonaktifkan dan ubah judul tiap section. Gunakan {count} pada judul Kelurahan agar jumlahnya otomatis.',
            'fields' => array_merge(
                [['key' => 'sec.stats.enabled', 'label' => 'Tampilkan section Statistik', 'type' => 'toggle']],
                $sectionFields('potensi', 'Potensi'),
                $sectionFields('kelurahan', 'Kelurahan'),
                $sectionFields('layanan', 'Layanan'),
                $sectionFields('berita', 'Berita'),
                $sectionFields('galeri', 'Galeri'),
                $sectionFields('kalender', 'Kalender'),
                $sectionFields('aspirasi', 'Aspirasi')
            ),
        ],
        'fitur' => [
            'label' => 'Berita, Kalender & Aspirasi', 'icon' => '🧩',
            'intro' => 'Teks tombol dan pesan kosong untuk modul dinamis.',
            'fields' => [
                ['key' => 'news.home_count',     'label' => 'Jumlah berita di beranda', 'type' => 'number', 'min' => 1, 'max' => 12],
                ['key' => 'news.per_page',       'label' => 'Berita per halaman (halaman /news)', 'type' => 'number', 'min' => 3, 'max' => 24],
                ['key' => 'news.link_label',     'label' => 'Teks ajakan pada kartu berita', 'type' => 'text', 'max' => 40],
                ['key' => 'news.button_label',   'label' => 'Teks tombol "semua berita"', 'type' => 'text', 'max' => 40],
                ['key' => 'news.empty_text',     'label' => 'Pesan jika belum ada berita', 'type' => 'text', 'max' => 150],
                ['key' => 'news.fallback_image', 'label' => 'Gambar bawaan berita tanpa thumbnail', 'type' => 'image'],
                ['key' => 'gallery.empty_text',  'label' => 'Pesan jika galeri kosong', 'type' => 'text', 'max' => 150],
                ['key' => 'calendar.upcoming_title', 'label' => 'Judul daftar acara', 'type' => 'text', 'max' => 60],
                ['key' => 'calendar.empty_text', 'label' => 'Pesan jika tidak ada acara bulan ini', 'type' => 'text', 'max' => 100],
                ['key' => 'aspirasi.button_label',   'label' => 'Teks tombol kirim aspirasi', 'type' => 'text', 'max' => 50],
                ['key' => 'aspirasi.success_message', 'label' => 'Pesan setelah aspirasi terkirim', 'type' => 'textarea', 'max' => 300],
            ],
        ],
        'footer' => [
            'label' => 'Footer & Kontak', 'icon' => '📍',
            'intro' => 'Alamat dan kontak juga dipakai sebagai data terstruktur (schema.org) untuk mesin pencari.',
            'fields' => [
                ['key' => 'footer.office_title',  'label' => 'Judul kolom alamat', 'type' => 'text', 'max' => 60],
                ['key' => 'footer.address',       'label' => 'Alamat kantor', 'type' => 'textarea', 'max' => 300],
                ['key' => 'footer.hours_title',   'label' => 'Judul kolom jam operasional', 'type' => 'text', 'max' => 60],
                ['key' => 'footer.contact_title', 'label' => 'Judul kolom kontak', 'type' => 'text', 'max' => 60],
                ['key' => 'footer.location',      'label' => 'Lokasi singkat', 'type' => 'text', 'max' => 100],
                ['key' => 'footer.email',         'label' => 'Email', 'type' => 'text', 'max' => 120],
                ['key' => 'footer.phone',         'label' => 'Telepon / WhatsApp (opsional)', 'type' => 'text', 'max' => 40],
                ['key' => 'footer.copyright',     'label' => 'Teks hak cipta (setelah © dan tahun)', 'type' => 'text', 'max' => 150],
            ],
        ],
    ];
}

/** Ratakan skema menjadi peta key => definisi field. */
function settingsFieldMap(): array
{
    $map = [];
    foreach (settingsSchema() as $group) {
        foreach ($group['fields'] as $field) {
            $map[$field['key']] = $field;
        }
    }
    return $map;
}
