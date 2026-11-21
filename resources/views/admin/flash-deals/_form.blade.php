@php
    $old = session()->hasOldInput();
    $startsAt = old('starts_at', $deal?->starts_at?->format('Y-m-d\TH:i') ?? now(\App\Models\Setting::tz())->startOfDay()->format('Y-m-d\TH:i'));
    $endsAt = old('ends_at', $deal?->ends_at?->format('Y-m-d\TH:i') ?? now(\App\Models\Setting::tz())->setTime(23, 59)->format('Y-m-d\TH:i'));
    $repeatDaily = $old ? (bool) old('repeat_daily') : ($deal?->repeat_daily ?? true);
    $showOnHome = $old ? (bool) old('show_on_home') : ($deal?->show_on_home ?? true);
    $showCountdown = $old ? (bool) old('show_countdown') : ($deal?->show_countdown ?? true);
    $isActive = $old ? (bool) old('is_active') : ($deal?->is_active ?? true);

    $rows = [];

    if ($old) {
        foreach ((array) old('products', []) as $row) {
            $rows[] = ['product_id' => (int) ($row['product_id'] ?? 0), 'discount_percent' => $row['discount_percent'] ?? 0, 'deal_stock' => $row['deal_stock'] ?? ''];
        }
    } elseif ($deal) {
        foreach ($deal->items as $item) {
            $rows[] = ['product_id' => $item->product_id, 'discount_percent' => $item->discount_percent, 'deal_stock' => $item->deal_stock ?? ''];
        }
    }

    $catalogData = $catalog->all();

    foreach ($deal?->items ?? [] as $item) {
        if ($item->product && ! isset($catalogData[$item->product_id])) {
            $catalogData[$item->product_id] = ['id' => $item->product_id, 'name' => $item->product->name.' (draft)', 'sku' => $item->product->sku, 'price' => (float) $item->product->price, 'img' => $item->product->cover_url];
        }
    }
@endphp

