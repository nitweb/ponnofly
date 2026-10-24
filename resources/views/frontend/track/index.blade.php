@extends('frontend.dashboard.dashboard')

@section('frontend_title', 'Track order | '.$setting('store_name'))
@section('frontend_description', 'Track your order with your order number and mobile number.')
@section('page', 'track')

@section('frontend_contents')
    @php
        $steps = [
            ['Order placed', 'We received your order.'],
            ['Confirmed', 'Your order is confirmed and being packed.'],
            ['Shipped', 'Your package is on the way.'],
            ['Out for delivery', 'Our rider will call you soon.'],
            ['Delivered', 'Package delivered. Enjoy!'],
        ];
        $current = $order ? match ($order->status) {
            'pending' => 0,
            'processing' => 1,
            'shipped' => 2,
            'out_for_delivery' => 3,
            'delivered' => 4,
            default => 0,
        } : 0;
        $closed = $order?->isFinal();
        $stamps = $order ? $order->events->groupBy('status')->map(fn ($events) => $events->first()->created_at) : collect();
        $stepStatus = ['pending', 'processing', 'shipped', 'out_for_delivery', 'delivered'];
    @endphp

    <div class="pg">
        <section class="ph">
            <div class="wrap ph__in">
                <div>
                    <nav aria-label="Breadcrumb" class="crumbs">
                        <ol>
                            <li><a href="{{ url('/') }}">Home</a></li>
                            <li aria-hidden="true">/</li>
                            <li aria-current="page">Track order</li>
                        </ol>
                    </nav>
                    <h1>Track order</h1>
                    <p class="ph__sub">Enter your order number and mobile number to see exactly where your package is.</p>
                </div>
                <div class="ph__badge" aria-hidden="true"><span class="ic ic-package" aria-hidden="true"></span></div>
            </div>
        </section>

        <section class="sec">
            <div class="wrap">
                <div class="trk-grid">
                    <div class="card trk-find">
                        <h2>Where is my package?</h2>
                        <p>Enter the order number from your confirmation message and the mobile number used at checkout.</p>
                        <form id="track-form" class="trk-form" method="POST" action="{{ route('track.lookup') }}" novalidate>
                            @csrf
                            <div class="fld"><label class="lbl" for="t-no">Order number</label>
                                <div class="fld__box"><span class="ic ic-package" aria-hidden="true"></span><input id="t-no" name="order_number" type="text" required
                                        placeholder="e.g. NM-10001" autocomplete="off" value="{{ old('order_number') }}"></div>
                                @error('order_number')<p class="text-sm font-bold mt-1" style="color:#c0392b">{{ $message }}</p>@enderror
                            </div>
                            <div class="fld"><label class="lbl" for="t-phone">Mobile number</label>
                                <div class="fld__box"><span class="ic ic-phone" aria-hidden="true"></span><input id="t-phone" name="phone" type="tel" required
                                        placeholder="01XXXXXXXXX" autocomplete="tel" inputmode="numeric" value="{{ old('phone') }}"></div>
                                @error('phone')<p class="text-sm font-bold mt-1" style="color:#c0392b">{{ $message }}</p>@enderror
                            </div>
                            <button type="submit" class="btn btn--primary">Track order <span class="ic ic-arrow" aria-hidden="true"></span></button>
                        </form>
                    </div>
                    <aside class="card trk-how">
                        <h3>How tracking works</h3>
                        <ol>
                            <li><span><b>Find your number</b>It is in the SMS and email we sent after checkout.</span></li>
                            <li><span><b>Enter it here</b>Order numbers start with NM, followed by digits.</span></li>
                            <li><span><b>See live status</b>Follow your package from packing to your door.</span></li>
                        </ol>
                    </aside>
                </div>

                @if ($order)
                    <section id="track-result" class="card trk-res" aria-live="polite">
                        <header class="trk-res__head">
                            <div>
                                <p class="trk-res__k">Order number</p>
                                <h2 id="t-id">{{ $order->number }}</h2>
                                <p id="t-eta" class="trk-res__eta">{{ $order->zone_label }} delivery{{ $order->delivery_time ? ', within '.$order->delivery_time : '' }}.</p>
                            </div><span class="pill" id="t-state">{{ $closed ? $order->status_label : $steps[$current][0] }}</span>
                        </header>

                        @if ($closed)
                            <p class="trk-res__eta" style="margin:1rem 0">This order was {{ strtolower($order->status_label) }}. Please contact support if you need help.</p>
                        @else
                            <div class="trk-flow">
                                <div class="trk-line" id="t-line" style="--p: {{ $current / (count($steps) - 1) * 100 }}"><i></i></div>
                                <ol id="t-steps" class="trk-steps">
                                    @foreach ($steps as $i => [$title, $text])
                                        @php
                                            $done = $i < $current || $current === 4;
                                            $stamp = $stepStatus[$i] ? $stamps->get($stepStatus[$i]) : null;
                                        @endphp
                                        <li class="trk-step {{ $done ? 'is-done' : ($i === $current ? 'is-now' : '') }}">
                                            <span class="trk-node">@if ($done)<span class="ic ic-check"></span>@else{{ $i + 1 }}@endif</span>
                                            <div>
                                                <p class="trk-t">{{ $title }}</p>
                                                <p class="trk-d">{{ $text }}@if ($stamp) · {{ $stamp->copy()->timezone(\App\Models\Setting::tz())->format('d M, h:i A') }}@endif</p>
                                            </div>
                                        </li>
                                    @endforeach
                                </ol>
                            </div>
                        @endif

                        <dl class="trk-facts">
                            <div><dt>Delivery zone</dt><dd id="t-zone">{{ $order->zone_label }}</dd></div>
                            <div><dt>Estimated arrival</dt><dd id="t-win">{{ $order->delivery_time ?: '—' }}</dd></div>
                            <div><dt>Need help?</dt><dd>{{ $setting('phone') }}</dd></div>
                        </dl>
                    </section>
                @elseif ($searched)
                    <section id="track-none" class="card trk-none" aria-live="polite"><span class="ic ic-x" aria-hidden="true"></span>
                        <div>
                            <h2>We could not find that order</h2>
                            <p>Check the order number and mobile number and try again, or <a href="{{ url('/') }}">contact support</a> and we will help right away.</p>
                        </div>
                    </section>
                @endif
            </div>
        </section>
    </div>
    @if ($searched)
        <script>
            document.addEventListener('DOMContentLoaded', function () {
                var target = document.getElementById('track-result') || document.getElementById('track-none');
                if (target) target.scrollIntoView({ behavior: 'smooth', block: 'start' });
            });
        </script>
    @endif
@endsection
