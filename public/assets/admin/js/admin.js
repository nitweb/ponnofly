/* NEBULA MART ADMIN — UI behaviour (sidebar, dropdowns, tabs, modals, table tools, charts, toasts). No dependencies. */
(function () {
  var P = {
    home: '<path d="m3 11 9-8 9 8"/><path d="M5 10v10h5v-6h4v6h5V10"/>',
    box: '<path d="M21 8 12 3 3 8v8l9 5 9-5z"/><path d="m3 8 9 5 9-5M12 13v8"/>',
    layers: '<path d="m12 3 9 5-9 5-9-5z"/><path d="m3 13 9 5 9-5"/>',
    award: '<circle cx="12" cy="9" r="6"/><path d="m8.5 14-1.5 7 5-3 5 3-1.5-7"/>',
    cart: '<circle cx="9" cy="20" r="1.5"/><circle cx="18" cy="20" r="1.5"/><path d="M2 3h3l2.7 12.4a2 2 0 0 0 2 1.6h7.7a2 2 0 0 0 2-1.5L21 8H6"/>',
    users: '<circle cx="9" cy="8" r="4"/><path d="M2 21a7 7 0 0 1 14 0M17 4a4 4 0 0 1 0 8M22 21a7 7 0 0 0-4-6.3"/>',
    user: '<circle cx="12" cy="8" r="4"/><path d="M4 21a8 8 0 0 1 16 0"/>',
    ticket: '<path d="M3 9a2 2 0 0 0 0 6v3a1 1 0 0 0 1 1h16a1 1 0 0 0 1-1v-3a2 2 0 0 1 0-6V6a1 1 0 0 0-1-1H4a1 1 0 0 0-1 1z"/><path d="M13 5v14" stroke-dasharray="2 3"/>',
    zap: '<path d="M13 2 4 14h7l-1 8 9-12h-7z"/>',
    image: '<rect x="3" y="3" width="18" height="18" rx="3"/><circle cx="9" cy="9" r="2"/><path d="m21 15-5-5L5 21"/>',
    file: '<path d="M14 3H7a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h10a2 2 0 0 0 2-2V8z"/><path d="M14 3v5h5M9 13h6M9 17h6"/>',
    help: '<circle cx="12" cy="12" r="9"/><path d="M9.5 9a2.5 2.5 0 1 1 3.5 2.3c-.7.4-1 .9-1 1.7M12 17h.01"/>',
    mail: '<rect x="3" y="5" width="18" height="14" rx="3"/><path d="m3 8 9 6 9-6"/>',
    chart: '<path d="M3 3v18h18"/><path d="M7 15v3M12 10v8M17 6v12"/>',
    settings: '<circle cx="12" cy="12" r="3"/><path d="M19.4 15a1.7 1.7 0 0 0 .3 1.8l.1.1a2 2 0 1 1-2.8 2.8l-.1-.1a1.7 1.7 0 0 0-1.8-.3 1.7 1.7 0 0 0-1 1.5V21a2 2 0 1 1-4 0v-.1a1.7 1.7 0 0 0-1.1-1.5 1.7 1.7 0 0 0-1.8.3l-.1.1a2 2 0 1 1-2.8-2.8l.1-.1a1.7 1.7 0 0 0 .3-1.8 1.7 1.7 0 0 0-1.5-1H3a2 2 0 1 1 0-4h.1a1.7 1.7 0 0 0 1.5-1.1 1.7 1.7 0 0 0-.3-1.8l-.1-.1a2 2 0 1 1 2.8-2.8l.1.1a1.7 1.7 0 0 0 1.8.3H9a1.7 1.7 0 0 0 1-1.5V3a2 2 0 1 1 4 0v.1a1.7 1.7 0 0 0 1 1.5 1.7 1.7 0 0 0 1.8-.3l.1-.1a2 2 0 1 1 2.8 2.8l-.1.1a1.7 1.7 0 0 0-.3 1.8V9a1.7 1.7 0 0 0 1.5 1H21a2 2 0 1 1 0 4h-.1a1.7 1.7 0 0 0-1.5 1z"/>',
    logout: '<path d="M9 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h4M16 17l5-5-5-5M21 12H9"/>',
    search: '<circle cx="11" cy="11" r="7"/><path d="m21 21-4.3-4.3"/>',
    bell: '<path d="M6 8a6 6 0 1 1 12 0c0 7 3 9 3 9H3s3-2 3-9M10 21a2 2 0 0 0 4 0"/>',
    plus: '<path d="M12 5v14M5 12h14"/>',
    edit: '<path d="M12 20h9"/><path d="M16.5 3.5a2.1 2.1 0 0 1 3 3L7 19l-4 1 1-4z"/>',
    trash: '<path d="M3 6h18M8 6V4a1 1 0 0 1 1-1h6a1 1 0 0 1 1 1v2M19 6l-1 14a2 2 0 0 1-2 2H8a2 2 0 0 1-2-2L5 6M10 11v6M14 11v6"/>',
    eye: '<path d="M2 12s3.6-7 10-7 10 7 10 7-3.6 7-10 7S2 12 2 12z"/><circle cx="12" cy="12" r="3"/>',
    'chev-down': '<path d="m6 9 6 6 6-6"/>', 'chev-right': '<path d="m9 6 6 6-6 6"/>', 'chev-left': '<path d="m15 6-6 6 6 6"/>',
    menu: '<path d="M3 6h18M3 12h18M3 18h18"/>', x: '<path d="M18 6 6 18M6 6l12 12"/>',
    upload: '<path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4M17 8l-5-5-5 5M12 3v12"/>',
    check: '<path d="m5 12 5 5L20 7"/>', 'check-c': '<circle cx="12" cy="12" r="9"/><path d="m8 12 3 3 5-6"/>',
    truck: '<path d="M2 6h12v10H2zM14 10h4l4 3v3h-8"/><circle cx="7" cy="18" r="2"/><circle cx="17" cy="18" r="2"/>',
    dollar: '<path d="M12 2v20M17 6.5C16 5 14.5 4.5 12 4.5c-3 0-4.5 1.3-4.5 3.2 0 5 9.5 2.3 9.5 7.3 0 2-1.8 3.5-5 3.5-2.5 0-4.2-.8-5.2-2.6"/>',
    up: '<path d="m3 17 6-6 4 4 8-8"/><path d="M15 7h6v6"/>', down: '<path d="m3 7 6 6 4-4 8 8"/><path d="M15 17h6v-6"/>',
    star: '<path d="m12 2 3 6.5 7 .9-5.2 4.8 1.4 7L12 17.7 5.8 21.2l1.4-7L2 9.4l7-.9z"/>',
    filter: '<path d="M3 5h18l-7 8v6l-4 2v-8z"/>', download: '<path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4M7 10l5 5 5-5M12 15V3"/>',
    globe: '<circle cx="12" cy="12" r="9"/><path d="M3 12h18M12 3a14 14 0 0 1 0 18M12 3a14 14 0 0 0 0 18"/>',
    lock: '<rect x="4" y="11" width="16" height="10" rx="3"/><path d="M8 11V7a4 4 0 0 1 8 0v4"/>',
    shield: '<path d="M12 3 4 6v6c0 5 3.4 8 8 9 4.6-1 8-4 8-9V6z"/><path d="m9 12 2 2 4-4"/>',
    card: '<rect x="2" y="5" width="20" height="14" rx="3"/><path d="M2 10h20"/>',
    pin: '<path d="M12 21s7-6 7-11a7 7 0 0 0-14 0c0 5 7 11 7 11z"/><circle cx="12" cy="10" r="2.5"/>',
    phone: '<path d="M22 17v3a2 2 0 0 1-2.2 2A19 19 0 0 1 2 4.2 2 2 0 0 1 4 2h3a2 2 0 0 1 2 1.7c.1 1 .4 1.9.7 2.8a2 2 0 0 1-.5 2.1L8 9.9a16 16 0 0 0 6 6l1.3-1.2a2 2 0 0 1 2.1-.4c.9.3 1.8.6 2.8.7A2 2 0 0 1 22 17z"/>',
    link: '<path d="M10 13a5 5 0 0 0 7 0l3-3a5 5 0 0 0-7-7l-1 1M14 11a5 5 0 0 0-7 0l-3 3a5 5 0 0 0 7 7l1-1"/>',
    clock: '<circle cx="12" cy="12" r="9"/><path d="M12 7v5l3 2"/>', printer: '<path d="M6 9V3h12v6M6 18H4a1 1 0 0 1-1-1v-6a2 2 0 0 1 2-2h14a2 2 0 0 1 2 2v6a1 1 0 0 1-1 1h-2"/><rect x="6" y="14" width="12" height="7"/>',
    dots: '<circle cx="12" cy="5" r="1.2"/><circle cx="12" cy="12" r="1.2"/><circle cx="12" cy="19" r="1.2"/>',
    copy: '<rect x="9" y="9" width="12" height="12" rx="2"/><path d="M5 15H4a1 1 0 0 1-1-1V4a1 1 0 0 1 1-1h10a1 1 0 0 1 1 1v1"/>',
    refresh: '<path d="M21 12a9 9 0 0 1-15.5 6.2L3 16M3 12a9 9 0 0 1 15.5-6.2L21 8M21 3v5h-5M3 21v-5h5"/>',
    tag: '<path d="M20.6 13.4 13.4 20.6a2 2 0 0 1-2.8 0L3 13V3h10l7.6 7.6a2 2 0 0 1 0 2.8z"/><circle cx="7.5" cy="7.5" r="1.2"/>',
    reply: '<path d="M9 14 4 9l5-5"/><path d="M4 9h10a6 6 0 0 1 6 6v3"/>', archive: '<rect x="3" y="3" width="18" height="5" rx="1"/><path d="M5 8v11a2 2 0 0 0 2 2h10a2 2 0 0 0 2-2V8M10 12h4"/>',
    alert: '<path d="M10.3 3.9 1.8 18a2 2 0 0 0 1.7 3h17a2 2 0 0 0 1.7-3L13.7 3.9a2 2 0 0 0-3.4 0zM12 9v4M12 17h.01"/>',
    external: '<path d="M18 13v6a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V8a2 2 0 0 1 2-2h6M15 3h6v6M10 14 21 3"/>',
    wallet: '<path d="M20 7H5a2 2 0 0 1 0-4h13v4zM3 5v14a2 2 0 0 0 2 2h15V7"/><circle cx="16" cy="14" r="1.2"/>',
    grid: '<rect x="3" y="3" width="7" height="7" rx="2"/><rect x="14" y="3" width="7" height="7" rx="2"/><rect x="3" y="14" width="7" height="7" rx="2"/><rect x="14" y="14" width="7" height="7" rx="2"/>',
    bold: '<path d="M7 5h6a3.5 3.5 0 0 1 0 7H7zM7 12h7a3.5 3.5 0 0 1 0 7H7z"/>', list: '<path d="M8 6h13M8 12h13M8 18h13M3 6h.01M3 12h.01M3 18h.01"/>',
    camera: '<path d="M23 19a2 2 0 0 1-2 2H3a2 2 0 0 1-2-2V8a2 2 0 0 1 2-2h4l2-3h6l2 3h4a2 2 0 0 1 2 2z"/><circle cx="12" cy="13" r="4"/>',
    'eye-off': '<path d="M17.9 17.9A10 10 0 0 1 12 19C5.6 19 2 12 2 12a18 18 0 0 1 5-5.9M9.9 5.1A9 9 0 0 1 12 5c6.4 0 10 7 10 7a18 18 0 0 1-2.2 3.2M1 1l22 22M14.1 14.1A3 3 0 0 1 9.9 9.9"/>'
  };
  window.NMA = { icon: function (n, c) { return '<svg class="ic ' + (c || '') + '"><use href="#i-' + n + '"/></svg>' } };
  var sp = '<svg xmlns="http://www.w3.org/2000/svg" style="display:none">';
  Object.keys(P).forEach(function (k) { sp += '<symbol id="i-' + k + '" viewBox="0 0 24 24">' + P[k] + '</symbol>' });
  document.addEventListener('DOMContentLoaded', function () {
    document.body.insertAdjacentHTML('afterbegin', sp + '</svg>');
    document.querySelectorAll('[data-i]').forEach(function (e) { e.outerHTML = NMA.icon(e.dataset.i, e.className) });
    var $$ = function (s, r) { return Array.prototype.slice.call((r || document).querySelectorAll(s)) };
    /* toasts */
    var tw = document.createElement('div'); tw.id = 'toasts'; document.body.appendChild(tw);
    window.toast = function (m, type) { var t = document.createElement('div'); t.className = 'toast'; t.innerHTML = NMA.icon(type === 'error' ? 'x' : 'check-c') + '<span>' + m + '</span>'; if (type === 'error') t.firstChild.style.color = 'rgb(var(--color-danger))'; tw.appendChild(t); setTimeout(function () { t.style.opacity = 0; t.style.transition = '.3s'; setTimeout(function () { t.remove() }, 300) }, 2600) };
    /* sidebar */
    var sb = document.getElementById('sidebar'), ov = document.getElementById('overlay');
    $$('[data-sidebar-toggle]').forEach(function (b) { b.onclick = function () { sb.classList.toggle('open'); ov.classList.toggle('hidden') } });
    if (ov) ov.onclick = function () { sb.classList.remove('open'); ov.classList.add('hidden') };
    /* dropdowns */
    $$('.dropdown > [data-dd]').forEach(function (b) { b.onclick = function (e) { e.stopPropagation(); var d = b.parentNode, o = d.classList.contains('open'); $$('.dropdown.open').forEach(function (x) { x.classList.remove('open') }); if (!o) d.classList.add('open') } });
    document.addEventListener('click', function (e) { if (!e.target.closest('.dropdown-menu')) $$('.dropdown.open').forEach(function (x) { x.classList.remove('open') }) });
    /* tabs */
    $$('[data-tabs]').forEach(function (w) {
      var tabs = $$('.tab', w), panes = $$('.tab-pane', document.querySelector(w.dataset.tabs) || document);
      tabs.forEach(function (t) { t.onclick = function () { tabs.forEach(function (x) { x.classList.toggle('active', x === t) }); panes.forEach(function (p) { p.classList.toggle('active', p.id === t.dataset.tab) }); try { history.replaceState(null, '', '#' + t.dataset.tab) } catch (e) { } } });
      var h = location.hash.slice(1), f = tabs.filter(function (t) { return t.dataset.tab === h })[0]; if (f) f.click();
    });
    /* modals */
    $$('[data-modal]').forEach(function (b) { b.onclick = function () { document.getElementById(b.dataset.modal).classList.add('open') } });
    $$('.modal').forEach(function (m) { m.addEventListener('click', function (e) { if (e.target === m || e.target.closest('[data-close]')) m.classList.remove('open') }) });
    document.addEventListener('keydown', function (e) { if (e.key === 'Escape') $$('.modal.open').forEach(function (m) { m.classList.remove('open') }) });
    /* delete confirm */
    var cm = document.getElementById('confirm-modal'), target = null, targetForm = null;
    $$('[data-delete]').forEach(function (b) { b.onclick = function () { target = b.closest('tr,li,article,[data-row]'); targetForm = b.closest('form'); if (cm) { cm.querySelector('[data-name]').textContent = b.dataset.delete || 'this item'; cm.classList.add('open') } } });
    if (cm) cm.querySelector('[data-confirm]').onclick = function () { cm.classList.remove('open'); if (targetForm) { targetForm.submit(); return } if (target) target.remove(); toast('Deleted successfully') };
    /* table select all + bulk bar */
    $$('[data-check-all]').forEach(function (a) { a.onchange = function () { var t = a.closest('table'); $$('tbody .chk', t).forEach(function (c) { c.checked = a.checked }); bulk() } });
    $$('tbody .chk').forEach(function (c) { c.onchange = bulk });
    function bulk() { var n = $$('tbody .chk:checked').length, b = document.getElementById('bulk-bar'); if (!b) return; b.classList.toggle('hidden', !n); var c = b.querySelector('[data-count]'); if (c) c.textContent = n }
    /* table search + status filter (client-side demo) */
    $$('[data-table-search]').forEach(function (i) { i.oninput = function () { var q = i.value.toLowerCase(); $$(i.dataset.tableSearch + ' tbody tr').forEach(function (r) { r.style.display = r.textContent.toLowerCase().indexOf(q) > -1 ? '' : 'none' }) } });
    $$('[data-filter-tabs]').forEach(function (w) { $$('[data-status]', w).forEach(function (t) { t.onclick = function () { $$('[data-status]', w).forEach(function (x) { x.classList.toggle('active', x === t) }); var s = t.dataset.status; $$(w.dataset.filterTabs + ' tbody tr').forEach(function (r) { r.style.display = (s === 'all' || r.dataset.status === s) ? '' : 'none' }) } }) });
    /* image preview */
    $$('input[type=file][data-preview]').forEach(function (f) { f.onchange = function () { var box = document.querySelector(f.dataset.preview); if (!box) return; Array.prototype.forEach.call(f.files, function (file) { var r = new FileReader(); r.onload = function (ev) { if (box.tagName === 'IMG') box.src = ev.target.result; else box.insertAdjacentHTML('beforeend', '<div><img src="' + ev.target.result + '" alt=""><button type="button" data-rm>' + NMA.icon('x', 'ic-sm') + '</button></div>') }; r.readAsDataURL(file) }) } });
    document.addEventListener('click', function (e) { var r = e.target.closest('[data-rm]'); if (r) (r.closest('.chip') || r.parentNode).remove() });
    /* chips input */
    $$('[data-chips]').forEach(function (i) { i.onkeydown = function (e) { if (e.key === 'Enter' && i.value.trim()) { e.preventDefault(); i.parentNode.querySelector('.chips').insertAdjacentHTML('beforeend', '<span class="chip">' + i.value.trim().replace(/</g, '&lt;') + '<button type="button" data-rm>' + NMA.icon('x', 'ic-sm') + '</button></span>'); i.value = '' } } });
    /* password toggle */
    $$('[data-pw]').forEach(function (b) { b.onclick = function () { var i = b.parentNode.querySelector('input'); i.type = i.type === 'password' ? 'text' : 'password' } });
    /* slug */
    $$('[data-slug-from]').forEach(function (s) { var src = document.querySelector(s.dataset.slugFrom); if (src) src.addEventListener('input', function () { s.value = src.value.toLowerCase().replace(/[^a-z0-9]+/g, '-').replace(/^-|-$/g, '') }) });
    /* demo forms */
    $$('form[data-demo]').forEach(function (f) { f.addEventListener('submit', function (e) { e.preventDefault(); toast(f.dataset.demo); if (f.dataset.go) setTimeout(function () { location.href = f.dataset.go }, 700) }) });
    $$('[data-toast]').forEach(function (b) { b.onclick = function (e) { e.preventDefault(); toast(b.dataset.toast) } });
    $$('[data-copy]').forEach(function (b) { b.onclick = function () { try { navigator.clipboard.writeText(b.dataset.copy) } catch (e) { } toast('Copied') } });
    $$('[data-print]').forEach(function (b) { b.onclick = function () { window.print() } });
    /* color sync */
    $$('input[type=color][data-hex]').forEach(function (c) { var t = document.querySelector(c.dataset.hex); c.oninput = function () { t.value = c.value } });
    /* charts */
    var NS = 'http://www.w3.org/2000/svg';
    function area(el, labels, series, colors) {
      var W = 760, H = 280, pl = 44, pb = 28, pt = 14, pr = 12, max = 0; series.forEach(function (s) { s.forEach(function (v) { if (v > max) max = v }) });
      if (!max) max = 1000; var step = Math.pow(10, Math.floor(Math.log10(max))); max = Math.ceil(max / step) * step;
      var x = function (i) { return pl + i * (W - pl - pr) / Math.max(labels.length - 1, 1) }, y = function (v) { return pt + (H - pt - pb) * (1 - v / max) };
      var g = '<svg viewBox="0 0 ' + W + ' ' + H + '" class="chart w-full h-auto">'; g += '<defs>';
      colors.forEach(function (c, k) { g += '<linearGradient id="g' + el.id + k + '" x1="0" y1="0" x2="0" y2="1"><stop offset="0" stop-color="' + c + '" stop-opacity=".28"/><stop offset="1" stop-color="' + c + '" stop-opacity="0"/></linearGradient>' }); g += '</defs>';
      for (var i = 0; i <= 4; i++) { var v = max * i / 4, yy = y(v); g += '<line x1="' + pl + '" x2="' + (W - pr) + '" y1="' + yy + '" y2="' + yy + '" stroke="#e2e8f3" stroke-dasharray="4 5"/><text x="' + (pl - 8) + '" y="' + (yy + 4) + '" text-anchor="end">' + (v >= 1000 ? (v / 1000) + 'k' : v) + '</text>' }
      labels.forEach(function (l, i) { if (labels.length < 14 || i % 2 === 0) g += '<text x="' + x(i) + '" y="' + (H - 6) + '" text-anchor="middle">' + l + '</text>' });
      series.forEach(function (s, k) {
        var d = s.map(function (v, i) { if (!i) return 'M' + x(0) + ' ' + y(v); var px = x(i - 1), py = y(s[i - 1]), cx = (px + x(i)) / 2; return 'C' + cx + ' ' + py + ' ' + cx + ' ' + y(v) + ' ' + x(i) + ' ' + y(v) }).join(' ');
        g += '<path d="' + d + ' L' + x(s.length - 1) + ' ' + (H - pb) + ' L' + x(0) + ' ' + (H - pb) + 'Z" fill="url(#g' + el.id + k + ')"/><path d="' + d + '" fill="none" stroke="' + colors[k] + '" stroke-width="2.8" stroke-linecap="round"/>';
        s.forEach(function (v, i) { g += '<circle cx="' + x(i) + '" cy="' + y(v) + '" r="4" fill="#fff" stroke="' + colors[k] + '" stroke-width="2.4"><title>' + labels[i] + ': ৳' + v.toLocaleString('en-US') + '</title></circle>' });
      });
      el.innerHTML = g + '</svg>';
    }
    function bars(el, labels, vals, color) {
      var W = 520, H = 240, pl = 36, pb = 26, max = Math.max.apply(null, vals) * 1.15, bw = (W - pl) / vals.length;
      var g = '<svg viewBox="0 0 ' + W + ' ' + H + '" class="chart w-full h-auto">';
      for (var i = 0; i <= 3; i++) { var yy = 10 + (H - 10 - pb) * i / 3; g += '<line x1="' + pl + '" x2="' + W + '" y1="' + yy + '" y2="' + yy + '" stroke="#e2e8f3" stroke-dasharray="4 5"/>' }
      vals.forEach(function (v, i) { var h = (H - 10 - pb) * v / max, x = pl + i * bw + bw * .22; g += '<rect x="' + x + '" y="' + (H - pb - h) + '" width="' + bw * .56 + '" height="' + h + '" rx="7" fill="' + color + '"><title>' + labels[i] + ': ' + v + '</title></rect><text x="' + (x + bw * .28) + '" y="' + (H - 8) + '" text-anchor="middle">' + labels[i] + '</text>' });
      el.innerHTML = g + '</svg>';
    }
    function donut(el, items) {
      var tot = items.reduce(function (a, b) { return a + b.v }, 0), R = 70, C = 2 * Math.PI * R, off = 0;
      var g = '<svg viewBox="0 0 200 200" class="w-44 h-44 -rotate-90"><circle cx="100" cy="100" r="' + R + '" fill="none" stroke="#eef1f8" stroke-width="22"/>';
      items.forEach(function (it) { var len = C * it.v / tot; g += '<circle cx="100" cy="100" r="' + R + '" fill="none" stroke="' + it.c + '" stroke-width="22" stroke-dasharray="' + (len - 3) + ' ' + (C - len + 3) + '" stroke-dashoffset="' + (-off) + '" stroke-linecap="round"><title>' + it.l + ': ' + it.v + '</title></circle>'; off += len });
      el.innerHTML = g + '</svg><div class="absolute inset-0 grid place-items-center text-center"><div><p class="font-display font-extrabold text-2xl">' + tot.toLocaleString('en-US') + '</p><p class="text-xs text-ink/50 font-semibold">' + (el.dataset.center || 'Total') + '</p></div></div>';
    }
    $$('[data-area]').forEach(function (el) {
      var D = JSON.parse(el.dataset.area), cur = Object.keys(D)[0];
      function draw(k) { var d = D[k]; area(el, d.labels, d.series, d.colors || ['#0B3A8C', '#F5A800']) }
      draw(cur);
      var sw = document.querySelector(el.dataset.switch); if (sw) $$('[data-range]', sw).forEach(function (p) { p.onclick = function () { $$('[data-range]', sw).forEach(function (x) { x.classList.toggle('active', x === p) }); draw(p.dataset.range) } });
    });
    $$('[data-bars]').forEach(function (el) { var d = JSON.parse(el.dataset.bars); bars(el, d.labels, d.values, d.color || '#0B3A8C') });
    $$('[data-donut]').forEach(function (el) { donut(el, JSON.parse(el.dataset.donut)) });
    $$('[data-spark]').forEach(function (el) { var v = JSON.parse(el.dataset.spark), c = el.dataset.color || '#0B3A8C', W = 110, H = 36, mx = Math.max.apply(null, v), mn = Math.min.apply(null, v); var pts = v.map(function (a, i) { return [i * W / (v.length - 1), H - 4 - (H - 8) * (a - mn) / ((mx - mn) || 1)] }); el.innerHTML = '<svg viewBox="0 0 ' + W + ' ' + H + '" class="spark w-full h-9"><path class="line" stroke="' + c + '" d="M' + pts.map(function (p) { return p[0].toFixed(1) + ' ' + p[1].toFixed(1) }).join(' L') + '"/></svg>' });
    /* image fallback (missing storefront images -> dummy) */
    var NOIMG = '/assets/admin/img/no-image.svg';
    function fix(i) { if (i.getAttribute('src') !== NOIMG && !i.dataset.fb) { i.dataset.fb = 1; i.src = NOIMG } }
    $$('img').forEach(function (i) { if (i.complete && i.naturalWidth === 0) fix(i) });
    document.addEventListener('error', function (e) { if (e.target.tagName === 'IMG') fix(e.target) }, true);
    /* reset upload preview */
    document.addEventListener('click', function (e) { var b = e.target.closest('[data-reset-img]'); if (!b) return; e.preventDefault(); var i = document.querySelector(b.dataset.resetImg); if (i) i.src = NOIMG; var f = b.closest('.drop').querySelector('input[type=file]'); if (f) f.value = '' });
    /* order form */
    var of = document.getElementById('order-form');
    if (of) {
      var body = document.getElementById('o-rows'), tpl = document.getElementById('o-tpl'), PR = JSON.parse(body.dataset.products), fmt = function (n) { return '৳' + Math.round(n).toLocaleString('en-US') }, cp = 0;
      var calc = function () { var sub = 0; $$('tr', body).forEach(function (r) { var q = +r.querySelector('input[type=number]').value || 0, p = +r.dataset.price || 0; r.querySelector('[data-line]').textContent = fmt(p * q); sub += p * q });
        var fee = +document.getElementById('o-zone').selectedOptions[0].dataset.fee, d = sub * cp; document.getElementById('s-sub').textContent = fmt(sub); document.getElementById('s-disc').textContent = '-' + fmt(d); document.getElementById('s-fee').textContent = fmt(fee); document.getElementById('s-total').textContent = fmt(sub - d + fee) };
      var add = function () { body.insertAdjacentHTML('beforeend', tpl.innerHTML); calc() };
      document.getElementById('o-add').onclick = function () { add(); var l = body.lastElementChild.querySelector('.combo-in'); if (l) l.focus() }; add();
      of.addEventListener('input', function (e) { if (e.target.type === 'number') calc() }); of.addEventListener('change', calc);
      body.addEventListener('click', function (e) { var b = e.target.closest('[data-del-row]'); if (b) { if (body.children.length > 1) b.closest('tr').remove(); else { var r = b.closest('tr'); r.dataset.price = 0; r.querySelector('.combo-in').value = ''; r.querySelector('[data-meta]').textContent = 'Nothing selected'; r.querySelector('.combo-thumb img').src = NOIMG } calc() } });
      document.getElementById('o-coupon-btn').onclick = function () { var v = document.getElementById('o-coupon').value.trim().toUpperCase(); if (v === 'NEBULA10') { cp = .1; toast('Coupon applied: 10% off') } else { cp = 0; toast(v ? 'Invalid coupon code' : 'Enter a coupon code') } calc() };
      /* searchable product picker */
      var list = document.createElement('ul'); list.className = 'combo-list'; list.hidden = true; document.body.appendChild(list);
      var cur = null, shown = [], hi = -1;
      function place() { if (!cur) return; var r = cur.getBoundingClientRect(), below = window.innerHeight - r.bottom; list.style.left = r.left + 'px'; list.style.width = Math.max(r.width, 340) + 'px'; list.style.top = (below > 280 ? r.bottom + 6 : Math.max(8, r.top - 6 - 280)) + 'px' }
      function draw(q) { q = (q || '').trim().toLowerCase(); var used = $$('tr', body).map(function (r) { return r.dataset.sku }); shown = PR.filter(function (p) { return !q || p.n.toLowerCase().indexOf(q) > -1 || p.k.toLowerCase().indexOf(q) > -1 }); hi = shown.length ? 0 : -1;
        list.innerHTML = shown.length ? shown.map(function (p, i) { return '<li data-i="' + i + '" class="' + (i === 0 ? 'on' : '') + '"><img src="' + p.i + '" alt=""><span class="min-w-0 flex-1"><b class="block truncate">' + p.n + '</b><small>SKU ' + p.k + ' · ' + (p.s > 0 ? p.s + ' in stock' : 'Out of stock') + (used.indexOf(p.k) > -1 ? ' · already added' : '') + '</small></span><em>' + fmt(p.p) + '</em></li>' }).join('') + '<li class="foot">' + shown.length + ' of ' + PR.length + ' products</li>' : '<li class="none">No product found for “' + q.replace(/</g, '&lt;') + '”</li>';
        $$('img', list).forEach(function (i) { i.onerror = function () { i.onerror = null; i.src = NOIMG } }) }
      function open_(inp) { cur = inp; draw(''); list.hidden = false; place() }
      function close_() { list.hidden = true; cur = null }
      function pick(i) { var p = shown[i]; if (!p || !cur) return; var r = cur.closest('tr'); r.dataset.price = p.p; r.dataset.sku = p.k; cur.value = p.n; r.querySelector('[data-meta]').textContent = 'SKU ' + p.k + ' · ' + fmt(p.p) + ' each · ' + (p.s > 0 ? p.s + ' in stock' : 'Out of stock'); var im = r.querySelector('.combo-thumb img'); im.src = p.i; im.onerror = function () { im.onerror = null; im.src = NOIMG }; close_(); calc() }
      function mark() { $$('li[data-i]', list).forEach(function (e, i) { e.classList.toggle('on', i === hi) }); var e = list.querySelector('li.on'); if (e) e.scrollIntoView({ block: 'nearest' }) }
      body.addEventListener('focusin', function (e) { if (e.target.classList.contains('combo-in')) { open_(e.target); e.target.select() } });
      body.addEventListener('input', function (e) { if (e.target.classList.contains('combo-in')) { cur = e.target; draw(e.target.value); list.hidden = false; place() } });
      body.addEventListener('keydown', function (e) { if (!e.target.classList.contains('combo-in')) return; if (e.key === 'ArrowDown') { e.preventDefault(); if (list.hidden) open_(e.target); else { hi = Math.min(hi + 1, shown.length - 1); mark() } } else if (e.key === 'ArrowUp') { e.preventDefault(); hi = Math.max(hi - 1, 0); mark() } else if (e.key === 'Enter') { if (!list.hidden) { e.preventDefault(); pick(hi) } } else if (e.key === 'Escape' || e.key === 'Tab') close_() });
      list.addEventListener('mousedown', function (e) { var li = e.target.closest('li[data-i]'); if (li) { e.preventDefault(); pick(+li.dataset.i) } });
      document.addEventListener('mousedown', function (e) { if (!e.target.closest('.combo') && !e.target.closest('.combo-list')) close_() });
      window.addEventListener('resize', place); window.addEventListener('scroll', place, true);
    }
    /* permission matrix */
    var mx = document.querySelector('[data-matrix]');
    if (mx) {
      var cnt = function () { var a = $$('.pm', mx), c = a.filter(function (x) { return x.checked }).length; $$('[data-perm-count]').forEach(function (e) { e.textContent = c + ' / ' + a.length });
        $$('[data-row-all]', mx).forEach(function (r) { var b = $$('.pm[data-row="' + r.dataset.rowAll + '"]', mx); r.checked = b.length && b.every(function (x) { return x.checked }) });
        $$('[data-col-all]', mx).forEach(function (r) { var b = $$('.pm[data-col="' + r.dataset.colAll + '"]', mx); r.checked = b.length && b.every(function (x) { return x.checked }) });
        var al = document.querySelector('[data-all]'); if (al) al.checked = c === a.length };
      mx.addEventListener('change', function (e) { var t = e.target;
        if (t.dataset.rowAll) $$('.pm[data-row="' + t.dataset.rowAll + '"]', mx).forEach(function (x) { x.checked = t.checked });
        if (t.dataset.colAll) $$('.pm[data-col="' + t.dataset.colAll + '"]', mx).forEach(function (x) { x.checked = t.checked });
        cnt() });
      var all = document.querySelector('[data-all]'); if (all) all.onchange = function () { $$('.pm', mx).forEach(function (x) { x.checked = all.checked }); cnt() };
      var ps = document.querySelector('[data-presets]'); if (ps) { var P = JSON.parse(ps.dataset.presets); ps.onchange = function () { var l = P[ps.value]; if (!l) return; $$('.pm', mx).forEach(function (x) { x.checked = l === 'all' || l.indexOf(x.dataset.row + ':' + x.dataset.col) > -1 }); cnt(); toast('Permissions copied from ' + ps.value) } }
      cnt();
    }
    /* countdown demo */
    $$('[data-countdown]').forEach(function (el) { var end = Date.now() + (+el.dataset.countdown) * 1000; setInterval(function () { var s = Math.max(0, Math.floor((end - Date.now()) / 1000)), p = function (n) { return String(n).padStart(2, '0') }; el.textContent = p(Math.floor(s / 3600)) + ':' + p(Math.floor(s % 3600 / 60)) + ':' + p(s % 60) }, 1000) });
  });
})();
