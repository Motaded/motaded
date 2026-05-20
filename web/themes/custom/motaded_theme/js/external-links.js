(function (Drupal, once) {
  function isExternalHttpLink(urlString, currentHost) {
    try {
      const url = new URL(urlString, window.location.href);
      return (url.protocol === 'http:' || url.protocol === 'https:') && url.host && url.host !== currentHost;
    } catch (e) {
      return false;
    }
  }

  Drupal.behaviors.motadedExternalLinks = {
    attach(context) {
      const currentHost = window.location.host;
      once('motadedExternalLinks', 'a[href]', context).forEach((a) => {
        const href = a.getAttribute('href') || '';
        if (!href || href.startsWith('#') || href.startsWith('mailto:') || href.startsWith('tel:')) {
          return;
        }
        if (!isExternalHttpLink(href, currentHost)) {
          return;
        }

        a.setAttribute('target', '_blank');
        const existingRel = (a.getAttribute('rel') || '').split(/\s+/).filter(Boolean);
        for (const token of ['noopener', 'noreferrer']) {
          if (!existingRel.includes(token)) existingRel.push(token);
        }
        a.setAttribute('rel', existingRel.join(' '));
      });
    },
  };

  // Open file links (pdf/doc/etc) in a new tab.
  // We intentionally include internal /sites/default/files/... URLs.
  Drupal.behaviors.motadedFileLinksBlank = {
    attach(context) {
      const selector = [
        'a[href^="/sites/default/files/"]',
        'a[href$=".pdf"]',
        'a[href$=".doc"]',
        'a[href$=".docx"]',
        'a[href$=".ppt"]',
        'a[href$=".pptx"]',
        'a[href$=".xls"]',
        'a[href$=".xlsx"]',
      ].join(',');

      once('motadedFileLinksBlank', selector, context).forEach((a) => {
        const href = a.getAttribute('href') || '';
        if (!href || href.startsWith('#') || href.startsWith('mailto:') || href.startsWith('tel:')) {
          return;
        }
        a.setAttribute('target', '_blank');
        const existingRel = (a.getAttribute('rel') || '').split(/\s+/).filter(Boolean);
        for (const token of ['noopener', 'noreferrer']) {
          if (!existingRel.includes(token)) existingRel.push(token);
        }
        a.setAttribute('rel', existingRel.join(' '));
      });
    },
  };
})(Drupal, once);

