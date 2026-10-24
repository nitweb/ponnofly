@extends('admin.dashboard.dashboard')
@section('admin_title', $customer->name.' · Customer · Nebula Mart Admin')
@section('admin_contents')

    @php
        $badges = ['pending' => 'b-warn', 'processing' => 'b-info', 'shipped' => 'b-purple', 'out_for_delivery' => 'b-info', 'delivered' => 'b-success', 'cancelled' => 'b-danger', 'returned' => 'b-muted'];
    @endphp

    <style media="print">
        #sidebar, .topbar, footer, .no-print, #overlay { display: none !important; }
        main { padding: 0 !important; }
    </style>

    <div class="flex flex-wrap items-end justify-between gap-4">
        <div class="flex items-center gap-4">
            <span class="w-14 h-14 rounded-2xl bg-gradient-to-br from-primary to-[#1d5fd1] text-white grid place-items-center font-display font-extrabold text-xl">{{ mb_strtoupper(mb_substr($customer->name, 0, 1)) }}</span>
            <div>
                <h1 class="font-display font-extrabold text-2xl sm:text-[28px] tracking-tight">{{ $customer->name }}</h1>
                <p class="text-ink/55 mt-1">{{ $customer->email }}{{ $customer->phone ? ' · '.$customer->phone : '' }} · Joined {{ $customer->created_at->timezone(\App\Models\Setting::tz())->format('d M Y') }}</p>
            </div>
        </div>
        <div class="flex flex-wrap gap-3 no-print">
            @if ($customer->is_restricted)
                <form method="POST" action="{{ route('admin.customers.restriction', $customer) }}" data-swal data-swal-title="Allow purchases?" data-swal-text="{{ $customer->name }} will be able to place orders again." data-swal-confirm="Yes, allow">
                    @csrf
                    @method('PATCH')
                    <button type="submit" class="btn btn-primary">Allow purchases</button>
                </form>
            @else
                <form method="POST" action="{{ route('admin.customers.restriction', $customer) }}" class="flex flex-wrap items-center gap-2" data-swal data-swal-title="Restrict purchases?" data-swal-text="{{ $customer->name }} will not be able to place orders." data-swal-confirm="Yes, restrict" data-swal-danger="1">
                    @csrf
                    @method('PATCH')
                    <input class="input !w-56" name="reason" maxlength="255" placeholder="Reason (optional)" aria-label="Restriction reason">
                    <button type="submit" class="btn btn-ghost text-danger">Restrict purchases</button>
                </form>
            @endif
            <button class="btn btn-ghost" data-print><svg class="ic ic-sm"><use href="#i-printer" /></svg>Print</button>
            <a href="{{ route('admin.customers.index') }}" class="btn btn-ghost"><svg class="ic ic-sm"><use href="#i-chev-left" /></svg>Back</a>
        </div>
    </div>

    @if ($customer->is_restricted)
        <p class="rounded-xl bg-accent/30 border border-line px-4 py-3 text-sm font-semibold text-danger" role="alert">
            Restricted from placing orders{{ $customer->restricted_at ? ' since '.$customer->restricted_at->timezone(\App\Models\Setting::tz())->format('d M Y') : '' }}{{ $customer->restriction_reason ? ' · '.$customer->restriction_reason : '' }}
        </p>
    @endif

    <div class="grid sm:grid-cols-2 xl:grid-cols-4 gap-5">
        @include('admin.reports._stat', ['tone' => 'is-5', 'icon' => 'dollar', 'label' => 'Lifetime spend', 'value' => '৳'.number_format($profile['spent']), 'hint' => 'Excludes cancelled & returned'])
        @include('admin.reports._stat', ['tone' => 'is-11', 'icon' => 'cart', 'label' => 'Orders', 'value' => number_format($profile['valid_count']), 'hint' => $profile['units'].' units · '.$profile['lost_count'].' cancelled / returned'])
        @include('admin.reports._stat', ['tone' => 'is-5', 'icon' => 'wallet', 'label' => 'Average order', 'value' => '৳'.number_format($profile['average_order']), 'hint' => 'Discounts used ৳'.number_format($profile['discounts'])])
        @include('admin.reports._stat', ['tone' => 'is-11', 'icon' => 'chart', 'label' => 'Profit from customer', 'value' => '৳'.number_format($profile['spent'] - $profile['cogs']), 'hint' => 'Before expenses'])
    </div>

    <div class="grid xl:grid-cols-3 gap-6 items-start">
        <section class="card xl:col-span-2">
            <div class="card-h"><h3>Spend, last 12 months</h3></div>
            <div class="card-b"><div data-bars='@json(['labels' => $profile['months']['labels'], 'values' => $profile['months']['values'], 'color' => '#0B3A8C'])'></div></div>
        </section>

        <section class="card">
            <div class="card-h"><h3>Customer details</h3></div>
            <div class="card-b space-y-3 text-sm">
                <p class="flex justify-between"><span class="text-ink/60">First order</span><b>{{ $profile['first_order']?->timezone(\App\Models\Setting::tz())->format('d M Y') ?? '—' }}</b></p>
                <p class="flex justify-between"><span class="text-ink/60">Last order</span><b>{{ $profile['last_order']?->timezone(\App\Models\Setting::tz())->format('d M Y') ?? '—' }}</b></p>
                @foreach ($profile['by_status'] as $status => $count)
                    <p class="flex justify-between"><span class="badge {{ $badges[$status] ?? 'b-muted' }}">{{ \App\Models\Order::STATUSES[$status] ?? $status }}</span><b>{{ $count }}</b></p>
                @endforeach
                @foreach ($customer->addresses as $address)
                    <p class="pt-3 border-t border-line text-ink/70">{{ $address->full_address }}</p>
                @endforeach
            </div>
        </section>
    </div>

    <div class="grid xl:grid-cols-3 gap-6 items-start">
        <section class="card">
            <div class="card-h"><h3>Favourite products</h3></div>
            <div class="overflow-x-auto">
                <table class="tbl">
                    <thead><tr><th>Product</th><th>Units</th><th class="text-right">Spent</th></tr></thead>
                    <tbody>
                        @forelse ($profile['products'] as $row)
                            <tr><td><b>{{ $row->name }}</b></td><td>{{ $row->units }}</td><td class="text-right font-bold">৳{{ number_format($row->spent) }}</td></tr>
                        @empty
                            <tr><td colspan="3" class="text-ink/55">No purchases yet.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </section>

        <section class="card xl:col-span-2">
            <div class="card-h"><h3>Order history</h3></div>
            <div class="overflow-x-auto">
                <table class="tbl">
                    <thead><tr><th>Order</th><th>Date</th><th>Items</th><th>Status</th><th class="text-right">Total</th></tr></thead>
                    <tbody>
                        @forelse ($profile['orders'] as $order)
                            <tr>
                                <td><a href="{{ route('admin.orders.show', $order) }}" class="font-bold text-primary hover:underline">#{{ $order->number }}</a></td>
                                <td class="text-ink/60">{{ $order->created_at->timezone(\App\Models\Setting::tz())->format('d M Y') }}</td>
                                <td>{{ $order->items->sum('quantity') }}</td>
                                <td><span class="badge {{ $badges[$order->status] ?? 'b-muted' }}">{{ $order->status_label }}</span></td>
                                <td class="text-right font-bold">৳{{ number_format($order->total) }}</td>
                            </tr>
                        @empty
                            <tr><td colspan="5" class="text-ink/55">No orders yet.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </section>
    </div>

@include('admin.partials.swal-confirm')

@endsection
