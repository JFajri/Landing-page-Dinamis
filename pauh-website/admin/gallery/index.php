<?php
require_once __DIR__ . '/../../includes/auth.php';
requireLogin();

$pdo = getConnection();
$photos = $pdo->query('SELECT id, image, caption, description, status, created_at FROM gallery ORDER BY created_at DESC')->fetchAll();

$pageTitle  = 'Galeri Foto';
$activeMenu = 'gallery';
require __DIR__ . '/../../includes/header.php';
?>

<div class="card">
    <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:16px">
        <h3 style="margin:0">Daftar Foto Galeri</h3>
        <a href="<?= e(BASE_URL) ?>/admin/gallery/create.php" class="btn btn-primary btn-sm">+ Tambah Foto</a>
    </div>

    <?php if (empty($photos)): ?>
        <div class="empty-state">
            <div class="icon">🖼️</div>
            <p>Belum ada foto galeri. <a href="<?= e(BASE_URL) ?>/admin/gallery/create.php" style="color:var(--primary-2);font-weight:700">Tambah foto pertama →</a></p>
        </div>
    <?php else: ?>
        <table>
            <thead>
                <tr>
                    <th>Foto</th>
                    <th>Caption</th>
                    <th>Status</th>
                    <th>Ditambahkan</th>
                    <th>Aksi</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($photos as $p): ?>
                    <tr>
                        <td><img src="<?= e(GALLERY_UPLOAD_URL . $p['image']) ?>" class="thumb-preview" alt="<?= e($p['caption']) ?>"></td>
                        <td><?= e($p['caption']) ?></td>
                        <td>
                            <span class="badge badge-<?= $p['status'] === 'active' ? 'published' : 'draft' ?>">
                                <?= $p['status'] === 'active' ? 'Tampil' : 'Disembunyikan' ?>
                            </span>
                        </td>
                        <td><?= e(formatTanggalIndo($p['created_at'])) ?></td>
                        <td class="actions">
                            <a href="<?= e(BASE_URL) ?>/admin/gallery/edit.php?id=<?= $p['id'] ?>" class="btn btn-outline btn-sm">Edit</a>
                            <form action="<?= e(BASE_URL) ?>/admin/gallery/delete.php" method="post" data-confirm="Hapus foto '<?= e($p['caption']) ?>' dari galeri? Tindakan ini tidak dapat dibatalkan.">
                                <?= csrfField() ?>
                                <input type="hidden" name="id" value="<?= $p['id'] ?>">
                                <button type="submit" class="btn btn-danger btn-sm">Hapus</button>
                            </form>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    <?php endif; ?>
</div>

<?php require __DIR__ . '/../../includes/footer.php'; ?>
