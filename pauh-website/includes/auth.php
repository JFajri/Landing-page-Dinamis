<?php
/**
 * includes/auth.php
 * Autentikasi & proteksi halaman admin berbasis PHP Session.
 * require_once file ini di paling atas SETIAP halaman admin (kecuali login.php).
 */

require_once __DIR__ . '/bootstrap.php';

// Halaman admin bersifat privat: jangan disimpan cache browser/proxy dan jangan diindeks.
if (!headers_sent()) {
    header('Cache-Control: no-store, max-age=0');
    header('X-Robots-Tag: noindex, nofollow');
}

// Sesi admin berakhir otomatis setelah sekian detik tanpa aktivitas.
if (!defined('ADMIN_IDLE_TIMEOUT')) {
    define('ADMIN_IDLE_TIMEOUT', 7200);
}

function isLoggedIn(): bool
{
    if (empty($_SESSION['admin_id'])) {
        return false;
    }
    $last = (int) ($_SESSION['admin_last_active'] ?? 0);
    if ($last > 0 && (time() - $last) > ADMIN_IDLE_TIMEOUT) {
        unset($_SESSION['admin_id'], $_SESSION['admin_name'], $_SESSION['admin_username'], $_SESSION['admin_role'], $_SESSION['admin_last_active']);
        return false;
    }
    $_SESSION['admin_last_active'] = time();
    return true;
}

/** Panggil di awal halaman admin yang wajib login. Redirect ke login jika belum. */
function requireLogin(): void
{
    if (!isLoggedIn()) {
        setFlash('error', 'Silakan login terlebih dahulu untuk mengakses halaman admin.');
        redirect('admin/login.php');
    }
}

function currentAdmin(): ?array
{
    if (!isLoggedIn()) {
        return null;
    }
    return [
        'id'       => $_SESSION['admin_id'],
        'name'     => $_SESSION['admin_name'] ?? '',
        'username' => $_SESSION['admin_username'] ?? '',
        'role'     => $_SESSION['admin_role'] ?? 'admin',
    ];
}

/** Coba login dengan username & password. Return true jika berhasil. */
function attemptLogin(PDO $pdo, string $username, string $password): bool
{
    $stmt = $pdo->prepare('SELECT id, name, username, password, role FROM users WHERE username = ? LIMIT 1');
    $stmt->execute([$username]);
    $user = $stmt->fetch();

    // Untuk username yang tidak ada, tetap jalankan password_verify() terhadap hash tiruan
    // supaya waktu respons tidak membocorkan apakah username tersebut terdaftar.
    $dummyHash = '$2y$10$iVjQPSl4JSmwJncWgpcUpuBQ8yTOJanQLLM5jJacdz0pUK2D5zDKO';
    $valid     = password_verify($password, $user ? $user['password'] : $dummyHash);

    if (!$user || !$valid) {
        return false;
    }

    // Regenerate session ID untuk mencegah session fixation
    session_regenerate_id(true);

    $_SESSION['admin_id']          = $user['id'];
    $_SESSION['admin_name']        = $user['name'];
    $_SESSION['admin_username']    = $user['username'];
    $_SESSION['admin_role']        = $user['role'];
    $_SESSION['admin_last_active'] = time();

    return true;
}

function doLogout(): void
{
    $_SESSION = [];
    if (ini_get('session.use_cookies')) {
        $params = session_get_cookie_params();
        setcookie(session_name(), '', time() - 42000, $params['path'], $params['domain'], $params['secure'], $params['httponly']);
    }
    session_destroy();
}
