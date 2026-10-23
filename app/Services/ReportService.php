<?php

namespace App\Services;

use App\Models\Admin;
use App\Models\Expense;
use App\Models\Order;
use App\Models\Product;
use App\Models\User;
use Carbon\CarbonImmutable;
use Carbon\CarbonInterface;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

class ReportService
{
    public const RANGES = [
        'today' => 'Today',
        '7d' => 'Last 7 days',
        '30d' => 'Last 30 days',
        'this_month' => 'This month',
        'last_month' => 'Last month',
        'this_year' => 'This year',
        'custom' => 'Custom range',
    ];

    /** Orders in these statuses never count as sales. */
    public const EXCLUDED_STATUSES = ['cancelled', 'returned'];

    /** Order of the weekday chart (Saturday first, as in the admin design). */
    private const WEEKDAYS = [6 => 'Sat', 0 => 'Sun', 1 => 'Mon', 2 => 'Tue', 3 => 'Wed', 4 => 'Thu', 5 => 'Fri'];

    /**
     * @return array{key: string, from: CarbonImmutable, to: CarbonImmutable, label: string, days: int, granularity: string}
     */
    public function period(Request $request): array
    {
        $key = (string) $request->query('range', '30d');
        $key = array_key_exists($key, self::RANGES) ? $key : '30d';
        $today = CarbonImmutable::today();

        [$from, $to] = match ($key) {
            'today' => [$today, $today],
            '7d' => [$today->subDays(6), $today],
            'this_month' => [$today->startOfMonth(), $today],
            'last_month' => [$today->subMonthNoOverflow()->startOfMonth(), $today->subMonthNoOverflow()->endOfMonth()->startOfDay()],
            'this_year' => [$today->startOfYear(), $today],
            'custom' => $this->customRange($request, $today),
            default => [$today->subDays(29), $today],
        };

        $days = (int) $from->diffInDays($to) + 1;

        return [
            'key' => $key,
            'from' => $from->startOfDay(),
            'to' => $to->endOfDay(),
            'label' => $from->isSameDay($to) ? $from->format('d M Y') : $from->format('d M Y').' – '.$to->format('d M Y'),
            'days' => $days,
            'granularity' => $days <= 31 ? 'day' : ($days <= 92 ? 'week' : 'month'),
        ];
    }

    /**
     * @return array{0: CarbonImmutable, 1: CarbonImmutable}
     */
    private function customRange(Request $request, CarbonImmutable $today): array
    {
        try {
            $from = CarbonImmutable::parse((string) $request->query('from'))->startOfDay();
            $to = CarbonImmutable::parse((string) $request->query('to'))->startOfDay();
        } catch (\Throwable) {
            return [$today->subDays(29), $today];
        }

        if ($to->lt($from)) {
            [$from, $to] = [$to, $from];
        }

        // Keep the report fast: at most five years at once.
        return [$from->max($to->subYears(5)), $to];
    }

    /** Orders that count as real sales inside the period. */
    public function salesQuery(array $period)
    {
        return Order::query()
            ->whereBetween('created_at', [$period['from'], $period['to']])
            ->whereNotIn('status', self::EXCLUDED_STATUSES);
    }

    private function itemsQuery(array $period)
    {
        return DB::table('order_items')
            ->join('orders', 'orders.id', '=', 'order_items.order_id')
            ->whereBetween('orders.created_at', [$period['from'], $period['to']])
            ->whereNotIn('orders.status', self::EXCLUDED_STATUSES);
    }

    private function expenseQuery(array $period)
    {
        return Expense::query()
            ->whereDate('expense_date', '>=', $period['from']->toDateString())
            ->whereDate('expense_date', '<=', $period['to']->toDateString());
    }

