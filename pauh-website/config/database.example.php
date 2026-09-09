<?php
/**
 * config/database.example.php
 *
 * INI HANYA CONTOH/TEMPLATE. JANGAN DIGUNAKAN LANGSUNG.
 * File config/database.php yang sebenarnya dibuat OTOMATIS oleh installer
 * (/install/) saat instalasi pertama kali, lengkap dengan kredensial yang
 * Anda masukkan di form installer. Jangan hardcode kredensial di sini atau
 * commit file config/database.php ke version control publik.
 */

define('DB_HOST', 'localhost');
define('DB_PORT', '3306');
define('DB_NAME', 'db_pauh_v2');
define('DB_USER', 'db_user');
define('DB_PASS', 'db_password');

function getConnection(): PDO
{
    static $pdo = null;
    if ($pdo === null) {
        $dsn = 'mysql:host=' . DB_HOST . ';port=' . DB_PORT . ';dbname=' . DB_NAME . ';charset=utf8mb4';
        try {
            $pdo = new PDO($dsn, DB_USER, DB_PASS, [
                PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                PDO::ATTR_EMULATE_PREPARES   => false,
            ]);
        } catch (PDOException $e) {
            error_log('DB Connection Error: ' . $e->getMessage());
            die('Terjadi kesalahan koneksi database. Silakan coba lagi nanti.');
        }
    }
    return $pdo;
}
