<?php
require_once __DIR__ . '/../../includes/auth.php';
requireLogin();

$pdo = getConnection();
$id  = (int) ($_GET['id'] ?? 0);

$stmt = $pdo->prepare('SELECT * FROM news WHERE id = ?');
$stmt->execute([$id]);
$news = $stmt->fetch();

if (!$news) {
    setFlash('error', 'Berita tidak ditemukan.');
    redirect('admin/news/index.php');
}

$categories = $pdo->query('SELECT id, name FROM categories ORDER BY name ASC')->fetchAll();

$errors = [];
$form = [
    'title'        => $news['title'],
    'category_id'  => $news['category_id'] !== null ? (string) $news['category_id'] : '',
    'excerpt'      => $news['excerpt'],
    'content'      => $news['content'],
    'status'       => $news['status'],
    'published_at' => $news['published_at'] ? date('Y-m-d\TH:i', strtotime($news['published_at'])) : '',
];
$removeThumbnail = false;

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
        $removeThumbnail      = !empty($_POST['remove_thumbnail']);

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
            $slug = generateUniqueSlug($pdo, $form['title'], 'news', $id);

            $publishedAt = $news['published_at'];
            if ($form['status'] === 'published') {
                $publishedAt = $form['published_at'] !== ''
                    ? date('Y-m-d H:i:s', strtotime($form['published_at']))
                    : ($publishedAt ?: date('Y-m-d H:i:s'));
            } elseif ($form['published_at'] !== '') {
                $publishedAt = date('Y-m-d H:i:s', strtotime($form['published_at']));
            }

            $thumbnail = $news['thumbnail'];
            if ($upload['filename']) {
                deleteThumbnail($news['thumbnail']);
                $thumbnail = $upload['filename'];
            } elseif ($removeThumbnail) {
                deleteThumbnail($news['thumbnail']);
                $thumbnail = null;
            }

            $update = $pdo->prepare('
                UPDATE news
                SET title = ?, slug = ?, excerpt = ?, content = ?, thumbnail = ?, category_id = ?, status = ?, published_at = ?, updated_at = NOW()
                WHERE id = ?
            ');
            $update->execute([
                $form['title'], $slug, $excerpt, $form['content'],
                $thumbnail, $categoryId, $form['status'], $publishedAt, $id,
            ]);

            setFlash('success', 'Berita berhasil diperbarui.');
            redirect('admin/news/index.php');
        }
    }
}

$pageTitle  = 'Edit Berita';
$activeMenu = 'news';
require __DIR__ . '/../../includes/header.php';
?>

<div class="card" style="max-width:760px">
    <h3>Edit Berita</h3>

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
        </div>

        <div class="form-group">
            <label for="excerpt">Ringkasan (Excerpt)</label>
            <textarea id="excerpt" name="excerpt" class="form-control" rows="2" maxlength="500"><?= e($form['excerpt']) ?></textarea>
        </div>

        <div class="form-group">
            <label for="content">Isi Berita <span style="color:var(--red)">*</span></label>
            <textarea id="content" name="content" class="form-control" rows="10" required><?= e($form['content']) ?></textarea>
        </div>

        <div class="form-group">
            <label>Thumbnail Saat Ini</label>
            <?php if ($news['thumbnail']): ?>
                <div class="current-thumb">
                    <img src="<?= e(UPLOAD_URL . $news['thumbnail']) ?>" alt="">
                    <label style="font-weight:500;font-size:13px;display:flex;align-items:center;gap:6px">
                        <input type="checkbox" name="remove_thumbnail" value="1"> Hapus thumbnail ini
                    </label>
                </div>
            <?php else: ?>
                <p class="hint">Belum ada thumbnail.</p>
            <?php endif; ?>
            <input type="file" id="thumbnail-input" name="thumbnail" class="form-control" accept=".jpg,.jpeg,.png,.webp">
            <p class="hint">Upload untuk mengganti thumbnail. Format JPG/PNG/WEBP, maksimal 2MB.</p>
            <img id="thumbnail-preview" style="display:none;max-width:220px;margin-top:10px;border-radius:8px;border:1px solid var(--line)" alt="Preview">
        </div>

        <div style="display:flex;gap:10px">
            <button type="submit" class="btn btn-primary">Simpan Perubahan</button>
            <a href="<?= e(BASE_URL) ?>/admin/news/index.php" class="btn btn-outline">Batal</a>
        </div>
    </form>
</div>

<?php require __DIR__ . '/../../includes/footer.php'; ?>
