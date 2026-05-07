(function () {
  const key = 'nutri_nova_theme';
  const html = document.documentElement;
  const saved = localStorage.getItem(key) || 'light';
  html.setAttribute('data-theme', saved);

  const toggle = document.getElementById('themeToggle');
  if (toggle) {
    toggle.addEventListener('click', () => {
      const next = html.getAttribute('data-theme') === 'light' ? 'dark' : 'light';
      html.setAttribute('data-theme', next);
      localStorage.setItem(key, next);
    });
  }
})();
