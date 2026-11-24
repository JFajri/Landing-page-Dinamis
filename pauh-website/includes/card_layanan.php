<?php
/**
 * includes/card_layanan.php
 * Kartu Layanan tunggal, dipakai bersama oleh beranda dan /layanan/.
 *
 * Susunan kartu: ikon → judul → deskripsi (mengisi sisa tinggi) → tautan aksi. Kartu berupa
 * flex-column sehingga tautan aksi SELALU menempel di dasar kartu, sejajar antar-kartu dalam
 * satu baris meskipun panjang deskripsinya berbeda (lihat .service-box di assets/css/style.css).
 *
 * Variabel: $svc (baris content_items), $hTag (opsional, tingkat judul, bawaan h3).
 */
$hTag      = $hTag ?? 'h3';
$svcLabel  = trim((string) ($svc['value'] ?? '')) !== '' ? $svc['value'] : 'Lihat Detail →';
?>
<div class="service-box reveal is-link">
    <?php if (!empty($svc['icon'])): ?>
        <div class="service-icon" aria-hidden="true"><?= e($svc['icon']) ?></div>
    <?php endif; ?>
    <<?= $hTag ?>><?= e($svc['title']) ?></<?= $hTag ?>>
    <p><?= e($svc['description']) ?></p>
    <a href="<?= e(contentCardLink('layanan', $svc)) ?>" class="service-link stretched-link"><?= e($svcLabel) ?></a>
</div>
