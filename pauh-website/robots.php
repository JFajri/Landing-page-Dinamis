<?php
/**
 * robots.php — dilayani sebagai /robots.txt lewat aturan di .htaccess.
 * Dibuat dinamis agar alamat Sitemap otomatis mengikuti domain yang dipakai.
 */
define('APP_NO_SESSION', true);
require_once __DIR__ . '/config/config.php';

header('Content-Type: text/plain; charset=UTF-8');
header('Cache-Control: public, max-age=3600');

$basePath = (string) parse_url(BASE_URL, PHP_URL_PATH);

echo "User-agent: *\n";
foreach (['admin', 'install', 'aspirasi', 'config', 'includes', 'database'] as $dir) {
    echo 'Disallow: ' . $basePath . '/' . $dir . "/\n";
}
echo "\nSitemap: " . BASE_URL . "/sitemap.xml\n";
