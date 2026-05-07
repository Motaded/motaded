(function (Drupal, once) {
  'use strict';

  var STORAGE_KEY = 'motaded:libraryHomeScrollY';

  function isSamePageLink(link) {
    if (!link || !link.href) {
      return false;
    }
    try {
      var url = new URL(link.href, window.location.href);
      return url.origin === window.location.origin && url.pathname === window.location.pathname;
    } catch (e) {
      return false;
    }
  }

  function hasLibraryQuery(link) {
    try {
      var url = new URL(link.href, window.location.href);
      return (
        url.searchParams.has('library_section') ||
        url.searchParams.has('field_category_target_id') ||
        url.searchParams.has('search')
      );
    } catch (e) {
      return false;
    }
  }

  function saveScroll() {
    try {
      window.sessionStorage.setItem(STORAGE_KEY, String(window.scrollY || 0));
    } catch (e) {
      // Ignore.
    }
  }

  function restoreScrollIfNeeded() {
    // Only restore when Library params are present; avoids surprising scroll jumps.
    var params = new URLSearchParams(window.location.search || '');
    if (!params.has('library_section') && !params.has('field_category_target_id') && !params.has('search')) {
      return;
    }
    var raw = null;
    try {
      raw = window.sessionStorage.getItem(STORAGE_KEY);
    } catch (e) {
      raw = null;
    }
    if (!raw) {
      return;
    }
    var y = parseInt(raw, 10);
    if (!isFinite(y) || y < 0) {
      return;
    }
    try {
      window.sessionStorage.removeItem(STORAGE_KEY);
    } catch (e) {
      // Ignore.
    }

    // After layout settles (fonts/images), then scroll.
    window.requestAnimationFrame(function () {
      window.requestAnimationFrame(function () {
        window.scrollTo(0, y);
      });
    });
  }

  Drupal.behaviors.libraryHomeScrollRestore = {
    attach: function (context) {
      once('library-home-scroll-restore', 'body', context).forEach(function () {
        restoreScrollIfNeeded();
      });
    }
  };

  Drupal.behaviors.libraryHomeScrollSave = {
    attach: function (context) {
      once('library-home-scroll-save', '.library-home-view a', context).forEach(function (link) {
        if (!isSamePageLink(link) || !hasLibraryQuery(link)) {
          return;
        }
        link.addEventListener('click', function () {
          saveScroll();
        });
      });
    }
  };
})(Drupal, once);

