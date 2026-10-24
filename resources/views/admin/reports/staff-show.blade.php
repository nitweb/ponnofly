@extends('admin.dashboard.dashboard')
@section('admin_title', $member->name.' · Staff performance · Nebula Mart Admin')
@section('admin_contents')

    @php
        $activityChart = ['labels' => array_column($profile['timeline'], 'label'), 'values' => array_column($profile['timeline'], 'orders'), 'color' => '#0B3A8C'];
        $subtitle = $member->displayTitle().' · sales performance';
        $badges = ['pending' => 'b-warn', 'processing' => 'b-info', 'shipped' => 'b-purple', 'out_for_delivery' => 'b-info', 'delivered' => 'b-success', 'cancelled' => 'b-danger', 'returned' => 'b-muted'];
    @endphp

    @include('admin.reports._filters', ['title' => $member->name, 'subtitle' => $subtitle])

    <div class="grid sm:grid-cols-2 xl:grid-cols-4 gap-5">
        @include('admin.reports._stat', ['tone' => 'is-5', 'icon' => 'cart', 'label' => 'Orders handled', 'value' => number_format($profile['orders'])])
        @include('admin.reports._stat', ['tone' => 'is-11', 'icon' => 'refresh', 'label' => 'Status updates', 'value' => number_format($profile['updates'])])
        @include('admin.reports._stat', ['tone' => 'is-5', 'icon' => 'truck', 'label' => 'Delivered', 'value' => number_format($profile['delivered'])])
        @include('admin.reports._stat', ['tone' => 'is-11', 'icon' => 'dollar', 'label' => 'Delivered value', 'value' => '৳'.number_format($profile['delivered_value'])])
    </div>

    <div class="grid xl:grid-cols-3 gap-6 items-start">
        <section class="card xl:col-span-2">
            <div class="card-h"><h3>Daily activity</h3></div>
            <div class="card-b">
                <div data-bars='@json($activityChart)'></div>
            </div>
        </section>

        <section class="card">
            <div class="card-h"><h3>Updates by status</h3></div>
            <div class="card-b space-y-3">
                @forelse ($profile['by_status'] as $status => $count)
                    <p class="flex justify-between"><span class="badge {{ $badges[$status] ?? 'b-muted' }}">{{ \App\Models\Order::STATUSES[$status] ?? $status }}</span><b>{{ $count }}</b></p>
                @empty
                    <p class="text-ink/55">No activity in this period.</p>
                @endforelse
            </div>
        </section>
    </div>

    <div class="grid xl:grid-cols-2 gap-6 items-start">
        <section class="card">
            <div class="card-h"><h3>Recent order updates</h3></div>
            <div class="overflow-x-auto">
                <table class="tbl">
                    <thead><tr><th>Order</th><th>Status</th><th>When</th><th class="text-right">Total</th></tr></thead>
                    <tbody>
                        @forelse ($profile['recent'] as $event)
                            <tr>
                                <td><a href="{{ route('admin.orders.show', $event->order_id) }}" class="font-bold text-primary hover:underline">#{{ $event->number }}</a><p class="text-xs text-ink/50">{{ $event->name }}</p></td>
                                <td><span class="badge {{ $badges[$event->status] ?? 'b-muted' }}">{{ \App\Models\Order::STATUSES[$event->status] ?? $event->status }}</span></td>
                                <td class="text-ink/60">{{ $event->created_at->timezone(\App\Models\Setting::tz())->format('d M, h:i A') }}</td>
                                <td class="text-right font-bold">৳{{ number_format($event->total) }}</td>
                            </tr>
                        @empty
                            <tr><td colspan="4" class="text-ink/55">No updates in this period.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </section>

        <section class="card">
            <div class="card-h"><h3>Account activity</h3></div>
            <div class="card-b space-y-3">
                @forelse ($profile['activities'] as $activity)
                    <p class="flex justify-between gap-4"><span>{{ $activity->description }}</span><span class="text-xs text-ink/50" style="white-space:nowrap">{{ $activity->created_at->timezone(\App\Models\Setting::tz())->format('d M, h:i A') }}</span></p>
                @empty
                    <p class="text-ink/55">No account activity in this period.</p>
                @endforelse
            </div>
        </section>
    </div>

@endsection
