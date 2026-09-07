<?php
require_once __DIR__ . '/../../includes/auth.php';
requireLogin();

$pdo = getConnection();
$categories = $pdo->query("
    SELECT c.id, c.name, c.slug, c.created_at, COUNT(n.id) AS news_count
    FROM categories c
    LEFT JOIN news n ON n.category_id = c.id
    GROUP BY c.id, c.name, c.slug, c.created_at
    ORDER BY c.name ASC
")->fetchAll();

$pageTitle  = 'Kategori Berita';
$activeMenu = 'categories';
require __DIR__ . '/../../includes/header.php';
?>

<div class="card">
    <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:16px">
        <h3 style="margin:0">Daftar Kategori</h3>
        <a href="<?= e(BASE_URL) ?>/admin/categories/create.php" class="btn btn-primary btn-sm">+ Tambah Kategori</a>
    </div>

    <?php if (empty($categories)): ?>
        <div class="empty-state">
            <div class="icon">🏷️</div>
            <p>Belum ada kategori.</p>
        </div>
    <?php else: ?>
        <table>
            <thead>
                <tr>
                    <th>Nama</th>
                    <th>Slug</th>
                    <th>Jumlah Berita</th>
                    <th>Dibuat</th>
                    <th>Aksi</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($categories as $cat): ?>
                    <tr>
                        <td><?= e($cat['name']) ?></td>
                        <td><code><?= e($cat['slug']) ?></code></td>
                        <td><?= (int) $cat['news_count'] ?></td>
                        <td><?= e(formatTanggalIndo($cat['created_at'])) ?></td>
                        <td class="actions">
                            <a href="<?= e(BASE_URL) ?>/admin/categories/edit.php?id=<?= $cat['id'] ?>" class="btn btn-outline btn-sm">Edit</a>
                            <form action="<?= e(BASE_URL) ?>/admin/categories/delete.php" method="post" data-confirm="Hapus kategori '<?= e($cat['name']) ?>'? Berita di kategori ini tidak akan terhapus, hanya jadi tanpa kategori.">
                                <?= csrfField() ?>
                                <input type="hidden" name="id" value="<?= $cat['id'] ?>">
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
