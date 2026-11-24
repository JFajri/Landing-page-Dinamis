<?php
/**
 * includes/aspirasi_form.php
 * Form Aspirasi & Pengaduan (CSRF + honeypot + jeda minimum + persetujuan privasi), dipakai
 * bersama oleh beranda (#aspirasi) dan halaman /aspirasi/. Dikirim lewat fetch() ke
 * aspirasi/submit.php oleh assets/js/main.js, tanpa memuat ulang halaman.
 *
 * Variabel: $kelurahanItems (daftar kelurahan aktif), $preselectKelurahan (opsional, nama
 * kelurahan yang dipilih otomatis, mis. dari halaman detail kelurahan).
 */
$preselectKelurahan = $preselectKelurahan ?? '';
?>
<div class="form-container">
    <?php $_SESSION['aspirasi_form_at'] = time(); // dipakai submit.php: jeda minimum anti-bot ?>
    <div class="alert" id="successAlert" role="alert">
        ✅ <?= e(setting('aspirasi.success_message')) ?>
    </div>
    <div class="alert alert-error" id="errorAlert" role="alert"></div>
    <form id="complaintForm" action="<?= e(BASE_URL) ?>/aspirasi/submit.php" method="post">
        <?= csrfField() ?>
        <?php /* Honeypot: disembunyikan dari manusia, tapi biasanya diisi bot. */ ?>
        <div class="hp-field" aria-hidden="true">
            <label for="website">Jangan diisi jika Anda manusia</label>
            <input type="text" id="website" name="website" tabindex="-1" autocomplete="off">
        </div>
        <div class="form-group">
            <label for="nama">Nama Lengkap <span aria-hidden="true">*</span></label>
            <input type="text" id="nama" name="name" autocomplete="name" minlength="2" maxlength="100" placeholder="Masukkan nama Anda" required aria-required="true">
        </div>
        <div class="form-group">
            <label for="email">Email atau Nomor WhatsApp <span aria-hidden="true">*</span></label>
            <input type="text" id="email" name="contact" autocomplete="email" maxlength="150" placeholder="nama@email.com atau 0812xxxxxxxx" required aria-required="true" aria-describedby="contactHint">
            <small class="form-hint" id="contactHint">Dipakai petugas untuk menghubungi Anda terkait laporan ini.</small>
        </div>
        <div class="form-group">
            <label for="kelurahanSelect">Pilih Kelurahan Domisili <span aria-hidden="true">*</span></label>
            <select id="kelurahanSelect" name="kelurahan" required aria-required="true">
                <option value="">-- Pilih Kelurahan --</option>
                <?php foreach ($kelurahanItems as $kel): ?>
                    <option value="<?= e($kel['title']) ?>"<?= $preselectKelurahan !== '' && $preselectKelurahan === $kel['title'] ? ' selected' : '' ?>><?= e($kel['title']) ?></option>
                <?php endforeach; ?>
            </select>
        </div>
        <div class="form-group">
            <label for="pesan">Pesan Pengaduan / Aspirasi <span aria-hidden="true">*</span></label>
            <textarea id="pesan" name="message" rows="4" minlength="10" maxlength="2000" placeholder="Tuliskan keluhan atau pengaduan Anda secara rinci..." required aria-required="true"></textarea>
        </div>
        <div class="form-check">
            <input type="checkbox" id="consent" name="consent" value="1" required aria-required="true">
            <label for="consent">Saya menyetujui data saya diproses sesuai <a href="<?= e(BASE_URL) ?>/privacy/" target="_blank" rel="noopener">Kebijakan Privasi</a>. <span aria-hidden="true">*</span></label>
        </div>
        <button type="submit" class="submit-btn"><?= e(setting('aspirasi.button_label')) ?></button>
    </form>
</div>
