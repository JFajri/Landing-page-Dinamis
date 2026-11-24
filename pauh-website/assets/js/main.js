// ===== FORM HANDLING (Aspirasi/Pengaduan - hanya ada di halaman beranda) =====
// Data dikirim lewat fetch() ke aspirasi/submit.php (CSRF token, honeypot, dan consent ikut
// terkirim karena seluruh isian dibaca dari <form>). Pesan sukses/gagal tampil di halaman
// (role="alert" agar dibacakan pembaca layar), bukan lewat alert() bawaan browser.
const complaintForm = document.getElementById('complaintForm');
const alertBox = document.getElementById('successAlert');
const errorBox = document.getElementById('errorAlert');

if (complaintForm && alertBox) {
  const showError = (message) => {
    alertBox.style.display = 'none';
    if (!errorBox) return;
    errorBox.textContent = message;
    errorBox.style.display = 'block';
    errorBox.scrollIntoView({ behavior: prefersReducedMotionEarly() ? 'auto' : 'smooth', block: 'center' });
  };

  complaintForm.addEventListener('submit', function (e) {
    e.preventDefault();

    const submitBtn = complaintForm.querySelector('.submit-btn');
    const originalLabel = submitBtn ? submitBtn.textContent : '';

    if (errorBox) errorBox.style.display = 'none';

    if (submitBtn) {
      submitBtn.disabled = true;
      submitBtn.textContent = 'Mengirim...';
    }

    fetch(complaintForm.getAttribute('action') || ((window.BASE_URL || '') + '/aspirasi/submit.php'), {
      method: 'POST',
      body: new FormData(complaintForm),
      credentials: 'same-origin',
      headers: { 'Accept': 'application/json' }
    })
      .then(res => res.json().catch(() => ({ success: false, message: '' })))
      .then(data => {
        if (data.success) {
          if (errorBox) errorBox.style.display = 'none';
          alertBox.style.display = 'block';
          complaintForm.reset();
          alertBox.scrollIntoView({ behavior: prefersReducedMotionEarly() ? 'auto' : 'smooth', block: 'center' });
        } else {
          showError(data.message || 'Gagal mengirim laporan. Silakan periksa kembali isian Anda.');
        }
      })
      .catch(() => {
        showError('Gagal menghubungi server. Periksa koneksi internet Anda lalu coba lagi.');
      })
      .finally(() => {
        if (submitBtn) {
          submitBtn.disabled = false;
          submitBtn.textContent = originalLabel;
        }
      });
  });
}

function prefersReducedMotionEarly() {
  return window.matchMedia('(prefers-reduced-motion: reduce)').matches;
}

// ===== GAMBAR CADANGAN =====
// Gambar yang dimuat dari situs lain bisa gagal (diblokir hotlink, dihapus, server mati).
// Jika gagal, ganti satu kali dengan gambar placeholder lokal supaya kartu tidak "rusak".
document.querySelectorAll('img[data-fallback]').forEach(img => {
  img.addEventListener('error', function onError() {
    img.removeEventListener('error', onError);
    const fallback = img.getAttribute('data-fallback');
    if (fallback && img.src !== fallback) img.src = fallback;
  });
  // Jika error sudah terjadi sebelum skrip ini berjalan.
  if (img.complete && img.naturalWidth === 0) {
    const fallback = img.getAttribute('data-fallback');
    if (fallback && img.getAttribute('src') !== fallback) img.src = fallback;
  }
});

// ===== NAVIGATION & MOBILE MENU (ada di semua halaman) =====
const mobileMenuBtn = document.getElementById('mobile-menu');
const navMenu = document.getElementById('nav-menu');
const navbar = document.querySelector('.navbar');
const prefersReducedMotion = window.matchMedia('(prefers-reduced-motion: reduce)').matches;

function closeMobileMenu() {
  if (!navMenu || !mobileMenuBtn) return;
  navMenu.classList.remove('active');
  mobileMenuBtn.setAttribute('aria-expanded', 'false');
}

if (mobileMenuBtn && navMenu) {
  mobileMenuBtn.addEventListener('click', () => {
    const isOpen = navMenu.classList.toggle('active');
    mobileMenuBtn.setAttribute('aria-expanded', String(isOpen));
  });

  navMenu.querySelectorAll('a').forEach(link => link.addEventListener('click', closeMobileMenu));

  document.addEventListener('keydown', (e) => {
    if (e.key === 'Escape' && navMenu.classList.contains('active')) {
      closeMobileMenu();
      mobileMenuBtn.focus();
    }
  });
}

