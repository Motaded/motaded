/**
 * @file
 * Routes implicit exposed-form submits (e.g. Enter in search) through Views AJAX.
 *
 * Core binds Views exposed AJAX to the Apply control's "click" event, not to
 * the form's "submit" event; implicit submission still performs a full page load.
 *
 * @see web/core/modules/views/js/ajax_view.js
 */
(function (Drupal, once, $) {
  'use strict';

  var FORM_IDS = [
    'views-exposed-form-library-block-1',
    'views-exposed-form-library-page-1',
  ];

  function isResetSubmitter(submitter) {
    if (!submitter) {
      return false;
    }
    var sel =
      submitter.getAttribute && submitter.getAttribute('data-drupal-selector');
    if (sel === 'edit-reset') {
      return true;
    }
    return (submitter.name || '') === 'reset';
  }

  function getApplyControl(form) {
    return (
      form.querySelector('.library-exposed__submit') ||
      form.querySelector(
        '.form-actions input[type="submit"]:not([data-drupal-selector="edit-reset"])',
      ) ||
      form.querySelector(
        '.form-actions button[type="submit"]:not([data-drupal-selector="edit-reset"])',
      )
    );
  }

  function elementHasDrupalAjax(element) {
    if (!element || !Drupal.ajax || !Drupal.ajax.instances) {
      return false;
    }
    var instances = Drupal.ajax.instances;
    for (var i = 0; i < instances.length; i++) {
      if (instances[i] && instances[i].element === element) {
        return true;
      }
    }
    return false;
  }

  Drupal.behaviors.libraryExposedAjaxSubmitBridge = {
    attach: function (context) {
      FORM_IDS.forEach(function (formId) {
        once('library-exposed-ajax-submit-bridge', '#' + formId, context).forEach(
          function (form) {
            if (!(form instanceof HTMLFormElement)) {
              return;
            }
            form.addEventListener(
              'submit',
              function (e) {
                if (isResetSubmitter(e.submitter)) {
                  return;
                }
                var apply = getApplyControl(form);
                if (!apply || !elementHasDrupalAjax(apply)) {
                  return;
                }
                e.preventDefault();
                $(apply).trigger('click');
              },
              true,
            );
          },
        );
      });
    },
  };
})(Drupal, once, jQuery);
