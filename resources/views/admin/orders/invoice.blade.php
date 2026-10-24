@php
    use App\Models\Setting;

    $tz = Setting::tz();
    $money = fn ($n) => '৳'.number_format((float) $n, 2);
    $store = Setting::get('store_name') ?: config('app.name');
    $logo = Setting::get('logo_header') ? asset(Setting::get('logo_header')) : null;
    $hexOr = fn ($v, $d) => preg_match('/^#[0-9a-fA-F]{6}$/', (string) $v) ? $v : $d;
    $accent = $hexOr(Setting::get('color_primary'), '#0B3A8C');
    $gold = $hexOr(Setting::get('color_accent'), '#F5A800');
    $inkColor = $hexOr(Setting::get('color_text'), '#1a2238');
    $stacks = [
        'Plus Jakarta Sans' => "'Plus Jakarta Sans', system-ui, -apple-system, 'Segoe UI', 'Noto Sans Bengali', sans-serif",
        'Inter' => "'Inter', system-ui, -apple-system, 'Segoe UI', 'Noto Sans Bengali', sans-serif",
    ];
    $fontHeading = $stacks[Setting::get('font_heading')] ?? $stacks['Plus Jakarta Sans'];
    $fontBody = $stacks[Setting::get('font_body')] ?? $stacks['Inter'];
    $mixHex = function (string $from, string $to, float $amount): string {
        $a = sscanf($from, '#%02x%02x%02x');
        $b = sscanf($to, '#%02x%02x%02x');

        return sprintf('#%02x%02x%02x', ...array_map(fn ($x, $y) => (int) round($x + ($y - $x) * $amount), $a, $b));
    };
    $accentDark = $mixHex($accent, '#000000', 0.22);
    $accentLight = $mixHex($accent, '#ffffff', 0.30);
    $accentTint = $mixHex($accent, '#ffffff', 0.90);
    $trackUrl = route('track.show', ['order' => $order->number]);
    $qr = 'https://api.qrserver.com/v1/create-qr-code/?size=240x240&margin=0&data='.urlencode($trackUrl);
    $placed = $order->created_at->timezone($tz);
    $paid = $order->payment_status === 'paid';
    $stamp = match ($order->status) { 'cancelled' => 'Cancelled', 'returned' => 'Returned', default => null };
    $qtyTotal = (int) $order->items->sum('quantity');
