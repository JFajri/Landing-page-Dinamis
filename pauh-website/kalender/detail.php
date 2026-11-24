<?php
/**
 * kalender/detail.php?id=N — Halaman detail satu agenda/kegiatan.
 */
require_once __DIR__ . '/../includes/bootstrap.php';
requireSectionOn('kalender', 'kalender');

$pdo  = getConnection();
$id   = (int) ($_GET['id'] ?? 0);
$stmt = $pdo->prepare("SELECT id, title, event_date, event_time, location, description FROM events WHERE id = ? AND status = 'active' LIMIT 1");
$stmt->execute([$id]);
$event = $stmt->fetch();
if (!$event) {
    renderNotFound('← Kembali ke Kalender', '/kalender/', 'kalender');
}

$today = date('Y-m-d');
$isPast = $event['event_date'] < $today;

$upcoming = $pdo->prepare("SELECT id, title, event_date FROM events WHERE status = 'active' AND event_date >= ? AND id <> ? ORDER BY event_date ASC, event_time ASC LIMIT 4");
$upcoming->execute([$today, $event['id']]);
$others = $upcoming->fetchAll();

$pageTitle       = $event['title'] . ' - ' . setting('site.name');
$pageDescription = trim((string) $event['description']) !== ''
    ? makeExcerpt((string) $event['description'], 155)
    : 'Agenda kegiatan ' . formatTanggalIndo($event['event_date']) . ' di ' . setting('site.brand_title') . '.';
$navActive       = 'kalender';
$canonicalUrl    = eventDetailUrl((int) $event['id']);
require __DIR__ . '/../includes/site_header.php';
?>
        <section class="section-padding page-top" id="kalender-detail" aria-labelledby="detail-title">
            <div class="container">
                <?php renderBreadcrumb([['Beranda', '/'], [setting('sec.kalender.title'), '/kalender/'], [$event['title'], null]]); ?>

                <div class="detail-layout">
                    <article class="detail-main">
                        <div class="eyebrow"><?= e(setting('sec.kalender.eyebrow')) ?><?= $isPast ? ' · Sudah Berlangsung' : '' ?></div>
                        <h1 class="detail-title" id="detail-title"><?= e($event['title']) ?></h1>

                        <dl class="event-facts">
                            <div><dt>Tanggal</dt><dd><span aria-hidden="true">📅</span> <time datetime="<?= e($event['event_date']) ?>"><?= e(formatTanggalIndo($event['event_date'])) ?></time></dd></div>
                            <?php if (!empty($event['event_time'])): ?>
                                <div><dt>Waktu</dt><dd><span aria-hidden="true">🕐</span> <?= e(substr($event['event_time'], 0, 5)) ?> WIB</dd></div>
                            <?php endif; ?>
                            <?php if (!empty($event['location'])): ?>
                                <div><dt>Lokasi</dt><dd><span aria-hidden="true">📍</span> <?= e($event['location']) ?></dd></div>
                            <?php endif; ?>
                        </dl>

                        <?php if (trim((string) $event['description']) !== ''): ?>
                            <div class="prose"><?= renderRichText($event['description']) ?></div>
                        <?php endif; ?>
                    </article>

                    <?php if ($others): ?>
                    <aside class="detail-aside" aria-labelledby="others-title">
                        <h2 class="aside-title" id="others-title">Agenda Mendatang Lainnya</h2>
                        <ul class="aside-list">
                            <?php foreach ($others as $other): ?>
                                <li>
                                    <a href="<?= e(eventDetailUrl((int) $other['id'])) ?>"><?= e($other['title']) ?></a>
                                    <span class="aside-meta"><?= e(formatTanggalIndo($other['event_date'])) ?></span>
                                </li>
                            <?php endforeach; ?>
                        </ul>
                    </aside>
                    <?php endif; ?>
                </div>

                <div class="section-action section-action-lg text-center">
                    <a href="<?= e(BASE_URL) ?>/kalender/" class="btn btn-solid">← <?= e(setting('sec.kalender.more')) ?></a>
                </div>
            </div>
        </section>

<?php require __DIR__ . '/../includes/site_footer.php'; ?>
