(function (Drupal, once) {
  'use strict';

  const focusableSelector = [
    'a[href]:not([tabindex="-1"])',
    'area[href]:not([tabindex="-1"])',
    'input:not([disabled]):not([tabindex="-1"])',
    'select:not([disabled]):not([tabindex="-1"])',
    'textarea:not([disabled]):not([tabindex="-1"])',
    'button:not([disabled]):not([tabindex="-1"])',
    'iframe',
    'audio[controls]',
    'video[controls]',
    '[contenteditable="true"]',
    '[tabindex]:not([tabindex="-1"])'
  ].join(',');

  function getFocusableElements(container, includeTrigger) {
    if (!container) {
      return [];
    }
    const elements = Array.from(container.querySelectorAll(focusableSelector));
    if (includeTrigger && includeTrigger instanceof HTMLElement) {
      elements.unshift(includeTrigger);
    }
    return elements.filter((element, index, array) => {
      if (!(element instanceof HTMLElement)) {
        return false;
      }
      const style = window.getComputedStyle(element);
      const hiddenByStyles = style.visibility === 'hidden' || style.display === 'none';
      const hiddenByOffset = element.offsetParent === null && style.position !== 'fixed';
      if (hiddenByStyles || hiddenByOffset) {
        return false;
      }
      return array.indexOf(element) === index;
    });
  }

  function trapFocus(event, focusable, returnFocus) {
    if (event.key !== 'Tab' || !focusable.length) {
      return;
    }

    const first = focusable[0];
    const last = focusable[focusable.length - 1];
    const active = document.activeElement;

    if (!event.shiftKey && active === last) {
      event.preventDefault();
      first.focus();
    } else if (event.shiftKey && active === first) {
      event.preventDefault();
      last.focus();
    }

    if (focusable.length === 1 && returnFocus && typeof returnFocus.focus === 'function' && document.activeElement === focusable[0]) {
      event.preventDefault();
      returnFocus.focus();
    }
  }

  function initDesktop(navContainer, siteHeader) {
    const triggers = Array.from(navContainer.querySelectorAll('[data-header-trigger]'));
    if (!triggers.length) {
      return;
    }

    let activeTrigger = null;
    let activePanel = null;
    let activeItem = null;

    function closeAll() {
      if (activeTrigger) {
        activeTrigger.setAttribute('aria-expanded', 'false');
        activeTrigger.classList.remove('is-open');
      }
      if (activePanel) {
        activePanel.hidden = true;
        activePanel.classList.remove('is-open');
      }
      if (activeItem) {
        activeItem.classList.remove('is-open');
      }
      activeTrigger = null;
      activePanel = null;
      activeItem = null;
      navContainer.classList.remove('is-open');
      if (siteHeader) {
        siteHeader.classList.remove('has-mega-open');
      }
    }

    function openPanel(trigger) {
      if (!trigger) {
        return;
      }
      if (activeTrigger === trigger) {
        closeAll();
        return;
      }
      closeAll();
      const panelId = trigger.getAttribute('data-panel');
      if (!panelId) {
        return;
      }
      const panel = navContainer.querySelector('#' + CSS.escape(panelId));
      if (!panel) {
        return;
      }
      const item = trigger.closest('.header-navigation__item');
      trigger.setAttribute('aria-expanded', 'true');
      trigger.classList.add('is-open');
      panel.hidden = false;
      panel.classList.add('is-open');
      if (item) {
        item.classList.add('is-open');
      }
      activeTrigger = trigger;
      activePanel = panel;
      activeItem = item || null;
      navContainer.classList.add('is-open');
      if (siteHeader) {
        siteHeader.classList.add('has-mega-open');
      }
    }

    function onTriggerClick(event) {
      openPanel(event.currentTarget);
    }

    function onTriggerEnter(event) {
      if (window.matchMedia('(hover: hover)').matches) {
        openPanel(event.currentTarget);
      }
    }

    function onTriggerFocus(event) {
      openPanel(event.currentTarget);
    }

    function handleKeydown(event) {
      if (event.key === 'Escape') {
        if (activeTrigger) {
          closeAll();
          activeTrigger.focus();
        } else {
          closeAll();
        }
        return;
      }
      if (event.key === 'Tab' && activePanel) {
        const focusable = getFocusableElements(activePanel, activeTrigger);
        trapFocus(event, focusable, activeTrigger);
      }
    }

    function handleOutside(event) {
      if (!navContainer.contains(event.target) && activePanel && !event.target.closest('[data-header-mobile-toggle]')) {
        closeAll();
      }
    }

    triggers.forEach((trigger) => {
      trigger.addEventListener('click', onTriggerClick);
      trigger.addEventListener('mouseenter', onTriggerEnter);
      trigger.addEventListener('focus', onTriggerFocus);
    });

    document.addEventListener('click', handleOutside);
    navContainer.addEventListener('keydown', handleKeydown);
    navContainer.addEventListener('focusout', (event) => {
      if (!navContainer.contains(event.relatedTarget)) {
        closeAll();
      }
    });
  }

  function initMobile(navContainer, siteHeader) {
    const mobileDrawer = navContainer.querySelector('[data-header-mobile]');
    const mobileToggle = siteHeader ? siteHeader.querySelector('[data-header-mobile-toggle]') : null;
    if (!mobileDrawer || !mobileToggle) {
      return;
    }

    if (mobileDrawer.id && mobileToggle.getAttribute('aria-controls') !== mobileDrawer.id) {
      mobileToggle.setAttribute('aria-controls', mobileDrawer.id);
    }

    let isOpen = false;

    function closeMobile() {
      if (!isOpen) {
        return;
      }
      isOpen = false;
      mobileToggle.setAttribute('aria-expanded', 'false');
      mobileDrawer.hidden = true;
      mobileDrawer.classList.remove('is-open');
      if (siteHeader) {
        siteHeader.classList.remove('site-header--mobile-open');
      }
      document.body.classList.remove('has-header-mobile-open');
      document.removeEventListener('keydown', onKeydown);
      document.removeEventListener('click', onOutsideClick, true);
    }

    function openMobile() {
      if (isOpen) {
        return;
      }
      isOpen = true;
      mobileToggle.setAttribute('aria-expanded', 'true');
      mobileDrawer.hidden = false;
      mobileDrawer.classList.add('is-open');
      if (siteHeader) {
        siteHeader.classList.add('site-header--mobile-open');
      }
      document.body.classList.add('has-header-mobile-open');
      const focusable = getFocusableElements(mobileDrawer);
      if (focusable.length) {
        focusable[0].focus();
      }
      document.addEventListener('keydown', onKeydown);
      document.addEventListener('click', onOutsideClick, true);
    }

    function toggleMobile() {
      if (isOpen) {
        closeMobile();
        mobileToggle.focus();
      } else {
        openMobile();
      }
    }

    function onKeydown(event) {
      if (event.key === 'Escape') {
        closeMobile();
        mobileToggle.focus();
        return;
      }
      if (event.key === 'Tab' && isOpen) {
        const focusable = getFocusableElements(mobileDrawer);
        trapFocus(event, focusable, mobileToggle);
      }
    }

    function onOutsideClick(event) {
      if (!siteHeader.contains(event.target)) {
        closeMobile();
      }
    }

    mobileToggle.addEventListener('click', (event) => {
      event.preventDefault();
      toggleMobile();
    });

    once('muin-mobile-accordion', '[data-mobile-trigger]', mobileDrawer).forEach((trigger) => {
      const panelId = trigger.getAttribute('aria-controls');
      const panel = panelId ? mobileDrawer.querySelector('#' + CSS.escape(panelId)) : null;
      trigger.addEventListener('click', (event) => {
        event.preventDefault();
        const expanded = trigger.getAttribute('aria-expanded') === 'true';
        trigger.setAttribute('aria-expanded', expanded ? 'false' : 'true');
        if (panel) {
          panel.hidden = expanded;
          panel.classList.toggle('is-open', !expanded);
        }
      });
    });
  }

  Drupal.behaviors.muinHeader = {
    attach(context) {
      once('muin-site-header', '[data-site-header]', context).forEach((header) => {
        const navContainer = header.querySelector('[data-header-nav]');
        if (!navContainer) {
          return;
        }
        initDesktop(navContainer, header);
        initMobile(navContainer, header);
      });
    }
  };
})(Drupal, once);
