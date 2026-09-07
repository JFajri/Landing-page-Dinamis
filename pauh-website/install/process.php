<?php
/**
 * install/process.php
 * Menjalankan proses instalasi sesungguhnya: koneksi -> (buat DB) -> buat tabel
 * -> buat admin -> tulis config -> buat install.lock.
 * Tidak memuat includes/bootstrap.php (aplikasi belum tentu terinstall).
 */

declare(strict_types=1);
error_reporting(E_ALL);
ini_set('display_errors', '0'); // jangan tampilkan error mentah ke user

$rootDir    = dirname(__DIR__);
$lockFile   = $rootDir . '/install.lock';
$configFile = $rootDir . '/config/database.php';
$schemaFile = __DIR__ . '/../database/schema.sql';

// Installer tidak boleh dijalankan jika sudah terkunci.
if (file_exists($lockFile)) {
    http_response_code(403);
    if (($_GET['action'] ?? '') === 'test_connection') {
        header('Content-Type: application/json');
        echo json_encode(['success' => false, 'message' => 'Instalasi sudah pernah selesai dilakukan.']);
    } else {
        echo 'Installation already completed.';
    }
    exit;
}

/** Bangun DSN opsional dengan nama database. */
function buildDsn(string $host, string $port, ?string $dbName = null): string
{
    $dsn = "mysql:host={$host};port={$port};charset=utf8mb4";
    if ($dbName) {
        $dsn .= ";dbname={$dbName}";
    }
    return $dsn;
}

/** Coba konek ke server (tanpa memilih database). */
function connectServer(string $host, string $port, string $user, string $pass): PDO
{
    return new PDO(buildDsn($host, $port), $user, $pass, [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_TIMEOUT => 6,
    ]);
}

function databaseExists(PDO $pdo, string $dbName): bool
{
    $stmt = $pdo->prepare('SELECT SCHEMA_NAME FROM information_schema.SCHEMATA WHERE SCHEMA_NAME = ?');
    $stmt->execute([$dbName]);
    return (bool) $stmt->fetchColumn();
}

/* =====================================================================
 * ACTION: TEST DATABASE CONNECTION (dipanggil via fetch(), balas JSON)
 * ===================================================================== */
if (($_GET['action'] ?? '') === 'test_connection') {
    header('Content-Type: application/json');

    $host = trim($_POST['db_host'] ?? '');
    $port = trim($_POST['db_port'] ?? '') ?: '3306';
    $name = trim($_POST['db_name'] ?? '');
    $user = trim($_POST['db_user'] ?? '');
    $pass = (string) ($_POST['db_pass'] ?? '');

    if ($host === '' || $user === '' || $name === '') {
        echo json_encode(['success' => false, 'message' => '❌ Host, username, dan nama database wajib diisi.']);
        exit;
    }

    try {
        $server = connectServer($host, $port, $user, $pass);
        if (databaseExists($server, $name)) {
            echo json_encode(['success' => true, 'message' => "✅ Koneksi berhasil. Database '{$name}' ditemukan dan siap digunakan."]);
        } else {
            echo json_encode(['success' => true, 'message' => "✅ Koneksi ke server database berhasil. Database '{$name}' belum ada — akan dibuat otomatis saat instalasi jika permission mengizinkan."]);
        }
    } catch (PDOException $e) {
        // Jangan tampilkan detail teknis/credential mentah ke user.
        echo json_encode(['success' => false, 'message' => '❌ Koneksi database gagal. Periksa kembali host, username, dan password Anda.']);
    }
    exit;
}

/* =====================================================================
 * ACTION: INSTALL (full form submission)
 * ===================================================================== */
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: index.php');
    exit;
}

$dbHost = trim($_POST['db_host'] ?? '');
$dbPort = trim($_POST['db_port'] ?? '') ?: '3306';
$dbName = trim($_POST['db_name'] ?? '');
$dbUser = trim($_POST['db_user'] ?? '');
$dbPass = (string) ($_POST['db_pass'] ?? '');

$adminName     = trim($_POST['admin_name'] ?? '');
$adminUsername = trim($_POST['admin_username'] ?? '');
$adminPassword = (string) ($_POST['admin_password'] ?? '');
$adminConfirm  = (string) ($_POST['admin_password_confirm'] ?? '');

$errors = [];

if ($dbHost === '' || $dbName === '' || $dbUser === '') {
    $errors[] = 'Konfigurasi database belum lengkap.';
}
if ($adminName === '' || $adminUsername === '') {
    $errors[] = 'Nama dan username admin wajib diisi.';
}
if (strlen($adminPassword) < 8) {
    $errors[] = 'Password admin minimal 8 karakter.';
}
if ($adminPassword !== $adminConfirm) {
    $errors[] = 'Konfirmasi password admin tidak sama.';
}

