(function (Drupal, once) {
  'use strict';

  /**
   * Syncs horizontal step tabs, column highlights, and platform panels.
   */
  Drupal.behaviors.motadedInvestorJourney = {
    attach(context) {
      once('motaded-investor-journey', '[data-investor-journey]', context).forEach((root) => {
        const tabs = root.querySelectorAll('[data-ij-tab]');
        const cols = root.querySelectorAll('[data-ij-col]');
        const panels = root.querySelectorAll('[data-ij-panel]');
        if (!tabs.length) {
          return;
        }

        const setActive = (index) => {
          tabs.forEach((tab, i) => {
            const on = i === index;
            tab.classList.toggle('is-active', on);
            tab.setAttribute('aria-selected', on ? 'true' : 'false');
            tab.setAttribute('tabindex', on ? '0' : '-1');
          });
          cols.forEach((col, i) => {
            col.classList.toggle('is-active', i === index);
          });
          panels.forEach((panel, i) => {
            if (i === index) {
              panel.removeAttribute('hidden');
            }
            else {
              panel.setAttribute('hidden', '');
            }
          });
        };

        tabs.forEach((tab, i) => {
          tab.addEventListener('click', () => setActive(i));
          tab.addEventListener('keydown', (e) => {
            let next = i;
            if (e.key === 'ArrowRight' || e.key === 'ArrowDown') {
              next = Math.min(i + 1, tabs.length - 1);
              e.preventDefault();
            }
            else if (e.key === 'ArrowLeft' || e.key === 'ArrowUp') {
              next = Math.max(i - 1, 0);
              e.preventDefault();
            }
            else if (e.key === 'Home') {
              next = 0;
              e.preventDefault();
            }
            else if (e.key === 'End') {
              next = tabs.length - 1;
              e.preventDefault();
            }
            if (next !== i) {
              setActive(next);
              tabs[next].focus();
            }
          });
        });

        cols.forEach((col, i) => {
          col.addEventListener('click', () => setActive(i));
        });
      });
    },
  };
})(Drupal, once);
