/*
  Partners carousel — Swiper loaded only when the block is near the viewport.
*/

(function (Drupal, drupalSettings, once) {
  const SWIPER_CSS =
    'https://cdn.jsdelivr.net/npm/swiper@11/swiper-bundle.min.css';
  const SWIPER_JS =
    'https://cdn.jsdelivr.net/npm/swiper@11/swiper-bundle.min.js';

  let swiperPromise = null;

  function ensureSwiper() {
    if (typeof Swiper !== 'undefined') {
      return Promise.resolve();
    }
    if (!swiperPromise) {
      swiperPromise = new Promise((resolve, reject) => {
        const link = document.createElement('link');
        link.rel = 'stylesheet';
        link.href = SWIPER_CSS;
        document.head.appendChild(link);

        const script = document.createElement('script');
        script.src = SWIPER_JS;
        script.async = true;
        script.onload = () => resolve();
        script.onerror = () => reject(new Error('Swiper failed to load'));
        document.body.appendChild(script);
      });
    }
    return swiperPromise;
  }

  function initPartnersCarousel(element) {
    if (typeof Swiper === 'undefined') return;

    new Swiper(element, {
      slidesPerView: 2,
      spaceBetween: 12,
      loop: true,
      autoplay: {
        delay: 3500,
        disableOnInteraction: false,
      },
      pagination: {
        el: element.querySelector('.swiper-pagination'),
        clickable: true,
      },
      grabCursor: true,
      touchEventsTarget: 'container',
      breakpoints: {
        640: { slidesPerView: 3 },
        768: { slidesPerView: 4 },
        1024: { slidesPerView: 5 },
        1280: { slidesPerView: 6 },
      },
    });
  }

  Drupal.behaviors.partnersCarousel = {
    attach(context) {
      once('partners-carousel-io', '.partners-swiper', context).forEach(
        (element) => {
          const observer = new IntersectionObserver(
            (entries, io) => {
              entries.forEach((entry) => {
                if (!entry.isIntersecting) return;
                io.disconnect();
                ensureSwiper()
                  .then(() => {
                    initPartnersCarousel(element);
                  })
                  .catch(() => {
                    // Swiper unavailable; carousel stays static.
                  });
              });
            },
            { rootMargin: '140px 0px', threshold: 0.01 },
          );
          observer.observe(element);
        },
      );
    },
  };
})(Drupal, drupalSettings, once);
