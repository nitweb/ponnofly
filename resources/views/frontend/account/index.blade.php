@extends('frontend.account.layout')

@section('frontend_title', 'My account | '.$setting('store_name'))
@section('account_title', 'My account')
@section('account_sub', 'Hello, '.\Illuminate\Support\Str::before($user->name, ' ').'. Here is what is going on with your orders.')

@section('account_body')
    @php
        $tk = fn (float $n) => '৳'.number_format($n, floor($n) == $n ? 0 : 2);
        $card = 'bg-white border border-line rounded-3xl p-6';
    @endphp

    <div class="grid grid-cols-2 lg:grid-cols-4 gap-4">
        <a href="{{ route('account.orders') }}" class="{{ $card }} hover:border-primary transition-colors block">
            <p class="text-sm text-ink/65">Total orders</p>
            <p class="font-display font-extrabold text-3xl mt-1">{{ $orderCount }}</p>
        </a>
        <a href="{{ route('account.orders') }}" class="{{ $card }} hover:border-primary transition-colors block">
            <p class="text-sm text-ink/65">On the way</p>
            <p class="font-display font-extrabold text-3xl mt-1">{{ $activeCount }}</p>
        </a>
        <div class="{{ $card }}">
            <p class="text-sm text-ink/65">Total spent</p>
            <p class="font-display font-extrabold text-3xl mt-1">{{ $tk($spent) }}</p>
        </div>
        <a href="{{ route('account.wishlist') }}" class="{{ $card }} hover:border-primary transition-colors block">
            <p class="text-sm text-ink/65">Wishlist</p>
            <p class="font-display font-extrabold text-3xl mt-1">{{ $wishlistCount }}</p>
        </a>
    </div>

    <section class="{{ $card }} mt-6 lg:p-8" aria-labelledby="h-recent">
        <div class="flex items-center justify-between gap-3">
            <h2 id="h-recent" class="font-display font-extrabold text-2xl">Recent orders</h2>
            @if ($orderCount)
                <a href="{{ route('account.orders') }}" class="text-sm font-bold underline underline-offset-4">View all</a>
            @endif
        </div>

        @forelse ($recentOrders as $order)
            <a href="{{ route('account.orders.show', $order->number) }}" class="flex flex-wrap items-center justify-between gap-3 py-4 {{ $loop->first ? 'mt-3' : 'border-t-2 border-dashed border-line' }}">
                <span>
                    <b class="font-display font-extrabold">{{ $order->number }}</b>
                    <span class="block text-sm text-ink/60">{{ $order->created_at->timezone(\App\Models\Setting::tz())->format('d M Y') }} · {{ $order->items->sum('quantity') }} item(s)</span>
                </span>
                <span class="flex items-center gap-3">
                    <b>{{ $tk($order->total) }}</b>
                    <span class="acct-badge" data-status="{{ $order->status }}">{{ $order->status_label }}</span>
                </span>
            </a>
        @empty
            <p class="mt-3 text-ink/70">You have not placed an order with this mobile number yet.</p>
            <a href="{{ route('shop') }}" class="inline-flex items-center justify-center bg-primary hover:bg-primary-dark text-white font-extrabold rounded-xl px-6 py-3 transition-colors mt-4">Start shopping</a>
        @endforelse
    </section>

    <div class="grid md:grid-cols-2 gap-4 mt-6">
        <section class="{{ $card }}" aria-labelledby="h-me">
            <h2 id="h-me" class="font-display font-extrabold text-xl">Your details</h2>
            <p class="mt-3 font-bold">{{ $user->name }}</p>
            <p class="text-sm text-ink/70">{{ $user->phone ?: 'No mobile number yet' }}</p>
            <p class="text-sm text-ink/70">{{ $user->email ?: 'No email yet' }}</p>
            <a href="{{ route('account.profile') }}" class="inline-block mt-4 text-sm font-bold underline underline-offset-4">Edit details</a>
        </section>
        <section class="{{ $card }}" aria-labelledby="h-addr">
            <h2 id="h-addr" class="font-display font-extrabold text-xl">Default address</h2>
            @if ($address)
                <p class="mt-3 font-bold">{{ $address->name }} <span class="acct-badge" data-status="info">{{ $address->label }}</span></p>
                <p class="text-sm text-ink/70">{{ $address->full_address }}</p>
                <p class="text-sm text-ink/70">{{ $address->phone }}</p>
            @else
                <p class="mt-3 text-sm text-ink/70">We save your address after your first order.</p>
            @endif
            <a href="{{ route('account.addresses') }}" class="inline-block mt-4 text-sm font-bold underline underline-offset-4">Manage addresses</a>
        </section>
    </div>
@endsection
