<?php
require_once __DIR__ . '/../../includes/auth.php';
requireLogin();

$pdo = getConnection();
$events = $pdo->query('SELECT id, title, event_date, event_time, location, status FROM events ORDER BY event_date DESC, event_time DESC')->fetchAll();

$pageTitle  = 'Kalender & Event';
$activeMenu = 'calendar';
require __DIR__ . '/../../includes/header.php';
?>

<div class="card">
    <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:16px">
        <h3 style="margin:0">Daftar Kegiatan</h3>
        <a href="<?= e(BASE_URL) ?>/admin/calendar/create.php" class="btn btn-primary btn-sm">+ Tambah Kegiatan</a>
    </div>

    <?php if (empty($events)): ?>
        <div class="empty-state">
            <div class="icon">📅</div>
            <p>Belum ada kegiatan. <a href="<?= e(BASE_URL) ?>/admin/calendar/create.php" style="color:var(--primary-2);font-weight:700">Tambah kegiatan pertama →</a></p>
        </div>
    <?php else: ?>
        <table>
            <thead>
                <tr>
                    <th>Judul</th>
                    <th>Tanggal</th>
                    <th>Waktu</th>
                    <th>Lokasi</th>
                    <th>Status</th>
                    <th>Aksi</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($events as $ev): ?>
                    <tr>
                        <td><?= e($ev['title']) ?></td>
                        <td><?= e(formatTanggalIndo($ev['event_date'])) ?></td>
                        <td><?= $ev['event_time'] ? e(substr($ev['event_time'], 0, 5)) . ' WIB' : '-' ?></td>
                        <td><?= e($ev['location'] ?: '-') ?></td>
                        <td>
                            <span class="badge badge-<?= $ev['status'] === 'active' ? 'published' : 'draft' ?>">
                                <?= $ev['status'] === 'active' ? 'Tampil' : 'Disembunyikan' ?>
                            </span>
                        </td>
                        <td class="actions">
                            <a href="<?= e(BASE_URL) ?>/admin/calendar/edit.php?id=<?= $ev['id'] ?>" class="btn btn-outline btn-sm">Edit</a>
                            <form action="<?= e(BASE_URL) ?>/admin/calendar/delete.php" method="post" data-confirm="Hapus kegiatan '<?= e($ev['title']) ?>'? Tindakan ini tidak dapat dibatalkan.">
                                <?= csrfField() ?>
                                <input type="hidden" name="id" value="<?= $ev['id'] ?>">
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
