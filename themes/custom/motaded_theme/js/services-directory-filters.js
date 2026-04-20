(function (Drupal, once) {
  'use strict';

  Drupal.behaviors.servicesDirectorySidebarFilters = {
    attach: function (context) {
      // Make "All" option look like placeholder for select filters.
      once(
        'services-directory-select-placeholder',
        'select[name="field_beneficiaries_target_id"], select[name="field_tags_target_id"]',
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

      once(
        'services-directory-sidebar-filters',
        '.services-filters-sidebar input[type="checkbox"][name^="field_taxonomy_target_id["]',
        context
      ).forEach(function (checkbox) {
        checkbox.addEventListener('change', function () {
          var formId = checkbox.getAttribute('form') || 'views-exposed-form-services-page-1';
          var form = document.getElementById(formId);
          if (!form) {
            return;
          }
          var ajaxSubmit = form.querySelector('[data-bef-auto-submit-click], input.js-form-submit[type="submit"], button.js-form-submit[type="submit"]');
          if (ajaxSubmit) {
            ajaxSubmit.click();
            return;
          }
          if (typeof form.requestSubmit === 'function') {
            form.requestSubmit();
            return;
          }
          form.submit();
        });
      });
    }
  };
})(Drupal, once);
