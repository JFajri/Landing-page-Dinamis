<?php
require_once __DIR__ . '/../../includes/auth.php';
requireLogin();

if ($_SERVER['REQUEST_METHOD'] !== 'POST' || !verifyCsrf()) {
    setFlash('error', 'Permintaan tidak valid.');
    redirect('admin/gallery/index.php');
}

$pdo = getConnection();
$id  = (int) ($_POST['id'] ?? 0);

$stmt = $pdo->prepare('SELECT id, caption, image FROM gallery WHERE id = ?');
$stmt->execute([$id]);
$photo = $stmt->fetch();

if (!$photo) {
    setFlash('error', 'Foto galeri tidak ditemukan.');
    redirect('admin/gallery/index.php');
}

try {
    $del = $pdo->prepare('DELETE FROM gallery WHERE id = ?');
    $del->execute([$id]);
    deleteGalleryImage($photo['image']);
    setFlash('success', 'Foto "' . $photo['caption'] . '" berhasil dihapus dari galeri.');
} catch (PDOException $e) {
    error_log('Delete gallery error: ' . $e->getMessage());
    setFlash('error', 'Gagal menghapus foto. Silakan coba lagi.');
}

redirect('admin/gallery/index.php');
