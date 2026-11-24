<?php
/**
 * admin/content/move.php
 * Menaikkan/menurunkan urutan satu item dalam sectionnya. Seluruh urutan dinomori
 * ulang (10, 20, 30, …) agar tetap rapi walau sebelumnya ada nilai yang sama.
 */
require_once __DIR__ . '/../../includes/auth.php';
requireLogin();

$sectionKey = $_POST['section'] ?? '';
$sections   = contentSections();
$listUrl    = isset($sections[$sectionKey]) ? 'admin/content/index.php?section=' . urlencode($sectionKey) : 'admin/index.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST' || !verifyCsrf() || !isset($sections[$sectionKey])) {
    setFlash('error', 'Permintaan tidak valid.');
    redirect($listUrl);
}

$pdo = getConnection();
$id  = (int) ($_POST['id'] ?? 0);
$dir = ($_POST['dir'] ?? '') === 'up' ? -1 : 1;

try {
    // Pada menu navigasi, urutan hanya berlaku di antara "saudara" (menu utama dengan menu
    // utama, submenu dengan submenu dari induk yang sama).
    $siblingSql = '';
    $params     = [$sectionKey];
    if ($sectionKey === 'nav') {
        $cur = $pdo->prepare("SELECT parent_id FROM content_items WHERE id = ? AND section = 'nav'");
        $cur->execute([$id]);
        $parentId = $cur->fetchColumn();
        if ($parentId === false) {
            redirect($listUrl);
        }
        if ($parentId === null) {
            $siblingSql = ' AND parent_id IS NULL';
        } else {
            $siblingSql = ' AND parent_id = ?';
            $params[]   = (int) $parentId;
        }
    }

    $stmt = $pdo->prepare('SELECT id FROM content_items WHERE section = ?' . $siblingSql . ' ORDER BY sort_order ASC, id ASC');
    $stmt->execute($params);
    $ids = array_map('intval', $stmt->fetchAll(PDO::FETCH_COLUMN));

    $pos = array_search($id, $ids, true);
    $target = $pos === false ? false : $pos + $dir;

    if ($pos !== false && $target >= 0 && $target < count($ids)) {
        [$ids[$pos], $ids[$target]] = [$ids[$target], $ids[$pos]];

        $pdo->beginTransaction();
        $update = $pdo->prepare('UPDATE content_items SET sort_order = ? WHERE id = ? AND section = ?');
        foreach ($ids as $i => $itemId) {
            $update->execute([($i + 1) * 10, $itemId, $sectionKey]);
        }
        $pdo->commit();
    }
} catch (Throwable $e) {
    if ($pdo->inTransaction()) {
        $pdo->rollBack();
    }
    error_log('Move content item error: ' . $e->getMessage());
    setFlash('error', 'Gagal mengubah urutan. Silakan coba lagi.');
}

redirect($listUrl);
