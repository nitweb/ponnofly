/* NEBULA MART — contact form (counter, validation, ajax submit + alert) */
(function () {
  'use strict';
  var form = document.getElementById('contact-form');
  if (!form) return;
  var ta = form.querySelector('textarea');
  var cnt = document.getElementById('c-count');
  if (ta && cnt) ta.addEventListener('input', function () { cnt.textContent = ta.value.length + ' / ' + ta.maxLength; });

  var labels = { name: 'Full name', phone: 'Mobile number', email: 'Email address', message: 'Message' };
  var btn = form.querySelector('button[type="submit"]');

  function alertBox(icon, title, html, intro) {
    if (typeof Swal === 'undefined') { window.alert(title + '\n' + html.replace(/<[^>]+>/g, ' ')); return; }
    var ok = icon === 'success';
    Swal.fire({
      icon: icon,
      title: title,
      html: (intro ? '<p class="nm-intro">' + intro + '</p>' : '') + html,
      confirmButtonText: ok ? 'Continue shopping' : 'Fix and retry',
      buttonsStyling: false,
      timer: ok ? 7000 : undefined,
      timerProgressBar: ok,
      customClass: { popup: 'swal-nm swal-nm--' + (ok ? 'ok' : 'err'), htmlContainer: 'nm-body' }
    });
  }

  function clearErrors() {
    [].forEach.call(form.querySelectorAll('.js-err'), function (el) { el.remove(); });
    [].forEach.call(form.querySelectorAll('.is-invalid'), function (el) { el.classList.remove('is-invalid'); });
  }

  function showError(name, text) {
    var input = form.querySelector('[name="' + name + '"]');
    if (!input) return;
    var small = document.createElement('small');
    small.className = 'js-err text-danger text-sm font-bold';
    small.setAttribute('role', 'alert');
    small.textContent = text;
    var fld = input.closest('.fld');
    fld.appendChild(small);
    var box = fld.querySelector('.fld__box');
    if (box) box.classList.add('is-invalid');
  }

  function listHtml(items) {
    return '<ul class="nm-list">' + items.map(function (t) { return '<li>' + t + '</li>'; }).join('') + '</ul>';
  }

  form.addEventListener('submit', function (e) {
    e.preventDefault();
    clearErrors();

    var problems = [];
    ['name', 'phone', 'email', 'message'].forEach(function (n) {
      var el = form.elements[n];
      var v = (el.value || '').trim();
      var msg = '';
      if (!v) msg = labels[n] + ' is required.';
      else if (n === 'email' && !/^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(v)) msg = 'Enter a valid email address.';
      else if (n === 'phone' && !/^(\+?88)?01\d{9}$/.test(v.replace(/[\s-]/g, ''))) msg = 'Enter a valid 11 digit mobile number, like 01XXXXXXXXX.';
      else if (n === 'message' && v.length < 10) msg = 'Message must be at least 10 characters.';
      if (msg) { problems.push(msg); showError(n, msg); }
    });

    if (problems.length) {
      alertBox('error', 'Please fix the form', listHtml(problems), 'A few details are missing or incorrect:');
      var first = form.querySelector('.is-invalid input, .is-invalid textarea'); if (first) first.focus();
      return;
    }

    if (btn) btn.disabled = true;
    fetch(form.action, {
      method: 'POST',
      headers: { 'Accept': 'application/json', 'X-Requested-With': 'XMLHttpRequest' },
      body: new FormData(form),
      credentials: 'same-origin'
    }).then(function (res) {
      return res.json().catch(function () { return {}; }).then(function (data) { return { ok: res.ok, status: res.status, data: data }; });
    }).then(function (r) {
      if (r.ok) {
        form.reset();
        if (cnt) cnt.textContent = '0 / ' + (ta ? ta.maxLength : 500);
        alertBox('success', 'Message sent!', '<div class="nm-note">We reply within one working day</div>', r.data.message || 'Thank you! We will reply within one working day.');
      } else if (r.status === 422 && r.data.errors) {
        var all = [];
        Object.keys(r.data.errors).forEach(function (k) { showError(k, r.data.errors[k][0]); all.push(r.data.errors[k][0]); });
        alertBox('error', 'Please fix the form', listHtml(all), 'A few details are missing or incorrect:');
      } else {
        alertBox('error', 'Something went wrong', 'Please try again in a moment.');
      }
    }).catch(function () {
      alertBox('error', 'Something went wrong', 'Please check your connection and try again.');
    }).then(function () { if (btn) btn.disabled = false; });
  });
})();
