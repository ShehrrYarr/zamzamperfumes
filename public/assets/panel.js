(() => {
  const app = document.querySelector('.app');
  const btn = document.getElementById('sidebarToggle');
  const overlay = document.getElementById('sidebarOverlay');

  if (!app || !btn) return;

  // short labels for collapsed state
  document.querySelectorAll('.nav .nav-link').forEach(a => {
    if (!a.dataset.short) {
      const txt = (a.textContent || '').trim();
      a.dataset.short = txt ? txt.substring(0, 2).toUpperCase() : '•';
    }
  });

  const isMobile = () => window.matchMedia('(max-width: 980px)').matches;

  // On mobile: default = collapsed (hidden)
  // On desktop: restore saved
  const saved = localStorage.getItem('zzp_sidebar');
  if (isMobile()) {
    app.setAttribute('data-sidebar', 'collapsed');
  } else if (saved === 'collapsed' || saved === 'open') {
    app.setAttribute('data-sidebar', saved);
  } else {
    app.setAttribute('data-sidebar', 'open');
  }

  function setState(state){
    app.setAttribute('data-sidebar', state);
    if (!isMobile()) localStorage.setItem('zzp_sidebar', state);
  }

  function toggle(){
    const current = app.getAttribute('data-sidebar') || 'open';
    setState(current === 'collapsed' ? 'open' : 'collapsed');
  }

  btn.addEventListener('click', toggle);

  // Clicking overlay closes sidebar (mobile)
  if (overlay) {
    overlay.addEventListener('click', () => setState('collapsed'));
  }

  // When switching between mobile/desktop, apply correct behavior
  window.addEventListener('resize', () => {
    if (isMobile()) {
      setState('collapsed');
    } else {
      const s = localStorage.getItem('zzp_sidebar') || 'open';
      setState(s);
    }
  });
})();

window.openBatchPrint = function (baseUrl) {
  const w = prompt("Sticker width (in inches)? e.g. 2", "2");
  if (w === null) return;

  const h = prompt("Sticker height (in inches)? e.g. 1", "1");
  if (h === null) return;

  const width = parseFloat(w);
  const height = parseFloat(h);

  if (!isFinite(width) || width <= 0) { alert("Invalid width"); return; }
  if (!isFinite(height) || height <= 0) { alert("Invalid height"); return; }

  const url = baseUrl + "?w=" + encodeURIComponent(width) + "&h=" + encodeURIComponent(height);
  window.open(url, "_blank", "noopener,noreferrer");
};
