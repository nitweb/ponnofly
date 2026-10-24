@extends('frontend.dashboard.dashboard')

@section('frontend_title', 'Order placed | '.$setting('store_name'))
@section('frontend_description', 'Your order has been placed.')
@section('page', 'success')

@section('frontend_contents')
    @php
        $tk = fn (float $n) => '৳'.number_format($n, floor($n) == $n ? 0 : 2);
        preg_match_all('/\d+/', (string) $order->delivery_time, $days);
        $maxDays = $days[0] ? max(array_map('intval', $days[0])) : null;
        $steps = [
            ['Order received', 'We have your order and are confirming it.'],
            ['Packed', 'Your items are checked and packed.'],
            ['On the way', 'Our courier is bringing it to you.'],
            ['Delivered', 'Enjoy! Returns are open for 7 days.'],
        ];
        $reached = match ($order->status) {
            'pending' => 1,
            'processing' => 2,
            'shipped' => 3,
            'out_for_delivery' => 3,
            'delivered' => 4,
            default => 1,
        };
        $closed = $order->isFinal();
    @endphp

    <section class="bg-accent border-b-4 border-line relative overflow-hidden"><span
        class="absolute -left-10 -top-10 w-44 h-44 rounded-full bg-accent"></span><span
        class="absolute right-10 -bottom-16 w-56 h-56 rounded-full bg-primary"></span><span
        class="absolute right-1/3 top-8 w-8 h-8 rounded-full bg-primary"></span><span
        class="absolute left-1/4 bottom-6 w-5 h-5 rounded-full bg-white"></span>
        <div class="max-w-7xl mx-auto px-4 lg:px-6 py-12 relative text-center">
            <span class="badge mx-auto w-24 h-24 rounded-full bg-white border-4 border-line grid place-items-center shadow-sm"><svg
                viewBox="0 0 24 24" class="w-12 h-12" fill="none" stroke="rgb(var(--color-ink))" stroke-width="3"
                stroke-linecap="round" stroke-linejoin="round">
                <path class="tick" d="m5 12.5 4.5 4.5L19 7" />
            </svg></span>
            <h1 class="font-display font-extrabold text-4xl lg:text-5xl mt-6">Thank you, <span id="who">{{ \Illuminate\Support\Str::before($order->name, ' ') }}</span>! Your order is placed.</h1>
            <p class="mt-3 max-w-xl mx-auto text-ink/80" id="sent">We sent a confirmation to {{ $order->email }} and will text {{ $order->phone }} when your order ships.</p>
            <div class="inline-flex items-center gap-3 mt-6 bg-white border border-line rounded-2xl pl-5 pr-2 py-2 shadow-sm">
                <span class="text-sm">Order number</span><b id="no" class="font-display font-extrabold text-xl">{{ $order->number }}</b><button
                    type="button" id="copy" class="no-print bg-accent border border-line rounded-xl px-3 py-1.5 text-sm font-bold">Copy</button>
            </div>
        </div>
    </section>

    @if (session('new_account') && ! auth()->check())
        <div class="max-w-7xl mx-auto px-4 lg:px-6 pt-8">
            <div class="bg-white border border-line rounded-3xl p-6 lg:p-8 flex flex-wrap items-center gap-4 justify-between">
                <div>
                    <h2 class="font-display font-extrabold text-xl">Your account is ready</h2>
                    <p class="mt-1 text-sm text-ink/75">Sign in with <b>{{ $order->phone }}</b> and the password <b>{{ \App\Services\CheckoutService::startingPassword($order->phone) }}</b> to see all your orders. You can change the password after signing in.</p>
                </div>
                <a href="{{ route('login') }}" class="inline-flex items-center justify-center bg-primary hover:bg-primary-dark text-white font-extrabold rounded-xl px-6 py-3 transition-colors">Sign in</a>
            </div>
        </div>
    @endif

    <div class="max-w-7xl mx-auto px-4 lg:px-6 py-8">
        <ol class="flex items-center gap-3 max-w-xl bg-white border border-line rounded-2xl px-5 py-3" aria-label="Order progress">
            <li class="flex items-center gap-2"><span class="w-9 h-9 rounded-full border border-line grid place-items-center font-extrabold bg-accent">✓</span><span class="font-bold ">Cart</span></li>
            <li aria-hidden="true" class="flex-1 h-1 rounded bg-ink"></li>
            <li class="flex items-center gap-2"><span class="w-9 h-9 rounded-full border border-line grid place-items-center font-extrabold bg-accent">✓</span><span class="font-bold ">Checkout</span></li>
            <li aria-hidden="true" class="flex-1 h-1 rounded bg-ink"></li>
            <li class="flex items-center gap-2" aria-current="step"><span class="w-9 h-9 rounded-full border border-line grid place-items-center font-extrabold bg-accent">3</span><span class="font-bold ">Done</span></li>
        </ol>

        <div class="grid lg:grid-cols-[1fr_400px] gap-8 mt-8 items-start">
            <div class="grid gap-6">
                <section class="bg-white border border-line rounded-3xl p-6 lg:p-8" aria-labelledby="h-items">
                    <h2 id="h-items" class="font-display font-extrabold text-2xl">Order summary</h2>
                    <ul id="items" class="grid gap-4 mt-5">
                        @foreach ($order->items as $item)
                            <li class="flex items-center gap-4">
                                <img referrerpolicy="no-referrer" src="{{ $item->image }}" alt="{{ $item->name }}" width="300" height="300" class="w-16 h-16 rounded-xl object-cover border border-line">
                                <div class="flex-1 min-w-0">
                                    <p class="font-bold leading-snug">{{ $item->name }}</p>
                                    <p class="text-sm text-ink/60">{{ $item->variant_label ? $item->variant_label.' · ' : '' }}Qty {{ $item->quantity }}</p>
                                </div>
                                <p class="font-bold">{{ $tk($item->line_total) }}</p>
                            </li>
                        @endforeach
                    </ul>
                    <dl class="mt-6 grid gap-2.5 border-t-2 border-dashed border-line pt-5 max-w-sm ml-auto">
                        <div class="flex justify-between">
                            <dt>Subtotal</dt>
                            <dd id="t-sub" class="font-bold">{{ $tk($order->subtotal) }}</dd>
                        </div>
                        @if ($order->coupon_code)
                            <div id="r-disc" class="flex justify-between">
                                <dt>Coupon {{ $order->coupon_code }}</dt>
                                <dd id="t-disc" class="font-bold text-success">{{ $order->discount > 0 ? '-'.$tk($order->discount) : 'Free delivery' }}</dd>
                            </div>
                        @endif
                        <div class="flex justify-between">
                            <dt>Delivery</dt>
                            <dd id="t-fee" class="font-bold">{{ $order->delivery_fee > 0 ? $tk($order->delivery_fee) : 'Free' }}</dd>
                        </div>
                        <div class="flex justify-between items-baseline border-t-2 border-line pt-4 mt-1">
                            <dt class="font-display font-extrabold text-xl">Total</dt>
                            <dd id="t-total" class="font-display font-extrabold text-3xl text-primary">{{ $tk($order->total) }}</dd>
                        </div>
                    </dl>
                </section>

                <section class="bg-white border border-line rounded-3xl p-6 lg:p-8" aria-labelledby="h-next">
                    <h2 id="h-next" class="font-display font-extrabold text-2xl">What happens next</h2>
                    @if ($closed)
                        <p class="mt-5 rounded-xl bg-accent/30 border border-line px-4 py-3 text-sm font-bold">This order was {{ strtolower($order->status_label) }}.</p>
                    @else
                        <ol class="grid gap-5 mt-5">
                            @foreach ($steps as $n => [$title, $text])
                                <li class="flex gap-3"><span class="w-8 h-8 rounded-full border border-line grid place-items-center text-sm font-extrabold shrink-0 {{ $n < $reached ? 'bg-accent' : 'bg-white' }}">{{ $n + 1 }}</span>
                                    <div>
                                        <p class="font-bold leading-tight">{{ $title }}</p>
                                        <p class="text-sm text-ink/65">{{ $text }}</p>
                                    </div>
                                </li>
                            @endforeach
                        </ol>
                    @endif
                </section>
            </div>

            <aside class="grid gap-6 lg:sticky lg:top-48" aria-label="Order details">
                <section class="bg-white border border-line rounded-3xl p-6 shadow-sm">
                    <h2 class="font-display font-extrabold text-xl">Delivering to</h2>
                    <address class="not-italic mt-3 text-[15px] leading-relaxed"><b id="a-name">{{ $order->name }}</b><br><span id="a-addr">{{ $order->address }}, {{ $order->area }}, {{ $order->district }}, {{ $order->division }}</span><br><span id="a-phone">{{ $order->phone }}</span></address>
                    @if ($order->note)
                        <p id="a-note" class="mt-3 text-sm text-ink/70">Note: {{ $order->note }}</p>
                    @endif
                    <p class="mt-4 rounded-xl bg-accent/50 border border-line px-4 py-3 text-sm"><b>Estimated delivery</b><br><span id="eta">{{ $order->zone_label }}@if ($order->delivery_time), within {{ $order->delivery_time }}@if ($maxDays) (by {{ $order->created_at->copy()->addDays($maxDays)->format('D, j M') }})@endif @endif</span></p>
                </section>
                <section class="bg-white border border-line rounded-3xl p-6 shadow-sm">
                    <h2 class="font-display font-extrabold text-xl">Payment</h2>
                    <p class="mt-3 font-bold" id="p-name">{{ $order->payment_label }}</p>
                    <p class="text-sm text-ink/70 mt-1" id="p-status">{{ $order->payment_status === 'paid' ? 'Paid in full. Thank you!' : 'Please pay '.$tk($order->total).' in cash on delivery.' }}</p>
                </section>
                <div class="no-print grid gap-3">
                    <a href="{{ route('shop') }}" class="text-center bg-accent font-extrabold rounded-xl py-3.5 border border-line shadow-sm">Continue shopping</a>
                    <a href="{{ route('checkout.invoice', $order->number) }}" target="_blank" rel="noopener" class="text-center font-bold underline underline-offset-4 py-2">View invoice</a>
                </div>
                <p class="no-print text-sm text-ink/70 text-center">Need help? Call <a href="tel:{{ preg_replace('/[^\d+]/', '', (string) $setting('phone')) }}" class="font-bold text-ink">{{ $setting('phone') }}</a>.</p>
            </aside>
        </div>
    </div>
@endsection
