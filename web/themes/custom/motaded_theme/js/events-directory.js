/**
 * @file
 * /events directory: cards vs calendar toggle + FullCalendar meta (type · region).
 */
(function (Drupal, once, drupalSettings) {
  'use strict';

  // Keep default view as Cards; no persistence across page loads.

  function escapeHtml(input) {
    const str = input == null ? '' : String(input);
    return str
      .replace(/&/g, '&amp;')
      .replace(/</g, '&lt;')
      .replace(/>/g, '&gt;')
      .replace(/"/g, '&quot;')
      .replace(/'/g, '&#39;');
  }

  function injectMetaIntoEventHtml(html, metaLine) {
    if (!metaLine) {
      return html;
    }
    try {
      const doc = new DOMParser().parseFromString(
        '<div class="motaded-fc-root">' + html + '</div>',
        'text/html',
      );
      const root = doc.querySelector('.motaded-fc-root');
      if (!root) {
        return html;
      }
      let metaEl = root.querySelector('.fc-event-card__meta');
      if (metaEl) {
        metaEl.textContent = metaLine;
      }
      else {
        const card = root.querySelector('.fc-event-card') || root.firstElementChild;
        if (card) {
          metaEl = doc.createElement('span');
          metaEl.className = 'fc-event-card__meta';
          metaEl.textContent = metaLine;
          card.appendChild(metaEl);
        }
      }
      return root.innerHTML;
    }
    catch (e) {
      return html;
    }
  }

  function patchAsideCalendarsInDirectory(root) {
    if (!drupalSettings.calendar || !Array.isArray(drupalSettings.calendar)) {
      return;
    }
    root.querySelectorAll('.events-directory__calendar-shell .js-drupal-fullcalendar').forEach(function (calendarEl) {
      const idx = parseInt(calendarEl.getAttribute('data-calendar-view-index'), 10);
      if (Number.isNaN(idx)) {
        return;
      }
      const cal = drupalSettings.calendar[idx];
      if (!cal || typeof cal.setOption !== 'function') {
        return;
      }
      if (cal._motadedEventHtmlDirectoryPatched) {
        return;
      }
      cal._motadedEventHtmlDirectoryPatched = true;
      try {
        cal.setOption('eventContent', function (arg) {
          const raw = arg.event.title;
          if (typeof raw !== 'string') {
            return;
          }
          const ep = arg.event.extendedProps || {};
          let metaLine =
            ep.motaded_meta_line != null && String(ep.motaded_meta_line).trim() !== ''
              ? String(ep.motaded_meta_line).trim()
              : '';
          if (!metaLine) {
            const parts = [ep.motaded_type, ep.motaded_region].filter(function (p) {
              return p != null && String(p).trim() !== '';
            });
            metaLine = parts
              .map(function (p) {
                return String(p).trim();
              })
              .join(' · ');
          }
          const timePart =
            arg.timeText && String(arg.timeText).trim() !== ''
              ? '<span class="fc-event-time">' + escapeHtml(arg.timeText) + '</span>'
              : '';
          if (raw.indexOf('<') !== -1) {
            return { html: timePart + injectMetaIntoEventHtml(raw, metaLine) };
          }
          let body =
            '<span class="fc-event-card">' +
            '<span class="fc-event-card__title">' +
            escapeHtml(raw) +
            '</span>';
          if (metaLine) {
            body += '<span class="fc-event-card__meta">' + escapeHtml(metaLine) + '</span>';
          }
          body += '</span>';
          return { html: timePart + body };
        });
      }
      catch (e) {
        cal._motadedEventHtmlDirectoryPatched = false;
      }
    });
  }

  function scheduleCalendarPatchRetries(root, attemptsDone) {
    let n = attemptsDone || 0;
    patchAsideCalendarsInDirectory(root);
    if (Drupal.motadedEventsCalendar && typeof Drupal.motadedEventsCalendar.patchAll === 'function') {
      Drupal.motadedEventsCalendar.patchAll(root);
    }
    if (n > 36) {
      return;
    }
    window.setTimeout(function () {
      scheduleCalendarPatchRetries(root, n + 1);
    }, 140);
  }

  /**
   * Opens the native date picker when the user clicks anywhere on the control.
   */
  function openDatePicker(input) {
    if (!input || input.disabled || input.readOnly) {
      return;
    }
    input.focus();
    if (typeof input.showPicker === 'function') {
      try {
        input.showPicker();
        return;
      }
      catch (e) {
        // showPicker() may throw if not in a direct user gesture.
      }
    }
    input.click();
  }

  Drupal.behaviors.motadedEventsDirectoryDatePicker = {
    attach(context) {
      const selector =
        '#events-directory-filters .form-item-motaded-event-date-from .form-el-wrapper, ' +
        '#events-directory-filters .form-item-motaded-event-date-to .form-el-wrapper';

      once('motaded-events-date-picker', selector, context).forEach(function (wrapper) {
        wrapper.addEventListener('click', function () {
          const input = wrapper.querySelector('input[type="date"]');
          openDatePicker(input);
        });
      });
    },
  };

  Drupal.behaviors.motadedEventsDirectory = {
    attach(context) {
      function getRoot(el) {
        if (!el) return null;
        return el.closest ? el.closest('.events-directory-page') : null;
      }

      function applyMode(root, mode) {
        if (!root) return;
        const listPanel = root.querySelector('[data-events-dir-panel="list"]');
        const calPanel = root.querySelector('[data-events-dir-panel="calendar"]');
        const btnList = root.querySelector('[data-events-dir-mode="list"]');
        const btnCal = root.querySelector('[data-events-dir-mode="calendar"]');
        const toolbar = root.querySelector('.events-directory__toolbar');

        if (!listPanel || !btnList || !btnCal) {
          return;
        }

        if (!calPanel) {
          if (toolbar) {
            toolbar.hidden = true;
          }
          listPanel.hidden = false;
          return;
        }

        const isList = mode === 'list';
        calPanel.hidden = isList;
        listPanel.hidden = !isList;
        btnList.setAttribute('aria-pressed', isList ? 'true' : 'false');
        btnCal.setAttribute('aria-pressed', !isList ? 'true' : 'false');
        btnList.classList.toggle('is-active', isList);
        btnCal.classList.toggle('is-active', !isList);

        if (!isList) {
          scheduleCalendarPatchRetries(root, 0);
          window.setTimeout(function () {
            window.dispatchEvent(new Event('resize'));
          }, 80);
        }
      }

      // Initial mode: always default to Cards.
      once('motaded-events-directory-init', '.events-directory-page', context).forEach(function (root) {
        applyMode(root, 'list');
      });

      // Bind toggles per-button so AJAX re-renders still work.
      once('motaded-events-directory-toggle', '[data-events-dir-mode]', context).forEach(function (btn) {
        btn.addEventListener('click', function (e) {
          const mode = btn.getAttribute('data-events-dir-mode') || 'list';
          applyMode(getRoot(btn), mode);
        });
      });
    },
  };
})(Drupal, once, drupalSettings);
