@extends('admin.dashboard.dashboard')
@section('admin_title', 'Customer report · Nebula Mart Admin')
@section('admin_contents')

    @include('admin.reports._filters', ['title' => 'Customer report', 'subtitle' => 'Who buys and how often', 'exportKey' => 'customers'])

    <div class="grid sm:grid-cols-2 xl:grid-cols-4 gap-5">
        @include('admin.reports._stat', ['tone' => 'is-5', 'icon' => 'users', 'label' => 'Active customers', 'value' => number_format($mix['total'])])
        @include('admin.reports._stat', ['tone' => 'is-11', 'icon' => 'user', 'label' => 'New customers', 'value' => number_format($mix['new']), 'hint' => 'First order in this period'])
        @include('admin.reports._stat', ['tone' => 'is-5', 'icon' => 'refresh', 'label' => 'Returning customers', 'value' => number_format($mix['returning']), 'hint' => $mix['total'] ? round($mix['returning'] / $mix['total'] * 100).'% of active' : '—'])
        @include('admin.reports._stat', ['tone' => 'is-11', 'icon' => 'cart', 'label' => 'Average order', 'value' => '৳'.number_format($summary['average_order'])])
    </div>

    <section class="card">
        <div class="card-h"><h3>Top customers</h3></div>
        <div class="overflow-x-auto">
            <table class="tbl">
                <thead><tr><th>Customer</th><th>Phone</th><th>Orders</th><th>Average order</th><th>Last order</th><th class="text-right">Total spent</th></tr></thead>
                <tbody>
                    @forelse ($customers as $row)
                        <tr>
                            <td>
                                @if ($row->user_id)
                                    <a href="{{ route('admin.customers.show', $row->user_id) }}" class="font-bold text-primary hover:underline">{{ $row->name }}</a>
                                @else
                                    <b>{{ $row->name }}</b> <span class="badge b-muted">Guest</span>
                                @endif
                                <p class="text-xs text-ink/50">{{ $row->email }}</p>
                            </td>
                            <td>{{ $row->phone }}</td>
                            <td>{{ $row->orders }}</td>
                            <td>৳{{ number_format($row->average_order) }}</td>
                            <td class="text-ink/60">{{ $row->last_order_at->timezone(\App\Models\Setting::tz())->format('d M Y') }}</td>
                            <td class="text-right font-bold">৳{{ number_format($row->spent) }}</td>
                        </tr>
                    @empty
                        <tr><td colspan="6" class="text-ink/55">No customers ordered in this period.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </section>

    <div class="grid xl:grid-cols-2 gap-6">
        <section class="card">
            <div class="card-h"><h3>Top districts</h3></div>
            <div class="overflow-x-auto">
                <table class="tbl">
                    <thead><tr><th>District</th><th>Orders</th><th class="text-right">Revenue</th></tr></thead>
                    <tbody>
                        @forelse ($districts as $row)
                            <tr><td><b>{{ $row->district }}</b></td><td>{{ $row->orders }}</td><td class="text-right font-bold">৳{{ number_format($row->revenue) }}</td></tr>
                        @empty
                            <tr><td colspan="3" class="text-ink/55">No orders.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </section>

        <section class="card">
            <div class="card-h"><h3>Coupon usage</h3></div>
            <div class="overflow-x-auto">
                <table class="tbl">
                    <thead><tr><th>Code</th><th>Orders</th><th class="text-right">Discount given</th></tr></thead>
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

@endsection
