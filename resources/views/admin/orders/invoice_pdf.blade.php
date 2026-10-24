@php
    use App\Models\Setting;

    $tz = Setting::tz();
    $money = fn ($n) => '<span class="tk">৳</span>'.number_format((float) $n, 2);
    $store = Setting::get('store_name') ?: config('app.name');
    $logoSetting = Setting::get('logo_header');
    $logoPath = $logoSetting ? public_path(ltrim($logoSetting, '/')) : null;
    $logo = $logoPath && is_file($logoPath) ? $logoPath : null;
    $hexOr = fn ($v, $d) => preg_match('/^#[0-9a-fA-F]{6}$/', (string) $v) ? $v : $d;
    $accent = $hexOr(Setting::get('color_primary'), '#0B3A8C');
    $gold = $hexOr(Setting::get('color_accent'), '#F5A800');
    $ink = $hexOr(Setting::get('color_text'), '#1a2238');
    $mixHex = function (string $from, string $to, float $amount): string {
        $a = sscanf($from, '#%02x%02x%02x');
        $b = sscanf($to, '#%02x%02x%02x');

        return sprintf('#%02x%02x%02x', ...array_map(fn ($x, $y) => (int) round($x + ($y - $x) * $amount), $a, $b));
    };
    $accentDark = $mixHex($accent, '#000000', 0.22);
    $accentTint = $mixHex($accent, '#ffffff', 0.90);
    $placed = $order->created_at->timezone($tz);
    $paid = $order->payment_status === 'paid';
    $stamp = match ($order->status) { 'cancelled' => 'CANCELLED', 'returned' => 'RETURNED', default => null };
    $qtyTotal = (int) $order->items->sum('quantity');
    $trackUrl = route('track.show', ['order' => $order->number]);
    $qr = 'https://api.qrserver.com/v1/create-qr-code/?size=240x240&margin=0&format=png&data='.urlencode($trackUrl);
    $dueBg = $paid ? '#dcf5e8' : '#fff0d1';
    $dueFg = $paid ? '#0f7a46' : '#9a5b00';
