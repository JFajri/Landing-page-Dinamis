<?php
/**
 * kalender/index.php — Halaman kalender lengkap: widget bulanan (tanpa batas jumlah agenda) dan
 * daftar semua agenda berpaginasi dengan filter Mendatang / Sudah Lewat / Semua.
 * Data dikelola di Admin → Kalender & Event.
 */
require_once __DIR__ . '/../includes/bootstrap.php';
requireSectionOn('kalender', 'kalender');

$pdo = getConnection();

$filters = ['mendatang' => 'Mendatang', 'lalu' => 'Sudah Lewat', 'semua' => 'Semua'];
$filter  = $_GET['tampil'] ?? 'mendatang';
if (!isset($filters[$filter])) {
    $filter = 'mendatang';
}
$today = date('Y-m-d');

$where  = "status = 'active'";
$params = [];
$order  = 'event_date ASC, event_time ASC, id ASC';
if ($filter === 'mendatang') {
    $where .= ' AND event_date >= ?';
    $params[] = $today;
} elseif ($filter === 'lalu') {
    $where .= ' AND event_date < ?';
    $params[] = $today;
    $order = 'event_date DESC, event_time DESC, id DESC';
} else {
    $order = 'event_date DESC, event_time DESC, id DESC';
}

$perPage = listPerPage();
$count   = $pdo->prepare("SELECT COUNT(*) FROM events WHERE $where");
$count->execute($params);
$query   = $filter === 'mendatang' ? [] : ['tampil' => $filter];
[$page, $totalPages] = resolvePage((int) $count->fetchColumn(), $perPage, '/kalender/', $query);

$stmt = $pdo->prepare("SELECT id, title, event_date, event_time, location, description FROM events WHERE $where ORDER BY $order LIMIT $perPage OFFSET " . (($page - 1) * $perPage));
$stmt->execute($params);
$events = $stmt->fetchAll();

$calendarEventsForJs = calendarEventsForJs($pdo);

$pageTitle       = setting('sec.kalender.title') . ' - ' . setting('site.name') . pageSuffix($page);
$pageDescription = setting('sec.kalender.desc');
$navActive       = 'kalender';
$canonicalUrl    = BASE_URL . '/kalender/' . ($query || $page > 1 ? '?' . http_build_query($query + ($page > 1 ? ['page' => $page] : [])) : '');
require __DIR__ . '/../includes/site_header.php';
?>
        <section class="section-padding text-center page-top" id="kalender" aria-labelledby="kalender-title">
            <div class="container">
                <?php renderSectionHead('kalender', [], 'h1'); ?>

                <?php require __DIR__ . '/../includes/calendar_widget.php'; ?>

                <h2 class="subsection-title" id="agenda-list-title">Daftar Agenda</h2>
                <nav class="filter-tabs" aria-label="Filter agenda">
                    <?php foreach ($filters as $key => $label): ?>
                        <a href="<?= e($key === 'mendatang' ? '?' : '?tampil=' . $key) ?>"<?= $key === $filter ? ' class="is-current" aria-current="true"' : '' ?>><?= e($label) ?></a>
                    <?php endforeach; ?>
                </nav>

                <?php if (!$events): ?>
                    <p class="empty-note empty-note-tight"><?= e($filter === 'lalu' ? 'Belum ada agenda yang sudah lewat.' : 'Belum ada agenda ' . ($filter === 'mendatang' ? 'mendatang' : '') . ' yang dipublikasikan.') ?></p>
                <?php else: ?>
                    <ul class="event-cards" aria-labelledby="agenda-list-title">
                        <?php foreach ($events as $ev): ?>
                            <?php $isPast = $ev['event_date'] < $today; ?>
                            <li class="event-card reveal<?= $isPast ? ' is-past' : '' ?>">
                                <div class="event-card-date" aria-hidden="true">
                                    <span class="event-card-day"><?= (int) date('j', strtotime($ev['event_date'])) ?></span>
                                    <span class="event-card-month"><?= e(mb_substr(preg_replace('/^\d+\s+(\S+)\s+\d+$/', '$1', formatTanggalIndo($ev['event_date'])), 0, 3)) ?></span>
                                </div>
                                <div class="event-card-body">
                                    <h3><a href="<?= e(eventDetailUrl((int) $ev['id'])) ?>" class="stretched-link"><?= e($ev['title']) ?></a></h3>
                                    <p class="event-card-when"><time datetime="<?= e($ev['event_date']) ?>"><?= e(formatTanggalIndo($ev['event_date'])) ?></time><?= eventMetaLine($ev) !== '' ? ' ' : '' ?><?= eventMetaLine($ev) ?></p>
                                    <?php if (trim((string) $ev['description']) !== ''): ?>
                                        <p class="event-card-desc"><?= e(makeExcerpt((string) $ev['description'], 140)) ?></p>
                                    <?php endif; ?>
                                </div>
                            </li>
                        <?php endforeach; ?>
                    </ul>
                    <?php renderPagination($page, $totalPages, 'Halaman agenda', $query); ?>
                <?php endif; ?>
            </div>
        </section>

<?php require __DIR__ . '/../includes/site_footer.php'; ?>
