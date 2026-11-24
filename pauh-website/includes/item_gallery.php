<?php
/**
 * includes/item_gallery.php
 * Satu foto galeri (tombol pembuka lightbox), dipakai bersama oleh beranda dan /galeri/.
 * Variabel: $photo (baris gallery).
 */
?>
<button type="button" class="gallery-item reveal" data-full="<?= e(GALLERY_UPLOAD_URL . $photo['image']) ?>" data-caption="<?= e($photo['caption']) ?>" data-description="<?= e((string) $photo['description']) ?>" aria-label="Perbesar foto: <?= e($photo['caption']) ?>">
    <img loading="lazy" decoding="async" src="<?= e(GALLERY_UPLOAD_URL . $photo['image']) ?>" alt="<?= e($photo['caption']) ?>">
    <span class="gallery-overlay" aria-hidden="true">🔍</span>
</button>
