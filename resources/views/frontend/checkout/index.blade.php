@extends('frontend.dashboard.dashboard')

@section('frontend_title', 'Checkout | '.$setting('store_name'))
@section('frontend_description', 'Enter your delivery details. Pay in cash when your order arrives.')
@section('page', 'checkout')

@section('frontend_contents')
    @php
        $tk = fn (float $n) => '৳'.number_format($n, floor($n) == $n ? 0 : 2);
        $field = 'w-full rounded-xl border border-line px-4 py-3 bg-white focus:outline-none focus:shadow-md';
        $shipping = $cartSummary['shipping'];
    @endphp

    <section class="bg-primary border-b-4 border-line relative overflow-hidden"><span
        class="absolute -right-10 -top-12 w-56 h-56 rounded-full bg-primary"></span><span
        class="absolute right-40 -bottom-14 w-40 h-40 rounded-full bg-accent"></span><span
        class="absolute right-24 top-8 w-8 h-8 rounded-full bg-accent"></span>
        <div class="max-w-7xl mx-auto px-4 lg:px-6 py-9 relative text-white">
            <nav aria-label="Breadcrumb" class="text-sm">
                <ol class="flex gap-2">
                    <li><a href="{{ url('/') }}" class="hover:underline">Home</a></li>
                    <li aria-hidden="true">/</li>
                    <li aria-current="page" class="font-bold">Checkout</li>
                </ol>
            </nav>
            <h1 class="font-display font-extrabold text-4xl lg:text-5xl mt-3">Checkout</h1>
            <p class="mt-2 max-w-lg opacity-90">Tell us where to deliver. You pay in cash when it arrives.</p>
        </div>
    </section>

    <div class="max-w-7xl mx-auto px-4 lg:px-6 py-8">
        <ol class="flex items-center gap-3 max-w-xl bg-white border border-line rounded-2xl px-5 py-3" aria-label="Order progress">
            <li class="flex items-center gap-2"><span class="w-9 h-9 rounded-full border border-line grid place-items-center font-extrabold bg-accent">✓</span><span class="font-bold ">Cart</span></li>
            <li aria-hidden="true" class="flex-1 h-1 rounded bg-ink"></li>
            <li class="flex items-center gap-2" aria-current="step"><span class="w-9 h-9 rounded-full border border-line grid place-items-center font-extrabold bg-accent">2</span><span class="font-bold ">Checkout</span></li>
            <li aria-hidden="true" class="flex-1 h-1 rounded bg-ink/20"></li>
            <li class="flex items-center gap-2"><span class="w-9 h-9 rounded-full border border-line grid place-items-center font-extrabold bg-white">3</span><span class="font-bold text-ink/50">Done</span></li>
        </ol>

        @if ($errors->has('cart'))
            <p class="mt-6 rounded-xl bg-accent/30 border border-line px-4 py-3 text-sm font-bold text-danger" role="alert">{{ $errors->first('cart') }}</p>
        @endif

        <form id="co" method="POST" action="{{ route('checkout.store') }}" novalidate class="grid lg:grid-cols-[1fr_400px] gap-8 mt-8 items-start">
            @csrf
            <div class="grid gap-6">
                <section class="bg-white border border-line rounded-3xl p-6 lg:p-8" aria-labelledby="h-ship">
                    <h2 id="h-ship" class="font-display font-extrabold text-2xl">Delivery details</h2>
                    @if ($addresses->count() > 1)
                        <div class="mt-5">
                            <label for="saved-address" class="block font-bold text-sm mb-1.5">Deliver to a saved address</label>
                            <select id="saved-address" class="{{ $field }}">
                                @foreach ($addresses as $saved)
                                    <option value="{{ $saved->id }}" data-name="{{ $saved->name }}" data-phone="{{ $saved->phone }}" data-district="{{ $saved->district }}" data-area="{{ $saved->area }}" data-address="{{ $saved->address }}">{{ $saved->label }}: {{ $saved->full_address }}</option>
                                @endforeach
                            </select>
                        </div>
                    @endif
                    <div class="grid sm:grid-cols-2 gap-4 mt-5">
                        <div>
                            <label for="name" class="block font-bold text-sm mb-1.5">Full name <span class="text-primary">*</span></label>
                            <input id="name" name="name" type="text" required autocomplete="name" placeholder="e.g. Rahim Uddin" maxlength="100" value="{{ old('name', $user?->name) }}" class="{{ $field }}">
                            @error('name')<p class="text-sm font-bold text-danger mt-1.5">{{ $message }}</p>@enderror
                        </div>
                        <div>
                            <label for="phone" class="block font-bold text-sm mb-1.5">Mobile number <span class="text-primary">*</span></label>
                            <input id="phone" name="phone" type="tel" required autocomplete="tel" inputmode="numeric" pattern="01[3-9][0-9]{8}" maxlength="11" placeholder="01XXXXXXXXX" value="{{ old('phone', $user?->phone) }}" class="{{ $field }}">
                            @error('phone')<p class="text-sm font-bold text-danger mt-1.5">{{ $message }}</p>@enderror
                        </div>
                        <div class="sm:col-span-2">
                            <label for="email" class="block font-bold text-sm mb-1.5">Email address <span class="text-primary">*</span></label>
                            <input id="email" name="email" type="email" required autocomplete="email" placeholder="you@example.com" maxlength="150" value="{{ old('email', $user?->email) }}" class="{{ $field }}">
                            @error('email')<p class="text-sm font-bold text-danger mt-1.5">{{ $message }}</p>@enderror
                        </div>
                        <div class="sm:col-span-2 relative">
                            <label for="district" class="block font-bold text-sm mb-1.5">District <span class="text-primary">*</span></label>
                            <input id="district" name="district" type="text" required autocomplete="off" role="combobox" aria-autocomplete="list" aria-expanded="false" aria-controls="district-list" placeholder="Search or select district" value="{{ old('district', $prefill?->district) }}" class="{{ $field }}">
                            <ul id="district-list" role="listbox" hidden></ul>
                            @error('district')<p class="text-sm font-bold text-danger mt-1.5">{{ $message }}</p>@enderror
                        </div>
                        <div class="sm:col-span-2 relative">
                            <label for="area-txt" class="block font-bold text-sm mb-1.5">Area / Thana <span class="text-primary">*</span></label>
                            <input id="area-txt" aria-autocomplete="list" aria-controls="area-list" name="area_txt" type="text" required autocomplete="off" maxlength="120" @disabled(! old('district', $prefill?->district)) placeholder="{{ old('district', $prefill?->district) ? 'Select or type thana / upazila' : 'Select district first' }}" value="{{ old('area_txt', old('area', $prefill?->area)) }}" class="{{ $field }}">
                            <ul id="area-list" role="listbox" hidden></ul>
                            @error('area')<p class="text-sm font-bold text-danger mt-1.5">{{ $message }}</p>@enderror
                        </div>
                        <div class="sm:col-span-2">
                            <label for="address" class="block font-bold text-sm mb-1.5">Full address <span class="text-primary">*</span></label>
                            <textarea id="address" name="address" rows="2" required maxlength="500" autocomplete="street-address" placeholder="House, road, block, landmark" class="{{ $field }}">{{ old('address', $prefill?->address) }}</textarea>
                            @error('address')<p class="text-sm font-bold text-danger mt-1.5">{{ $message }}</p>@enderror
                        </div>
                        <div class="sm:col-span-2">
                            <label for="note" class="block font-bold text-sm mb-1.5">Order note <span class="font-medium text-ink/55">(optional)</span></label>
                            <textarea id="note" name="note" rows="2" maxlength="500" placeholder="Anything we should know, for example delivery time or gift wrap" class="{{ $field }}">{{ old('note') }}</textarea>
                            @error('note')<p class="text-sm font-bold text-danger mt-1.5">{{ $message }}</p>@enderror
                        </div>
                    </div>
                    <p id="zone-msg" class="mt-4 rounded-xl bg-accent/30 border border-line px-4 py-3 text-sm font-bold" role="status">Select your district to see the delivery charge.</p>
                </section>

                <section class="bg-white border border-line rounded-3xl p-6 lg:p-8" aria-labelledby="h-pay">
                    <h2 id="h-pay" class="font-display font-extrabold text-2xl">Payment method</h2>
                    <div class="grid gap-3 mt-5">
                        <label class="block"><input type="radio" name="pay" value="cod" class="peer sr-only" checked><span
                            class="flex items-center gap-4 p-4 rounded-2xl border border-line bg-white cursor-pointer peer-checked:bg-primary-soft peer-checked:shadow-sm peer-focus-visible:ring-4 peer-focus-visible:ring-primary"><span
                                class="w-16 text-center py-2 rounded-lg text-xs font-extrabold shrink-0 text-ink u-ef28f7">COD</span><span><span
                                    class="block font-bold">Cash on delivery</span><span class="block text-sm text-ink/65">Pay in cash when your order arrives</span></span></span></label>
                    </div>
                    <div data-pay-panel="cod" class="mt-4 rounded-2xl border border-line bg-accent/25 p-5 text-sm">Pay <b class="pay-amt">{{ $tk($cartSummary['total']) }}</b> in cash to our delivery person. Please keep the exact amount ready.</div>
                </section>
            </div>

            <aside aria-label="Order summary" class="lg:sticky lg:top-48 bg-white border border-line rounded-3xl p-6 shadow-sm">
                <div class="flex items-center justify-between">
                    <h2 class="font-display font-extrabold text-2xl">Your order</h2>
                    <a href="{{ route('cart.index') }}" class="text-sm font-bold underline underline-offset-4">Edit cart</a>
                </div>
                <ul id="mini" class="grid gap-3 mt-5 max-h-72 overflow-y-auto u-cdfbc3">
                    @foreach ($cartSummary['items'] as $line)
                        <li class="flex gap-3">
                            <span class="relative shrink-0"><img src="{{ $line['image'] }}" alt="" width="300" height="300" class="w-14 h-14 rounded-lg object-cover border border-line"><span class="absolute -top-2 -right-2 min-w-5 h-5 px-1 rounded-full bg-ink text-white text-xs font-bold grid place-items-center">{{ $line['quantity'] }}</span></span>
                            <span class="flex-1 min-w-0 text-sm font-bold leading-snug">{{ $line['name'] }}@if ($line['variant'])<span class="block text-xs font-medium text-ink/60">{{ $line['variant'] }}</span>@endif</span>
                            <span class="mi-price font-bold text-sm">{{ $tk($line['line_total']) }}</span>
                        </li>
                    @endforeach
                </ul>
                <div class="mt-5">
                    <label for="co-coupon" class="font-bold block mb-2">Coupon code</label>
                    <div class="flex gap-2">
                        <input id="co-coupon" type="text" placeholder="Enter coupon code" maxlength="40" autocomplete="off" value="{{ $cartSummary['coupon']['code'] ?? '' }}" @readonly((bool) $cartSummary['coupon']) class="w-full rounded-xl border border-line px-4 py-3 bg-white focus:outline-none focus:shadow-md uppercase read-only:bg-ink/5 read-only:cursor-not-allowed">
                        <button type="button" id="co-apply" @disabled((bool) $cartSummary['coupon']) class="bg-ink text-white font-bold rounded-xl px-5 hover:bg-primary disabled:opacity-50 disabled:cursor-not-allowed disabled:hover:bg-ink">Apply</button>
                    </div>
                    <p id="co-cmsg" class="text-sm mt-2" role="status">@if ($cartSummary['coupon'])<span class="text-success font-bold">{{ $cartSummary['coupon']['code'] }} applied: {{ $cartSummary['coupon']['label'] }}.</span>@endif</p>
                </div>
                <dl class="mt-5 grid gap-2.5 border-t-2 border-dashed border-line pt-5">
                    <div class="flex justify-between">
                        <dt>Subtotal</dt>
                        <dd id="t-sub" class="font-bold">{{ $tk($cartSummary['subtotal']) }}</dd>
                    </div>
                    <div id="r-disc" class="flex justify-between" @if (! $cartSummary['coupon'] || (! $cartSummary['discount'] && ! $cartSummary['coupon']['free_delivery'])) hidden @endif>
                        <dt id="r-disc-label">Coupon {{ $cartSummary['coupon']['code'] ?? '' }}</dt>
                        <dd id="t-disc" class="font-bold text-success">{{ ($cartSummary['coupon']['free_delivery'] ?? false) ? 'Free delivery' : '-'.$tk($cartSummary['discount']) }}</dd>
                    </div>
                    <div class="flex justify-between">
                        <dt>Delivery <span id="t-zone" class="text-xs text-ink/60">({{ $shipping['label'] }})</span></dt>
                        <dd id="t-fee" class="font-bold">{{ $shipping['free'] ? 'Free' : $tk($shipping['fee']) }}</dd>
                    </div>
                    <div class="flex justify-between items-baseline border-t-2 border-line pt-4 mt-1">
                        <dt class="font-display font-extrabold text-xl">Total</dt>
                        <dd id="t-total" class="font-display font-extrabold text-3xl text-primary">{{ $tk($cartSummary['total']) }}</dd>
                    </div>
                </dl>
                <div class="mt-6">
                    <label class="flex items-start gap-3 cursor-pointer"><input id="terms" name="terms" value="1" type="checkbox" required @checked(old('terms')) class="w-5 h-5 mt-0.5 accent-primary"><span class="text-sm">I agree to the <a href="{{ url('/terms') }}" class="underline font-bold">terms of service</a>, the <a href="{{ url('/returns') }}" class="underline font-bold">return policy</a> and the <a href="{{ url('/privacy') }}" class="underline font-bold">privacy policy</a>.</span></label>
                    @error('terms')<p class="text-sm font-bold text-danger mt-1.5">{{ $message }}</p>@enderror
                </div>
                <button type="submit" id="place-order" class="w-full mt-4 bg-primary hover:bg-primary-dark text-white font-extrabold rounded-xl py-4 border border-line shadow-sm hover:shadow-md transition-all">Place order</button>
                <div class="mt-5 flex items-center gap-3"><svg class="w-5 h-5 shrink-0"><use href="#i-shield" /></svg>
                    <p class="text-xs text-ink/70">Your details are encrypted and used only to deliver your order.</p>
                </div>
            </aside>
        </form>
    </div>

    @if ($addresses->count() > 1)
        @push('scripts')
            <script>
                document.addEventListener('DOMContentLoaded', function () {
                    var pick = document.getElementById('saved-address');
                    if (!pick) return;
                    pick.addEventListener('change', function () {
                        var o = pick.options[pick.selectedIndex], d = o.dataset, f = function (id) { return document.getElementById(id) };
                        f('name').value = d.name;
                        f('phone').value = d.phone;
                        f('address').value = d.address;
                        f('district').value = d.district;
                        f('district').dispatchEvent(new Event('input', { bubbles: true }));
                        f('district-list').hidden = true;
                        f('area-txt').value = d.area;
                    });
                });
            </script>
        @endpush
    @endif
@endsection
