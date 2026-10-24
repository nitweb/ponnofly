@extends('admin.dashboard.dashboard')
@section('admin_title', 'Orders · Nebula Mart Admin')
@section('admin_contents')

    @php
        $badges = ['pending' => 'b-warn', 'processing' => 'b-info', 'shipped' => 'b-purple', 'out_for_delivery' => 'b-info', 'delivered' => 'b-success', 'cancelled' => 'b-danger', 'returned' => 'b-muted'];
        $current = $filters['status'] ?? 'all';
        $keep = fn (array $merge) => array_filter(array_merge(request()->except('page'), $merge), fn ($value) => $value !== null && $value !== '');
    @endphp

    <div class="flex flex-wrap items-end justify-between gap-4">
        <div>
            <h1 class="font-display font-extrabold text-2xl sm:text-[28px] tracking-tight">Orders</h1>
            <p class="text-ink/55 mt-1">Track, process and deliver customer orders.</p>
        </div>
    </div>

    <section class="card">
        <div class="tabs">
            <a href="{{ route('admin.orders.index', $keep(['status' => null])) }}" class="tab {{ $current === 'all' ? 'active' : '' }}">All <small>{{ $counts->sum() }}</small></a>
            @foreach (\App\Models\Order::STATUSES as $key => $label)
                <a href="{{ route('admin.orders.index', $keep(['status' => $key])) }}" class="tab {{ $current === $key ? 'active' : '' }}">{{ $label }} <small>{{ $counts[$key] ?? 0 }}</small></a>
            @endforeach
        </div>

        <form method="GET" action="{{ route('admin.orders.index') }}" class="flex flex-wrap items-center gap-3 p-5 border-b border-line">
            @if ($filters['status'] ?? null)
                <input type="hidden" name="status" value="{{ $filters['status'] }}">
            @endif
            <div class="input-group flex-1 min-w-[220px] max-w-sm">
                <svg class="ic"><use href="#i-search" /></svg>
                <input class="input" name="q" value="{{ $filters['q'] ?? '' }}" placeholder="Search order, name, phone…">
            </div>
            <div class="w-40">
                <select class="select" name="payment" onchange="this.form.submit()" aria-label="Payment">
                    <option value="">All payments</option>
                    <option value="paid" @selected(($filters['payment'] ?? '') === 'paid')>Paid</option>
                    <option value="unpaid" @selected(($filters['payment'] ?? '') === 'unpaid')>Unpaid</option>
                </select>
            </div>
            <div class="w-40">
                <select class="select" name="courier" onchange="this.form.submit()" aria-label="Courier">
                    <option value="">All couriers</option>
                    @foreach (\App\Models\Setting::COURIERS as $key => $label)
                        <option value="{{ $key }}" @selected(($filters['courier'] ?? '') === $key)>{{ $label }}</option>
                    @endforeach
                    <option value="none" @selected(($filters['courier'] ?? '') === 'none')>No courier yet</option>
                </select>
            </div>
            <input type="date" name="date" value="{{ $filters['date'] ?? '' }}" class="input w-44" onchange="this.form.submit()" aria-label="Date">
            <button class="btn btn-ghost btn-sm" type="submit">Filter</button>
            @if (array_filter($filters))
                <a href="{{ route('admin.orders.index') }}" class="btn btn-ghost btn-sm">Reset</a>
            @endif
        </form>

        <div class="overflow-x-auto">
            <table class="tbl" id="tbl">
                <thead>
                    <tr>
                        <th>Order</th>
                        <th>Customer</th>
                        <th>Date</th>
                        <th>Items</th>
                        <th>Total</th>
                        <th>Payment</th>
                        <th>Courier</th>
                        <th>Status</th>
                        <th class="text-right">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($orders as $order)
                        <tr>
                            <td><a href="{{ route('admin.orders.show', $order) }}" class="font-bold text-primary hover:underline">#{{ $order->number }}</a></td>
                            <td>
                                <p class="font-semibold">{{ $order->name }}</p>
                                <p class="text-xs text-ink/50">{{ $order->district }}</p>
                            </td>
                            <td class="text-ink/60">{{ $order->created_at->timezone(\App\Models\Setting::tz())->format('d M Y') }}</td>
                            <td>{{ $order->item_count }} {{ $order->item_count === 1 ? 'item' : 'items' }}</td>
                            <td><b>৳{{ number_format($order->total) }}</b></td>
                            <td>
                                <span class="badge {{ $order->payment_status === 'paid' ? 'b-success' : 'b-warn' }}">{{ $order->payment_status === 'paid' ? 'Paid' : 'Unpaid' }}</span>
                                <p class="text-[11px] text-ink/45 mt-1">{{ $order->payment_label }}</p>
                            </td>
                            <td>{{ $order->courier_label ?? '—' }}</td>
                            <td><span class="badge {{ $badges[$order->status] ?? 'b-muted' }}">{{ $order->status_label }}</span></td>
                            <td>
                                <div class="flex justify-end gap-1">
                                    <a href="{{ route('admin.orders.show', $order) }}" class="icon-btn" title="View"><svg class="ic"><use href="#i-eye" /></svg></a>
                                    <a href="{{ route('admin.orders.invoice.pdf', $order) }}" class="icon-btn" title="Download invoice PDF"><svg class="ic" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M12 3v12m0 0l-4-4m4 4l4-4M5 21h14"/></svg></a>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="9" class="text-ink/55">No orders found.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if ($orders->hasPages())
            <div class="flex flex-wrap items-center justify-between gap-3 px-5 py-4 border-t border-line">
                <p class="text-sm text-ink/55">Showing <b class="text-ink">{{ $orders->firstItem() }}–{{ $orders->lastItem() }}</b> of <b class="text-ink">{{ $orders->total() }}</b> results</p>
                <div class="pg flex items-center gap-1">
                    @if ($orders->onFirstPage())
                        <span class="px-1 text-ink/30" aria-hidden="true">‹</span>
                    @else
                        <a href="{{ $orders->previousPageUrl() }}" aria-label="Previous">‹</a>
                    @endif
                    @foreach ($orders->getUrlRange(max(1, $orders->currentPage() - 3), min($orders->lastPage(), $orders->currentPage() + 3)) as $page => $url)
                        <a href="{{ $url }}" @class(['active' => $page === $orders->currentPage()])>{{ $page }}</a>
                    @endforeach
                    @if ($orders->hasMorePages())
                        <a href="{{ $orders->nextPageUrl() }}" aria-label="Next">›</a>
                    @else
                        <span class="px-1 text-ink/30" aria-hidden="true">›</span>
                    @endif
                </div>
            </div>
        @endif
    </section>

@endsection
