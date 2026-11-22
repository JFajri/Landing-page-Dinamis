# Portal Kecamatan Pauh — Website Dinamis (PHP Native + MySQL)

Website ini adalah hasil konversi landing page statis `landing_page.html` menjadi
website dinamis dengan sistem admin & portal berita, menggunakan **HTML5, CSS3,
Vanilla JavaScript, PHP native, dan MySQL** — tanpa framework tambahan.

Desain asli (warna, layout, typography, animasi) **tidak diubah**. Hanya section
Berita yang sekarang dinamis (dari database), sesuai acceptance criteria.

## Requirement Hosting

- PHP 8.0+ dengan ekstensi: **PDO MySQL**, **mbstring**, **fileinfo** (semua ini
  sudah aktif secara default di hampir semua shared hosting cPanel).
- MySQL / MariaDB 5.7+.
- Apache dengan `mod_rewrite` (opsional — untuk pretty URL berita; jika tidak
  tersedia, website tetap berjalan normal memakai `?slug=...`).

## Cara Install (sekali saja)

1. Upload seluruh isi folder ini ke hosting Anda (public_html atau subfolder).
2. Buka `https://domain-anda.com/install/` di browser.
3. Isi konfigurasi database (host, port, nama database, username, password).
   Gunakan tombol **Test Database Connection** untuk memverifikasi sebelum lanjut.
   - Jika database belum ada dan akun DB Anda punya izin `CREATE DATABASE`,
     installer akan membuatkannya otomatis.
   - Jika hosting Anda **tidak** mengizinkan `CREATE DATABASE` (umum di shared
     hosting), buat database kosong terlebih dahulu lewat cPanel/phpMyAdmin,
     lalu masukkan nama database tersebut di form installer.
4. Isi data akun admin pertama (nama, username, password).
5. Klik **Install Website**. Installer akan otomatis:
   `Connect DB → Create DB (jika perlu) → Create Tables → Create Admin → Create Config → Create Lock`
6. Setelah sukses, klik **Go to Admin Login** dan login dengan akun yang baru dibuat.
7. **Disarankan:** hapus atau rename folder `/install/` setelah instalasi selesai
   (opsional — `install.lock` sudah mencegah instalasi ulang meski folder ini
   tidak dihapus).

## Struktur Folder

```
/
├── index.php              # Landing page (Berita, Galeri, Kalender dinamis dari DB)
├── news/                  # Halaman publik daftar & detail berita
├── aspirasi/submit.php    # Endpoint publik penerima form Aspirasi/Pengaduan
├── admin/                 # Panel admin
│   ├── news/              # CRUD Berita
│   ├── categories/        # CRUD Kategori Berita
│   ├── gallery/           # CRUD Galeri Foto
│   ├── calendar/          # CRUD Kalender & Event
│   └── complaints/        # Monitoring Aspirasi & Pengaduan (lihat, ubah status, hapus)
├── config/                # config.php (umum) + database.php (dibuat installer)
├── includes/              # bootstrap, auth, functions, header/footer (public & admin)
├── assets/                # css/js hasil ekstraksi dari desain asli + admin.css/js
├── uploads/
│   ├── news/              # Thumbnail berita
│   └── gallery/           # Foto galeri (memakai mekanisme upload yang sama dengan berita)
├── install/               # Installer satu-kali
└── database/
    ├── schema.sql                              # Skema lengkap (dipakai installer untuk install baru)
    └── migration_v2_galeri_kalender_aspirasi.sql  # Migrasi tambahan untuk website yang SUDAH diinstall sebelumnya
```

## Update: Galeri, Kalender & Aspirasi Kini Dinamis

Tiga bagian yang sebelumnya statis kini sepenuhnya dikelola dari Admin, tanpa
perlu menyentuh kode HTML:

- **Galeri** (Admin → Galeri): upload/edit/hapus foto, atur status
  tampil/sembunyi. Foto otomatis muncul di section Galeri landing page.
  Memakai kembali mekanisme upload gambar yang sama dengan Berita (validasi
  ekstensi, MIME type, ukuran, dan penamaan file acak).
- **Kalender & Event** (Admin → Kalender & Event): tambah/edit/hapus kegiatan
  dengan judul, tanggal, waktu (opsional), lokasi (opsional), deskripsi
  (opsional), dan status tampil/sembunyi. Widget kalender di landing page
  (vanilla JS yang sudah ada) **tidak diubah logikanya** — hanya sumber
  datanya yang beralih dari object hardcode menjadi data dari database.
