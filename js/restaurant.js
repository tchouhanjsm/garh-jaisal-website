(function () {
  'use strict';

  var tabs = document.querySelectorAll('.restaurant-tabs a');
  var sections = document.querySelectorAll('.restaurant-category');
  var vegToggle = document.querySelector('.restaurant-vegtoggle');

  if (tabs.length && 'IntersectionObserver' in window) {
    var observer = new IntersectionObserver(function (entries) {
      entries.forEach(function (entry) {
        if (!entry.isIntersecting) return;
        tabs.forEach(function (tab) {
          var active = tab.getAttribute('href') === '#' + entry.target.id;
          tab.classList.toggle('is-active', active);
          if (active) tab.setAttribute('aria-current', 'page');
          else tab.removeAttribute('aria-current');
        });
      });
    }, { rootMargin: '-20% 0px -65% 0px' });

    Array.prototype.forEach.call(sections, function (section) {
      observer.observe(section);
    });
  }

  if (vegToggle) {
    vegToggle.addEventListener('click', function () {
      var vegOnly = vegToggle.getAttribute('aria-pressed') !== 'true';
      vegToggle.setAttribute('aria-pressed', String(vegOnly));

      document.querySelectorAll('.restaurant-dish').forEach(function (dish) {
        dish.hidden = vegOnly && dish.dataset.veg !== '1';
      });

      document.querySelectorAll('.restaurant-category').forEach(function (category) {
        category.hidden = !category.querySelector('.restaurant-dish:not([hidden])');
      });
    });
  }

  var form = document.querySelector('.restaurant-form');
  if (form) {
    var dateField = form.querySelector('#date');
    var submit = form.querySelector('button[type="submit"]');

    if (dateField && !dateField.min) {
      dateField.min = new Date().toISOString().slice(0, 10);
    }

    form.addEventListener('submit', function () {
      if (submit) {
        submit.disabled = true;
        submit.textContent = 'Sending…';
      }
    });
  }
})();
