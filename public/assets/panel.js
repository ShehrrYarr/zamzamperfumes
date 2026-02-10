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
