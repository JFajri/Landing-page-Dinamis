<?php
require_once __DIR__ . '/../../includes/auth.php';
require_once __DIR__ . '/../../includes/admin_forms.php';
requireLogin();

$pdo    = getConnection();
$schema = settingsSchema();

$groupKey = $_GET['group'] ?? array_key_first($schema);
if (!isset($schema[$groupKey])) {
    $groupKey = array_key_first($schema);
}
$group  = $schema[$groupKey];
$errors = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!verifyCsrf()) {
        $errors[] = 'Sesi form tidak valid. Silakan coba lagi.';
    } else {
        $toSave     = [];
        $oldImages  = []; // file lama yang dihapus setelah penyimpanan sukses
        $newUploads = []; // file yang baru diunggah (dibersihkan jika penyimpanan gagal)

        foreach ($group['fields'] as $field) {
            $key     = $field['key'];
            $current = setting($key);

            if ($field['type'] === 'image') {
                $newValue = resolveImageField($key, $field['label'], $current, $errors);
                if ($newValue !== $current) {
                    $oldImages[] = $current;
                    if ($newValue !== '' && !preg_match('#^https?://#i', $newValue)) {
                        $newUploads[] = $newValue;
                    }
                }
            } else {
                $newValue = readFieldValue($field, $current, $errors);
            }
            $toSave[$key] = $newValue;
        }

        if (empty($errors)) {
            try {
                saveSettings($pdo, $toSave);
                foreach ($oldImages as $old) {
                    deleteSiteImage($old);
                }
                setFlash('success', 'Pengaturan "' . $group['label'] . '" berhasil disimpan.');
                redirect('admin/settings/index.php?group=' . urlencode($groupKey));
            } catch (Throwable $e) {
                error_log('Save settings error: ' . $e->getMessage());
                $errors[] = 'Gagal menyimpan pengaturan. Silakan coba lagi.';
            }
        }

        // Penyimpanan tidak terjadi (validasi/DB gagal): buang file yang sudah terlanjur diunggah.
        if (!empty($errors)) {
            foreach ($newUploads as $orphan) {
                deleteSiteImage($orphan);
            }
        }
    }
}

$pageTitle  = 'Pengaturan: ' . $group['label'];
$activeMenu = 'settings-' . $groupKey;
require __DIR__ . '/../../includes/header.php';
?>

<div class="card" style="max-width:760px">
    <h3><?= e($group['icon']) ?> <?= e($group['label']) ?></h3>
    <?php if (!empty($group['intro'])): ?>
        <p class="hint" style="margin:-8px 0 18px"><?= e($group['intro']) ?></p>
    <?php endif; ?>

    <?php foreach ($errors as $error): ?>
        <div class="alert alert-error"><?= e($error) ?></div>
    <?php endforeach; ?>

    <form method="post" enctype="multipart/form-data" novalidate>
        <?= csrfField() ?>

        <?php foreach ($group['fields'] as $field): ?>
            <?php
            // Saat validasi gagal, tampilkan kembali isian terakhir pengguna (bukan nilai tersimpan).
            $shown = setting($field['key']);
            if ($_SERVER['REQUEST_METHOD'] === 'POST' && $errors && $field['type'] !== 'image' && isset($_POST['f'][$field['key']]) && is_string($_POST['f'][$field['key']])) {
                $shown = $_POST['f'][$field['key']];
            }
            renderField($field, $shown);
            ?>
        <?php endforeach; ?>

        <div style="display:flex;gap:10px;flex-wrap:wrap">
            <button type="submit" class="btn btn-primary">Simpan Pengaturan</button>
            <a href="<?= e(BASE_URL) ?>/" target="_blank" rel="noopener" class="btn btn-outline">Lihat Website ↗</a>
        </div>
    </form>
</div>

<?php require __DIR__ . '/../../includes/footer.php'; ?>
