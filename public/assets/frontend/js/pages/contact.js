/* NEBULA MART — contact form (character counter) */
(function () {
  'use strict';
  var form = document.getElementById('contact-form');
  if (!form) return;
  var ta = form.querySelector('textarea');
  var cnt = document.getElementById('c-count');
  if (ta && cnt) ta.addEventListener('input', function () { cnt.textContent = ta.value.length + ' / ' + ta.maxLength; });
})();
