@extends('admin.dashboard.dashboard')
@section('admin_title', 'Dashboard · Nebula Mart Admin')
@section('admin_contents')

    @php
        $dashAdmin = auth('admin')->user();
        $orderBadges = ['pending' => 'b-warn', 'processing' => 'b-info', 'shipped' => 'b-purple', 'out_for_delivery' => 'b-info', 'delivered' => 'b-success', 'cancelled' => 'b-danger', 'returned' => 'b-muted'];
        $statCards = [
            ['key' => 'revenue', 'label' => 'Total Revenue', 'icon' => 'dollar', 'tone' => 'is-5', 'color' => '#0B3A8C', 'prefix' => '৳'],
            ['key' => 'orders', 'label' => 'Total Orders', 'icon' => 'cart', 'tone' => 'is-11', 'color' => '#F5A800', 'prefix' => ''],
            ['key' => 'customers', 'label' => 'Customers', 'icon' => 'users', 'tone' => 'is-9', 'color' => '#0A8F6B', 'prefix' => ''],
            ['key' => 'products', 'label' => 'Products', 'icon' => 'box', 'tone' => 'is-7', 'color' => '#7C3AED', 'prefix' => ''],
        ];
        $statusTotal = array_sum(array_column($statuses, 'count'));
        $donut = [];
        foreach ($statuses as $status) {
            if ($status['count'] > 0) {
                $donut[] = ['l' => $status['label'], 'v' => $status['count'], 'c' => $status['color']];
            }
        }
        $sparkData = [];
        foreach ($statCards as $card) {
            $sparkData[$card['key']] = json_encode($stats[$card['key']]['spark']);
        }
    @endphp

    <div class="flex flex-wrap items-end justify-between gap-4">
        <div>
            <h1 class="font-display font-extrabold text-2xl sm:text-[28px] tracking-tight">Dashboard</h1>
            <p class="text-ink/55 mt-1">Welcome back, {{ \Illuminate\Support\Str::before($dashAdmin->name, ' ') }} — here’s what’s happening today.</p>
        </div>
        <div class="flex flex-wrap gap-3">
            @if ($dashAdmin->hasPermission('reports:export'))
                <a href="{{ route('admin.reports.export', 'sales') }}" class="btn btn-ghost">
                    <svg class="ic ic-sm">
                        <use href="#i-download" />
                    </svg>
                    Export report
                </a>
            @endif
            @if ($dashAdmin->hasPermission('products:create'))
                <a href="{{ route('admin.products.create') }}" class="btn btn-primary">
                    <svg class="ic ic-sm">
                        <use href="#i-plus" />
                    </svg>
                    Add product
                </a>
            @endif
        </div>
    </div>

    <div class="grid sm:grid-cols-2 xl:grid-cols-4 gap-5">
        @foreach ($statCards as $card)
            @php
                $stat = $stats[$card['key']];
                $growth = $stat['growth'];
            @endphp
            <div class="card p-5">
                <div class="flex items-start justify-between">
                    <span class="{{ $card['tone'] }} stat-ic">
                        <svg class="ic ic-lg">
                            <use href="#i-{{ $card['icon'] }}" />
                        </svg>
                    </span>
                    @if ($growth !== null)
                        <span class="badge {{ $growth >= 0 ? 'b-success' : 'b-danger' }} !py-0" title="Last 30 days vs the 30 days before">
                            <svg class="ic ic-sm">
                                <use href="#i-{{ $growth >= 0 ? 'up' : 'down' }}" />
                            </svg>
                            {{ abs($growth) }}%
                        </span>
                    @endif
                </div>
                <p class="text-ink/55 font-semibold mt-4">{{ $card['label'] }}</p>
                <p class="font-display font-extrabold text-[28px] tracking-tight leading-tight">{{ $card['prefix'] }}{{ number_format($stat['value']) }}</p>
                <div data-spark='{{ $sparkData[$card['key']] }}' data-color="{{ $card['color'] }}" class="mt-2"></div>
            </div>
        @endforeach
    </div>

    {{-- Revenue overview / Orders by status --}}
    <div class="grid xl:grid-cols-3 gap-6">

        {{-- Revenue overview --}}
        <section class="card xl:col-span-2">
            <div class="card-h">
                <h3>Revenue overview</h3>
                <div id="rev-sw" class="pills">
                    <span class="pill active" data-range="7d">7D</span>
                    <span class="pill" data-range="30d">30D</span>
                    <span class="pill" data-range="12m">12M</span>
                </div>
            </div>
            <div class="card-b">
                <div class="flex gap-5 mb-4 text-sm font-semibold">
                    <span class="flex items-center gap-2">
                        <i class="w-3 h-3 rounded-full bg-primary"></i>
                        Revenue
                    </span>
                    <span class="flex items-center gap-2">
                        <i class="w-3 h-3 rounded-full bg-accent"></i>
                        Profit
                    </span>
                </div>
                <div id="rev" data-area='@json($chart)' data-switch="#rev-sw"></div>
            </div>
        </section>

        {{-- Orders by status --}}
        <section class="card ">
            <div class="card-h">
                <h3>Orders by status</h3>
            </div>
            <div class="card-b">
                @if ($statusTotal > 0)
                    <div class="relative w-44 h-44 mx-auto" data-donut='@json($donut)' data-center="Orders"></div>
                @else
                    <div class="w-44 h-44 mx-auto grid place-items-center text-center text-sm text-ink/50 rounded-full border-4 border-dashed border-line">No orders yet</div>
                @endif
                <ul class="mt-6 space-y-3 text-sm">
                    @foreach ($statuses as $status)
                        <li class="flex items-center justify-between">
                            <span class="flex items-center gap-2">
                                <i class="w-2.5 h-2.5 rounded-full" style="background: {{ $status['color'] }}"></i>
                                {{ $status['label'] }}
                            </span>
                            <b>{{ number_format($status['count']) }}</b>
                        </li>
                    @endforeach
                </ul>
            </div>
        </section>

    </div>

    {{-- Recent orders / Top selling products --}}
    <div class="grid xl:grid-cols-3 gap-6">

        {{-- Recent orders --}}
        <section class="card xl:col-span-2">
            <div class="card-h">
                <h3>Recent orders</h3>
                @if ($dashAdmin->hasPermission('orders:view'))
                    <a href="{{ route('admin.orders.index') }}" class="btn btn-ghost btn-sm">View all</a>
                @endif
            </div>
            <div class="">
                <div class="overflow-x-auto">
                    <table class="tbl">
                        <thead>
                            <tr>
                                <th>Order</th>
                                <th>Customer</th>
                                <th>Total</th>
                                <th>Status</th>
                                <th>Date</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse ($recentOrders as $order)
                                <tr>
                                    <td>
                                        @if ($dashAdmin->hasPermission('orders:view'))
                                            <a href="{{ route('admin.orders.show', $order) }}" class="font-bold text-primary hover:underline">#{{ $order->number }}</a>
                                        @else
                                            <b>#{{ $order->number }}</b>
                                        @endif
                                    </td>
                                    <td>
                                        <p class="font-semibold">{{ $order->name }}</p>
                                        <p class="text-xs text-ink/50">{{ $order->district }}</p>
                                    </td>
                                    <td class="font-bold">৳{{ number_format($order->total) }}</td>
                                    <td><span class="badge {{ $orderBadges[$order->status] ?? 'b-muted' }}">{{ $order->status_label }}</span></td>
                                    <td class="text-ink/60">{{ $order->created_at->timezone(\App\Models\Setting::tz())->format('d M Y') }}</td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="5" class="text-center text-ink/50 py-8">No orders yet</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </section>

        {{-- Top selling products --}}
        <section class="card ">
            <div class="card-h">
                <h3>Top selling products</h3>
                @if ($dashAdmin->hasPermission('reports:view'))
                    <a href="{{ route('admin.reports.products') }}" class="text-sm font-semibold text-primary">Report</a>
                @endif
            </div>
            <div class="card-b">
                <ul class="space-y-4">
                    @forelse ($topProducts as $product)
                        <li class="flex items-center gap-3">
                            <img class="thumb" src="{{ $product->image }}" alt="">
                            <div class="min-w-0 flex-1">
                                <p class="font-semibold truncate">{{ $product->name }}</p>
                                <p class="text-xs text-ink/50">{{ number_format($product->units) }} sold</p>
                            </div>
                            <b>৳{{ number_format($product->revenue) }}</b>
                        </li>
                    @empty
                        <li class="text-sm text-ink/50 text-center py-6">No sales yet</li>
                    @endforelse
                </ul>
            </div>
        </section>

    </div>

    {{-- Low stock alert / Courier split / Quick actions --}}
    <div class="grid lg:grid-cols-3 gap-6">

        {{-- Low stock alert --}}
        <section class="card ">
            <div class="card-h">
                <h3>Low stock alert</h3>
            </div>
            <div class="card-b">
                <ul class="space-y-4">
                    @forelse ($lowStock as $product)
                        <li class="flex items-center gap-3">
                            <img class="thumb !w-10 !h-10" src="{{ $product->cover_url }}" alt="">
                            <p class="flex-1 min-w-0 truncate font-semibold">{{ $product->name }}</p>
                            <span class="badge {{ $product->stock_total <= 0 ? 'b-danger' : 'b-warn' }}">{{ $product->stock_total }} left</span>
                        </li>
                    @empty
                        <li class="text-sm text-ink/50 text-center py-6">Every product is well stocked</li>
                    @endforelse
                </ul>
                @if ($dashAdmin->hasPermission('products:view'))
                    <a href="{{ route('admin.products.index') }}" class="btn btn-ghost btn-sm w-full mt-5">Manage inventory</a>
                @endif
            </div>
        </section>

        {{-- Courier split --}}
        <section class="card ">
            <div class="card-h">
                <h3>Courier split</h3>
            </div>
            <div class="card-b">
                <div class="space-y-5">
                    @forelse ($couriers as $courier)
                        <div>
                            <div class="flex justify-between mb-1.5">
                                <b>{{ $courier['label'] }}</b>
                                <span class="text-ink/55">{{ $courier['percent'] }}%</span>
                            </div>
                            <div class="bar-track"><i class="{{ ['is-19', 'is-20', 'is-21'][$loop->index % 3] }}" style="width: {{ $courier['percent'] }}%"></i></div>
                        </div>
                    @empty
                        <p class="text-sm text-ink/50 text-center py-6">No courier orders in the last 30 days</p>
                    @endforelse
                </div>
                <p class="text-xs text-ink/50 mt-5">Cash on Delivery only · based on last 30 days</p>
            </div>
        </section>

        {{-- Quick actions --}}
        <section class="card ">
            <div class="card-h">
                <h3>Quick actions</h3>
            </div>
            <div class="card-b">
                <div class="grid grid-cols-2 gap-3">
                    @foreach ([
                        ['products', 'admin.products.create', 'plus', 'Add product'],
                        ['coupons', 'admin.coupons.create', 'ticket', 'New coupon'],
                        ['flash', 'admin.flash-deals.create', 'zap', 'Flash deal'],
                        ['banners', 'admin.banners.create', 'image', 'Add banner'],
                    ] as [$module, $route, $icon, $label])
                        @if ($dashAdmin->hasPermission("{$module}:create"))
                            <a href="{{ route($route) }}" class="rounded-2xl border border-line p-4 hover:border-primary hover:bg-primary-soft transition">
                                <span class="w-10 h-10 rounded-xl bg-primary/10 text-primary grid place-items-center mb-3">
                                    <svg class="ic ">
                                        <use href="#i-{{ $icon }}" />
                                    </svg>
                                </span>
                                <b>{{ $label }}</b>
                            </a>
                        @endif
                    @endforeach
                </div>
            </div>
        </section>

    </div>

@endsection
