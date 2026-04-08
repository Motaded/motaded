/**
 * Non-blocking Font Awesome: only core + brands CSS from the theme (no all.min / v4-shims / cdnjs).
 * Paths are under themes/custom/motaded_theme/dist/fontawesome/css/
 */
(function () {
  if (document.documentElement.getAttribute('data-fa-async') === '1') {
    return;
  }
  document.documentElement.setAttribute('data-fa-async', '1');

  var pathBase = (typeof drupalSettings !== 'undefined' && drupalSettings.path && drupalSettings.path.baseUrl)
    ? drupalSettings.path.baseUrl
    : '/';
  var base = pathBase.replace(/\/?$/, '/') + 'themes/custom/motaded_theme/dist/fontawesome/css/';

  function inject() {
    ['fontawesome.min.css', 'brands.min.css'].forEach(function (file) {
      var link = document.createElement('link');
      link.rel = 'stylesheet';
      link.href = base + file;
      document.head.appendChild(link);
    });
  }

  if (typeof window.requestIdleCallback === 'function') {
    window.requestIdleCallback(inject, { timeout: 2000 });
  }
  else {
    window.setTimeout(inject, 1);
  }
})();
