<?php
require_once __DIR__ . '/../../includes/auth.php';
requireLogin();

$pdo = getConnection();

$statusFilter = clean($_GET['status'] ?? '');
$allowedStatus = ['baru', 'dibaca', 'diproses', 'selesai'];
$where = '';
$params = [];
if (in_array($statusFilter, $allowedStatus, true)) {
    $where = 'WHERE status = ?';
    $params[] = $statusFilter;
}

$perPage = 15;
$page    = max(1, (int) ($_GET['page'] ?? 1));
$offset  = ($page - 1) * $perPage;

$countStmt = $pdo->prepare("SELECT COUNT(*) FROM complaints $where");
$countStmt->execute($params);
$totalRows  = (int) $countStmt->fetchColumn();
$totalPages = max(1, (int) ceil($totalRows / $perPage));

$sql = "SELECT id, name, contact, kelurahan, message, status, created_at FROM complaints $where ORDER BY created_at DESC LIMIT $perPage OFFSET $offset";
$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$complaints = $stmt->fetchAll();

$statusLabel = ['baru' => 'Baru', 'dibaca' => 'Dibaca', 'diproses' => 'Diproses', 'selesai' => 'Selesai'];

$pageTitle  = 'Aspirasi & Pengaduan';
$activeMenu = 'complaints';
require __DIR__ . '/../../includes/header.php';
?>

<div class="card">
    <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:16px;flex-wrap:wrap;gap:12px">
        <h3 style="margin:0">Daftar Laporan Warga</h3>
    </div>

    <div style="display:flex;gap:8px;margin-bottom:18px;flex-wrap:wrap">
        <a href="?status=" class="btn btn-sm <?= $statusFilter === '' ? 'btn-primary' : 'btn-outline' ?>">Semua</a>
        <a href="?status=baru" class="btn btn-sm <?= $statusFilter === 'baru' ? 'btn-primary' : 'btn-outline' ?>">Baru</a>
        <a href="?status=dibaca" class="btn btn-sm <?= $statusFilter === 'dibaca' ? 'btn-primary' : 'btn-outline' ?>">Dibaca</a>
        <a href="?status=diproses" class="btn btn-sm <?= $statusFilter === 'diproses' ? 'btn-primary' : 'btn-outline' ?>">Diproses</a>
        <a href="?status=selesai" class="btn btn-sm <?= $statusFilter === 'selesai' ? 'btn-primary' : 'btn-outline' ?>">Selesai</a>
    </div>

    <?php if (empty($complaints)): ?>
        <div class="empty-state">
            <div class="icon">📨</div>
            <p>Belum ada laporan aspirasi/pengaduan untuk filter ini.</p>
        </div>
    <?php else: ?>
        <table>
            <thead>
                <tr>
                    <th>Nama</th>
                    <th>Kelurahan</th>
                    <th>Isi Laporan</th>
                    <th>Status</th>
                    <th>Tanggal</th>
                    <th>Aksi</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($complaints as $c): ?>
                    <tr>
                        <td><?= e($c['name']) ?></td>
                        <td><?= e($c['kelurahan'] ?: '-') ?></td>
                        <td><?= e(makeExcerpt($c['message'], 60)) ?></td>
                        <td><span class="badge badge-status-<?= e($c['status']) ?>"><?= e($statusLabel[$c['status']] ?? $c['status']) ?></span></td>
                        <td><?= e(formatTanggalIndo($c['created_at'])) ?></td>
                        <td class="actions">
                            <a href="<?= e(BASE_URL) ?>/admin/complaints/detail.php?id=<?= $c['id'] ?>" class="btn btn-outline btn-sm">Lihat</a>
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
