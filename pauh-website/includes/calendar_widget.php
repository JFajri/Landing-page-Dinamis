<?php
/**
 * includes/calendar_widget.php
 * Widget kalender bulanan + daftar agenda bulan itu (vanilla JS di assets/js/main.js; logikanya
 * tidak berubah). Dipakai bersama oleh beranda dan /kalender/.
 * Variabel: $calendarEventsForJs (hasil calendarEventsForJs()).
 * Batas jumlah agenda pada daftar diatur lewat $calendarLimit di includes/site_footer.php.
 */
?>
<script>window.CALENDAR_EVENTS = <?= json_encode($calendarEventsForJs, JSON_UNESCAPED_UNICODE | JSON_HEX_TAG | JSON_HEX_AMP) ?>;</script>

<div class="kalender-wrapper">
    <div class="kalender-container">
        <div class="calendar-header">
            <h3 id="calendar-month"><?= e(preg_replace('/^\d+\s+/', '', formatTanggalIndo(date('Y-m-d')))) ?></h3>
            <div class="calendar-nav">
                <button type="button" id="prev-month" aria-label="Bulan sebelumnya">&lt;</button>
                <button type="button" id="next-month" aria-label="Bulan berikutnya">&gt;</button>
            </div>
        </div>
        <div class="calendar-weekdays" aria-hidden="true">
            <div class="calendar-weekday">Min</div>
            <div class="calendar-weekday">Sen</div>
            <div class="calendar-weekday">Sel</div>
            <div class="calendar-weekday">Rab</div>
            <div class="calendar-weekday">Kam</div>
            <div class="calendar-weekday">Jum</div>
            <div class="calendar-weekday">Sab</div>
        </div>
        <div class="calendar-days" id="calendar-days" aria-label="Kalender tanggal"></div>
    </div>

    <div class="events-list">
        <h3><span aria-hidden="true">📋</span> <?= e(setting('calendar.upcoming_title')) ?></h3>
        <div id="events-container"></div>
    </div>
</div>
