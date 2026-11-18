/* PONNOFLY: back-to-top button (shows after scrolling down; survives AJAX page swaps) */
(function () {
  'use strict';
  var btn = document.querySelector('.back-top');
  if (!btn) {
    btn = document.createElement('button');
    btn.type = 'button';
    btn.className = 'back-top';
    btn.setAttribute('aria-label', 'Back to top');
    btn.innerHTML = '<svg viewBox="0 0 24 24" aria-hidden="true"><path fill="none" stroke="currentColor" stroke-width="2.4" stroke-linecap="round" stroke-linejoin="round" d="M12 19V5M5 12l7-7 7 7"/></svg>';
    document.body.appendChild(btn);
  }
  var ticking = false;
  function update() {
    ticking = false;
    btn.classList.toggle('is-visible', (window.scrollY || document.documentElement.scrollTop) > 400);
  }
  function queue() { if (!ticking) { ticking = true; requestAnimationFrame(update); } }
  var reduce = window.matchMedia && window.matchMedia('(prefers-reduced-motion: reduce)').matches;
  btn.addEventListener('click', function () { window.scrollTo({ top: 0, left: 0, behavior: reduce ? 'auto' : 'smooth' }); });
  window.addEventListener('scroll', queue, { passive: true });
  update();
})();
