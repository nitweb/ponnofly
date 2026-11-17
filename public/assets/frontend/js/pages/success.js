/* NEBULA MART — order success page */
(function () {
  var timer = null;

  // Fallback for non-secure contexts (http) or older browsers.
  function legacyCopy(text) {
    var ta = document.createElement('textarea');
    ta.value = text;
    ta.setAttribute('readonly', '');
    ta.style.cssText = 'position:fixed;top:0;left:0;opacity:0;';
    document.body.appendChild(ta);
    ta.select();
    ta.setSelectionRange(0, text.length);
    var ok = false;
    try { ok = document.execCommand('copy'); } catch (err) { ok = false; }
    document.body.removeChild(ta);
    return ok;
  }

  function writeClipboard(text) {
    if (navigator.clipboard && navigator.clipboard.writeText && window.isSecureContext) {
      return navigator.clipboard.writeText(text).then(function () { return true; }, function () { return legacyCopy(text); });
    }
    return Promise.resolve(legacyCopy(text));
  }

  function showCopied(btn, original) {
    clearTimeout(timer);
    btn.textContent = '✓ Copied!';
    btn.setAttribute('aria-label', 'Order number copied');
    btn.classList.add('is-copied');
    timer = setTimeout(function () {
      btn.textContent = original;
      btn.removeAttribute('aria-label');
      btn.classList.remove('is-copied');
    }, 2000);
  }

  document.addEventListener('click', function (e) {
    if (e.target.closest('[data-print]')) { window.print(); return; }

    var copy = e.target.closest('#copy');
    if (!copy) return;

    var no = document.getElementById('no');
    if (!no) return;

    var text = no.textContent.trim();
    if (!copy.dataset.label) copy.dataset.label = copy.textContent.trim();

    writeClipboard(text).then(function (ok) {
      if (ok) {
        showCopied(copy, copy.dataset.label);
        if (window.notify) window.notify('Order number ' + text + ' copied!', 'success');
      } else if (window.notify) {
        window.notify('Could not copy. Please copy the order number manually.', 'error');
      }
    });
  });
})();
