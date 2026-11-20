/* PonnoFly: product gallery slider (arrows, swipe, thumbnail strip) */
(function () {
  'use strict';
  var root = document.querySelector('[data-gallery]');
  if (!root) return;
  var thumbs = [].slice.call(root.querySelectorAll('.thumb'));
  if (thumbs.length < 2) return;
  var track = root.querySelector('[data-thumbs]');
  var sPrev = root.querySelector('[data-strip-prev]');
  var sNext = root.querySelector('[data-strip-next]');
  var fig = root.querySelector('.pgal__main');

  function current() {
    for (var i = 0; i < thumbs.length; i++) { if (thumbs[i].classList.contains('border-primary')) return i; }
    return 0;
  }
  function go(i) {
    i = (i + thumbs.length) % thumbs.length;
    thumbs[i].click();
    var li = thumbs[i].parentNode;
    track.scrollTo({ left: li.offsetLeft - (track.clientWidth - li.offsetWidth) / 2, behavior: 'smooth' });
  }

  root.querySelector('[data-gal-prev]').addEventListener('click', function (e) { e.stopPropagation(); go(current() - 1); });
  root.querySelector('[data-gal-next]').addEventListener('click', function (e) { e.stopPropagation(); go(current() + 1); });
  sPrev.addEventListener('click', function () { go(current() - 1); });
  sNext.addEventListener('click', function () { go(current() + 1); });

  thumbs.forEach(function (t, i) {
    t.addEventListener('click', function () {
      var li = t.parentNode;
      track.scrollTo({ left: li.offsetLeft - (track.clientWidth - li.offsetWidth) / 2, behavior: 'smooth' });
    });
  });

  /* swipe on touch */
  var x0 = null;
  fig.addEventListener('touchstart', function (e) { x0 = e.touches[0].clientX; }, { passive: true });
  fig.addEventListener('touchend', function (e) {
    if (x0 === null) return;
    var dx = e.changedTouches[0].clientX - x0;
    x0 = null;
    if (Math.abs(dx) > 40) go(current() + (dx < 0 ? 1 : -1));
  }, { passive: true });

  /* keyboard arrows while the image is focused */
  fig.addEventListener('keydown', function (e) {
    if (e.key === 'ArrowRight') { e.preventDefault(); go(current() + 1); }
    else if (e.key === 'ArrowLeft') { e.preventDefault(); go(current() - 1); }
  });
})();
