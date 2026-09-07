<?php
require_once __DIR__ . '/../../includes/auth.php';
requireLogin();

if ($_SERVER['REQUEST_METHOD'] !== 'POST' || !verifyCsrf()) {
    setFlash('error', 'Permintaan tidak valid.');
    redirect('admin/news/index.php');
}

$pdo = getConnection();
$id  = (int) ($_POST['id'] ?? 0);

$stmt = $pdo->prepare('SELECT id, title, thumbnail FROM news WHERE id = ?');
$stmt->execute([$id]);
$news = $stmt->fetch();

if (!$news) {
    setFlash('error', 'Berita tidak ditemukan.');
    redirect('admin/news/index.php');
}

try {
    $del = $pdo->prepare('DELETE FROM news WHERE id = ?');
    $del->execute([$id]);
    deleteThumbnail($news['thumbnail']);
    setFlash('success', 'Berita "' . $news['title'] . '" berhasil dihapus.');
} catch (PDOException $e) {
    error_log('Delete news error: ' . $e->getMessage());
    setFlash('error', 'Gagal menghapus berita. Silakan coba lagi.');
}

redirect('admin/news/index.php');
