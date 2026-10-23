@extends('admin.dashboard.dashboard')
@section('admin_title', 'Reports · Nebula Mart Admin')
@section('admin_contents')

    @include('admin.reports._filters', ['title' => 'Reports', 'subtitle' => 'Sales, profit and customer insights', 'exportKey' => 'sales'])

    <div class="grid sm:grid-cols-2 xl:grid-cols-4 gap-5">
        @include('admin.reports._stat', ['tone' => 'is-5', 'icon' => 'dollar', 'label' => 'Net revenue', 'value' => '৳'.number_format($summary['net_revenue']), 'current' => $summary['net_revenue'], 'previous' => $previous['net_revenue'], 'hint' => $summary['orders'].' orders · '.$summary['units'].' units'])
        @include('admin.reports._stat', ['tone' => 'is-11', 'icon' => 'cart', 'label' => 'Average order value', 'value' => '৳'.number_format($summary['average_order']), 'current' => $summary['average_order'], 'previous' => $previous['average_order'], 'hint' => 'Gross sales ৳'.number_format($summary['gross_sales'])])
        @include('admin.reports._stat', ['tone' => 'is-5', 'icon' => 'wallet', 'label' => 'Expenses', 'value' => '৳'.number_format($summary['expenses']), 'current' => $summary['expenses'], 'previous' => $previous['expenses'], 'invert' => true, 'hint' => 'Cost of goods ৳'.number_format($summary['cogs'])])
        @include('admin.reports._stat', ['tone' => 'is-11', 'icon' => 'chart', 'label' => 'Net profit', 'value' => '৳'.number_format($summary['net_profit']), 'current' => $summary['net_profit'], 'previous' => $previous['net_profit'], 'hint' => $summary['margin'].'% margin'])
    </div>

    <div class="grid xl:grid-cols-3 gap-6">
        <section class="card xl:col-span-2">
            <div class="card-h"><h3>Revenue, expenses &amp; profit</h3></div>
            <div class="card-b">
                @include('admin.reports._timeline-chart', [
                    'chartId' => 'ov-chart',
                    'timeline' => $timeline,
                    'series' => [array_column($timeline, 'revenue'), array_column($timeline, 'profit'), array_column($timeline, 'expenses')],
                    'colors' => ['#0B3A8C', '#0A8F6B', '#F5A800'],
                    'names' => ['Net revenue', 'Net profit', 'Expenses'],
                ])
            </div>
        </section>

        <section class="card">
            <div class="card-h"><h3>Sales by category</h3></div>
            <div class="card-b">
                @php($catTotal = max($categories->sum('revenue'), 1))
                <div class="space-y-5">
                    @forelse ($categories->take(6) as $row)
                        <div>
                            <div class="flex justify-between mb-1.5"><b>{{ $row->name }}</b><span class="text-ink/55">৳{{ number_format($row->revenue) }}</span></div>
                            <div class="bar-track"><i style="width: {{ round($row->revenue / $catTotal * 100) }}%; background: #0B3A8C"></i></div>
                        </div>
                    @empty
                        <p class="text-ink/55">No sales in this period.</p>
                    @endforelse
                </div>
            </div>
        </section>
    </div>

    <div class="grid xl:grid-cols-2 gap-6">
        <section class="card">
            <div class="card-h"><h3>Orders by weekday</h3></div>
            <div class="card-b"><div data-bars='@json(['labels' => $weekdays['labels'], 'values' => $weekdays['orders'], 'color' => '#F5A800'])'></div></div>
        </section>

        <section class="card">
            <div class="card-h"><h3>Top districts</h3></div>
            <div class="overflow-x-auto">
                <table class="tbl">
                    <thead><tr><th>District</th><th>Orders</th><th>Revenue</th></tr></thead>
                    <tbody>
                        @forelse ($districts as $row)
                            <tr><td><b>{{ $row->district }}</b></td><td>{{ $row->orders }}</td><td>৳{{ number_format($row->revenue) }}</td></tr>
                        @empty
                            <tr><td colspan="3" class="text-ink/55">No orders in this period.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </section>
    </div>

    <div class="grid sm:grid-cols-2 xl:grid-cols-4 gap-5">
        @foreach ([
            ['admin.reports.sales', 'Sales report', 'Daily sales, status, payment, coupons', 'cart'],
            ['admin.reports.profit', 'Profit & loss', 'Revenue, cost of goods, expenses', 'dollar'],
            ['admin.reports.products', 'Products & stock', 'Best sellers, low stock, slow movers', 'box'],
            ['admin.reports.customers', 'Customers', 'Top customers, new vs returning', 'users'],
        ] as [$routeName, $name, $text, $icon])
            <a href="{{ route($routeName, request()->only(['range', 'from', 'to'])) }}" class="card p-5 no-print">
                <span class="is-5 stat-ic"><svg class="ic ic-lg"><use href="#i-{{ $icon }}" /></svg></span>
                <p class="font-display font-extrabold mt-4">{{ $name }}</p>
                <p class="text-sm text-ink/55 mt-1">{{ $text }}</p>
            </a>
        @endforeach
    </div>

    @if ($summary['missing_cost_items'] > 0)
        <p class="text-sm text-ink/55">{{ $summary['missing_cost_items'] }} sold item line(s) have no cost price, so profit may be overstated. Set the cost price on products to fix this.</p>
    @endif

@endsection