    /**
     * Headline numbers: sales, cost of goods, expenses and profit.
     *
     * @return array<string, float|int>
     */
    public function summary(array $period): array
    {
        $orders = $this->salesQuery($period)
            ->selectRaw('COUNT(*) as orders, COALESCE(SUM(subtotal),0) as gross, COALESCE(SUM(discount),0) as discount, COALESCE(SUM(delivery_fee),0) as delivery, COALESCE(SUM(total),0) as net')
            ->first();

        $items = $this->itemsQuery($period)
            ->selectRaw('COALESCE(SUM(order_items.quantity),0) as units, COALESCE(SUM(order_items.cost_price * order_items.quantity),0) as cogs, COALESCE(SUM(CASE WHEN order_items.cost_price IS NULL THEN 1 ELSE 0 END),0) as missing_cost')
            ->first();

        $lost = Order::query()
            ->whereBetween('created_at', [$period['from'], $period['to']])
            ->whereIn('status', self::EXCLUDED_STATUSES)
            ->selectRaw('COUNT(*) as orders, COALESCE(SUM(total),0) as value')
            ->first();

        $expenses = (float) $this->expenseQuery($period)->sum('amount');
        $count = (int) $orders->orders;
        $net = (float) $orders->net;
        $cogs = (float) $items->cogs;
        $grossProfit = $net - $cogs;
        $netProfit = $grossProfit - $expenses;

        return [
            'orders' => $count,
            'units' => (int) $items->units,
            'gross_sales' => (float) $orders->gross,
            'discounts' => (float) $orders->discount,
            'delivery_fees' => (float) $orders->delivery,
            'net_revenue' => $net,
            'average_order' => $count ? $net / $count : 0.0,
            'cogs' => $cogs,
            'gross_profit' => $grossProfit,
            'expenses' => $expenses,
            'net_profit' => $netProfit,
            'margin' => $net > 0 ? round($netProfit / $net * 100, 1) : 0.0,
            'missing_cost_items' => (int) $items->missing_cost,
            'lost_orders' => (int) $lost->orders,
            'lost_value' => (float) $lost->value,
        ];
    }

    /**
     * The same summary for the equal-length period right before, to show growth.
     *
     * @return array<string, float|int>
     */
    public function previousSummary(array $period): array
    {
        $length = $period['days'];
        $from = $period['from']->subDays($length);
        $to = $period['from']->subSecond();

        return $this->summary(array_merge($period, ['from' => $from, 'to' => $to]));
    }

    public function growth(float|int $current, float|int $previous): ?float
    {
        if ($previous == 0) {
            return null;
        }

        return round(($current - $previous) / abs($previous) * 100, 1);
    }

    private function bucketKey(CarbonInterface $date, string $granularity): string
    {
        return match ($granularity) {
            'month' => $date->format('Y-m'),
            'week' => $date->copy()->startOfWeek(CarbonInterface::SATURDAY)->format('Y-m-d'),
            default => $date->format('Y-m-d'),
        };
    }

    /**
     * Empty buckets for every day / week / month in the period so charts have no gaps.
     *
     * @return array<string, array<string, mixed>>
     */
    private function buckets(array $period): array
    {
        $g = $period['granularity'];
        $cursor = $g === 'month' ? $period['from']->startOfMonth() : ($g === 'week' ? $period['from']->startOfWeek(CarbonInterface::SATURDAY) : $period['from']->startOfDay());
        $buckets = [];

        while ($cursor->lte($period['to'])) {
            $buckets[$this->bucketKey($cursor, $g)] = [
                'label' => $g === 'month' ? $cursor->format('M y') : $cursor->format('d M'),
                'orders' => 0, 'units' => 0, 'gross' => 0.0, 'discount' => 0.0, 'revenue' => 0.0,
                'cogs' => 0.0, 'expenses' => 0.0, 'profit' => 0.0,
            ];
            $cursor = match ($g) {
                'month' => $cursor->addMonth(),
                'week' => $cursor->addWeek(),
                default => $cursor->addDay(),
            };
        }

        return $buckets;
    }

