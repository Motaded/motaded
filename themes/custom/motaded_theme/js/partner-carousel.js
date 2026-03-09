/*
  Partners carousel — Swiper.js
  Smooth transitions, swipe support, autoplay, infinite loop
*/

(function (Drupal, drupalSettings, once) {
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
      once('partners-carousel', '.partners-swiper', context).forEach(initPartnersCarousel);
    },
  };
})(Drupal, drupalSettings, once);
