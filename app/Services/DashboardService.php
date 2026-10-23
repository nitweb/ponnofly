<?php

namespace App\Services;

use App\Models\Order;
use App\Models\Product;
use App\Models\Setting;
use App\Models\User;
use Carbon\CarbonImmutable;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

class DashboardService
{
    /** Donut / legend colours per order status. */
    public const STATUS_COLORS = [
        'delivered' => '#0A8F6B',
        'shipped' => '#7C3AED',
        'processing' => '#2563EB',
        'pending' => '#F5A800',
        'cancelled' => '#DC2626',
        'out_for_delivery' => '#0EA5E9',
        'returned' => '#64748B',
    ];

    public function __construct(private ReportService $reports) {}

    /** @return array<string, mixed> */
    public function data(): array
    {
        $today = CarbonImmutable::today();

        return [
            'stats' => $this->stats($today),
            'chart' => $this->chart($today),
            'statuses' => $this->statuses(),
            'recentOrders' => Order::query()->latest()->latest('id')->limit(6)->get(),
            'topProducts' => $this->topProducts(),
            'lowStock' => $this->lowStock(),
            'couriers' => $this->couriers($today),
        ];
    }

    /** @return array<string, array<string, mixed>> */
    private function stats(CarbonImmutable $today): array
    {
        $current = $this->window($today->subDays(29), $today);
        $previous = $this->window($today->subDays(59), $today->subDays(30));

        $now = $this->reports->summary($current);
        $before = $this->reports->summary($previous);

        $since = $today->subDays(7)->startOfDay();
        $orders = Order::query()->where('created_at', '>=', $since)->get(['created_at', 'total', 'status']);
        $users = User::query()->where('created_at', '>=', $since)->get(['created_at']);
        $products = Product::query()->where('created_at', '>=', $since)->get(['created_at']);

        return [
            'revenue' => [
                'value' => (float) Order::query()->whereNotIn('status', ReportService::EXCLUDED_STATUSES)->sum('total'),
                'growth' => $this->reports->growth($now['net_revenue'], $before['net_revenue']),
                'spark' => $this->spark($orders->reject(fn ($o) => in_array($o->status, ReportService::EXCLUDED_STATUSES, true)), fn ($o) => (float) $o->total),
            ],
            'orders' => [
                'value' => Order::query()->count(),
                'growth' => $this->reports->growth($this->countBetween(Order::query(), $current), $this->countBetween(Order::query(), $previous)),
                'spark' => $this->spark($orders, fn () => 1),
            ],
            'customers' => [
                'value' => User::query()->count(),
                'growth' => $this->reports->growth($this->countBetween(User::query(), $current), $this->countBetween(User::query(), $previous)),
                'spark' => $this->spark($users, fn () => 1),
            ],
            'products' => [
                'value' => Product::query()->count(),
                'growth' => $this->reports->growth($this->countBetween(Product::query(), $current), $this->countBetween(Product::query(), $previous)),
                'spark' => $this->spark($products, fn () => 1),
            ],
        ];
    }

    /** @return array{from: CarbonImmutable, to: CarbonImmutable, days: int, granularity: string} */
    private function window(CarbonImmutable $from, CarbonImmutable $to, string $granularity = 'day'): array
    {
        return [
            'from' => $from->startOfDay(),
            'to' => $to->endOfDay(),
            'days' => (int) $from->diffInDays($to) + 1,
            'granularity' => $granularity,
        ];
    }

    /** @param  array{from: CarbonImmutable, to: CarbonImmutable}  $period */
    private function countBetween($query, array $period): int
    {
        return (int) $query->whereBetween('created_at', [$period['from'], $period['to']])->count();
    }

    /**
     * Last eight days, one value per day, for the little trend lines.
     *
     * @return list<float|int>
     */
    private function spark(Collection $rows, callable $value): array
    {
        $days = [];

        for ($i = 7; $i >= 0; $i--) {
            $days[CarbonImmutable::today()->subDays($i)->toDateString()] = 0;
        }

        foreach ($rows as $row) {
            $key = $row->created_at->toDateString();

            if (array_key_exists($key, $days)) {
                $days[$key] += $value($row);
            }
        }

        return array_values($days);
    }

