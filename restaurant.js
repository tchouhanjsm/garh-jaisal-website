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
          if (active) tab.setAttribute('aria-current', 'location');
          else tab.removeAttribute('aria-current');
        });
      });
    }, { rootMargin: '-20% 0px -65% 0px' });

    Array.prototype.forEach.call(sections, function (section) {
      observer.observe(section);
    });
  }

  if (vegToggle) {
    var menuStatus = document.querySelector('[data-restaurant-menu-status]');

    var updateMenuFilter = function (vegOnly) {
      var visibleDishCount = 0;
      var visibleCategoryCount = 0;

      document.querySelectorAll('.restaurant-dish').forEach(function (dish) {
        dish.hidden = vegOnly && dish.dataset.veg !== '1';
        if (!dish.hidden) visibleDishCount += 1;
      });

      document.querySelectorAll('.restaurant-category').forEach(function (category) {
        var hasVisibleDishes = Boolean(category.querySelector('.restaurant-dish:not([hidden])'));
        category.hidden = !hasVisibleDishes;
        if (hasVisibleDishes) visibleCategoryCount += 1;
      });

      if (menuStatus) {
        var dishLabel = visibleDishCount === 1 ? 'dish' : 'dishes';
        var categoryLabel = visibleCategoryCount === 1 ? 'category' : 'categories';
        menuStatus.textContent = (vegOnly ? 'Vegetarian filter on' : 'Vegetarian filter off')
          + ': showing ' + visibleDishCount + ' ' + dishLabel
          + ' across ' + visibleCategoryCount + ' ' + categoryLabel + '.';
      }
    };

    vegToggle.addEventListener('click', function () {
      var vegOnly = vegToggle.getAttribute('aria-pressed') !== 'true';
      vegToggle.setAttribute('aria-pressed', String(vegOnly));
      updateMenuFilter(vegOnly);
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
