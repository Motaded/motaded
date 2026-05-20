(function (Drupal, once) {
  'use strict';

  function samePath(urlHref, baseHref) {
    try {
      var u = new URL(urlHref, baseHref);
      var b = new URL(baseHref);
      return u.origin === b.origin && u.pathname === b.pathname;
    } catch (e) {
      return false;
    }
  }

  function isLibrarySectionTab(anchor) {
    return !!(
      anchor &&
      anchor.closest('.library-home-view__tab-cards, .library-directory__tab-cards')
    );
  }

  function isLibraryHomeHub() {
    return !!document.getElementById('library-home');
  }

  function isLibraryNavLink(anchor) {
    if (!anchor || anchor.tagName !== 'A') {
      return false;
    }
    if (anchor.closest('.library-home-view__tab-cards')) {
      return true;
    }
    if (anchor.closest('.library-directory__tab-cards')) {
      return true;
    }
    if (
      anchor.closest('.library-directory__sidebar') ||
      anchor.classList.contains('library-directory__sidebar-all')
    ) {
      return true;
    }
    return false;
  }

  function viewAjaxAvailable(settings) {
    return !!(
      settings &&
      settings.views &&
      settings.views.ajaxViews &&
      Object.keys(settings.views.ajaxViews).length
    );
  }

  function librarySectionFromAnchor(anchor, url) {
    var sec = url.searchParams.get('library_section');
    if (sec !== null && sec !== '') {
      return sec;
    }
    var name = anchor.getAttribute('name') || '';
    var match = name.match(/library_section\[(\d+)\]/);
    return match ? match[1] : null;
  }

  function setLibrarySectionValue(form, sec) {
    if (sec === null || sec === '') {
      return;
    }
    var sel =
      form.querySelector('select[name="library_section"]') ||
      form.querySelector('input[name="library_section"]');
    if (sel) {
      sel.value = sec;
      return;
    }
    form.querySelectorAll('input[type="radio"][name="library_section"]').forEach(function (radio) {
      radio.checked = String(radio.value) === String(sec);
    });
  }

  function syncFormFromUrl(form, url, anchor) {
    var sec = anchor ? librarySectionFromAnchor(anchor, url) : url.searchParams.get('library_section');
    if (sec !== null && sec !== '') {
      setLibrarySectionValue(form, sec);
    }

    var catVal = url.searchParams.get('library_category');
    if (catVal === null || catVal === '') {
      catVal = url.searchParams.get('field_category_target_id');
    }
    var catSelect = form.querySelector('select[name="library_category"]');
    if (catSelect) {
      if (catVal !== null && catVal !== '') {
        catSelect.value = catVal;
      } else if (catSelect.querySelector('option[value=""]')) {
        catSelect.value = '';
      } else {
        catSelect.selectedIndex = 0;
      }
    } else {
      form.querySelectorAll('input[type="radio"][name="library_category"]').forEach(function (radio) {
        if (catVal === null || catVal === '') {
          var v = String(radio.value);
          radio.checked = v === '' || v === 'All' || v === '_none';
        } else {
          radio.checked = String(radio.value) === String(catVal);
        }
      });
    }

    var searchEl = form.querySelector('[name="search"]');
    if (searchEl && url.searchParams.has('search')) {
      searchEl.value = url.searchParams.get('search');
    }
  }

  Drupal.behaviors.libraryHomeAjaxLinks = {
    attach: function (context, settings) {
      once('library-home-ajax-links', 'body', context).forEach(function (body) {
        body.addEventListener(
          'click',
          function (e) {
            var a = e.target.closest('a[href]');
            if (!a || !isLibraryNavLink(a)) {
              return;
            }
            var onHomeLibrary = isLibraryHomeHub() && isLibrarySectionTab(a);
            if (!samePath(a.href, window.location.href) && !onHomeLibrary) {
              return;
            }
            if (!viewAjaxAvailable(settings)) {
              return;
            }

            var url;
            try {
              url = new URL(a.href, window.location.href);
            } catch (err) {
              return;
            }

            var form = document.querySelector('.library-view__exposed-form');
            var submit =
              form &&
              (form.querySelector('.library-exposed__submit') ||
                form.querySelector(
                  'input[type="submit"]:not([data-drupal-selector="edit-reset"])',
                ));
            if (!form || !submit) {
              return;
            }

            e.preventDefault();
            e.stopPropagation();
            syncFormFromUrl(form, url, a);
            if (onHomeLibrary) {
              var homeUrl = new URL(window.location.href);
              var sec = librarySectionFromAnchor(a, url);
              if (sec !== null && sec !== '') {
                homeUrl.searchParams.set('library_section', sec);
              } else {
                homeUrl.searchParams.delete('library_section');
              }
              window.history.replaceState(
                null,
                '',
                homeUrl.pathname + homeUrl.search + homeUrl.hash,
              );
            } else {
              window.history.replaceState(null, '', url.pathname + url.search + url.hash);
            }
            submit.click();
          },
          true,
        );
      });
    },
  };
})(Drupal, once);
