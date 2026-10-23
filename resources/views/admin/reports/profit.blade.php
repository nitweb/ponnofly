@extends('admin.dashboard.dashboard')
@section('admin_title', 'Profit & loss · Nebula Mart Admin')
@section('admin_contents')

    @include('admin.reports._filters', ['title' => 'Profit & loss', 'subtitle' => 'What the store really earned', 'exportKey' => 'profit'])

    <div class="grid sm:grid-cols-2 xl:grid-cols-4 gap-5">
        @include('admin.reports._stat', ['tone' => 'is-5', 'icon' => 'dollar', 'label' => 'Net revenue', 'value' => '৳'.number_format($summary['net_revenue']), 'current' => $summary['net_revenue'], 'previous' => $previous['net_revenue']])
        @include('admin.reports._stat', ['tone' => 'is-11', 'icon' => 'box', 'label' => 'Gross profit', 'value' => '৳'.number_format($summary['gross_profit']), 'current' => $summary['gross_profit'], 'previous' => $previous['gross_profit'], 'hint' => 'After cost of goods'])
        @include('admin.reports._stat', ['tone' => 'is-5', 'icon' => 'wallet', 'label' => 'Expenses', 'value' => '৳'.number_format($summary['expenses']), 'current' => $summary['expenses'], 'previous' => $previous['expenses'], 'invert' => true])
        @include('admin.reports._stat', ['tone' => 'is-11', 'icon' => 'chart', 'label' => 'Net profit', 'value' => '৳'.number_format($summary['net_profit']), 'current' => $summary['net_profit'], 'previous' => $previous['net_profit'], 'hint' => $summary['margin'].'% net margin'])
    </div>

    <div class="grid xl:grid-cols-3 gap-6 items-start">
        <section class="card">
            <div class="card-h"><h3>Profit &amp; loss statement</h3></div>
            <div class="card-b space-y-3">
                <p class="flex justify-between"><span class="text-ink/60">Gross sales</span><b>৳{{ number_format($summary['gross_sales']) }}</b></p>
                <p class="flex justify-between"><span class="text-ink/60">Discounts</span><b class="text-danger">-৳{{ number_format($summary['discounts']) }}</b></p>
                <p class="flex justify-between"><span class="text-ink/60">Delivery fees</span><b>৳{{ number_format($summary['delivery_fees']) }}</b></p>
                <p class="flex justify-between pt-3 border-t border-line"><b>Net revenue</b><b>৳{{ number_format($summary['net_revenue']) }}</b></p>
                <p class="flex justify-between"><span class="text-ink/60">Cost of goods sold</span><b class="text-danger">-৳{{ number_format($summary['cogs']) }}</b></p>
                <p class="flex justify-between pt-3 border-t border-line"><b>Gross profit</b><b>৳{{ number_format($summary['gross_profit']) }}</b></p>
                <p class="flex justify-between"><span class="text-ink/60">Operating expenses</span><b class="text-danger">-৳{{ number_format($summary['expenses']) }}</b></p>
                <p class="flex justify-between text-lg pt-3 border-t border-line"><b>Net profit</b><b class="font-display font-extrabold {{ $summary['net_profit'] >= 0 ? 'text-success' : 'text-danger' }}">{{ $summary['net_profit'] < 0 ? '-' : '' }}৳{{ number_format(abs($summary['net_profit'])) }}</b></p>
                @if ($summary['missing_cost_items'] > 0)
                    <p class="text-xs text-ink/50">{{ $summary['missing_cost_items'] }} sold item line(s) have no cost price and count as zero cost.</p>
                @endif
            </div>
        </section>

        <section class="card xl:col-span-2">
            <div class="card-h"><h3>Revenue vs profit</h3></div>
            <div class="card-b">
                @include('admin.reports._timeline-chart', [
                    'chartId' => 'pl-chart',
                    'timeline' => $timeline,
                    'series' => [array_column($timeline, 'revenue'), array_column($timeline, 'profit')],
                    'colors' => ['#0B3A8C', '#0A8F6B'],
                    'names' => ['Net revenue', 'Net profit'],
                ])
            </div>
        </section>
    </div>

    <div class="grid xl:grid-cols-2 gap-6">
        <section class="card">
            <div class="card-h"><h3>Most profitable products</h3></div>
            <div class="overflow-x-auto">
                <table class="tbl">
                    <thead><tr><th>Product</th><th>Units</th><th>Revenue</th><th class="text-right">Profit</th></tr></thead>
                    <tbody>
                        @forelse ($products->sortByDesc('profit')->take(10) as $row)
                            <tr><td><b>{{ $row->name }}</b></td><td>{{ $row->units }}</td><td>৳{{ number_format($row->revenue) }}</td><td class="text-right font-bold">৳{{ number_format($row->profit) }}</td></tr>
                        @empty
                            <tr><td colspan="4" class="text-ink/55">No sales in this period.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </section>

        <section class="card">
            <div class="card-h"><h3>Expenses by category</h3></div>
            <div class="card-b">
                @php($expTotal = max($expenses['total'], 1))
                <div class="space-y-5">
                    @forelse ($expenses['by_category'] as $row)
                        <div>
                            <div class="flex justify-between mb-1.5"><b>{{ $row->name }}</b><span class="text-ink/55">৳{{ number_format($row->total) }}</span></div>
                            <div class="bar-track"><i style="width: {{ round($row->total / $expTotal * 100) }}%; background: {{ $row->color }}"></i></div>
                        </div>
                    @empty
                        <p class="text-ink/55">No expenses in this period.</p>
                    @endforelse
                </div>
            </div>
        </section>
    </div>

    <section class="card">
        <div class="card-h"><h3>Breakdown by {{ $period['granularity'] }}</h3></div>
        <div class="overflow-x-auto">
            <table class="tbl">
                <thead><tr><th>Period</th><th>Net revenue</th><th>Cost of goods</th><th>Expenses</th><th class="text-right">Net profit</th></tr></thead>
                <tbody>
                    @foreach (array_reverse($timeline) as $row)
                        <tr>
                            <td><b>{{ $row['label'] }}</b></td>
                            <td>৳{{ number_format($row['revenue']) }}</td>
                            <td>৳{{ number_format($row['cogs']) }}</td>
                            <td>৳{{ number_format($row['expenses']) }}</td>
                            <td class="text-right font-bold {{ $row['profit'] < 0 ? 'text-danger' : '' }}">{{ $row['profit'] < 0 ? '-' : '' }}৳{{ number_format(abs($row['profit'])) }}</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </section>

@endsection
