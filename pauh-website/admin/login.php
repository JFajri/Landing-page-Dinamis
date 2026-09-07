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
            if (attemptLogin($pdo, $username, $password)) {
                redirect('admin/index.php');
            } else {
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
    <title>Login Admin - <?= e(SITE_NAME) ?></title>
    <link rel="stylesheet" href="<?= e(BASE_URL) ?>/assets/css/admin.css">
</head>
<body>
<div class="login-wrap">
    <div class="login-box">
        <div class="logo">🏛️</div>
        <h1>Admin <?= e(SITE_NAME) ?></h1>
        <p class="sub">Masuk untuk mengelola konten website</p>

        <?php foreach ($errors as $error): ?>
            <div class="alert alert-error"><?= e($error) ?></div>
        <?php endforeach; ?>

        <form method="post" class="js-validate" novalidate>
            <?= csrfField() ?>
            <div class="form-group">
                <label for="username">Username</label>
                <input type="text" id="username" name="username" class="form-control" required autofocus value="<?= e($_POST['username'] ?? '') ?>">
            </div>
            <div class="form-group">
                <label for="password">Password</label>
                <input type="password" id="password" name="password" class="form-control" required>
            </div>
            <button type="submit" class="btn btn-primary btn-block">Masuk</button>
        </form>
    </div>
</div>
<script src="<?= e(BASE_URL) ?>/assets/js/admin.js"></script>
</body>
</html>
