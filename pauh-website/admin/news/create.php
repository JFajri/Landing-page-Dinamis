<?php
require_once __DIR__ . '/../../includes/auth.php';
requireLogin();

$pdo = getConnection();
$categories = $pdo->query('SELECT id, name FROM categories ORDER BY name ASC')->fetchAll();

$errors = [];
$form = [
    'title'         => '',
    'category_id'   => '',
    'excerpt'       => '',
    'content'       => '',
    'status'        => 'draft',
    'published_at'  => '',
];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!verifyCsrf()) {
        $errors[] = 'Sesi form tidak valid. Silakan coba lagi.';
    } else {
        $form['title']        = clean($_POST['title'] ?? '');
        $form['category_id']  = clean($_POST['category_id'] ?? '');
        $form['excerpt']      = clean($_POST['excerpt'] ?? '');
        $form['content']      = trim($_POST['content'] ?? '');
        $form['status']       = ($_POST['status'] ?? 'draft') === 'published' ? 'published' : 'draft';
        $form['published_at'] = clean($_POST['published_at'] ?? '');

        // ===== SERVER-SIDE VALIDATION =====
        if ($form['title'] === '') {
            $errors[] = 'Judul berita wajib diisi.';
        } elseif (mb_strlen($form['title']) > 255) {
            $errors[] = 'Judul berita maksimal 255 karakter.';
        }
        if ($form['content'] === '') {
            $errors[] = 'Isi berita wajib diisi.';
        }
        if ($form['category_id'] !== '' && !ctype_digit($form['category_id'])) {
            $errors[] = 'Kategori tidak valid.';
        }

        $upload = ['success' => true, 'filename' => null, 'error' => null];
        if (!empty($_FILES['thumbnail']['name'])) {
            $upload = handleThumbnailUpload($_FILES['thumbnail']);
            if (!$upload['success']) {
                $errors[] = $upload['error'];
            }
        }

        if (empty($errors)) {
            $excerpt = $form['excerpt'] !== '' ? $form['excerpt'] : makeExcerpt($form['content']);
            $categoryId = $form['category_id'] !== '' ? (int) $form['category_id'] : null;
            $slug = generateUniqueSlug($pdo, $form['title'], 'news');

            $publishedAt = null;
            if ($form['status'] === 'published') {
                $publishedAt = $form['published_at'] !== ''
                    ? date('Y-m-d H:i:s', strtotime($form['published_at']))
                    : date('Y-m-d H:i:s');
            } elseif ($form['published_at'] !== '') {
                $publishedAt = date('Y-m-d H:i:s', strtotime($form['published_at']));
            }

            $stmt = $pdo->prepare('
                INSERT INTO news (title, slug, excerpt, content, thumbnail, category_id, status, published_at, created_at, updated_at)
                VALUES (?, ?, ?, ?, ?, ?, ?, ?, NOW(), NOW())
            ');
            $stmt->execute([
                $form['title'], $slug, $excerpt, $form['content'],
                $upload['filename'], $categoryId, $form['status'], $publishedAt,
            ]);

            setFlash('success', 'Berita "' . $form['title'] . '" berhasil ditambahkan.');
            redirect('admin/news/index.php');
        }
    }
}

$pageTitle  = 'Tambah Berita';
$activeMenu = 'news';
require __DIR__ . '/../../includes/header.php';
?>

<div class="card" style="max-width:760px">
    <h3>Tambah Berita Baru</h3>

    <?php foreach ($errors as $error): ?>
        <div class="alert alert-error"><?= e($error) ?></div>
    <?php endforeach; ?>

    <form method="post" enctype="multipart/form-data" class="js-validate" novalidate>
        <?= csrfField() ?>

        <div class="form-group">
            <label for="title">Judul Berita <span style="color:var(--red)">*</span></label>
            <input type="text" id="title" name="title" class="form-control" required maxlength="255" value="<?= e($form['title']) ?>">
        </div>

        <div class="form-row">
            <div class="form-group">
                <label for="category_id">Kategori</label>
                <select id="category_id" name="category_id" class="form-control">
                    <option value="">-- Tanpa Kategori --</option>
                    <?php foreach ($categories as $cat): ?>
                        <option value="<?= $cat['id'] ?>" <?= (string) $cat['id'] === $form['category_id'] ? 'selected' : '' ?>><?= e($cat['name']) ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="form-group">
                <label for="status">Status</label>
                <select id="status" name="status" class="form-control">
                    <option value="draft" <?= $form['status'] === 'draft' ? 'selected' : '' ?>>Draft</option>
                    <option value="published" <?= $form['status'] === 'published' ? 'selected' : '' ?>>Published</option>
                </select>
            </div>
        </div>

        <div class="form-group">
            <label for="published_at">Tanggal Publikasi</label>
            <input type="datetime-local" id="published_at" name="published_at" class="form-control" value="<?= e($form['published_at']) ?>">
            <p class="hint">Kosongkan untuk memakai waktu sekarang saat status Published.</p>
        </div>

        <div class="form-group">
            <label for="excerpt">Ringkasan (Excerpt)</label>
            <textarea id="excerpt" name="excerpt" class="form-control" rows="2" maxlength="500"><?= e($form['excerpt']) ?></textarea>
            <p class="hint">Kosongkan agar dibuat otomatis dari isi berita.</p>
        </div>

        <div class="form-group">
            <label for="content">Isi Berita <span style="color:var(--red)">*</span></label>
            <textarea id="content" name="content" class="form-control" rows="10" required><?= e($form['content']) ?></textarea>
        </div>

        <div class="form-group">
            <label for="thumbnail-input">Thumbnail</label>
            <input type="file" id="thumbnail-input" name="thumbnail" class="form-control" accept=".jpg,.jpeg,.png,.webp">
            <p class="hint">Format JPG/PNG/WEBP, maksimal 2MB.</p>
            <img id="thumbnail-preview" style="display:none;max-width:220px;margin-top:10px;border-radius:8px;border:1px solid var(--line)" alt="Preview">
        </div>

        <div style="display:flex;gap:10px">
            <button type="submit" class="btn btn-primary">Simpan Berita</button>
            <a href="<?= e(BASE_URL) ?>/admin/news/index.php" class="btn btn-outline">Batal</a>
        </div>
    </form>
</div>

<?php require __DIR__ . '/../../includes/footer.php'; ?>