// ===== DROPDOWN SUBMENU (mis. "Profil"): buka/tutup lewat klik/Enter/Spasi =====
// Di desktop dropdown juga terbuka saat hover (CSS). Tutup dengan Escape atau klik di luar.
const subToggles = document.querySelectorAll('.nav-sub-toggle');

function closeSubMenus(except) {
  subToggles.forEach(btn => {
    if (btn === except) return;
    btn.setAttribute('aria-expanded', 'false');
    btn.parentElement.classList.remove('open');
  });
}

subToggles.forEach(btn => {
  btn.addEventListener('click', (e) => {
    e.stopPropagation();
    const item = btn.parentElement;
    const isOpen = item.classList.toggle('open');
    btn.setAttribute('aria-expanded', String(isOpen));
    closeSubMenus(btn);
  });
});

document.addEventListener('click', (e) => {
  if (!e.target.closest('.has-sub')) closeSubMenus();
});
document.addEventListener('keydown', (e) => {
  if (e.key !== 'Escape') return;
  const openBtn = document.querySelector('.has-sub.open > .nav-sub-toggle');
  if (openBtn) {
    closeSubMenus();
    openBtn.focus();
  }
});

// ===== LINK BERANDA: menuju URL utama, TANPA "#beranda" di address bar =====
// Link beranda (data-home-link) di-render sebagai URL bersih (mis. "/"). Jika pengunjung
// sudah berada di beranda, klik cukup menggulir ke atas dan membersihkan hash/query yang
// tersisa; jika berada di halaman lain (mis. /news), browser menavigasi biasa ke "/".
function normalizePath(path) {
  return path.replace(/index\.php$/, '').replace(/\/+$/, '') || '/';
}

function cleanUrlOfHash() {
  if (location.hash) {
    history.replaceState(null, '', location.pathname + location.search);
  }
}

document.querySelectorAll('a[data-home-link]').forEach(link => {
  link.addEventListener('click', (e) => {
    const target = new URL(link.href, location.href);
    const samePage = target.origin === location.origin && normalizePath(target.pathname) === normalizePath(location.pathname);
    if (!samePage) return;
    e.preventDefault();
    window.scrollTo({ top: 0, behavior: prefersReducedMotion ? 'auto' : 'smooth' });
    history.replaceState(null, '', target.pathname + target.search);
    closeMobileMenu();
  });
});

// Tautan lama yang masih memakai "/#beranda" (bookmark, hasil pencarian, atau tautan
// di tempat lain) dirapikan otomatis — baik saat halaman dibuka maupun saat hash berubah
// di halaman yang sama. Posisi scroll tetap di hero (section beranda) karena replaceState
// tidak menggulir ulang halaman.
function cleanLegacyHomeHash() {
  if (location.hash === '#beranda') {
    cleanUrlOfHash();
  }
}
cleanLegacyHomeHash();
window.addEventListener('hashchange', cleanLegacyHomeHash);

// ===== NAVBAR STATE & SCROLL-SPY =====
// Menu aktif ditentukan dari atribut data-spy (diisi server dari daftar menu), bukan dari
// href — jadi tetap benar walau href berupa URL absolut dan walau menu diubah di Admin.
const spyLinks = Array.from(document.querySelectorAll('.nav-links a[data-spy]'));
const spySections = Array.from(document.querySelectorAll('main section[id]'));
let scrollTicking = false;

function updateNavState() {
  scrollTicking = false;
  if (navbar) {
    navbar.classList.toggle('scrolled', window.scrollY > 30);
  }
  if (!spyLinks.length || !spySections.length) return;

  const offset = (navbar ? navbar.offsetHeight : 76) + 24;
  let currentId = '';
  let best = -Infinity;
  spySections.forEach(section => {
    const top = section.getBoundingClientRect().top - offset;
    if (top <= 0 && top > best) {
      best = top;
      currentId = section.id;
    }
  });
  if (!currentId) currentId = spySections[0].id;

  spyLinks.forEach(link => {
    link.classList.toggle('active', link.dataset.spy === currentId);
  });
}

window.addEventListener('scroll', () => {
  if (!scrollTicking) {
    scrollTicking = true;
    window.requestAnimationFrame(updateNavState);
  }
}, { passive: true });
window.addEventListener('resize', updateNavState);
updateNavState();

