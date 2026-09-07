<?php
require_once __DIR__ . '/../../includes/auth.php';
requireLogin();

$pdo = getConnection();
$id  = (int) ($_GET['id'] ?? 0);

$stmt = $pdo->prepare('SELECT * FROM complaints WHERE id = ?');
$stmt->execute([$id]);
$complaint = $stmt->fetch();

if (!$complaint) {
    setFlash('error', 'Laporan tidak ditemukan.');
    redirect('admin/complaints/index.php');
}

$errors = [];
$statusOptions = ['baru' => 'Baru', 'dibaca' => 'Dibaca', 'diproses' => 'Diproses', 'selesai' => 'Selesai'];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!verifyCsrf()) {
        $errors[] = 'Sesi form tidak valid. Silakan coba lagi.';
    } else {
        $newStatus = $_POST['status'] ?? '';
        if (!array_key_exists($newStatus, $statusOptions)) {
            $errors[] = 'Status tidak valid.';
        } else {
            $update = $pdo->prepare('UPDATE complaints SET status = ?, updated_at = NOW() WHERE id = ?');
            $update->execute([$newStatus, $id]);
            setFlash('success', 'Status laporan berhasil diperbarui menjadi "' . $statusOptions[$newStatus] . '".');
            redirect('admin/complaints/detail.php?id=' . $id);
        }
    }
} elseif ($complaint['status'] === 'baru') {
    // Otomatis tandai "dibaca" begitu admin membuka detail laporan (seperti kotak masuk pada umumnya).
    $auto = $pdo->prepare("UPDATE complaints SET status = 'dibaca', updated_at = NOW() WHERE id = ?");
    $auto->execute([$id]);
    $complaint['status'] = 'dibaca';
}

$pageTitle  = 'Detail Aspirasi';
$activeMenu = 'complaints';
require __DIR__ . '/../../includes/header.php';
?>

<div style="margin-bottom:16px">
    <a href="<?= e(BASE_URL) ?>/admin/complaints/index.php" style="color:var(--primary-2);font-weight:700;font-size:13.5px">← Kembali ke Daftar Laporan</a>
</div>

<?php foreach ($errors as $error): ?>
    <div class="alert alert-error"><?= e($error) ?></div>
<?php endforeach; ?>

<div class="card" style="max-width:720px">
    <h3>Detail Laporan</h3>

    <table style="margin-bottom:6px">
        <tbody>
            <tr><th style="width:160px">Nama</th><td><?= e($complaint['name']) ?></td></tr>
            <tr><th>Kontak (Email/WA)</th><td><?= e($complaint['contact']) ?></td></tr>
            <tr><th>Kelurahan</th><td><?= e($complaint['kelurahan'] ?: '-') ?></td></tr>
            <tr><th>Tanggal Laporan</th><td><?= e(formatTanggalIndo($complaint['created_at'])) ?></td></tr>
            <tr><th>Status Saat Ini</th><td><span class="badge badge-status-<?= e($complaint['status']) ?>"><?= e($statusOptions[$complaint['status']] ?? $complaint['status']) ?></span></td></tr>
        </tbody>
    </table>

    <div class="form-group" style="margin-top:18px">
        <label>Isi Laporan / Aspirasi</label>
        <div style="background:#f8fafc;border:1px solid var(--line);border-radius:9px;padding:14px 16px;font-size:14px;line-height:1.8;white-space:pre-line"><?= e($complaint['message']) ?></div>
    </div>

    <form method="post" style="margin-top:20px;display:flex;gap:10px;align-items:flex-end;flex-wrap:wrap">
        <?= csrfField() ?>
        <div class="form-group" style="margin-bottom:0;flex:1;min-width:200px">
            <label for="status">Ubah Status</label>
            <select id="status" name="status" class="form-control">
                <?php foreach ($statusOptions as $value => $label): ?>
                    <option value="<?= e($value) ?>" <?= $complaint['status'] === $value ? 'selected' : '' ?>><?= e($label) ?></option>
                <?php endforeach; ?>
            </select>
        </div>
        <button type="submit" class="btn btn-primary">Simpan Status</button>
    </form>
</div>

<div class="card" style="max-width:720px">
    <form action="<?= e(BASE_URL) ?>/admin/complaints/delete.php" method="post" data-confirm="Hapus laporan dari '<?= e($complaint['name']) ?>'? Tindakan ini tidak dapat dibatalkan.">
        <?= csrfField() ?>
        <input type="hidden" name="id" value="<?= $complaint['id'] ?>">
        <button type="submit" class="btn btn-danger btn-sm">🗑️ Hapus Laporan Ini</button>
    </form>
</div>

<?php require __DIR__ . '/../../includes/footer.php'; ?>
