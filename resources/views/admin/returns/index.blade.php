@extends('admin.dashboard.dashboard')
@section('admin_title', 'Returns · Nebula Mart Admin')
@section('admin_contents')

    @php
        $badges = ['pending' => 'b-warn', 'approved' => 'b-success', 'rejected' => 'b-danger'];
        $current = $filters['status'] ?? 'all';
        $keep = fn (array $merge) => array_filter(array_merge(request()->except('page'), $merge), fn ($value) => $value !== null && $value !== '');
    @endphp

    <div class="flex flex-wrap items-end justify-between gap-4">
        <div>
            <h1 class="font-display font-extrabold text-2xl sm:text-[28px] tracking-tight">Returns</h1>
            <p class="text-ink/55 mt-1">Review customer return and cancellation requests.</p>
        </div>
        <button type="button" class="btn btn-primary" id="rt-add"><svg class="ic ic-sm"><use href="#i-plus" /></svg>Add return product</button>
    </div>

    <section class="card">
        <div class="tabs">
            <a href="{{ route('admin.return-requests.index', $keep(['status' => null])) }}" class="tab {{ $current === 'all' ? 'active' : '' }}">All <small>{{ $counts->sum() }}</small></a>
            @foreach (\App\Models\OrderReturn::STATUSES as $key => $label)
                <a href="{{ route('admin.return-requests.index', $keep(['status' => $key])) }}" class="tab {{ $current === $key ? 'active' : '' }}">{{ $label }} <small>{{ $counts[$key] ?? 0 }}</small></a>
            @endforeach
        </div>

        <form method="GET" action="{{ route('admin.return-requests.index') }}" class="flex flex-wrap items-center gap-3 p-5 border-b border-line">
            @if ($filters['status'] ?? null)
                <input type="hidden" name="status" value="{{ $filters['status'] }}">
            @endif
            <div class="input-group flex-1 min-w-[220px] max-w-sm">
                <svg class="ic"><use href="#i-search" /></svg>
                <input class="input" name="q" value="{{ $filters['q'] ?? '' }}" placeholder="Search order, name, phone…">
            </div>
            <button class="btn btn-ghost btn-sm" type="submit">Filter</button>
            @if (array_filter($filters))
                <a href="{{ route('admin.return-requests.index') }}" class="btn btn-ghost btn-sm">Reset</a>
            @endif
        </form>

        <div class="overflow-x-auto">
            <table class="tbl">
                <thead>
                    <tr>
                        <th>Request</th>
                        <th>Order</th>
                        <th>Customer</th>
                        <th>Type</th>
                        <th>Items</th>
                        <th>Value</th>
                        <th>Date</th>
                        <th>Status</th>
                        <th class="text-right">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($returns as $return)
                        <tr>
                            <td><a href="{{ route('admin.return-requests.show', $return) }}" class="font-bold text-primary hover:underline">{{ $return->number }}</a></td>
                            <td><a href="{{ route('admin.orders.show', $return->order) }}" class="font-semibold hover:underline">#{{ $return->order->number }}</a></td>
                            <td>
                                <p class="font-semibold">{{ $return->order->name }}</p>
                                <p class="text-xs text-ink/50">{{ $return->order->phone }}</p>
                            </td>
                            <td>{{ $return->type_label }}</td>
                            <td>{{ $return->item_count }} {{ $return->item_count === 1 ? 'item' : 'items' }}</td>
                            <td><b>৳{{ number_format($return->amount) }}</b></td>
                            <td class="text-ink/60">{{ $return->created_at->timezone(\App\Models\Setting::tz())->format('d M Y') }}</td>
                            <td><span class="badge {{ $badges[$return->status] ?? 'b-muted' }}">{{ $return->status_label }}</span></td>
                            <td>
                                <div class="flex justify-end gap-1">
                                    <a href="{{ route('admin.return-requests.show', $return) }}" class="icon-btn" title="View"><svg class="ic"><use href="#i-eye" /></svg></a>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="9" class="text-ink/55">No return requests found.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if ($returns->hasPages())
            <div class="flex flex-wrap items-center justify-between gap-3 px-5 py-4 border-t border-line">
                <p class="text-sm text-ink/55">Showing <b class="text-ink">{{ $returns->firstItem() }}–{{ $returns->lastItem() }}</b> of <b class="text-ink">{{ $returns->total() }}</b> results</p>
                <div class="pg flex items-center gap-1">
                    @if ($returns->onFirstPage())
                        <span class="px-1 text-ink/30" aria-hidden="true">‹</span>
                    @else
                        <a href="{{ $returns->previousPageUrl() }}" aria-label="Previous">‹</a>
                    @endif
                    @foreach ($returns->getUrlRange(max(1, $returns->currentPage() - 3), min($returns->lastPage(), $returns->currentPage() + 3)) as $page => $url)
                        <a href="{{ $url }}" @class(['active' => $page === $returns->currentPage()])>{{ $page }}</a>
                    @endforeach
                    @if ($returns->hasMorePages())
                        <a href="{{ $returns->nextPageUrl() }}" aria-label="Next">›</a>
                    @else
                        <span class="px-1 text-ink/30" aria-hidden="true">›</span>
                    @endif
                </div>
            </div>
        @endif
    </section>

    <div id="rt-modal" class="modal">
        <div class="!max-w-2xl">
            <form method="POST" action="{{ route('admin.return-requests.store') }}" id="rt-form">
                @csrf
                <input type="hidden" name="order_id" id="rt-order-id" value="{{ old('order_id') }}">
                <div class="flex items-center justify-between px-6 py-5 border-b border-line">
                    <h3 class="font-display font-extrabold text-xl">Add return product</h3>
                    <button type="button" class="icon-btn" data-close aria-label="Close"><svg class="ic"><use href="#i-x" /></svg></button>
                </div>
                <div class="p-6 space-y-5">
                    @if ($errors->any())
                        <p class="text-danger text-sm">{{ $errors->first() }}</p>
                    @endif
                    <div>
                        <label class="lbl" for="rt-q">Order number, name or phone</label>
                        <div class="input-group"><svg class="ic"><use href="#i-search" /></svg><input class="input" id="rt-q" type="text" autocomplete="off" placeholder="e.g. NM-10025 or 01700000000"></div>
                        <ul id="rt-results" class="mt-2 space-y-1"></ul>
                    </div>
                    <div id="rt-picked" hidden>
                        <div class="flex items-center justify-between rounded-xl bg-bg px-4 py-3">
                            <p class="font-semibold" id="rt-picked-label"></p>
                            <button type="button" class="btn btn-ghost btn-sm" id="rt-change">Change</button>
                        </div>
                        <div class="overflow-x-auto mt-4">
                            <table class="tbl">
                                <thead><tr><th class="w-10"><input type="checkbox" id="rt-all" class="w-4 h-4 accent-primary" aria-label="Select all"></th><th>Product</th><th>Price</th><th class="w-36">Return qty</th></tr></thead>
                                <tbody id="rt-items"></tbody>
                            </table>
                        </div>
                        <div class="space-y-5 mt-5">
                            <div>
                                <label class="lbl" for="rt-reason">Reason</label>
                                <select class="select" id="rt-reason" name="reason" required>
                                    <option value="">Select a reason</option>
                                    @foreach (\App\Models\OrderReturn::REASONS as $key => $label)
                                        <option value="{{ $key }}" @selected(old('reason') === $key)>{{ $label }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div>
                                <label class="lbl" for="rt-details">Details (optional)</label>
                                <textarea class="textarea w-full" id="rt-details" name="details" rows="3" maxlength="500">{{ old('details') }}</textarea>
                            </div>
                            <p class="text-sm text-ink/60">Stock and sold count are restored as soon as you save.</p>
                        </div>
                    </div>
                </div>
                <div class="flex gap-3 px-6 pb-6">
                    <button type="button" class="btn btn-ghost flex-1 !h-12" data-close>Cancel</button>
                    <button class="btn btn-primary flex-1 !h-12" type="submit" id="rt-submit" disabled>Return &amp; restock</button>
                </div>
            </form>
        </div>
    </div>

    <script>
        (function () {
            var modal = document.getElementById('rt-modal');
            var q = document.getElementById('rt-q');
            var results = document.getElementById('rt-results');
            var picked = document.getElementById('rt-picked');
            var itemsBody = document.getElementById('rt-items');
            var orderId = document.getElementById('rt-order-id');
            var submit = document.getElementById('rt-submit');
            var all = document.getElementById('rt-all');
            var searchUrl = @json(route('admin.return-requests.search-orders'));
            var timer = null;

            function esc(value) { var d = document.createElement('div'); d.textContent = value == null ? '' : value; return d.innerHTML; }
            function money(value) { return '৳' + Math.round(value).toLocaleString('en-US'); }
            function syncSubmit() { submit.disabled = ! orderId.value || ! itemsBody.querySelector('.rt-pick:checked'); }
            function reset() {
                orderId.value = ''; picked.hidden = true; itemsBody.innerHTML = ''; results.innerHTML = ''; all.checked = false; syncSubmit();
            }

            function pick(order) {
                orderId.value = order.id;
                results.innerHTML = '';
                q.value = '';
                document.getElementById('rt-picked-label').textContent = '#' + order.number + ' · ' + order.name + ' · ' + order.phone + ' · ' + order.status;
                itemsBody.innerHTML = order.items.map(function (item) {
                    var opts = '';
                    for (var i = 1; i <= item.available; i++) { opts += '<option value="' + i + '"' + (i === item.available ? ' selected' : '') + '>' + i + ' of ' + item.quantity + '</option>'; }
                    return '<tr><td><input type="checkbox" class="rt-pick w-4 h-4 accent-primary" name="items[]" value="' + item.id + '" id="rt-i' + item.id + '"></td>'
                        + '<td><label for="rt-i' + item.id + '" class="flex items-center gap-3 cursor-pointer"><img class="thumb" src="' + esc(item.image) + '" alt=""><span><span class="block font-bold">' + esc(item.name) + '</span><span class="block text-xs text-ink/50">' + esc(item.meta) + '</span></span></label></td>'
                        + '<td>' + money(item.price) + '</td>'
                        + '<td><select class="select" name="qty[' + item.id + ']" aria-label="Quantity">' + opts + '</select></td></tr>';
                }).join('');
                picked.hidden = false;
                all.checked = false;
                syncSubmit();
            }

            q.addEventListener('input', function () {
                clearTimeout(timer);
                var term = q.value.trim();
                if (term.length < 2) { results.innerHTML = ''; return; }
                timer = setTimeout(function () {
                    fetch(searchUrl + '?q=' + encodeURIComponent(term), { headers: { 'Accept': 'application/json', 'X-Requested-With': 'XMLHttpRequest' }, credentials: 'same-origin' })
                        .then(function (r) { return r.json(); })
                        .then(function (data) {
                            results.innerHTML = '';
                            if (! data.orders.length) { results.innerHTML = '<li class="text-sm text-ink/55 px-1">No order with returnable products found.</li>'; return; }
                            data.orders.forEach(function (order) {
                                var li = document.createElement('li');
                                li.innerHTML = '<button type="button" class="w-full text-left rounded-xl border border-line px-4 py-2.5 hover:bg-bg"><b>#' + esc(order.number) + '</b> · ' + esc(order.name) + ' · ' + esc(order.phone) + ' <span class="text-xs text-ink/50">(' + esc(order.status) + ', ' + order.items.length + ' product' + (order.items.length === 1 ? '' : 's') + ')</span></button>';
                                li.firstChild.addEventListener('click', function () { pick(order); });
                                results.appendChild(li);
                            });
                        });
                }, 250);
            });

            all.addEventListener('change', function () { [].forEach.call(itemsBody.querySelectorAll('.rt-pick'), function (b) { b.checked = all.checked; }); syncSubmit(); });
            itemsBody.addEventListener('change', syncSubmit);
            document.getElementById('rt-change').addEventListener('click', function () { reset(); q.focus(); });
            document.getElementById('rt-add').addEventListener('click', function () { modal.classList.add('open'); q.focus(); });
            [].forEach.call(modal.querySelectorAll('[data-close]'), function (b) { b.addEventListener('click', function () { modal.classList.remove('open'); }); });
            modal.addEventListener('click', function (e) { if (e.target === modal) { modal.classList.remove('open'); } });
            syncSubmit();
        })();
    </script>

@endsection
