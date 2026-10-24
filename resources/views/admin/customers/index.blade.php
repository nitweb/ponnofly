@extends('admin.dashboard.dashboard')
@section('admin_title', 'Customers · Nebula Mart Admin')
@section('admin_contents')

    <div class="flex flex-wrap items-end justify-between gap-4">
        <div>
            <h1 class="font-display font-extrabold text-2xl sm:text-[28px] tracking-tight">Customers</h1>
            <p class="text-ink/55 mt-1">Registered customers and accounts created at checkout.</p>
        </div>
    </div>

    <section class="card">
        <form method="GET" action="{{ route('admin.customers.index') }}" class="flex flex-wrap items-center gap-3 p-5 border-b border-line">
            <div class="input-group flex-1 min-w-[220px] max-w-sm">
                <svg class="ic"><use href="#i-search" /></svg>
                <input class="input" name="q" value="{{ $term }}" placeholder="Search name, email, phone…">
            </div>
            <button class="btn btn-ghost btn-sm" type="submit">Search</button>
            @if ($term !== '')
                <a href="{{ route('admin.customers.index') }}" class="btn btn-ghost btn-sm">Reset</a>
            @endif
        </form>

        <div class="overflow-x-auto">
            <table class="tbl">
                <thead>
                    <tr>
                        <th>Customer</th>
                        <th>Phone</th>
                        <th>Orders</th>
                        <th>Ordered items</th>
                        <th>Total spent</th>
                        <th>Joined</th>
                        <th>Purchase</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($customers as $customer)
                        <tr>
                            <td>
                                <a href="{{ route('admin.customers.show', $customer) }}" class="font-semibold text-primary hover:underline">{{ $customer->name }}</a>
                                <p class="text-xs text-ink/50">{{ $customer->email }}</p>
                                @if ($customer->is_restricted)
                                    <span class="badge b-danger mt-1">Restricted</span>
                                @endif
                            </td>
                            <td>{{ $customer->phone ?? '—' }}</td>
                            <td>{{ $customer->orders_count }}</td>
                            <td>
                                @forelse ($customer->orders as $order)
                                    <div class="mb-2 last:mb-0">
                                        <a href="{{ route('admin.orders.show', $order) }}" class="font-bold text-primary hover:underline">#{{ $order->number }}</a>
                                        <span class="badge {{ ['pending' => 'b-warn', 'processing' => 'b-info', 'shipped' => 'b-purple', 'out_for_delivery' => 'b-info', 'delivered' => 'b-success', 'cancelled' => 'b-danger', 'returned' => 'b-muted'][$order->status] ?? 'b-muted' }}">{{ $order->status_label }}</span>
                                        <p class="text-xs text-ink/60">
                                            @foreach ($order->items as $item){{ $item->name }}{{ $item->variant_label ? ' ('.$item->variant_label.')' : '' }} ×{{ $item->quantity }}{{ ! $loop->last ? ', ' : '' }}@endforeach
                                        </p>
                                    </div>
                                @empty
                                    <span class="text-ink/45">—</span>
                                @endforelse
                                @if ($customer->orders_count > 5)
                                    <p class="text-xs text-ink/45">+{{ $customer->orders_count - 5 }} more</p>
                                @endif
                            </td>
                            <td><b>৳{{ number_format((float) $customer->orders_sum_total) }}</b></td>
                            <td class="text-ink/60">{{ $customer->created_at->timezone(\App\Models\Setting::tz())->format('d M Y') }}</td>
                            <td>
                                <form method="POST" action="{{ route('admin.customers.restriction', $customer) }}" data-swal data-swal-title="{{ $customer->is_restricted ? 'Allow purchases?' : 'Restrict purchases?' }}" data-swal-text="{{ $customer->is_restricted ? $customer->name.' will be able to place orders again.' : $customer->name.' will not be able to place orders.' }}" data-swal-confirm="{{ $customer->is_restricted ? 'Yes, allow' : 'Yes, restrict' }}" data-swal-danger="{{ $customer->is_restricted ? '0' : '1' }}">
                                    @csrf
                                    @method('PATCH')
                                    <button type="submit" class="btn btn-ghost btn-sm {{ $customer->is_restricted ? '' : 'text-danger' }}">{{ $customer->is_restricted ? 'Allow' : 'Restrict' }}</button>
                                </form>
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="7" class="text-ink/55">No customers found.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if ($customers->hasPages())
            <div class="flex flex-wrap items-center justify-between gap-3 px-5 py-4 border-t border-line">
                <p class="text-sm text-ink/55">Showing <b class="text-ink">{{ $customers->firstItem() }}–{{ $customers->lastItem() }}</b> of <b class="text-ink">{{ $customers->total() }}</b> results</p>
                <div class="pg flex items-center gap-1">
                    @foreach ($customers->getUrlRange(max(1, $customers->currentPage() - 3), min($customers->lastPage(), $customers->currentPage() + 3)) as $page => $url)
                        <a href="{{ $url }}" @class(['active' => $page === $customers->currentPage()])>{{ $page }}</a>
                    @endforeach
                </div>
            </div>
        @endif
    </section>

@include('admin.partials.swal-confirm')

@endsection
