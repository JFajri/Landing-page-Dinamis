<?php
/**
 * includes/card_potensi.php
 * Kartu Potensi Wilayah tunggal, dipakai bersama oleh beranda dan /potensi/.
 * Seluruh kartu bisa diklik (stretched link): ke halaman detail, atau ke tautan khusus
 * bila admin mengisinya.
 *
 * Variabel: $card (baris content_items), $hTag (opsional, tingkat judul, bawaan h3).
 */
$hTag = $hTag ?? 'h3';
?>
<article class="card reveal is-link">
    <?php if (!empty($card['image'])): ?>
        <img loading="lazy" decoding="async" width="600" height="400" src="<?= e(mediaUrl($card['image'])) ?>" alt="" class="card-img" referrerpolicy="no-referrer" data-fallback="<?= e(assetUrl('img/placeholder.svg')) ?>">
    <?php endif; ?>
    <div class="card-body">
        <<?= $hTag ?>><a href="<?= e(contentCardLink('potensi', $card)) ?>" class="stretched-link"><?= e($card['title']) ?></a></<?= $hTag ?>>
        <p><?= e($card['description']) ?></p>
    </div>
</article>
