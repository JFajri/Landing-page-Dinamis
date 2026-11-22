<?php
/**
 * privacy/index.php — Kebijakan Privasi.
 *
 * CATATAN UNTUK PENGELOLA: ini adalah teks dasar yang disusun sesuai cara kerja situs ini
 * (formulir aspirasi, cookie sesi, pembatasan spam). Sebelum dipakai resmi, mohon tinjau
 * bersama PPID / bagian hukum instansi dan sesuaikan bila ada ketentuan lain (mis. jangka
 * waktu penyimpanan laporan atau pihak yang menerima teruskan laporan).
 */
require_once __DIR__ . '/../includes/bootstrap.php';

$siteName = setting('site.name');
$email    = setting('footer.email');
$phone    = setting('footer.phone');
$address  = setting('footer.address');

$pageTitle       = 'Kebijakan Privasi - ' . $siteName;
$pageDescription = 'Kebijakan privasi ' . setting('site.brand_title') . ': data apa yang dikumpulkan lewat formulir aspirasi, untuk apa digunakan, dan hak Anda atas data tersebut.';
$navActive       = '';
require __DIR__ . '/../includes/site_header.php';
?>
        <section class="section-padding page-top" id="privasi" aria-labelledby="privasi-title">
            <div class="container container-narrow">
                <h1 class="section-title text-center article-title" id="privasi-title">Kebijakan Privasi</h1>
                <p class="text-center prose-meta">Terakhir diperbarui: 22 November 2026</p>

                <div class="prose">
                    <p>Kebijakan ini menjelaskan bagaimana <?= e($siteName) ?> mengelola data pribadi yang Anda sampaikan melalui situs ini. Dengan menggunakan formulir di situs ini, Anda menyetujui pengelolaan data sebagaimana dijelaskan di bawah.</p>

                    <h2>1. Data yang kami kumpulkan</h2>
                    <ul>
                        <li><strong>Formulir Aspirasi &amp; Pengaduan:</strong> nama lengkap, alamat email atau nomor WhatsApp, kelurahan domisili, dan isi pesan yang Anda tulis.</li>
                        <li><strong>Data teknis untuk keamanan:</strong> alamat IP Anda <em>tidak</em> disimpan apa adanya. Yang disimpan hanya sidik satu arah (hash) dari alamat tersebut, semata-mata untuk membatasi pengiriman spam dan percobaan login berulang, dan dibersihkan secara berkala.</li>
                        <li><strong>Cookie:</strong> situs memakai cookie sesi yang diperlukan agar formulir dan login petugas berfungsi dengan aman. Cookie ini tidak dipakai untuk iklan maupun pelacakan lintas situs.</li>
                    </ul>

                    <h2>2. Untuk apa data digunakan</h2>
                    <ul>
                        <li>Menindaklanjuti laporan, aspirasi, atau pengaduan yang Anda sampaikan.</li>
                        <li>Menghubungi Anda bila diperlukan klarifikasi atau untuk menyampaikan tanggapan.</li>
                        <li>Mengevaluasi dan meningkatkan kualitas layanan publik.</li>
                    </ul>

                    <h2>3. Siapa yang dapat mengakses data Anda</h2>
                    <p>Data laporan hanya dapat dilihat oleh petugas berwenang di kantor kecamatan melalui panel pengelola. Data Anda tidak diperjualbelikan. Laporan dapat diteruskan kepada perangkat daerah atau instansi terkait hanya sebatas yang diperlukan untuk menindaklanjutinya, atau bila diwajibkan oleh peraturan perundang-undangan.</p>

                    <h2>4. Penyimpanan dan keamanan</h2>
                    <p>Data disimpan selama diperlukan untuk penanganan laporan dan sesuai ketentuan kearsipan yang berlaku. Kami menerapkan pengamanan teknis yang wajar, antara lain kata sandi petugas yang disimpan dalam bentuk hash, pembatasan percobaan login, dan perlindungan formulir dari penyalahgunaan. Meski demikian, tidak ada sistem yang sepenuhnya bebas risiko; mohon tidak menuliskan data yang sangat sensitif (misalnya nomor KTP atau kata sandi) di dalam pesan.</p>

                    <h2>5. Hak Anda</h2>
                    <p>Sesuai peraturan yang berlaku, termasuk Undang-Undang Nomor 27 Tahun 2022 tentang Pelindungan Data Pribadi, Anda dapat meminta akses, perbaikan, atau penghapusan data pribadi yang Anda sampaikan kepada kami, serta menarik persetujuan Anda. Permintaan dapat diajukan lewat kontak di bawah ini.</p>

                    <h2>6. Tautan ke situs lain</h2>
                    <p>Situs ini dapat memuat tautan ke situs pihak lain. Kebijakan privasi situs tersebut berada di luar kendali kami; mohon membaca kebijakan masing-masing.</p>

                    <h2>7. Perubahan kebijakan</h2>
                    <p>Kebijakan ini dapat diperbarui dari waktu ke waktu. Tanggal pembaruan terakhir tercantum di bagian atas halaman ini.</p>

                    <h2>8. Hubungi kami</h2>
                    <p>
                        <?= nl2br(e($address)) ?>
                        <?php if ($email !== ''): ?><br>Email: <a href="mailto:<?= e($email) ?>"><?= e($email) ?></a><?php endif; ?>
                        <?php if ($phone !== ''): ?><br>Telp/WA: <a href="tel:<?= e(preg_replace('/[^0-9+]/', '', $phone)) ?>"><?= e($phone) ?></a><?php endif; ?>
                    </p>
                </div>

                <div class="section-action section-action-lg">
                    <a href="<?= e(BASE_URL) ?>/" class="btn btn-solid">← Kembali ke Beranda</a>
                </div>
            </div>
        </section>

<?php require __DIR__ . '/../includes/site_footer.php'; ?>
