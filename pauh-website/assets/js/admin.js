// ===== SIDEBAR TOGGLE (mobile) =====
const sidebarToggle = document.getElementById('sidebar-toggle');
const sidebar = document.getElementById('admin-sidebar');
const sidebarBackdrop = document.getElementById('sidebar-backdrop');

function closeSidebar() {
  if (sidebar) sidebar.classList.remove('open');
  if (sidebarBackdrop) sidebarBackdrop.classList.remove('open');
}

if (sidebarToggle && sidebar) {
  sidebarToggle.addEventListener('click', () => {
    sidebar.classList.toggle('open');
    if (sidebarBackdrop) sidebarBackdrop.classList.toggle('open');
  });
}
if (sidebarBackdrop) {
  sidebarBackdrop.addEventListener('click', closeSidebar);
}
if (sidebar) {
  // Tutup sidebar otomatis setelah memilih menu di layar kecil.
  sidebar.querySelectorAll('nav a').forEach(link => {
    link.addEventListener('click', closeSidebar);
  });
}

// ===== FLASH MESSAGE AUTO-DISMISS =====
document.querySelectorAll('.alert[data-autohide]').forEach(alertEl => {
  setTimeout(() => {
    alertEl.style.transition = 'opacity .4s ease';
    alertEl.style.opacity = '0';
    setTimeout(() => alertEl.remove(), 400);
  }, 4000);
});

// ===== DELETE CONFIRMATION MODAL =====
// Setiap <form> dengan atribut data-confirm="Pesan konfirmasi" akan menampilkan
// modal konfirmasi sebelum benar-benar submit (dipakai untuk aksi hapus).
(function () {
  let pendingForm = null;

  const overlay = document.createElement('div');
  overlay.id = 'confirm-modal-overlay';
  overlay.style.cssText = 'display:none;position:fixed;inset:0;background:rgba(10,20,35,.55);z-index:2000;align-items:center;justify-content:center;padding:20px';
  overlay.innerHTML = `
    <div style="background:#fff;border-radius:14px;max-width:380px;width:100%;padding:24px;box-shadow:0 25px 60px rgba(0,0,0,.3)">
      <h3 style="margin:0 0 10px;font-size:16px;color:#142033">Konfirmasi Tindakan</h3>
      <p id="confirm-modal-message" style="margin:0 0 20px;font-size:13.5px;color:#64748b"></p>
      <div style="display:flex;gap:10px;justify-content:flex-end">
        <button type="button" id="confirm-modal-cancel" class="btn btn-outline btn-sm">Batal</button>
        <button type="button" id="confirm-modal-ok" class="btn btn-danger btn-sm">Ya, Lanjutkan</button>
      </div>
    </div>
  `;
  document.body.appendChild(overlay);

  const messageEl = overlay.querySelector('#confirm-modal-message');
  const cancelBtn = overlay.querySelector('#confirm-modal-cancel');
  const okBtn = overlay.querySelector('#confirm-modal-ok');

  function closeModal() {
    overlay.style.display = 'none';
    pendingForm = null;
  }

  cancelBtn.addEventListener('click', closeModal);
  overlay.addEventListener('click', (e) => {
    if (e.target === overlay) closeModal();
  });
  okBtn.addEventListener('click', () => {
    if (pendingForm) {
      const formToSubmit = pendingForm;
      pendingForm = null;
      overlay.style.display = 'none';
      formToSubmit.submit();
    }
  });

  document.querySelectorAll('form[data-confirm]').forEach(form => {
    form.addEventListener('submit', function (e) {
      e.preventDefault();
      pendingForm = form;
      messageEl.textContent = form.getAttribute('data-confirm') || 'Apakah Anda yakin?';
      overlay.style.display = 'flex';
    });
  });
})();

// ===== IMAGE PREVIEW ON UPLOAD (form tambah/edit berita) =====
const thumbnailInput = document.getElementById('thumbnail-input');
const thumbnailPreview = document.getElementById('thumbnail-preview');

if (thumbnailInput && thumbnailPreview) {
  thumbnailInput.addEventListener('change', function () {
    const file = this.files && this.files[0];
    if (!file) return;
    const reader = new FileReader();
    reader.onload = e => {
      thumbnailPreview.src = e.target.result;
      thumbnailPreview.style.display = 'block';
    };
    reader.readAsDataURL(file);
  });
}

// ===== SIMPLE CLIENT-SIDE FORM VALIDATION FEEDBACK =====
document.querySelectorAll('form.js-validate').forEach(form => {
  form.addEventListener('submit', function (e) {
    let valid = true;
    form.querySelectorAll('[required]').forEach(field => {
      if (!field.value || !field.value.trim()) {
        valid = false;
        field.style.borderColor = '#c0392b';
      } else {
        field.style.borderColor = '';
      }
    });
    if (!valid) {
      e.preventDefault();
      alert('Mohon lengkapi semua kolom yang wajib diisi.');
    }
  });
});
