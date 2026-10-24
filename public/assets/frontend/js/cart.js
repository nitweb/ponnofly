/* NEBULA MART — cart.js: live cart (header, drawer, floating button, cart page). The server owns the cart. */
(function () {
  'use strict';

  var cfg = window.NEBULA_CART;
  if (!cfg) return;

  var PAGE = document.body.getAttribute('data-page') || '';
  var meta = document.querySelector('meta[name="csrf-token"]');
  var token = meta ? meta.getAttribute('content') : '';
  var state = cfg.initial;
  var fab = null;
  var toastTimer;

  function $(id) { return document.getElementById(id); }
  function esc(v) { return String(v == null ? '' : v).replace(/[&<>"']/g, function (c) { return { '&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;', "'": '&#39;' }[c]; }); }
  function money(n) {
    n = Number(n) || 0;
    var whole = Math.round(n * 100) % 100 === 0;
    return '৳' + n.toLocaleString('en-US', { minimumFractionDigits: whole ? 0 : 2, maximumFractionDigits: whole ? 0 : 2 });
  }

  function toast(message, type) {
    if (window.notify) window.notify(message, type);
  }

  function request(method, url, body) {
    return fetch(url, {
      method: method,
      credentials: 'same-origin',
      headers: { 'Accept': 'application/json', 'Content-Type': 'application/json', 'X-CSRF-TOKEN': token, 'X-Requested-With': 'XMLHttpRequest' },
      body: body ? JSON.stringify(body) : undefined
    }).then(function (res) {
      return res.json().catch(function () { return {}; }).then(function (data) {
        if (data.cart) render(data.cart);
        if (res.ok) return { ok: true, message: data.message || '' };
        var first = data.errors && Object.keys(data.errors).length ? data.errors[Object.keys(data.errors)[0]][0] : '';
        return { ok: false, message: data.message && res.status !== 422 ? data.message : (first || data.message || 'Something went wrong. Please try again.') };
      });
    }).catch(function () {
      return { ok: false, message: 'Network problem. Please check your connection and try again.' };
    });
  }

  /* ---------- rendering ---------- */

  function renderHeader(c) {
    var badge = document.querySelector('[data-cart-badge]');
    var total = document.querySelector('[data-cart-total]');
    var open = $('open-cart');
    if (badge) badge.textContent = c.count;
    if (total) total.textContent = money(c.subtotal);
    if (open) open.setAttribute('aria-label', 'Open cart, ' + c.count + (c.count === 1 ? ' item' : ' items'));
    if (fab) {
      fab.querySelector('.fc-n').textContent = c.count + (c.count === 1 ? ' Item' : ' Items');
      fab.querySelector('.fc-total').textContent = money(c.subtotal);
    }
  }

  function stepper(line, size) {
    var small = size === 'sm';
    var btn = small ? 'px-2.5 py-1' : 'px-3.5 py-2';
    var atMax = line.quantity >= line.max_quantity;
    return '<div class="flex items-center border border-line ' + (small ? 'rounded-lg text-sm' : 'rounded-xl') + ' font-bold">' +
      '<button type="button" data-cart-qty="' + esc(line.key) + '" data-delta="-1" class="' + btn + '" aria-label="Decrease quantity"' + (line.quantity <= 1 ? ' disabled style="opacity:.4;cursor:not-allowed"' : '') + '>−</button>' +
      '<span class="' + (small ? 'px-2' : 'px-3 min-w-8 text-center') + '">' + line.quantity + '</span>' +
      '<button type="button" data-cart-qty="' + esc(line.key) + '" data-delta="1" class="' + btn + '" aria-label="Increase quantity"' + (atMax ? ' disabled style="opacity:.4;cursor:not-allowed"' : '') + '>+</button></div>';
  }

  function renderDrawer(c) {
    var drawer = $('cart-drawer');
    if (!drawer) return;
    var count = drawer.querySelector('[data-cart-count]');
    var list = drawer.querySelector('[data-cart-list]');
    var sub = drawer.querySelector('[data-cart-subtotal]');
    var checkout = drawer.querySelector('[data-cart-checkout]');
    if (count) count.textContent = '(' + c.count + ')';
    if (sub) sub.textContent = money(c.subtotal);
    if (checkout) {
      checkout.setAttribute('href', c.checkout_url);
      checkout.style.opacity = c.can_checkout ? '' : '.5';
      checkout.style.pointerEvents = c.can_checkout ? '' : 'none';
    }
    if (!list) return;
    list.innerHTML = c.items.length ? c.items.map(function (i) {
      return '<li class="flex gap-3 p-3 rounded-2xl border border-line">' +
        '<a href="' + esc(i.url) + '" class="shrink-0"><img src="' + esc(i.image) + '" alt="' + esc(i.name) + '" width="80" height="80" class="w-20 h-20 rounded-xl object-cover border border-line shrink-0"></a>' +
        '<div class="flex-1 min-w-0"><div class="flex justify-between gap-2">' +
        '<a href="' + esc(i.url) + '" class="font-bold leading-snug">' + esc(i.name) + '</a>' +
        '<button type="button" data-cart-remove="' + esc(i.key) + '" class="self-start p-1 rounded-lg hover:bg-primary hover:text-white" aria-label="Remove ' + esc(i.name) + '"><svg class="w-4 h-4"><use href="#i-x"/></svg></button></div>' +
        (i.variant ? '<p class="text-sm text-ink/60">' + esc(i.variant) + '</p>' : '') +
        '<div class="flex items-center justify-between mt-2">' + stepper(i, 'sm') + '<span class="font-extrabold">' + money(i.line_total) + '</span></div></div></li>';
    }).join('') : '<li class="text-center py-10 font-bold text-ink/60">Your cart is empty.</li>';
  }

  function renderPage(c) {
    if (PAGE !== 'cart' || !$('items')) return;
    $('cart-wrap').hidden = !c.items.length;
    $('cart-empty').hidden = c.items.length > 0;
    $('count').textContent = '(' + c.count + (c.count === 1 ? ' item)' : ' items)');

    $('items').innerHTML = c.items.map(function (i) {
      return '<li class="flex gap-4 bg-white border border-line rounded-2xl p-4">' +
        '<a href="' + esc(i.url) + '" class="shrink-0"><img src="' + esc(i.image) + '" alt="' + esc(i.name) + '" width="300" height="300" class="w-24 h-24 sm:w-28 sm:h-28 rounded-xl object-cover border border-line"></a>' +
        '<div class="flex-1 min-w-0"><div class="flex justify-between gap-3"><div>' +
        '<h3 class="font-bold leading-snug"><a href="' + esc(i.url) + '" class="hover:text-primary">' + esc(i.name) + '</a></h3>' +
        (i.variant ? '<p class="text-sm text-ink/60 mt-0.5">' + esc(i.variant) + '</p>' : '') +
        (i.is_flash ? '<p class="text-xs font-bold text-primary mt-1">Flash deal price</p>' : '') +
        (i.max_quantity <= 5 ? '<p class="text-xs font-bold text-primary mt-1">Only ' + i.max_quantity + ' available</p>' : '') +
        '</div><button type="button" data-cart-remove="' + esc(i.key) + '" class="self-start p-1.5 rounded-lg hover:bg-primary hover:text-white" aria-label="Remove ' + esc(i.name) + '"><svg class="w-5 h-5"><use href="#i-x"/></svg></button></div>' +
        '<div class="flex flex-wrap items-end justify-between gap-3 mt-3">' + stepper(i, 'lg') +
        '<div class="text-right"><p class="font-display font-extrabold text-xl">' + money(i.line_total) + '</p>' +
        '<p class="text-xs text-ink/55">' + money(i.unit_price) + ' each' + (i.old_price ? ' <s class="ml-1">' + money(i.old_price) + '</s>' : '') + '</p></div></div></div></li>';
    }).join('');

    $('t-sub').textContent = money(c.subtotal);
    $('t-save').textContent = '-' + money(c.savings);
    $('r-save').hidden = !c.savings;
    $('r-disc').hidden = !c.coupon || (!c.discount && !c.coupon.free_delivery);
    $('r-disc-label').textContent = c.coupon ? 'Coupon ' + c.coupon.code : 'Coupon';
    $('t-disc').textContent = c.coupon && c.coupon.free_delivery ? 'Free delivery' : '-' + money(c.discount);
    $('t-fee').textContent = c.shipping.free ? 'Free' : money(c.shipping.fee);
    $('t-total').textContent = money(c.total);

    document.querySelectorAll('input[name=zone]').forEach(function (r) { r.checked = r.value === c.shipping.zone; });

    var coupon = $('coupon');
    if (c.coupon) {
      coupon.value = c.coupon.code;
      $('cmsg').innerHTML = '<span class="text-success font-bold">' + esc(c.coupon.code) + ' applied: ' + esc(c.coupon.label) + '.</span> <button type="button" id="rmc" class="underline">Remove</button>';
    } else if ($('cmsg').querySelector('#rmc')) {
      coupon.value = '';
      $('cmsg').textContent = '';
    }

    var note = $('cart-note');
    if (c.min_order && !c.meets_min_order) note.textContent = 'The minimum order is ' + money(c.min_order) + '. Add ' + money(c.min_order - c.subtotal) + ' more to check out.';
    else if (c.free_delivery_remaining > 0) note.textContent = 'Add ' + money(c.free_delivery_remaining) + ' more to get free delivery.';
    else if (c.shipping.free) note.textContent = 'You get free delivery on this order.';
    else note.textContent = '';
    note.hidden = !note.textContent;

    var go = $('checkout-btn');
    go.setAttribute('href', c.checkout_url);
    go.setAttribute('aria-disabled', c.can_checkout ? 'false' : 'true');
    go.style.opacity = c.can_checkout ? '' : '.5';
    go.style.pointerEvents = c.can_checkout ? '' : 'none';
  }

  function render(c) {
    state = c;
    renderHeader(c);
    renderDrawer(c);
    renderPage(c);
    if (c.notices && c.notices.length) toast(c.notices.join(' '), 'error');
  }

  /* ---------- actions ---------- */

  function add(productId, variantId, quantity) {
    return request('POST', cfg.routes.add, {
      product_id: Number(productId),
      variant_id: Number(variantId) || null,
      quantity: Math.max(1, parseInt(quantity, 10) || 1)
    }).then(function (result) {
      toast(result.message, result.ok ? 'success' : 'error');
      if (result.ok && fab) { fab.classList.remove('fc-pulse'); void fab.offsetWidth; fab.classList.add('fc-pulse'); }
      return result;
    });
  }

  window.NebulaCart = { add: add, state: function () { return state; }, refresh: function () { return request('GET', cfg.routes.summary); } };

  function line(key) {
    return state.items.filter(function (i) { return i.key === key; })[0];
  }

  /* quick view: remember which product the modal shows */
  document.addEventListener('click', function (e) {
    var qv = e.target.closest('.qv');
    var modal = $('qv');
    if (qv && modal) {
      modal.dataset.productId = qv.dataset.id || '';
      modal.dataset.hasVariants = qv.dataset.hasVariants || '0';
    }
  }, true);

  document.addEventListener('click', function (e) {
    var b = e.target.closest('.addcart');
    if (b) {
      var modal = b.closest('#qv');
      var id = b.dataset.productId || (modal && modal.dataset.productId);
      if (!id) return;
      if (modal && modal.dataset.hasVariants === '1') { location.href = $('qv-link').href; return; }
      if (b.dataset.busy) return;
      b.dataset.busy = '1';
      var qty = b.id === 'add-main' && $('qty') ? $('qty').value : 1;
      add(id, b.dataset.variantId, qty).then(function () { delete b.dataset.busy; });
      return;
    }

    b = e.target.closest('[data-cart-remove]');
    if (b) { request('DELETE', cfg.routes.item + '/' + b.dataset.cartRemove).then(function (r) { toast(r.message, r.ok === false ? 'error' : 'success'); }); return; }

    b = e.target.closest('[data-cart-qty]');
    if (b) {
      var current = line(b.dataset.cartQty);
      if (!current) return;
      var next = current.quantity + Number(b.dataset.delta);
      if (next < 1) return;
      if (next > current.max_quantity) { toast('Only ' + current.max_quantity + ' available.', 'error'); return; }
      request('PATCH', cfg.routes.item + '/' + b.dataset.cartQty, { quantity: next }).then(function (r) { if (!r.ok) toast(r.message, 'error'); });
      return;
    }

    if (PAGE !== 'cart') return;

    if (e.target.id === 'rmc') {
      request('DELETE', cfg.routes.coupon).then(function () { $('coupon').value = ''; $('cmsg').textContent = ''; });
    } else if (e.target.closest('#apply')) {
      var code = $('coupon').value.trim();
      if (!code) { $('cmsg').innerHTML = '<span class="text-danger font-bold">Enter a coupon code.</span>'; return; }
      request('POST', cfg.routes.coupon, { code: code }).then(function (r) {
        if (!r.ok) $('cmsg').innerHTML = '<span class="text-danger font-bold">' + esc(r.message) + '</span>';
      });
    }
  });

  document.addEventListener('change', function (e) {
    if (PAGE === 'cart' && e.target.name === 'zone') {
      request('PUT', cfg.routes.zone, { zone: e.target.value }).then(function (r) { if (!r.ok) toast(r.message, 'error'); });
    }
  });

  if (PAGE === 'cart') {
    document.addEventListener('keydown', function (e) {
      if (e.key === 'Enter' && e.target.id === 'coupon') { e.preventDefault(); $('apply').click(); }
    });
  }

  /* ---------- floating cart button ---------- */
  if (['cart', 'checkout', 'success'].indexOf(PAGE) < 0) {
    fab = document.createElement('button');
    fab.type = 'button';
    fab.id = 'float-cart';
    fab.setAttribute('aria-label', 'Open cart');
    fab.innerHTML = '<span class="fc-top"><svg class="fc-ico" aria-hidden="true"><use href="#i-bag"/></svg><span class="fc-n">0 Items</span></span><span class="fc-total">৳0</span>';
    document.body.appendChild(fab);
    fab.addEventListener('click', function () { var open = $('open-cart'); if (open) open.click(); });
  }

  render(state);
})();
