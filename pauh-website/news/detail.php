<?php
require_once __DIR__ . '/../includes/bootstrap.php';

$pdo  = getConnection();
$slug = clean($_GET['slug'] ?? '');

$news = null;
if ($slug !== '') {
    $stmt = $pdo->prepare("
        SELECT n.*, c.name AS category_name
        FROM news n
        LEFT JOIN categories c ON c.id = n.category_id
        WHERE n.slug = ? AND n.status = 'published'
        LIMIT 1
    ");
    $stmt->execute([$slug]);
    $news = $stmt->fetch();
}

if (!$news) {
    http_response_code(404);
}

$pageTitle       = $news ? $news['title'] . ' - ' . SITE_NAME : 'Berita Tidak Ditemukan - ' . SITE_NAME;
$pageDescription = $news ? $news['excerpt'] : 'Berita yang Anda cari tidak ditemukan atau belum dipublikasikan.';
require __DIR__ . '/../includes/site_header.php';
?>
        <section class="section-padding" id="berita-detail" aria-label="Detail berita" style="padding-top:150px">
            <div class="container" style="max-width:820px">

                <?php if (!$news): ?>
                    <div class="text-center">
                        <h2 class="section-title">Berita Tidak Ditemukan</h2>
                        <p class="section-desc" style="margin:0 auto 30px">Berita yang Anda cari mungkin sudah dihapus, belum dipublikasikan, atau alamat tidak valid.</p>
                        <a href="<?= e(BASE_URL) ?>/news/" class="btn btn-main">← Kembali ke Daftar Berita</a>
                    </div>
                <?php else: ?>
                    <div class="berita-meta" style="padding:0;justify-content:center;margin-bottom:14px">
                        <span aria-label="Tanggal">📅 <?= e(formatTanggalIndo($news['published_at'])) ?></span>
                        <?php if ($news['category_name']): ?>
                            <span class="berita-badge"><?= e($news['category_name']) ?></span>
                        <?php endif; ?>
                    </div>
                    <h1 class="section-title text-center" style="max-width:100%"><?= e($news['title']) ?></h1>

                    <?php if ($news['thumbnail']): ?>
                        <img loading="lazy" src="<?= e(UPLOAD_URL . $news['thumbnail']) ?>" alt="<?= e($news['title']) ?>"
                             style="width:100%;max-height:420px;object-fit:cover;border-radius:16px;margin:24px 0;box-shadow:var(--shadow-sm)">
                    <?php endif; ?>

                    <div class="reveal visible" style="font-size:15.5px;line-height:1.9;color:var(--ink)">
                        <?= nl2br(e($news['content'])) ?>
                    </div>

                    <div style="margin-top:40px;text-align:center">
                        <a href="<?= e(BASE_URL) ?>/news/" class="btn btn-sub" style="background:#eef4fb;color:var(--primary);border-color:var(--line)">← Kembali ke Daftar Berita</a>
                    </div>
                <?php endif; ?>

            </div>
        </section>

<?php require __DIR__ . '/../includes/site_footer.php'; ?>
