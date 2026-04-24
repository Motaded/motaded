/*
  Theme: Muin (Custom Licensed Version)
  © Banafsijy.com – Single Project License – Do Not Redistribute
 */

(function (Drupal, once) {
  Drupal.behaviors.motadedServiceBodyReadmore = {
    attach(context) {
      once(
        'motaded-service-body-readmore',
        '[data-drupal-selector="service-body-readmore"]',
        context,
      ).forEach((wrapper) => {
        const btn = wrapper.querySelector('.service-page__body-readmore-btn');
        if (!btn) {
          return;
        }
        const readMore = btn.getAttribute('data-label-expand') || Drupal.t('Read more');
        const readLess = btn.getAttribute('data-label-collapse') || Drupal.t('Read less');

        btn.addEventListener('click', () => {
          const expanded = wrapper.classList.toggle('is-expanded');
          btn.setAttribute('aria-expanded', expanded ? 'true' : 'false');
          btn.textContent = expanded ? readLess : readMore;
        });
      });
    },
  };
})(Drupal, once);
