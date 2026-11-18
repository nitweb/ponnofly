/* NEBULA MART: floating contact launcher (Call + WhatsApp + Messenger), expands from one button */
(function () {
  'use strict';
  /* Values come from Admin > Site Settings > Chat launcher (window.NEBULA_CHAT, set in the layout) */
  var CFG = window.NEBULA_CHAT || {};
  if (CFG.show === false) return;
  var PHONE_NUMBER = CFG.phone || '';                 // hotline, digits only
  var WHATSAPP_NUMBER = CFG.whatsapp || '';           // country code + number, no + or spaces
  var WHATSAPP_TEXT = 'Hello ' + (CFG.name || 'NEBULA MART') + ', I need help with an order.';
  var MESSENGER_PAGE = CFG.messenger || '';           // facebook page username or ID (m.me/<this>)

  var ICON = {
    chat: '<path fill="none" stroke="currentColor" stroke-width="2" stroke-linejoin="round" d="M5 4h14a2 2 0 0 1 2 2v9a2 2 0 0 1-2 2h-8l-5 4v-4H5a2 2 0 0 1-2-2V6a2 2 0 0 1 2-2z"/>',
    close: '<path fill="none" stroke="currentColor" stroke-width="2.4" stroke-linecap="round" d="M6 6l12 12M18 6 6 18"/>',
    call: '<path fill="currentColor" d="M6.6 10.8a15.1 15.1 0 0 0 6.6 6.6l2.2-2.2a1 1 0 0 1 1-.25c1.1.37 2.3.57 3.6.57a1 1 0 0 1 1 1V20a1 1 0 0 1-1 1C10.6 21 3 13.4 3 4a1 1 0 0 1 1-1h3.5a1 1 0 0 1 1 1c0 1.25.2 2.45.57 3.6a1 1 0 0 1-.25 1z"/>',
    wa: '<path fill="currentColor" d="M12.04 2C6.58 2 2.13 6.45 2.13 11.91c0 1.75.46 3.45 1.32 4.95L2.05 22l5.25-1.38a9.9 9.9 0 0 0 4.74 1.21c5.46 0 9.91-4.45 9.91-9.91S17.5 2 12.04 2zm5.8 14.03c-.25.69-1.44 1.32-1.98 1.37-.5.05-1.13.07-1.82-.11-.42-.13-.96-.31-1.65-.6-2.9-1.25-4.8-4.18-4.94-4.37-.14-.19-1.18-1.57-1.18-3s.75-2.13 1.01-2.42c.26-.29.57-.36.76-.36l.55.01c.18 0 .42-.07.65.5.25.6.84 2.07.91 2.22.07.15.12.32.02.51-.1.19-.15.31-.29.48-.15.17-.31.37-.44.5-.15.15-.3.31-.13.6.17.29.76 1.25 1.63 2.03 1.12 1 2.06 1.31 2.35 1.46.29.14.46.12.63-.07.17-.19.73-.85.92-1.14.19-.29.39-.24.65-.14.26.1 1.67.79 1.96.93.29.14.48.22.55.34.07.12.07.69-.18 1.38z"/>',
    ms: '<path fill="currentColor" d="M12 2C6.48 2 2 6.14 2 11.25c0 2.9 1.45 5.48 3.72 7.17V22l3.4-1.87c.9.25 1.86.39 2.88.39 5.52 0 10-4.14 10-9.27S17.52 2 12 2zm1.07 12.48-2.55-2.72-4.98 2.72 5.48-5.82 2.61 2.72 4.92-2.72-5.48 5.82z"/>'
  };
  function svg(k) { return '<svg viewBox="0 0 24 24" aria-hidden="true">' + ICON[k] + '</svg>'; }
  function item(cls, href, label, icon, dot, ext) {
    return '<a class="chat-fab__item ' + cls + '" href="' + href + '"' + (ext ? ' target="_blank" rel="noopener"' : '') + ' aria-label="' + label + '">' +
      '<span class="chat-fab__tip">' + label + '</span>' + svg(icon) + (dot ? '<i class="chat-fab__dot" aria-hidden="true"></i>' : '') + '</a>';
  }

  /* after an AJAX page swap this script runs again: reuse the existing launcher and only re-attach its listeners */
  var wrap = document.querySelector('.chat-fab');
  var fresh = !wrap;
  if (fresh) { wrap = document.createElement('div'); wrap.className = 'chat-fab'; }
  if (fresh) wrap.innerHTML =
    '<div class="chat-fab__list" id="chat-fab-list" role="group" aria-label="Contact options">' +
    (PHONE_NUMBER ? item('chat-fab__call', 'tel:' + PHONE_NUMBER, 'Call us', 'call', false, false) : '') +
    (WHATSAPP_NUMBER ? item('chat-fab__wa', 'https://wa.me/' + WHATSAPP_NUMBER + '?text=' + encodeURIComponent(WHATSAPP_TEXT), 'WhatsApp', 'wa', false, true) : '') +
    (MESSENGER_PAGE ? item('chat-fab__ms', 'https://m.me/' + encodeURIComponent(MESSENGER_PAGE), 'Messenger', 'ms', false, true) : '') +
    '</div>' +
    '<button type="button" class="chat-fab__toggle" aria-expanded="false" aria-controls="chat-fab-list" aria-label="Contact us">' +
    '<span class="chat-fab__tip chat-fab__tip--hide">Hide</span>' +
    '<span class="chat-fab__ic chat-fab__ic--open">' + svg('chat') + '</span>' +
    '<span class="chat-fab__ic chat-fab__ic--close">' + svg('close') + '</span>' +
    '<i class="chat-fab__dot" aria-hidden="true"></i></button>';
  if (fresh) document.body.appendChild(wrap);

  var btn = wrap.querySelector('.chat-fab__toggle');
  function set(open) {
    wrap.classList.toggle('is-open', open);
    btn.setAttribute('aria-expanded', open);
    btn.setAttribute('aria-label', open ? 'Hide contact options' : 'Contact us');
  }
  btn.addEventListener('click', function () { set(!wrap.classList.contains('is-open')); });
  document.addEventListener('keydown', function (e) { if (e.key === 'Escape' && wrap.classList.contains('is-open')) { set(false); btn.focus(); } });

  /* lift the stack above any sticky mobile bar on the product page */
  if (document.querySelector('[data-sticky-bar], #sticky-cta')) document.documentElement.style.setProperty('--chat-bottom', '5.5rem');
})();
