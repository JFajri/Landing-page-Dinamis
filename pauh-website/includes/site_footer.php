    </main>

    <!-- FOOTER -->
    <footer id="kontak" aria-label="Informasi kontak dan footer">
        <div class="container">
            <div class="footer-grid">
                <div class="footer-col">
                    <h2><?= e(setting('footer.office_title')) ?></h2>
                    <p><?= nl2br(e(setting('footer.address'))) ?></p>
                </div>
                <?php $officeHours = contentItems('hours'); ?>
                <?php if ($officeHours): ?>
                <div class="footer-col">
                    <h2><?= e(setting('footer.hours_title')) ?></h2>
                    <ul>
                        <?php foreach ($officeHours as $row): ?>
                            <li><strong><?= e($row['title']) ?>:</strong> <?= e($row['value']) ?></li>
                        <?php endforeach; ?>
                    </ul>
                </div>
                <?php endif; ?>
                <div class="footer-col">
                    <h2><?= e(setting('footer.contact_title')) ?></h2>
                    <p>
                        <?= e(setting('footer.location')) ?>
                        <?php if (setting('footer.email') !== ''): ?>
                            <br><a href="mailto:<?= e(setting('footer.email')) ?>">Email: <?= e(setting('footer.email')) ?></a>
                        <?php endif; ?>
                        <?php if (setting('footer.phone') !== ''): ?>
                            <br><a href="tel:<?= e(preg_replace('/[^0-9+]/', '', setting('footer.phone'))) ?>">Telp/WA: <?= e(setting('footer.phone')) ?></a>
                        <?php endif; ?>
                    </p>
                </div>
            </div>
            <div class="footer-bottom">
                <p>&copy; <?= date('Y') ?> <?= e(setting('footer.copyright')) ?>
                    <span class="footer-sep" aria-hidden="true">·</span>
                    <a href="<?= e(BASE_URL) ?>/privacy/">Kebijakan Privasi</a></p>
            </div>
        </div>
    </footer>

    <!-- JAVASCRIPT -->
    <script>window.BASE_URL = <?= json_encode(BASE_URL) ?>;window.SITE_TEXT = <?= json_encode([
        'calendarEmpty' => setting('calendar.empty_text'),
    ], JSON_UNESCAPED_UNICODE | JSON_HEX_TAG | JSON_HEX_AMP) ?>;</script>
    <script src="<?= e(assetUrl('js/main.js')) ?>"></script>
</body>
</html>
