<?php
/**
 * includes/functions.php
 * Kumpulan fungsi bantuan (sanitasi, slug, upload, csrf, flash message, dsb).
 */

// Polyfill ringan jika ekstensi mbstring tidak aktif di hosting (jarang terjadi,
// hampir semua shared hosting PHP standar sudah mengaktifkannya secara default).
if (!function_exists('mb_strlen')) {
    function mb_strlen($string, $encoding = null) { return strlen($string); }
}
if (!function_exists('mb_substr')) {
    function mb_substr($string, $start, $length = null, $encoding = null) { return substr($string, $start, $length); }
}

/**
 * Auto-sync skema database terhadap database/schema.sql.
 * Dipanggil sekali di includes/bootstrap.php pada setiap request. Hanya benar-benar
 * menyentuh database ketika isi schema.sql berubah sejak terakhir kali dicek
 * (dibandingkan lewat hash yang disimpan di config/.schema_version), jadi request
 * normal sehari-hari tidak kena beban tambahan sama sekali.
 *
 * AMAN: hanya menjalankan pernyataan CREATE TABLE IF NOT EXISTS dari schema.sql,
 * tidak pernah DROP/ALTER/DELETE, sehingga tabel & data yang sudah ada tidak
 * pernah tersentuh — hanya tabel yang BELUM ADA yang akan dibuat.
 */
function ensureDatabaseSchema(PDO $pdo): void
{
    $schemaFile  = __DIR__ . '/../database/schema.sql';
    $versionFile = __DIR__ . '/../config/.schema_version';

    if (!is_readable($schemaFile)) {
        return;
    }

    $schemaSql   = file_get_contents($schemaFile);
    $currentHash = md5($schemaSql);
    $lastHash    = is_readable($versionFile) ? trim((string) file_get_contents($versionFile)) : '';

    if ($currentHash === $lastHash) {
        return; // Skema sudah sinkron, tidak perlu dicek ulang.
    }

    try {
        // Buang seluruh baris komentar (--) terlebih dahulu SEBELUM di-split per
        // statement. Ini penting: kalau sebuah blok komentar menempel langsung
        // sebelum sebuah CREATE TABLE tanpa ada ';' di antaranya (mis. komentar
        // header untuk tabel baru), split naif berbasis ';' akan menggabungkan
        // komentar itu dengan statement sesudahnya jadi satu potongan — dan kalau
        // potongan itu diperiksa hanya dari baris pertama (yang berupa komentar),
        // seluruh statement asli di dalamnya ikut terlewat/tidak pernah dijalankan.
        $cleanedSql = preg_replace('/^\s*--.*$/m', '', $schemaSql);
        $statements = array_filter(array_map('trim', explode(';', $cleanedSql)));
        foreach ($statements as $statement) {
            if ($statement === '' || str_starts_with(strtoupper($statement), 'SET NAMES')) {
                continue;
            }
            $pdo->exec($statement);
        }
        @file_put_contents($versionFile, $currentHash);
    } catch (Throwable $e) {
        // Sengaja tidak dilempar ulang: kalau auto-sync gagal (mis. user DB tidak
        // punya izin CREATE TABLE), halaman tetap harus bisa dimuat, bukan crash.
        error_log('ensureDatabaseSchema error: ' . $e->getMessage());
    }
}

/** Escape output untuk mencegah XSS. Singkatan pengganti htmlspecialchars(). */
function e(?string $value): string
{
    return htmlspecialchars($value ?? '', ENT_QUOTES, 'UTF-8');
}

/** Bersihkan input teks dari whitespace berlebih. */
function clean(?string $value): string
{
    return trim($value ?? '');
}

/** Redirect ke URL relatif terhadap BASE_URL lalu hentikan eksekusi. */
function redirect(string $path): void
{
    $path = ltrim($path, '/');
    header('Location: ' . BASE_URL . '/' . $path);
    exit;
}

/** Simpan pesan flash (notifikasi sukses/error) ke session untuk ditampilkan setelah redirect. */
function setFlash(string $type, string $message): void
{
    $_SESSION['flash'] = ['type' => $type, 'message' => $message];
}

/** Ambil & hapus pesan flash (dipanggil sekali di halaman tujuan). */
function getFlash(): ?array
{
    if (!empty($_SESSION['flash'])) {
        $flash = $_SESSION['flash'];
        unset($_SESSION['flash']);
        return $flash;
    }
    return null;
}

/** Buat slug URL-friendly dari string, unik terhadap tabel & kolom tertentu. */
function generateUniqueSlug(PDO $pdo, string $text, string $table, int $excludeId = 0): string
{
    $base = strtolower(trim($text));
    $base = preg_replace('/[^a-z0-9]+/', '-', $base);
    $base = trim($base, '-');
    if ($base === '') {
        $base = 'item';
    }

    $slug = $base;
    $i = 1;
    while (true) {
        $sql = "SELECT COUNT(*) FROM {$table} WHERE slug = ? AND id != ?";
        $stmt = $pdo->prepare($sql);
        $stmt->execute([$slug, $excludeId]);
        if ((int) $stmt->fetchColumn() === 0) {
            break;
        }
        $i++;
        $slug = $base . '-' . $i;
    }
    return $slug;
}

