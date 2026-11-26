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
    const search = booklet.querySelector('[data-menu-search]');
    const groups = Array.from(booklet.querySelectorAll('[data-menu-group]'));
    const dishes = groups.flatMap(group => Array.from(group.querySelectorAll('[data-menu-dish]'), row => ({
      row,
      text: (row.dataset.menuDish + ' ' + group.dataset.menuGroup).toLowerCase()
    })));
    let activePage = 0;
    let showAll = false;

    function showMenu(pageIndex, all, animate) {
      const direction = pageIndex < activePage ? 1 : -1;
      const page = pages[pageIndex];
      activePage = pageIndex;
      showAll = all;
      const words = search.value.toLowerCase().trim().split(/\s+/).filter(Boolean);
      const searching = words.length > 0;
      let matchCount = 0;
      dishes.forEach(({ row, text }) => {
        row.hidden = !words.every(word => text.includes(word));
        if (!row.hidden) matchCount += 1;
      });
      groups.forEach(group => {
        group.hidden = searching && !group.querySelector('[data-menu-dish]:not([hidden])');
      });
      const expanded = all || searching;
      pages.forEach((item, index) => {
        item.hidden = searching ? !item.querySelector('[data-menu-group]:not([hidden])') : !all && index !== pageIndex;
        item.classList.remove('is-turning');
        if (!expanded && index === pageIndex) tabs[index].setAttribute('aria-current', 'location');
        else tabs[index].removeAttribute('aria-current');
      });
      if (animate && !expanded) {
        page.style.setProperty('--page-turn-angle', (direction * 8) + 'deg');
        page.classList.add('is-turning');
      }
      footer.hidden = expanded;
      pageTurns.forEach(button => {
        const target = pageIndex + Number(button.dataset.menuTurn);
        button.hidden = expanded;
        button.disabled = target < 0 || target >= pages.length;
      });
      viewAll.textContent = all && !searching ? 'Back to booklet' : 'View all dishes';
      if (searching) {
        status.textContent = matchCount === 0 ? 'No dishes found' : matchCount + (matchCount === 1 ? ' dish found' : ' dishes found');
      } else {
        status.textContent = all ? 'All dishes' : 'Page ' + (pageIndex + 1) + ' of ' + pages.length + ' · ' + page.dataset.menuPageLabel;
      }
    }

    function restoreMenu() {
      const hash = window.location.hash;
      const pageIndex = pages.findIndex(page => '#' + page.id === hash);
      if (pageIndex >= 0) showMenu(pageIndex, false, false);
      else if (hash === '#all-dishes') showMenu(activePage, true, false);
      else if (hash === '#menu' || !hash) showMenu(0, false, false);
    }

    function openPage(pageIndex) {
      search.value = '';
      showMenu(pageIndex, false, showAll || pageIndex !== activePage);
      window.history.replaceState(null, '', '#' + pages[pageIndex].id);
    }

    search.closest('[data-menu-search-wrap]').hidden = false;
    search.addEventListener('input', () => showMenu(activePage, showAll, false));
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
      const wasSearching = search.value.trim() !== '';
      search.value = '';
      showMenu(activePage, wasSearching || !showAll, false);
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
