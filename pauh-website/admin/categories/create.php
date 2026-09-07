<?php
require_once __DIR__ . '/../../includes/auth.php';
requireLogin();

$pdo = getConnection();
$errors = [];
$name = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!verifyCsrf()) {
        $errors[] = 'Sesi form tidak valid. Silakan coba lagi.';
    } else {
        $name = clean($_POST['name'] ?? '');

        if ($name === '') {
            $errors[] = 'Nama kategori wajib diisi.';
        } elseif (mb_strlen($name) > 100) {
            $errors[] = 'Nama kategori maksimal 100 karakter.';
        }

        if (empty($errors)) {
            $slug = generateUniqueSlug($pdo, $name, 'categories');
            $stmt = $pdo->prepare('INSERT INTO categories (name, slug, created_at, updated_at) VALUES (?, ?, NOW(), NOW())');
            $stmt->execute([$name, $slug]);

            setFlash('success', 'Kategori "' . $name . '" berhasil ditambahkan.');
            redirect('admin/categories/index.php');
        }
    }
}

$pageTitle  = 'Tambah Kategori';
$activeMenu = 'categories';
require __DIR__ . '/../../includes/header.php';
?>

<div class="card" style="max-width:560px">
    <h3>Tambah Kategori Baru</h3>

    <?php foreach ($errors as $error): ?>
        <div class="alert alert-error"><?= e($error) ?></div>
    <?php endforeach; ?>

    <form method="post" class="js-validate" novalidate>
        <?= csrfField() ?>
        <div class="form-group">
            <label for="name">Nama Kategori <span style="color:var(--red)">*</span></label>
            <input type="text" id="name" name="name" class="form-control" required maxlength="100" value="<?= e($name) ?>" placeholder="Contoh: Pengumuman">
            <p class="hint">Slug URL akan dibuat otomatis dari nama ini.</p>
        </div>
        <div style="display:flex;gap:10px">
            <button type="submit" class="btn btn-primary">Simpan Kategori</button>
            <a href="<?= e(BASE_URL) ?>/admin/categories/index.php" class="btn btn-outline">Batal</a>
        </div>
    </form>
</div>

<?php require __DIR__ . '/../../includes/footer.php'; ?>