- **Aspirasi & Pengaduan** (Admin → Aspirasi & Pengaduan): form yang sudah ada
  di landing page sekarang benar-benar mengirim & menyimpan data ke database
  lewat `aspirasi/submit.php` (AJAX/fetch, tanpa reload halaman — tampilan
  tidak berubah). Admin dapat melihat daftar laporan, membuka detail (otomatis
  ditandai "Dibaca" saat dibuka), mengubah status (Baru/Dibaca/Diproses/Selesai),
  dan menghapus laporan.

**Jika website Anda sudah pernah diinstall sebelum update ini**, tabel baru
(`gallery`, `events`, `complaints`) akan **otomatis dibuat sendiri** saat
Anda membuka halaman apa pun di website — tidak perlu menjalankan SQL manual.
Ini ditangani oleh `ensureDatabaseSchema()` di `includes/functions.php`, yang
dipanggil sekali di `includes/bootstrap.php` pada setiap request: ia
membandingkan `database/schema.sql` dengan penanda versi terakhir
(`config/.schema_version`), dan kalau berbeda, otomatis menjalankan
`CREATE TABLE IF NOT EXISTS` untuk tabel yang belum ada — **tidak pernah**
menyentuh/menghapus tabel atau data yang sudah ada. Setelah tersinkron,
pengecekan ini jadi sangat ringan (hanya baca file & bandingkan hash) sehingga
tidak membebani request berikutnya. File `database/migration_v2_galeri_kalender_aspirasi.sql`
tetap disediakan sebagai cara manual/alternatif lewat phpMyAdmin bila Anda lebih suka begitu.

## Yang Dinamis vs. Yang Tetap Statis

Mengikuti acceptance criteria di brief awal (hanya modul Berita yang wajib
dinamis) — namun kini diperluas mencakup Galeri, Kalender, dan Aspirasi:

- **Dinamis (dari database):** Berita (beranda, `/news`, detail), Galeri,
  Kalender & Event, dan penyimpanan laporan Aspirasi/Pengaduan.
- **Tetap statis (sesuai desain asli, tidak diubah):** section Potensi
  Wilayah, Kelurahan, dan Layanan Publik. Bagian-bagian ini tidak termasuk
  dalam permintaan yang diminta, sehingga sengaja dibiarkan statis. Jika
  suatu saat ingin dibuat CRUD-able juga, struktur proyek ini sudah dirancang
  agar mudah ditambah modul baru dengan pola yang sama.

## Keamanan yang Sudah Diterapkan

- Password admin di-hash dengan `password_hash()` / diverifikasi `password_verify()`.
- Semua query database memakai PDO **prepared statements**.
- Proteksi **CSRF token** di semua form admin (login, create/edit/delete).
- Validasi **server-side** penuh + validasi client-side dasar (Vanilla JS).
- Upload thumbnail: validasi ekstensi, MIME type asli file, `getimagesize()`,
  ukuran maksimal, dan penamaan file acak (bukan nama asli).
- Folder `/uploads/` diblokir dari eksekusi PHP/script via `.htaccess`.
- Folder `/config/`, `/includes/`, `/database/` diblokir dari akses langsung browser.
- Kredensial database **tidak** ada di source code — hanya di `config/database.php`
  yang dibuat installer dan tidak untuk di-commit ke version control publik.
- Installer terkunci otomatis (`install.lock`) setelah sukses sekali.
- Session PHP dengan `HttpOnly` + `SameSite=Lax`, regenerasi session ID saat login.

## Pengujian yang Sudah Dilakukan

Sebelum diserahkan, seluruh alur berikut sudah diuji langsung terhadap server
PHP + MariaDB sungguhan (bukan hanya dibaca sekilas):

- ✅ Instalasi penuh (test connection, create tables, create admin, create config, create lock)
- ✅ Installer terkunci setelah sukses (`install.lock` mencegah instalasi ulang)
- ✅ Login/logout admin + proteksi CSRF + proteksi halaman tanpa login
- ✅ CRUD Kategori (create, delete, dengan FK `ON DELETE SET NULL` ke berita)
- ✅ CRUD Berita (create dengan upload thumbnail, edit, slug otomatis unik)
- ✅ Berita `published` tampil di beranda & `/news`, berita `draft` **tidak** tampil
  dan akses langsung ke slug draft mengembalikan 404
