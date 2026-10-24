@extends('frontend.account.layout')

@section('frontend_title', 'My orders | '.$setting('store_name'))
@section('account_title', 'My orders')
@section('account_sub', 'Every order placed with your mobile number.')

@section('account_body')
    @php
        $tk = fn (float $n) => '৳'.number_format($n, floor($n) == $n ? 0 : 2);
        $filters = ['' => 'All'] + \App\Models\Order::STATUSES;
    @endphp

    <div class="flex gap-2 overflow-x-auto pb-1" role="navigation" aria-label="Filter orders">
        @foreach ($filters as $key => $label)
            <a href="{{ route('account.orders', $key === '' ? [] : ['status' => $key]) }}"
                class="shrink-0 px-4 py-2 rounded-xl border border-line font-bold text-sm {{ $status === $key ? 'bg-primary text-white' : 'bg-white hover:bg-primary-soft' }}">{{ $label }}</a>
        @endforeach
    </div>

    <div class="grid gap-4 mt-5">
        @forelse ($orders as $order)
            <article class="bg-white border border-line rounded-3xl p-5 lg:p-6">
                <div class="flex flex-wrap items-start justify-between gap-3">
                    <div>
                        <a href="{{ route('account.orders.show', $order->number) }}" class="font-display font-extrabold text-lg hover:text-primary">{{ $order->number }}</a>
                        <p class="text-sm text-ink/60">{{ $order->created_at->timezone(\App\Models\Setting::tz())->format('d M Y, h:i A') }}</p>
                    </div>
                    <span class="acct-badge" data-status="{{ $order->status }}">{{ $order->status_label }}</span>
                </div>

                <ul class="flex flex-wrap gap-2 mt-4">
                    @foreach ($order->items->take(5) as $item)
                        <li><img src="{{ $item->image }}" alt="{{ $item->name }}" title="{{ $item->name }}" width="300" height="300" class="w-14 h-14 rounded-lg object-cover border border-line" loading="lazy"></li>
                    @endforeach
                    @if ($order->items->count() > 5)
                        <li class="w-14 h-14 rounded-lg border border-line grid place-items-center text-sm font-bold">+{{ $order->items->count() - 5 }}</li>
                    @endif
                </ul>

                <div class="flex flex-wrap items-center justify-between gap-3 mt-4 pt-4 border-t-2 border-dashed border-line">
                    <p class="text-sm text-ink/70">{{ $order->items->sum('quantity') }} item(s) · <b class="text-ink">{{ $tk($order->total) }}</b></p>
                    <div class="flex flex-wrap gap-2">
                        <a href="{{ route('account.orders.show', $order->number) }}" class="px-4 py-2 rounded-xl border border-line font-bold text-sm hover:bg-primary-soft">View details</a>
                        <form method="POST" action="{{ route('account.orders.reorder', $order->number) }}">
                            @csrf
                            <button type="submit" class="px-4 py-2 rounded-xl bg-primary text-white font-bold text-sm hover:bg-primary-dark">Order again</button>
                        </form>
                    </div>
                </div>
            </article>
        @empty
            <div class="bg-white border border-line rounded-3xl p-6 lg:p-8 text-center">
                <p class="font-display text-2xl">No orders found</p>
                <p class="mt-2 text-ink/70">When you place an order it will show up here.</p>
                <a href="{{ route('shop') }}" class="inline-flex items-center justify-center bg-primary hover:bg-primary-dark text-white font-extrabold rounded-xl px-6 py-3.5 transition-colors mt-5">Start shopping</a>
            </div>
        @endforelse
    </div>

    @if ($orders->hasPages())
        <div class="mt-6 flex items-center justify-between gap-3 text-sm font-bold">
            @if ($orders->onFirstPage())
                <span class="px-4 py-2 rounded-xl border border-line opacity-50">Previous</span>
            @else
                <a href="{{ $orders->previousPageUrl() }}" class="px-4 py-2 rounded-xl border border-line bg-white hover:bg-primary-soft">Previous</a>
            @endif
            <span>Page {{ $orders->currentPage() }} of {{ $orders->lastPage() }}</span>
            @if ($orders->hasMorePages())
                <a href="{{ $orders->nextPageUrl() }}" class="px-4 py-2 rounded-xl border border-line bg-white hover:bg-primary-soft">Next</a>
            @else
                <span class="px-4 py-2 rounded-xl border border-line opacity-50">Next</span>
            @endif
        </div>
    @endif
@endsection
