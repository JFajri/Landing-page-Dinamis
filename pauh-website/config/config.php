<?php
/**
 * config/config.php
 * Konfigurasi umum aplikasi (BUKAN kredensial database).
 * File ini aman untuk berada di source code / version control.
 * Kredensial database ada di config/database.php (dibuat otomatis oleh installer).
 *
 * Pengaturan khusus server (opsional) bisa ditaruh di config/site.php — file itu
 * diabaikan git. Contoh isi ada di config/site.example.php.
 */

// Tampilkan error hanya untuk development. Set ke '0' saat production.
define('APP_DEBUG', false);

if (APP_DEBUG) {
    ini_set('display_errors', 1);
    error_reporting(E_ALL);
} else {
    ini_set('display_errors', 0);
    error_reporting(0);
}

// Zona waktu bawaan: WIB. Dipakai penentuan "hari ini" pada daftar agenda. Dapat ditimpa
// dari config/site.php (date_default_timezone_set) bila instansi berada di zona lain.
date_default_timezone_set('Asia/Jakarta');

// Pengaturan khusus server (mis. BASE_URL permanen, header IP di belakang proxy).
$siteOverride = __DIR__ . '/site.php';
if (is_file($siteOverride)) {
    require_once $siteOverride;
}

// Nama situs
define('SITE_NAME', 'Portal Resmi Kecamatan Pauh');

/** Apakah permintaan ini lewat HTTPS (termasuk di belakang proxy/CDN yang meneruskan skema). */
if (!function_exists('isHttps')) {
    function isHttps(): bool
    {
        if (!empty($_SERVER['HTTPS']) && strtolower((string) $_SERVER['HTTPS']) !== 'off') {
            return true;
        }
        if ((int) ($_SERVER['SERVER_PORT'] ?? 0) === 443) {
            return true;
        }
        return strtolower((string) ($_SERVER['HTTP_X_FORWARDED_PROTO'] ?? '')) === 'https';
    }
}

// Deteksi BASE_URL otomatis (mendukung instalasi di root atau subfolder).
// Untuk menetapkannya secara permanen (disarankan di production), definisikan
// BASE_URL di config/site.php.
if (!defined('BASE_URL')) {
    $protocol = isHttps() ? 'https://' : 'http://';

    // Header Host berasal dari klien dan bisa dipalsukan, jadi hanya hostname/IP[:port]
    // yang wajar yang diterima; selain itu dipakai SERVER_NAME, lalu 'localhost'.
    $hostPattern = '/^(?:[a-z0-9](?:[a-z0-9.-]*[a-z0-9])?|\[[0-9a-f:]+\])(?::\d{1,5})?$/';
    $host = strtolower((string) ($_SERVER['HTTP_HOST'] ?? ''));
    if (!preg_match($hostPattern, $host)) {
        $host = strtolower((string) ($_SERVER['SERVER_NAME'] ?? ''));
        if (!preg_match($hostPattern, $host)) {
            $host = 'localhost';
        }
    }

    $script = str_replace('\\', '/', dirname($_SERVER['SCRIPT_NAME'] ?? ''));
    // Buang subfolder khusus agar BASE_URL selalu mengarah ke root aplikasi
    $script = preg_replace('#/(admin|news|install|aspirasi|privacy|potensi|kelurahan|layanan|galeri|kalender|profil|visi-misi|struktur-organisasi)(/.*)?$#', '', $script);
    $script = rtrim($script, '/');
    define('BASE_URL', $protocol . $host . $script);
}

// Path & URL upload berita
define('UPLOAD_DIR', __DIR__ . '/../uploads/news/');
define('UPLOAD_URL', BASE_URL . '/uploads/news/');

// Path & URL upload galeri (memakai mekanisme upload yang sama dengan berita,
// hanya folder tujuannya berbeda — lihat includes/functions.php: handleImageUpload())
define('GALLERY_UPLOAD_DIR', __DIR__ . '/../uploads/gallery/');
define('GALLERY_UPLOAD_URL', BASE_URL . '/uploads/gallery/');

// Path & URL upload aset situs (gambar hero, potensi wilayah, OG image, dll.
// yang dikelola dari Admin > Pengaturan Situs / Konten Halaman)
define('SITE_UPLOAD_DIR', __DIR__ . '/../uploads/site/');
define('SITE_UPLOAD_URL', BASE_URL . '/uploads/site/');

// Batas ukuran upload gambar (bytes) - 2MB
define('UPLOAD_MAX_SIZE', 2 * 1024 * 1024);

// Ekstensi & MIME type gambar yang diizinkan
define('UPLOAD_ALLOWED_EXT', ['jpg', 'jpeg', 'png', 'webp']);
define('UPLOAD_ALLOWED_MIME', ['image/jpeg', 'image/png', 'image/webp']);

// Header keamanan dasar. Berlaku untuk semua halaman PHP; file statis mendapatkannya
// dari .htaccess. (Sengaja tanpa Content-Security-Policy: halaman memakai skrip/gaya inline.)
if (!headers_sent()) {
    header('X-Content-Type-Options: nosniff');
    header('X-Frame-Options: SAMEORIGIN');
    header('Referrer-Policy: strict-origin-when-cross-origin');
    header('Permissions-Policy: camera=(), microphone=(), geolocation=(), payment=()');
    if (isHttps()) {
        header('Strict-Transport-Security: max-age=15552000');
    }
    @header_remove('X-Powered-By');
}

// Session cookie settings (harus sebelum session_start() pertama kali dipanggil).
// Endpoint yang tidak butuh session (robots.txt, sitemap.xml) bisa mendefinisikan
// APP_NO_SESSION sebelum meng-include file ini.
if (!defined('APP_NO_SESSION') && session_status() === PHP_SESSION_NONE) {
    ini_set('session.use_strict_mode', '1');
    ini_set('session.use_only_cookies', '1');
    // Bawaan PHP menambahkan "no-store" ke semua halaman yang memakai session, sehingga
    // tombol Back/Forward browser harus memuat ulang dari server. Ganti dengan "private,
    // no-cache": selalu divalidasi ulang dan tidak disimpan cache bersama (halaman memuat
    // token CSRF per pengunjung). Halaman admin menimpanya dengan "no-store" (includes/auth.php).
    session_cache_limiter('');
    if (!headers_sent()) {
        header('Cache-Control: private, no-cache');
    }
    session_set_cookie_params([
        'lifetime' => 0,
        'path'     => '/',
        'secure'   => isHttps(),
        'httponly' => true,
        'samesite' => 'Lax',
    ]);
    session_start();
}
