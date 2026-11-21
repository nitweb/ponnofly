/* PONNOFLY — spa.js: AJAX navigation for the whole storefront (no full page reloads) */
(function () {
  'use strict';
  if (!window.fetch || !window.DOMParser || !history.pushState) return;

  var nativeAdd = EventTarget.prototype.addEventListener;
  var nativeSetInterval = window.setInterval;
  var recording = true;
  var recordedListeners = [];
  var recordedIntervals = [];

  /* Record listeners/timers created while page scripts boot, so they can be torn down and re-run after a swap */
  EventTarget.prototype.addEventListener = function (type, fn, opts) {
    if (type === 'DOMContentLoaded' && (this === document || this === window) && document.readyState !== 'loading' && typeof fn === 'function') { var self = this; setTimeout(function () { fn.call(self, new Event('DOMContentLoaded')); }, 0); return; }
    if (recording && fn) recordedListeners.push([this, type, fn, opts]);
    return nativeAdd.call(this, type, fn, opts);
  };
  window.setInterval = function () {
    var id = nativeSetInterval.apply(window, arguments);
    if (recording) recordedIntervals.push(id);
    return id;
  };

  var SWAP = ['#site-header', '#mobile-menu', '#cart-drawer', '#main', 'footer', '#qv'];
  var scripts = [];
  var busy = 0, seq = 0, ctl = null, bar = null, barTimer = null, curPath = location.pathname;

  function own(url) { return url.origin === location.origin; }
  function same(a, b) { return a.pathname === b.pathname && a.search === b.search; }

  nativeAdd.call(document, 'DOMContentLoaded', function () {
    recording = false;
    Array.prototype.forEach.call(document.querySelectorAll('script[src]'), function (s) {
      var src = s.getAttribute('src') || '';
      if (/\/assets\/frontend\/js\//.test(src) && !/spa\.js|electronics-data\.js/.test(src)) scripts.push(src);
    });
    try { history.scrollRestoration = 'manual'; } catch (e) { }
    history.replaceState({ y: 0 }, '', location.href);
  });

  /* ---------- progress bar ---------- */
  function progress(on) {
    if (!bar) { bar = document.createElement('div'); bar.id = 'spa-bar'; document.body.appendChild(bar); }
    clearTimeout(barTimer);
    if (on) { bar.style.width = '0'; bar.classList.add('on'); requestAnimationFrame(function () { bar.style.width = '70%'; }); }
    else { bar.style.width = '100%'; barTimer = setTimeout(function () { bar.classList.remove('on'); bar.style.width = '0'; }, 250); }
  }

  /* ---------- swapping ---------- */
  function teardown() {
    recordedListeners.forEach(function (l) { try { l[0].removeEventListener(l[1], l[2], l[3]); } catch (e) { } });
    recordedIntervals.forEach(function (id) { clearInterval(id); });
    recordedListeners = []; recordedIntervals = [];
  }

  function reexecute(done) {
    var i = 0;
    recording = true;
    (function next() {
      if (i >= scripts.length) { recording = false; done(); return; }
      var src = scripts[i++];
      Array.prototype.forEach.call(document.querySelectorAll('script[src]'), function (o) { if (o.getAttribute('src') === src) o.remove(); });
      var s = document.createElement('script');
      s.src = src;
      s.onload = s.onerror = next;
      document.body.appendChild(s);
    })();
  }

  function runInline(root) {
    Array.prototype.forEach.call(root.querySelectorAll('script'), function (old) {
      var s = document.createElement('script');
      Array.prototype.forEach.call(old.attributes, function (a) { s.setAttribute(a.name, a.value); });
      s.text = old.textContent;
      old.parentNode.replaceChild(s, old);
    });
  }

  function syncHead(doc) {
    if (doc.title) document.title = doc.title;
    ['meta[name="description"]', 'meta[property="og:title"]', 'meta[property="og:description"]', 'meta[property="og:url"]', 'meta[property="og:image"]', 'link[rel="canonical"]'].forEach(function (sel) {
      var n = doc.querySelector(sel), o = document.querySelector(sel);
      if (n && o) { var attr = n.hasAttribute('content') ? 'content' : 'href'; o.setAttribute(attr, n.getAttribute(attr)); }
    });
    var ld = document.querySelectorAll('head script[type="application/ld+json"][data-spa]');
    Array.prototype.forEach.call(ld, function (n) { n.remove(); });
    Array.prototype.forEach.call(doc.querySelectorAll('head script[type="application/ld+json"]'), function (n, i) {
      if (i < 2) return; /* organization + website are identical on every page */
      var s = document.createElement('script'); s.type = 'application/ld+json'; s.setAttribute('data-spa', '1'); s.text = n.textContent; document.head.appendChild(s);
    });
  }

  /* The cart snapshot lives in an inline script outside the swapped regions, so refresh it from the fetched page or the cart/checkout pages render stale (empty) totals. */
  function syncCartSnapshot(doc) {
    if (!window.NEBULA_CART) return;
    var scripts = doc.querySelectorAll('script:not([src])');
    for (var i = 0; i < scripts.length; i++) {
      var t = scripts[i].textContent, k = t.indexOf('window.NEBULA_CART');
      if (k < 0) continue;
      var a = t.indexOf('{', t.indexOf('initial', k));
      if (a < 0) return;
      var depth = 0, inStr = false, esc = false;
      for (var j = a; j < t.length; j++) {
        var c = t[j];
        if (inStr) { if (esc) esc = false; else if (c === '\\') esc = true; else if (c === '"') inStr = false; continue; }
        if (c === '"') inStr = true;
        else if (c === '{') depth++;
        else if (c === '}' && --depth === 0) {
          try { window.NEBULA_CART.initial = JSON.parse(t.slice(a, j + 1)); } catch (e) { }
          return;
        }
      }
      return;
    }
  }

  function swap(doc, finalUrl, opts) {
    if (!doc.querySelector('#main')) return false;
    syncCartSnapshot(doc);
    syncHead(doc);
    document.body.setAttribute('data-page', doc.body.getAttribute('data-page') || '');
    document.body.style.overflow = '';
    teardown();
    SWAP.forEach(function (sel) {
      var n = doc.querySelector(sel), o = document.querySelector(sel);
      if (n && o) o.replaceWith(document.importNode(n, true));
      else if (n && !o && sel === '#qv') document.body.appendChild(document.importNode(n, true));
    });
    runInline(document.querySelector('#main'));
    reexecute(function () {
      var hash = finalUrl.hash && document.getElementById(decodeURIComponent(finalUrl.hash.slice(1)));
      if (hash) hash.scrollIntoView({ behavior: 'instant' });
      else window.scrollTo({ top: opts.y || 0, left: 0, behavior: 'instant' });
      window.dispatchEvent(new Event('scroll'));
      window.dispatchEvent(new CustomEvent('spa:load', { detail: { url: finalUrl.href } }));
    });
    return true;
  }

  /* ---------- navigation ---------- */
  function go(url, o) {
    o = o || {};
    var target = new URL(url, location.href);
    var id = ++seq;
    if (ctl) ctl.abort();
    ctl = window.AbortController ? new AbortController() : null;
    busy++; progress(true);
    var main = document.getElementById('main'); if (main) main.classList.add('spa-busy');
    if (!o.method && !o.keepScroll && target.pathname !== curPath && window.spaSkeleton) { if (!o.pop) window.scrollTo({ top: 0, left: 0, behavior: 'instant' }); window.spaSkeleton.show(target.href); }

    var init = {
      method: o.method || 'GET', credentials: 'same-origin', redirect: 'follow', cache: 'no-store',
      headers: { 'X-Requested-With': 'XMLHttpRequest', 'Accept': 'text/html,application/xhtml+xml' },
      signal: ctl ? ctl.signal : undefined
    };
    if (o.body) init.body = o.body;

    if (!o.pop && o.push !== false) {
      /* remember where we were scrolled before leaving */
      try { history.replaceState({ y: window.scrollY }, '', location.href); } catch (e) { }
    }

    fetch(target.href, init).then(function (r) {
      var ct = r.headers.get('Content-Type') || '';
      var final = new URL(r.url || target.href, location.href);
      if (!own(final) || ct.indexOf('text/html') < 0) throw new Error('hard');
      return r.text().then(function (t) { return { text: t, final: final, redirected: r.redirected, ok: r.ok }; });
    }).then(function (d) {
      if (id !== seq) return;
      var doc = new DOMParser().parseFromString(d.text, 'text/html');
      var keep = o.keepScroll || same(d.final, new URL(location.href));
      var y = o.pop ? o.y : (keep && !o.method ? window.scrollY : 0);
      if (!doc.querySelector('#main')) throw new Error('hard');
      if (o.pop) { /* url already changed by the browser */ }
      else if (o.replace || same(d.final, new URL(location.href)) && d.final.hash === location.hash && o.method) history.replaceState({ y: 0 }, '', d.final.href);
      else history.pushState({ y: 0 }, '', d.final.href);
      curPath = d.final.pathname;
      swap(doc, d.final, { keepScroll: keep, y: y });
    }).catch(function (e) {
      if (id !== seq || (e && e.name === 'AbortError')) return;
      if (o.method && o.method !== 'GET') { /* never replay a POST as a GET */ if (window.Toastify) Toastify({ text: 'Something went wrong. Please try again.', duration: 3000 }).showToast(); return; }
      location.href = target.href;
    }).then(function () {
      if (id !== seq) return;
      busy = 0; progress(false);
      var m = document.getElementById('main'); if (m) m.classList.remove('spa-busy');
      if (window.spaSkeleton) window.spaSkeleton.hide();
    });
  }
  window.spaGo = go;

  function skipLink(a, e) {
    if (e.defaultPrevented || e.button !== 0 || e.metaKey || e.ctrlKey || e.shiftKey || e.altKey) return true;
    if (a.target && a.target !== '_self') return true;
    if (a.hasAttribute('download') || a.hasAttribute('data-no-spa') || a.getAttribute('rel') === 'external') return true;
    var href = a.getAttribute('href');
    if (!href || /^(mailto:|tel:|javascript:|sms:|#)/i.test(href)) return true;
    var u = new URL(a.href, location.href);
    if (!own(u)) return true;
    if (/^\/(admin|storage|livewire|_)/.test(u.pathname)) return true;
    if (/\.(pdf|zip|xml|csv|xlsx?|docx?|jpe?g|png|gif|webp|svg|txt)$/i.test(u.pathname)) return true;
    if (same(u, new URL(location.href)) && u.hash) return true; /* in-page anchor */
    return false;
  }

  nativeAdd.call(document, 'click', function (e) {
    var a = e.target.closest && e.target.closest('a[href]');
    if (!a || skipLink(a, e)) return;
    e.preventDefault();
    go(a.href);
  });

  nativeAdd.call(document, 'submit', function (e) {
    var f = e.target;
    if (e.defaultPrevented || !f || f.tagName !== 'FORM' || f.hasAttribute('data-no-spa')) return;
    if ((f.getAttribute('target') || '_self') !== '_self') return;
    var action = new URL(f.getAttribute('action') || location.href, location.href);
    if (!own(action) || /^\/(admin|livewire|_)/.test(action.pathname)) return;
    var method = (f.getAttribute('method') || 'GET').toUpperCase();
    var sub = e.submitter;
    if (sub && sub.getAttribute('formmethod')) method = sub.getAttribute('formmethod').toUpperCase();
    var data = new FormData(f);
    if (sub && sub.name) data.append(sub.name, sub.value);
    e.preventDefault();
    if (method === 'GET') {
      var qs = new URLSearchParams();
      data.forEach(function (v, k) { if (typeof v === 'string' && v !== '') qs.append(k, v); });
      action.search = qs.toString();
      go(action.href, { keepScroll: f.hasAttribute('data-live') });
    } else {
      go(action.href, { method: 'POST', body: data, replace: true });
    }
  });

  /* live filtering: checkboxes, sort, price (on change) re-render the shop without a reload */
  nativeAdd.call(document, 'change', function (e) {
    var f = e.target.form;
    if (!f || !f.hasAttribute('data-live') || e.target.id === 'shop-q') return;
    if (f.requestSubmit) f.requestSubmit(); else f.dispatchEvent(new Event('submit', { cancelable: true, bubbles: true }));
  });

  nativeAdd.call(window, 'popstate', function (e) {
    go(location.href, { pop: true, y: (e.state && e.state.y) || 0 });
  });
})();
