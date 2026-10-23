@extends('admin.dashboard.dashboard')
@section('admin_title', 'Sales report · Nebula Mart Admin')
@section('admin_contents')

    @php
        $badges = ['pending' => 'b-warn', 'processing' => 'b-info', 'shipped' => 'b-purple', 'out_for_delivery' => 'b-info', 'delivered' => 'b-success', 'cancelled' => 'b-danger', 'returned' => 'b-muted'];
        $statusColors = ['pending' => '#F5A800', 'processing' => '#2563EB', 'shipped' => '#7C3AED', 'out_for_delivery' => '#0891B2', 'delivered' => '#0A8F6B', 'cancelled' => '#DC2626', 'returned' => '#64748B'];
        $donut = $statuses->map(fn ($row, $status) => ['l' => $orderStatuses[$status] ?? $status, 'v' => (int) $row->orders, 'c' => $statusColors[$status] ?? '#64748B'])->values();
    @endphp

    @include('admin.reports._filters', ['title' => 'Sales report', 'subtitle' => 'Orders, revenue and what sells', 'exportKey' => 'sales'])

    <div class="grid sm:grid-cols-2 xl:grid-cols-4 gap-5">
        @include('admin.reports._stat', ['tone' => 'is-5', 'icon' => 'dollar', 'label' => 'Gross sales', 'value' => '৳'.number_format($summary['gross_sales']), 'current' => $summary['gross_sales'], 'previous' => $previous['gross_sales']])
        @include('admin.reports._stat', ['tone' => 'is-11', 'icon' => 'cart', 'label' => 'Orders', 'value' => number_format($summary['orders']), 'current' => $summary['orders'], 'previous' => $previous['orders'], 'hint' => $summary['units'].' units sold'])
        @include('admin.reports._stat', ['tone' => 'is-5', 'icon' => 'ticket', 'label' => 'Discounts', 'value' => '৳'.number_format($summary['discounts']), 'hint' => 'Delivery fees ৳'.number_format($summary['delivery_fees'])])
        @include('admin.reports._stat', ['tone' => 'is-11', 'icon' => 'wallet', 'label' => 'Net revenue', 'value' => '৳'.number_format($summary['net_revenue']), 'current' => $summary['net_revenue'], 'previous' => $previous['net_revenue'], 'hint' => 'Average order ৳'.number_format($summary['average_order'])])
    </div>

    <div class="grid xl:grid-cols-3 gap-6 items-start">
        <section class="card xl:col-span-2">
            <div class="card-h"><h3>Net revenue</h3></div>
            <div class="card-b">
                @include('admin.reports._timeline-chart', ['chartId' => 'sales-chart', 'timeline' => $timeline, 'series' => [array_column($timeline, 'revenue')], 'colors' => ['#0B3A8C'], 'names' => ['Net revenue']])
            </div>
        </section>

        <section class="card">
            <div class="card-h"><h3>Orders by status</h3></div>
            <div class="card-b">
                <div class="relative w-44 h-44 mx-auto" data-donut='@json($donut)' data-center="Orders"></div>
                <ul class="mt-6 space-y-3 text-sm">
                    @foreach ($statuses as $status => $row)
                        <li class="flex items-center justify-between">
                            <span class="flex items-center gap-2"><i class="w-2.5 h-2.5 rounded-full" style="background: {{ $statusColors[$status] ?? '#64748B' }}"></i>{{ $orderStatuses[$status] ?? $status }}</span>
                            <b>{{ $row->orders }} <span class="text-ink/45 font-semibold">· ৳{{ number_format($row->value) }}</span></b>
                        </li>
                    @endforeach
                </ul>
                <p class="text-xs text-ink/50 mt-5">{{ $summary['lost_orders'] }} cancelled / returned orders (৳{{ number_format($summary['lost_value']) }}) are not counted as sales.</p>
            </div>
        </section>
    </div>

    <div class="grid xl:grid-cols-2 gap-6">
        <section class="card">
            <div class="card-h"><h3>Top products</h3></div>
            <div class="overflow-x-auto">
                <table class="tbl">
                    <thead><tr><th>Product</th><th>Units</th><th class="text-right">Revenue</th></tr></thead>
                    <tbody>
                        @forelse ($products as $row)
                            <tr><td><b>{{ $row->name }}</b></td><td>{{ $row->units }}</td><td class="text-right font-bold">৳{{ number_format($row->revenue) }}</td></tr>
                        @empty
                            <tr><td colspan="3" class="text-ink/55">No sales in this period.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </section>

        <section class="card">
            <div class="card-h"><h3>Sales by category</h3></div>
            <div class="card-b">
                @php($catTotal = max($categories->sum('revenue'), 1))
                <div class="space-y-5">
                    @forelse ($categories->take(8) as $row)
                        <div>
                            <div class="flex justify-between mb-1.5"><b>{{ $row->name }}</b><span class="text-ink/55">{{ $row->units }} units · ৳{{ number_format($row->revenue) }}</span></div>
                            <div class="bar-track"><i style="width: {{ round($row->revenue / $catTotal * 100) }}%; background: #0B3A8C"></i></div>
                        </div>
                    @empty
                        <p class="text-ink/55">No sales in this period.</p>
                    @endforelse
                </div>
            </div>
        </section>
    </div>

    <div class="grid xl:grid-cols-3 gap-6">
        <section class="card">
            <div class="card-h"><h3>Orders by weekday</h3></div>
            <div class="card-b"><div data-bars='@json(['labels' => $weekdays['labels'], 'values' => $weekdays['orders'], 'color' => '#F5A800'])'></div></div>
        </section>

        <section class="card">
            <div class="card-h"><h3>Payment</h3></div>
            <div class="overflow-x-auto">
                <table class="tbl">
                    <thead><tr><th>Method</th><th>Status</th><th>Orders</th><th class="text-right">Amount</th></tr></thead>
                    <tbody>
                        @forelse ($payments as $row)
                            <tr>
                                <td>{{ \App\Models\Order::PAYMENT_METHODS[$row->payment_method] ?? strtoupper($row->payment_method) }}</td>
                                <td><span class="badge {{ $row->payment_status === 'paid' ? 'b-success' : 'b-warn' }}">{{ ucfirst($row->payment_status) }}</span></td>
                                <td>{{ $row->orders }}</td>
                                <td class="text-right font-bold">৳{{ number_format($row->value) }}</td>
                            </tr>
                        @empty
                            <tr><td colspan="4" class="text-ink/55">No orders.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </section>

        <section class="card">
            <div class="card-h"><h3>Coupon usage</h3></div>
            <div class="overflow-x-auto">
                <table class="tbl">
                    <thead><tr><th>Code</th><th>Orders</th><th class="text-right">Discount</th></tr></thead>
                    <tbody>
                        @forelse ($coupons as $row)
                            <tr><td><span class="font-mono font-bold">{{ $row->coupon_code }}</span></td><td>{{ $row->orders }}</td><td class="text-right font-bold">৳{{ number_format($row->discount) }}</td></tr>
                        @empty
                            <tr><td colspan="3" class="text-ink/55">No coupons used.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </section>
    </div>

    <section class="card">
        <div class="card-h"><h3>Breakdown by {{ $period['granularity'] }}</h3></div>
        <div class="overflow-x-auto">
            <table class="tbl">
                <thead><tr><th>Period</th><th>Orders</th><th>Units</th><th>Gross sales</th><th>Discounts</th><th class="text-right">Net revenue</th></tr></thead>
                <tbody>
                    @foreach (array_reverse($timeline) as $row)
                        <tr>
                            <td><b>{{ $row['label'] }}</b></td>
                            <td>{{ $row['orders'] }}</td>
                            <td>{{ $row['units'] }}</td>
                            <td>৳{{ number_format($row['gross']) }}</td>
                            <td>৳{{ number_format($row['discount']) }}</td>
                            <td class="text-right font-bold">৳{{ number_format($row['revenue']) }}</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </section>

@endsection
