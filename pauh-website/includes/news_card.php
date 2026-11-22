<?php
/**
 * includes/news_card.php
 * Kartu berita tunggal, dipakai bersama oleh beranda (index.php) dan daftar /news.
 * Satu sumber markup supaya perilaku & tampilannya selalu konsisten.
 *
 * Seluruh area kartu bisa diklik memakai pola "stretched link" (seperti Bootstrap/GOV.UK):
 * hanya ada SATU tautan (judul berita) yang pseudo-elemen ::after-nya diperluas menutupi
 * kartu. Hasilnya: klik di mana pun pada kartu membuka berita, pembaca layar & keyboard
 * tidak menemukan tautan ganda, dan teks tetap bisa diseleksi.
 *
 * Variabel yang dibutuhkan: $item (baris berita), diisi dari foreach pemanggil.
 */
$newsHref  = BASE_URL . '/news/detail.php?slug=' . urlencode($item['slug']);
$newsImage = $item['thumbnail'] ? UPLOAD_URL . $item['thumbnail'] : mediaUrl(setting('news.fallback_image'));
if ($newsImage === '') {
    $newsImage = assetUrl('img/placeholder.svg');
}
?>
<article class="berita-card reveal">
    <img loading="lazy" decoding="async" width="600" height="360" src="<?= e($newsImage) ?>" alt="" class="berita-img" referrerpolicy="no-referrer" data-fallback="<?= e(assetUrl('img/placeholder.svg')) ?>">
    <div class="berita-meta">
        <span><span aria-hidden="true">📅</span> <time datetime="<?= e(substr((string) $item['published_at'], 0, 10)) ?>"><?= e(formatTanggalIndo($item['published_at'])) ?></time></span>
        <?php if ($item['category_name']): ?>
            <span class="berita-badge"><?= e($item['category_name']) ?></span>
        <?php endif; ?>
    </div>
    <div class="berita-body">
        <h3><a href="<?= e($newsHref) ?>" class="stretched-link"><?= e($item['title']) ?></a></h3>
        <p><?= e($item['excerpt']) ?></p>
        <span class="berita-link" aria-hidden="true"><?= e(setting('news.link_label')) ?></span>
    </div>
</article>
