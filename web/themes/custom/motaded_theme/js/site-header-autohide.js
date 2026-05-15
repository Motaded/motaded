/**
 * @file
 * Hide fixed header on scroll down, reveal on scroll up (near top always visible).
 */
(function (Drupal) {
  'use strict';

  const CONCEAL_AFTER = 8;
  const REVEAL_NEAR_TOP = 72;

  Drupal.behaviors.motadedSiteHeaderAutohide = {
    attach(context) {
      const root = context.querySelector('#site-header.site-header-root');
      if (!root || root.dataset.motHeaderAutohideAttached) {
        return;
      }
      if (window.matchMedia && window.matchMedia('(prefers-reduced-motion: reduce)').matches) {
        root.dataset.motHeaderAutohideAttached = '1';
        return;
      }
      root.dataset.motHeaderAutohideAttached = '1';

      let lastY = window.scrollY;
      let concealed = false;

      const apply = () => {
        if (document.documentElement.classList.contains('mot-menu-open')) {
          root.classList.remove('site-header--concealed');
          return;
        }
        if (concealed) {
          root.classList.add('site-header--concealed');
        } else {
          root.classList.remove('site-header--concealed');
        }
      };

      const onScroll = () => {
        const y = window.scrollY || 0;
        const dy = y - lastY;

        if (document.documentElement.classList.contains('mot-menu-open')) {
          lastY = y;
          apply();
          return;
        }

        if (y <= REVEAL_NEAR_TOP) {
          concealed = false;
        } else if (dy > CONCEAL_AFTER) {
          concealed = true;
        } else if (dy < -CONCEAL_AFTER) {
          concealed = false;
        }

        lastY = y;
        apply();
      };

      let ticking = false;
      const onScrollRaf = () => {
        if (ticking) {
          return;
        }
        ticking = true;
        window.requestAnimationFrame(() => {
          onScroll();
          ticking = false;
        });
      };

      window.addEventListener('scroll', onScrollRaf, { passive: true });

      const mo = new MutationObserver(apply);
      mo.observe(document.documentElement, {
        attributes: true,
        attributeFilter: ['class'],
      });

      apply();
    },
  };
})(Drupal);
