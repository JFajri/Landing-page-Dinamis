    </main>

    <!-- FOOTER -->
    <footer id="kontak" role="contentinfo" aria-label="Informasi kontak dan footer">
        <div class="container">
            <div class="footer-grid">
                <div class="footer-col">
                    <h2>Kantor Camat Pauh</h2>
                    <p>Jl. Raya Pasar Baru - Unand, Pasar Baru, Kec. Pauh, Kota Padang, Sumatra Barat 25161.</p>
                </div>
                <div class="footer-col">
                    <h2>Jam Operasional Kantor</h2>
                    <ul>
                        <li><strong>Senin - Kamis:</strong> 07:30 - 16:00 WIB</li>
                        <li><strong>Jumat:</strong> 07:30 - 16:30 WIB</li>
                        <li><strong>Sabtu - Minggu:</strong> Libur (Tutup)</li>
                    </ul>
                </div>
                <div class="footer-col">
                    <h2>Kontak & Info</h2>
                    <p>Kota Padang, Sumatra Barat<br><a href="mailto:kec.pauh@padang.go.id">Email: kec.pauh@padang.go.id</a></p>
                </div>
            </div>
            <div class="footer-bottom">
                <p>&copy; <?= date('Y') ?> Pemerintah Kecamatan Pauh - Kota Padang. Hak Cipta Dilindungi.</p>
            </div>
        </div>
    </footer>

    <!-- JAVASCRIPT -->
    <script>window.BASE_URL = <?= json_encode(BASE_URL) ?>;</script>
    <script src="<?= e(BASE_URL) ?>/assets/js/main.js"></script>
</body>
</html>
