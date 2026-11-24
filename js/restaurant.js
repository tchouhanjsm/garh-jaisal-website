(function () {
  'use strict';

  const booklet = document.querySelector('[data-menu-booklet]');
  if (booklet) {
    const pages = Array.from(booklet.querySelectorAll('[data-menu-page]'));
    const tabs = Array.from(booklet.querySelectorAll('[data-menu-tab]'));
    const pageTurns = Array.from(booklet.querySelectorAll('[data-menu-turn]'));
    const footer = booklet.querySelector('[data-menu-footer]');
    const status = booklet.querySelector('[data-menu-page-status]');
    const viewAll = booklet.querySelector('[data-menu-all]');
    let activePage = 0;
    let showAll = false;

    function showMenu(pageIndex, all, animate) {
      const direction = pageIndex < activePage ? 1 : -1;
      const page = pages[pageIndex];
      activePage = pageIndex;
      showAll = all;
      pages.forEach((item, index) => {
        item.hidden = !all && index !== pageIndex;
        item.classList.remove('is-turning');
        if (!all && index === pageIndex) tabs[index].setAttribute('aria-current', 'location');
        else tabs[index].removeAttribute('aria-current');
      });
      if (animate && !all) {
        page.style.setProperty('--page-turn-angle', (direction * 8) + 'deg');
        page.classList.add('is-turning');
      }
      footer.hidden = all;
      pageTurns.forEach(button => {
        const target = pageIndex + Number(button.dataset.menuTurn);
        button.hidden = all;
        button.disabled = target < 0 || target >= pages.length;
      });
      viewAll.textContent = all ? 'Back to booklet' : 'View all dishes';
      status.textContent = all
        ? 'All dishes'
        : 'Page ' + (pageIndex + 1) + ' of ' + pages.length + ' · ' + page.dataset.menuPageLabel;
    }

    function restoreMenu() {
      const hash = window.location.hash;
      const pageIndex = pages.findIndex(page => '#' + page.id === hash);
      if (pageIndex >= 0) showMenu(pageIndex, false, false);
      else if (hash === '#all-dishes') showMenu(activePage, true, false);
      else if (hash === '#menu' || !hash) showMenu(0, false, false);
    }

    function openPage(pageIndex) {
      showMenu(pageIndex, false, showAll || pageIndex !== activePage);
      window.history.replaceState(null, '', '#' + pages[pageIndex].id);
    }

    booklet.classList.add('is-enhanced');
    booklet.querySelector('.restaurant-booklet-controls').hidden = false;
    showMenu(0, false, false);
    restoreMenu();

    tabs.forEach((tab, index) => {
      tab.addEventListener('click', event => {
        if (event.ctrlKey || event.metaKey || event.shiftKey || event.altKey) return;
        event.preventDefault();
        openPage(index);
      });
    });
    pageTurns.forEach(button => {
      button.addEventListener('click', () => {
        openPage(activePage + Number(button.dataset.menuTurn));
        if (footer.contains(button)) {
          pages[activePage].querySelector('h3').focus({ preventScroll: true });
          pages[activePage].scrollIntoView({ block: 'start' });
        } else if (button.disabled) {
          tabs[activePage].focus();
        }
      });
    });
    viewAll.addEventListener('click', () => {
      showMenu(activePage, !showAll, false);
      window.history.replaceState(null, '', showAll ? '#all-dishes' : '#' + pages[activePage].id);
    });
    booklet.querySelector('[data-menu-print]').addEventListener('click', () => window.print());
    window.addEventListener('hashchange', restoreMenu);
  }

  var form = document.querySelector('.restaurant-form');
  if (form) {
    var dateField = form.querySelector('#date');
    var submit = form.querySelector('button[type="submit"]');
    if (dateField && !dateField.min) dateField.min = new Date().toISOString().slice(0, 10);
    form.addEventListener('submit', function () {
      if (submit) {
        submit.disabled = true;
        submit.textContent = 'Sending…';
      }
    });
  }
})();
