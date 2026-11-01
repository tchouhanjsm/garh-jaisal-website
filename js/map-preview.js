'use strict';

(function () {
  function initialiseMapPreviews() {
    document.querySelectorAll('[data-map-preview]').forEach(function (details) {
      if (details.dataset.mapPreviewInitialised === 'true') return;

      var iframe = details.querySelector('iframe[data-map-src]');
      var label = details.querySelector('[data-map-preview-label]');
      if (!iframe) return;

      details.dataset.mapPreviewInitialised = 'true';
      details.addEventListener('toggle', function () {
        if (details.open && !iframe.hasAttribute('src')) {
          var source = iframe.getAttribute('data-map-src');
          if (source) iframe.setAttribute('src', source);
        }

        if (label) {
          if (details.open) {
            label.textContent = 'Hide interactive map';
          } else {
            label.textContent = iframe.hasAttribute('src')
              ? 'Show interactive map'
              : 'Load interactive map';
          }
        }
      });
    });
  }

  if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', initialiseMapPreviews);
  } else {
    initialiseMapPreviews();
  }
}());
