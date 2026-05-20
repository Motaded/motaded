(function (Drupal, once) {
  'use strict';

  function applyMode(root, mode) {
    if (!root) return;
    const grid = root.querySelector('[data-platforms-dir-grid]');
    if (!grid) return;

    const isList = mode === 'list';
    grid.dataset.platformsDirMode = isList ? 'list' : 'grid';

    const buttons = root.querySelectorAll('[data-platforms-dir-mode]');
    buttons.forEach((btn) => {
      const active = btn.getAttribute('data-platforms-dir-mode') === mode;
      btn.classList.toggle('is-active', active);
      btn.setAttribute('aria-pressed', active ? 'true' : 'false');
    });
  }

  Drupal.behaviors.motadedPlatformsDirectory = {
    attach(context) {
      const roots = once('motaded-platforms-directory-root', '.platforms-directory.platforms-directory-page', context);
      roots.forEach((root) => {
        applyMode(root, 'grid');
      });

      const btns = once('motaded-platforms-directory-toggle', '[data-platforms-dir-mode]', context);
      btns.forEach((btn) => {
        btn.addEventListener('click', () => {
          const root = btn.closest('.platforms-directory.platforms-directory-page');
          const mode = btn.getAttribute('data-platforms-dir-mode') || 'grid';
          applyMode(root, mode);
        });
      });
    },
  };
})(Drupal, once);

