/* NEBULA MART — wishlist.js: heart buttons saved on the server for the signed-in customer. */
(function () {
  'use strict';

  var cfg = window.NEBULA_WISH;
  if (!cfg) return;

  var meta = document.querySelector('meta[name="csrf-token"]');
  var token = meta ? meta.getAttribute('content') : '';
  var busy = false;

  function toast(message, type) {
    if (window.notify) window.notify(message, type);
  }

  function paint(button, on) {
    button.setAttribute('aria-pressed', on ? 'true' : 'false');
    button.classList.toggle('bg-primary', on);
    button.classList.toggle('text-white', on);
    if (!on && button.classList.contains('w-9')) button.classList.add('bg-white');
    if (on) button.classList.remove('bg-white');
  }

  function badge(count) {
    document.querySelectorAll('[data-wish-badge]').forEach(function (el) {
      el.textContent = count;
      el.hidden = !count;
    });
  }

  function syncOthers(productId, on) {
    document.querySelectorAll('.wish[data-product-id="' + productId + '"]').forEach(function (b) { paint(b, on); });
  }

  document.addEventListener('click', function (e) {
    var button = e.target.closest('.wish');
    if (!button) return;

    var productId = button.getAttribute('data-product-id');
    if (!productId) return;

    if (cfg.guest) {
      toast('Please sign in to save items to your wishlist.', 'error');
      setTimeout(function () { window.spaGo ? window.spaGo(cfg.login) : (window.location.href = cfg.login); }, 900);
      return;
    }

    if (busy) return;
    busy = true;

    fetch(cfg.toggle, {
      method: 'POST',
      credentials: 'same-origin',
      headers: { 'Accept': 'application/json', 'Content-Type': 'application/json', 'X-CSRF-TOKEN': token, 'X-Requested-With': 'XMLHttpRequest' },
      body: JSON.stringify({ product_id: Number(productId) })
    }).then(function (res) {
      if (res.status === 401) { window.spaGo ? window.spaGo(cfg.login) : (window.location.href = cfg.login); return null; }
      return res.json().catch(function () { return {}; }).then(function (data) {
        if (!res.ok) { toast(data.message || 'Something went wrong. Please try again.', 'error'); return; }
        syncOthers(productId, data.wished);
        badge(data.count);
        toast(data.message, 'success');

        var page = document.querySelector('[data-wishlist-page]');
        if (page && !data.wished) {
          var card = button.closest('li');
          if (card) card.remove();
          if (!page.querySelector('ul > li')) {
            var list = page.querySelector('ul');
            if (list) list.remove();
            var empty = page.querySelector('[data-wishlist-empty]');
            if (empty) empty.hidden = false;
          }
        }
      });
    }).catch(function () {
      toast('Network problem. Please check your connection and try again.', 'error');
    }).then(function () { busy = false; });
  });
})();
