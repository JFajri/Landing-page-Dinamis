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

if (mobileMenuBtn && navMenu) {
  mobileMenuBtn.addEventListener('click', () => {
    const isOpen = navMenu.classList.toggle('active');
    mobileMenuBtn.setAttribute('aria-expanded', isOpen);
  });

  document.querySelectorAll('.nav-links a').forEach(link => {
    link.addEventListener('click', () => {
      navMenu.classList.remove('active');
      mobileMenuBtn.setAttribute('aria-expanded', false);
    });
  });
}

window.addEventListener('scroll', () => {
  if (navbar) {
    navbar.classList.toggle('scrolled', window.scrollY > 30);
  }

  const sections = document.querySelectorAll('section[id], footer[id]');
  const links = document.querySelectorAll('.nav-links a:not(.btn-cta)');
  let current = '';
  sections.forEach(section => {
    if (window.scrollY >= section.offsetTop - 120) current = section.id;
  });
  links.forEach(link => {
    link.classList.toggle('active', link.getAttribute('href') === '#' + current);
  });
});

// ===== INTERSECTION OBSERVER FOR REVEAL ANIMATION (ada di semua halaman) =====
const observer = new IntersectionObserver(entries => {
  entries.forEach(entry => {
    if (entry.isIntersecting) {
      entry.target.classList.add('visible');
      observer.unobserve(entry.target);
    }
  });
}, { threshold: .12 });

document.querySelectorAll('.reveal').forEach(el => observer.observe(el));

// ===== CALENDAR & EVENTS (hanya ada di halaman beranda) =====
// Data kegiatan sekarang dikelola Admin (Admin > Kalender & Event) dan disimpan
// di database. index.php menyuntikkan datanya lewat window.CALENDAR_EVENTS
// sebelum file ini dimuat, sehingga widget kalender di bawah ini tidak perlu
// diubah logikanya sama sekali — hanya sumber datanya yang berpindah dari
// object hardcode menjadi data dinamis dari server.
const events = window.CALENDAR_EVENTS || {};

let currentDate = new Date();
const calendarMonthEl = document.getElementById('calendar-month');
const calendarDaysEl = document.getElementById('calendar-days');
const eventsContainerEl = document.getElementById('events-container');
const prevMonthBtn = document.getElementById('prev-month');
const nextMonthBtn = document.getElementById('next-month');

function renderCalendar() {
  if (!calendarDaysEl || !calendarMonthEl) return;

  const year = currentDate.getFullYear();
  const month = currentDate.getMonth();

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

    const dateStr = `${year}-${String(month + 1).padStart(2, '0')}-${String(i).padStart(2, '0')}`;
    const today = new Date();

    if (dateStr === today.toISOString().split('T')[0]) {
      day.classList.add('today');
    }
    if (events[dateStr]) {
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

  const monthEvents = Object.entries(events)
    .filter(([date]) => {
      const [y, m] = date.split('-').map(Number);
      return y === year && m === month + 1;
    })
    .sort(([dateA], [dateB]) => dateA.localeCompare(dateB));

  eventsContainerEl.innerHTML = '';
  if (monthEvents.length === 0) {
    eventsContainerEl.innerHTML = '<p style="color:var(--muted);font-size:13px;margin:0">Tidak ada acara bulan ini</p>';
    return;
  }

  monthEvents.forEach(([date, event]) => {
    const d = new Date(date + 'T00:00:00');
    const eventEl = document.createElement('div');
    eventEl.className = 'event-item';
    eventEl.setAttribute('role', 'listitem');

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
    currentDate.setMonth(currentDate.getMonth() - 1);
    renderCalendar();
  });
}
if (nextMonthBtn) {
  nextMonthBtn.addEventListener('click', () => {
    currentDate.setMonth(currentDate.getMonth() + 1);
    renderCalendar();
  });
}
if (calendarDaysEl) {
  renderCalendar();
}

// ===== GALLERY INTERACTION (hanya ada di halaman beranda) =====
document.querySelectorAll('.gallery-item').forEach(item => {
  item.addEventListener('click', function () {
    const img = this.querySelector('img');
    console.log('Galeri item diklik:', img ? img.alt : '');
  });

  item.addEventListener('keypress', function (e) {
    if (e.key === 'Enter' || e.key === ' ') {
      e.preventDefault();
      this.click();
    }
  });
});
