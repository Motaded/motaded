/**
 * @file
 * FullCalendar day numbers: add crawlable href (Lighthouse) + preserve FC navigation.
 */
(function (Drupal, drupalSettings) {
  'use strict';

  const cfg = drupalSettings.motadedEventsCalendar || {};
  const eventsPath = cfg.eventsPath || '/events';
  const fromKey = cfg.dateFrom || 'motaded_event_date_from';
  const toKey = cfg.dateTo || 'motaded_event_date_to';

  function parseGotoDate(gotoRaw) {
    if (!gotoRaw) {
      return null;
    }
    try {
      const data = JSON.parse(gotoRaw);
      if (data && typeof data.date === 'string' && /^\d{4}-\d{2}-\d{2}$/.test(data.date)) {
        return data.date;
      }
    }
    catch (e) {
      return null;
    }
    return null;
  }

  function buildDayHref(isoDate) {
    const url = new URL(eventsPath, window.location.origin);
    url.searchParams.set(fromKey, isoDate);
    url.searchParams.set(toKey, isoDate);
    return url.pathname + url.search;
  }

  /**
   * Adds href to fc-day-number anchors; optional click guard for in-widget nav.
   */
  function patchDayLinksInContainer(container, preventNavigation) {
    if (!container) {
      return;
    }
    container.querySelectorAll('a.fc-day-number').forEach(function (anchor) {
      const iso = parseGotoDate(anchor.getAttribute('data-goto'));
      if (!iso) {
        return;
      }
      const href = buildDayHref(iso);
      if (anchor.getAttribute('href') !== href) {
        anchor.setAttribute('href', href);
      }
      if (preventNavigation && !anchor.dataset.motadedFcDayClickGuard) {
        anchor.dataset.motadedFcDayClickGuard = '1';
        anchor.addEventListener('click', function (e) {
          e.preventDefault();
        });
      }
    });
  }

  function wrapDatesSet(cal, preventNavigation) {
    const previous = cal.getOption('datesSet');
    if (previous && previous._motadedDayHrefWrapper) {
      return;
    }
    const wrapped = function (info) {
      if (typeof previous === 'function') {
        previous(info);
      }
      patchDayLinksInContainer(cal.el, preventNavigation);
    };
    wrapped._motadedDayHrefWrapper = true;
    cal.setOption('datesSet', wrapped);
  }

  function patchCalendarInstance(cal, preventNavigation) {
    if (!cal || typeof cal.setOption !== 'function' || !cal.el) {
      return false;
    }
    if (!cal._motadedDayHrefPatched) {
      cal._motadedDayHrefPatched = true;
      try {
        wrapDatesSet(cal, preventNavigation);
      }
      catch (e) {
        cal._motadedDayHrefPatched = false;
        return false;
      }
    }
    patchDayLinksInContainer(cal.el, preventNavigation);
    return true;
  }

  function patchCalendarsInScope(root, selector, preventNavigation) {
    if (!drupalSettings.calendar || !Array.isArray(drupalSettings.calendar)) {
      return;
    }
    const scope = root && root.querySelectorAll ? root : document;
    scope.querySelectorAll(selector).forEach(function (calendarEl) {
      const idx = parseInt(calendarEl.getAttribute('data-calendar-view-index'), 10);
      if (Number.isNaN(idx)) {
        return;
      }
      const cal = drupalSettings.calendar[idx];
      patchCalendarInstance(cal, preventNavigation);
    });
  }

  Drupal.motadedEventsCalendar = Drupal.motadedEventsCalendar || {};
  Drupal.motadedEventsCalendar.patchAll = function (root) {
    const scope = root && root.querySelectorAll ? root : document;
    patchCalendarsInScope(
      scope,
      '.events-home__calendar-shell .js-drupal-fullcalendar',
      true,
    );
    patchCalendarsInScope(
      scope,
      '.events-directory__calendar-shell .js-drupal-fullcalendar',
      false,
    );
  };

  Drupal.behaviors.motadedEventsCalendarCrawlableDays = {
    attach(context) {
      Drupal.motadedEventsCalendar.patchAll(context);
    },
  };
})(Drupal, drupalSettings);
