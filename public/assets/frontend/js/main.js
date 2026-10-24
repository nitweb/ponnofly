/* NEBULA MART — main.js (all pages: menu/drawer, quick view, cart store, page modules) */
(function () {
  'use strict';
  var PAGE = document.body.getAttribute('data-page') || '';
  function run(name, fn) { try { fn() } catch (e) { console.error('[main.js:' + name + ']', e) } }

  /* menu */
  if (document.getElementById('open-menu')) {
    run('menu', function () {
      (function () {
        var menu = document.getElementById('mobile-menu');
        var cart = document.getElementById('cart-drawer');
        var openMenu = document.getElementById('open-menu');
        var openCart = document.getElementById('open-cart');

        function toggle(el, btn, show) {
          el.classList.toggle('hidden', !show);
          document.body.style.overflow = show ? 'hidden' : '';
          if (btn) btn.setAttribute('aria-expanded', show ? 'true' : 'false');
        }

        openMenu.addEventListener('click', function () { toggle(menu, openMenu, true); });
        openCart.addEventListener('click', function () { toggle(cart, openCart, true); });
        menu.querySelectorAll('[data-close-menu]').forEach(function (el) {
          el.addEventListener('click', function () { toggle(menu, openMenu, false); });
        });
        cart.querySelectorAll('[data-close-cart]').forEach(function (el) {
          el.addEventListener('click', function () { toggle(cart, openCart, false); });
        });
        document.addEventListener('keydown', function (e) {
          if (e.key === 'Escape') { toggle(menu, openMenu, false); toggle(cart, openCart, false); }
        });
        document.getElementById('year').textContent = new Date().getFullYear();
      })();
    });
  }

  /* quickview */
  if (document.getElementById('qv') && document.getElementById('toast')) {
    run('quickview', function () {
      (function () {
        var qv = document.getElementById('qv'), tt = document.getElementById('toast'), cb = document.querySelector('#open-cart .bg-accent'), tm;
        function toast(m) { if (window.notify) window.notify(m, 'success') }
        function cq() { qv.classList.add('hidden') }
        document.addEventListener('click', function (e) {
          var b;
          if (b = e.target.closest('.qv')) { var d = b.dataset; qv.querySelector('#qv-img').src = d.img; qv.querySelector('#qv-img').alt = d.name; qv.querySelector('#qv-name').textContent = d.name; qv.querySelector('#qv-price').textContent = d.price; qv.querySelector('#qv-old').textContent = d.old; qv.querySelector('#qv-old').parentElement.classList.toggle('hidden', !d.old); qv.querySelector('#qv-link').href = d.href; var qc = qv.querySelector('#qv-cat'), qa = b.closest('article'), qe = qa && qa.querySelector('p.text-xs'); qc.textContent = qe ? qe.textContent.trim() : ''; qc.hidden = !qc.textContent; var qd = qv.querySelector('#qv-desc'); qd.textContent = d.desc || ''; qd.hidden = !d.desc; qv.classList.remove('hidden') }
          else if (e.target.closest('[data-close-qv]')) cq();
          
        });
        document.addEventListener('keydown', function (e) { if (e.key === 'Escape') cq() })
      })();
    });
  }

  /* store */
  if (true) {
    run('store', function () {
      window.RD = (function () {
        var DEF = [{ id: 'axis-y-glow-serum', name: 'AXIS-Y Dark Spot Correcting Glow Serum – 50ml', img: 'assets/images/products/axis-y-dark-spot-correcting-glow-serum-50ml.webp', variant: 'Size: 50ml', price: 1299, old: 2000, qty: 1 },
        { id: 'cotton-panjabi-oxxo', name: 'OXXO Premium Cotton Panjabi for Men', img: 'assets/images/products/oxxo-premium-cotton-panjabi-for-men.webp', variant: 'Size: L', price: 946, old: 1300, qty: 1 },
        { id: 'the-ordinary-niacinamide', name: 'The Ordinary Niacinamide 10% + Zinc 1% – 30ml', img: 'assets/images/products/the-ordinary-niacinamide-10-percent-plus-zinc-1-percent-30ml.webp', variant: '30ml', price: 1099, old: 1650, qty: 2 }];
        var ZONES = { dhaka: { label: 'Inside Dhaka', fee: 70, eta: '1-2 days', days: 2 }, sub: { label: 'Sub-Dhaka', fee: 100, eta: '2-3 days', days: 3 }, out: { label: 'Outside Dhaka', fee: 150, eta: '3-5 days', days: 5 } };
        function get(k, d) { try { var v = localStorage.getItem(k); return v ? JSON.parse(v) : d } catch (e) { return d } }
        function set(k, v) { try { localStorage.setItem(k, JSON.stringify(v)) } catch (e) { } }
        function tk(n) { return '৳' + Number(n).toLocaleString('en-US') }
        function totals(c, o) { var s = 0, v = 0; c.forEach(function (i) { s += i.price * i.qty; v += (i.old - i.price) * i.qty }); var d = o.coupon === 'NEBULA10' ? Math.round(s * 0.1) : 0, f = c.length ? ZONES[o.zone || 'dhaka'].fee : 0; return { sub: s, save: v, disc: d, fee: f, total: s - d + f } }
        function hdr(c) { var q = 0, s = 0; c.forEach(function (i) { q += i.qty; s += i.qty * i.price }); var b = document.querySelector('#open-cart .bg-accent'), t = document.querySelector('#open-cart > span:last-child'); if (b) b.textContent = q; if (t) t.textContent = tk(s) }
        return { DEF: DEF, ZONES: ZONES, tk: tk, totals: totals, hdr: hdr, cart: function () { return get('rd_cart', DEF) }, saveCart: function (c) { set('rd_cart', c) }, order: function () { return get('rd_order', { zone: 'dhaka', coupon: '' }) }, saveOrder: function (o) { set('rd_order', o) }, last: function () { return get('rd_last', null) }, saveLast: function (v) { set('rd_last', v) } }
      })();
    });
  }

  /* hero */
  if (PAGE === 'home') {
    run('hero', function () {
      (function () {
        var h = document.getElementById('hero'); if (!h) return;
        var t = document.getElementById('hero-track'), sl = [].slice.call(t.children), n = sl.length, i = 0, tm, nt, dots = h.querySelectorAll('[data-dot]');
        /* endless loop: a copy of the last slide before the first, a copy of the first after the last */
        function cl(el) { var c = el.cloneNode(true); c.setAttribute('aria-hidden', 'true'); c.setAttribute('tabindex', '-1'); c.removeAttribute('role'); c.removeAttribute('aria-label'); [].forEach.call(c.querySelectorAll('img'), function (im) { im.removeAttribute('fetchpriority'); im.loading = 'eager' }); return c }
        t.insertBefore(cl(sl[n - 1]), t.firstChild); t.appendChild(cl(sl[0]));
        function place() { t.style.transform = 'translateX(-' + (i + 1) * 100 + '%)' }
        function mark() { var a = ((i % n) + n) % n; sl.forEach(function (s, j) { s.setAttribute('aria-hidden', j !== a) }); dots.forEach(function (d, j) { d.setAttribute('aria-current', j === a); d.classList.toggle('w-10', j === a); d.classList.toggle('w-3', j !== a) }) }
        function jump(k) { t.style.transition = 'none'; i = k; place(); void t.offsetWidth; t.style.transition = ''; mark() }   /* invisible reposition */
        function fix() { clearTimeout(nt); if (i >= n) jump(0); else if (i < 0) jump(n - 1) }
        function go(k) { if (k > n || k < -1) return; i = k; place(); mark(); clearTimeout(nt); nt = setTimeout(fix, 800) }
        t.addEventListener('transitionend', function (e) { if (e.target === t) fix() });
        function stop() { clearInterval(tm) }
        function play() { if (matchMedia('(prefers-reduced-motion: reduce)').matches) return; stop(); tm = setInterval(function () { go(i + 1) }, 5000) }
        h.querySelector('[data-prev]').onclick = function () { go(i - 1) }; h.querySelector('[data-next]').onclick = function () { go(i + 1) };
        dots.forEach(function (d, j) { d.onclick = function () { fix(); go(j) } });
        h.addEventListener('mouseenter', stop); h.addEventListener('mouseleave', play); h.addEventListener('focusin', stop); h.addEventListener('focusout', play); jump(0); play()
      })();
    });
  }

  /* product */
  if (PAGE === 'product') {
    run('product', function () {
      (function () {
        var m = document.getElementById('main-img'), ts = document.querySelectorAll('.thumb');
        ts.forEach(function (t, i) { t.onclick = function () { m.src = t.dataset.full; m.alt = (m.dataset.alt || 'Product') + ', view ' + (i + 1); ts.forEach(function (x) { x.classList.replace('border-primary', 'border-line') }); t.classList.replace('border-line', 'border-primary') } });
        var q = document.getElementById('qty');
        document.getElementById('q-').onclick = function () { q.value = Math.max(1, +q.value - 1) }; document.getElementById('q+').onclick = function () { q.value = Math.max(1, (parseInt(q.value, 10) || 1) + 1) }; q.addEventListener('change', function () { q.value = Math.max(1, parseInt(q.value, 10) || 1) });
        var tabs = [].slice.call(document.querySelectorAll('.tab'));
        function sel(x0) { tabs.forEach(function (x) { var on = x === x0; x.setAttribute('aria-selected', on); x.tabIndex = on ? 0 : -1; x.classList.toggle('bg-primary', on); x.classList.toggle('text-white', on); x.classList.toggle('bg-white', !on); document.getElementById(x.getAttribute('aria-controls')).hidden = !on }) }
        tabs.forEach(function (x, i) { x.onclick = function () { sel(x) }; x.onkeydown = function (e) { var k = e.key === 'ArrowRight' ? 1 : e.key === 'ArrowLeft' ? -1 : 0; if (k) { var n = tabs[(i + k + tabs.length) % tabs.length]; sel(n); n.focus() } } });
      })();
    });
  }

  /* shop: merge static electronics (assets/js/electronics-data.js) into the real products */
  function shopApi(done) {
    var grid = document.getElementById('shop-grid'), fin = false;
    function finish() { if (fin) return; fin = true; counts(); done() }
    function esc(t) { return String(t == null ? '' : t).replace(/[&<>"']/g, function (c) { return { '&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;', "'": '&#39;' }[c] }) }
    function counts() {
      var lis = [].slice.call(grid.children);
      ['cat', 'brand'].forEach(function (n) {
        document.querySelectorAll('input[name="' + n + '"]').forEach(function (i) {
          var c = lis.filter(function (li) { return li.dataset[n] === i.value }).length, lb = i.closest('label'), sp = lb && lb.querySelector('span:last-child');
          if (sp) sp.textContent = c; if (lb) lb.hidden = c === 0
        })
      })
    }
    function li(p, k) {
      var name = esc(p.name), price = Math.round(+p.price) || 0, img = esc(p.image || 'assets/images/placeholder.svg'), href = 'product.html?id=' + encodeURIComponent(p.id), pf = price.toLocaleString('en-US');
      return '<li data-cat="Electronics" data-brand="" data-color="" data-size="" data-price="' + price + '" data-n="' + (10 + k) + '" data-idx="' + (100 + k) + '"><article class="group relative bg-white border border-line rounded-2xl overflow-hidden hover:shadow-md transition-shadow flex flex-col h-full"><div class="relative overflow-hidden bg-primary-soft"><a href="' + href + '" class="block aspect-square" tabindex="-1" aria-hidden="true"><img src="' + img + '" referrerpolicy="no-referrer" alt="' + name + '" width="600" height="600" loading="lazy" class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-500"></a><div class="card-actions"><button type="button" class="wish w-9 h-9 grid place-items-center rounded-full bg-white border border-line hover:bg-primary hover:text-white" aria-label="Add ' + name + ' to wishlist" aria-pressed="false"><svg class="w-5 h-5"><use href="#i-heart" /></svg></button><button type="button" class="qv" aria-label="Quick view ' + name + '" data-name="' + name + '" data-price="' + pf + '" data-old="" data-desc="' + esc(p.description) + '" data-img="' + img + '" data-href="' + href + '"><svg class="w-5 h-5"><use href="#i-eye" /></svg></button></div></div><div class="p-4 flex flex-col flex-1"><p class="text-xs font-bold text-primary">Electronics</p><h3 class="font-bold leading-snug mt-1 line-clamp-2"><a href="' + href + '" class="hover:text-primary">' + name + '</a></h3><p class="ldesc hidden text-sm text-ink/70 mt-2">' + esc(p.description) + '</p><div class="mt-auto pt-3 flex items-baseline gap-2"><span class="font-display font-extrabold text-xl">৳' + pf + '</span></div></div><button type="button" class="addcart card-cart" aria-label="Add ' + name + ' to cart">Add to cart</button></article></li>'
    }
    var t = setTimeout(finish, 10000);
    Promise.resolve({ data: window.NM_ELECTRONICS || [] })
      .then(function (j) {
        var list = Array.isArray(j) ? j : j && (Array.isArray(j.data) ? j.data : j.data && Array.isArray(j.data.data) ? j.data.data : Array.isArray(j.products) ? j.products : null);
        if (!list) throw new Error('Unexpected API response');
        j = { data: list.map(function (p) { return { id: p.id != null ? p.id : p._id, name: p.name || p.title, price: p.price != null ? p.price : p.sale_price, image: p.image || p.thumbnail || (p.images && p.images[0]) || '', description: p.description || '' } }) };
        if (j.data.length && !fin) {
          grid.insertAdjacentHTML('beforeend', j.data.map(li).join(''));
          var mx = Math.max.apply(null, j.data.map(function (p) { return +p.price || 0 })), top = Math.max(5000, Math.ceil(mx / 100) * 100), pr = document.getElementById('prange'), pm = document.getElementById('pmax');
          if (pr) { pr.max = top; pr.value = top } if (pm) pm.value = top;
          grid.querySelectorAll('img').forEach(function (im) { im.addEventListener('error', function () { if (im.src.indexOf('placeholder.svg') < 0) im.src = 'assets/images/placeholder.svg' }, { once: true }) })
        }
      })
      .catch(function (e) {
        console.error('[shop-api]', e);
        if (!document.getElementById('api-note')) grid.insertAdjacentHTML('beforebegin', '<p id="api-note" class="mt-4 bg-accent/40 border border-line rounded-xl px-4 py-3 font-bold">Electronics products load kora jaini (' + esc(e && e.message) + '). <button type="button" class="underline" onclick="location.reload()">Try again</button></p>')
      })
      .then(function () { clearTimeout(t); finish() })
  }

  /* shop */
  if (PAGE === 'shop') {
    run('shop', function () { shopApi(function () {
      (function () {
        var grid = document.getElementById('shop-grid'), items = [].slice.call(grid.children), panel = document.getElementById('filter-panel'), ov = document.getElementById('f-ov'),
          pmin = document.getElementById('pmin'), pmax = document.getElementById('pmax'), pr = document.getElementById('prange'), chipsEl = document.getElementById('chips'),
          more = document.getElementById('more'), shown = 12, step = 6, PM = +document.getElementById('prange').max || 5000, st = {};
        var sorters = { pop: function (a, b) { return b.dataset.n - a.dataset.n }, new: function (a, b) { return b.dataset.idx - a.dataset.idx }, lo: function (a, b) { return a.dataset.price - b.dataset.price }, hi: function (a, b) { return b.dataset.price - a.dataset.price } };
        function vals(n) { return [].map.call(document.querySelectorAll('input[name="' + n + '"]:checked'), function (i) { return i.value }) }
        function read() { st.cat = vals('cat'); st.brand = vals('brand'); st.color = vals('color'); st.size = vals('size'); st.min = +pmin.value || 0; st.max = pmax.value === '' ? 1e9 : +pmax.value }
        function chip(f, v, l) { return '<button type="button" data-f="' + f + '" data-v="' + v + '" class="flex items-center gap-1.5 bg-accent border border-line rounded-full pl-3 pr-2 py-1 text-sm font-bold">' + l + ' <span aria-hidden="true">×</span><span class="sr-only">remove filter</span></button>' }
        function chips() { var h = '';['cat', 'brand', 'color', 'size'].forEach(function (f) { st[f].forEach(function (v) { h += chip(f, v, v) }) }); if (st.min > 0 || st.max < PM) h += chip('price', '', '৳' + st.min + ' to ৳' + st.max); chipsEl.innerHTML = h ? h + '<button type="button" data-clear-all class="underline font-bold text-sm px-2">Clear all</button>' : '' }
        var Q = (new URLSearchParams(location.search).get('q') || '').trim().toLowerCase(), sq = document.getElementById('shop-q'), sqc = document.getElementById('shop-q-clear'), sqt;
        if (sq) sq.value = new URLSearchParams(location.search).get('q') || '';
        function apply() {
          read();
          var list = items.filter(function (li) {
            var d = li.dataset;
            if (Q && Q.split(/\s+/).some(function (t) { return (li.textContent + ' ' + d.cat + ' ' + d.brand).toLowerCase().indexOf(t) < 0 })) return false;
            if (st.cat.length && st.cat.indexOf(d.cat) < 0) return false;
            if (st.brand.length && st.brand.indexOf(d.brand) < 0) return false;
            if (st.color.length && st.color.indexOf(d.color) < 0) return false;
            if (st.size.length && !st.size.some(function (s) { return (' ' + d.size + ' ').indexOf(' ' + s + ' ') >= 0 })) return false;
            if (+d.price < st.min || +d.price > st.max) return false; return true
          });
          list.sort(sorters[document.getElementById('sort').value]);
          items.forEach(function (li) { li.classList.add('hidden') });
          list.forEach(function (li, i) { grid.appendChild(li); li.classList.toggle('hidden', i >= shown) });
          var n = list.length, s = Math.min(shown, n);
          document.getElementById('count').innerHTML = '<span class="shop-count__n">' + n + '</span><span class="shop-count__t">' + (n === 1 ? 'product found' : 'products found') + '</span>';
          document.getElementById('note').textContent = 'Showing ' + s + ' of ' + n + ' products';
          document.getElementById('empty').hidden = n > 0; document.getElementById('more-wrap').hidden = n === 0; more.hidden = shown >= n;
          chips()
        }
        function setQ(v) { Q = v.trim().toLowerCase(); if (sqc) sqc.hidden = !v; shown = 12; apply(); try { var u = new URL(location.href); if (v.trim()) u.searchParams.set('q', v.trim()); else u.searchParams.delete('q'); history.replaceState(null, '', u) } catch (e) { } }
        if (sq) {
          if (sqc) sqc.hidden = !sq.value;
          sq.addEventListener('input', function () { clearTimeout(sqt); sqt = setTimeout(function () { setQ(sq.value) }, 120) });
          sq.addEventListener('keydown', function (e) { if (e.key === 'Escape' && sq.value) { sq.value = ''; setQ('') } });
          sqc.addEventListener('click', function () { sq.value = ''; setQ(''); sq.focus() });
        }
        function clearAll() { if (sq) { sq.value = ''; setQ('') } document.querySelectorAll('#filter-panel input[type=checkbox]').forEach(function (i) { i.checked = false }); pmin.value = 0; pmax.value = PM; pr.value = PM; shown = 12; apply() }
        panel.addEventListener('change', function () { shown = 12; apply() });
        pmin.addEventListener('input', function () { shown = 12; apply() });
        pmax.addEventListener('input', function () { pr.value = pmax.value; shown = 12; apply() });
        pr.addEventListener('input', function () { pmax.value = pr.value; shown = 12; apply() });
        document.getElementById('sort').addEventListener('change', apply);
        (function () {
          var dd = document.getElementById('sort-dd'), btn = document.getElementById('sort-btn'), menu = document.getElementById('sort-menu'), val = document.getElementById('sort-val'), sel = document.getElementById('sort'), opts = [].slice.call(menu.children);
          function open(o) { menu.hidden = !o; btn.setAttribute('aria-expanded', o); dd.classList.toggle('is-open', o) }
          function pick(li) { opts.forEach(function (x) { x.setAttribute('aria-selected', x === li) }); val.textContent = li.textContent; sel.value = li.dataset.v; sel.dispatchEvent(new Event('change')); open(false); btn.focus() }
          btn.addEventListener('click', function () { open(menu.hidden) });
          menu.addEventListener('click', function (e) { var li = e.target.closest('[data-v]'); if (li) pick(li) });
          document.addEventListener('click', function (e) { if (!dd.contains(e.target)) open(false) });
          dd.addEventListener('keydown', function (e) { if (e.key === 'Escape') { open(false); btn.focus() } });
        })();
        more.addEventListener('click', function () { shown += step; apply() });
        document.getElementById('clear').addEventListener('click', clearAll);
        document.addEventListener('click', function (e) {
          if (e.target.closest('[data-clear-all]')) clearAll();
          var b = e.target.closest('#chips [data-f]');
          if (b) { var f = b.dataset.f; if (f === 'price') { pmin.value = 0; pmax.value = PM; pr.value = PM } else document.querySelector('input[name=' + f + '][value="' + b.dataset.v + '"]').checked = false; shown = 12; apply() }
          if (e.target.closest('[data-close-f]')) { panel.classList.remove('open'); ov.classList.add('hidden'); document.body.style.overflow = '' }
        });
        var of = document.getElementById('open-filters'); if (of) of.addEventListener('click', function () { panel.classList.add('open'); ov.classList.remove('hidden'); document.body.style.overflow = 'hidden' });
        var vg = document.getElementById('v-grid'), vl = document.getElementById('v-list');
        function view(list) { grid.classList.toggle('is-list', list);[[vg, !list], [vl, list]].forEach(function (p) { p[0].setAttribute('aria-pressed', p[1]); p[0].classList.toggle('bg-ink', p[1]); p[0].classList.toggle('text-white', p[1]); p[0].classList.toggle('bg-white', !p[1]) }) }
        vg.onclick = function () { view(false) }; vl.onclick = function () { view(true) };
        apply()
      })();
    }); });
  }


  /* orders + addresses */
  if (PAGE === 'orders' || PAGE === 'addresses') {
    run('account', function () {
      var L = RD.last(), $ = function (i) { return document.getElementById(i) };
      if (PAGE === 'orders') {
        if (!L) { $('orders-empty').hidden = false; return }
        $('orders-list').innerHTML = '<article class="bg-white border border-line rounded-3xl p-6"><div class="flex flex-wrap justify-between gap-3"><div><p class="font-bold">Order ' + L.no + '</p><p class="text-sm text-ink/60">' + new Date(L.at).toLocaleDateString('en-GB', { day: 'numeric', month: 'short', year: 'numeric' }) + ' · ' + L.items.length + ' item(s)</p></div><p class="font-display text-xl">' + RD.tk(L.t.total) + '</p></div><div class="mt-4 flex gap-3 flex-wrap"><a class="text-primary font-bold hover:underline" href="track.html">Track order</a><a class="text-primary font-bold hover:underline" href="success.html">View details</a></div></article>'
      }
      else {
        if (!L) { $('addr-empty').hidden = false; return }
        $('addr-list').innerHTML = '<article class="bg-white border border-line rounded-3xl p-6"><p class="font-bold">' + L.name + '</p><p class="mt-1 text-ink/75">' + L.address + ', ' + L.area + ', ' + L.district + ', ' + L.division + '</p><p class="text-sm text-ink/60 mt-1">' + L.phone + '</p></article>'
      }
    });
  }

  /* shop page: AJAX name search (grid + count + pagination), separate from the header search */
  if (PAGE === 'catalog') run('shop-search', function () {
    var form = document.getElementById('shop-form'), q = document.getElementById('shop-q'), box = document.getElementById('shop-results');
    if (!form || !q || !box) return;
    var tm, ctl = null, seq = 0;
    function done() { box.style.opacity = ''; box.removeAttribute('aria-busy') }
    function load(url, scroll) {
      var id = ++seq;
      if (ctl) ctl.abort();
      ctl = window.AbortController ? new AbortController() : null;
      box.setAttribute('aria-busy', 'true'); box.style.opacity = '.5';
      fetch(url, { headers: { 'Accept': 'application/json', 'X-Requested-With': 'XMLHttpRequest' }, credentials: 'same-origin', signal: ctl ? ctl.signal : undefined })
        .then(function (r) { if (!r.ok) throw new Error(r.status); return r.json() })
        .then(function (d) { if (id !== seq) return; box.innerHTML = d.html; done(); if (scroll) box.scrollIntoView({ behavior: 'smooth', block: 'start' }) })
        .catch(function (e) { if (id !== seq || (e && e.name === 'AbortError')) return; done() });
    }
    function search(scroll) { load((form.getAttribute('action') || location.pathname) + '?' + new URLSearchParams(new FormData(form)).toString(), scroll) }
    q.addEventListener('input', function () { clearTimeout(tm); tm = setTimeout(search, 250) });
    q.addEventListener('keydown', function (e) { if (e.key === 'Enter') { e.preventDefault(); clearTimeout(tm); search() } });
    box.addEventListener('click', function (e) {
      var a = e.target.closest('nav[aria-label=Pagination] a'); if (!a) return;
      e.preventDefault(); load(a.href, true);
    });
  });

  /* live search (AJAX: asks /search/suggest as you type) */
  run('search', function () {
    var forms = document.querySelectorAll('form[role=search]'); if (!forms.length) return;
    var qs = new URLSearchParams(location.search).get('q') || '';
    function esc(s) { return String(s).replace(/[&<>"']/g, function (c) { return { '&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;', "'": '&#39;' }[c] }) }
    function rx(s) { return s.replace(/[.*+?^${}()|[\]\\]/g, '\\$&') }
    forms.forEach(function (form) {
      var input = form.querySelector('input[type=search]'), pop = document.createElement('div'), act = -1, tm, seq = 0, ctl = null, cache = {};
      var api = form.getAttribute('data-suggest-url'), shop = form.getAttribute('action') || '/search';
      if (!input || !api) return;
      pop.className = 's-pop'; pop.hidden = true; pop.setAttribute('role', 'listbox'); form.appendChild(pop);
      input.setAttribute('autocomplete', 'off'); input.setAttribute('role', 'combobox'); input.setAttribute('aria-expanded', 'false'); input.setAttribute('aria-controls', input.id + '-list'); pop.id = input.id + '-list';
      if (qs && PAGE === 'search') input.value = qs;
      function shopUrl(q) { return shop + (shop.indexOf('?') < 0 ? '?' : '&') + 'q=' + encodeURIComponent(q) }
      function show(h) { pop.innerHTML = h; pop.hidden = false; act = -1; input.setAttribute('aria-expanded', 'true') }
      function hide() { pop.hidden = true; act = -1; input.setAttribute('aria-expanded', 'false') }
      function go(q) { location.href = shopUrl(q) }
      function fetchHits(q) {
        if (cache[q]) return Promise.resolve(cache[q]);
        if (ctl) ctl.abort();
        ctl = window.AbortController ? new AbortController() : null;
        return fetch(api + (api.indexOf('?') < 0 ? '?' : '&') + 'q=' + encodeURIComponent(q), { headers: { 'Accept': 'application/json', 'X-Requested-With': 'XMLHttpRequest' }, credentials: 'same-origin', signal: ctl ? ctl.signal : undefined })
          .then(function (r) { if (!r.ok) throw new Error(r.status); return r.json() })
          .then(function (d) { return (cache[q] = d) });
      }
      function render(q, d) {
        var hit = d.products || [], toks = q.toLowerCase().split(/\s+/).filter(Boolean), re = new RegExp('(' + toks.map(rx).join('|') + ')', 'gi');
        if (!hit.length) { show('<div class="s-msg">No products found for “' + esc(q) + '”</div>'); return }
        function hl(t) { return String(t).split(re).map(function (p, i) { return i % 2 ? '<mark>' + esc(p) + '</mark>' : esc(p) }).join('') }
        show(hit.map(function (p) {
          return '<a class="s-item" role="option" href="' + esc(p.url) + '"><img src="' + esc(p.image) + '" alt="" width="52" height="52"><span class="s-t"><span class="s-n">' + hl(p.name) + '</span><span class="s-p">' + esc(p.price) + '</span><span class="s-c">' + hl(p.brand || p.category || '') + '</span></span></a>';
        }).join('') + '<a class="s-all" href="' + esc(shopUrl(q)) + '">See all ' + d.total + ' result' + (d.total === 1 ? '' : 's') + ' for “' + esc(q) + '”</a>');
      }
      function run2() {
        var q = input.value.trim(), id = ++seq;
        if (q.length < 2) { if (ctl) ctl.abort(); hide(); return }
        if (!cache[q]) show('<div class="s-msg">Searching…</div>');
        fetchHits(q).then(function (d) { if (id === seq) render(q, d) }).catch(function (e) {
          if (id !== seq || (e && e.name === 'AbortError')) return;
          show('<a class="s-all" href="' + esc(shopUrl(q)) + '">Search for “' + esc(q) + '” in shop</a>');
        });
      }
      input.addEventListener('input', function () { clearTimeout(tm); tm = setTimeout(run2, 200) });
      input.addEventListener('focus', function () { if (input.value.trim() && pop.innerHTML) pop.hidden = false });
      input.addEventListener('keydown', function (e) {
        var its = pop.querySelectorAll('.s-item');
        if (e.key === 'Escape') { hide(); return }
        if ((e.key === 'ArrowDown' || e.key === 'ArrowUp') && its.length) {
          e.preventDefault(); act = (act + (e.key === 'ArrowDown' ? 1 : -1) + its.length) % its.length;
          its.forEach(function (el, i) { el.classList.toggle('on', i === act) }); its[act].scrollIntoView({ block: 'nearest' });
        }
      });
      form.addEventListener('submit', function (e) {
        e.preventDefault();
        var on = pop.querySelector('.s-item.on'); if (on) { location.href = on.getAttribute('href'); return }
        var q = input.value.trim(); if (q) go(q);
      });
      document.addEventListener('click', function (e) { if (!form.contains(e.target)) hide() });
    });
  });


  /* image fallback */
  document.addEventListener('error', function (e) { var t = e.target; if (t && t.tagName === 'IMG' && !t.dataset.fb) { t.dataset.fb = '1'; t.src = 'assets/images/placeholder.svg' } }, true);
})();