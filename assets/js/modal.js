// Simple Modal helper for the app. Exposes Modal namespace.
(function (global) {
  const Modal = {};
  const focusableSelector = 'a[href], button:not([disabled]), textarea, input, select';

  Modal.open = function (id) {
    const el = document.getElementById(id);
    if (!el) return;
    el.classList.add('open');
    el.setAttribute('aria-hidden', 'false');
    // manage focus
    const first = el.querySelector(focusableSelector);
    if (first) first.focus();
    document.body.style.overflow = 'hidden';
  };

  Modal.close = function (id) {
    const el = document.getElementById(id);
    if (!el) return;
    el.classList.remove('open');
    el.setAttribute('aria-hidden', 'true');
    document.body.style.overflow = '';
  };

  Modal.setStatus = function (id, text, type) {
    const el = document.getElementById(id);
    if (!el) return;
    const s = el.querySelector('.status');
    if (!s) return;
    s.textContent = text || '';
    s.className = 'status' + (type ? ' ' + type : '');
  };

  Modal.setBusy = function (id, busy, options = {}) {
    const el = document.getElementById(id);
    if (!el) return;
    const btn = el.querySelector('.btn-primary');
    if (!btn) return;
    if (busy) {
      btn.disabled = true;
      btn.dataset.text = btn.textContent;
      btn.textContent = options.busyText || 'En cours…';
    } else {
      btn.disabled = false;
      btn.textContent = btn.dataset.text || (options.readyText || 'Créer la partie');
    }
  };

  Modal.fetchGameTypes = async function (selectSelector, url) {
    const sel = document.querySelector(selectSelector);
    if (!sel) return;
    sel.innerHTML = '<option value="">Chargement...</option>';
    try {
      const res = await fetch(url);
      const data = await res.json();
      sel.innerHTML = '<option value="">-- Choisissez --</option>' + data.map(gt => `<option value="${gt.id}">${gt.name}</option>`).join('');
      return data;
    } catch (e) {
      sel.innerHTML = '<option value="">Erreur de chargement</option>';
      throw e;
    }
  };

  // Close on click outside content
  document.addEventListener('click', (e) => {
    const modal = e.target.closest('.modal');
    if (!modal) return;
    const content = e.target.closest('.modal-content');
    if (!content) Modal.close(modal.id);
  });

  // Close buttons
  document.addEventListener('click', (e) => {
    if (e.target.closest('.close-btn')) {
      const modal = e.target.closest('.modal');
      if (modal) Modal.close(modal.id);
    }
  });

  // Esc key
  document.addEventListener('keydown', (e) => {
    if (e.key === 'Escape') {
      const open = document.querySelector('.modal.open');
      if (open) Modal.close(open.id);
    }
  });

  global.Modal = Modal;
})(window);
