/**
 * @file
 * Tasheer visa: disable final submit until last-step consent is checked.
 */
(function (Drupal, once) {
  'use strict';

  Drupal.behaviors.motadedCustomTasheerFollowupAccept = {
    attach(context) {
      once(
        'tasheer-followup-accept',
        'input[name$="[tasheer_followup_consent]"]',
        context,
      ).forEach((agree) => {
        const form = agree.closest('form');
        if (!form) {
          return;
        }
        const submit = form.querySelector(
          'input.webform-button--submit, button.webform-button--submit',
        );
        if (!submit) {
          return;
        }
        const sync = () => {
          submit.disabled = !agree.checked;
        };
        agree.addEventListener('change', sync);
        sync();
      });
    },
  };
})(Drupal, once);
