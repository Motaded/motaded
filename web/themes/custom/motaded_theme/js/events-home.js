/**
 * @file
 * Home events strip: FullCalendar v5+ HTML titles via eventContent + hovers.
 * List + calendar filters are one View (calendar block + attachment): no JS sync.
 */
(function ($, Drupal, drupalSettings) {
  'use strict';

  let ajaxGuardsBound = false;
  let tooltipListenerBound = false;
  let tooltipEl = null;
  let patchPollerId = null;

  function escapeHtml(input) {
    const str = input == null ? '' : String(input);
    return str
      .replace(/&/g, '&amp;')
      .replace(/</g, '&lt;')
      .replace(/>/g, '&gt;')
      .replace(/"/g, '&quot;')
      .replace(/'/g, '&#39;');
  }

  /** Fill or append `.fc-event-card__meta` using extendedProps (contrib strips HTML in title). */
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
      } else {
        const card = root.querySelector('.fc-event-card') || root.firstElementChild;
        if (card) {
          metaEl = doc.createElement('span');
          metaEl.className = 'fc-event-card__meta';
          metaEl.textContent = metaLine;
          card.appendChild(metaEl);
        }
      }
      return root.innerHTML;
    } catch (e) {
      return html;
    }
  }

  /** Layout + calendar block (list is a View attachment inside the same DOM). */
  function isEventsHomeCalendarContext() {
    return (
      document.querySelector('.events-home__grid, .events-home-strip') &&
      document.querySelector('.view-id-events.view-display-id-block_calendar')
    );
  }

  function clearAjaxFullscreenProgress() {
    $('.ajax-progress-fullscreen, .ajax-progress.ajax-progress-fullscreen').remove();
  }

  /**
   * FullCalendar v5+ ignores legacy eventRender; use eventContent for HTML titles.
   */
  function patchAsideCalendarsEventHtml() {
    if (!drupalSettings.calendar || !Array.isArray(drupalSettings.calendar)) {
      return;
    }
    document
      .querySelectorAll('.events-home__calendar-shell .js-drupal-fullcalendar')
      .forEach(function (calendarEl) {
        const idx = parseInt(calendarEl.getAttribute('data-calendar-view-index'), 10);
        if (Number.isNaN(idx)) {
          return;
        }
        const cal = drupalSettings.calendar[idx];
        if (!cal || typeof cal.setOption !== 'function') {
          return;
        }
        if (cal._motadedEventHtmlPatched) {
          return;
        }
        cal._motadedEventHtmlPatched = true;
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
              metaLine = parts.map(function (p) {
                return String(p).trim();
              }).join(' · ');
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
              body +=
                '<span class="fc-event-card__meta">' + escapeHtml(metaLine) + '</span>';
            }
            body += '</span>';
            return { html: timePart + body };
          });
        }
        catch (e) {
          // Older FullCalendar builds: ignore.
        }
      });
  }

  function schedulePatchRetries() {
    if (patchPollerId !== null) {
      window.clearInterval(patchPollerId);
      patchPollerId = null;
    }
    let attempts = 0;
    patchPollerId = window.setInterval(function () {
      attempts++;
      patchAsideCalendarsEventHtml();
      if (attempts > 40) {
        window.clearInterval(patchPollerId);
        patchPollerId = null;
      }
    }, 110);
  }

  function getEventTooltipData(eventEl) {
    const timeEl = eventEl.querySelector('.fc-event-time');
    const titleEl =
      eventEl.querySelector('.fc-event-card__title') ||
      eventEl.querySelector('.fc-event-title');
    const metaEl = eventEl.querySelector('.fc-event-card__meta');
    const primary = titleEl ? titleEl.textContent.trim() : '';
    const meta = metaEl ? metaEl.textContent.trim() : '';
    const merged = [];
    if (primary) {
      merged.push(primary);
    }
    if (meta) {
      merged.push(meta);
    }
    const title = merged.join(' · ');
    const time = timeEl ? timeEl.textContent.trim() : '';
    return {
      title,
      time,
    };
  }

  function ensureTooltip() {
    if (tooltipEl) {
      return tooltipEl;
    }
    tooltipEl = document.createElement('div');
    tooltipEl.className = 'events-home-calendar-tooltip';
    tooltipEl.setAttribute('role', 'tooltip');
    tooltipEl.setAttribute('aria-hidden', 'true');
    document.body.appendChild(tooltipEl);
    return tooltipEl;
  }

  function setTooltipPosition(clientX, clientY) {
    if (!tooltipEl) {
      return;
    }
    const gap = 14;
    const rect = tooltipEl.getBoundingClientRect();
    let left = clientX + gap;
    let top = clientY + gap;
    const maxLeft = window.innerWidth - rect.width - 8;
    const maxTop = window.innerHeight - rect.height - 8;
    if (left > maxLeft) {
      left = Math.max(8, clientX - rect.width - gap);
    }
    if (top > maxTop) {
      top = Math.max(8, clientY - rect.height - gap);
    }
    tooltipEl.style.left = left + 'px';
    tooltipEl.style.top = top + 'px';
  }

  function showTooltip(eventEl, clientX, clientY) {
    const data = getEventTooltipData(eventEl);
    if (!data.title) {
      return;
    }
    const el = ensureTooltip();
    const timeHtml = data.time
      ? '<div class="events-home-calendar-tooltip__time">' + escapeHtml(data.time) + '</div>'
      : '';
    el.innerHTML =
      '<div class="events-home-calendar-tooltip__title">' +
      escapeHtml(data.title) +
      '</div>' +
      timeHtml;
    el.classList.add('is-visible');
    el.setAttribute('aria-hidden', 'false');
    setTooltipPosition(clientX, clientY);
  }

  function hideTooltip() {
    if (!tooltipEl) {
      return;
    }
    tooltipEl.classList.remove('is-visible');
    tooltipEl.setAttribute('aria-hidden', 'true');
  }

  Drupal.behaviors.motadedEventsHome = {
    attach() {
      if (!isEventsHomeCalendarContext()) {
        return;
      }

      if (!ajaxGuardsBound) {
        ajaxGuardsBound = true;
        $(document).on(
          'ajaxComplete.motadedEventsHome ajaxStop.motadedEventsHome ajaxError.motadedEventsHome',
          function () {
            clearAjaxFullscreenProgress();
            window.setTimeout(schedulePatchRetries, 0);
          },
        );
      }

      schedulePatchRetries();

      if (tooltipListenerBound) {
        return;
      }
      if (window.matchMedia && window.matchMedia('(hover: none)').matches) {
        return;
      }
      tooltipListenerBound = true;

      const calShell = '.events-home__calendar-shell';
      $(document).on('mouseenter.motadedEventsTooltip', calShell + ' .fc-event', function (e) {
        showTooltip(this, e.clientX, e.clientY);
      });
      $(document).on('mousemove.motadedEventsTooltip', calShell + ' .fc-event', function (e) {
        setTooltipPosition(e.clientX, e.clientY);
      });
      $(document).on('mouseleave.motadedEventsTooltip', calShell + ' .fc-event', function () {
        hideTooltip();
      });
      $(document).on('scroll.motadedEventsTooltip resize.motadedEventsTooltip', function () {
        hideTooltip();
      });
    },
  };
})(jQuery, Drupal, drupalSettings);