/** Format tanggal ke format Indonesia, mis. "15 Agustus 2026". */
function formatTanggalIndo(?string $datetime): string
{
    if (empty($datetime)) {
        return '-';
    }
    $bulan = [
        1 => 'Januari', 2 => 'Februari', 3 => 'Maret', 4 => 'April',
        5 => 'Mei', 6 => 'Juni', 7 => 'Juli', 8 => 'Agustus',
        9 => 'September', 10 => 'Oktober', 11 => 'November', 12 => 'Desember',
    ];
    $ts = strtotime($datetime);
    if ($ts === false) {
        return '-';
    }
    return (int) date('j', $ts) . ' ' . $bulan[(int) date('n', $ts)] . ' ' . date('Y', $ts);
}

/** Buat cuplikan (excerpt) otomatis dari teks panjang jika excerpt kosong. */
function makeExcerpt(string $text, int $length = 160): string
{
    $text = trim(strip_tags($text));
    if (mb_strlen($text) <= $length) {
        return $text;
    }
    return mb_substr($text, 0, $length) . '...';
}

/* =========================================================
 * CSRF PROTECTION
 * ========================================================= */

function csrfToken(): string
{
    if (empty($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['csrf_token'];
}

function csrfField(): string
{
    return '<input type="hidden" name="csrf_token" value="' . e(csrfToken()) . '">';
}

function verifyCsrf(): bool
{
    $token = $_POST['csrf_token'] ?? '';
    return !empty($token) && !empty($_SESSION['csrf_token']) && hash_equals($_SESSION['csrf_token'], $token);
}

/* =========================================================
 * FILE UPLOAD (dipakai bersama oleh Berita & Galeri)
 * =========================================================
 * Mengembalikan array ['success' => bool, 'filename' => string|null, 'error' => string|null]
 *
 * handleImageUpload() adalah fungsi generik: menerima $targetDir tujuan
 * penyimpanan. handleThumbnailUpload() (khusus Berita) dan
 * handleGalleryUpload() (khusus Galeri) hanyalah pembungkus tipis di
 * atasnya, supaya validasi ekstensi/MIME/ukuran/nama-file-aman konsisten
 * di seluruh project tanpa duplikasi logika.
 */
function handleImageUpload(array $file, string $targetDir): array
{
    if (!isset($file['error']) || $file['error'] === UPLOAD_ERR_NO_FILE) {
        return ['success' => true, 'filename' => null, 'error' => null]; // upload opsional
    }

    if ($file['error'] !== UPLOAD_ERR_OK) {
        return ['success' => false, 'filename' => null, 'error' => 'Terjadi kesalahan saat upload file.'];
    }

    if ($file['size'] > UPLOAD_MAX_SIZE) {
        return ['success' => false, 'filename' => null, 'error' => 'Ukuran file maksimal 2MB.'];
    }

    $ext = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
    if (!in_array($ext, UPLOAD_ALLOWED_EXT, true)) {
        return ['success' => false, 'filename' => null, 'error' => 'Ekstensi file tidak diizinkan. Gunakan JPG, PNG, atau WEBP.'];
    }

    // Validasi MIME type asli file (bukan hanya dari nama file)
    $finfo = finfo_open(FILEINFO_MIME_TYPE);
    $mime  = finfo_file($finfo, $file['tmp_name']);
    finfo_close($finfo);
    if (!in_array($mime, UPLOAD_ALLOWED_MIME, true)) {
        return ['success' => false, 'filename' => null, 'error' => 'Jenis file tidak valid.'];
    }

    // Pastikan file benar-benar gambar (proteksi tambahan agar tidak ada file executable menyamar)
    if (@getimagesize($file['tmp_name']) === false) {
        return ['success' => false, 'filename' => null, 'error' => 'File bukan gambar yang valid.'];
    }

    if (!is_dir($targetDir)) {
        mkdir($targetDir, 0755, true);
    }

    // Nama file aman & unik (tidak memakai nama asli)
    $newName = bin2hex(random_bytes(16)) . '.' . $ext;
    $destination = rtrim($targetDir, '/\\') . '/' . $newName;

    if (!move_uploaded_file($file['tmp_name'], $destination)) {
        return ['success' => false, 'filename' => null, 'error' => 'Gagal menyimpan file ke server.'];
    }

    return ['success' => true, 'filename' => $newName, 'error' => null];
}

function deleteImageFile(string $targetDir, ?string $filename): void
{
    if (!empty($filename)) {
        $path = rtrim($targetDir, '/\\') . '/' . $filename;
        if (is_file($path)) {
            @unlink($path);
        }
    }
}

/** Pembungkus khusus Berita (dipakai admin/news/*). */
function handleThumbnailUpload(array $file): array
{
    return handleImageUpload($file, UPLOAD_DIR);
}

function deleteThumbnail(?string $filename): void
{
    deleteImageFile(UPLOAD_DIR, $filename);
}

/** Pembungkus khusus Galeri (dipakai admin/gallery/*) — reuse mekanisme upload Berita. */
function handleGalleryUpload(array $file): array
{
    return handleImageUpload($file, GALLERY_UPLOAD_DIR);
}

function deleteGalleryImage(?string $filename): void
{
    deleteImageFile(GALLERY_UPLOAD_DIR, $filename);
}
