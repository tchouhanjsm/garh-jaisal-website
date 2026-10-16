(function () {
  'use strict';

  var booklet = document.querySelector('[data-menu-booklet]');
  var tabs = Array.prototype.slice.call(document.querySelectorAll('.restaurant-tabs a[data-menu-tab]'));
  var sections = Array.prototype.slice.call(document.querySelectorAll('.restaurant-category[data-menu-page]'));
  var vegToggle = document.querySelector('.restaurant-vegtoggle');
  var filterStatus = document.querySelector('[data-restaurant-menu-status]');
  var pageStatus = booklet ? booklet.querySelector('[data-menu-page-status]') : null;
  var previousButton = booklet ? booklet.querySelector('[data-menu-previous]') : null;
  var nextButton = booklet ? booklet.querySelector('[data-menu-next]') : null;
  var printButton = booklet ? booklet.querySelector('[data-menu-print]') : null;
  var currentSection = null;

  function sectionForTab(tab) {
    if (!tab) return null;
    var href = tab.getAttribute('href') || '';
    if (href.charAt(0) !== '#') return null;
    return document.getElementById(href.slice(1));
  }

  function tabForSection(section) {
    if (!section) return null;
    return tabs.find(function (tab) {
      return sectionForTab(tab) === section;
    }) || null;
  }

  function setCurrentSection(section, updateHash) {
    if (!section || sections.indexOf(section) === -1 || section.hidden) return;
    currentSection = section;

    sections.forEach(function (candidate) {
      candidate.classList.toggle('is-current-page', candidate === section);
    });

    tabs.forEach(function (tab) {
      var active = sectionForTab(tab) === section;
      tab.classList.toggle('is-active', active);
      if (active) {
        tab.setAttribute('aria-current', 'location');
        tab.removeAttribute('aria-disabled');
        tab.removeAttribute('tabindex');
      } else {
        tab.removeAttribute('aria-current');
      }
    });

    var index = sections.indexOf(section);
    if (pageStatus) {
      var label = section.getAttribute('data-menu-page-label') || section.querySelector('h3')?.textContent?.trim() || 'Menu';
      pageStatus.textContent = 'Page ' + (index + 1) + ' of ' + sections.length + ': ' + label;
    }
    if (previousButton) previousButton.disabled = index <= 0;
    if (nextButton) nextButton.disabled = index >= sections.length - 1;

    if (updateHash && section.id && window.location.hash !== '#' + section.id) {
      window.history.pushState(null, '', '#' + section.id);
    }
  }

  function filterCounts(vegetarianOnly) {
    var allDishes = Array.prototype.slice.call(document.querySelectorAll('.restaurant-dish'));
    var categoriesWithMatches = sections.filter(function (section) {
      return Array.prototype.some.call(section.querySelectorAll('.restaurant-dish'), function (dish) {
        return !vegetarianOnly || dish.dataset.veg === '1';
      });
    });
    return {
      dishes: allDishes.filter(function (dish) {
        return !vegetarianOnly || dish.dataset.veg === '1';
      }).length,
      categories: categoriesWithMatches.length
    };
  }

  function updateFilterAnnouncement(vegetarianOnly) {
    if (!filterStatus) return;
    var counts = filterCounts(vegetarianOnly);
    var dishLabel = counts.dishes === 1 ? 'dish' : 'dishes';
    var categoryLabel = counts.categories === 1 ? 'category' : 'categories';
    filterStatus.textContent = (vegetarianOnly ? 'Vegetarian filter on' : 'Vegetarian filter off')
      + ': ' + counts.dishes + ' matching ' + dishLabel
      + ' across ' + counts.categories + ' menu ' + categoryLabel + '.';
  }

  function applyDietaryFilter(vegetarianOnly, announce) {
    sections.forEach(function (section) {
      var dishes = Array.prototype.slice.call(section.querySelectorAll('.restaurant-dish'));
      var matchingDishCount = 0;
      dishes.forEach(function (dish) {
        dish.hidden = vegetarianOnly && dish.dataset.veg !== '1';
        if (!dish.hidden) matchingDishCount += 1;
      });
      section.hidden = matchingDishCount === 0;
      var tab = tabForSection(section);
      if (tab) {
        if (section.hidden) {
          tab.setAttribute('aria-disabled', 'true');
          tab.setAttribute('tabindex', '-1');
        } else {
          tab.removeAttribute('aria-disabled');
          tab.removeAttribute('tabindex');
        }
      }
    });

    if (currentSection && currentSection.hidden) {
      var firstAvailable = sections.find(function (section) { return !section.hidden; });
      if (firstAvailable) setCurrentSection(firstAvailable, true);
    }

    if (announce) updateFilterAnnouncement(vegetarianOnly);
  }

  if (booklet && tabs.length && sections.length) {
    var requestedSection = window.location.hash
      ? document.getElementById(decodeURIComponent(window.location.hash.slice(1)))
      : null;
    if (!requestedSection || sections.indexOf(requestedSection) === -1) {
      requestedSection = sections[0];
    }

    // Progressive enhancement: hide non-current pages only after a usable initial page exists.
    setCurrentSection(requestedSection, false);
    booklet.classList.add('is-enhanced');

    tabs.forEach(function (tab) {
      tab.addEventListener('click', function (event) {
        if (tab.getAttribute('aria-disabled') === 'true') {
          event.preventDefault();
          return;
        }
        var section = sectionForTab(tab);
        if (section) setCurrentSection(section, false);
      });
    });

    if (previousButton) {
      previousButton.addEventListener('click', function () {
        var index = currentSection ? sections.indexOf(currentSection) : 0;
        for (var previousIndex = index - 1; previousIndex >= 0; previousIndex -= 1) {
          if (!sections[previousIndex].hidden) {
            setCurrentSection(sections[previousIndex], true);
            break;
          }
        }
      });
    }

    if (nextButton) {
      nextButton.addEventListener('click', function () {
        var index = currentSection ? sections.indexOf(currentSection) : 0;
        for (var nextIndex = index + 1; nextIndex < sections.length; nextIndex += 1) {
          if (!sections[nextIndex].hidden) {
            setCurrentSection(sections[nextIndex], true);
            break;
          }
        }
      });
    }

    if (printButton) {
      printButton.addEventListener('click', function () {
        window.print();
      });
    }

    window.addEventListener('popstate', function () {
      var section = window.location.hash
        ? document.getElementById(decodeURIComponent(window.location.hash.slice(1)))
        : sections[0];
      if (section && sections.indexOf(section) !== -1 && !section.hidden) {
        setCurrentSection(section, false);
      }
    });
  }

  if (vegToggle) {
    vegToggle.addEventListener('click', function () {
      var vegetarianOnly = vegToggle.getAttribute('aria-pressed') !== 'true';
      vegToggle.setAttribute('aria-pressed', String(vegetarianOnly));
      applyDietaryFilter(vegetarianOnly, true);
    });
  }

  var form = document.querySelector('.restaurant-form');
  if (form) {
    var dateField = form.querySelector('#date');
    var submit = form.querySelector('button[type="submit"]');

    if (dateField && !dateField.min) {
      // toISOString() uses UTC and can set the minimum to yesterday/tomorrow locally.
      var today = new Date();
      var month = String(today.getMonth() + 1).padStart(2, '0');
      var day = String(today.getDate()).padStart(2, '0');
      dateField.min = today.getFullYear() + '-' + month + '-' + day;
    }

    form.addEventListener('submit', function () {
      if (submit && !submit.disabled) {
        submit.disabled = true;
        submit.textContent = 'Sending…';
      }
    });
  }
})();
