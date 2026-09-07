<?php
require_once __DIR__ . '/../../includes/auth.php';
requireLogin();

if ($_SERVER['REQUEST_METHOD'] !== 'POST' || !verifyCsrf()) {
    setFlash('error', 'Permintaan tidak valid.');
    redirect('admin/categories/index.php');
}

$pdo = getConnection();
$id  = (int) ($_POST['id'] ?? 0);

$stmt = $pdo->prepare('SELECT id, name FROM categories WHERE id = ?');
$stmt->execute([$id]);
$category = $stmt->fetch();

if (!$category) {
    setFlash('error', 'Kategori tidak ditemukan.');
    redirect('admin/categories/index.php');
}

try {
    $del = $pdo->prepare('DELETE FROM categories WHERE id = ?');
    $del->execute([$id]);
    setFlash('success', 'Kategori "' . $category['name'] . '" berhasil dihapus.');
} catch (PDOException $e) {
    error_log('Delete category error: ' . $e->getMessage());
    setFlash('error', 'Gagal menghapus kategori. Silakan coba lagi.');
}

redirect('admin/categories/index.php');
