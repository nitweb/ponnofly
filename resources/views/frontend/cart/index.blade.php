@extends('frontend.dashboard.dashboard')

@section('frontend_title', 'Shopping cart | '.$setting('store_name'))
@section('frontend_description', 'Review your items, pick a delivery area and apply a coupon.')
@section('page', 'cart')

@section('frontend_contents')
    <section class="bg-primary border-b-4 border-line relative overflow-hidden"><span 
        class="absolute -right-10 -top-12 w-56 h-56 rounded-full bg-primary"></span><span 
        class="absolute right-40 -bottom-14 w-40 h-40 rounded-full bg-accent"></span><span 
        class="absolute right-24 top-8 w-8 h-8 rounded-full bg-accent"></span> 
        <div class="max-w-7xl mx-auto px-4 lg:px-6 py-9 relative text-white"> 
        <nav aria-label="Breadcrumb" class="text-sm"> 
        <ol class="flex gap-2"> 
        <li><a href="{{ url('/') }}" class="hover:underline">Home</a></li> 
        <li aria-hidden="true">/</li> 
        <li aria-current="page" class="font-bold">Cart</li> 
        </ol> 
        </nav> 
        <h1 class="font-display font-extrabold text-4xl lg:text-5xl mt-3">Shopping cart</h1> 
        <p class="mt-2 max-w-lg opacity-90">Review your items, pick a delivery area and apply a coupon.</p> 
        </div> 
        </section>

    <div class="max-w-7xl mx-auto px-4 lg:px-6 py-8">

        @if (session('error'))
            <p class="mb-6 rounded-xl bg-accent/30 border border-line px-4 py-3 text-sm font-bold text-danger" role="alert">{{ session('error') }}</p>
        @endif

        <ol class="flex items-center gap-3 max-w-xl bg-white border border-line rounded-2xl px-5 py-3" aria-label="Order progress">
            <li class="flex items-center gap-2" aria-current="step"><span class="w-9 h-9 rounded-full border border-line grid place-items-center font-extrabold bg-accent">1</span><span class="font-bold">Cart</span></li>
            <li aria-hidden="true" class="flex-1 h-1 rounded bg-ink/20"></li>
            <li class="flex items-center gap-2"><span class="w-9 h-9 rounded-full border border-line grid place-items-center font-extrabold bg-white">2</span><span class="font-bold text-ink/50">Checkout</span></li>
            <li aria-hidden="true" class="flex-1 h-1 rounded bg-ink/20"></li>
            <li class="flex items-center gap-2"><span class="w-9 h-9 rounded-full border border-line grid place-items-center font-extrabold bg-white">3</span><span class="font-bold text-ink/50">Done</span></li>
        </ol>

        <div class="cart-head flex items-end justify-between mt-8">
            <h2 class="font-display font-extrabold text-2xl">Your items <span id="count" class="text-ink/60 text-lg font-bold"></span></h2>
            <a href="{{ route('shop') }}" class="font-bold underline underline-offset-4">Continue shopping</a>
        </div>

        <div id="cart-wrap" class="grid lg:grid-cols-[1fr_390px] gap-8 mt-5 items-start" @if (! $cartSummary['items']) hidden @endif>
            <ul id="items" class="grid gap-4" aria-label="Cart items"></ul>

            <aside aria-label="Order summary" class="lg:sticky lg:top-48 bg-white border border-line rounded-3xl p-6 shadow-sm">
                <h2 class="font-display font-extrabold text-2xl">Order summary</h2>

                <fieldset class="mt-5">
                    <legend class="font-bold mb-2">Delivery area</legend>
                    <div class="grid gap-2">
                        @foreach ($cartSummary['zones'] as $zone)
                            <label class="block">
                                <input type="radio" name="zone" value="{{ $zone['key'] }}" class="peer sr-only" @checked($cartSummary['shipping']['zone'] === $zone['key'])>
                                <span class="flex items-center justify-between gap-3 rounded-xl border border-line px-4 py-3 cursor-pointer bg-white peer-checked:bg-accent peer-checked:shadow-sm peer-focus-visible:ring-4 peer-focus-visible:ring-primary">
                                    <span>
                                        <span class="block font-bold">{{ $zone['label'] }}</span>
                                        @if ($zone['time'])
                                            <span class="block text-xs text-ink/65">Arrives in {{ $zone['time'] }}</span>
                                        @endif
                                    </span>
                                    <span class="font-extrabold">৳{{ number_format($zone['fee'], floor($zone['fee']) == $zone['fee'] ? 0 : 2) }}</span>
                                </span>
                            </label>
                        @endforeach
                    </div>
                </fieldset>

                <div class="mt-5">
                    <label for="coupon" class="font-bold block mb-2">Coupon code</label>
                    <div class="flex gap-2">
                        <input id="coupon" type="text" placeholder="Enter coupon code" maxlength="40" autocomplete="off" value="{{ $cartSummary['coupon']['code'] ?? '' }}" class="w-full rounded-xl border border-line px-4 py-3 bg-white focus:outline-none focus:shadow-md uppercase">
                        <button type="button" id="apply" class="bg-ink text-white font-bold rounded-xl px-5 hover:bg-primary">Apply</button>
                    </div>
                    <p id="cmsg" class="text-sm mt-2" role="status"></p>
                </div>

                <dl class="mt-6 grid gap-2.5 border-t-2 border-dashed border-line pt-5">
                    <div class="flex justify-between">
                        <dt>Subtotal</dt>
                        <dd id="t-sub" class="font-bold"></dd>
                    </div>
                    <div id="r-save" class="flex justify-between text-ink/70" hidden>
                        <dt>Product discounts</dt>
                        <dd id="t-save" class="font-bold text-success"></dd>
                    </div>
                    <div id="r-disc" class="flex justify-between" hidden>
                        <dt id="r-disc-label">Coupon</dt>
                        <dd id="t-disc" class="font-bold text-success"></dd>
                    </div>
                    <div class="flex justify-between">
                        <dt>Delivery</dt>
                        <dd id="t-fee" class="font-bold"></dd>
                    </div>
                    <div class="flex justify-between items-baseline border-t-2 border-line pt-4 mt-1">
                        <dt class="font-display font-extrabold text-xl">Total</dt>
                        <dd id="t-total" class="font-display font-extrabold text-3xl text-primary"></dd>
                    </div>
                </dl>

                <p id="cart-note" class="mt-4 text-sm font-bold text-primary" role="status" hidden></p>

                <a id="checkout-btn" href="{{ $cartSummary['checkout_url'] }}" class="block text-center mt-6 bg-primary hover:bg-primary-dark text-white font-extrabold rounded-xl py-4 border border-line shadow-sm hover:shadow-md transition-all">Proceed to checkout</a>

            </aside>
        </div>

        <div id="cart-empty" @if ($cartSummary['items']) hidden @endif class="mt-8 text-center bg-white border border-line rounded-3xl p-12">
            <p class="text-6xl" aria-hidden="true">🛒</p>
            <h2 class="font-display font-extrabold text-3xl mt-4">Your cart is empty</h2>
            <p class="mt-2 text-ink/70">Add something and it will show up here.</p>
            <a href="{{ route('shop') }}" class="inline-block mt-6 bg-accent font-extrabold rounded-xl px-8 py-3.5 border border-line shadow-sm">Start shopping</a>
        </div>

        @if ($recommended->isNotEmpty())
            <section class="mt-16" aria-labelledby="h-rec">
                <h2 id="h-rec" class="font-display font-extrabold text-2xl mb-6">You may also like</h2>
                <ul class="grid grid-cols-2 lg:grid-cols-4 gap-3 sm:gap-5">
                    @foreach ($recommended as $item)
                        @include('frontend.partials.product_card', ['product' => $item])
                    @endforeach
                </ul>
            </section>
        @endif

    </div>
@endsection
