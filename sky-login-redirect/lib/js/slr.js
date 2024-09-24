"use strict";

jQuery(document).ready(function () {

  var upgrade_url = SLR.upgrade_url;
  var pro_feature = SLR.pro_feature;
  var business_feature = SLR.business_feature;

  jQuery('ul.cf-container__tabs-list li:nth-child(1)').prepend('<svg xmlns="http://www.w3.org/2000/svg" width="1.625em" height="1.625em" style="transform: rotate(360deg);" preserveAspectRatio="xMidYMid meet" viewBox="0 0 32 32"><path class="fill-olive" d="M16 4c-5.11 0-9.383 3.16-11.125 7.625l1.844.75C8.176 8.64 11.71 6 16 6c3.24 0 6.134 1.59 7.938 4H20v2h7V5h-2v3.094A11.928 11.928 0 0 0 16 4zm9.28 15.625C23.824 23.36 20.29 26 16 26c-3.276 0-6.157-1.612-7.97-4H12v-2H5v7h2v-3.094C9.19 26.386 12.395 28 16 28c5.11 0 9.383-3.16 11.125-7.625l-1.844-.75z" fill="#626262"/><rect x="0" y="0" width="32" height="32" fill="rgba(0, 0, 0, 0)" /></svg>').attr('id', 'tab-rules');
  jQuery('ul.cf-container__tabs-list li:nth-child(2)').prepend('<svg xmlns="http://www.w3.org/2000/svg" width="1.625em" height="1.625em" style="transform: rotate(360deg);" preserveAspectRatio="xMidYMid meet" viewBox="0 0 36 36"><path class="fill-purple" d="M31 10V4a2 2 0 0 0-2-2H6a2 2 0 0 0-2 2v6a2 2 0 0 0 2 2h23a2 2 0 0 0 2-2zM6 4h23v6H6z" class="clr-i-outline clr-i-outline-path-1" fill="#626262"/><path class="fill-pink" d="M33 6h-1v6.29l-13.3 4.25a1 1 0 0 0-.7 1V19h-2v14a2 2 0 0 0 2 2h2a2 2 0 0 0 2-2V19h-2v-.73L33.3 14a1 1 0 0 0 .7-1V7a1 1 0 0 0-1-1zM20 33h-2V21h2z" class="clr-i-outline clr-i-outline-path-2" fill="#626262"/><rect x="0" y="0" width="36" height="36" fill="rgba(0, 0, 0, 0)" /></svg>').attr('id', 'tab-customizer').attr('id', 'tab-customizer');
  jQuery('ul.cf-container__tabs-list li:nth-child(3)').prepend('<svg xmlns="http://www.w3.org/2000/svg" width="1.625em" height="1.625em" style=" transform: rotate(360deg);" preserveAspectRatio="xMidYMid meet" viewBox="0 0 16 16"><g fill="#626262"><path fill-rule="evenodd" d="M0 1l1-1l3.081 2.2a1 1 0 0 1 .419.815v.07a1 1 0 0 0 .293.708L10.5 9.5l.914-.305a1 1 0 0 1 1.023.242l3.356 3.356a1 1 0 0 1 0 1.414l-1.586 1.586a1 1 0 0 1-1.414 0l-3.356-3.356a1 1 0 0 1-.242-1.023L9.5 10.5L3.793 4.793a1 1 0 0 0-.707-.293h-.071a1 1 0 0 1-.814-.419L0 1zm11.354 9.646a.5.5 0 0 0-.708.708l3 3a.5.5 0 0 0 .708-.708l-3-3z"/><path fill-rule="evenodd" class="fill-blue" d="M15.898 2.223a3.003 3.003 0 0 1-3.679 3.674L5.878 12.15a3 3 0 1 1-2.027-2.027l6.252-6.341A3 3 0 0 1 13.778.1l-2.142 2.142L12 4l1.757.364l2.141-2.141zm-13.37 9.019L3.001 11l.471.242l.529.026l.287.445l.445.287l.026.529L5 13l-.242.471l-.026.529l-.445.287l-.287.445l-.529.026L3 15l-.471-.242L2 14.732l-.287-.445L1.268 14l-.026-.529L1 13l.242-.471l.026-.529l.445-.287l.287-.445l.529-.026z"/></g><rect x="0" y="0" width="16" height="16" fill="rgba(0, 0, 0, 0)" /></svg>').attr('id', 'tab-tweaks');
  jQuery('ul.cf-container__tabs-list li:nth-child(4)').prepend('<svg xmlns="http://www.w3.org/2000/svg" width="1.625em" height="1.625em" style="transform: rotate(360deg);" preserveAspectRatio="xMidYMid meet" viewBox="0 0 16 16"><g fill="#626262"><path fill-rule="evenodd" class="fill-olive" d="M11.354 5.646a.5.5 0 0 1 0 .708l-3 3a.5.5 0 0 1-.708 0l-1.5-1.5a.5.5 0 1 1 .708-.708L8 8.293l2.646-2.647a.5.5 0 0 1 .708 0z"/><path fill-rule="evenodd" d="M0 1.5A.5.5 0 0 1 .5 1H2a.5.5 0 0 1 .485.379L2.89 3H14.5a.5.5 0 0 1 .491.592l-1.5 8A.5.5 0 0 1 13 12H4a.5.5 0 0 1-.491-.408L2.01 3.607L1.61 2H.5a.5.5 0 0 1-.5-.5zM3.102 4l1.313 7h8.17l1.313-7H3.102zM5 12a2 2 0 1 0 0 4a2 2 0 0 0 0-4zm7 0a2 2 0 1 0 0 4a2 2 0 0 0 0-4zm-7 1a1 1 0 1 0 0 2a1 1 0 0 0 0-2zm7 0a1 1 0 1 0 0 2a1 1 0 0 0 0-2z"/></g><rect x="0" y="0" width="16" height="16" fill="rgba(0, 0, 0, 0)" /></svg>').attr('id', 'tab-shop');
  jQuery('ul.cf-container__tabs-list li:nth-child(5)').prepend('<svg xmlns="http://www.w3.org/2000/svg" width="1.625em" height="1.625em" style="transform: rotate(360deg);" preserveAspectRatio="xMidYMid meet" viewBox="0 0 36 36"><path class="fill-navy" d="M33.53 18.76l-6.93-3.19h-2l-6.5 3l-6.5-3V8l6.08 2.8a1 1 0 0 0 .84 0l.24-.11a4.17 4.17 0 0 1 .29-.65l1.33-2.31l-2.28 1l-5.1-2.3l5.1-2.35l3.47 1.6l1-1.73l-4.07-1.87a1 1 0 0 0-.84 0l-7.5 3.45a1 1 0 0 0-.58.91v9.14l-6.9 3.18a1 1 0 0 0-.58.91v9.78a1 1 0 0 0 .58.91l7.5 3.45a1 1 0 0 0 .84 0l7.08-3.26l7.08 3.26a1 1 0 0 0 .84 0l7.5-3.45a1 1 0 0 0 .58-.91v-9.78a1 1 0 0 0-.57-.91zM10.6 17.31l5.11 2.35L10.6 22l-5.11-2.33zm0 14.49l-6.5-3v-7.57L10.18 24a1 1 0 0 0 .82 0l6.08-2.8v7.6zm15-14.48l5.11 2.35l-5.1 2.33l-5.11-2.33zm0 14.49l-6.51-3v-7.59l6.1 2.78a1 1 0 0 0 .81 0l6.08-2.8v7.61z" class="clr-i-outline--alerted clr-i-outline-path-1--alerted" fill="#626262"/><path class="fill-red" d="M26.85 1.14l-5.72 9.91a1.27 1.27 0 0 0 1.1 1.95h11.45a1.27 1.27 0 0 0 1.1-1.91l-5.72-9.95a1.28 1.28 0 0 0-2.21 0z" class="clr-i-outline--alerted clr-i-outline-path-2--alerted clr-i-alert" fill="#626262"/><rect x="0" y="0" width="36" height="36" fill="rgba(0, 0, 0, 0)" /></svg>').attr('id', 'tab-blocks');
  jQuery('ul.cf-container__tabs-list li:nth-child(6)').prepend('<svg xmlns="http://www.w3.org/2000/svg" width="1.625em" height="1.625em" style="transform: rotate(360deg);" preserveAspectRatio="xMidYMid meet" viewBox="0 0 32 32"><path class="fill-teal" d="M28 4H10a2.006 2.006 0 0 0-2 2v14a2.006 2.006 0 0 0 2 2h18a2.006 2.006 0 0 0 2-2V6a2.006 2.006 0 0 0-2-2zm0 16H10V6h18z" fill="#626262"/><path class="fill-olive" d="M18 26H4V16h2v-2H4a2.006 2.006 0 0 0-2 2v10a2.006 2.006 0 0 0 2 2h14a2.006 2.006 0 0 0 2-2v-2h-2z" fill="#626262"/><rect x="0" y="0" width="32" height="32" fill="rgba(0, 0, 0, 0)" /></svg>');
  jQuery('ul.cf-container__tabs-list li:nth-child(8)').prepend('<svg xmlns="http://www.w3.org/2000/svg" width="1.625em" height="1.625em" style="transform: rotate(360deg);" preserveAspectRatio="xMidYMid meet" viewBox="0 0 128 128"><path d="M27.9 36.5H20V7.9c0-2.2 1.8-4 4-4s4 1.8 4 4l-.1 28.6z" fill="#e0e0e0"/><path d="M52 36.5h-7.9V7.9c0-2.2 1.8-4 4-4s4 1.8 4 4L52 36.5z" fill="#e0e0e0"/><path class="fill-fuchsia" d="M123.9 64.1c0 1-.8 1.8-1.8 1.8H98.5c-8.6 0-15.5 6.9-15.5 15.5l.2 16.4c0 14.9-11.6 26.5-26.2 26.3c-14.4-.2-25.8-12.4-25.8-26.8v-33c0-2.5 1.9-4.8 4.4-5c2.9-.3 5.3 2 5.3 4.8v33.2c0 9 6.9 16.7 15.9 17c9.4.4 16.8-7 16.8-16.6l-.2-16.4c0-13.9 11.3-25.2 25.2-25.2h23.7c1 0 1.8.8 1.8 1.8v6.2h-.2z" fill="#424242"/><path d="M121.9 58.2v5.7H98.5c-9.7 0-17.5 7.8-17.5 17.5l.2 16.4c0 13.6-10.4 24.3-23.8 24.3H57c-13.1-.2-23.8-11.3-23.8-24.8v-33c0-1.6 1.1-2.9 2.5-3h.3c1.6 0 2.8 1.3 2.8 2.8v33.2c0 10.2 7.8 18.6 17.8 19h.8c10.2 0 18.1-8.2 18.1-18.6l-.2-16.4c0-12.8 10.4-23.2 23.2-23.2l23.4.1m.3-2H98.5c-13.9 0-25.2 11.3-25.2 25.2l.2 16.4c0 9.3-7.1 16.6-16.1 16.6h-.7c-9-.4-15.9-8.1-15.9-17V64.1c0-2.7-2.2-4.8-4.8-4.8h-.5c-2.5.2-4.4 2.5-4.4 5v32.9c0 14.4 11.4 26.5 25.8 26.8h.5c14.5 0 25.8-11.5 25.8-26.3L83 81.3c0-8.6 6.9-15.5 15.5-15.5h23.7c1 0 1.8-.8 1.8-1.8v-6.2c-.1-.9-.9-1.6-1.8-1.6z" fill="#eee" opacity=".2"/><path d="M24 5.9c1.1 0 2 .9 2 2v26.6h-4V7.9c0-1.1.9-2 2-2m0-2c-2.2 0-4 1.8-4 4v28.6h7.9V7.9c0-2.2-1.8-4-3.9-4z" fill="#424242" opacity=".2"/><path d="M48.1 5.9c1.1 0 2 .9 2 2v26.6h-3.9V7.9c-.1-1.1.8-2 1.9-2m0-2c-2.2 0-4 1.8-4 4v28.6H52V7.9c0-2.2-1.7-4-3.9-4z" fill="#424242" opacity=".2"/><path d="M61.9 24H10c-3.3 0-6 2.5-6 5.9c0 3.3 2.7 6.1 6 6.1h2v19.3c0 5.3 3.6 12.2 8 15.1c19.3 13.2 40-.3 40-18.6V36h1.9c3.3 0 6-2.8 6-6.2v.1c0-3.3-2.7-5.9-6-5.9z" fill="#424242"/><path d="M61.9 27c1.7 0 3.1 1.3 3.1 2.9c0 1.7-1.5 3.1-3.1 3.1H60c-1.7 0-3 1.3-3 3v15.8c0 11.5-9.4 20.9-21 20.9c-4.8 0-9.8-1.7-14.3-4.8c-3.6-2.4-6.7-8.4-6.7-12.7V36c0-1.7-1.3-3-3-3h-2c-1.7 0-3-1.4-3-3.1c0-1.6 1.3-2.9 3-2.9h51.9m0-3H10c-3.3 0-6 2.5-6 5.9c0 3.3 2.7 6.1 6 6.1h2v19.3c0 5.3 3.6 12.2 8 15.1c5.4 3.7 10.9 5.3 16 5.3c13.2 0 24-10.7 24-23.9V36h1.9c3.3 0 6.1-2.8 6.1-6.1S65.2 24 61.9 24zm6.1 5.8c0 .1 0 0 0 0z" fill="#eee" opacity=".2"/><rect x="0" y="0" width="128" height="128" fill="rgba(0, 0, 0, 0)" /></svg>').attr('id', 'tab-plugins');
  jQuery('ul.cf-container__tabs-list li:nth-child(7)').prepend('<svg xmlns="http://www.w3.org/2000/svg" xmlns:xlink="http://www.w3.org/1999/xlink" aria-hidden="true" focusable="false" width="1.625em" height="1.625em" style="transform: rotate(360deg);" preserveAspectRatio="xMidYMid meet" viewBox="0 0 50 50"><path d="M34 23h-2v-4c0-3.9-3.1-7-7-7s-7 3.1-7 7v4h-2v-4c0-5 4-9 9-9s9 4 9 9v4z" fill="#626262"/><path d="M33 40H17c-1.7 0-3-1.3-3-3V25c0-1.7 1.3-3 3-3h16c1.7 0 3 1.3 3 3v12c0 1.7-1.3 3-3 3zM17 24c-.6 0-1 .4-1 1v12c0 .6.4 1 1 1h16c.6 0 1-.4 1-1V25c0-.6-.4-1-1-1H17z" fill="#626262"/><circle class="fill-olive" cx="25" cy="28" r="2" fill="#626262"/><path class="fill-olive" d="M25.5 28h-1l-1 6h3z" fill="#626262"/><rect x="0" y="0" width="128" height="128" fill="rgba(0, 0, 0, 0)" /></svg>').attr('id', 'tab-restrict');

  /* help */
  if (jQuery('ul.cf-container__tabs-list li:nth-child(1):visible')) {
    jQuery('.cf-container div.cf-container__fields:visible').addClass('content-login');
    jQuery('.content-login .cf-field__help').last().css({ 'margin-top': '2rem', 'text-align': 'center' });
  }

  /* On montre le message à ceux qui n'ont pas le plan idoine */
  jQuery('.free .starter *, .free .business *, .free .platinum *, .plan-starter .business *, .plan-starter .platinum *, .plan-business .platinum *').prop('disabled', 'disabled').addClass('upgrade-premium');
  jQuery('.free .upselly, .plan-starter .upselly.business, .plan-starter .upselly.platinum, .plan-business .upselly.platinum').prop("disabled", false).show().find("div, a").prop("disabled", false);

  setTimeout(function () {
    jQuery('input:disabled, select:disabled').each(function () {
      jQuery(this).prop('checked', false);
    })
  }, 2000);

  window.setInterval(function () {
    jQuery('ul.cf-container__tabs-list li:nth-child(1)').addClass('tab-rules').attr('id', 'tab-rules');
    jQuery('ul.cf-container__tabs-list li:nth-child(2)').addClass('tab-customizer').attr('id', 'tab-customizer');
    jQuery('ul.cf-container__tabs-list li:nth-child(3)').addClass('tab-tweaks').attr('id', 'tab-tweaks');
    jQuery('ul.cf-container__tabs-list li:nth-child(4)').addClass('tab-shop').attr('id', 'tab-shop');
    jQuery('ul.cf-container__tabs-list li:nth-child(5)').addClass('tab-blocks').attr('id', 'tab-blocks');
    jQuery('ul.cf-container__tabs-list li:nth-child(6)').addClass('tab-modal').attr('id', 'tab-modal');
    jQuery('ul.cf-container__tabs-list li:nth-child(8)').addClass('tab-plugins').attr('id', 'tab-plugins');
    jQuery('ul.cf-container__tabs-list li:nth-child(7)').addClass('tab-restrict').attr('id', 'tab-restrict');
  }, 2000);

  window.setInterval(function () {
    if (jQuery(".cf-complex__tabs--tabbed-vertical .cf-complex__inserter").length > 0) { jQuery('.more-rules').hide(); }
    else { jQuery('.more-rules').show(); }
    var ruleBtn = document.querySelector('.cf-container-carbon_fields_container_sky_login_redirect button.cf-complex__inserter-button')
    if (ruleBtn) {
      ruleBtn.textContent = 'Add rule';
    }
  }, 10);

  jQuery('#slr-login-iframe').insertBefore('.babar').show();

  // codemirror
  function refreshCodeMirrors() {

    var readonly = false;
    if (jQuery('.cf-container').hasClass('free')) {
      var readonly = 'nocursor';
      return;
    }

    // HTML textareas
    jQuery('.codemirror-js textarea').each(function (e) {
      if (jQuery(this).is(':visible')) {
        let editorSettings = wp.codeEditor.defaultSettings ? _.clone(wp.codeEditor.defaultSettings) : {};
        editorSettings.codemirror = _.extend(
          {},
          editorSettings.codemirror,
          {
            indentUnit: 2,
            tabSize: 2,
            mode: 'text/html',
            autoRefresh: true,
            readOnly: readonly,
          }
        );
        wp.codeEditor.initialize(jQuery(this), editorSettings);
      }
    });

    // CSS textarea
    jQuery('.codemirror-css textarea').each(function (e) {
      if (jQuery(this).is(':visible')) {
        let editorSettings = wp.codeEditor.defaultSettings ? _.clone(wp.codeEditor.defaultSettings) : {};
        editorSettings.codemirror = _.extend(
          {},
          editorSettings.codemirror,
          {
            indentUnit: 2,
            tabSize: 2,
            mode: 'text/css',
            autoRefresh: true,
            readOnly: readonly,
          }
        );
        wp.codeEditor.initialize(jQuery(this), editorSettings);
      }
    });
  };

  setInterval(refreshCodeMirrors, 1000);

  /*
  var colors = ['#AE0A25', '#075A8D', '#006130'];
  var random_color = colors[Math.floor(Math.random() * colors.length)];
  jQuery('#buy .button').css('background-color', random_color);
  */
});

document.addEventListener("DOMContentLoaded", function () {
  // Get the elements
  const promoBox = document.getElementById("promo-box");
  const minimizeIcon = document.getElementById("minimize-icon");
  const maximizeIcon = document.getElementById("maximize-icon");
  const contentElements = promoBox.querySelectorAll('p, ul');

  // Minimize content by default on page load
  contentElements.forEach(el => el.classList.add('minimized'));
  minimizeIcon.style.display = 'none';
  maximizeIcon.style.display = 'block';

  // Add click event listener for minimize
  minimizeIcon.addEventListener("click", function () {
    // Hide the content and show the maximize icon
    contentElements.forEach(el => el.classList.add('minimized'));
    minimizeIcon.style.display = 'none';
    maximizeIcon.style.display = 'block';
  });

  // Add click event listener for maximize
  maximizeIcon.addEventListener("click", function () {
    // Show the content and hide the maximize icon
    contentElements.forEach(el => el.classList.remove('minimized'));
    minimizeIcon.style.display = 'block';
    maximizeIcon.style.display = 'none';
  });
});

