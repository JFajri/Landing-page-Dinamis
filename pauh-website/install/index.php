<?php
/**
 * install/index.php
 * Installer satu-kali. TIDAK memuat includes/bootstrap.php karena aplikasi
 * belum tentu terinstall — installer berdiri sendiri.
 */

$lockFile = __DIR__ . '/../install.lock';

if (file_exists($lockFile)) {
    // Sudah pernah diinstall — installer tidak boleh dijalankan lagi.
    ?>
    <!DOCTYPE html>
    <html lang="id">
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <meta name="robots" content="noindex, nofollow">
        <meta name="theme-color" content="#0d2c52">
        <link rel="icon" type="image/svg+xml" href="../assets/favicon.svg">
        <title>Instalasi Sudah Selesai</title>
        <link rel="stylesheet" href="assets/install.css">
    </head>
    <body>
        <div class="wrap">
            <div class="box" style="text-align:center">
                <div class="header">
                    <div class="icon">✅</div>
                    <h1>Installation Already Completed</h1>
                    <p>Website ini sudah pernah diinstall sebelumnya. Untuk alasan keamanan, installer tidak dapat dijalankan kembali.</p>
                </div>
                <div class="actions-row">
                    <a href="../" class="btn btn-outline">Ke Website</a>
                    <a href="../admin/login.php" class="btn btn-primary">Ke Login Admin</a>
                </div>
                <p class="hint" style="margin-top:18px">Jika Anda perlu menginstall ulang, hapus file <code>install.lock</code> dan folder terkait secara manual di server (berisiko — hanya untuk fresh setup).</p>
            </div>
        </div>
    </body>
    </html>
    <?php
    exit;
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="robots" content="noindex, nofollow">
    <meta name="theme-color" content="#0d2c52">
    <link rel="icon" type="image/svg+xml" href="../assets/favicon.svg">
    <title>Installer - Portal Kecamatan Pauh</title>
    <link rel="stylesheet" href="assets/install.css">
</head>
<body>
<div class="wrap">
    <div class="box">
        <div class="header">
            <div class="icon"><img src="../assets/favicon.svg" alt="" width="56" height="56"></div>
            <h1>Instalasi Portal Kecamatan Pauh</h1>
            <p>Lengkapi konfigurasi database dan buat akun admin untuk memulai.</p>
        </div>

        <div class="steps"><span class="done"></span><span class="done"></span><span></span></div>

        <div id="php-check"></div>

        <form id="install-form" action="process.php" method="post" autocomplete="off">
            <fieldset>
                <legend>1. Konfigurasi Database</legend>

                <div class="form-row">
                    <div class="form-group">
                        <label for="db_host">Database Host</label>
                        <input type="text" id="db_host" name="db_host" class="form-control" value="localhost" required>
                    </div>
                    <div class="form-group">
                        <label for="db_port">Port</label>
                        <input type="text" id="db_port" name="db_port" class="form-control" value="3306" required>
                    </div>
                </div>

                <div class="form-group">
                    <label for="db_name">Nama Database</label>
                    <input type="text" id="db_name" name="db_name" class="form-control" placeholder="db_pauh" required>
                    <p class="hint">Jika database belum ada, installer akan mencoba membuatnya otomatis. Jika hosting Anda tidak mengizinkan (mis. shared hosting cPanel), buat database ini lebih dulu lewat cPanel/phpMyAdmin.</p>
                </div>

                <div class="form-row">
                    <div class="form-group">
                        <label for="db_user">Database Username</label>
                        <input type="text" id="db_user" name="db_user" class="form-control" required>
                    </div>
                    <div class="form-group">
                        <label for="db_pass">Database Password</label>
                        <input type="password" id="db_pass" name="db_pass" class="form-control">
                    </div>
                </div>

                <button type="button" id="test-connection-btn" class="btn btn-outline">🔌 Test Database Connection</button>
                <div id="test-result"></div>
            </fieldset>

            <fieldset>
                <legend>2. Buat Akun Admin</legend>

                <div class="form-group">
                    <label for="admin_name">Nama Lengkap</label>
                    <input type="text" id="admin_name" name="admin_name" class="form-control" required>
                </div>
                <div class="form-group">
                    <label for="admin_username">Username</label>
                    <input type="text" id="admin_username" name="admin_username" class="form-control" required>
                </div>
                <div class="form-row">
                    <div class="form-group">
                        <label for="admin_password">Password</label>
                        <input type="password" id="admin_password" name="admin_password" class="form-control" required minlength="8">
                    </div>
                    <div class="form-group">
                        <label for="admin_password_confirm">Konfirmasi Password</label>
                        <input type="password" id="admin_password_confirm" name="admin_password_confirm" class="form-control" required minlength="8">
                    </div>
                </div>
                <p class="hint">Minimal 8 karakter.</p>
            </fieldset>

            <button type="submit" class="btn btn-accent btn-block">🚀 Install Website</button>
        </form>
    </div>
</div>

<script>
document.getElementById('test-connection-btn').addEventListener('click', async function () {
    const btn = this;
    const resultBox = document.getElementById('test-result');
    const payload = new FormData();
    payload.append('db_host', document.getElementById('db_host').value);
    payload.append('db_port', document.getElementById('db_port').value);
    payload.append('db_name', document.getElementById('db_name').value);
    payload.append('db_user', document.getElementById('db_user').value);
    payload.append('db_pass', document.getElementById('db_pass').value);

    btn.disabled = true;
    btn.textContent = 'Menguji koneksi...';
    resultBox.className = '';
    resultBox.style.display = 'none';

    try {
        const res = await fetch('process.php?action=test_connection', {
            method: 'POST',
            body: payload
        });
        const data = await res.json();
        resultBox.textContent = data.message;
        resultBox.className = data.success ? 'ok' : 'fail';
    } catch (err) {
        resultBox.textContent = 'Gagal menghubungi server installer. Silakan coba lagi.';
        resultBox.className = 'fail';
    } finally {
        btn.disabled = false;
        btn.textContent = '🔌 Test Database Connection';
    }
});

document.getElementById('install-form').addEventListener('submit', function (e) {
    const pass = document.getElementById('admin_password').value;
    const confirm = document.getElementById('admin_password_confirm').value;
    if (pass !== confirm) {
        e.preventDefault();
        alert('Password dan konfirmasi password tidak sama.');
    }
});
</script>
</body>
</html>
