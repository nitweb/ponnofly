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

@endsection
