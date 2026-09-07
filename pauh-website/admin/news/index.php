<?php
require_once __DIR__ . '/../../includes/auth.php';
requireLogin();

$pdo = getConnection();

$statusFilter = clean($_GET['status'] ?? '');
$allowedStatus = ['draft', 'published'];
$where = '';
$params = [];
if (in_array($statusFilter, $allowedStatus, true)) {
    $where = 'WHERE n.status = ?';
    $params[] = $statusFilter;
}

$perPage = 10;
$page    = max(1, (int) ($_GET['page'] ?? 1));
$offset  = ($page - 1) * $perPage;

$countStmt = $pdo->prepare("SELECT COUNT(*) FROM news n $where");
$countStmt->execute($params);
$totalRows  = (int) $countStmt->fetchColumn();
$totalPages = max(1, (int) ceil($totalRows / $perPage));

$sql = "
    SELECT n.id, n.title, n.slug, n.status, n.thumbnail, n.published_at, n.created_at, c.name AS category_name
    FROM news n
    LEFT JOIN categories c ON c.id = n.category_id
    $where
    ORDER BY n.created_at DESC
    LIMIT $perPage OFFSET $offset
";
$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$newsList = $stmt->fetchAll();

$pageTitle  = 'Kelola Berita';
$activeMenu = 'news';
require __DIR__ . '/../../includes/header.php';
?>

<div class="card">
    <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:16px;flex-wrap:wrap;gap:12px">
        <h3 style="margin:0">Daftar Berita</h3>
        <a href="<?= e(BASE_URL) ?>/admin/news/create.php" class="btn btn-primary btn-sm">+ Tambah Berita</a>
    </div>

    <div style="display:flex;gap:8px;margin-bottom:18px">
        <a href="?status=" class="btn btn-sm <?= $statusFilter === '' ? 'btn-primary' : 'btn-outline' ?>">Semua</a>
        <a href="?status=published" class="btn btn-sm <?= $statusFilter === 'published' ? 'btn-primary' : 'btn-outline' ?>">Published</a>
        <a href="?status=draft" class="btn btn-sm <?= $statusFilter === 'draft' ? 'btn-primary' : 'btn-outline' ?>">Draft</a>
    </div>

    <?php if (empty($newsList)): ?>
        <div class="empty-state">
            <div class="icon">📰</div>
            <p>Tidak ada berita untuk filter ini.</p>
        </div>
    <?php else: ?>
        <table>
            <thead>
                <tr>
                    <th>Thumbnail</th>
                    <th>Judul</th>
                    <th>Kategori</th>
                    <th>Status</th>
                    <th>Tgl Publikasi</th>
                    <th>Aksi</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($newsList as $n): ?>
                    <tr>
                        <td>
                            <?php if ($n['thumbnail']): ?>
                                <img src="<?= e(UPLOAD_URL . $n['thumbnail']) ?>" class="thumb-preview" alt="">
                            <?php else: ?>
                                <div class="thumb-preview" style="display:flex;align-items:center;justify-content:center;font-size:11px;color:#999">—</div>
                            <?php endif; ?>
                        </td>
                        <td><?= e($n['title']) ?></td>
                        <td><?= e($n['category_name'] ?? '-') ?></td>
                        <td><span class="badge badge-<?= $n['status'] ?>"><?= e($n['status']) ?></span></td>
                        <td><?= $n['published_at'] ? e(formatTanggalIndo($n['published_at'])) : '-' ?></td>
                        <td class="actions">
                            <a href="<?= e(BASE_URL) ?>/admin/news/edit.php?id=<?= $n['id'] ?>" class="btn btn-outline btn-sm">Edit</a>
                            <form action="<?= e(BASE_URL) ?>/admin/news/delete.php" method="post" data-confirm="Hapus berita '<?= e($n['title']) ?>'? Tindakan ini tidak dapat dibatalkan.">
                                <?= csrfField() ?>
                                <input type="hidden" name="id" value="<?= $n['id'] ?>">
                                <button type="submit" class="btn btn-danger btn-sm">Hapus</button>
                            </form>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>

        <?php if ($totalPages > 1): ?>
            <div style="margin-top:18px;display:flex;gap:6px;flex-wrap:wrap">
                <?php for ($p = 1; $p <= $totalPages; $p++): ?>
                    <a href="?status=<?= e($statusFilter) ?>&page=<?= $p ?>" class="btn btn-sm <?= $p === $page ? 'btn-primary' : 'btn-outline' ?>"><?= $p ?></a>
                <?php endfor; ?>
            </div>
        <?php endif; ?>
    <?php endif; ?>
</div>

<?php require __DIR__ . '/../../includes/footer.php'; ?>