    /**
     * Sales, cost, expenses and profit per day / week / month.
     *
     * @return list<array<string, mixed>>
     */
    public function timeline(array $period): array
    {
        $g = $period['granularity'];
        $buckets = $this->buckets($period);

        $this->salesQuery($period)->get(['id', 'created_at', 'subtotal', 'discount', 'total'])->each(function (Order $order) use (&$buckets, $g) {
            $key = $this->bucketKey($order->created_at, $g);

            if (isset($buckets[$key])) {
                $buckets[$key]['orders']++;
                $buckets[$key]['gross'] += $order->subtotal;
                $buckets[$key]['discount'] += $order->discount;
                $buckets[$key]['revenue'] += $order->total;
            }
        });

        $this->itemsQuery($period)->get(['orders.created_at', 'order_items.quantity', 'order_items.cost_price'])->each(function ($row) use (&$buckets, $g) {
            $key = $this->bucketKey(CarbonImmutable::parse($row->created_at), $g);

            if (isset($buckets[$key])) {
                $buckets[$key]['units'] += (int) $row->quantity;
                $buckets[$key]['cogs'] += (float) $row->cost_price * (int) $row->quantity;
            }
        });

        $this->expenseQuery($period)->get(['expense_date', 'amount'])->each(function (Expense $expense) use (&$buckets, $g) {
            $key = $this->bucketKey($expense->expense_date, $g);

            if (isset($buckets[$key])) {
                $buckets[$key]['expenses'] += $expense->amount;
            }
        });

        foreach ($buckets as &$bucket) {
            $bucket['profit'] = $bucket['revenue'] - $bucket['cogs'] - $bucket['expenses'];
        }

        return array_values($buckets);
    }

    /**
     * Every order of the period (cancelled and returned included) by status.
     *
     * @return Collection<string, object>
     */
    public function statusBreakdown(array $period): Collection
    {
        return Order::query()
            ->whereBetween('created_at', [$period['from'], $period['to']])
            ->selectRaw('status, COUNT(*) as orders, COALESCE(SUM(total),0) as value')
            ->groupBy('status')
            ->get()
            ->keyBy('status');
    }

    /** @return Collection<int, object> */
    public function paymentBreakdown(array $period): Collection
    {
        return $this->salesQuery($period)
            ->selectRaw('payment_method, payment_status, COUNT(*) as orders, COALESCE(SUM(total),0) as value')
            ->groupBy('payment_method', 'payment_status')
            ->get();
    }

    /** @return Collection<int, object> */
    public function topProducts(array $period, int $limit = 10): Collection
    {
        return $this->itemsQuery($period)
            ->selectRaw('order_items.product_id, MAX(order_items.name) as name, SUM(order_items.quantity) as units, SUM(order_items.line_total) as revenue, SUM(order_items.cost_price * order_items.quantity) as cogs, COUNT(DISTINCT orders.id) as orders')
            ->groupBy('order_items.product_id')
            ->orderByDesc('revenue')
            ->limit($limit)
            ->get()
            ->map(function ($row) {
                $row->profit = (float) $row->revenue - (float) $row->cogs;

                return $row;
            });
    }

    /** @return Collection<int, object> */
    public function categorySales(array $period): Collection
    {
        return $this->itemsQuery($period)
            ->leftJoin('products', 'products.id', '=', 'order_items.product_id')
            ->leftJoin('categories', 'categories.id', '=', 'products.category_id')
            ->selectRaw('categories.id as category_id, MAX(categories.name) as name, SUM(order_items.quantity) as units, SUM(order_items.line_total) as revenue')
            ->groupBy('categories.id')
            ->orderByDesc('revenue')
            ->get()
            ->map(function ($row) {
                $row->name ??= 'Uncategorized';

                return $row;
            });
    }

    /** @return Collection<int, object> */
    public function districts(array $period, int $limit = 8): Collection
    {
        return $this->salesQuery($period)
            ->selectRaw('district, COUNT(*) as orders, COALESCE(SUM(total),0) as revenue')
            ->groupBy('district')
            ->orderByDesc('revenue')
            ->limit($limit)
            ->get();
    }

    /**
     * @return array{labels: list<string>, orders: list<int>}
     */
    public function weekdays(array $period): array
    {
        $counts = array_fill_keys(array_keys(self::WEEKDAYS), 0);

        $this->salesQuery($period)->get(['id', 'created_at'])->each(function (Order $order) use (&$counts) {
            $counts[$order->created_at->dayOfWeek]++;
        });

        return ['labels' => array_values(self::WEEKDAYS), 'orders' => array_values($counts)];
    }

    /** @return Collection<int, object> */
    public function couponUsage(array $period): Collection
    {
        return $this->salesQuery($period)
            ->whereNotNull('coupon_code')
            ->selectRaw('coupon_code, COUNT(*) as orders, COALESCE(SUM(discount),0) as discount, COALESCE(SUM(total),0) as revenue')
            ->groupBy('coupon_code')
            ->orderByDesc('orders')
            ->get();
    }

