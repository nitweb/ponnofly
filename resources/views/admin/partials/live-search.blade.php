<script>
    (function () {
        var form = document.querySelector('form[data-live-search]');
        var results = document.querySelector('[data-live-results]');
        if (!form || !results) return;

        var input = form.querySelector('input[name="q"]');
        var tabs = document.querySelector('[data-live-tabs]');
        var reset = form.querySelector('[data-live-reset]');
        var timer = null, controller = null, composing = false;

        function formUrl() {
            var params = new URLSearchParams();
            new FormData(form).forEach(function (value, key) {
                if (typeof value === 'string' && value.trim() !== '') params.append(key, value.trim());
            });
            var query = params.toString();
            return form.getAttribute('action') + (query ? '?' + query : '');
        }

        function hasFilters() {
            return Array.prototype.some.call(form.elements, function (el) {
                return el.name && el.type !== 'submit' && el.type !== 'button' && String(el.value).trim() !== '';
            });
        }

        function load(url) {
            if (controller) controller.abort();
            var mine = controller = new AbortController();
            results.style.opacity = '.5';

            fetch(url, {
                headers: { 'X-Requested-With': 'XMLHttpRequest', 'Accept': 'text/html' },
                credentials: 'same-origin',
                signal: mine.signal
            }).then(function (response) {
                if (!response.ok) throw new Error('HTTP ' + response.status);
                return response.text();
            }).then(function (html) {
                var doc = new DOMParser().parseFromString(html, 'text/html');
                var nextResults = doc.querySelector('[data-live-results]');
                if (!nextResults) throw new Error('Results not found');
                results.innerHTML = nextResults.innerHTML;

                var nextTabs = doc.querySelector('[data-live-tabs]');
                if (tabs && nextTabs) tabs.innerHTML = nextTabs.innerHTML;

                if (reset) reset.style.display = hasFilters() ? '' : 'none';
                history.replaceState(null, '', url);
            }).catch(function (error) {
                if (error.name === 'AbortError') return;
                window.location.href = url;
            }).then(function () {
                if (controller === mine) results.style.opacity = '';
            });
        }

        function schedule() {
            clearTimeout(timer);
            timer = setTimeout(function () { load(formUrl()); }, 300);
        }

        if (input) {
            input.setAttribute('autocomplete', 'off');
            input.addEventListener('compositionstart', function () { composing = true; });
            input.addEventListener('compositionend', function () { composing = false; schedule(); });
            input.addEventListener('input', function () { if (!composing) schedule(); });
        }

        form.addEventListener('submit', function (event) {
            event.preventDefault();
            clearTimeout(timer);
            load(formUrl());
        });

        results.addEventListener('click', function (event) {
            var link = event.target.closest('.pg a');
            if (!link || event.metaKey || event.ctrlKey || event.shiftKey) return;
            event.preventDefault();
            load(link.href);
        });
    })();
</script>
