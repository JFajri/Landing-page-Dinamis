<?php
/**
 * config/config.php
 * Konfigurasi umum aplikasi (BUKAN kredensial database).
 * File ini aman untuk berada di source code / version control.
 * Kredensial database ada di config/database.php (dibuat otomatis oleh installer).
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

// Nama situs
define('SITE_NAME', 'Portal Resmi Kecamatan Pauh');

// Deteksi BASE_URL otomatis (mendukung instalasi di root atau subfolder)
if (!defined('BASE_URL')) {
    $protocol = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? 'https://' : 'http://';
    $host     = $_SERVER['HTTP_HOST'] ?? 'localhost';
    $script   = str_replace('\\', '/', dirname($_SERVER['SCRIPT_NAME'] ?? ''));
    // Buang subfolder khusus (admin, news, install) agar BASE_URL selalu mengarah ke root aplikasi
    $script   = preg_replace('#/(admin|news|install)(/.*)?$#', '', $script);
    $script   = rtrim($script, '/');
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

// Session cookie settings (harus sebelum session_start() pertama kali dipanggil)
if (session_status() === PHP_SESSION_NONE) {
    session_set_cookie_params([
        'lifetime' => 0,
        'path'     => '/',
        'httponly' => true,
        'samesite' => 'Lax',
    ]);
    session_start();
}
