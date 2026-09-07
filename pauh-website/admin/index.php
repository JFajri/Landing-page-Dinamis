<?php
require_once __DIR__ . '/../includes/auth.php';
requireLogin();

$pdo = getConnection();

$totalNews      = (int) $pdo->query("SELECT COUNT(*) FROM news")->fetchColumn();
$publishedNews  = (int) $pdo->query("SELECT COUNT(*) FROM news WHERE status = 'published'")->fetchColumn();
$draftNews      = (int) $pdo->query("SELECT COUNT(*) FROM news WHERE status = 'draft'")->fetchColumn();
$totalCategories = (int) $pdo->query("SELECT COUNT(*) FROM categories")->fetchColumn();
$totalGallery     = (int) $pdo->query("SELECT COUNT(*) FROM gallery")->fetchColumn();
$totalEvents      = (int) $pdo->query("SELECT COUNT(*) FROM events")->fetchColumn();
$newComplaints    = (int) $pdo->query("SELECT COUNT(*) FROM complaints WHERE status = 'baru'")->fetchColumn();

$recentStmt = $pdo->query("
    SELECT n.id, n.title, n.status, n.created_at, c.name AS category_name
    FROM news n
    LEFT JOIN categories c ON c.id = n.category_id
    ORDER BY n.created_at DESC
    LIMIT 5
");
$recentNews = $recentStmt->fetchAll();

$recentComplaints = $pdo->query("
    SELECT id, name, kelurahan, status, created_at
    FROM complaints
    ORDER BY created_at DESC
    LIMIT 5
")->fetchAll();
$complaintStatusLabel = ['baru' => 'Baru', 'dibaca' => 'Dibaca', 'diproses' => 'Diproses', 'selesai' => 'Selesai'];

$pageTitle  = 'Dashboard';
$activeMenu = 'dashboard';
require __DIR__ . '/../includes/header.php';
?>

<div class="stat-cards">
    <div class="stat-card">
        <div class="num"><?= $totalNews ?></div>
        <div class="label">Total Berita</div>
    </div>
    <div class="stat-card">
        <div class="num"><?= $publishedNews ?></div>
        <div class="label">Berita Published</div>
    </div>
    <div class="stat-card">
        <div class="num"><?= $draftNews ?></div>
        <div class="label">Berita Draft</div>
    </div>
    <div class="stat-card">
        <div class="num"><?= $totalCategories ?></div>
        <div class="label">Kategori</div>
    </div>
    <div class="stat-card">
        <div class="num"><?= $totalGallery ?></div>
        <div class="label">Foto Galeri</div>
    </div>
    <div class="stat-card">
        <div class="num"><?= $totalEvents ?></div>
        <div class="label">Event Kalender</div>
    </div>
    <div class="stat-card">
        <div class="num"><?= $newComplaints ?></div>
        <div class="label">Aspirasi Baru</div>
    </div>
</div>

<div class="card">
    <h3>Berita Terbaru</h3>
    <?php if (empty($recentNews)): ?>
        <div class="empty-state">
            <div class="icon">📰</div>
            <p>Belum ada berita. <a href="<?= e(BASE_URL) ?>/admin/news/create.php" style="color:var(--primary-2);font-weight:700">Tambah berita pertama →</a></p>
        </div>
    <?php else: ?>
        <table>
            <thead>
                <tr>
                    <th>Judul</th>
                    <th>Kategori</th>
                    <th>Status</th>
                    <th>Dibuat</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($recentNews as $n): ?>
                    <tr>
                        <td><?= e($n['title']) ?></td>
                        <td><?= e($n['category_name'] ?? '-') ?></td>
                        <td>
                            <span class="badge badge-<?= $n['status'] ?>"><?= e($n['status']) ?></span>
                        </td>
                        <td><?= e(formatTanggalIndo($n['created_at'])) ?></td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    <?php endif; ?>
</div>

<div class="card">
    <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:16px">
        <h3 style="margin:0">Aspirasi & Pengaduan Terbaru</h3>
        <a href="<?= e(BASE_URL) ?>/admin/complaints/index.php" style="font-size:12.5px;font-weight:700;color:var(--primary-2)">Lihat Semua →</a>
    </div>
    <?php if (empty($recentComplaints)): ?>
        <div class="empty-state">
            <div class="icon">📨</div>
            <p>Belum ada laporan aspirasi/pengaduan dari warga.</p>
        </div>
    <?php else: ?>
        <table>
            <thead>
                <tr>
                    <th>Nama</th>
                    <th>Kelurahan</th>
                    <th>Status</th>
                    <th>Tanggal</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($recentComplaints as $c): ?>
                    <tr onclick="window.location='<?= e(BASE_URL) ?>/admin/complaints/detail.php?id=<?= $c['id'] ?>'" style="cursor:pointer">
                        <td><?= e($c['name']) ?></td>
                        <td><?= e($c['kelurahan'] ?: '-') ?></td>
                        <td><span class="badge badge-status-<?= e($c['status']) ?>"><?= e($complaintStatusLabel[$c['status']] ?? $c['status']) ?></span></td>
                        <td><?= e(formatTanggalIndo($c['created_at'])) ?></td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    <?php endif; ?>
</div>

<div class="card" style="display:flex;gap:14px;flex-wrap:wrap">
    <a href="<?= e(BASE_URL) ?>/admin/news/create.php" class="btn btn-primary">+ Tambah Berita</a>
    <a href="<?= e(BASE_URL) ?>/admin/categories/create.php" class="btn btn-outline">+ Tambah Kategori</a>
    <a href="<?= e(BASE_URL) ?>/admin/gallery/create.php" class="btn btn-outline">+ Tambah Foto Galeri</a>
    <a href="<?= e(BASE_URL) ?>/admin/calendar/create.php" class="btn btn-outline">+ Tambah Kegiatan</a>
</div>

<?php require __DIR__ . '/../includes/footer.php'; ?>
