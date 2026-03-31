/**
 * @file
 * Blocks final webform submit until email OTP is verified (modal + JSON API).
 */
(function (Drupal, once) {
  'use strict';

  /**
   * @param {string} webformId
   * @param {string} email
   * @returns {string}
   */
  function storageKey(webformId, email) {
    return `emailVerification:${webformId}:${email.trim().toLowerCase()}`;
  }

  /**
   * @returns {HTMLDialogElement}
   */
  function getOrCreateDialog() {
    let el = document.getElementById('email-verification-dialog');
    if (el) {
      return el;
    }
    el = document.createElement('dialog');
    el.id = 'email-verification-dialog';
    el.className = 'email-verification-dialog';
    el.setAttribute('aria-modal', 'true');
    el.innerHTML =
      '<div class="email-verification-dialog__inner">' +
      '<button type="button" class="email-verification-dialog__close" aria-label="Close">' +
      '<span class="email-verification-dialog__close-icon" aria-hidden="true">&times;</span>' +
      '</button>' +
      '<p class="email-verification-dialog__banner" role="status" aria-live="polite" hidden></p>' +
      '<h2 class="email-verification-dialog__title"></h2>' +
      '<p class="email-verification-dialog__hint"></p>' +
      '<label class="email-verification-dialog__label">' +
      '<span class="email-verification-dialog__label-text"></span>' +
      '<input type="text" class="email-verification-dialog__input" autocomplete="one-time-code" inputmode="numeric" maxlength="12" />' +
      '</label>' +
      '<p class="email-verification-dialog__error" hidden></p>' +
      '<div class="email-verification-dialog__actions">' +
      '<button type="button" class="button email-verification-dialog__btn-resend"></button>' +
      '<button type="button" class="button button--primary email-verification-dialog__btn-confirm"></button>' +
      '</div>' +
      '</div>';
    document.body.appendChild(el);
    return el;
  }

  /**
   * @param {object} settings
   * @param {HTMLInputElement|HTMLButtonElement} submitBtn
   * @param {HTMLInputElement} emailInput
   */
  function attachModalFlow(settings, submitBtn, emailInput) {
    const s = settings.emailVerification;
    const dialog = getOrCreateDialog();
    const titleEl = dialog.querySelector('.email-verification-dialog__title');
    const hintEl = dialog.querySelector('.email-verification-dialog__hint');
    const labelTextEl = dialog.querySelector('.email-verification-dialog__label-text');
    const inputEl = dialog.querySelector('.email-verification-dialog__input');
    const errEl = dialog.querySelector('.email-verification-dialog__error');
    const bannerEl = dialog.querySelector('.email-verification-dialog__banner');
    const resendBtn = dialog.querySelector('.email-verification-dialog__btn-resend');
    const confirmBtn = dialog.querySelector('.email-verification-dialog__btn-confirm');
    const closeBtn = dialog.querySelector('.email-verification-dialog__close');

    titleEl.textContent = Drupal.t('Enter verification code');
    hintEl.textContent = Drupal.t(
      'To submit this form, enter the code we sent to your email address.',
    );
    labelTextEl.textContent = Drupal.t('Verification code');
    resendBtn.textContent = Drupal.t('Resend code');
    confirmBtn.textContent = Drupal.t('Confirm and submit');
    closeBtn.setAttribute('aria-label', Drupal.t('Close'));

    let allowNextSubmit = false;

    function showError(msg) {
      errEl.textContent = msg;
      errEl.hidden = false;
    }

    function clearError() {
      errEl.textContent = '';
      errEl.hidden = true;
    }

    function showBanner(msg, variant) {
      bannerEl.textContent = msg;
      bannerEl.hidden = !msg;
      bannerEl.classList.remove(
        'email-verification-dialog__banner--error',
        'email-verification-dialog__banner--success',
      );
      if (msg && variant) {
        bannerEl.classList.add(`email-verification-dialog__banner--${variant}`);
      }
    }

    function clearBanner() {
      showBanner('', '');
    }

    function jsonHeaders() {
      return {
        'Content-Type': 'application/json',
        'X-CSRF-Token': s.csrfToken,
      };
    }

    function sendOtp() {
      const email = emailInput.value.trim();
      if (!email) {
        emailInput.reportValidity();
        return Promise.reject(new Error('no-email'));
      }
      return fetch(Drupal.url('email-verification/send-otp'), {
        method: 'POST',
        credentials: 'same-origin',
        headers: jsonHeaders(),
        body: JSON.stringify({
          webform_id: s.webformId,
          email,
        }),
      }).then((response) => {
        if (!response.ok) {
          return response
            .json()
            .catch(() => ({}))
            .then((body) => {
              throw new Error(
                (body && body.message) || Drupal.t('Could not send verification email.'),
              );
            });
        }
        return response.json();
      });
    }

    function verifyOtp(code) {
      const email = emailInput.value.trim();
      return fetch(Drupal.url('email-verification/verify-otp'), {
        method: 'POST',
        credentials: 'same-origin',
        headers: jsonHeaders(),
        body: JSON.stringify({
          webform_id: s.webformId,
          email,
          code: String(code).trim(),
        }),
      }).then((response) => {
        if (!response.ok) {
          return response
            .json()
            .catch(() => ({}))
            .then((body) => {
              throw new Error(
                (body && body.message) || Drupal.t('Invalid or expired code.'),
              );
            });
        }
        return response.json();
      });
    }

    function openDialog() {
      clearError();
      clearBanner();
      inputEl.value = '';
      if (typeof dialog.showModal === 'function') {
        dialog.showModal();
      } else {
        dialog.setAttribute('open', '');
      }
      window.setTimeout(() => {
        inputEl.focus();
      }, 50);
    }

    function closeDialog() {
      if (typeof dialog.close === 'function') {
        dialog.close();
      } else {
        dialog.removeAttribute('open');
      }
    }

    function onFinalSubmitClick(event) {
      if (allowNextSubmit) {
        return;
      }
      const email = emailInput.value.trim();
      if (!email) {
        return;
      }
      if (sessionStorage.getItem(storageKey(s.webformId, email)) === '1') {
        return;
      }
      event.preventDefault();
      event.stopImmediatePropagation();
      if (typeof event.stopPropagation === 'function') {
        event.stopPropagation();
      }

      confirmBtn.disabled = true;
      resendBtn.disabled = true;
      sendOtp()
        .then((data) => {
          if (data.status === 'already_verified') {
            sessionStorage.setItem(storageKey(s.webformId, email), '1');
            allowNextSubmit = true;
            submitBtn.click();
            allowNextSubmit = false;
            return;
          }
          openDialog();
        })
        .catch((e) => {
          if (e.message !== 'no-email') {
            openDialog();
            showBanner(
              e.message || Drupal.t('Something went wrong.'),
              'error',
            );
          }
        })
        .finally(() => {
          confirmBtn.disabled = false;
          resendBtn.disabled = false;
        });
    }

    confirmBtn.addEventListener('click', () => {
      clearError();
      clearBanner();
      const code = inputEl.value;
      if (!code) {
        showError(Drupal.t('Please enter the code from your email.'));
        return;
      }
      confirmBtn.disabled = true;
      verifyOtp(code)
        .then(() => {
          const email = emailInput.value.trim();
          sessionStorage.setItem(storageKey(s.webformId, email), '1');
          allowNextSubmit = true;
          closeDialog();
          submitBtn.click();
          allowNextSubmit = false;
        })
        .catch((e) => {
          showError(e.message);
        })
        .finally(() => {
          confirmBtn.disabled = false;
        });
    });

    closeBtn.addEventListener('click', () => {
      closeDialog();
    });

    resendBtn.addEventListener('click', () => {
      clearError();
      clearBanner();
      resendBtn.disabled = true;
      sendOtp()
        .then(() => {
          showBanner(Drupal.t('A new code was sent to your email.'), 'success');
        })
        .catch((e) => {
          if (e.message !== 'no-email') {
            showBanner(e.message || Drupal.t('Could not resend.'), 'error');
          }
        })
        .finally(() => {
          resendBtn.disabled = false;
        });
    });

    submitBtn.addEventListener('click', onFinalSubmitClick, true);
  }

  Drupal.behaviors.emailVerificationModal = {
    attach(context, settings) {
      if (!settings.emailVerification) {
        return;
      }
      const forms = once(
        'email-verification-modal',
        'form[data-email-verification]',
        context,
      );
      forms.forEach((form) => {
        const submitBtn = form.querySelector(
          'input.webform-button--submit, button.webform-button--submit',
        );
        const emailName = settings.emailVerification.emailElementName || 'email';
        const emailInput =
          form.querySelector(`[name="${emailName}"]`) ||
          form.querySelector(`[name$="[${emailName}]"]`);
        if (!submitBtn || !emailInput) {
          return;
        }
        attachModalFlow(settings, submitBtn, emailInput);
      });
    },
  };
})(Drupal, once);
