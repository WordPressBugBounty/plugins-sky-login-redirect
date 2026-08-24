"use strict";

document.addEventListener("DOMContentLoaded", function () {
  // Preserve access to localized vars if provided
  var upgrade_url = typeof SLR !== 'undefined' ? SLR.upgrade_url : undefined;
  var pro_feature = typeof SLR !== 'undefined' ? SLR.pro_feature : undefined;
  var business_feature = typeof SLR !== 'undefined' ? SLR.business_feature : undefined;

  // Simple debounce helper to coalesce rapid DOM changes
  function debounce(fn, wait) {
    let t;
    return function () {
      clearTimeout(t);
      const ctx = this, args = arguments;
      t = setTimeout(function () { fn.apply(ctx, args); }, wait);
    };
  }

  // Prepend an inline SVG icon referencing a sprite <symbol> already in the page
  function insertSymbolIcon(li, symbolId) {
    if (!li || !symbolId) return;
    if (li.querySelector('svg.slr-icon')) return; // idempotent
    try {
      const svg = document.createElementNS('http://www.w3.org/2000/svg', 'svg');
      svg.setAttribute('class', 'slr-icon');
      svg.setAttribute('width', '1.625em');
      svg.setAttribute('height', '1.625em');
      svg.style.verticalAlign = 'middle';
      const use = document.createElementNS('http://www.w3.org/2000/svg', 'use');
      use.setAttribute('href', '#' + symbolId);
      svg.appendChild(use);
      li.insertAdjacentElement('afterbegin', svg);
    } catch (e) { /* noop */ }
  }

  // Prepend SVG icons to tabs and set IDs
  const tabList = document.querySelector('ul.cf-container__tabs-list');
  if (tabList) {
    const li1 = tabList.querySelector('li:nth-child(1)');
    if (li1) {
      insertSymbolIcon(li1, 'rules');
      li1.id = 'tab-rules';
    }
    const li2 = tabList.querySelector('li:nth-child(2)');
    if (li2) {
      insertSymbolIcon(li2, 'customizer');
      li2.id = 'tab-customizer';
    }
    const li3 = tabList.querySelector('li:nth-child(3)');
    if (li3) {
      insertSymbolIcon(li3, 'tweaks');
      li3.id = 'tab-tweaks';
    }
    const li4 = tabList.querySelector('li:nth-child(4)');
    if (li4) {
      insertSymbolIcon(li4, 'shop');
      li4.id = 'tab-shop';
    }
    const li5 = tabList.querySelector('li:nth-child(5)');
    if (li5) {
      insertSymbolIcon(li5, 'blocks');
      li5.id = 'tab-blocks';
    }
    const li6 = tabList.querySelector('li:nth-child(6)');
    if (li6) {
      insertSymbolIcon(li6, 'modal');
      li6.id = 'tab-modal';
    }
    const li7 = tabList.querySelector('li:nth-child(7)');
    if (li7) {
      insertSymbolIcon(li7, 'restrict');
      li7.id = 'tab-restrict';
    }
    const li8 = tabList.querySelector('li:nth-child(8)');
    if (li8) {
      insertSymbolIcon(li8, 'plugins');
      li8.id = 'tab-plugins';
    }
  }

  // Help styling applied to visible fields container
  const visibleFields = Array.from(document.querySelectorAll('.cf-container div.cf-container__fields')).filter(el => el.offsetParent !== null);
  if (visibleFields.length) {
    visibleFields.forEach(v => v.classList.add('content-login'));
    const helps = visibleFields[0].querySelectorAll('.cf-field__help');
    if (helps.length) {
      const last = helps[helps.length - 1];
      last.style.marginTop = '2rem';
      last.style.textAlign = 'center';
    }
  }

  // Feature gating: disable elements for lower plans and show upsell
  const disableSelectors = '.free .starter *, .free .business *, .free .platinum *, .plan-starter .business *, .plan-starter .platinum *, .plan-business .platinum *';
  document.querySelectorAll(disableSelectors).forEach(el => {
    if ('disabled' in el) el.disabled = true;
    el.classList.add('upgrade-premium');
  });
  const upsellSelectors = '.free .upselly, .plan-starter .upselly.business, .plan-starter .upselly.platinum, .plan-business .upselly.platinum';
  document.querySelectorAll(upsellSelectors).forEach(el => {
    if ('disabled' in el) el.disabled = false;
    el.style.display = '';
    el.querySelectorAll('div, a, button, input, select, textarea').forEach(child => {
      if ('disabled' in child) child.disabled = false;
    });
  });

  // Uncheck disabled inputs shortly after render
  function uncheckDisabled() {
    document.querySelectorAll('input:disabled, select:disabled').forEach(function (el) {
      if ('checked' in el) el.checked = false;
    });
  }
  // Run once on load
  uncheckDisabled();
  // React to DOM changes within the Carbon Fields container
  const cfContainer = document.querySelector('.cf-container');
  if (cfContainer && window.MutationObserver) {
    const uncheckDebounced = debounce(uncheckDisabled, 100);
    new MutationObserver(function (muts) {
      for (const m of muts) {
        if (m.type === 'childList' || m.type === 'attributes') { uncheckDebounced(); break; }
      }
    }).observe(cfContainer, { childList: true, subtree: true, attributes: true, attributeFilter: ['disabled', 'class', 'style'] });
  }

  // Complex tabs: hide/show "more rules" and rename inserter button (observer-driven)
  function updateComplexTabsUI() {
    const hasTabbedVertical = document.querySelector(".cf-complex__tabs--tabbed-vertical .cf-complex__inserter") !== null;
    document.querySelectorAll('.more-rules').forEach(el => { el.style.display = hasTabbedVertical ? 'none' : 'block'; });
    document
      .querySelectorAll('.cf-container-carbon_fields_container_sky_login_redirect button.cf-complex__inserter-button')
      .forEach(function (btn) { btn.textContent = 'Add rule'; });
  }
  // Run once and then on DOM changes
  updateComplexTabsUI();
  if (cfContainer && window.MutationObserver) {
    const updateTabsDebounced = debounce(updateComplexTabsUI, 50);
    new MutationObserver(function () { updateTabsDebounced(); })
      .observe(cfContainer, { childList: true, subtree: true });
  }

  // Hoist "Save Changes" button next to the page title
  const slrContainer = document.getElementById('carbon_fields_container_sky_login_redirect');
  if (slrContainer) {
    const slrForm = slrContainer.closest('form');
    const slrWrap = slrForm ? slrForm.parentElement : null;
    const slrHeading = slrWrap
      ? Array.from(slrWrap.querySelectorAll('h1, h2')).find(function (el) {
          return !el.closest('form') && !el.closest('.cf-container');
        })
      : null;

    if (slrForm && slrHeading) {
      if (!slrForm.id) slrForm.id = 'slr-options-form';

      const titleBtn = document.createElement('button');
      titleBtn.type = 'submit';
      titleBtn.setAttribute('form', slrForm.id);
      titleBtn.className = 'button button-primary';
      titleBtn.textContent = 'Save Changes';

      const titleBar = document.createElement('div');
      titleBar.className = 'slr-title-bar';
      slrHeading.parentNode.insertBefore(titleBar, slrHeading);
      titleBar.appendChild(slrHeading);
      titleBar.appendChild(titleBtn);
    }
  }

  // Move and show iframe
  const iframe = document.getElementById('slr-login-iframe');
  const beforeEl = document.querySelector('.babar');
  if (iframe && beforeEl && beforeEl.parentNode) {
    beforeEl.parentNode.insertBefore(iframe, beforeEl);
    iframe.style.display = 'block';
  }

  // CodeMirror initialization
  function refreshCodeMirrors() {

    var readonly = false;
    const container = document.querySelector('.cf-container');
    if (container && container.classList.contains('free')) {
      readonly = 'nocursor';
      return;
    }

    // Ensure Code Editor API is available before attempting to initialize
    if (!(window.wp && wp.codeEditor)) {
      // Try again shortly; assets may still be loading
      setTimeout(refreshCodeMirrors, 500);
      return;
    }

    // Shared initializer for CodeMirror textareas
    function initEditors(selectorList, mode) {
      const selectors = Array.isArray(selectorList) ? selectorList : [selectorList];
      const nodes = selectors.flatMap(function(sel){ return Array.from(document.querySelectorAll(sel)); });

      nodes.forEach(function (ta) {
        // If the selector matched a wrapper, pick its textarea child
        if (ta && ta.tagName && ta.tagName.toLowerCase() !== 'textarea') {
          ta = ta.querySelector('textarea');
        }
        if (!ta) return;

        // Initialize regardless of visibility; autoRefresh handles rendering later
        if (ta.dataset.cmInitialized === '1') return; // prevent duplicate init

        // Prefer settings localized from wp_enqueue_code_editor when available
        var baseSettings = null;
        if (mode === 'text/css' && window.cm_css && window.cm_css.codeEditor) {
          baseSettings = window.cm_css.codeEditor;
        } else if (mode === 'text/html' && window.cm_js && window.cm_js.codeEditor) {
          baseSettings = window.cm_js.codeEditor;
        }
        if (!baseSettings && wp.codeEditor && wp.codeEditor.defaultSettings) {
          baseSettings = wp.codeEditor.defaultSettings;
        }

        // Clone base settings
        var editorSettings = {};
        if (baseSettings) {
          try { editorSettings = JSON.parse(JSON.stringify(baseSettings)); }
          catch (e) { editorSettings = Object.assign({}, baseSettings); }
        }

        // Merge codemirror options
        editorSettings.codemirror = Object.assign(
          {},
          editorSettings.codemirror || {},
          {
            indentUnit: 2,
            tabSize: 2,
            mode: mode,
            autoRefresh: true,
          }
        );

        wp.codeEditor.initialize(ta, editorSettings);
        ta.dataset.cmInitialized = '1';
      });
    }

    // Initialize both HTML (JS) and CSS editors
    // Support both wrapper-class and textarea-class; fallback to name-based selectors used in options.php
    initEditors([
      '.codemirror-js textarea',
      'textarea.codemirror-js',
      'textarea[name*="slr_header_code"]',
      'textarea[name*="slr_form_code"]',
      'textarea[name*="slr_footer_code"]'
    ], 'text/html');

    initEditors([
      '.codemirror-css textarea',
      'textarea.codemirror-css',
      'textarea[name*="slr_login_css"]'
    ], 'text/css');
  }

  // Public debug flag (set window.SLR_DEBUG = true in console to enable)
  if (typeof window.SLR_DEBUG === 'undefined') { window.SLR_DEBUG = false; }

  // Initialize editors once, then on DOM changes
  // Small delay to allow other assets/tabs to render
  setTimeout(refreshCodeMirrors, 100);

  function attachCFObserver(target) {
    if (!target || !window.MutationObserver) return;
    const cmDebounced = debounce(refreshCodeMirrors, 500);
    new MutationObserver(function () { cmDebounced(); }).observe(target, {
      childList: true,
      subtree: true,
      attributes: true,
      attributeFilter: ['class', 'style']
    });
  }

  // Tab click/keyboard navigation: re-run init shortly after switching tabs
  const tabsList = document.querySelector('.cf-container__tabs-list');
  if (tabsList) {
    const retrigger = () => setTimeout(refreshCodeMirrors, 120);
    tabsList.addEventListener('click', retrigger, true);
    tabsList.addEventListener('keyup', function (e) {
      if (e.key === 'Enter' || e.key === ' ' || e.code === 'Space') { retrigger(); }
    }, true);
  }

  // If container exists now, observe it; otherwise observe body and wait for it to appear
  if (cfContainer) {
    attachCFObserver(cfContainer);
  } else if (window.MutationObserver) {
    const findDebounced = debounce(function(){
      const later = document.querySelector('.cf-container');
      if (later) {
        attachCFObserver(later);
        if (window.SLR_DEBUG) { try { console.debug('[SLR] Attached observer to .cf-container (late)'); } catch(e){} }
      }
    }, 200);
    new MutationObserver(function () { findDebounced(); }).observe(document.body, { childList: true, subtree: true });
  }

  // Try again on full window load in case assets render after DOMContentLoaded
  window.addEventListener('load', function(){ setTimeout(refreshCodeMirrors, 100); });
});

