<?php
/**
 * includes/page_helpers.php
 * Fungsi bantu untuk halaman publik baru (daftar, detail, dan halaman statis):
 * halaman 404, paginasi, judul halaman, dan tab sub-navigasi Profil.
 * Dimuat otomatis oleh includes/bootstrap.php.
 */

/**
 * Tampilkan halaman "tidak ditemukan" bergaya situs (status HTTP 404, tidak diindeks)
 * lalu hentikan eksekusi. $navActive menandai menu aktif.
 */
function renderNotFound(string $backLabel, string $backPath, string $navActive = '', string $message = ''): void
{
    http_response_code(404);
    $pageTitle       = 'Halaman Tidak Ditemukan - ' . setting('site.name');
    $pageDescription = 'Halaman yang Anda cari tidak ditemukan atau belum dipublikasikan.';
    $noIndex         = true;
    if ($message === '') {
        $message = 'Halaman yang Anda cari mungkin sudah dihapus, belum dipublikasikan, atau alamatnya tidak valid.';
    }
    require __DIR__ . '/site_header.php';
    ?>
        <section class="section-padding page-top text-center" aria-labelledby="notfound-title">
            <div class="container container-narrow">
                <h1 class="section-title" id="notfound-title">Halaman Tidak Ditemukan</h1>
                <p class="section-desc section-desc-flush"><?= e($message) ?></p>
                <a href="<?= e(BASE_URL . $backPath) ?>" class="btn btn-main"><?= e($backLabel) ?></a>
            </div>
        </section>
    <?php
    require __DIR__ . '/site_footer.php';
    exit;
}

/** Hentikan dengan 404 bila section tertentu dinonaktifkan admin (Tampilan Website → Judul Section). */
function requireSectionOn(string $section, string $navActive, string $backLabel = '← Kembali ke Beranda', string $backPath = '/'): void
{
    if (!sectionOn($section)) {
        renderNotFound($backLabel, $backPath, $navActive);
    }
}

/**
 * Tentukan halaman saat ini untuk daftar berpaginasi. Nomor di luar rentang diarahkan ke
 * halaman terakhir yang ada (bukan daftar kosong yang bisa ikut terindeks).
 * Mengembalikan [halaman, totalHalaman].
 */
function resolvePage(int $totalItems, int $perPage, string $listPath, array $query = []): array
{
    $totalPages = max(1, (int) ceil($totalItems / max(1, $perPage)));
    $page       = max(1, (int) ($_GET['page'] ?? 1));
    if ($page > $totalPages) {
        if ($totalPages > 1) {
            $query['page'] = $totalPages;
        }
        header('Location: ' . BASE_URL . $listPath . ($query ? '?' . http_build_query($query) : ''), true, 302);
        exit;
    }
    return [$page, $totalPages];
}

/** Tautan paginasi bergaya sama dengan halaman /news (kelas .pagination & .page-link). */
function renderPagination(int $page, int $totalPages, string $ariaLabel, array $query = []): void
{
    if ($totalPages <= 1) {
        return;
    }
    echo '<nav class="pagination" aria-label="' . e($ariaLabel) . '">';
    for ($p = 1; $p <= $totalPages; $p++) {
        $href = '?' . http_build_query($query + ['page' => $p]);
        echo '<a href="' . e($href) . '" class="page-link' . ($p === $page ? ' is-current' : '') . '"'
           . ($p === $page ? ' aria-current="page"' : '')
           . ' aria-label="Halaman ' . $p . '">' . $p . '</a>';
    }
    echo '</nav>';
}

/** Akhiran judul tab browser untuk halaman berpaginasi, mis. " (Halaman 2)". */
function pageSuffix(int $page): string
{
    return $page > 1 ? ' (Halaman ' . $page . ')' : '';
}

