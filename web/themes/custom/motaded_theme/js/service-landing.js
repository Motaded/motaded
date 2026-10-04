(function (Drupal, once) {
  Drupal.behaviors.motadedServiceFaq = {
    attach: function attach(context) {
      const items = once('svc-faq', '.landing-page--service #faq details', context);
      items.forEach((item) => {
        item.addEventListener('toggle', () => {
          if (!item.open) {
            return;
          }
          items.forEach((other) => {
            if (other !== item) {
              other.open = false;
            }
          });
        });
      });
    },
  };

  Drupal.behaviors.motadedServiceTabs = {
    attach: function attach(context) {
      once('svc-tabs', '[data-svc-tabs]', context).forEach((root) => {
        const tabs = Array.from(root.querySelectorAll('[role="tab"]'));
        const panels = Array.from(root.querySelectorAll('[role="tabpanel"]'));
        if (!tabs.length) {
          return;
        }

        const activate = (index, focus) => {
          tabs.forEach((tab, i) => {
            const selected = i === index;
            tab.classList.toggle('is-active', selected);
            tab.setAttribute('aria-selected', selected ? 'true' : 'false');
            tab.tabIndex = selected ? 0 : -1;
            if (panels[i]) {
              panels[i].hidden = !selected;
              panels[i].classList.toggle('is-active', selected);
            }
          });
          if (focus) {
            tabs[index].focus();
          }
        };

        tabs.forEach((tab, index) => {
          tab.addEventListener('click', () => {
            activate(index, false);
          });
          tab.addEventListener('keydown', (event) => {
            const next = event.key === 'ArrowRight' || event.key === 'ArrowDown';
            const prev = event.key === 'ArrowLeft' || event.key === 'ArrowUp';
            if (next || prev) {
              event.preventDefault();
              activate((index + (next ? 1 : -1) + tabs.length) % tabs.length, true);
            }
          });
        });
      });
    },
  };

  Drupal.behaviors.motadedAccountingListAjax = {
    attach: function attach(context) {
      once(
        'acc-svc-ajax-submit',
        '#views-exposed-form-services-block-accounting',
        context,
      ).forEach((form) => {
        form.addEventListener(
          'submit',
          (event) => {
            const submitter = event.submitter;
            if (
              submitter &&
              (submitter.getAttribute('data-drupal-selector') === 'edit-reset' ||
                submitter.name === 'reset')
            ) {
              return;
            }
            const apply = form.querySelector(
              '.form-actions input[type="submit"]:not([data-drupal-selector="edit-reset"])',
            );
            if (!apply || !Drupal.ajax || !Drupal.ajax.instances) {
              return;
            }
            const bound = Drupal.ajax.instances.some(
              (instance) => instance && instance.element === apply,
            );
            if (!bound) {
              return;
            }
            event.preventDefault();
            apply.click();
          },
          true,
        );
      });
    },
  };

  Drupal.behaviors.motadedServiceFormDock = {
    attach: function attach(context) {
      once('svc-form-dock', '.landing-page--service-sidebar', context).forEach((root) => {
        const form = root.querySelector('#assessment');
        const dock = root.querySelector('.svc-form-dock');
        if (!form || !dock || typeof IntersectionObserver === 'undefined') {
          return;
        }
        const observer = new IntersectionObserver(
          (entries) => {
            const visible = entries.some((entry) => entry.isIntersecting);
            dock.hidden = visible;
          },
          { threshold: 0.12 },
        );
        observer.observe(form);
      });
    },
  };

  Drupal.behaviors.motadedServiceProcess = {
    attach: function attach(context) {
      if (typeof Swiper === 'undefined') {
        return;
      }
      once('svc-process', '.customs-process-swiper', context).forEach((element) => {
        if (element.swiper) {
          return;
        }
        new Swiper(element, {
          slidesPerView: 1,
          spaceBetween: 20,
          autoHeight: true,
          watchOverflow: true,
          observer: true,
          observeParents: true,
          navigation: {
            nextEl: element.querySelector('.swiper-button-next'),
            prevEl: element.querySelector('.swiper-button-prev'),
          },
          pagination: {
            el: element.querySelector('.swiper-pagination'),
            clickable: true,
            renderBullet: function renderBullet(index, className) {
              const n = index + 1 < 10 ? '0' + (index + 1) : String(index + 1);
              return '<button type="button" class="' + className + '">' + n + '</button>';
            },
          },
        });
      });
    },
  };
})(Drupal, once);
