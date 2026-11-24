<?php
/**
 * includes/item_kelurahan.php
 * Satu kelurahan (chip) pada daftar, dipakai bersama oleh beranda dan /kelurahan/.
 * Variabel: $kel (baris content_items).
 */
?>
<li class="kelurahan-item is-link reveal"><a href="<?= e(contentDetailUrl('kelurahan', (int) $kel['id'])) ?>" class="stretched-link"><?= e($kel['title']) ?></a></li>
