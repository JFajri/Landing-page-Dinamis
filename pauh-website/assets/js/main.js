// ===== FORM HANDLING (Aspirasi/Pengaduan - hanya ada di halaman beranda) =====
// Data benar-benar dikirim & disimpan ke database lewat aspirasi/submit.php,
// lalu Admin bisa melihat & memproses laporan di Admin > Aspirasi & Pengaduan.
const complaintForm = document.getElementById('complaintForm');
const alertBox = document.getElementById('successAlert');

if (complaintForm && alertBox) {
  complaintForm.addEventListener('submit', function (e) {
    e.preventDefault();

    const submitBtn = complaintForm.querySelector('.submit-btn');
    const originalLabel = submitBtn ? submitBtn.textContent : '';

    const formData = new FormData();
    formData.append('name', (document.getElementById('nama') || {}).value || '');
    formData.append('contact', (document.getElementById('email') || {}).value || '');
    formData.append('kelurahan', (document.getElementById('kelurahanSelect') || {}).value || '');
    formData.append('message', (document.getElementById('pesan') || {}).value || '');

    if (submitBtn) {
      submitBtn.disabled = true;
      submitBtn.textContent = 'Mengirim...';
    }

    fetch((window.BASE_URL || '') + '/aspirasi/submit.php', {
      method: 'POST',
      body: formData
    })
      .then(res => res.json())
      .then(data => {
        if (data.success) {
          alertBox.style.display = 'block';
          complaintForm.reset();
          setTimeout(() => alertBox.style.display = 'none', 5000);
          alertBox.scrollIntoView({ behavior: 'smooth', block: 'center' });
        } else {
          alert(data.message || 'Gagal mengirim laporan. Silakan periksa kembali isian Anda.');
        }
      })
      .catch(() => {
        alert('Gagal menghubungi server. Silakan coba lagi.');
      })
      .finally(() => {
        if (submitBtn) {
          submitBtn.disabled = false;
          submitBtn.textContent = originalLabel;
        }
      });
  });
}

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

  monthEvents.forEach(([date, event]) => {
    const d = new Date(date + 'T00:00:00');
    const eventEl = document.createElement('div');
    eventEl.className = 'event-item';

    // event.time / event.location bersifat opsional (diisi Admin jika perlu).
    // Data ini sudah di-escape di sisi server (lihat index.php) sebelum dikirim
    // ke browser, jadi aman disisipkan langsung di sini.
    const metaParts = [];
    if (event.time) metaParts.push('🕐 ' + event.time + ' WIB');
    if (event.location) metaParts.push('📍 ' + event.location);
    const metaLine = metaParts.length
      ? `<div class="event-desc" style="font-weight:700;color:var(--primary-2)">${metaParts.join(' &nbsp;·&nbsp; ')}</div>`
      : '';

    eventEl.innerHTML = `
      <div class="event-date">${d.toLocaleDateString('id-ID', { day: 'numeric', month: 'long', year: 'numeric' })}</div>
      <div class="event-title">${event.title}</div>
      ${metaLine}
      <div class="event-desc">${event.desc}</div>
    `;
    eventsContainerEl.appendChild(eventEl);
  });
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

// ===== GALERI: LIGHTBOX (hanya ada di halaman beranda) =====
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
      lightboxCaption.textContent = caption;
      dialog.showModal();
    });
  });

  dialog.querySelector('.lightbox-close').addEventListener('click', () => dialog.close());
  dialog.addEventListener('click', (e) => {
    if (e.target === dialog) dialog.close(); // klik area gelap di luar foto
  });
  dialog.addEventListener('close', () => { lightboxImg.removeAttribute('src'); });
}
