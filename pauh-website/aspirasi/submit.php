<?php
/**
 * aspirasi/submit.php
 * Endpoint publik yang dipanggil via fetch() oleh form Aspirasi/Pengaduan
 * di beranda (lihat #complaintForm pada index.php & assets/js/main.js).
 * Balasan berupa JSON: {success: bool, message: string}.
 *
 * Perlindungan anti-spam berlapis (semua dari sisi server, tanpa layanan pihak ketiga):
 *   1. Token CSRF dari session pengunjung.
 *   2. Honeypot: kolom tersembunyi "website" yang tidak akan diisi manusia.
 *   3. Waktu minimum antara halaman dimuat dan formulir dikirim.
 *   4. Pembatasan laju per alamat IP (disimpan sebagai HMAC, bukan IP mentah).
 *   5. Validasi isian + penolakan pesan yang berisi banyak tautan.
 */

require_once __DIR__ . '/../includes/bootstrap.php';

header('Content-Type: application/json; charset=UTF-8');
header('Cache-Control: no-store');

function jsonReply(int $status, bool $success, string $message): void
{
    http_response_code($status);
    echo json_encode(['success' => $success, 'message' => $message], JSON_UNESCAPED_UNICODE);
    exit;
}

const COMPLAINT_MIN_FILL_SECONDS = 3;   // formulir tidak mungkin diisi manusia lebih cepat dari ini
const COMPLAINT_MAX_PER_HOUR     = 5;   // laporan per alamat IP per jam

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Allow: POST');
    jsonReply(405, false, 'Metode tidak diizinkan.');
}

if (!verifyCsrf()) {
    jsonReply(403, false, 'Sesi formulir sudah berakhir. Silakan muat ulang halaman lalu coba lagi.');
}

// Honeypot terisi = hampir pasti bot. Balas "sukses" agar bot tidak belajar menghindarinya.
if (clean($_POST['website'] ?? '') !== '') {
    jsonReply(200, true, 'Laporan berhasil terkirim.');
}

$shownAt = (int) ($_SESSION['aspirasi_form_at'] ?? 0);
if ($shownAt === 0 || (time() - $shownAt) < COMPLAINT_MIN_FILL_SECONDS) {
    jsonReply(429, false, 'Mohon periksa isian Anda lalu kirim kembali dalam beberapa detik.');
}

$name      = clean($_POST['name'] ?? '');
$contact   = clean($_POST['contact'] ?? '');
$kelurahan = clean($_POST['kelurahan'] ?? '');
$message   = clean($_POST['message'] ?? '');
$consent   = ($_POST['consent'] ?? '') === '1';

$errors = [];
if ($name === '') {
    $errors[] = 'Nama wajib diisi.';
} elseif (mb_strlen($name) < 2 || mb_strlen($name) > 100) {
    $errors[] = 'Nama harus 2–100 karakter.';
}

if ($contact === '') {
    $errors[] = 'Email/WhatsApp wajib diisi.';
} elseif (mb_strlen($contact) > 150 || !isValidContact($contact)) {
    $errors[] = 'Isi dengan alamat email yang valid atau nomor WhatsApp (8–15 digit).';
}

if ($kelurahan === '') {
    $errors[] = 'Kelurahan domisili wajib dipilih.';
} elseif (mb_strlen($kelurahan) > 100) {
    $errors[] = 'Kelurahan tidak valid.';
}

if ($message === '') {
    $errors[] = 'Pesan pengaduan/aspirasi wajib diisi.';
} elseif (mb_strlen($message) < 10) {
    $errors[] = 'Pesan terlalu singkat. Mohon tuliskan minimal 10 karakter.';
} elseif (mb_strlen($message) > 2000) {
    $errors[] = 'Pesan terlalu panjang (maksimal 2000 karakter).';
}

if (!$consent) {
    $errors[] = 'Anda perlu menyetujui Kebijakan Privasi untuk mengirim laporan.';
}

if (!empty($errors)) {
    jsonReply(422, false, implode(' ', $errors));
}

try {
    $pdo = getConnection();

    // Kelurahan harus salah satu dari daftar resmi (jika daftarnya diisi di Admin).
    $validKelurahan = array_column(contentItems('kelurahan'), 'title');
    if ($validKelurahan && !in_array($kelurahan, $validKelurahan, true)) {
        jsonReply(422, false, 'Kelurahan yang dipilih tidak valid.');
    }

    // Pesan yang dipenuhi tautan hampir selalu spam.
    if (preg_match_all('#https?://|www\.#i', $message) > 2) {
        jsonReply(422, false, 'Pesan tidak boleh memuat lebih dari dua tautan.');
    }

    $ip = clientIp();
    if (rateLimitCount($pdo, 'complaint', $ip, 3600) >= COMPLAINT_MAX_PER_HOUR) {
        header('Retry-After: 3600');
        jsonReply(429, false, 'Terlalu banyak laporan dari jaringan Anda dalam satu jam terakhir. Silakan coba lagi nanti.');
    }

    $stmt = $pdo->prepare("
        INSERT INTO complaints (name, contact, kelurahan, message, status, created_at, updated_at)
        VALUES (?, ?, ?, ?, 'baru', NOW(), NOW())
    ");
    $stmt->execute([$name, $contact, $kelurahan, $message]);

    rateLimitHit($pdo, 'complaint', $ip);
    $_SESSION['aspirasi_form_at'] = time(); // laporan berikutnya juga harus melewati jeda minimum

    jsonReply(200, true, 'Laporan berhasil terkirim.');
} catch (PDOException $e) {
    error_log('Complaint submit error: ' . $e->getMessage());
    jsonReply(500, false, 'Terjadi kesalahan pada server. Silakan coba lagi nanti.');
}
