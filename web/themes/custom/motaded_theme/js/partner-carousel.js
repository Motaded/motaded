/*
 * Partners carousel — tablet/mobile (<1024px). Desktop uses CSS marquee.
 */
(function (Drupal, once) {
  'use strict';

  const MOBILE_CAROUSEL_MQ = '(max-width: 1023px)';

  function isMobileCarouselViewport() {
    return window.matchMedia(MOBILE_CAROUSEL_MQ).matches;
  }

  function initPartnersCarousel(element) {
    if (typeof Swiper === 'undefined' || !isMobileCarouselViewport()) {
      return;
    }
    if (element.swiper) {
      element.swiper.update();
      return;
    }

    const slideCount = element.querySelectorAll('.swiper-slide').length;
    if (slideCount < 1) {
      return;
    }

    const prefersReducedMotion = window.matchMedia(
      '(prefers-reduced-motion: reduce)',
    ).matches;

    new Swiper(element, {
      slidesPerView: 2.15,
      spaceBetween: 12,
      loop: slideCount > 2,
      watchOverflow: true,
      observer: true,
      observeParents: true,
      autoplay: prefersReducedMotion
        ? false
        : {
            delay: 3500,
            disableOnInteraction: false,
            pauseOnMouseEnter: true,
          },
      pagination: {
        el: element.querySelector('.swiper-pagination'),
        clickable: true,
      },
      grabCursor: true,
      breakpoints: {
        480: { slidesPerView: 2.5 },
        640: { slidesPerView: 3 },
        768: { slidesPerView: 3.5 },
      },
    });
  }

  function destroyPartnersCarousel(element) {
    if (element.swiper) {
      element.swiper.destroy(true, true);
    }
  }

  function syncPartnersCarousel(element) {
    if (isMobileCarouselViewport()) {
      initPartnersCarousel(element);
    } else {
      destroyPartnersCarousel(element);
    }
  }

  Drupal.behaviors.partnersCarousel = {
    attach(context) {
      once('partners-carousel', '.partners-swiper', context).forEach(
        (element) => {
          const run = () => syncPartnersCarousel(element);

          run();

          if (typeof window.requestAnimationFrame === 'function') {
            window.requestAnimationFrame(run);
          }

          window
            .matchMedia(MOBILE_CAROUSEL_MQ)
            .addEventListener('change', run);
        },
      );
    },
  };
})(Drupal, once);
