<?php
require_once __DIR__ . '/../../includes/auth.php';
requireLogin();

$pdo = getConnection();
$id  = (int) ($_GET['id'] ?? 0);

$stmt = $pdo->prepare('SELECT * FROM events WHERE id = ?');
$stmt->execute([$id]);
$event = $stmt->fetch();

if (!$event) {
    setFlash('error', 'Kegiatan tidak ditemukan.');
    redirect('admin/calendar/index.php');
}

$errors = [];
$form = [
    'title'       => $event['title'],
    'event_date'  => $event['event_date'],
    'event_time'  => $event['event_time'] ? substr($event['event_time'], 0, 5) : '',
    'location'    => $event['location'],
    'description' => $event['description'],
    'status'      => $event['status'],
];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!verifyCsrf()) {
        $errors[] = 'Sesi form tidak valid. Silakan coba lagi.';
    } else {
        $form['title']       = clean($_POST['title'] ?? '');
        $form['event_date']  = clean($_POST['event_date'] ?? '');
        $form['event_time']  = clean($_POST['event_time'] ?? '');
        $form['location']    = clean($_POST['location'] ?? '');
        $form['description'] = clean($_POST['description'] ?? '');
        $form['status']      = ($_POST['status'] ?? 'active') === 'inactive' ? 'inactive' : 'active';

        if ($form['title'] === '') {
            $errors[] = 'Judul kegiatan wajib diisi.';
        } elseif (mb_strlen($form['title']) > 150) {
            $errors[] = 'Judul kegiatan maksimal 150 karakter.';
        }
        if ($form['event_date'] === '' || !preg_match('/^\d{4}-\d{2}-\d{2}$/', $form['event_date'])) {
            $errors[] = 'Tanggal kegiatan wajib diisi dengan format yang benar.';
        }
        if ($form['event_time'] !== '' && !preg_match('/^\d{2}:\d{2}$/', $form['event_time'])) {
            $errors[] = 'Format waktu tidak valid.';
        }

        if (empty($errors)) {
            $update = $pdo->prepare('
                UPDATE events
                SET title = ?, event_date = ?, event_time = ?, location = ?, description = ?, status = ?, updated_at = NOW()
                WHERE id = ?
            ');
            $update->execute([
                $form['title'],
                $form['event_date'],
                $form['event_time'] !== '' ? $form['event_time'] . ':00' : null,
                $form['location'] ?: null,
                $form['description'] ?: null,
                $form['status'],
                $id,
            ]);

            setFlash('success', 'Kegiatan berhasil diperbarui.');
            redirect('admin/calendar/index.php');
        }
    }
}

$pageTitle  = 'Edit Kegiatan';
$activeMenu = 'calendar';
require __DIR__ . '/../../includes/header.php';
?>

<div class="card" style="max-width:640px">
    <h3>Edit Kegiatan Kalender</h3>

    <?php foreach ($errors as $error): ?>
        <div class="alert alert-error"><?= e($error) ?></div>
    <?php endforeach; ?>

    <form method="post" class="js-validate" novalidate>
        <?= csrfField() ?>

        <div class="form-group">
            <label for="title">Judul Kegiatan <span style="color:var(--red)">*</span></label>
            <input type="text" id="title" name="title" class="form-control" required maxlength="150" value="<?= e($form['title']) ?>">
        </div>

        <div class="form-row">
            <div class="form-group">
                <label for="event_date">Tanggal <span style="color:var(--red)">*</span></label>
                <input type="date" id="event_date" name="event_date" class="form-control" required value="<?= e($form['event_date']) ?>">
            </div>
            <div class="form-group">
                <label for="event_time">Waktu (opsional)</label>
                <input type="time" id="event_time" name="event_time" class="form-control" value="<?= e($form['event_time']) ?>">
            </div>
        </div>

        <div class="form-group">
            <label for="location">Lokasi (opsional)</label>
            <input type="text" id="location" name="location" class="form-control" maxlength="150" value="<?= e($form['location']) ?>">
        </div>

        <div class="form-group">
            <label for="description">Deskripsi (opsional)</label>
            <textarea id="description" name="description" class="form-control" rows="3" maxlength="500"><?= e($form['description']) ?></textarea>
        </div>

        <div class="form-group">
            <label for="status">Status</label>
            <select id="status" name="status" class="form-control">
                <option value="active" <?= $form['status'] === 'active' ? 'selected' : '' ?>>Tampil di Kalender</option>
                <option value="inactive" <?= $form['status'] === 'inactive' ? 'selected' : '' ?>>Sembunyikan</option>
            </select>
        </div>

        <div style="display:flex;gap:10px">
            <button type="submit" class="btn btn-primary">Simpan Perubahan</button>
            <a href="<?= e(BASE_URL) ?>/admin/calendar/index.php" class="btn btn-outline">Batal</a>
        </div>
    </form>
</div>

<?php require __DIR__ . '/../../includes/footer.php'; ?>
