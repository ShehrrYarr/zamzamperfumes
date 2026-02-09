(function () {
  const app = document.querySelector('.app');
  const btn = document.getElementById('sidebarToggle');

  if (!app || !btn) return;

  // restore preference
  const saved = localStorage.getItem('sidebar_state');
  if (saved === 'closed') app.setAttribute('data-sidebar', 'closed');

  btn.addEventListener('click', () => {
    const current = app.getAttribute('data-sidebar') || 'open';
    const next = current === 'open' ? 'closed' : 'open';
    app.setAttribute('data-sidebar', next);
    localStorage.setItem('sidebar_state', next);
  });
})();
