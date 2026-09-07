<?php
/**
 * aspirasi/submit.php
 * Endpoint publik yang dipanggil via fetch() oleh form Aspirasi/Pengaduan
 * di beranda (lihat #complaintForm pada index.php & assets/js/main.js).
 * Balasan berupa JSON: {success: bool, message: string}.
 */

require_once __DIR__ . '/../includes/bootstrap.php';

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['success' => false, 'message' => 'Metode tidak diizinkan.']);
    exit;
}

$name      = clean($_POST['name'] ?? '');
$contact   = clean($_POST['contact'] ?? '');
$kelurahan = clean($_POST['kelurahan'] ?? '');
$message   = clean($_POST['message'] ?? '');

$errors = [];
if ($name === '') {
    $errors[] = 'Nama wajib diisi.';
}
if ($contact === '') {
    $errors[] = 'Email/WhatsApp wajib diisi.';
}
if ($kelurahan === '') {
    $errors[] = 'Kelurahan domisili wajib dipilih.';
}
if ($message === '') {
    $errors[] = 'Pesan pengaduan/aspirasi wajib diisi.';
}
if (mb_strlen($name) > 100 || mb_strlen($contact) > 150 || mb_strlen($kelurahan) > 100) {
    $errors[] = 'Salah satu isian melebihi batas panjang yang diizinkan.';
}

if (!empty($errors)) {
    http_response_code(422);
    echo json_encode(['success' => false, 'message' => implode(' ', $errors)]);
    exit;
}

try {
    $pdo = getConnection();
    $stmt = $pdo->prepare("
        INSERT INTO complaints (name, contact, kelurahan, message, status, created_at, updated_at)
        VALUES (?, ?, ?, ?, 'baru', NOW(), NOW())
    ");
    $stmt->execute([$name, $contact, $kelurahan, $message]);

    echo json_encode(['success' => true, 'message' => 'Laporan berhasil terkirim.']);
} catch (PDOException $e) {
    error_log('Complaint submit error: ' . $e->getMessage());
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => 'Terjadi kesalahan pada server. Silakan coba lagi nanti.']);
}
