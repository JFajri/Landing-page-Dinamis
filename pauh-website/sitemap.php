<?php
/**
 * sitemap.php — dilayani sebagai /sitemap.xml lewat aturan di .htaccess.
 * Berisi beranda, daftar berita, kebijakan privasi, dan semua berita berstatus
 * "published" (otomatis bertambah saat admin menerbitkan berita baru).
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
];
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
