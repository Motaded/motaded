(function (drupalSettings, window) {
  'use strict';

  const settings =
    (drupalSettings && drupalSettings.muin && drupalSettings.muin.topbar) || {};

  const clamp = (value, min, max) => Math.min(Math.max(value, min), max);

  window.topbar = function topbar() {
    return {
      scale: 0,
      date: settings.date || '',
      increaseFontTip: settings.increaseFontTip || '',
      increaseFontLabel: settings.increaseFontLabel || '',
      decreaseFontTip: settings.decreaseFontTip || '',
      decreaseFontLabel: settings.decreaseFontLabel || '',
      invertColorsTip: settings.invertColorsTip || '',
      invertColorsLabel: settings.invertColorsLabel || '',
      calendarAlt: settings.calendarAlt || '',

      loadData() {
        if (!this.date) {
          try {
            const langcode =
              settings.langcode || document.documentElement.lang || 'en';
            const formatter = new Intl.DateTimeFormat(langcode, {
              weekday: 'long',
              month: 'long',
              day: 'numeric',
              year: 'numeric',
            });
            this.date = formatter.format(new Date());
          }
          catch (error) {
            if (window.console && typeof window.console.error === 'function') {
              window.console.error('Unable to format topbar date', error);
            }
          }
        }
      },

      adjustScale(step) {
        this.scale = clamp(this.scale + step, -3, 4);
        const base = 100 + this.scale * 6;
        document.documentElement.style.fontSize = base + '%';
      },

      increaseFont() {
        this.adjustScale(1);
      },

      decreaseFont() {
        this.adjustScale(-1);
      },

      toggleInvert() {
        document.documentElement.classList.toggle('invert');
      },
    };
  };
})(typeof drupalSettings === 'undefined' ? {} : drupalSettings, window);