function renderResultPage(bool $success, array $checklist, array $errors = []): void
{
    ?>
    <!DOCTYPE html>
    <html lang="id">
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <meta name="robots" content="noindex, nofollow">
        <title><?= $success ? 'Instalasi Berhasil' : 'Instalasi Gagal' ?></title>
        <link rel="stylesheet" href="assets/install.css">
    </head>
    <body>
    <div class="wrap">
        <div class="box">
            <div class="header">
                <div class="icon"><?= $success ? '🎉' : '⚠️' ?></div>
                <h1><?= $success ? 'Installation Completed' : 'Instalasi Belum Selesai' ?></h1>
                <p><?= $success ? 'Website berhasil diinstall dan siap digunakan.' : 'Terjadi kendala saat proses instalasi.' ?></p>
            </div>

            <?php if (!empty($errors)): ?>
                <?php foreach ($errors as $err): ?>
                    <div class="alert alert-error"><?= htmlspecialchars($err, ENT_QUOTES, 'UTF-8') ?></div>
                <?php endforeach; ?>
            <?php endif; ?>

            <ul class="checklist">
                <?php foreach ($checklist as $label => $done): ?>
                    <li><span><?= htmlspecialchars($label, ENT_QUOTES, 'UTF-8') ?></span>
                        <span class="<?= $done ? 'ok' : '' ?>"><?= $done ? '✓ Selesai' : '— Belum' ?></span>
                    </li>
                <?php endforeach; ?>
            </ul>

            <?php if ($success): ?>
                <div class="actions-row">
                    <a href="../" class="btn btn-outline">🌐 Go to Website</a>
                    <a href="../admin/login.php" class="btn btn-primary">🔐 Go to Admin Login</a>
                </div>
                <p class="hint" style="margin-top:18px">Untuk keamanan tambahan, sebaiknya hapus atau rename folder <code>/install/</code> sekarang. Namun website tetap aman meskipun folder ini belum dihapus, karena <code>install.lock</code> mencegah instalasi kedua.</p>
            <?php else: ?>
                <div class="actions-row">
                    <a href="index.php" class="btn btn-primary btn-block">← Coba Lagi</a>
                </div>
            <?php endif; ?>
        </div>
    </div>
    </body>
    </html>
    <?php
}

$checklist = [
    'Connect database'      => false,
    'Create database (jika perlu)' => false,
    'Create tables'          => false,
    'Create admin account'   => false,
    'Create configuration'   => false,
    'Create installation lock' => false,
];

if (!empty($errors)) {
    renderResultPage(false, $checklist, $errors);
    exit;
}

// ---- STEP 1: Connect to server ----
try {
    $server = connectServer($dbHost, $dbPort, $dbUser, $dbPass);
    $checklist['Connect database'] = true;
} catch (PDOException $e) {
    $errors[] = 'Koneksi ke server database gagal. Periksa kembali host, username, dan password.';
    renderResultPage(false, $checklist, $errors);
    exit;
}

// ---- STEP 2: Ensure database exists (create if permitted) ----
try {
    if (databaseExists($server, $dbName)) {
        $checklist['Create database (jika perlu)'] = true;
    } else {
        try {
            $server->exec("CREATE DATABASE IF NOT EXISTS `{$dbName}` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci");
            $checklist['Create database (jika perlu)'] = true;
        } catch (PDOException $e) {
            $errors[] = "Database '{$dbName}' belum ada dan akun database ini tidak memiliki izin untuk membuatnya secara otomatis. Silakan buat database tersebut terlebih dahulu melalui cPanel/phpMyAdmin, lalu jalankan installer ini lagi.";
            renderResultPage(false, $checklist, $errors);
            exit;
        }
    }
} catch (PDOException $e) {
    $errors[] = 'Gagal memeriksa ketersediaan database.';
    renderResultPage(false, $checklist, $errors);
    exit;
}

// ---- STEP 3: Connect to the actual database & create tables ----
try {
    $pdo = new PDO(buildDsn($dbHost, $dbPort, $dbName), $dbUser, $dbPass, [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_TIMEOUT => 6,
    ]);

    if (!is_readable($schemaFile)) {
        throw new RuntimeException('Schema file tidak ditemukan.');
    }
    $schemaSql = file_get_contents($schemaFile);

    // Pisahkan per statement. Komentar (--) dibuang dulu sebelum di-split supaya
    // blok komentar yang menempel langsung sebelum sebuah CREATE TABLE (tanpa ';'
    // di antaranya) tidak ikut membuat statement tersebut terlewat.
    $cleanedSql = preg_replace('/^\s*--.*$/m', '', $schemaSql);
    $statements = array_filter(array_map('trim', explode(';', $cleanedSql)));
    foreach ($statements as $statement) {
        if ($statement === '' || str_starts_with(strtoupper($statement), 'SET NAMES')) {
            continue;
        }
        $pdo->exec($statement);
    }
    @file_put_contents($rootDir . '/config/.schema_version', md5($schemaSql));
    $checklist['Create tables'] = true;
} catch (Throwable $e) {
    error_log('Installer schema error: ' . $e->getMessage());
    $errors[] = 'Gagal membuat tabel database. Silakan periksa permission user database Anda (CREATE, ALTER).';
    renderResultPage(false, $checklist, $errors);
    exit;
}

