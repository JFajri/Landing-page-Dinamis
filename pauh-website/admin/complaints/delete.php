<?php
require_once __DIR__ . '/../../includes/auth.php';
requireLogin();

if ($_SERVER['REQUEST_METHOD'] !== 'POST' || !verifyCsrf()) {
    setFlash('error', 'Permintaan tidak valid.');
    redirect('admin/complaints/index.php');
}

$pdo = getConnection();
$id  = (int) ($_POST['id'] ?? 0);

$stmt = $pdo->prepare('SELECT id, name FROM complaints WHERE id = ?');
$stmt->execute([$id]);
$complaint = $stmt->fetch();

if (!$complaint) {
    setFlash('error', 'Laporan tidak ditemukan.');
    redirect('admin/complaints/index.php');
}

try {
    $del = $pdo->prepare('DELETE FROM complaints WHERE id = ?');
    $del->execute([$id]);
    setFlash('success', 'Laporan dari "' . $complaint['name'] . '" berhasil dihapus.');
} catch (PDOException $e) {
    error_log('Delete complaint error: ' . $e->getMessage());
    setFlash('error', 'Gagal menghapus laporan. Silakan coba lagi.');
}

redirect('admin/complaints/index.php');