/** Judul + deskripsi halaman dari tiga pengaturan page.<kunci>.eyebrow|title|desc (H1). */
function renderPageHeading(string $key): void
{
    $eyebrow = setting("page.$key.eyebrow");
    $title   = setting("page.$key.title");
    $desc    = setting("page.$key.desc");
    if ($eyebrow !== '') {
        echo '<div class="eyebrow">' . e($eyebrow) . '</div>' . "\n";
    }
    echo '<h1 class="section-title" id="page-title">' . e($title) . '</h1>' . "\n";
    if ($desc !== '') {
        echo '<p class="section-desc">' . e($desc) . '</p>' . "\n";
    }
}

/** Tab sub-navigasi antar halaman profil (Profil, Visi & Misi, Struktur Organisasi). */
function renderProfilTabs(string $active): void
{
    $tabs = [
        'profil'               => ['Profil Kecamatan', '/profil'],
        'visi-misi'            => ['Visi & Misi', '/visi-misi'],
        'struktur-organisasi'  => ['Struktur Organisasi', '/struktur-organisasi'],
    ];
    echo '<nav class="subnav" aria-label="Halaman profil"><ul>';
    foreach ($tabs as $key => [$label, $path]) {
        $isCurrent = $key === $active;
        echo '<li><a href="' . e(BASE_URL . $path) . '"' . ($isCurrent ? ' class="is-current" aria-current="page"' : '') . '>' . e($label) . '</a></li>';
    }
    echo '</ul></nav>';
}

/** Blok "tidak ada data" bergaya situs. */
function renderEmptyNote(string $text = ''): void
{
    echo '<p class="empty-note">' . e($text !== '' ? $text : setting('page.empty_text')) . '</p>';
}

/** Breadcrumb: $trail = [[label, path|null], ...]; item terakhir (path null) = halaman saat ini. */
function renderBreadcrumb(array $trail): void
{
    echo '<nav class="breadcrumb" aria-label="Breadcrumb"><ol>';
    foreach ($trail as $i => [$label, $path]) {
        $isLast = $i === count($trail) - 1;
        echo '<li>';
        if ($path !== null && !$isLast) {
            echo '<a href="' . e(BASE_URL . $path) . '">' . e($label) . '</a>';
        } else {
            echo '<span aria-current="page">' . e($label) . '</span>';
        }
        echo '</li>';
    }
    echo '</ol></nav>';
}

/** Alamat halaman detail agenda. */
function eventDetailUrl(int $id): string
{
    return BASE_URL . '/kalender/detail.php?id=' . $id;
}

/**
 * Data seluruh agenda aktif untuk widget kalender (assets/js/main.js), dikelompokkan per
 * tanggal YYYY-MM-DD. Semua teks sudah di-escape di sini karena JS menyisipkannya lewat
 * innerHTML.
 */
function calendarEventsForJs(PDO $pdo): array
{
    $rows = $pdo->query("
        SELECT id, event_date, event_time, title, location, description
        FROM events
        WHERE status = 'active'
        ORDER BY event_date ASC, event_time ASC
    ")->fetchAll();

    $byDate = [];
    foreach ($rows as $ev) {
        $byDate[$ev['event_date']][] = [
            'id'       => (int) $ev['id'],
            'url'      => e(eventDetailUrl((int) $ev['id'])),
            'title'    => e($ev['title']),
            'desc'     => e($ev['description'] ?? ''),
            'time'     => $ev['event_time'] ? e(substr($ev['event_time'], 0, 5)) : '',
            'location' => e($ev['location'] ?? ''),
        ];
    }
    return $byDate;
}

/** Baris info singkat agenda: "🕐 09:00 WIB · 📍 Aula Kantor Camat" (sudah di-escape). */
function eventMetaLine(array $event): string
{
    $parts = [];
    if (!empty($event['event_time'])) {
        $parts[] = '<span><span aria-hidden="true">🕐</span> ' . e(substr($event['event_time'], 0, 5)) . ' WIB</span>';
    }
    if (!empty($event['location'])) {
        $parts[] = '<span><span aria-hidden="true">📍</span> ' . e($event['location']) . '</span>';
    }
    return implode('', $parts);
}
