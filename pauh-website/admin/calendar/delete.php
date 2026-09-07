<?php
require_once __DIR__ . '/../../includes/auth.php';
requireLogin();

if ($_SERVER['REQUEST_METHOD'] !== 'POST' || !verifyCsrf()) {
    setFlash('error', 'Permintaan tidak valid.');
    redirect('admin/calendar/index.php');
}

$pdo = getConnection();
$id  = (int) ($_POST['id'] ?? 0);

$stmt = $pdo->prepare('SELECT id, title FROM events WHERE id = ?');
$stmt->execute([$id]);
$event = $stmt->fetch();

if (!$event) {
    setFlash('error', 'Kegiatan tidak ditemukan.');
    redirect('admin/calendar/index.php');
}

try {
    $del = $pdo->prepare('DELETE FROM events WHERE id = ?');
    $del->execute([$id]);
    setFlash('success', 'Kegiatan "' . $event['title'] . '" berhasil dihapus.');
} catch (PDOException $e) {
    error_log('Delete event error: ' . $e->getMessage());
    setFlash('error', 'Gagal menghapus kegiatan. Silakan coba lagi.');
}

redirect('admin/calendar/index.php');
