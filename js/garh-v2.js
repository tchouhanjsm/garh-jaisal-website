document.addEventListener('DOMContentLoaded', () => {
  // The shared header appears before each page's main landmark.
  // Give the skip link a stable, focusable destination on every rendered route.
  const main = document.querySelector('main');
  if (main) {
    if (!main.id) main.id = 'main-content';
    if (!main.hasAttribute('tabindex')) main.setAttribute('tabindex', '-1');
  }

  const button = document.querySelector('.nav-toggle');
  const nav = document.querySelector('.main-nav');
  if (!button || !nav) return;

  const items = Array.from(nav.querySelectorAll('[data-nav-item]'));

  const syncItem = (item) => {
    const toggle = item.querySelector('.nav-sub-toggle');
    if (!toggle) return;
    const forced = item.classList.contains('is-open');
    const dismissed = item.classList.contains('is-dismissed');
    const live = !forced && !dismissed && (item.matches(':hover') || item.matches(':focus-within'));
    const mobile = window.matchMedia('(max-width:1080px)').matches;
    const open = forced || (!mobile && live);
    toggle.setAttribute('aria-expanded', String(open));
    const label = toggle.dataset.label || '';
    toggle.setAttribute('aria-label', (open ? 'Hide ' : 'Show ') + label + ' submenu');
  };

  const closeItems = (except) => {
    items.forEach((item) => {
      if (item === except) return;
      item.classList.remove('is-open');
      syncItem(item);
    });
  };

  const setMenu = (open) => {
    nav.classList.toggle('is-open', open);
    button.setAttribute('aria-expanded', String(open));
    button.setAttribute('aria-label', open ? 'Close navigation' : 'Open navigation');
    if (!open) closeItems(null);
  };

  button.addEventListener('click', () => {
    setMenu(!nav.classList.contains('is-open'));
  });

  nav.querySelectorAll('a').forEach((link) => {
    link.addEventListener('click', () => setMenu(false));
  });

  items.forEach((item) => {
    const toggle = item.querySelector('.nav-sub-toggle');
    if (!toggle) return;

    toggle.addEventListener('click', () => {
      const willOpen = !item.classList.contains('is-open');
      closeItems(item);
      item.classList.toggle('is-open', willOpen);
      syncItem(item);
    });

    ['mouseenter', 'mouseleave', 'focusin', 'focusout'].forEach((type) => {
      item.addEventListener(type, () => {
        // Defer so :hover and :focus-within reflect the new state.
        window.setTimeout(() => {
          // Escape hides the panel until the pointer and focus have both left.
          if (!item.matches(':hover') && !item.matches(':focus-within')) {
            item.classList.remove('is-dismissed');
          }
          syncItem(item);
        }, 0);
      });
    });
  });

  document.addEventListener('keydown', (event) => {
    if (event.key !== 'Escape') return;

    const mobile = window.matchMedia('(max-width:1080px)').matches;
    if (mobile && nav.classList.contains('is-open')) {
      setMenu(false);
      button.focus();
      event.preventDefault();
      return;
    }

    const openItem = items.find((item) =>
      (item.classList.contains('is-open') || item.contains(document.activeElement)) &&
      item.querySelector('.nav-sub-toggle')
    );
    if (openItem) {
      openItem.classList.remove('is-open');
      openItem.classList.add('is-dismissed');
      const toggle = openItem.querySelector('.nav-sub-toggle');
      syncItem(openItem);
      if (toggle) toggle.focus();
      event.preventDefault();
      return;
    }

    if (nav.classList.contains('is-open')) {
      setMenu(false);
      button.focus();
      event.preventDefault();
    }
  });

  document.addEventListener('click', (event) => {
    if (!nav.contains(event.target)) closeItems(null);
    if (!nav.classList.contains('is-open')) return;
    if (nav.contains(event.target) || button.contains(event.target)) return;
    setMenu(false);
  });
});
