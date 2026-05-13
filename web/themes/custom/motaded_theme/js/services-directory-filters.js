(function (Drupal, once) {
  'use strict';

  /**
   * Services directory: select “placeholder” styling when value is All/empty.
   * Exposed filters stay inside the form (see preprocess_views_exposed_form);
   * BEF autosubmit handles filter changes.
   */
  Drupal.behaviors.servicesDirectorySidebarFilters = {
    attach: function (context) {
      once(
        'services-directory-select-placeholder',
        '#services-directory-filters select[name="field_beneficiaries_target_id"], #services-directory-filters select[name="field_sector_target_id"], #services-directory-filters select[name="field_tags_target_id"]',
        context
      ).forEach(function (select) {
        var togglePlaceholder = function () {
          var currentValue = (select.value || '').toLowerCase();
          var isPlaceholder = currentValue === '' || currentValue === 'all';
          select.classList.toggle('services-directory__select--placeholder', isPlaceholder);
        };
        togglePlaceholder();
        select.addEventListener('change', togglePlaceholder);
      });
    },
  };
})(Drupal, once);
