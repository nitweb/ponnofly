/* Shop page — mobile filters drawer */
(function () {
  var CLS = 'shop-filters-open';
  var mq = window.matchMedia ? window.matchMedia('(max-width: 1023.98px)') : null;

  function form() { return document.getElementById('shop-form'); }

  function isOpen() {
    var f = form();
    return !!(f && f.classList.contains('is-filters-open'));
  }

  function setOpen(open) {
    var f = form();
    if (!f) { document.documentElement.classList.remove(CLS); return; }
    f.classList.toggle('is-filters-open', open);
    document.documentElement.classList.toggle(CLS, open);
    var btn = f.querySelector('[data-filters-open]');
    if (btn) btn.setAttribute('aria-expanded', open ? 'true' : 'false');
    if (open) {
      var c = f.querySelector('.shop-filters__close');
      if (c) c.focus();
    }
  }

  // On mobile the search box lives in the drawer; on desktop it goes back to the toolbar.
  function placeSearch() {
    var wrap = document.getElementById('shop-q-wrap');
    var home = document.getElementById('shop-q-home');
    var slot = document.getElementById('shop-drawer-search');
    if (!wrap || !home || !slot) return;
    if (mq && mq.matches) {
      if (wrap.parentNode !== slot) slot.appendChild(wrap);
    } else if (wrap.parentNode === slot) {
      home.parentNode.insertBefore(wrap, home.nextSibling);
    }
  }

  setOpen(false);
  placeSearch();

  if (mq) {
    var onChange = function () {
      placeSearch();
      if (!mq.matches) setOpen(false);
    };
    if (mq.addEventListener) mq.addEventListener('change', onChange);
    else if (mq.addListener) mq.addListener(onChange);
  }

  document.addEventListener('click', function (e) {
    if (e.target.closest('[data-filters-open]')) { setOpen(true); return; }
    if (e.target.closest('[data-filters-close]')) setOpen(false);
  });

  document.addEventListener('keydown', function (e) {
    if (e.key === 'Escape' && isOpen()) setOpen(false);
  });

  // While the drawer is open, wait for "Apply filters" instead of re-rendering on every tap.
  document.addEventListener('change', function (e) {
    if (isOpen() && e.target.closest && e.target.closest('.shop-filters')) e.stopPropagation();
  }, true);

  document.addEventListener('submit', function (e) {
    if (e.target && e.target.id === 'shop-form') setOpen(false);
  }, true);
})();
