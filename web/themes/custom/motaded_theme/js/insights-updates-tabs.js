(function (Drupal, once) {
  'use strict';

  Drupal.behaviors.insightsUpdatesBlogNewsTabs = {
    attach: function (context) {
      once('iu-bn-tabs', '.insights-updates[data-iu-tabs]', context).forEach(function (root) {
        var tabs = root.querySelectorAll('.iu-bn__tab');
        var cells = root.querySelectorAll('.iu-bn__cell');
        var footerBlog = root.querySelector('.iu-bn__cta[data-iu-footer-link="blog"]');
        var footerNews = root.querySelector('.iu-bn__cta[data-iu-footer-link="news"]');

        function updateFooterLinks(value) {
          if (!footerBlog && !footerNews) {
            return;
          }
          var showBlog = value === 'all' || value === 'blog';
          var showNews = value === 'all' || value === 'news';
          if (footerBlog) {
            footerBlog.toggleAttribute('hidden', !showBlog);
          }
          if (footerNews) {
            footerNews.toggleAttribute('hidden', !showNews);
          }
        }

        function setFilter(value) {
          root.setAttribute('data-iu-active-filter', value);
          tabs.forEach(function (btn) {
            var active = btn.getAttribute('data-iu-tab') === value;
            btn.classList.toggle('is-active', active);
            btn.setAttribute('aria-selected', active ? 'true' : 'false');
          });
          cells.forEach(function (cell) {
            var kind = cell.getAttribute('data-iu-kind') || '';
            var show =
              value === 'all' ||
              (value === 'blog' && kind === 'blog') ||
              (value === 'news' && kind === 'news');
            cell.toggleAttribute('hidden', !show);
          });
          updateFooterLinks(value);
        }

        tabs.forEach(function (btn) {
          btn.addEventListener('click', function () {
            setFilter(btn.getAttribute('data-iu-tab') || 'all');
          });
        });

        setFilter(root.getAttribute('data-iu-active-filter') || 'all');
      });
    },
  };
})(Drupal, once);
