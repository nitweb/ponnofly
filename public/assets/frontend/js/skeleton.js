/* PONNOFLY — skeleton.js: shimmer placeholders (page navigation, infinite scroll, shop search, suggestions, lazy images) */
(function () {
  'use strict';

  function s(cls) { return '<span class="sk ' + (cls || '') + '"></span>'; }
  function lines(n, last) {
    var h = '';
    for (var i = 0; i < n; i++) h += s('sk-line ' + (i === n - 1 ? (last || 'w60') : 'w100'));
    return '<div class="sk-stack">' + h + '</div>';
  }

  function card() {
    return '<li class="sk-card" data-skel><span class="sk sk-img"></span><div class="sk-body">' + s('sk-line w40') + s('sk-line w90') + s('sk-line w60') + s('sk-price') + '</div><span class="sk sk-cta"></span></li>';
  }
  function cards(n) { var h = ''; for (var i = 0; i < n; i++) h += card(); return h; }
  function grid(n, cls) { return '<ul class="sk-grid ' + (cls || '') + '">' + cards(n) + '</ul>'; }
  function head(title) { return '<div class="sk-between">' + s(title || 'sk-h2') + s('sk-line w20') + '</div>'; }

  function field() { return '<div class="sk-field">' + s('sk-line w30') + s('sk-input') + '</div>'; }
  function panel(inner) { return '<div class="sk-panel">' + inner + '</div>'; }
  function item() { return '<div class="sk-item">' + s('sk-thumb') + '<div class="sk-stack">' + s('sk-line w80') + s('sk-line w40') + '</div></div>'; }

  var T = {
    home: function () {
      var cats = '';
      for (var i = 0; i < 6; i++) cats += '<div class="sk-cat">' + s('sk-circle') + s('sk-line w60') + '</div>';
      return s('sk-banner') +
        '<div class="sk-section">' + head() + '<div class="sk-cats" style="margin-top:1.25rem">' + cats + '</div></div>' +
        '<div class="sk-section">' + head() + '<div style="margin-top:1.25rem">' + grid(4) + '</div></div>' +
        '<div class="sk-section">' + head() + '<div style="margin-top:1.25rem">' + grid(4) + '</div></div>';
    },
    shop: function () {
      var filters = panel(s('sk-h2') + lines(4) + s('sk-h2') + lines(4) + s('sk-h2') + s('sk-btn'));
      return s('sk-h1') + '<div style="margin-top:.75rem">' + s('sk-line w50') + '</div>' +
        '<div class="sk-split side"><div>' + filters + '</div><div>' + s('sk-line w30') + '<div style="margin-top:1.25rem">' + grid(6, 'cols-3') + '</div></div></div>';
    },
    product: function () {
      return '<div class="sk-split half"><div>' + s('sk-gallery') + '<div class="sk-thumbs">' + s() + s() + s() + s() + '</div></div>' +
        '<div class="sk-stack" style="align-content:start;gap:1.1rem">' + s('sk-line w30') + s('sk-h1') + s('sk-line w40') + s('sk-h2') + lines(4) + '<div class="sk-row">' + s('sk-pill') + s('sk-pill') + s('sk-pill') + '</div>' + s('sk-btn') + s('sk-btn') + '</div></div>' +
        '<div class="sk-section">' + head() + '<div style="margin-top:1.25rem">' + grid(4) + '</div></div>';
    },
    cart: function () {
      return s('sk-h1') + '<div class="sk-split main"><div class="sk-stack" style="gap:1rem">' + panel(item() + item() + item()) + '</div>' + panel(s('sk-h2') + lines(3) + s('sk-btn')) + '</div>';
    },
    checkout: function () {
      return s('sk-h1') + '<div class="sk-split main"><div class="sk-stack" style="gap:1rem">' + panel(s('sk-h2') + field() + field() + field() + field()) + panel(s('sk-h2') + field() + field()) + '</div>' + panel(s('sk-h2') + item() + item() + lines(3) + s('sk-btn')) + '</div>';
    },
    account: function () {
      return s('sk-h1') + '<div class="sk-split side"><div>' + panel(lines(5, 'w80')) + '</div><div class="sk-stack" style="gap:1rem">' + panel(s('sk-h2') + lines(3)) + panel(item() + item() + item()) + '</div></div>';
    },
    form: function () {
      return '<div class="sk-form-card">' + panel(s('sk-h2') + field() + field() + s('sk-btn')) + '</div>';
    },
    contact: function () {
      return s('sk-h1') + '<div class="sk-split side"><div>' + panel(lines(4)) + '</div>' + panel(field() + field() + field() + s('sk-input sk-box') + s('sk-btn')) + '</div>';
    },
    text: function () {
      return s('sk-h1') + '<div style="margin-top:1.5rem" class="sk-stack">' + lines(4) + '<br>' + lines(5) + '<br>' + lines(3) + '</div>';
    }
  };

  function kind(path) {
    path = path.replace(/\/+$/, '') || '/';
    if (path === '/') return 'home';
    if (/^\/(shop|search|flash-deals|category|brand)(\/|$)/.test(path)) return 'shop';
    if (/^\/product\//.test(path)) return 'product';
    if (path === '/cart') return 'cart';
    if (/^\/(checkout|order)(\/|$)/.test(path)) return 'checkout';
    if (/^\/(account|profile|dashboard)(\/|$)/.test(path)) return 'account';
    if (/^\/(login|register|forgot-password|reset-password|confirm-password|verify-email)(\/|$)/.test(path)) return 'form';
    if (path === '/contact' || path === '/track') return 'contact';
    return 'text';
  }

  var shown = null;

  function show(url) {
    var main = document.getElementById('main');
    if (!main) return;
    hide();
    var root = document.createElement('div');
    root.className = 'sk-root';
    root.setAttribute('role', 'status');
    root.setAttribute('aria-label', 'Loading');
    root.innerHTML = T[kind(new URL(url, location.href).pathname)]();
    Array.prototype.forEach.call(root.querySelectorAll('*'), function (n) { n.setAttribute('aria-hidden', 'true'); });
    main.appendChild(root);
    main.classList.add('sk-on');
    main.setAttribute('aria-busy', 'true');
    shown = root;
  }

  function hide() {
    var main = document.getElementById('main');
    if (shown && shown.parentNode) shown.parentNode.removeChild(shown);
    shown = null;
    if (main) { main.classList.remove('sk-on'); main.removeAttribute('aria-busy'); }
  }

  window.spaSkeleton = {
    show: show,
    hide: hide,
    cards: function (n) { return cards(n || 4); },
    results: function () { return '<div class="sk-stack" style="margin-top:1rem">' + s('sk-line w20') + '</div><div style="margin-top:1.25rem">' + grid(6, 'cols-3') + '</div>'; },
    suggest: function () {
      var h = '';
      for (var i = 0; i < 3; i++) h += '<div class="sk-hit">' + s() + '<div class="sk-stack">' + s('sk-line w80') + s('sk-line w40') + '</div></div>';
      return '<div class="sk-suggest" aria-hidden="true">' + h + '</div>';
    }
  };

  /* lazy image shimmer: stop animating once the image is in */
  function mark(img) {
    var w = img.closest && img.closest('.skel-img');
    if (w) w.classList.add('ok');
  }
  function scan() {
    Array.prototype.forEach.call(document.querySelectorAll('.skel-img img'), function (img) {
      if (img.complete && img.naturalWidth) mark(img);
    });
  }
  document.addEventListener('load', function (e) { if (e.target && e.target.tagName === 'IMG') mark(e.target); }, true);
  document.addEventListener('error', function (e) { if (e.target && e.target.tagName === 'IMG') mark(e.target); }, true);
  scan();
})();
