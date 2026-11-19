<?php
/**
 * includes/bootstrap.php
 * Dipanggil di paling atas setiap entry point publik/admin (bukan installer).
 * Tugasnya: load config umum, pastikan aplikasi sudah diinstall (config/database.php
 * & install.lock ada), lalu load koneksi database + helper functions.
 */

require_once __DIR__ . '/../config/config.php';

$dbConfigFile = __DIR__ . '/../config/database.php';
$lockFile     = __DIR__ . '/../install.lock';

if (!file_exists($dbConfigFile) || !file_exists($lockFile)) {
    // Aplikasi belum diinstall — arahkan ke installer.
    $installUrl = BASE_URL . '/install/';
    header('Location: ' . $installUrl);
    exit;
}

require_once $dbConfigFile;
require_once __DIR__ . '/functions.php';
require_once __DIR__ . '/settings.php';

// Auto-sync skema database: kalau ada tabel baru di database/schema.sql yang
// belum ada di database Anda (mis. setelah update fitur seperti Galeri/Kalender/
// Aspirasi), otomatis dibuatkan di sini — tanpa perlu jalankan SQL manual.
// Tidak pernah menghapus/mengubah tabel atau data yang sudah ada (hanya
// CREATE TABLE IF NOT EXISTS), dan hanya dijalankan sekali per perubahan
// schema.sql (dicek lewat hash file) supaya tidak membebani setiap request.
ensureDatabaseSchema(getConnection());

// Isi konten awal (menu, statistik, potensi, kelurahan, layanan, jam kantor) satu
// kali saja agar situs yang sudah berjalan tampil persis seperti sebelumnya dan
// bisa langsung dikelola dari Admin. Tidak pernah menimpa data yang sudah ada.
seedDefaultContent(getConnection());
