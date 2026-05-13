(function (Drupal, once) {
  'use strict';

  /**
   * /sectors: placeholder styling on taxonomy + sort selects (BEF autosubmit).
   */
  Drupal.behaviors.sectorsDirectoryFilters = {
    attach: function (context) {
      once(
        'sectors-directory-select-placeholder',
        '#sectors-directory-filters select[name="field_sector_target_id"], #sectors-directory-filters select[name="sort_by"], #sectors-directory-filters select[name="sort_order"]',
        context
      ).forEach(function (select) {
        var togglePlaceholder = function () {
          var currentValue = (select.value || '').toLowerCase();
          var isPlaceholder =
            currentValue === '' ||
            currentValue === 'all' ||
            currentValue === '- any -' ||
            currentValue === 'all sectors';
          select.classList.toggle('services-directory__select--placeholder', isPlaceholder);
        };
        togglePlaceholder();
        select.addEventListener('change', togglePlaceholder);
      });
    },
  };
})(Drupal, once);
