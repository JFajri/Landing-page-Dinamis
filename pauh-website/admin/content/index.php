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

// Menu navigasi: tampilkan bertingkat (submenu langsung di bawah induknya). Tombol urutan
// hanya menggeser di antara "saudara" (induk yang sama), sesuai admin/content/move.php.
$isFirstSibling = [];
$isLastSibling  = [];
if ($sectionKey === 'nav') {
    $titles = [];
    foreach ($items as $row) {
        $titles[(int) $row['id']] = $row['title'];
    }
    $fieldDefs['parent_id']['options'] = ['' => '—'] + array_map(fn($t) => $t, $titles);

    $tops = [];
    $kids = [];
    foreach ($items as $row) {
        $pid = (int) ($row['parent_id'] ?? 0);
        if ($pid > 0 && isset($titles[$pid])) {
            $kids[$pid][] = $row;
        } else {
            $tops[] = $row;
        }
    }
    $ordered = [];
    foreach ($tops as $i => $top) {
        $ordered[] = $top;
        $isFirstSibling[(int) $top['id']] = $i === 0;
        $isLastSibling[(int) $top['id']]  = $i === count($tops) - 1;
        foreach (($kids[(int) $top['id']] ?? []) as $j => $kid) {
            $ordered[] = $kid;
            $isFirstSibling[(int) $kid['id']] = $j === 0;
            $isLastSibling[(int) $kid['id']]  = $j === count($kids[(int) $top['id']]) - 1;
        }
    }
    $items = $ordered;
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
                                    <button type="submit" class="btn btn-outline btn-sm btn-icon" aria-label="Naikkan urutan" <?= ($isFirstSibling[(int) $row['id']] ?? $i === 0) ? 'disabled' : '' ?>>↑</button>
                                </form>
                                <form action="<?= e(BASE_URL) ?>/admin/content/move.php" method="post">
                                    <?= csrfField() ?>
                                    <input type="hidden" name="section" value="<?= e($sectionKey) ?>">
                                    <input type="hidden" name="id" value="<?= (int) $row['id'] ?>">
                                    <input type="hidden" name="dir" value="down">
                                    <button type="submit" class="btn btn-outline btn-sm btn-icon" aria-label="Turunkan urutan" <?= ($isLastSibling[(int) $row['id']] ?? $i === count($items) - 1) ? 'disabled' : '' ?>>↓</button>
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
                                    $text = $cell !== '' ? e(mb_strlen($cell) > 60 ? mb_substr($cell, 0, 60) . '…' : $cell) : '—';
                                    if ($sectionKey === 'nav' && $col === 'title' && (int) ($row['parent_id'] ?? 0) > 0) {
                                        $text = '<span aria-hidden="true" style="color:var(--muted,#6b7a8d)">↳</span> ' . $text; // submenu
                                    }
                                    echo $text;
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
