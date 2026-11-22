<?php
require_once __DIR__ . '/../includes/bootstrap.php';

// Sudah login? langsung ke dashboard.
if (!empty($_SESSION['admin_id'])) {
    redirect('admin/index.php');
}

require_once __DIR__ . '/../includes/auth.php';

$errors = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!verifyCsrf()) {
        $errors[] = 'Sesi form tidak valid. Silakan coba lagi.';
    } else {
        $username = clean($_POST['username'] ?? '');
        $password = (string) ($_POST['password'] ?? '');

        if ($username === '' || $password === '') {
            $errors[] = 'Username dan password wajib diisi.';
        } else {
            $pdo = getConnection();

            // Pembatasan percobaan login: maksimal 5 kegagalan per (IP + username) dan
            // 20 kegagalan per IP dalam 15 menit. Setelah itu login ditolak sementara.
            $ip        = clientIp();
            $userKey   = $ip . '|' . strtolower($username);
            $window    = 900;
            $locked    = rateLimitCount($pdo, 'login', $userKey, $window) >= 5
                      || rateLimitCount($pdo, 'login_ip', $ip, $window) >= 20;

            if ($locked) {
                $errors[] = 'Terlalu banyak percobaan login yang gagal. Silakan coba lagi dalam 15 menit.';
            } elseif (attemptLogin($pdo, $username, $password)) {
                rateLimitClear($pdo, 'login', $userKey);
                redirect('admin/index.php');
            } else {
                rateLimitHit($pdo, 'login', $userKey);
                rateLimitHit($pdo, 'login_ip', $ip);
                $errors[] = 'Username atau password salah.';
            }
        }
    }
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="robots" content="noindex, nofollow">
    <meta name="theme-color" content="#0d2542">
    <?= faviconLinkTag() ?>
    <title>Login Admin - <?= e(SITE_NAME) ?></title>
    <link rel="stylesheet" href="<?= e(assetUrl('css/admin.css')) ?>">
</head>
<body>
<div class="login-wrap">
    <div class="login-box">
        <div class="logo"><img src="<?= e(logoUrl()) ?>" alt="" width="56" height="56"></div>
        <h1>Admin <?= e(SITE_NAME) ?></h1>
        <p class="sub">Masuk untuk mengelola konten website</p>

        <?php foreach ($errors as $error): ?>
            <div class="alert alert-error"><?= e($error) ?></div>
        <?php endforeach; ?>

        <form method="post" class="js-validate" novalidate>
            <?= csrfField() ?>
            <div class="form-group">
                <label for="username">Username</label>
                <input type="text" id="username" name="username" class="form-control" required autofocus autocomplete="username" autocapitalize="none" spellcheck="false" value="<?= e($_POST['username'] ?? '') ?>">
            </div>
            <div class="form-group">
                <label for="password">Password</label>
                <input type="password" id="password" name="password" class="form-control" required autocomplete="current-password">
            </div>
            <button type="submit" class="btn btn-primary btn-block">Masuk</button>
        </form>
    </div>
</div>
<script src="<?= e(assetUrl('js/admin.js')) ?>"></script>
</body>
</html>