- ✅ Permintaan tanpa CSRF token valid ditolak
- ✅ CRUD Galeri (upload foto reuse mekanisme Berita, edit, hapus) — foto `active`
  otomatis tampil di landing page, foto `inactive` tersembunyi
- ✅ CRUD Kalender & Event (dengan & tanpa field opsional waktu/lokasi) — event
  `active` otomatis muncul di widget kalender landing page dengan data yang
  di-inject lewat `window.CALENDAR_EVENTS`, event `inactive` tersembunyi
- ✅ Form Aspirasi/Pengaduan benar-benar tersimpan ke database lewat endpoint
  `aspirasi/submit.php`, termasuk validasi server-side untuk field kosong
- ✅ Admin → Aspirasi & Pengaduan: daftar dengan filter status, detail laporan
  otomatis berubah status "Baru" → "Dibaca" saat dibuka, ubah status manual,
  badge notifikasi jumlah laporan baru di sidebar

Seluruh 37 file PHP juga lolos `php -l` (no syntax errors), dan kedua file
JavaScript (`main.js`, `admin.js`) lolos pengecekan sintaks.

## Finalisasi: Responsif & Polish

- **Mobile & desktop**: seluruh halaman (landing page, `/news`, panel Admin,
  installer) sudah diuji breakpoint-nya di lebar layar besar (desktop),
  tablet (≤992px/≤900px), ponsel (≤768px), dan ponsel kecil (≤480px/≤380px).
- **Panel Admin**: sidebar jadi off-canvas dengan backdrop gelap yang bisa
  di-tap untuk menutup di layar kecil, otomatis tertutup saat memilih menu,
  tabel bisa di-scroll horizontal tanpa merusak layout, kartu statistik
  menyesuaikan jumlah kolom sesuai lebar layar.
- **Installer**: form konfigurasi database & akun admin kini juga responsif
  (sebelumnya belum ada breakpoint sama sekali).
- **Favicon & theme-color** ditambahkan di semua halaman (landing page,
  admin, installer).
- **Lazy loading** (`loading="lazy"`) ditambahkan pada gambar-gambar konten
  (potensi wilayah, berita, galeri) di halaman publik untuk mempercepat
  waktu muat halaman.
- Diperiksa juga tidak ada elemen dengan lebar tetap (fixed width) yang bisa
  menyebabkan horizontal scroll tak diinginkan di layar sempit.

## Mengelola Tampilan Website (tanpa mengubah kode)

Seluruh teks, warna, menu, dan daftar pada halaman publik dikelola dari **Admin**, sehingga
situs ini bisa diduplikasi untuk instansi/keperluan lain cukup dengan mengganti isinya.

| Menu Admin | Yang dikelola |
|---|---|
| Tampilan Website → Identitas & SEO | Nama situs, brand navbar, **logo resmi (sekaligus favicon)**, judul/deskripsi/kata kunci, gambar pratinjau sosial |
| Tampilan Website → Warna | Warna utama, sekunder, dan aksen (diterapkan ke seluruh halaman publik) |
| Tampilan Website → Hero | Label, judul, deskripsi, dua tombol (teks + tautan), gambar latar |
| Tampilan Website → Judul Section | Aktif/nonaktif + judul/deskripsi tiap section (`{count}` = jumlah otomatis) |
| Tampilan Website → Berita, Kalender & Aspirasi | Jumlah berita, teks tombol, pesan kosong, pesan sukses aspirasi |
| Tampilan Website → Footer & Kontak | Alamat, email, telepon, hak cipta |
| Konten Halaman | Menu navigasi, Statistik, Potensi Wilayah, Kelurahan (juga jadi dropdown aspirasi), Layanan, Jam Operasional — bisa tambah/ubah/hapus/urutkan/sembunyikan |

**Aturan tautan** (menu, tombol, kartu): `/` = beranda (URL bersih tanpa `#`), `#layanan` = ke section,
`/news` = halaman internal, `https://…` / `mailto:` / `tel:` = tautan luar. Skema lain (mis. `javascript:`) ditolak.

**Instalasi baru atau upgrade:** tabel `settings` dan `content_items` dibuat otomatis oleh
`ensureDatabaseSchema()`, dan konten awal diisi satu kali oleh `seedDefaultContent()` (tidak pernah
menimpa data yang sudah diubah admin).

