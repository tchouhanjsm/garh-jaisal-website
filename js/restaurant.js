(function () {
  'use strict';

  var menuBooklet = document.querySelector('[data-menu-booklet]');
  var tabs = menuBooklet ? menuBooklet.querySelectorAll('[data-menu-tab]') : [];
  var pages = menuBooklet ? menuBooklet.querySelectorAll('[data-menu-page]') : [];
  var previous = menuBooklet ? menuBooklet.querySelector('[data-menu-previous]') : null;
  var next = menuBooklet ? menuBooklet.querySelector('[data-menu-next]') : null;
  var printButton = menuBooklet ? menuBooklet.querySelector('[data-menu-print]') : null;
  var pageStatus = menuBooklet ? menuBooklet.querySelector('[data-menu-page-status]') : null;
  var activePage = 0;

  if (menuBooklet && tabs.length && pages.length) {
    var initialId = window.location.hash ? window.location.hash.slice(1) : '';
    Array.prototype.forEach.call(pages, function (page, index) {
      if (page.id === initialId) activePage = index;
    });

    function showMenuPage(index, updateAddress) {
      activePage = Math.max(0, Math.min(index, pages.length - 1));
      Array.prototype.forEach.call(pages, function (page, pageIndex) {
        page.hidden = pageIndex !== activePage;
      });
      Array.prototype.forEach.call(tabs, function (tab, tabIndex) {
        if (tabIndex === activePage) {
          tab.classList.add('is-active');
          tab.setAttribute('aria-current', 'location');
        } else {
          tab.classList.remove('is-active');
          tab.removeAttribute('aria-current');
        }
      });
      if (previous) previous.disabled = activePage === 0;
      if (next) next.disabled = activePage === pages.length - 1;
      if (pageStatus) {
        var label = pages[activePage].getAttribute('data-menu-page-label') || 'Menu';
        pageStatus.textContent = 'Page ' + (activePage + 1) + ' of ' + pages.length + ': ' + label;
      }
      if (updateAddress && window.history && window.history.replaceState) {
        window.history.replaceState(null, '', '#' + pages[activePage].id);
      }
    }

    menuBooklet.classList.add('is-enhanced');
    showMenuPage(activePage, false);
    Array.prototype.forEach.call(tabs, function (tab, index) {
      tab.addEventListener('click', function (event) {
        event.preventDefault();
        showMenuPage(index, true);
      });
    });
    if (previous) previous.addEventListener('click', function () { showMenuPage(activePage - 1, true); });
    if (next) next.addEventListener('click', function () { showMenuPage(activePage + 1, true); });
    if (printButton) printButton.addEventListener('click', function () { window.print(); });
  }

  var vegToggle = document.querySelector('.restaurant-vegtoggle');
  if (vegToggle) {
    vegToggle.addEventListener('click', function () {
      var vegOnly = vegToggle.getAttribute('aria-pressed') !== 'true';
      vegToggle.setAttribute('aria-pressed', String(vegOnly));
      document.querySelectorAll('.restaurant-dish').forEach(function (dish) {
        dish.hidden = vegOnly && dish.dataset.veg !== '1';
      });
    });
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
