(function (Drupal, once) {
  'use strict';

  function toggleActiveState(link, isActive) {
    link.classList.toggle('bg-primary-50', isActive);
    link.classList.toggle('text-primary-700', isActive);
    link.classList.toggle('ring-1', isActive);
    link.classList.toggle('ring-primary-600/20', isActive);
  }

  Drupal.behaviors.muinArticle = {
    attach(context) {
      const wrappers = once('muin-article', '[data-muin-article]', context);
      wrappers.forEach((root) => {
        const tocPanel = root.querySelector('[data-article-toc-panel]');
        const toggle = root.querySelector('[data-article-toc-toggle]');
        const toggleIcon = root.querySelector('[data-article-toc-icon]');
        const tocLinks = Array.from(root.querySelectorAll('[data-heading-target]'));
        const bodyContainer = root.querySelector('[data-article-body]');
        const contentContainer = bodyContainer || root.querySelector('[data-article-content]');

        const mediaQuery = window.matchMedia('(min-width: 768px)');
        let expanded = mediaQuery.matches;

        const applyVisibility = () => {
          const shouldShow = expanded || mediaQuery.matches;
          if (tocPanel) {
            if (shouldShow) {
              tocPanel.classList.remove('hidden');
            }
            else {
              tocPanel.classList.add('hidden');
            }
          }

          if (toggle) {
            toggle.setAttribute('aria-expanded', shouldShow ? 'true' : 'false');
          }

          if (toggleIcon) {
            toggleIcon.classList.toggle('rotate-180', shouldShow && !mediaQuery.matches);
          }
        };

        const handleMediaChange = () => {
          expanded = mediaQuery.matches ? true : false;
          applyVisibility();
        };

        if (toggle && tocPanel) {
          toggle.addEventListener('click', () => {
            expanded = !expanded;
            applyVisibility();
          });
        }

        if (typeof mediaQuery.addEventListener === 'function') {
          mediaQuery.addEventListener('change', handleMediaChange);
        }
        else if (typeof mediaQuery.addListener === 'function') {
          mediaQuery.addListener(handleMediaChange);
        }

        applyVisibility();

        if (!tocLinks.length || !contentContainer) {
          return;
        }

        const headingsScope = bodyContainer || contentContainer;
        const headings = Array.from(headingsScope.querySelectorAll('h2, h3'));
        if (!headings.length) {
          return;
        }

        const sortedLinks = tocLinks
          .slice()
          .sort((a, b) => Number(a.dataset.headingOrder || 0) - Number(b.dataset.headingOrder || 0));

        let headingIndex = 0;
        sortedLinks.forEach((link) => {
          const level = parseInt(link.dataset.headingLevel || '2', 10);
          const targetId = link.dataset.headingTarget;
          for (let index = headingIndex; index < headings.length; index += 1) {
            const heading = headings[index];
            const headingLevel = parseInt(heading.tagName.substring(1), 10);
            if (headingLevel === level) {
              heading.id = targetId;
              headingIndex = index + 1;
              break;
            }
          }
        });

        const offset = parseInt(root.getAttribute('data-article-offset') || '96', 10);
        const setActive = (id) => {
          if (!id) {
            return;
          }

          tocLinks.forEach((link) => {
            const isActive = link.dataset.headingTarget === id;
            toggleActiveState(link, isActive);
          });
        };

        sortedLinks.forEach((link) => {
          link.addEventListener('click', (event) => {
            const targetId = link.dataset.headingTarget;
            const target = targetId ? document.getElementById(targetId) : null;
            if (!target) {
              return;
            }

            event.preventDefault();
            const destination = target.getBoundingClientRect().top + window.scrollY - offset;
            window.scrollTo({ top: destination, behavior: 'smooth' });
            if (typeof history.replaceState === 'function') {
              history.replaceState(null, '', `#${targetId}`);
            }
            setActive(targetId);
            if (!mediaQuery.matches) {
              expanded = false;
              applyVisibility();
            }
          });
        });

        const observer = new IntersectionObserver(
          (entries) => {
            entries.forEach((entry) => {
              if (entry.isIntersecting) {
                setActive(entry.target.id);
              }
            });
          },
          { rootMargin: '-30% 0px -60% 0px', threshold: 0.1 },
        );

        headings.forEach((heading) => observer.observe(heading));

        if (headings.length) {
          setActive(headings[0].id);
        }

        if (window.location.hash) {
          const hashId = decodeURIComponent(window.location.hash.substring(1));
          setActive(hashId);
        }
      });
    },
  };
})(Drupal, once);