**Menambah pengaturan/daftar baru (untuk developer):** tambahkan nilai bawaan di `siteDefaults()` lalu satu
field di `settingsSchema()` — atau satu entri di `contentSections()` untuk jenis daftar baru. Form admin,
validasi, dan menu sidebar dibuat otomatis dari definisi tersebut (`includes/settings.php`).

## Catatan Aksesibilitas & Kinerja Tampilan Publik

- Hero memakai tinggi `100svh` dikurangi ruang intip statistik, dibatasi 520–780px, sehingga kartu statistik
  langsung terlihat di layar pertama (laptop 1366×650 hingga monitor Full HD dan ponsel).
- Kartu berita, potensi, dan layanan memakai pola *stretched link* (satu tautan, seluruh area kartu bisa diklik).
- Skip link, `aria-current`, fokus keyboard terlihat, target sentuh ≥ 44px, `prefers-reduced-motion`,
  meta SEO (canonical, Open Graph, Twitter Card, JSON-LD), dan lightbox galeri berbasis `<dialog>`.

## Mengganti Logo / Favicon

Logo dipakai sebagai gambar (bukan emoji) di navbar, panel admin, dan favicon browser. Dua cara mengganti:
1. **Lewat Admin** (disarankan): Tampilan Website → Identitas & SEO → *Logo resmi*. Unggah PNG/WEBP persegi
   (min. 256×256 px, latar transparan) — navbar, admin, dan favicon berganti sekaligus.
2. **Lewat file:** timpa `assets/favicon.svg` (dipakai jika logo di Admin kosong, dan oleh halaman instalasi).

## Persiapan Sebelum Tayang Publik (Production)

Perubahan pada tahap ini (SEO, keamanan, aksesibilitas) dan hal yang perlu Anda lakukan di server:

1. **Tetapkan alamat situs.** Salin `config/site.example.php` menjadi `config/site.php`, lalu isi
   `BASE_URL` dengan alamat resmi (mis. `https://domain-anda.go.id`). Alamat ini dipakai untuk canonical,
   `sitemap.xml`, dan tautan saat dibagikan, sehingga tidak bergantung pada header `Host` pengunjung.
2. **Aktifkan HTTPS.** Setelah sertifikat SSL aktif, buka komentar 3 baris `RewriteCond/RewriteRule`
   di `.htaccess` (bagian "paksa HTTPS").
3. **Di belakang Cloudflare / proxy?** Isi `TRUSTED_IP_HEADER` di `config/site.php`. Tanpa itu, pembatasan
   kirim formulir dan percobaan login menganggap semua pengunjung berasal dari satu alamat.
4. **Tinjau teks Kebijakan Privasi** (`/privacy/`, file `privacy/index.php`) bersama PPID / bagian hukum instansi.
   Teks ini disusun mengikuti cara kerja situs, bukan nasihat hukum.
5. **Ganti gambar bawaan.** Gambar hero dan "Potensi Wilayah" awalnya mengambil dari situs pihak lain.
   Unggah foto milik sendiri lewat Admin > Pengaturan Situs / Konten Halaman (lebih cepat, tidak
   bergantung situs lain, dan bebas masalah hak cipta).
6. **Kirim sitemap** `https://domain-anda/sitemap.xml` ke Google Search Console.
7. **Jangan commit kredensial.** `config/database.php`, `config/site.php`, `config/.app_secret`, dan
   `install.lock` ada di `.gitignore`. Bila repo ini sebelumnya memuatnya, lepaskan dari git
   (`git rm --cached`) — **cadangkan dulu berkasnya di server**, karena `git pull` pada commit yang
   menghapus berkas terlacak ikut menghapusnya di server.

Yang ditambahkan: `.htaccess` (blokir `config/`, `includes/`, `database/`, header keamanan, kompresi, cache),
`robots.txt` & `sitemap.xml` dinamis, data terstruktur (Organization/GovernmentOrganization, NewsArticle),
formulir aspirasi dengan CSRF + honeypot + jeda minimum + batas 5 laporan/jam/IP + validasi kontak + persetujuan
privasi, pembatasan percobaan login admin (5 gagal / 15 menit), batas waktu sesi admin, kontras warna sesuai
WCAG AA (warna teks di atas warna aksen dipilih otomatis), ukuran teks minimum 11–12px, dan gambar cadangan
lokal bila gambar eksternal gagal dimuat.
