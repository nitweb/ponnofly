@php
    use App\Models\Setting;

    $tz = Setting::tz();
    $money = fn ($n) => '৳'.number_format((float) $n, 2);
    $store = Setting::get('store_name') ?: config('app.name');
    $logo = Setting::get('logo_header') ? asset(Setting::get('logo_header')) : null;
    $accent = preg_match('/^#[0-9a-fA-F]{6}$/', (string) Setting::get('color_primary')) ? Setting::get('color_primary') : '#0B3A8C';
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
    <style>
        :root { --accent: {{ $accent }}; --ink: #1a2238; --muted: #6b7388; --line: #e4e8f1; --soft: #f5f7fc; }
        * { box-sizing: border-box; }
        html, body { margin: 0; padding: 0; }
        body { font-family: Inter, 'Segoe UI', 'Noto Sans Bengali', 'Nirmala UI', Arial, sans-serif; color: var(--ink); background: #e9edf5; font-size: 13px; line-height: 1.5; -webkit-print-color-adjust: exact; print-color-adjust: exact; }
        .toolbar { max-width: 794px; margin: 18px auto 0; display: flex; justify-content: flex-end; gap: 10px; padding: 0 8px; }
        .toolbar button, .toolbar a { font: inherit; font-weight: 600; padding: 9px 18px; border-radius: 10px; border: 1px solid var(--line); background: #fff; color: var(--ink); cursor: pointer; text-decoration: none; }
        .toolbar .primary { background: var(--accent); border-color: var(--accent); color: #fff; }
        .sheet { position: relative; width: 794px; max-width: 100%; min-height: 1000px; margin: 14px auto 28px; background: #fff; padding: 44px 48px 36px; box-shadow: 0 10px 40px -12px rgb(20 30 70 / .25); border-top: 6px solid var(--accent); overflow: hidden; }
        header.top { display: flex; justify-content: space-between; align-items: flex-start; gap: 24px; }
        .brand img { max-height: 54px; max-width: 220px; display: block; margin-bottom: 8px; }
        .brand .name { font-size: 22px; font-weight: 800; letter-spacing: -.01em; }
        .brand p { margin: 2px 0 0; color: var(--muted); max-width: 300px; }
        .doc { text-align: right; }
        .doc h1 { margin: 0; font-size: 30px; letter-spacing: .12em; font-weight: 800; color: var(--accent); }
        .doc dl { margin: 10px 0 0; display: grid; grid-template-columns: auto auto; gap: 3px 14px; justify-content: end; }
        .doc dt { color: var(--muted); text-align: right; }
        .doc dd { margin: 0; font-weight: 700; text-align: right; }
        .pill { display: inline-block; padding: 2px 12px; border-radius: 999px; font-size: 11px; font-weight: 800; letter-spacing: .06em; text-transform: uppercase; }
        .pill.paid { background: #dcf5e8; color: #0f7a46; }
        .pill.unpaid { background: #fff0d1; color: #9a5b00; }
        .info { display: grid; grid-template-columns: repeat(3, 1fr); gap: 18px; margin-top: 34px; }
        .info h3 { margin: 0 0 6px; font-size: 10.5px; letter-spacing: .12em; text-transform: uppercase; color: var(--muted); }
        .info p { margin: 0; }
        .info .strong { font-weight: 700; font-size: 14px; }
        table.items { width: 100%; border-collapse: collapse; margin-top: 30px; }
        table.items thead th { background: var(--accent); color: #fff; font-size: 10.5px; letter-spacing: .1em; text-transform: uppercase; text-align: left; padding: 10px 12px; }
        table.items thead th:first-child { border-radius: 8px 0 0 8px; }
        table.items thead th:last-child { border-radius: 0 8px 8px 0; }
        table.items th.r, table.items td.r { text-align: right; }
        table.items td { padding: 12px; border-bottom: 1px solid var(--line); vertical-align: top; }
        table.items tr { page-break-inside: avoid; }
        .item-name { font-weight: 600; }
        .item-meta { color: var(--muted); font-size: 11.5px; margin-top: 2px; }
        .lower { display: grid; grid-template-columns: 1fr 300px; gap: 28px; margin-top: 22px; align-items: start; }
        .notes h3 { margin: 0 0 6px; font-size: 10.5px; letter-spacing: .12em; text-transform: uppercase; color: var(--muted); }
        .notes p { margin: 0 0 14px; color: var(--ink); }
        .totals { background: var(--soft); border-radius: 12px; padding: 14px 16px; }
        .totals .row { display: flex; justify-content: space-between; padding: 5px 0; }
        .totals .row span:first-child { color: var(--muted); }
        .totals .grand { margin-top: 8px; padding-top: 12px; border-top: 2px solid var(--accent); font-size: 17px; font-weight: 800; }
        .totals .grand span:first-child { color: var(--ink); }
        .totals .grand span:last-child { color: var(--accent); }
        .due { display: flex; justify-content: space-between; margin-top: 8px; padding: 10px 12px; border-radius: 8px; font-weight: 700; background: {{ $paid ? '#dcf5e8' : '#fff0d1' }}; color: {{ $paid ? '#0f7a46' : '#9a5b00' }}; }
        footer.foot { margin-top: 44px; padding-top: 16px; border-top: 1px solid var(--line); display: flex; justify-content: space-between; gap: 20px; color: var(--muted); font-size: 11.5px; }
        footer.foot .thanks { color: var(--ink); font-weight: 700; font-size: 13px; margin-bottom: 2px; }
        .stamp { position: absolute; top: 300px; left: 50%; transform: translateX(-50%) rotate(-18deg); font-size: 64px; font-weight: 900; letter-spacing: .1em; text-transform: uppercase; color: rgb(200 40 40 / .12); border: 8px solid rgb(200 40 40 / .12); padding: 4px 28px; border-radius: 14px; pointer-events: none; }
        @media (max-width: 640px) { .sheet { padding: 28px 20px; } header.top, .lower { display: block; } .doc { text-align: left; margin-top: 18px; } .doc dl { justify-content: start; } .info { grid-template-columns: 1fr; } .totals { margin-top: 18px; } }
        @page { size: A4; margin: 10mm; }
        @media print {
            body { background: #fff; }
            .toolbar { display: none; }
            .sheet { box-shadow: none; margin: 0; width: auto; min-height: 0; padding: 0; border-top-width: 5px; padding-top: 24px; }
        }
    </style>
</head>
<body>
    <div class="toolbar">
        <a href="{{ route('admin.orders.show', $order) }}">Back to order</a>
        <button type="button" class="primary" onclick="window.print()">Print / Save as PDF</button>
    </div>

    <main class="sheet">
        @if ($stamp)<div class="stamp">{{ $stamp }}</div>@endif

        <header class="top">
            <div class="brand">
                @if ($logo)<img src="{{ $logo }}" alt="{{ $store }}">@else<div class="name">{{ $store }}</div>@endif
                @if (Setting::get('address'))<p>{{ Setting::get('address') }}</p>@endif
                <p>
                    @if (Setting::get('phone')){{ Setting::get('phone') }}@endif
                    @if (Setting::get('phone') && Setting::get('support_email')) · @endif
                    @if (Setting::get('support_email')){{ Setting::get('support_email') }}@endif
                </p>
            </div>
            <div class="doc">
                <h1>INVOICE</h1>
                <dl>
                    <dt>Invoice no.</dt><dd>{{ $order->number }}</dd>
                    <dt>Date</dt><dd>{{ $placed->format('d M Y') }}</dd>
                    <dt>Time</dt><dd>{{ $placed->format('h:i A') }}</dd>
                    <dt>Order status</dt><dd>{{ $order->status_label }}</dd>
                    <dt>Payment</dt><dd><span class="pill {{ $paid ? 'paid' : 'unpaid' }}">{{ $paid ? 'Paid' : 'Unpaid' }}</span></dd>
                </dl>
            </div>
        </header>

        <section class="info">
            <div>
                <h3>Billed to</h3>
                <p class="strong">{{ $order->name }}</p>
                <p>{{ $order->phone }}</p>
                @if ($order->email)<p>{{ $order->email }}</p>@endif
            </div>
            <div>
                <h3>Ship to</h3>
                <p>{{ $order->address }}</p>
                <p>{{ $order->area }}, {{ $order->district }}</p>
                <p>{{ $order->division }}, Bangladesh</p>
            </div>
            <div>
                <h3>Order details</h3>
                <p><span style="color:var(--muted)">Method:</span> {{ $order->payment_label }}</p>
                <p><span style="color:var(--muted)">Delivery:</span> {{ $order->zone_label }}</p>
                @if ($order->delivery_time)<p><span style="color:var(--muted)">Slot:</span> {{ $order->delivery_time }}</p>@endif
                @if ($order->courier_label)<p><span style="color:var(--muted)">Courier:</span> {{ $order->courier_label }}@if ($order->tracking_id) · {{ $order->tracking_id }}@endif</p>@endif
            </div>
        </section>

        <table class="items">
            <thead>
                <tr>
                    <th style="width:36px">#</th>
                    <th>Item</th>
                    <th class="r" style="width:110px">Unit price</th>
                    <th class="r" style="width:56px">Qty</th>
                    <th class="r" style="width:120px">Amount</th>
                </tr>
            </thead>
            <tbody>
                @foreach ($order->items as $item)
                    <tr>
                        <td>{{ $loop->iteration }}</td>
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
            </div>
            <div style="text-align:right">
                <div>This is a computer-generated invoice and needs no signature.</div>
                <div>Printed {{ now()->timezone($tz)->format('d M Y, h:i A') }}</div>
            </div>
        </footer>
    </main>

    @if (request()->boolean('print'))
        <script>window.addEventListener('load', function () { setTimeout(function () { window.print(); }, 300); });</script>
    @endif
</body>
</html>
