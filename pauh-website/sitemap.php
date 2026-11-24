<?php
/**
 * sitemap.php — dilayani sebagai /sitemap.xml lewat aturan di .htaccess.
 * Berisi beranda, halaman statis (profil, visi-misi, struktur), halaman daftar & detail
 * (potensi, kelurahan, layanan, galeri, kalender, aspirasi), daftar berita, kebijakan
 * privasi, dan semua berita berstatus "published". Section yang dinonaktifkan admin
 * tidak dimasukkan. Otomatis bertambah saat konten baru ditambahkan.
 */
define('APP_NO_SESSION', true);
require_once __DIR__ . '/includes/bootstrap.php';

try {
    $rows = getConnection()->query("
        SELECT slug, published_at, updated_at
        FROM news
        WHERE status = 'published'
        ORDER BY published_at DESC
        LIMIT 5000
    ")->fetchAll();
} catch (Throwable $e) {
    error_log('sitemap error: ' . $e->getMessage());
    http_response_code(503);
    header('Retry-After: 300');
    exit;
}

$toDate = static function (?string $value): string {
    $ts = $value ? strtotime($value) : false;
    return $ts ? date('Y-m-d', $ts) : '';
};

$latest = '';
foreach ($rows as $row) {
    $d = $toDate($row['updated_at'] ?: $row['published_at']);
    if ($d > $latest) {
        $latest = $d;
    }
}

$urls = [
    ['loc' => BASE_URL . '/',         'lastmod' => $latest, 'changefreq' => 'weekly',  'priority' => '1.0'],
    ['loc' => BASE_URL . '/news/',    'lastmod' => $latest, 'changefreq' => 'daily',   'priority' => '0.8'],
    ['loc' => BASE_URL . '/privacy/', 'lastmod' => '',      'changefreq' => 'yearly',  'priority' => '0.3'],
    ['loc' => BASE_URL . '/profil/',  'lastmod' => '', 'changefreq' => 'yearly', 'priority' => '0.7'],
    ['loc' => BASE_URL . '/visi-misi/', 'lastmod' => '', 'changefreq' => 'yearly', 'priority' => '0.6'],
    ['loc' => BASE_URL . '/struktur-organisasi/', 'lastmod' => '', 'changefreq' => 'yearly', 'priority' => '0.6'],
];

// Halaman daftar + detail dari section yang aktif.
try {
    foreach (['potensi', 'kelurahan', 'layanan'] as $section) {
        if (!sectionOn($section)) {
            continue;
        }
        $urls[] = ['loc' => BASE_URL . '/' . $section . '/', 'lastmod' => '', 'changefreq' => 'monthly', 'priority' => '0.7'];
        foreach (contentItems($section) as $item) {
            $urls[] = ['loc' => contentDetailUrl($section, (int) $item['id']), 'lastmod' => $toDate($item['updated_at'] ?? null), 'changefreq' => 'monthly', 'priority' => '0.5'];
        }
    }
    if (sectionOn('galeri')) {
        $urls[] = ['loc' => BASE_URL . '/galeri/', 'lastmod' => '', 'changefreq' => 'weekly', 'priority' => '0.6'];
    }
    if (sectionOn('aspirasi')) {
        $urls[] = ['loc' => BASE_URL . '/aspirasi/', 'lastmod' => '', 'changefreq' => 'yearly', 'priority' => '0.6'];
    }
    if (sectionOn('kalender')) {
        $urls[] = ['loc' => BASE_URL . '/kalender/', 'lastmod' => '', 'changefreq' => 'weekly', 'priority' => '0.6'];
        $events = getConnection()->query("SELECT id, updated_at FROM events WHERE status = 'active' ORDER BY event_date DESC LIMIT 1000")->fetchAll();
        foreach ($events as $ev) {
            $urls[] = ['loc' => eventDetailUrl((int) $ev['id']), 'lastmod' => $toDate($ev['updated_at'] ?? null), 'changefreq' => 'monthly', 'priority' => '0.4'];
        }
    }
} catch (Throwable $e) {
    error_log('sitemap content error: ' . $e->getMessage());
}
foreach ($rows as $row) {
    $urls[] = [
        'loc'        => BASE_URL . '/news/detail.php?slug=' . urlencode($row['slug']),
        'lastmod'    => $toDate($row['updated_at'] ?: $row['published_at']),
        'changefreq' => 'monthly',
        'priority'   => '0.6',
    ];
}

header('Content-Type: application/xml; charset=UTF-8');
header('Cache-Control: public, max-age=3600');

echo '<?xml version="1.0" encoding="UTF-8"?>' . "\n";
echo '<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">' . "\n";
foreach ($urls as $u) {
    echo "  <url>\n";
    echo '    <loc>' . e($u['loc']) . "</loc>\n";
    if ($u['lastmod'] !== '') {
        echo '    <lastmod>' . e($u['lastmod']) . "</lastmod>\n";
    }
    echo '    <changefreq>' . $u['changefreq'] . "</changefreq>\n";
    echo '    <priority>' . $u['priority'] . "</priority>\n";
    echo "  </url>\n";
}
echo "</urlset>\n";