    /** @return array<string, array{labels: list<string>, series: list<list<float>>}> */
    private function chart(CarbonImmutable $today): array
    {
        $ranges = [
            '7d' => [$today->subDays(6), 'day', 'D'],
            '30d' => [$today->subDays(29), 'day', 'j M'],
            '12m' => [$today->subMonths(11)->startOfMonth(), 'month', 'M'],
        ];

        $chart = [];

        foreach ($ranges as $key => [$from, $granularity, $format]) {
            $rows = $this->reports->timeline($this->window($from, $today, $granularity));
            $labels = [];
            $revenue = [];
            $profit = [];

            foreach ($rows as $i => $row) {
                $labels[] = ($granularity === 'month' ? $from->addMonths($i) : $from->addDays($i))->format($format);
                $revenue[] = round((float) $row['revenue']);
                $profit[] = max(0, round((float) $row['revenue'] - (float) $row['cogs']));
            }

            $chart[$key] = ['labels' => $labels, 'series' => [$revenue, $profit]];
        }

        return $chart;
    }

    /** @return list<array{key: string, label: string, count: int, color: string}> */
    private function statuses(): array
    {
        $counts = Order::query()->selectRaw('status, COUNT(*) as total')->groupBy('status')->pluck('total', 'status');
        $primary = ['delivered', 'shipped', 'processing', 'pending', 'cancelled'];
        $extra = array_filter(['out_for_delivery', 'returned'], fn (string $status) => (int) ($counts[$status] ?? 0) > 0);
        $rows = [];

        foreach ([...$primary, ...$extra] as $status) {
            $rows[] = [
                'key' => $status,
                'label' => Order::STATUSES[$status],
                'count' => (int) ($counts[$status] ?? 0),
                'color' => self::STATUS_COLORS[$status],
            ];
        }

        return $rows;
    }

    /** @return Collection<int, object> */
    private function topProducts(): Collection
    {
        $rows = DB::table('order_items')
            ->join('orders', 'orders.id', '=', 'order_items.order_id')
            ->whereNotIn('orders.status', ReportService::EXCLUDED_STATUSES)
            ->whereNotNull('order_items.product_id')
            ->selectRaw('order_items.product_id, MAX(order_items.name) as name, SUM(order_items.quantity) as units, SUM(order_items.line_total) as revenue')
            ->groupBy('order_items.product_id')
            ->orderByDesc('units')
            ->limit(5)
            ->get();

        $products = Product::query()->with('images')->whereIn('id', $rows->pluck('product_id'))->get()->keyBy('id');

        return $rows->map(function ($row) use ($products) {
            $row->image = $products->get($row->product_id)?->cover_url ?? asset('assets/frontend/images/placeholder.svg');

            return $row;
        });
    }

    /** @return Collection<int, Product> */
    private function lowStock(): Collection
    {
        return Product::query()
            ->with(['images', 'variants'])
            ->where('status', 'active')
            ->where('track_inventory', true)
            ->where(fn ($query) => $query->whereHas('variants')->orWhereColumn('stock', '<=', 'low_stock'))
            ->get()
            ->filter(fn (Product $product) => $product->stock_total <= (int) $product->low_stock)
            ->sortBy('stock_total')
            ->take(4)
            ->values();
    }

    /** @return list<array{label: string, count: int, percent: int}> */
    private function couriers(CarbonImmutable $today): array
    {
        $counts = Order::query()
            ->whereNotNull('courier')
            ->where('created_at', '>=', $today->subDays(29)->startOfDay())
            ->selectRaw('courier, COUNT(*) as total')
            ->groupBy('courier')
            ->orderByDesc('total')
            ->pluck('total', 'courier');

        $sum = (int) $counts->sum();

        return $counts->map(fn ($total, $courier) => [
            'label' => Setting::COURIERS[$courier] ?? ucfirst((string) $courier),
            'count' => (int) $total,
            'percent' => $sum ? (int) round($total / $sum * 100) : 0,
        ])->values()->all();
    }
}