// Promo box minimize/maximize (kept intact)
document.addEventListener("DOMContentLoaded", function () {
  // Get the elements
  const promoBox = document.getElementById("promo-box");
  if (!promoBox) return;
  const minimizeIcon = document.getElementById("minimize-icon");
  const maximizeIcon = document.getElementById("maximize-icon");
  const contentElements = promoBox.querySelectorAll('p, ul');

  // Minimize content by default on page load
  /*
  contentElements.forEach(el => el.classList.add('minimized'));
  if (minimizeIcon) minimizeIcon.style.display = 'none';
  if (maximizeIcon) maximizeIcon.style.display = 'block';
*/

  // Add click event listener for minimize
  if (minimizeIcon) {
    minimizeIcon.addEventListener("click", function () {
      contentElements.forEach(el => el.classList.add('minimized'));
      minimizeIcon.style.display = 'none';
      if (maximizeIcon) maximizeIcon.style.display = 'block';
    });
  }

  // Add click event listener for maximize
  if (maximizeIcon) {
    maximizeIcon.addEventListener("click", function () {
      contentElements.forEach(el => el.classList.remove('minimized'));
      if (minimizeIcon) minimizeIcon.style.display = 'block';
      maximizeIcon.style.display = 'none';
    });
  }
});

(function () {
  function renderCatalog(catalog) {
    var plugins = Array.isArray(catalog) ? catalog : (catalog && catalog.plugins);
    var grid = document.getElementById('slr-plugins-grid');
    if (!grid || !Array.isArray(plugins)) {
      return;
    }

    plugins = plugins.filter(function (plugin) {
      return plugin && plugin.slug !== 'sky-login-redirect';
    });
    grid.innerHTML = '';

    plugins.forEach(function (plugin) {
      var name = plugin.name || plugin.title || '';
      var description = plugin.description || plugin.body || '';
      var pluginUrl = plugin.url || '#';
      var installUrl = pluginUrl;
      var catalogInstallUrl = SLRPlugins.installUrl || '';
      if (plugin.install_slug && catalogInstallUrl) {
        installUrl = catalogInstallUrl.replace(
          /([?&])s=[^&]*/,
          '$1s=' + encodeURIComponent(plugin.install_slug)
        );
      }

      var block = document.createElement('article');
      block.className = 'slr-plugin-card';
      var imageLink = document.createElement('a');
      imageLink.href = pluginUrl;
      imageLink.target = '_blank';
      imageLink.rel = 'noopener';
      var image = document.createElement('img');
      image.className = 'slr-plugin-image';
      image.alt = name;
      image.src = plugin.icon || '';
      image.addEventListener('error', function () {
        imageLink.remove();
      });
      imageLink.appendChild(image);

      var body = document.createElement('div');
      body.className = 'slr-plugin-body';
      var title = document.createElement('strong');
      var titleLink = document.createElement('a');
      titleLink.href = pluginUrl;
      titleLink.target = '_blank';
      titleLink.rel = 'noopener';
      titleLink.textContent = name;
      title.appendChild(titleLink);
      var copy = document.createElement('p');
      copy.textContent = description;
      body.appendChild(title);
      body.appendChild(copy);

      var action = document.createElement('a');
      action.className = 'button button-primary';
      action.href = installUrl;
      action.textContent = plugin.cta || SLRPlugins.installText || 'Install';

      block.appendChild(imageLink);
      block.appendChild(body);
      block.appendChild(action);
      grid.appendChild(block);
    });
  }

  function loadCatalog() {
    if (typeof SLRPlugins === 'undefined' || !SLRPlugins.catalogUrl) {
      return;
    }
    fetch(SLRPlugins.catalogUrl, { credentials: 'same-origin' })
      .then(function (response) { return response.ok ? response.json() : null; })
      .then(renderCatalog)
      .catch(function () {});
  }

  document.addEventListener('DOMContentLoaded', loadCatalog);
}());