// ---- STEP 4: Create admin account ----
try {
    $checkStmt = $pdo->prepare('SELECT COUNT(*) FROM users WHERE username = ?');
    $checkStmt->execute([$adminUsername]);
    if ((int) $checkStmt->fetchColumn() > 0) {
        $errors[] = 'Username admin tersebut sudah digunakan. Gunakan username lain.';
        renderResultPage(false, $checklist, $errors);
        exit;
    }

    $pdo->beginTransaction();
    $hashed = password_hash($adminPassword, PASSWORD_DEFAULT);
    $insert = $pdo->prepare('INSERT INTO users (name, username, password, role, created_at, updated_at) VALUES (?, ?, ?, ?, NOW(), NOW())');
    $insert->execute([$adminName, $adminUsername, $hashed, 'admin']);
    $pdo->commit();
    $checklist['Create admin account'] = true;
} catch (Throwable $e) {
    if ($pdo->inTransaction()) {
        $pdo->rollBack();
    }
    error_log('Installer admin creation error: ' . $e->getMessage());
    $errors[] = 'Gagal membuat akun admin.';
    renderResultPage(false, $checklist, $errors);
    exit;
}

// ---- STEP 5: Write config/database.php ----
try {
    $configContent = "<?php\n"
        . "/**\n * config/database.php\n * DIBUAT OTOMATIS OLEH INSTALLER pada " . date('Y-m-d H:i:s') . ".\n"
        . " * Jangan commit file ini ke version control publik.\n */\n\n"
        . "define('DB_HOST', " . var_export($dbHost, true) . ");\n"
        . "define('DB_PORT', " . var_export($dbPort, true) . ");\n"
        . "define('DB_NAME', " . var_export($dbName, true) . ");\n"
        . "define('DB_USER', " . var_export($dbUser, true) . ");\n"
        . "define('DB_PASS', " . var_export($dbPass, true) . ");\n\n"
        . "function getConnection(): PDO\n{\n"
        . "    static \$pdo = null;\n"
        . "    if (\$pdo === null) {\n"
        . "        \$dsn = 'mysql:host=' . DB_HOST . ';port=' . DB_PORT . ';dbname=' . DB_NAME . ';charset=utf8mb4';\n"
        . "        try {\n"
        . "            \$pdo = new PDO(\$dsn, DB_USER, DB_PASS, [\n"
        . "                PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,\n"
        . "                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,\n"
        . "                PDO::ATTR_EMULATE_PREPARES   => false,\n"
        . "            ]);\n"
        . "        } catch (PDOException \$e) {\n"
        . "            error_log('DB Connection Error: ' . \$e->getMessage());\n"
        . "            die('Terjadi kesalahan koneksi database. Silakan coba lagi nanti.');\n"
        . "        }\n"
        . "    }\n"
        . "    return \$pdo;\n"
        . "}\n";

    if (file_put_contents($configFile, $configContent) === false) {
        throw new RuntimeException('Tidak dapat menulis file konfigurasi.');
    }
    @chmod($configFile, 0640);
    $checklist['Create configuration'] = true;
} catch (Throwable $e) {
    error_log('Installer config write error: ' . $e->getMessage());
    $errors[] = 'Gagal menulis file konfigurasi database. Pastikan folder /config/ dapat ditulis oleh web server (writable).';
    renderResultPage(false, $checklist, $errors);
    exit;
}

// ---- STEP 6: Create installation lock ----
try {
    $lockContent = "Installed at: " . date('Y-m-d H:i:s') . "\n";
    if (file_put_contents($lockFile, $lockContent) === false) {
        throw new RuntimeException('Tidak dapat menulis file lock.');
    }
    $checklist['Create installation lock'] = true;
} catch (Throwable $e) {
    error_log('Installer lock write error: ' . $e->getMessage());
    $errors[] = 'Gagal membuat installation lock. Instalasi mungkin bisa dijalankan ulang secara tidak sengaja — segera hapus /install/ secara manual.';
    renderResultPage(false, $checklist, $errors);
    exit;
}

renderResultPage(true, $checklist);