    /**
     * Customers are identified by mobile number so guest checkouts count too.
     *
     * @return Collection<int, object>
     */
    public function topCustomers(array $period, int $limit = 15): Collection
    {
        return $this->salesQuery($period)
            ->selectRaw('phone, MAX(name) as name, MAX(email) as email, MAX(user_id) as user_id, COUNT(*) as orders, SUM(total) as spent, MAX(created_at) as last_order_at')
            ->groupBy('phone')
            ->orderByDesc('spent')
            ->limit($limit)
            ->get()
            ->map(function ($row) {
                $row->average_order = $row->orders ? (float) $row->spent / $row->orders : 0.0;
                $row->last_order_at = CarbonImmutable::parse($row->last_order_at);

                return $row;
            });
    }

    /**
     * @return array{new: int, returning: int, total: int}
     */
    public function customerMix(array $period): array
    {
        $phones = $this->salesQuery($period)->distinct()->pluck('phone');

        if ($phones->isEmpty()) {
            return ['new' => 0, 'returning' => 0, 'total' => 0];
        }

        $new = 0;

        foreach ($phones->chunk(500) as $chunk) {
            $new += Order::query()
                ->whereIn('phone', $chunk)
                ->whereNotIn('status', self::EXCLUDED_STATUSES)
                ->groupBy('phone')
                ->havingRaw('MIN(created_at) >= ?', [$period['from']])
                ->pluck('phone')
                ->count();
        }

        return ['new' => $new, 'returning' => $phones->count() - $new, 'total' => $phones->count()];
    }

    /**
     * Stock health right now (not tied to the date range).
     *
     * @return array<string, mixed>
     */
    public function stock(): array
    {
        $tracked = Product::query()->where('status', 'active')->where('track_inventory', true);

        $value = Product::query()
            ->selectRaw('COALESCE(SUM(stock * cost_price),0) as at_cost, COALESCE(SUM(stock * price),0) as at_retail, COUNT(*) as products, COALESCE(SUM(stock),0) as units')
            ->where('status', 'active')
            ->first();

        return [
            'at_cost' => (float) $value->at_cost,
            'at_retail' => (float) $value->at_retail,
            'products' => (int) $value->products,
            'units' => (int) $value->units,
            'out' => (clone $tracked)->where('stock', '<=', 0)->count(),
            'low' => (clone $tracked)->where('stock', '>', 0)->whereColumn('stock', '<=', 'low_stock')->count(),
            'low_list' => (clone $tracked)->where('stock', '>', 0)->whereColumn('stock', '<=', 'low_stock')->orderBy('stock')->limit(12)->get(['id', 'name', 'sku', 'stock', 'low_stock']),
            'out_list' => (clone $tracked)->where('stock', '<=', 0)->orderBy('name')->limit(12)->get(['id', 'name', 'sku', 'stock', 'low_stock']),
        ];
    }

    /**
     * Active products in stock that did not sell at all in the period.
     *
     * @return Collection<int, Product>
     */
    public function slowMovers(array $period, int $limit = 10): Collection
    {
        $sold = $this->itemsQuery($period)->whereNotNull('order_items.product_id')->distinct()->pluck('order_items.product_id');

        return Product::query()
            ->where('status', 'active')
            ->where('stock', '>', 0)
            ->whereNotIn('id', $sold)
            ->orderByDesc('stock')
            ->limit($limit)
            ->get(['id', 'name', 'sku', 'stock', 'price', 'cost_price']);
    }