@endphp
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="robots" content="noindex, nofollow">
    <title>Invoice {{ $order->number }} · {{ $store }}</title>
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=inter:400,500,600,700|plus-jakarta-sans:600,700,800&display=swap" rel="stylesheet">
    <style>
        :root { --accent: {{ $accent }}; --accent-dark: {{ $accentDark }}; --accent-light: {{ $accentLight }}; --accent-tint: {{ $accentTint }}; --gold: {{ $gold }}; --ink: {{ $inkColor }}; --muted: #6b7388; --line: #e6eaf3; --soft: #f5f7fc; --display: {!! $fontHeading !!}; --body: {!! $fontBody !!}; }
        * { box-sizing: border-box; }
        html, body { margin: 0; padding: 0; }
        body { font-family: var(--body); color: var(--ink); background: #e9edf5; font-size: 13px; line-height: 1.55; -webkit-print-color-adjust: exact; print-color-adjust: exact; }
        .toolbar { max-width: 794px; margin: 18px auto 0; display: flex; justify-content: flex-end; gap: 10px; padding: 0 8px; }
        .toolbar button, .toolbar a { font: inherit; font-weight: 600; padding: 9px 18px; border-radius: 12px; border: 1px solid var(--line); background: #fff; color: var(--ink); cursor: pointer; text-decoration: none; }
        .toolbar .primary { background: linear-gradient(135deg, var(--accent), var(--accent-dark)); border-color: transparent; color: #fff; }
        .sheet { position: relative; width: 794px; max-width: 100%; min-height: 1000px; margin: 14px auto 28px; background: #fff; border-radius: 22px; box-shadow: 0 18px 50px -16px rgba(20, 30, 70, .3); overflow: hidden; }
        .hero { position: relative; padding: 38px 48px 74px; color: #fff; background: linear-gradient(125deg, var(--accent-dark) 0%, var(--accent) 55%, var(--accent-light) 100%); overflow: hidden; }
        .hero::before { content: ""; position: absolute; width: 320px; height: 320px; right: -90px; top: -150px; border-radius: 50%; background: rgba(255, 255, 255, .10); }
        .hero::after { content: ""; position: absolute; width: 200px; height: 200px; left: 38%; bottom: -130px; border-radius: 50%; background: var(--gold); opacity: .28; }
        header.top { position: relative; z-index: 1; display: flex; justify-content: space-between; align-items: flex-start; gap: 24px; }
        .brand .logo { display: inline-block; background: #fff; border-radius: 14px; padding: 8px 14px; margin-bottom: 10px; }
        .brand img { max-height: 44px; max-width: 200px; display: block; }
        .brand .name { font-family: var(--display); font-size: 24px; font-weight: 800; letter-spacing: -.01em; margin-bottom: 6px; }
        .brand p { margin: 2px 0 0; opacity: .85; max-width: 300px; font-size: 12px; }
        .doc { text-align: right; }
        .doc h1 { margin: 0; font-family: var(--display); font-size: 34px; letter-spacing: .16em; font-weight: 800; line-height: 1; }
        .doc .no { display: inline-block; margin-top: 10px; padding: 5px 14px; border-radius: 999px; background: var(--gold); color: #1a1a1a; font-weight: 800; font-size: 12.5px; letter-spacing: .04em; }
        .pill { display: inline-block; padding: 2px 12px; border-radius: 999px; font-size: 11px; font-weight: 800; letter-spacing: .06em; text-transform: uppercase; }
        .pill.paid { background: #dcf5e8; color: #0f7a46; }
        .pill.unpaid { background: #fff0d1; color: #9a5b00; }
        .meta { position: relative; z-index: 2; display: grid; grid-template-columns: repeat(4, 1fr); gap: 0; margin: -40px 48px 0; background: #fff; border-radius: 16px; box-shadow: 0 12px 30px -14px rgba(20, 30, 70, .35); border: 1px solid var(--line); }
        .meta div { padding: 14px 18px; border-right: 1px solid var(--line); }
        .meta div:last-child { border-right: 0; }
        .meta small { display: block; font-size: 10px; letter-spacing: .12em; text-transform: uppercase; color: var(--muted); font-weight: 700; }
        .meta b { display: block; margin-top: 3px; font-size: 13.5px; font-family: var(--display); }
        .body { padding: 28px 48px 32px; }
        .info { display: grid; grid-template-columns: repeat(3, 1fr); gap: 14px; }
        .card { background: var(--soft); border-radius: 14px; padding: 14px 16px; border: 1px solid var(--line); }
        .card h3 { margin: 0 0 8px; font-size: 10.5px; letter-spacing: .12em; text-transform: uppercase; color: var(--accent); font-weight: 800; }
        .card p { margin: 0 0 2px; }
        .card .strong { font-weight: 700; font-size: 14px; font-family: var(--display); }
        .card .k { color: var(--muted); }
        table.items { width: 100%; border-collapse: separate; border-spacing: 0; margin-top: 24px; }
        table.items thead th { background: var(--accent); color: #fff; font-size: 10.5px; letter-spacing: .1em; text-transform: uppercase; text-align: left; padding: 11px 12px; }
        table.items thead th:first-child { border-radius: 10px 0 0 10px; }
        table.items thead th:last-child { border-radius: 0 10px 10px 0; }
        table.items th.r, table.items td.r { text-align: right; }
        table.items td { padding: 13px 12px; border-bottom: 1px solid var(--line); vertical-align: top; }
        table.items tbody tr:nth-child(even) td { background: #fafbfe; }
        table.items tr { page-break-inside: avoid; }
        .idx { display: inline-flex; width: 24px; height: 24px; align-items: center; justify-content: center; border-radius: 8px; background: var(--accent-tint); color: var(--accent); font-weight: 800; font-size: 11px; }
        .item-name { font-weight: 700; }
        .item-meta { color: var(--muted); font-size: 11.5px; margin-top: 2px; }
        .lower { display: grid; grid-template-columns: 1fr 300px; gap: 28px; margin-top: 22px; align-items: start; }
        .notes h3 { margin: 0 0 6px; font-size: 10.5px; letter-spacing: .12em; text-transform: uppercase; color: var(--muted); }
        .notes p { margin: 0 0 14px; }
        .totals { background: var(--soft); border: 1px solid var(--line); border-radius: 16px; padding: 14px 16px; }
        .totals .row { display: flex; justify-content: space-between; padding: 5px 0; }
        .totals .row span:first-child { color: var(--muted); }
        .totals .grand { margin-top: 8px; padding-top: 12px; border-top: 2px dashed var(--line); font-size: 17px; font-weight: 800; font-family: var(--display); }
        .totals .grand span:first-child { color: var(--ink); }
        .totals .grand span:last-child { color: var(--accent); }
        .due { display: flex; justify-content: space-between; margin-top: 10px; padding: 12px 16px; border-radius: 14px; font-weight: 800; font-family: var(--display); background: {{ $paid ? '#dcf5e8' : '#fff0d1' }}; color: {{ $paid ? '#0f7a46' : '#9a5b00' }}; }
        footer.foot { margin-top: 34px; padding-top: 18px; border-top: 1px solid var(--line); display: flex; justify-content: space-between; align-items: center; gap: 20px; color: var(--muted); font-size: 11.5px; }
        footer.foot .thanks { color: var(--ink); font-weight: 800; font-size: 14px; font-family: var(--display); margin-bottom: 2px; }
        .qr { display: flex; align-items: center; gap: 12px; text-align: right; }
        .qr img { width: 76px; height: 76px; border-radius: 10px; border: 1px solid var(--line); padding: 4px; background: #fff; }
        .qr small { display: block; }
        .bar { height: 6px; background: linear-gradient(90deg, var(--accent), var(--gold)); }
        .stamp { position: absolute; top: 340px; left: 50%; transform: translateX(-50%) rotate(-18deg); font-size: 64px; font-weight: 900; letter-spacing: .1em; text-transform: uppercase; color: rgba(200, 40, 40, .12); border: 8px solid rgba(200, 40, 40, .12); padding: 4px 28px; border-radius: 14px; pointer-events: none; z-index: 3; }
        @media screen and (max-width: 640px) { .hero { padding: 28px 20px 70px; } .meta { margin: -40px 16px 0; grid-template-columns: 1fr 1fr; } .meta div:nth-child(2) { border-right: 0; } .meta div:nth-child(-n+2) { border-bottom: 1px solid var(--line); } .body { padding: 22px 20px; } header.top, .lower, footer.foot { display: block; } .doc { text-align: left; margin-top: 18px; } .info { grid-template-columns: 1fr; } .totals { margin-top: 18px; } .qr { margin-top: 16px; text-align: left; } }
        @page { size: A4; margin: 8mm; }
        @media print {
            html, body { width: 210mm; background: #fff; }
            .toolbar { display: none; }
            .sheet { box-shadow: none; margin: 0; width: 100%; max-width: 100%; min-height: 0; border-radius: 0; }
        }
    </style>
</head>
<body>
    <div class="toolbar">
        <a href="{{ $backUrl ?? route('admin.orders.show', $order) }}">{{ $backLabel ?? 'Back to order' }}</a>
        <button type="button" class="primary" onclick="window.print()">Print / Save as PDF</button>
    </div>

    <main class="sheet">
        @if ($stamp)<div class="stamp">{{ $stamp }}</div>@endif

        <div class="hero">
            <header class="top">
                <div class="brand">
                    @if ($logo)<span class="logo"><img src="{{ $logo }}" alt="{{ $store }}"></span>@else<div class="name">{{ $store }}</div>@endif
                    @if (Setting::get('address'))<p>{{ Setting::get('address') }}</p>@endif
                    <p>
                        @if (Setting::get('phone')){{ Setting::get('phone') }}@endif
                        @if (Setting::get('phone') && Setting::get('support_email')) · @endif
                        @if (Setting::get('support_email')){{ Setting::get('support_email') }}@endif
                    </p>
                </div>
                <div class="doc">
                    <h1>INVOICE</h1>
                    <span class="no">{{ $order->number }}</span>
                </div>
            </header>
        </div>

        <section class="meta">
            <div><small>Date</small><b>{{ $placed->format('d M Y') }}</b></div>
            <div><small>Time</small><b>{{ $placed->format('h:i A') }}</b></div>
            <div><small>Order status</small><b>{{ $order->status_label }}</b></div>
            <div><small>Payment</small><b><span class="pill {{ $paid ? 'paid' : 'unpaid' }}">{{ $paid ? 'Paid' : 'Unpaid' }}</span></b></div>
        </section>

        <div class="body">
            <section class="info">
                <div class="card">
                    <h3>Billed to</h3>
                    <p class="strong">{{ $order->name }}</p>
                    <p>{{ $order->phone }}</p>
                    @if ($order->email)<p>{{ $order->email }}</p>@endif
                </div>
                <div class="card">
                    <h3>Ship to</h3>
                    <p>{{ $order->address }}</p>
                    <p>{{ $order->area }}, {{ $order->district }}</p>
                    <p>{{ $order->division }}, Bangladesh</p>
                </div>
                <div class="card">
                    <h3>Order details</h3>
                    <p><span class="k">Method:</span> {{ $order->payment_label }}</p>
                    <p><span class="k">Delivery:</span> {{ $order->zone_label }}</p>
                    @if ($order->delivery_time)<p><span class="k">Slot:</span> {{ $order->delivery_time }}</p>@endif
                    @if ($order->courier_label)<p><span class="k">Courier:</span> {{ $order->courier_label }}@if ($order->tracking_id) · {{ $order->tracking_id }}@endif</p>@endif
                </div>
            </section>

            <table class="items">
                <thead>
                    <tr>
                        <th style="width:40px">#</th>
                        <th>Item</th>
                        <th class="r" style="width:110px">Unit price</th>
                        <th class="r" style="width:56px">Qty</th>
                        <th class="r" style="width:120px">Amount</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($order->items as $item)
                        <tr>
                            <td><span class="idx">{{ $loop->iteration }}</span></td>
                            <td>
                                <div class="item-name">{{ $item->name }}</div>
                                <div class="item-meta">
                                    @if ($item->variant_label){{ $item->variant_label }}@endif
                                    @if ($item->variant_label && $item->sku) · @endif
                                    @if ($item->sku)SKU {{ $item->sku }}@endif
                                </div>
                            </td>
                            <td class="r">{{ $money($item->unit_price) }}</td>
                            <td class="r">{{ $item->quantity }}</td>
                            <td class="r"><b>{{ $money($item->line_total) }}</b></td>
                        </tr>
                    @endforeach
                </tbody>
            </table>

            <section class="lower">
                <div class="notes">
                    @if ($order->note)
                        <h3>Customer note</h3>
                        <p>{{ $order->note }}</p>
                    @endif
                    <h3>Payment terms</h3>
                    <p>
                        @if ($paid)
                            Payment received in full. Thank you.
                        @else
                            Please pay {{ $money($order->total) }} in cash to the delivery person upon receiving your order.
                        @endif
                    </p>
                </div>
                <div>
                    <div class="totals">
                        <div class="row"><span>Subtotal ({{ $qtyTotal }} {{ \Illuminate\Support\Str::plural('item', $qtyTotal) }})</span><span>{{ $money($order->subtotal) }}</span></div>
                        @if ($order->discount > 0)
                            <div class="row"><span>Discount{{ $order->coupon_code ? ' ('.$order->coupon_code.')' : '' }}</span><span>-{{ $money($order->discount) }}</span></div>
                        @elseif ($order->coupon_code)
                            <div class="row"><span>Coupon ({{ $order->coupon_code }})</span><span>Free delivery</span></div>
                        @endif
                        <div class="row"><span>Delivery ({{ $order->zone_label }})</span><span>{{ $order->delivery_fee > 0 ? $money($order->delivery_fee) : 'Free' }}</span></div>
                        <div class="row grand"><span>Total</span><span>{{ $money($order->total) }}</span></div>
                    </div>
                    <div class="due"><span>{{ $paid ? 'Amount paid' : 'Amount due' }}</span><span>{{ $money($order->total) }}</span></div>
                </div>
            </section>

            <footer class="foot">
                <div>
                    <div class="thanks">Thank you for shopping with {{ $store }}!</div>
                    @if (Setting::get('return_days'))<div>Returns accepted within {{ Setting::get('return_days') }} days of delivery.</div>@endif
                    <div>This is a computer-generated invoice and needs no signature.</div>
                    <div>Printed {{ now()->timezone($tz)->format('d M Y, h:i A') }}</div>
                </div>
                <div class="qr">
                    <div><small><b style="color:var(--ink)">Track your order</b></small><small>Scan to view status</small></div>
                    <img src="{{ $qr }}" alt="QR" onerror="this.parentNode.style.display='none'">
                </div>
            </footer>
        </div>
        <div class="bar"></div>
    </main>

    @if (request()->boolean('print'))
        <script>window.addEventListener('load', function () { setTimeout(function () { window.print(); }, 300); });</script>
    @endif
</body>
</html>
