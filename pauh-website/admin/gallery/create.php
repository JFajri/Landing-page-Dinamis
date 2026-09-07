<?php
require_once __DIR__ . '/../../includes/auth.php';
requireLogin();

$pdo = getConnection();
$errors = [];
$form = [
    'caption'     => '',
    'description' => '',
    'status'      => 'active',
];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!verifyCsrf()) {
        $errors[] = 'Sesi form tidak valid. Silakan coba lagi.';
    } else {
        $form['caption']     = clean($_POST['caption'] ?? '');
        $form['description'] = clean($_POST['description'] ?? '');
        $form['status']      = ($_POST['status'] ?? 'active') === 'inactive' ? 'inactive' : 'active';

        if ($form['caption'] === '') {
            $errors[] = 'Caption/judul foto wajib diisi.';
        } elseif (mb_strlen($form['caption']) > 150) {
            $errors[] = 'Caption maksimal 150 karakter.';
        }
        if (empty($_FILES['image']['name'])) {
            $errors[] = 'Foto wajib diupload.';
        }

        $upload = ['success' => true, 'filename' => null, 'error' => null];
        if (!empty($_FILES['image']['name'])) {
            $upload = handleGalleryUpload($_FILES['image']); // reuse mekanisme upload Berita
            if (!$upload['success']) {
                $errors[] = $upload['error'];
            }
        }

        if (empty($errors)) {
            $stmt = $pdo->prepare('
                INSERT INTO gallery (image, caption, description, status, created_at, updated_at)
                VALUES (?, ?, ?, ?, NOW(), NOW())
            ');
            $stmt->execute([$upload['filename'], $form['caption'], $form['description'] ?: null, $form['status']]);

            setFlash('success', 'Foto "' . $form['caption'] . '" berhasil ditambahkan ke galeri.');
            redirect('admin/gallery/index.php');
        }
    }
}

$pageTitle  = 'Tambah Foto Galeri';
$activeMenu = 'gallery';
require __DIR__ . '/../../includes/header.php';
?>

<div class="card" style="max-width:600px">
    <h3>Tambah Foto Galeri</h3>

    <?php foreach ($errors as $error): ?>
        <div class="alert alert-error"><?= e($error) ?></div>
    <?php endforeach; ?>

    <form method="post" enctype="multipart/form-data" class="js-validate" novalidate>
        <?= csrfField() ?>

        <div class="form-group">
            <label for="image-input">Foto <span style="color:var(--red)">*</span></label>
            <input type="file" id="thumbnail-input" name="image" class="form-control" accept=".jpg,.jpeg,.png,.webp" required>
            <p class="hint">Format JPG/PNG/WEBP, maksimal 2MB.</p>
            <img id="thumbnail-preview" style="display:none;max-width:220px;margin-top:10px;border-radius:8px;border:1px solid var(--line)" alt="Preview">
        </div>

        <div class="form-group">
            <label for="caption">Caption / Judul Foto <span style="color:var(--red)">*</span></label>
            <input type="text" id="caption" name="caption" class="form-control" required maxlength="150" value="<?= e($form['caption']) ?>" placeholder="Contoh: Kegiatan sosialisasi kesehatan masyarakat">
            <p class="hint">Dipakai juga sebagai teks alternatif (alt text) foto.</p>
        </div>

        <div class="form-group">
            <label for="description">Deskripsi (opsional)</label>
            <textarea id="description" name="description" class="form-control" rows="2" maxlength="255"><?= e($form['description']) ?></textarea>
        </div>

        <div class="form-group">
            <label for="status">Status</label>
            <select id="status" name="status" class="form-control">
                <option value="active" <?= $form['status'] === 'active' ? 'selected' : '' ?>>Tampil di Landing Page</option>
                <option value="inactive" <?= $form['status'] === 'inactive' ? 'selected' : '' ?>>Sembunyikan</option>
            </select>
        </div>

        <div style="display:flex;gap:10px">
            <button type="submit" class="btn btn-primary">Simpan Foto</button>
            <a href="<?= e(BASE_URL) ?>/admin/gallery/index.php" class="btn btn-outline">Batal</a>
        </div>
    </form>
</div>

<?php require __DIR__ . '/../../includes/footer.php'; ?>
