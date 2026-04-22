(function () {
  function emitEvent(target, type) {
    const event = new Event(type, { bubbles: true });
    target.dispatchEvent(event);
  }

  function clearFilters(button) {
    const form = button.closest('form');

    if (!form) {
      return;
    }

    const textSelectors = [
      'input[type="text"]',
      'input[type="search"]',
      'input[type="number"]',
      'input[type="email"]',
      'input[type="url"]',
      'textarea',
    ];

    form.reset();

    form.querySelectorAll(textSelectors.join(', ')).forEach((field) => {
      if (field.hasAttribute('readonly') || field.hasAttribute('disabled')) {
        return;
      }

      field.value = '';
      emitEvent(field, 'input');
      emitEvent(field, 'change');
    });

    form.querySelectorAll('select').forEach((select) => {
      if (select.hasAttribute('disabled')) {
        return;
      }

      if (select.options.length > 0) {
        select.selectedIndex = 0;
      }

      emitEvent(select, 'change');
    });

    const pageField = form.querySelector('input[name="page"]');

    if (pageField) {
      pageField.value = '0';
    }

    const submitButton = form.querySelector('button[type="submit"], input[type="submit"]');

    if (typeof form.requestSubmit === 'function' && submitButton instanceof HTMLElement) {
      form.requestSubmit(submitButton);
    } else {
      form.submit();
    }
  }

  const CLEAR_BUTTON_SELECTOR = '[data-articles-filter-clear], [data-services-filter-clear]';

  document.addEventListener('click', (event) => {
    const button = event.target.closest(CLEAR_BUTTON_SELECTOR);

    if (!button) {
      return;
    }

    event.preventDefault();
    if (typeof button.blur === 'function') {
      button.blur();
    }
    clearFilters(button);
  });
})();
