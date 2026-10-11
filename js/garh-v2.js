document.addEventListener('DOMContentLoaded', () => {
  const button = document.querySelector('.nav-toggle');
  const nav = document.querySelector('.main-nav');
  if (!button || !nav) return;

  const setMenu = (open) => {
    nav.classList.toggle('is-open', open);
    button.setAttribute('aria-expanded', String(open));
    button.setAttribute('aria-label', open ? 'Close navigation' : 'Open navigation');
  };

  button.addEventListener('click', () => {
    setMenu(!nav.classList.contains('is-open'));
  });

  nav.querySelectorAll('a').forEach((link) => {
    link.addEventListener('click', () => setMenu(false));
  });

  document.addEventListener('keydown', (event) => {
    if (event.key === 'Escape') setMenu(false);
  });

  document.addEventListener('click', (event) => {
    if (!nav.classList.contains('is-open')) return;
    if (nav.contains(event.target) || button.contains(event.target)) return;
    setMenu(false);
  });
});
