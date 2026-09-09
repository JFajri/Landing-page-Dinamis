<?php
/**
 * config/database.php
 * DIBUAT OTOMATIS OLEH INSTALLER pada 2026-09-09 06:33:17.
 * Jangan commit file ini ke version control publik.
 */

define('DB_HOST', 'localhost');
define('DB_PORT', '3306');
define('DB_NAME', 'db_pauh');
define('DB_USER', 'root');
define('DB_PASS', '');

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
