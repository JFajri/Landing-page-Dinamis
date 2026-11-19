<?php
require_once __DIR__ . '/../../includes/auth.php';
requireLogin();

$sections   = contentSections();
$sectionKey = $_GET['section'] ?? '';
if (!isset($sections[$sectionKey])) {
    setFlash('error', 'Jenis konten tidak dikenal.');
    redirect('admin/index.php');
}
$cfg   = $sections[$sectionKey];
$items = contentItems($sectionKey, false);

// Label tampilan untuk nilai pilihan (mis. gaya menu "cta").
$fieldDefs = [];
foreach ($cfg['fields'] as $f) {
    $fieldDefs[$f['key']] = $f;
}

$pageTitle  = $cfg['label'];
$activeMenu = 'content-' . $sectionKey;
require __DIR__ . '/../../includes/header.php';
?>

<div class="card">
    <div style="display:flex;justify-content:space-between;align-items:center;gap:12px;flex-wrap:wrap;margin-bottom:6px">
        <h3 style="margin:0"><?= e($cfg['icon']) ?> <?= e($cfg['label']) ?></h3>
        <a href="<?= e(BASE_URL) ?>/admin/content/form.php?section=<?= e($sectionKey) ?>" class="btn btn-primary btn-sm">+ Tambah <?= e($cfg['singular']) ?></a>
    </div>
    <?php if (!empty($cfg['intro'])): ?>
        <p class="hint" style="margin:0 0 16px"><?= e($cfg['intro']) ?></p>
    <?php endif; ?>

    <?php if (empty($items)): ?>
        <div class="empty-state">
            <div class="icon"><?= e($cfg['icon']) ?></div>
            <p>Belum ada data. <a href="<?= e(BASE_URL) ?>/admin/content/form.php?section=<?= e($sectionKey) ?>" style="color:var(--primary-2);font-weight:700">Tambah <?= e(strtolower($cfg['singular'])) ?> pertama →</a></p>
        </div>
    <?php else: ?>
        <table>
            <thead>
                <tr>
                    <th>Urutan</th>
                    <?php foreach ($cfg['list'] as $col => $colLabel): ?>
                        <th><?= e($colLabel) ?></th>
                    <?php endforeach; ?>
                    <th>Status</th>
                    <th>Aksi</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($items as $i => $row): ?>
                    <tr>
                        <td>
                            <div class="actions">
                                <form action="<?= e(BASE_URL) ?>/admin/content/move.php" method="post">
                                    <?= csrfField() ?>
                                    <input type="hidden" name="section" value="<?= e($sectionKey) ?>">
                                    <input type="hidden" name="id" value="<?= (int) $row['id'] ?>">
                                    <input type="hidden" name="dir" value="up">
                                    <button type="submit" class="btn btn-outline btn-sm btn-icon" aria-label="Naikkan urutan" <?= $i === 0 ? 'disabled' : '' ?>>↑</button>
                                </form>
                                <form action="<?= e(BASE_URL) ?>/admin/content/move.php" method="post">
                                    <?= csrfField() ?>
                                    <input type="hidden" name="section" value="<?= e($sectionKey) ?>">
                                    <input type="hidden" name="id" value="<?= (int) $row['id'] ?>">
                                    <input type="hidden" name="dir" value="down">
                                    <button type="submit" class="btn btn-outline btn-sm btn-icon" aria-label="Turunkan urutan" <?= $i === count($items) - 1 ? 'disabled' : '' ?>>↓</button>
                                </form>
                            </div>
                        </td>
                        <?php foreach ($cfg['list'] as $col => $colLabel): ?>
                            <td>
                                <?php
                                $cell = (string) ($row[$col] ?? '');
                                if ($col === 'image') {
                                    echo $cell !== '' ? '<img src="' . e(mediaUrl($cell)) . '" class="thumb-preview" alt="">' : '—';
                                } elseif (isset($fieldDefs[$col]['options'])) {
                                    echo e($fieldDefs[$col]['options'][$cell] ?? $cell);
                                } else {
                                    echo $cell !== '' ? e(mb_strlen($cell) > 60 ? mb_substr($cell, 0, 60) . '…' : $cell) : '—';
                                }
                                ?>
                            </td>
                        <?php endforeach; ?>
                        <td>
                            <span class="badge badge-<?= $row['status'] === 'active' ? 'published' : 'draft' ?>">
                                <?= $row['status'] === 'active' ? 'Tampil' : 'Disembunyikan' ?>
                            </span>
                        </td>
                        <td class="actions">
                            <a href="<?= e(BASE_URL) ?>/admin/content/form.php?section=<?= e($sectionKey) ?>&amp;id=<?= (int) $row['id'] ?>" class="btn btn-outline btn-sm">Edit</a>
                            <form action="<?= e(BASE_URL) ?>/admin/content/delete.php" method="post" data-confirm="Hapus '<?= e($row['title']) ?>'? Tindakan ini tidak dapat dibatalkan.">
                                <?= csrfField() ?>
                                <input type="hidden" name="section" value="<?= e($sectionKey) ?>">
                                <input type="hidden" name="id" value="<?= (int) $row['id'] ?>">
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
