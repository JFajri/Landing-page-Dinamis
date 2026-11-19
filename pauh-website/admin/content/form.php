<?php
/**
 * admin/content/form.php
 * Form tambah (tanpa ?id) dan edit (dengan ?id) untuk SEMUA jenis konten halaman.
 * Field yang tampil ditentukan oleh contentSections() di includes/settings.php.
 */
require_once __DIR__ . '/../../includes/auth.php';
require_once __DIR__ . '/../../includes/admin_forms.php';
requireLogin();

$pdo        = getConnection();
$sections   = contentSections();
$sectionKey = $_GET['section'] ?? '';
if (!isset($sections[$sectionKey])) {
    setFlash('error', 'Jenis konten tidak dikenal.');
    redirect('admin/index.php');
}
$cfg    = $sections[$sectionKey];
$listUrl = 'admin/content/index.php?section=' . urlencode($sectionKey);

$id  = (int) ($_GET['id'] ?? 0);
$row = null;
if ($id > 0) {
    $stmt = $pdo->prepare('SELECT * FROM content_items WHERE id = ? AND section = ?');
    $stmt->execute([$id, $sectionKey]);
    $row = $stmt->fetch();
    if (!$row) {
        setFlash('error', 'Data tidak ditemukan.');
        redirect($listUrl);
    }
}
$isEdit = $row !== null;

// Nilai awal form.
$form = ['status' => $isEdit ? $row['status'] : 'active'];
foreach ($cfg['fields'] as $field) {
    $form[$field['key']] = $isEdit ? (string) ($row[$field['key']] ?? '') : '';
}
$errors = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!verifyCsrf()) {
        $errors[] = 'Sesi form tidak valid. Silakan coba lagi.';
    } else {
        $values     = [];
        $oldImages  = [];
        $newUploads = [];

        foreach ($cfg['fields'] as $field) {
            $key     = $field['key'];
            $current = $form[$key];
            if ($field['type'] === 'image') {
                $new = resolveImageField($key, $field['label'], $current, $errors);
                if ($new !== $current) {
                    $oldImages[] = $current;
                    if ($new !== '' && !preg_match('#^https?://#i', $new)) {
                        $newUploads[] = $new;
                    }
                }
            } else {
                $new = readFieldValue($field, $current, $errors);
            }
            $values[$key] = $new;
        }
        $status = ($_POST['status'] ?? 'active') === 'inactive' ? 'inactive' : 'active';

        // Tampilkan kembali isian pengguna jika ada error.
        foreach ($values as $k => $v) {
            $form[$k] = $v;
        }
        $form['status'] = $status;

        if (empty($errors)) {
            try {
                $col = fn(string $k) => $values[$k] ?? ($isEdit ? $row[$k] : null);
                $val = fn(string $k) => ($col($k) === '' ? null : $col($k));

                if ($isEdit) {
                    $pdo->prepare('
                        UPDATE content_items
                        SET title = ?, value = ?, description = ?, icon = ?, image = ?, link_url = ?, status = ?, updated_at = NOW()
                        WHERE id = ? AND section = ?
                    ')->execute([
                        (string) $col('title'), $val('value'), $val('description'), $val('icon'), $val('image'), $val('link_url'),
                        $status, $id, $sectionKey,
                    ]);
                } else {
                    $stmt = $pdo->prepare('SELECT COALESCE(MAX(sort_order), 0) + 10 FROM content_items WHERE section = ?');
                    $stmt->execute([$sectionKey]);
                    $nextOrder = (int) $stmt->fetchColumn();

                    $pdo->prepare('
                        INSERT INTO content_items (section, title, value, description, icon, image, link_url, sort_order, status, created_at, updated_at)
                        VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, NOW(), NOW())
                    ')->execute([
                        $sectionKey, (string) $col('title'), $val('value'), $val('description'), $val('icon'), $val('image'), $val('link_url'),
                        $nextOrder, $status,
                    ]);
                }

                foreach ($oldImages as $old) {
                    deleteSiteImage($old);
                }
                setFlash('success', $cfg['singular'] . ' "' . $values['title'] . '" berhasil ' . ($isEdit ? 'diperbarui' : 'ditambahkan') . '.');
                redirect($listUrl);
            } catch (Throwable $e) {
                error_log('Save content item error: ' . $e->getMessage());
                $errors[] = 'Gagal menyimpan data. Silakan coba lagi.';
            }
        }

        // Penyimpanan tidak terjadi: buang file yang sudah terlanjur diunggah.
        foreach ($newUploads as $orphan) {
            deleteSiteImage($orphan);
        }
        if ($isEdit) {
            // Pratinjau gambar tetap menunjukkan gambar yang tersimpan, bukan yang gagal diunggah.
            foreach ($cfg['fields'] as $field) {
                if ($field['type'] === 'image') {
                    $form[$field['key']] = (string) ($row[$field['key']] ?? '');
                }
            }
        }
    }
}

$pageTitle  = ($isEdit ? 'Edit ' : 'Tambah ') . $cfg['singular'];
$activeMenu = 'content-' . $sectionKey;
require __DIR__ . '/../../includes/header.php';
?>

<div class="card" style="max-width:680px">
    <h3><?= e($cfg['icon']) ?> <?= e($pageTitle) ?></h3>

    <?php foreach ($errors as $error): ?>
        <div class="alert alert-error"><?= e($error) ?></div>
    <?php endforeach; ?>

    <form method="post" enctype="multipart/form-data" novalidate>
        <?= csrfField() ?>

        <?php foreach ($cfg['fields'] as $field): ?>
            <?php renderField($field, $form[$field['key']]); ?>
        <?php endforeach; ?>

        <div class="form-group">
            <label for="status">Status</label>
            <select id="status" name="status" class="form-control">
                <option value="active" <?= $form['status'] === 'active' ? 'selected' : '' ?>>Tampil di website</option>
                <option value="inactive" <?= $form['status'] === 'inactive' ? 'selected' : '' ?>>Sembunyikan</option>
            </select>
        </div>

        <div style="display:flex;gap:10px">
            <button type="submit" class="btn btn-primary"><?= $isEdit ? 'Simpan Perubahan' : 'Simpan' ?></button>
            <a href="<?= e(BASE_URL) ?>/<?= e($listUrl) ?>" class="btn btn-outline">Batal</a>
        </div>
    </form>
</div>

<?php require __DIR__ . '/../../includes/footer.php'; ?>
