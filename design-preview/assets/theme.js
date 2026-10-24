/**
 * Progressive enhancements for the ISG theme.
 * Homepage scenes are visual placeholders for corporate video assets.
 */
(function () {
  'use strict';

  var yearNodes = document.querySelectorAll('[data-current-year]');
  yearNodes.forEach(function (node) { node.textContent = String(new Date().getFullYear()); });

  var menuToggle = document.querySelector('.site-header__menu-toggle');
  var primaryNavigation = document.getElementById('primary-navigation');
  var searchToggle = document.querySelector('.header-search-toggle');
  var searchPanel = document.getElementById('header-search');

  function closeMenu() {
    if (!menuToggle || !primaryNavigation) return;
    menuToggle.setAttribute('aria-expanded', 'false');
    primaryNavigation.classList.remove('is-open');
  }

  if (menuToggle && primaryNavigation) {
    menuToggle.addEventListener('click', function () {
      var expanded = menuToggle.getAttribute('aria-expanded') === 'true';
      menuToggle.setAttribute('aria-expanded', String(!expanded));
      primaryNavigation.classList.toggle('is-open', !expanded);
    });
    primaryNavigation.addEventListener('click', function (event) {
      if (event.target.closest('a') && window.matchMedia('(max-width: 900px)').matches) closeMenu();
    });
  }

  if (searchToggle && searchPanel) {
    searchToggle.addEventListener('click', function () {
      var expanded = searchToggle.getAttribute('aria-expanded') === 'true';
      searchToggle.setAttribute('aria-expanded', String(!expanded));
      searchPanel.hidden = expanded;
      if (!expanded) {
        var input = searchPanel.querySelector('input[type="search"]');
        if (input) input.focus();
      }
    });
  }

  var heroControls = Array.prototype.slice.call(document.querySelectorAll('[data-hero-target]'));
  var heroPanels = Array.prototype.slice.call(document.querySelectorAll('[data-hero-panel]'));
  var heroScenes = Array.prototype.slice.call(document.querySelectorAll('[data-hero-scene]'));
  var heroStatus = document.querySelector('[data-hero-status]');
  var heroCount = document.querySelector('[data-hero-count]');
  var heroStatusLabels = ['КАРТА SOLFLARE', 'INNOVATION FOR LIFE', 'ALLVEND', 'УМНЫЙ ГОРОД'];
  var heroIndex = 0;
  var heroTimer = null;
  var reducedMotion = window.matchMedia('(prefers-reduced-motion: reduce)').matches;

  function activateHeroSlide(index, fromUser) {
    if (!heroControls.length || !heroPanels.length) return;
    heroIndex = (index + heroControls.length) % heroControls.length;
    heroControls.forEach(function (control) {
      var active = Number(control.getAttribute('data-hero-target')) === heroIndex;
      control.classList.toggle('is-active', active);
      control.setAttribute('aria-pressed', String(active));
    });
    heroPanels.forEach(function (panel) {
      var active = Number(panel.getAttribute('data-hero-panel')) === heroIndex;
      panel.classList.toggle('is-active', active);
      panel.setAttribute('aria-hidden', String(!active));
    });
    heroScenes.forEach(function (scene) {
      var active = Number(scene.getAttribute('data-hero-scene')) === heroIndex;
      scene.classList.toggle('is-active', active);
      scene.setAttribute('aria-hidden', String(!active));
    });
    if (heroStatus) heroStatus.textContent = heroStatusLabels[heroIndex] || heroStatusLabels[0];
    if (heroCount) heroCount.textContent = String(heroIndex + 1).padStart(2, '0');
    if (fromUser) restartHeroTimer();
  }

  function restartHeroTimer() {
    if (heroTimer) window.clearInterval(heroTimer);
    if (reducedMotion || heroControls.length < 2) return;
    heroTimer = window.setInterval(function () { activateHeroSlide(heroIndex + 1, false); }, 8000);
  }

  heroControls.forEach(function (control) {
    control.addEventListener('click', function () {
      activateHeroSlide(Number(control.getAttribute('data-hero-target')), true);
    });
  });
  if (heroControls.length) restartHeroTimer();

  document.addEventListener('keydown', function (event) {
    if (event.key === 'Escape') {
      closeMenu();
      if (searchToggle && searchPanel && !searchPanel.hidden) {
        searchPanel.hidden = true;
        searchToggle.setAttribute('aria-expanded', 'false');
        searchToggle.focus();
      }
    }
  });
})();