// ===== INTERSECTION OBSERVER FOR REVEAL ANIMATION (ada di semua halaman) =====
const revealEls = document.querySelectorAll('.reveal');
if ('IntersectionObserver' in window) {
  const observer = new IntersectionObserver(entries => {
    entries.forEach(entry => {
      if (entry.isIntersecting) {
        entry.target.classList.add('visible');
        observer.unobserve(entry.target);
      }
    });
  }, { threshold: .12 });
  revealEls.forEach(el => observer.observe(el));
} else {
  revealEls.forEach(el => el.classList.add('visible'));
}

// ===== CALENDAR & EVENTS (hanya ada di halaman beranda) =====
// Data kegiatan dikelola Admin (Admin > Kalender & Event). index.php menyuntikkannya lewat
// window.CALENDAR_EVENTS dalam bentuk { 'YYYY-MM-DD': [ {title, desc, time, location}, … ] }
// sehingga beberapa kegiatan pada tanggal yang sama semuanya tampil.
const events = window.CALENDAR_EVENTS || {};
const calendarEmptyText = (window.SITE_TEXT && window.SITE_TEXT.calendarEmpty) || 'Tidak ada acara bulan ini';

let currentDate = new Date();
const calendarMonthEl = document.getElementById('calendar-month');
const calendarDaysEl = document.getElementById('calendar-days');
const eventsContainerEl = document.getElementById('events-container');
const prevMonthBtn = document.getElementById('prev-month');
const nextMonthBtn = document.getElementById('next-month');

function eventsOn(dateKey) {
  const value = events[dateKey];
  if (Array.isArray(value)) return value;
  return value ? [value] : [];
}

// Kunci tanggal memakai zona waktu perangkat (bukan UTC) agar "hari ini" benar di WIB.
function toDateKey(d) {
  return d.getFullYear() + '-' + String(d.getMonth() + 1).padStart(2, '0') + '-' + String(d.getDate()).padStart(2, '0');
}

function renderCalendar() {
  if (!calendarDaysEl || !calendarMonthEl) return;

  const year = currentDate.getFullYear();
  const month = currentDate.getMonth();
  const todayKey = toDateKey(new Date());

  calendarMonthEl.textContent =
    currentDate.toLocaleDateString('id-ID', { month: 'long', year: 'numeric' });

  const firstDay = new Date(year, month, 1).getDay();
  const daysInMonth = new Date(year, month + 1, 0).getDate();
  const daysInPrevMonth = new Date(year, month, 0).getDate();

  calendarDaysEl.innerHTML = '';

  for (let i = firstDay - 1; i >= 0; i--) {
    const day = document.createElement('div');
    day.className = 'calendar-day other-month';
    day.textContent = daysInPrevMonth - i;
    calendarDaysEl.appendChild(day);
  }

  for (let i = 1; i <= daysInMonth; i++) {
    const day = document.createElement('div');
    day.className = 'calendar-day';
    day.textContent = i;

    const dateKey = `${year}-${String(month + 1).padStart(2, '0')}-${String(i).padStart(2, '0')}`;

    if (dateKey === todayKey) {
      day.classList.add('today');
    }
    if (eventsOn(dateKey).length) {
      day.classList.add('has-event');
    }

    calendarDaysEl.appendChild(day);
  }

  const totalCells = calendarDaysEl.children.length;
  for (let i = 1; i <= 42 - totalCells; i++) {
    const day = document.createElement('div');
    day.className = 'calendar-day other-month';
    day.textContent = i;
    calendarDaysEl.appendChild(day);
  }

  renderEvents();
}

