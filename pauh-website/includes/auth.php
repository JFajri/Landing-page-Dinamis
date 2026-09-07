<?php
/**
 * includes/auth.php
 * Autentikasi & proteksi halaman admin berbasis PHP Session.
 * require_once file ini di paling atas SETIAP halaman admin (kecuali login.php).
 */

require_once __DIR__ . '/bootstrap.php';

function isLoggedIn(): bool
{
    return !empty($_SESSION['admin_id']);
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

    if (!$user || !password_verify($password, $user['password'])) {
        return false;
    }

    // Regenerate session ID untuk mencegah session fixation
    session_regenerate_id(true);

    $_SESSION['admin_id']       = $user['id'];
    $_SESSION['admin_name']     = $user['name'];
    $_SESSION['admin_username'] = $user['username'];
    $_SESSION['admin_role']     = $user['role'];

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