@endphp
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <title>Invoice {{ $order->number }}</title>
    <style>
        @font-face { font-family: 'Inter'; font-weight: normal; src: url('{{ $fontPath }}/Inter-Regular.ttf') format('truetype'); }
        @font-face { font-family: 'Inter'; font-weight: bold; src: url('{{ $fontPath }}/Inter-Bold.ttf') format('truetype'); }
        @font-face { font-family: 'Jakarta'; font-weight: normal; src: url('{{ $fontPath }}/Jakarta-ExtraBold.ttf') format('truetype'); }
        @font-face { font-family: 'Jakarta'; font-weight: bold; src: url('{{ $fontPath }}/Jakarta-ExtraBold.ttf') format('truetype'); }
        @font-face { font-family: 'NotoBn'; font-weight: normal; src: url('{{ $fontPath }}/NotoBengali-Regular.ttf') format('truetype'); }
        @font-face { font-family: 'NotoBn'; font-weight: bold; src: url('{{ $fontPath }}/NotoBengali-Bold.ttf') format('truetype'); }

        @page { margin: 8mm; }
        body { margin: 0; font-family: 'Inter', sans-serif; font-size: 12px; line-height: 1.3; color: {{ $ink }}; }
        table { border-collapse: collapse; }
        .tk { font-family: 'NotoBn', 'Inter', sans-serif; }
        .muted { color: #6b7388; }
        .disp { font-family: 'Jakarta', 'Inter', sans-serif; font-weight: bold; }
        .eyebrow { font-size: 9px; letter-spacing: 1.2px; text-transform: uppercase; color: #6b7388; font-weight: bold; }

        .sheet { height: 1040px; overflow: hidden; }
        .hero { height: 150px; padding: 30px 48px 0 48px; color: #fff; background-color: {{ $accent }}; @if ($heroImage) background-image: url('{{ $heroImage }}'); background-size: 100% 100%; background-repeat: no-repeat; @endif }
        .hero td { vertical-align: top; }
        .store { font-size: 22px; line-height: 1.2; }
        .hero .sub { font-size: 11px; color: #e3e9f6; margin-top: 1px; }
        .inv { font-size: 30px; line-height: 1; letter-spacing: 5.4px; text-align: right; }
        .no { display: inline-block; background-color: {{ $gold }}; color: #1a1a1a; font-weight: bold; font-size: 11.5px; padding: 4px 13px; border-radius: 13px; }
        .logo { display: inline-block; background-color: #fff; border-radius: 14px; padding: 8px 14px; }

        .metacard { margin: -34px 48px 0 48px; background-color: #fff; border: 1px solid #e6eaf3; border-radius: 16px; }
        .meta { width: 100%; }
        .meta td { width: 25%; padding: 10px 16px; border-right: 1px solid #e6eaf3; vertical-align: top; }
        .meta td.last { border-right: 0; }
        .meta .val { margin-top: 2px; font-size: 12.5px; font-weight: bold; }
        .pill { display: inline-block; padding: 2px 10px; border-radius: 9px; font-size: 10px; font-weight: bold; letter-spacing: .66px; text-transform: uppercase; background-color: {{ $dueBg }}; color: {{ $dueFg }}; }

        .body { padding: 18px 48px 0 48px; }
        .infowrap { margin: 0 -14px; }
        .info { width: 100%; border-collapse: separate; border-spacing: 14px 0; }
        .info td { width: 33.33%; vertical-align: top; background-color: #f5f7fc; border: 1px solid #e6eaf3; border-radius: 12px; padding: 10px 14px; }
        .info .h { font-size: 9.5px; letter-spacing: 1.1px; text-transform: uppercase; color: {{ $accent }}; font-weight: bold; margin-bottom: 5px; }
        .strong { font-size: 13px; }

        .itemshead { width: 637px; margin-top: 18px; background-color: {{ $accent }}; border-radius: 10px; }
        .items { width: 637px; border-collapse: separate; border-spacing: 0; }
        .items th { color: #fff; font-size: 9.5px; letter-spacing: .9px; text-transform: uppercase; text-align: left; padding: 9px 10px; }
        .items th.r, .items td.r { text-align: right; }
        .items td { padding: 9px 10px; border-bottom: 1px solid #e6eaf3; vertical-align: top; }
        .idx { width: 24px; height: 19px; padding-top: 5px; line-height: 13px; text-align: center; border-radius: 8px; background-color: {{ $accentTint }}; color: {{ $accent }}; font-weight: bold; font-size: 11px; }
        .item-meta { color: #6b7388; font-size: 10.5px; margin-top: 1px; }

        .lower { width: 637px; margin-top: 16px; table-layout: fixed; }
        .lower td { vertical-align: top; }
        .notes .eyebrow { margin-bottom: 6px; }
        .notes p { margin: 0 0 10px 0; }
        .totals { background-color: #f5f7fc; border: 1px solid #e6eaf3; border-radius: 14px; padding: 8px 14px; }
        .totals table { width: 100%; }
        .totals td { padding: 3px 0; }
        .totals td.r { text-align: right; }
        .totals tr.grand td { padding-top: 8px; border-top: 2px dashed #e6eaf3; font-size: 15px; }
        .totals tr.grand td.r { color: {{ $accent }}; }
        .due { margin-top: 8px; padding: 9px 14px; border-radius: 12px; background-color: {{ $dueBg }}; color: {{ $dueFg }}; font-size: 12px; }
        .due table { width: 100%; }
        .due td.r { text-align: right; }

        .foot { margin-top: 16px; border-top: 1px solid #e6eaf3; padding-top: 12px; width: 637px; }
        .foot td { vertical-align: middle; color: #6b7388; font-size: 10.5px; }
        .thanks { color: {{ $ink }}; font-size: 13px; margin-bottom: 2px; }
        .qrimg { width: 58px; height: 58px; border: 1px solid #e6eaf3; border-radius: 10px; padding: 4px; background-color: #fff; }
        .bar { margin-top: 14px; height: 6px; background-color: {{ $accent }}; @if ($barImage) background-image: url('{{ $barImage }}'); background-size: 100% 100%; background-repeat: no-repeat; @endif }
        .stamp { display: inline-block; margin-top: 8px; color: #ffd9d9; font-size: 12px; font-weight: bold; letter-spacing: 2px; border: 2px solid #ffd9d9; padding: 2px 10px; border-radius: 6px; }
    </style>
</head>
<body>
<div class="sheet">
    <div class="hero">
        <table width="100%">
            <tr>
                <td width="55%">
                    @if ($logo)
                        <span class="logo"><img src="{{ $logo }}" style="max-height:44px;max-width:200px;" alt="{{ $store }}"></span>
                    @else
                        <div class="store disp">{{ $store }}</div>
                    @endif
                    @if (Setting::get('address'))<div class="sub" style="margin-top:8px;max-width:300px;">{{ Setting::get('address') }}</div>@endif
                    <div class="sub">
                        @if (Setting::get('phone')){{ Setting::get('phone') }}@endif
                        @if (Setting::get('phone') && Setting::get('support_email')) &middot; @endif
                        @if (Setting::get('support_email')){{ Setting::get('support_email') }}@endif
                    </div>
                </td>
                <td width="45%" align="right">
                    <div class="inv disp">INVOICE</div>
                    <div style="margin-top:10px;"><span class="no">{{ $order->number }}</span></div>
                    @if ($stamp)<div><span class="stamp">{{ $stamp }}</span></div>@endif
                </td>
            </tr>
        </table>
    </div>

    <div class="metacard">
        <table class="meta">
            <tr>
                <td><div class="eyebrow">Date</div><div class="val disp">{{ $placed->format('d M Y') }}</div></td>
                <td><div class="eyebrow">Time</div><div class="val disp">{{ $placed->format('h:i A') }}</div></td>
                <td><div class="eyebrow">Order status</div><div class="val disp">{{ $order->status_label }}</div></td>
                <td class="last"><div class="eyebrow">Payment</div><div style="margin-top:3px;"><span class="pill">{{ $paid ? 'Paid' : 'Unpaid' }}</span></div></td>
            </tr>
        </table>
    </div>

    <div class="body">
        <div class="infowrap">
            <table class="info">
                <tr>
                    <td>
                        <div class="h">Billed to</div>
                        <div class="strong disp">{{ $order->name }}</div>
                        <div>{{ $order->phone }}</div>
                        @if ($order->email)<div>{{ $order->email }}</div>@endif
                    </td>
                    <td>
                        <div class="h">Ship to</div>
                        <div>{{ $order->address }}</div>
                        <div>{{ $order->area }}, {{ $order->district }}</div>
                        <div>{{ $order->division }}, Bangladesh</div>
                    </td>
                    <td>
                        <div class="h">Order details</div>
                        <div><span class="muted">Method:</span> {{ $order->payment_label }}</div>
                        <div><span class="muted">Delivery:</span> {{ $order->zone_label }}</div>
                        @if ($order->delivery_time)<div><span class="muted">Slot:</span> {{ $order->delivery_time }}</div>@endif
                        @if ($order->courier_label)<div><span class="muted">Courier:</span> {{ $order->courier_label }}@if ($order->tracking_id) &middot; {{ $order->tracking_id }}@endif</div>@endif
                    </td>
                </tr>
            </table>
        </div>

        <div class="itemshead">
            <table class="items">
                <tr>
                    <th width="6%" style="width:6%">#</th>
                    <th width="55%" style="width:55%">Item</th>
                    <th class="r" width="16%" style="width:16%">Unit price</th>
                    <th class="r" width="7%" style="width:7%">Qty</th>
                    <th class="r" width="16%" style="width:16%">Amount</th>
                </tr>
            </table>
        </div>
        <table class="items">
            <tbody>
                @foreach ($order->items as $item)
                    <tr>
                        <td width="6%" @if ($loop->even) style="background-color:#fafbfe" @endif><div class="idx">{{ $loop->iteration }}</div></td>
                        <td width="55%" @if ($loop->even) style="background-color:#fafbfe" @endif>
                            <div style="font-weight:bold;">{{ $item->name }}</div>
                            @if ($item->variant_label || $item->sku)
                                <div class="item-meta">
                                    @if ($item->variant_label){{ $item->variant_label }}@endif
                                    @if ($item->variant_label && $item->sku) &middot; @endif
                                    @if ($item->sku)SKU {{ $item->sku }}@endif
                                </div>
                            @endif
                        </td>
                        <td class="r" width="16%" @if ($loop->even) style="background-color:#fafbfe" @endif>{!! $money($item->unit_price) !!}</td>
                        <td class="r" width="7%" @if ($loop->even) style="background-color:#fafbfe" @endif>{{ $item->quantity }}</td>
                        <td class="r" width="16%" @if ($loop->even) style="background-color:#fafbfe" @endif><b>{!! $money($item->line_total) !!}</b></td>
                    </tr>
                @endforeach
            </tbody>
        </table>

        <table class="lower">
            <tr>
                <td class="notes" style="width:337px;padding-right:24px;">
                    @if ($order->note)
                        <div class="eyebrow">Customer note</div>
                        <p>{{ $order->note }}</p>
                    @endif
                    <div class="eyebrow">Payment terms</div>
                    <p>
                        @if ($paid)
                            Payment received in full. Thank you.
                        @else
                            Please pay {!! $money($order->total) !!} in cash to the delivery person upon receiving your order.
                        @endif
                    </p>
                </td>
                <td style="width:300px;">
                    <div class="totals">
                        <table>
                            <tr><td class="muted">Subtotal ({{ $qtyTotal }} {{ \Illuminate\Support\Str::plural('item', $qtyTotal) }})</td><td class="r">{!! $money($order->subtotal) !!}</td></tr>
                            @if ($order->discount > 0)
                                <tr><td class="muted">Discount{{ $order->coupon_code ? ' ('.$order->coupon_code.')' : '' }}</td><td class="r">-{!! $money($order->discount) !!}</td></tr>
                            @elseif ($order->coupon_code)
                                <tr><td class="muted">Coupon ({{ $order->coupon_code }})</td><td class="r">Free delivery</td></tr>
                            @endif
                            <tr><td class="muted">Delivery ({{ $order->zone_label }})</td><td class="r">{!! $order->delivery_fee > 0 ? $money($order->delivery_fee) : 'Free' !!}</td></tr>
                            <tr class="grand disp"><td>Total</td><td class="r">{!! $money($order->total) !!}</td></tr>
                        </table>
                    </div>
                    <div class="due disp">
                        <table>
                            <tr><td>{{ $paid ? 'Amount paid' : 'Amount due' }}</td><td class="r">{!! $money($order->total) !!}</td></tr>
                        </table>
                    </div>
                </td>
            </tr>
        </table>

        <table class="foot">
            <tr>
                <td>
                    <div class="thanks disp">Thank you for shopping with {{ $store }}!</div>
                    @if (Setting::get('return_days'))<div>Returns accepted within {{ Setting::get('return_days') }} days of delivery.</div>@endif
                    <div>This is a computer-generated invoice and needs no signature.</div>
                    <div>Printed {{ now()->timezone($tz)->format('d M Y, h:i A') }}</div>
                </td>
                <td align="right" width="190">
                    <table align="right">
                        <tr>
                            <td style="text-align:right;padding-right:12px;font-size:10px;"><b style="color:{{ $ink }}">Track your order</b><br>Scan to view status</td>
                            <td><img class="qrimg" src="{{ $qr }}" alt=""></td>
                        </tr>
                    </table>
                </td>
            </tr>
        </table>
    </div>
    <div class="bar"></div>
</div>
</body>
</html>