    /**
     * @return array{total: float, count: int, by_category: Collection<int, object>, by_method: Collection<int, object>, biggest: Collection<int, Expense>}
     */
    public function expenses(array $period): array
    {
        $base = $this->expenseQuery($period);

        $byCategory = (clone $base)
            ->leftJoin('expense_categories', 'expense_categories.id', '=', 'expenses.expense_category_id')
            ->selectRaw('expense_categories.id as category_id, MAX(expense_categories.name) as name, MAX(expense_categories.color) as color, COUNT(*) as entries, SUM(expenses.amount) as total')
            ->groupBy('expense_categories.id')
            ->orderByDesc('total')
            ->get()
            ->map(function ($row) {
                $row->name ??= 'Uncategorized';
                $row->color ??= '#64748B';

                return $row;
            });

        return [
            'total' => (float) (clone $base)->sum('amount'),
            'count' => (clone $base)->count(),
            'by_category' => $byCategory,
            'by_method' => (clone $base)->selectRaw('payment_method, COUNT(*) as entries, SUM(amount) as total')->groupBy('payment_method')->orderByDesc('total')->get(),
            'biggest' => (clone $base)->with('category:id,name')->orderByDesc('amount')->limit(8)->get(),
        ];
    }

    /**
     * Order handling per staff member, based on the status changes they made.
     *
     * @return Collection<int, object>
     */
    public function staffPerformance(array $period): Collection
    {
        $stats = DB::table('order_events')
            ->join('orders', 'orders.id', '=', 'order_events.order_id')
            ->whereNotNull('order_events.admin_id')
            ->whereBetween('order_events.created_at', [$period['from'], $period['to']])
            ->selectRaw("order_events.admin_id, COUNT(*) as updates, COUNT(DISTINCT order_events.order_id) as orders, SUM(CASE WHEN order_events.status = 'delivered' THEN 1 ELSE 0 END) as delivered, SUM(CASE WHEN order_events.status IN ('cancelled','returned') THEN 1 ELSE 0 END) as cancelled, SUM(CASE WHEN order_events.status = 'delivered' THEN orders.total ELSE 0 END) as delivered_value, MAX(order_events.created_at) as last_action")
            ->groupBy('order_events.admin_id')
            ->get()
            ->keyBy('admin_id');

        return Admin::query()->with('role:id,name,color')->orderBy('name')->get()->map(function (Admin $admin) use ($stats) {
            $row = $stats->get($admin->id);

            return (object) [
                'admin' => $admin,
                'updates' => (int) ($row->updates ?? 0),
                'orders' => (int) ($row->orders ?? 0),
                'delivered' => (int) ($row->delivered ?? 0),
                'cancelled' => (int) ($row->cancelled ?? 0),
                'delivered_value' => (float) ($row->delivered_value ?? 0),
                'last_action' => ($row->last_action ?? null) ? CarbonImmutable::parse($row->last_action) : null,
            ];
        })->sortByDesc('delivered_value')->values();
    }

    /**
     * One staff member: totals, daily activity and the latest status changes.
     *
     * @return array<string, mixed>
     */
    public function staffProfile(Admin $admin, array $period): array
    {
        $events = DB::table('order_events')
            ->join('orders', 'orders.id', '=', 'order_events.order_id')
            ->where('order_events.admin_id', $admin->id)
            ->whereBetween('order_events.created_at', [$period['from'], $period['to']])
            ->orderByDesc('order_events.id')
            ->get(['order_events.id', 'order_events.order_id', 'order_events.status', 'order_events.created_at', 'orders.number', 'orders.name', 'orders.total']);

        $buckets = $this->buckets($period);

        foreach ($events as $event) {
            $key = $this->bucketKey(CarbonImmutable::parse($event->created_at), $period['granularity']);

            if (isset($buckets[$key])) {
                $buckets[$key]['orders']++;
            }
        }

        $byStatus = $events->groupBy('status')->map->count();
        $delivered = $events->where('status', 'delivered');

        return [
            'updates' => $events->count(),
            'orders' => $events->pluck('order_id')->unique()->count(),
            'delivered' => $delivered->count(),
            'delivered_value' => (float) $delivered->sum('total'),
            'by_status' => $byStatus,
            'timeline' => array_values($buckets),
            'recent' => $events->take(15)->map(function ($event) {
                $event->created_at = CarbonImmutable::parse($event->created_at);

                return $event;
            }),
            'activities' => $admin->activities()->whereBetween('created_at', [$period['from'], $period['to']])->limit(10)->get(),
        ];
    }

