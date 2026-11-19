<?php
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

$stmt = $pdo->prepare('SELECT id, title, image FROM content_items WHERE id = ? AND section = ?');
$stmt->execute([$id, $sectionKey]);
$item = $stmt->fetch();

if (!$item) {
    setFlash('error', 'Data tidak ditemukan.');
    redirect($listUrl);
}

try {
    $pdo->prepare('DELETE FROM content_items WHERE id = ? AND section = ?')->execute([$id, $sectionKey]);
    deleteSiteImage($item['image']);
    setFlash('success', '"' . $item['title'] . '" berhasil dihapus.');
} catch (PDOException $e) {
    error_log('Delete content item error: ' . $e->getMessage());
    setFlash('error', 'Gagal menghapus data. Silakan coba lagi.');
}

redirect($listUrl);