<style>
    .deal-picker { position: relative; }
    .deal-results { position: absolute; left: 0; right: 0; top: calc(100% + .35rem); z-index: 30; background: #fff; border: 1px solid rgb(0 0 0 / .1); border-radius: .9rem; box-shadow: 0 12px 32px rgb(0 0 0 / .14); max-height: 24rem; overflow-y: auto; padding: .35rem; }
    .deal-item { display: flex; align-items: center; gap: .75rem; width: 100%; text-align: left; padding: .5rem; border-radius: .65rem; cursor: pointer; background: none; border: 0; }
    .deal-item:hover, .deal-item.is-active { background: rgb(0 0 0 / .05); }
    .deal-item[disabled] { opacity: .5; cursor: default; }
    .deal-item img { width: 52px; height: 52px; object-fit: cover; border-radius: .55rem; flex: none; background: #f3f4f6; }
    .deal-item .n { display: block; font-weight: 700; line-height: 1.25; }
    .deal-item .m { display: block; font-size: .75rem; opacity: .6; margin-top: .15rem; }
    .deal-item .p { margin-left: auto; font-weight: 800; white-space: nowrap; }
    .deal-note { padding: .85rem; font-size: .875rem; opacity: .6; }
</style>

<div class="grid xl:grid-cols-3 gap-6 items-start">
    <div class="xl:col-span-2 space-y-6">

        <section class="card">
            <div class="card-h"><h3>Campaign</h3></div>
            <div class="card-b">
                <div class="grid sm:grid-cols-2 gap-5">
                    <div class="sm:col-span-2">
                        <label class="lbl" for="title">Campaign title <span class="text-danger">*</span></label>
                        <input class="input" id="title" name="title" type="text" value="{{ old('title', $deal?->title) }}" required>
                        @error('title')<p class="text-danger text-xs mt-1">{{ $message }}</p>@enderror
                    </div>
                    <div>
                        <label class="lbl" for="starts_at">Starts at</label>
                        <input class="input" id="starts_at" name="starts_at" type="datetime-local" value="{{ $startsAt }}" required>
                        @error('starts_at')<p class="text-danger text-xs mt-1">{{ $message }}</p>@enderror
                    </div>
                    <div>
                        <label class="lbl" for="ends_at">Ends at</label>
                        <input class="input" id="ends_at" name="ends_at" type="datetime-local" value="{{ $endsAt }}" required>
                        @error('ends_at')<p class="text-danger text-xs mt-1">{{ $message }}</p>@enderror
                    </div>
                    <div class="flex items-center justify-between gap-4 sm:col-span-2">
                        <div>
                            <p class="font-semibold">Repeat daily</p>
                            <p class="text-xs text-ink/50">Resets at midnight (Dhaka time). Uses the start and end time of day, every day from the start date.</p>
                        </div>
                        <label class="switch"><input type="checkbox" name="repeat_daily" value="1" id="repeat_daily" @checked($repeatDaily)><i></i></label>
                    </div>
                </div>
            </div>
        </section>

        <section class="card">
            <div class="card-h"><h3>Deal products</h3></div>
            <div>
                <div class="p-5 border-b border-line">
                    <div class="deal-picker">
                        <div class="input-group">
                            <svg class="ic"><use href="#i-search" /></svg>
                            <input class="input" id="deal-search" type="search" placeholder="Search products by name or SKU to add…" autocomplete="off" role="combobox" aria-expanded="false" aria-controls="deal-results">
                        </div>
                        <div id="deal-results" class="deal-results" role="listbox" hidden></div>
                    </div>
                </div>

                @error('products')<p class="text-danger text-xs px-5 pt-3">{{ $message }}</p>@enderror
                @error('products.*.product_id')<p class="text-danger text-xs px-5 pt-3">{{ $message }}</p>@enderror
                @error('products.*.discount_percent')<p class="text-danger text-xs px-5 pt-3">{{ $message }}</p>@enderror
                @error('products.*.deal_stock')<p class="text-danger text-xs px-5 pt-3">{{ $message }}</p>@enderror

                <div class="overflow-x-auto">
                    <table class="tbl">
                        <thead>
                            <tr>
                                <th>Product</th>
                                <th>Price</th>
                                <th>Discount</th>
                                <th>Deal price</th>
                                <th>Deal stock</th>
                                <th></th>
                            </tr>
                        </thead>
                        <tbody id="deal-rows">
                            @foreach ($rows as $index => $row)
                                @php $product = $catalogData[$row['product_id']] ?? null; @endphp
                                @continue(! $product)
                                <tr data-id="{{ $product['id'] }}" data-price="{{ $product['price'] }}">
                                    <td>
                                        <div class="flex items-center gap-3">
                                            <img class="thumb" src="{{ $product['img'] }}" alt="">
                                            <b class="max-w-[220px] truncate">{{ $product['name'] }}</b>
                                            <input type="hidden" name="products[{{ $index }}][product_id]" value="{{ $product['id'] }}">
                                        </div>
                                    </td>
                                    <td>৳{{ number_format($product['price'], floor($product['price']) == $product['price'] ? 0 : 2) }}</td>
                                    <td>
                                        <div class="addon w-28">
                                            <span>%</span>
                                            <input class="input" type="number" min="0" max="95" step="any" name="products[{{ $index }}][discount_percent]" value="{{ $row['discount_percent'] }}" data-pct>
                                        </div>
                                    </td>
                                    <td><b class="text-success" data-deal-price></b></td>
                                    <td><input class="input !w-24" type="number" min="0" name="products[{{ $index }}][deal_stock]" value="{{ $row['deal_stock'] }}" placeholder="∞"></td>
                                    <td><button type="button" class="icon-btn danger" data-deal-rm><svg class="ic"><use href="#i-trash" /></svg></button></td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
                <p id="deal-empty" class="p-5 text-ink/55 text-sm" @if (count($rows)) hidden @endif>No products added yet.</p>
            </div>
        </section>

    </div>

    <div class="space-y-6">

        <section class="card">
            <div class="card-h"><h3>Live preview</h3></div>
            <div class="card-b">
                <div class="is-12 rounded-2xl p-5 text-white text-center">
                    <p class="text-xs tracking-widest uppercase font-bold text-white/70">Runs for</p>
                    <p class="font-display font-extrabold text-4xl my-2 tracking-wider" id="deal-duration">00:00:00</p>
                    <p class="font-semibold text-accent" id="deal-headline">Flash Deals</p>
                </div>
            </div>
        </section>

        <section class="card">
            <div class="card-h"><h3>Settings</h3></div>
            <div class="card-b">
                <div class="space-y-5">
                    <div class="flex items-center justify-between gap-4">
                        <div><p class="font-semibold">Active</p></div>
                        <label class="switch"><input type="checkbox" name="is_active" value="1" @checked($isActive)><i></i></label>
                    </div>
                    <div class="flex items-center justify-between gap-4">
                        <div><p class="font-semibold">Show on homepage</p></div>
                        <label class="switch"><input type="checkbox" name="show_on_home" value="1" @checked($showOnHome)><i></i></label>
                    </div>
                    <div class="flex items-center justify-between gap-4">
                        <div><p class="font-semibold">Show countdown</p></div>
                        <label class="switch"><input type="checkbox" name="show_countdown" value="1" @checked($showCountdown)><i></i></label>
                    </div>
                </div>
            </div>
        </section>

    </div>
</div>

<div class="savebar mt-6">
    <a href="{{ route('admin.flash-deals.index') }}" class="btn btn-ghost">Cancel</a>
    <button type="submit" class="btn btn-primary">
        <svg class="ic ic-sm"><use href="#i-check" /></svg>Save deal
    </button>
</div>

<script>
    (function () {
        var body = document.getElementById('deal-rows');
        var empty = document.getElementById('deal-empty');
        var search = document.getElementById('deal-search');
        var counter = body.querySelectorAll('tr').length;

        function money(n) { return '৳' + Number(n).toLocaleString('en-US', { maximumFractionDigits: 2 }); }
        function esc(s) { return String(s).replace(/[&<>"]/g, function (c) { return { '&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;' }[c]; }); }

        function refresh() {
            var best = 0;
            body.querySelectorAll('tr').forEach(function (tr) {
                var pct = parseFloat(tr.querySelector('[data-pct]').value) || 0;
                tr.querySelector('[data-deal-price]').textContent = money(Math.round(tr.dataset.price * (1 - pct / 100)));
                best = Math.max(best, pct);
            });
            empty.hidden = body.querySelectorAll('tr').length > 0;
            document.getElementById('deal-headline').textContent = best ? 'Flash Deals · Up to ' + best + '% OFF' : 'Flash Deals';
        }

        function duration() {
            var s = document.getElementById('starts_at').value, e = document.getElementById('ends_at').value, out = '00:00:00';
            if (s && e) {
                var a = new Date(s), b = new Date(e), ms = b - a;
                if (document.getElementById('repeat_daily').checked) {
                    ms = ((b.getHours() * 60 + b.getMinutes()) - (a.getHours() * 60 + a.getMinutes())) * 60000;
                    if (b.getHours() === 23 && b.getMinutes() === 59) ms += 60000;
                    else if (ms <= 0) ms += 86400000;
                }
                if (ms > 0) {
                    var t = Math.floor(ms / 1000), p = function (n) { return String(n).padStart(2, '0'); };
                    out = p(Math.floor(t / 3600)) + ':' + p(Math.floor(t % 3600 / 60)) + ':' + p(t % 60);
                }
            }
            document.getElementById('deal-duration').textContent = out;
        }

        var results = document.getElementById('deal-results');
        var timer = null, controller = null, current = [], active = -1;

        function inDeal(id) { return !!body.querySelector('tr[data-id="' + id + '"]'); }

        function closeResults() { results.hidden = true; results.innerHTML = ''; search.setAttribute('aria-expanded', 'false'); current = []; active = -1; }

        function render(products) {
            current = products; active = -1;
            if (!products.length) { results.innerHTML = '<p class="deal-note">No products found.</p>'; }
            else {
                results.innerHTML = products.map(function (p, i) {
                    var added = inDeal(p.id);
                    return '<button type="button" class="deal-item" role="option" data-i="' + i + '"' + (added ? ' disabled' : '') + '>' +
                        '<img src="' + esc(p.img) + '" alt="" loading="lazy">' +
                        '<span><span class="n">' + esc(p.name) + '</span><span class="m">' + esc([p.sku, p.category, added ? 'Already added' : ''].filter(Boolean).join(' · ')) + '</span></span>' +
                        '<span class="p">' + money(p.price) + '</span></button>';
                }).join('');
            }
            results.hidden = false; search.setAttribute('aria-expanded', 'true');
        }

        function query() {
            var q = search.value.trim();
            if (q.length < 2) { closeResults(); return; }
            if (controller) controller.abort();
            controller = new AbortController();
            fetch(@json(route('admin.flash-deals.search-products')) + '?q=' + encodeURIComponent(q), { headers: { 'Accept': 'application/json' }, signal: controller.signal })
                .then(function (r) { return r.json(); })
                .then(function (d) { if (search.value.trim() === q) render(d.products || []); })
                .catch(function () {});
        }

        function add(product) {
            if (!product || inDeal(product.id)) return;
            var i = counter++;
            body.insertAdjacentHTML('beforeend',
                '<tr data-id="' + product.id + '" data-price="' + product.price + '">' +
                '<td><div class="flex items-center gap-3"><img class="thumb" src="' + esc(product.img) + '" alt=""><b class="max-w-[220px] truncate">' + esc(product.name) + '</b>' +
                '<input type="hidden" name="products[' + i + '][product_id]" value="' + product.id + '"></div></td>' +
                '<td>' + money(product.price) + '</td>' +
                '<td><div class="addon w-28"><span>%</span><input class="input" type="number" min="0" max="95" step="any" name="products[' + i + '][discount_percent]" value="10" data-pct></div></td>' +
                '<td><b class="text-success" data-deal-price></b></td>' +
                '<td><input class="input !w-24" type="number" min="0" name="products[' + i + '][deal_stock]" placeholder="∞"></td>' +
                '<td><button type="button" class="icon-btn danger" data-deal-rm><svg class="ic"><use href="#i-trash"/></svg></button></td></tr>');
            search.value = '';
            closeResults();
            refresh();
        }

        function highlight(n) {
            var items = results.querySelectorAll('.deal-item:not([disabled])');
            if (!items.length) return;
            active = (n + items.length) % items.length;
            items.forEach(function (el, k) { el.classList.toggle('is-active', k === active); });
            items[active].scrollIntoView({ block: 'nearest' });
        }

        search.addEventListener('input', function () { clearTimeout(timer); timer = setTimeout(query, 250); });
        search.addEventListener('keydown', function (e) {
            if (e.key === 'ArrowDown') { e.preventDefault(); highlight(active + 1); }
            else if (e.key === 'ArrowUp') { e.preventDefault(); highlight(active - 1); }
            else if (e.key === 'Escape') { closeResults(); }
            else if (e.key === 'Enter') {
                e.preventDefault();
                var items = results.querySelectorAll('.deal-item:not([disabled])');
                var el = items[active >= 0 ? active : 0];
                if (el) add(current[+el.dataset.i]);
            }
        });
        results.addEventListener('click', function (e) {
            var el = e.target.closest('.deal-item');
            if (el && !el.disabled) add(current[+el.dataset.i]);
        });
        document.addEventListener('click', function (e) { if (!e.target.closest('.deal-picker')) closeResults(); });
        body.addEventListener('input', function (e) { if (e.target.matches('[data-pct]')) refresh(); });
        body.addEventListener('click', function (e) {
            var button = e.target.closest('[data-deal-rm]');
            if (button) { button.closest('tr').remove(); refresh(); }
        });
        ['starts_at', 'ends_at', 'repeat_daily'].forEach(function (id) { document.getElementById(id).addEventListener('input', duration); document.getElementById(id).addEventListener('change', duration); });

        refresh();
        duration();
    })();
</script>
