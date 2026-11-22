<?php
/**
 * config/site.example.php
 *
 * CONTOH pengaturan khusus server. Salin menjadi config/site.php (file itu
 * diabaikan git) lalu buka komentar baris yang dibutuhkan.
 */

// Alamat situs yang tetap. Sangat disarankan di production: dipakai untuk canonical,
// sitemap, dan tautan Open Graph tanpa bergantung pada header Host dari pengunjung.
// Tanpa garis miring di akhir; sertakan subfolder bila situs tidak di root domain.
// define('BASE_URL', 'https://kecamatan-pauh.padang.go.id');

// Jika situs berada di belakang Cloudflare atau proxy lain, REMOTE_ADDR berisi alamat
// proxy, bukan alamat pengunjung — pembatasan kirim formulir dan percobaan login lalu
// menghitung semua orang sebagai satu pengunjung. Isi dengan nama header yang berisi
// alamat asli pengunjung HANYA bila server Anda memang menerima trafik lewat proxy itu.
// define('TRUSTED_IP_HEADER', 'HTTP_CF_CONNECTING_IP');