    /**
     * Lifetime sales profile of a single customer.
     *
     * @return array<string, mixed>
     */
    public function customerProfile(User $customer): array
    {
        $orders = $customer->customerOrders()->with('items')->latest('id')->get();
        $valid = $orders->reject(fn (Order $order) => in_array($order->status, self::EXCLUDED_STATUSES, true));
        $spent = (float) $valid->sum('total');

        $products = $valid->flatMap->items
            ->groupBy(fn ($item) => $item->product_id ?: 'n'.$item->name)
            ->map(fn (Collection $lines) => (object) [
                'product_id' => $lines->first()->product_id,
                'name' => $lines->first()->name,
                'units' => (int) $lines->sum('quantity'),
                'spent' => (float) $lines->sum('line_total'),
            ])
            ->sortByDesc('units')
            ->take(8)
            ->values();

        $months = collect(range(11, 0))->mapWithKeys(fn (int $ago) => [CarbonImmutable::today()->startOfMonth()->subMonths($ago)->format('Y-m') => 0.0])->all();

        foreach ($valid as $order) {
            $key = $order->created_at->format('Y-m');

            if (isset($months[$key])) {
                $months[$key] += $order->total;
            }
        }

        return [
            'orders' => $orders,
            'valid_count' => $valid->count(),
            'spent' => $spent,
            'average_order' => $valid->count() ? $spent / $valid->count() : 0.0,
            'units' => (int) $valid->flatMap->items->sum('quantity'),
            'discounts' => (float) $valid->sum('discount'),
            'first_order' => $valid->last()?->created_at,
            'last_order' => $valid->first()?->created_at,
            'lost_count' => $orders->count() - $valid->count(),
            'by_status' => $orders->groupBy('status')->map->count(),
            'products' => $products,
            'months' => ['labels' => array_map(fn (string $key) => CarbonImmutable::createFromFormat('Y-m', $key)->format('M'), array_keys($months)), 'values' => array_values($months)],
            'cogs' => (float) $valid->flatMap->items->sum(fn ($item) => (float) $item->cost_price * $item->quantity),
        ];
    }

    /**
     * Header and rows of a CSV export.
     *
     * @return array{0: string, 1: list<string>, 2: list<list<scalar|null>>}
     */
    public function exportRows(string $report, array $period): array
    {
        $money = fn (float|int|null $value) => number_format((float) $value, 2, '.', '');

        switch ($report) {
            case 'sales':
                $rows = array_map(fn (array $row) => [$row['label'], $row['orders'], $row['units'], $money($row['gross']), $money($row['discount']), $money($row['revenue'])], $this->timeline($period));

                return ['sales', ['Period', 'Orders', 'Units', 'Gross sales', 'Discounts', 'Net revenue'], $rows];

            case 'profit':
                $rows = array_map(fn (array $row) => [$row['label'], $money($row['revenue']), $money($row['cogs']), $money($row['revenue'] - $row['cogs']), $money($row['expenses']), $money($row['profit'])], $this->timeline($period));

                return ['profit-loss', ['Period', 'Net revenue', 'Cost of goods', 'Gross profit', 'Expenses', 'Net profit'], $rows];

            case 'products':
                $rows = $this->topProducts($period, 500)->map(fn ($row) => [$row->name, (int) $row->units, (int) $row->orders, $money($row->revenue), $money($row->cogs), $money($row->profit)])->all();

                return ['products', ['Product', 'Units sold', 'Orders', 'Revenue', 'Cost of goods', 'Profit'], $rows];

            case 'customers':
                $rows = $this->topCustomers($period, 1000)->map(fn ($row) => [$row->name, $row->phone, $row->email, (int) $row->orders, $money($row->spent), $money($row->average_order), $row->last_order_at->format('Y-m-d')])->all();

                return ['customers', ['Customer', 'Phone', 'Email', 'Orders', 'Total spent', 'Average order', 'Last order'], $rows];

            case 'staff':
                $rows = $this->staffPerformance($period)->map(fn ($row) => [$row->admin->name, $row->admin->role?->name, $row->updates, $row->orders, $row->delivered, $row->cancelled, $money($row->delivered_value)])->all();

                return ['staff-performance', ['Staff', 'Role', 'Status updates', 'Orders handled', 'Delivered', 'Cancelled / returned', 'Delivered value'], $rows];
        }

        abort(404);
    }
}
