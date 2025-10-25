/**
 * @file
 * Global utilities.
 *
 */
/* eslint-disable */
(function (Drupal) {
  Drupal.behaviors.bootstrap_barrio3 = {
    attach(context, settings) {
      once("lang-switcher", ".language-switcher-language-url", context).forEach(function (item,i) {
        const active_link = item.querySelector('.language-link.is-active');
        if (active_link) {
          item.querySelector('.muin-language-switcher__label').innerHTML = active_link.textContent.trim();
          item.querySelector('.sr-only.current-lang').innerHTML = Drupal.t(`Select language, current: ${active_link.textContent.trim()}`);
        }
      });
    },
  };
})(Drupal);
/* eslint-enable */