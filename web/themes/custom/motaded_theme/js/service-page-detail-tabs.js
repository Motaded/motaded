/*
 * Service page: underline tab nav reflects scroll position (mockup behaviour).
 */
(function (Drupal, once) {
  Drupal.behaviors.motadedServicePageDetailTabs = {
    attach(context) {
      once(
        'motaded-service-page-detail-tabs',
        '.node--service-page .service-page__section-tabs',
        context,
      ).forEach((nav) => {
        const tabs = Array.from(nav.querySelectorAll('a.service-page__section-tab'));
        if (!tabs.length) {
          return;
        }

        const idToTab = new Map();
        const sections = [];
        tabs.forEach((a) => {
          const href = a.getAttribute('href');
          if (!href || href.charAt(0) !== '#') {
            return;
          }
          const id = href.slice(1);
          const el = document.getElementById(id);
          if (el) {
            idToTab.set(id, a);
            sections.push(el);
          }
        });

        if (!sections.length) {
          return;
        }

        const setActive = (active) => {
          tabs.forEach((t) => t.classList.remove('is-active'));
          if (active) {
            active.classList.add('is-active');
          }
        };

        const io = new IntersectionObserver(
          (entries) => {
            const visible = entries.filter((e) => e.isIntersecting);
            if (!visible.length) {
              return;
            }
            visible.sort((a, b) => b.intersectionRatio - a.intersectionRatio);
            const id = visible[0].target.id;
            const tab = idToTab.get(id);
            if (tab) {
              setActive(tab);
            }
          },
          {
            root: null,
            rootMargin: '-20% 0px -55% 0px',
            threshold: [0, 0.1, 0.25, 0.5, 0.75, 1],
          },
        );

        sections.forEach((sec) => io.observe(sec));

        const hash = window.location.hash && window.location.hash.slice(1);
        if (hash && idToTab.has(hash)) {
          setActive(idToTab.get(hash));
        }
        else {
          setActive(tabs[0]);
        }

        tabs.forEach((a) => {
          a.addEventListener('click', () => {
            const href = a.getAttribute('href');
            if (href && href.charAt(0) === '#') {
              window.setTimeout(() => setActive(a), 0);
            }
          });
        });
      });
    },
  };
})(Drupal, once);