function renderEvents() {
  if (!eventsContainerEl) return;

  const year = currentDate.getFullYear();
  const month = currentDate.getMonth();

  const monthDates = Object.keys(events)
    .filter(date => {
      const [y, m] = date.split('-').map(Number);
      return y === year && m === month + 1;
    })
    .sort();

  eventsContainerEl.innerHTML = '';

  const monthEvents = [];
  monthDates.forEach(date => eventsOn(date).forEach(event => monthEvents.push([date, event])));

  if (monthEvents.length === 0) {
    const empty = document.createElement('p');
    empty.style.cssText = 'color:var(--muted);font-size:13px;margin:0';
    empty.textContent = calendarEmptyText;
    eventsContainerEl.appendChild(empty);
    return;
  }

  // Beranda membatasi jumlah agenda yang tampil (SITE_TEXT.calendarLimit); sisanya ada di
  // halaman /kalender/. Nilai 0 = tanpa batas (halaman kalender lengkap).
  const limit = (window.SITE_TEXT && Number(window.SITE_TEXT.calendarLimit)) || 0;
  const visibleEvents = limit > 0 ? monthEvents.slice(0, limit) : monthEvents;

  visibleEvents.forEach(([date, event]) => {
    const d = new Date(date + 'T00:00:00');
    const eventEl = document.createElement('div');
    eventEl.className = 'event-item';

    // event.time / event.location bersifat opsional (diisi Admin jika perlu).
    // Data ini sudah di-escape di sisi server (lihat includes/page_helpers.php) sebelum dikirim
    // ke browser (calendarEventsForJs), jadi aman disisipkan langsung di sini.
    const metaParts = [];
    if (event.time) metaParts.push('🕐 ' + event.time + ' WIB');
    if (event.location) metaParts.push('📍 ' + event.location);
    const metaLine = metaParts.length
      ? `<div class="event-desc" style="font-weight:700;color:var(--primary-2)">${metaParts.join(' &nbsp;·&nbsp; ')}</div>`
      : '';

    eventEl.innerHTML = `
      <div class="event-date">${d.toLocaleDateString('id-ID', { day: 'numeric', month: 'long', year: 'numeric' })}</div>
      <div class="event-title">${event.url ? `<a href="${event.url}">${event.title}</a>` : event.title}</div>
      ${metaLine}
      <div class="event-desc">${event.desc}</div>
    `;
    eventsContainerEl.appendChild(eventEl);
  });

  if (visibleEvents.length < monthEvents.length && window.SITE_TEXT && window.SITE_TEXT.calendarMoreUrl) {
    const more = document.createElement('a');
    more.className = 'events-more';
    more.href = window.SITE_TEXT.calendarMoreUrl;
    more.textContent = `+${monthEvents.length - visibleEvents.length} agenda lainnya bulan ini →`;
    eventsContainerEl.appendChild(more);
  }
}

if (prevMonthBtn) {
  prevMonthBtn.addEventListener('click', () => {
    currentDate.setMonth(currentDate.getMonth() - 1, 1);
    renderCalendar();
  });
}
if (nextMonthBtn) {
  nextMonthBtn.addEventListener('click', () => {
    currentDate.setMonth(currentDate.getMonth() + 1, 1);
    renderCalendar();
  });
}
if (calendarDaysEl) {
  renderCalendar();
}

// ===== GALERI: LIGHTBOX (beranda dan halaman /galeri/) =====
// Memakai elemen <dialog> bawaan browser: fokus terkunci di dalam dialog, tombol Esc
// menutup, dan fokus kembali ke foto yang diklik — tanpa library tambahan.
const galleryItems = document.querySelectorAll('.gallery-item[data-full]');
if (galleryItems.length) {
  const dialog = document.createElement('dialog');
  dialog.className = 'lightbox';
  dialog.setAttribute('aria-label', 'Pratinjau foto galeri');
  dialog.innerHTML =
    '<button type="button" class="lightbox-close" aria-label="Tutup pratinjau">&times;</button>' +
    '<figure><img alt=""><figcaption></figcaption></figure>';
  document.body.appendChild(dialog);

  const lightboxImg = dialog.querySelector('img');
  const lightboxCaption = dialog.querySelector('figcaption');

  galleryItems.forEach(item => {
    item.addEventListener('click', () => {
      const full = item.getAttribute('data-full');
      const caption = item.getAttribute('data-caption') || '';
      if (typeof dialog.showModal !== 'function') {
        window.open(full, '_blank', 'noopener');
        return;
      }
      lightboxImg.src = full;
      lightboxImg.alt = caption;
      const description = item.getAttribute('data-description') || '';
      lightboxCaption.textContent = '';
      const captionEl = document.createElement('strong');
      captionEl.textContent = caption;
      lightboxCaption.appendChild(captionEl);
      if (description) {
        const descEl = document.createElement('span');
        descEl.className = 'lightbox-desc';
        descEl.textContent = description;
        lightboxCaption.appendChild(descEl);
      }
      dialog.showModal();
    });
  });

  dialog.querySelector('.lightbox-close').addEventListener('click', () => dialog.close());
  dialog.addEventListener('click', (e) => {
    if (e.target === dialog) dialog.close(); // klik area gelap di luar foto
  });
  dialog.addEventListener('close', () => { lightboxImg.removeAttribute('src'); });
}
