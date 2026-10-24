@extends('frontend.account.layout')

@section('frontend_title', 'Order '.$order->number.' | '.$setting('store_name'))
@section('account_title', 'Order '.$order->number)
@section('account_sub', 'Placed on '.$order->created_at->timezone(\App\Models\Setting::tz())->format('d M Y, h:i A'))

@section('account_body')
    @php
        $tk = fn (float $n) => '৳'.number_format($n, floor($n) == $n ? 0 : 2);
        $card = 'bg-white border border-line rounded-3xl p-6 lg:p-8';
    @endphp

    <div class="grid gap-6">
        <section class="{{ $card }}" aria-labelledby="h-status">
            <div class="flex flex-wrap items-center justify-between gap-3">
                <h2 id="h-status" class="font-display font-extrabold text-2xl">Status</h2>
                <span class="acct-badge" data-status="{{ $order->status }}">{{ $order->status_label }}</span>
            </div>
            <ol class="mt-5 grid gap-4">
                @foreach ($order->events->reverse() as $event)
                    <li class="flex gap-3">
                        <span class="mt-1.5 w-3 h-3 rounded-full shrink-0 {{ $loop->first ? 'bg-primary' : 'bg-ink/25' }}"></span>
                        <span>
                            <b>{{ \App\Models\Order::STATUSES[$event->status] ?? ucfirst($event->status) }}</b>
                            <span class="block text-sm text-ink/60">{{ \Illuminate\Support\Carbon::parse($event->created_at)->timezone(\App\Models\Setting::tz())->format('d M Y, h:i A') }}@if ($event->note) · {{ $event->note }}@endif</span>
                        </span>
                    </li>
                @endforeach
            </ol>
            @if ($order->courier_label || $order->tracking_id)
                <p class="mt-5 rounded-xl bg-accent/25 border border-line px-4 py-3 text-sm">Courier: <b>{{ $order->courier_label ?: 'Not assigned yet' }}</b>@if ($order->tracking_id) · Tracking ID: <b>{{ $order->tracking_id }}</b>@endif</p>
            @endif
            <div class="flex flex-wrap gap-2 mt-5">
                <a href="{{ route('track.show') }}" class="px-4 py-2 rounded-xl border border-line font-bold text-sm hover:bg-primary-soft">Track this order</a>
                <form method="POST" action="{{ route('account.orders.reorder', $order->number) }}">
                    @csrf
                    <button type="submit" class="px-4 py-2 rounded-xl bg-primary text-white font-bold text-sm hover:bg-primary-dark">Order again</button>
                </form>
            </div>
        </section>

        <section class="{{ $card }}" aria-labelledby="h-items">
            <h2 id="h-items" class="font-display font-extrabold text-2xl">Items</h2>
            <ul class="grid gap-4 mt-5">
                @foreach ($order->items as $item)
                    <li class="flex items-center gap-4">
                        <img src="{{ $item->image }}" alt="{{ $item->name }}" width="300" height="300" class="w-16 h-16 rounded-lg object-cover border border-line" loading="lazy">
                        <div class="flex-1 min-w-0">
                            @if ($item->product)
                                <a href="{{ $item->product->url }}" class="font-bold leading-snug hover:text-primary">{{ $item->name }}</a>
                            @else
                                <p class="font-bold leading-snug">{{ $item->name }}</p>
                            @endif
                            <p class="text-sm text-ink/60">{{ $item->variant_label ? $item->variant_label.' · ' : '' }}Qty {{ $item->quantity }} × {{ $tk($item->unit_price) }}</p>
                        </div>
                        <p class="font-bold">{{ $tk($item->line_total) }}</p>
                    </li>
                @endforeach
            </ul>
            <dl class="mt-6 grid gap-2.5 border-t-2 border-dashed border-line pt-5 max-w-sm ml-auto">
                <div class="flex justify-between"><dt>Subtotal</dt><dd class="font-bold">{{ $tk($order->subtotal) }}</dd></div>
                @if ($order->discount > 0)
                    <div class="flex justify-between"><dt>Coupon {{ $order->coupon_code }}</dt><dd class="font-bold text-success">-{{ $tk($order->discount) }}</dd></div>
                @endif
                <div class="flex justify-between"><dt>Delivery <span class="text-xs text-ink/60">({{ $order->zone_label }})</span></dt><dd class="font-bold">{{ $order->delivery_fee > 0 ? $tk($order->delivery_fee) : 'Free' }}</dd></div>
                <div class="flex justify-between items-baseline border-t-2 border-line pt-4 mt-1"><dt class="font-display font-extrabold text-xl">Total</dt><dd class="font-display font-extrabold text-2xl text-primary">{{ $tk($order->total) }}</dd></div>
            </dl>
        </section>

        <div class="grid md:grid-cols-2 gap-4">
            <section class="{{ $card }} acct-info" aria-labelledby="h-ship">
                <div class="acct-info__head">
                    <span class="acct-info__ic"><span class="ic ic-pin" aria-hidden="true"></span></span>
                    <h2 id="h-ship" class="font-display font-extrabold text-xl">Delivery address</h2>
                </div>
                <dl class="acct-info__list">
                    <div><dt>Name</dt><dd>{{ $order->name }}</dd></div>
                    <div><dt>Mobile</dt><dd>{{ $order->phone }}</dd></div>
                    <div><dt>Address</dt><dd>{{ $order->full_address }}</dd></div>
                    @if ($order->note)
                        <div><dt>Note</dt><dd>{{ $order->note }}</dd></div>
                    @endif
                </dl>
            </section>
            <section class="{{ $card }} acct-info" aria-labelledby="h-pay">
                <div class="acct-info__head">
                    <span class="acct-info__ic"><span class="ic ic-wallet" aria-hidden="true"></span></span>
                    <h2 id="h-pay" class="font-display font-extrabold text-xl">Payment</h2>
                </div>
                <dl class="acct-info__list">
                    <div><dt>Method</dt><dd>{{ $order->payment_label }}</dd></div>
                    <div><dt>Status</dt><dd><span class="acct-badge" data-status="{{ $order->payment_status === 'paid' ? 'delivered' : 'pending' }}">{{ ucfirst($order->payment_status) }}</span></dd></div>
                    <div><dt>Total</dt><dd>{{ $tk($order->total) }}</dd></div>
                </dl>
            </section>
        </div>
    </div>
@endsection
