/**
 * Integrated Services (homepage): ensure all category radios stay visible.
 * Clears inline hiding from BEF soft_limit if it ran before config/theme fixes.
 */
(function (Drupal, once) {
  'use strict';

  Drupal.behaviors.motadedServicesHomeAllFilters = {
    attach(context) {
      once(
        'motaded-services-home-all-filters',
        '.paragraph--view-block--services-home .services-home-sidebar',
        context,
      ).forEach((aside) => {
        aside.querySelectorAll('.js-form-type-radio').forEach((row) => {
          row.hidden = false;
          row.style.removeProperty('display');
        });
        aside.querySelectorAll('.bef-soft-limit-link').forEach((link) => {
          link.remove();
        });
      });
    },
  };
})(Drupal, once);
