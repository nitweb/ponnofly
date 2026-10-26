@extends('admin.dashboard.dashboard')
@section('admin_title', 'Order #'.$order->number.' · Nebula Mart Admin')
@section('admin_contents')

    @php
        $badges = ['pending' => 'b-warn', 'processing' => 'b-info', 'shipped' => 'b-purple', 'out_for_delivery' => 'b-info', 'delivered' => 'b-success', 'cancelled' => 'b-danger', 'returned' => 'b-muted'];
        $events = $order->events;
        $lastEvent = $events->last();
    @endphp

    <div class="flex flex-wrap items-end justify-between gap-4">
        <div>
            <h1 class="font-display font-extrabold text-2xl sm:text-[28px] tracking-tight">Order #{{ $order->number }}</h1>
            <p class="text-ink/55 mt-1">Placed on {{ $order->created_at->timezone(\App\Models\Setting::tz())->format('d M Y') }} · <span class="badge {{ $badges[$order->status] ?? 'b-muted' }}">{{ $order->status_label }}</span></p>
        </div>
        <div class="flex flex-wrap gap-3">
            <a href="{{ route('admin.orders.invoice', $order) }}" target="_blank" rel="noopener" class="btn btn-ghost"><svg class="ic ic-sm"><use href="#i-eye" /></svg>View invoice</a>
            <a href="{{ route('admin.orders.index') }}" class="btn btn-ghost"><svg class="ic ic-sm"><use href="#i-chev-left" /></svg>Back</a>
        </div>
    </div>

    <div class="grid xl:grid-cols-3 gap-6 items-start" id="invoice">
        <div class="xl:col-span-2 space-y-6">

            <section class="card">
                <div class="card-h"><h3>Order items</h3></div>
                <div>
                    <div class="overflow-x-auto">
                        <table class="tbl">
                            <thead>
                                <tr>
                                    <th>Product</th>
                                    <th>Price</th>
                                    <th>Qty</th>
                                    <th class="text-right">Total</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($order->items as $item)
                                    <tr>
                                        <td>
                                            <div class="flex items-center gap-3">
                                                <img class="thumb" src="{{ $item->image ?: asset('assets/admin/img/no-image.svg') }}" alt="">
                                                <div>
                                                    <p class="font-bold">{{ $item->name }}</p>
                                                    <p class="text-xs text-ink/50">{{ collect([$item->variant_label, $item->sku ? 'SKU '.$item->sku : null])->filter()->implode(' · ') }}</p>
                                                </div>
                                            </div>
                                        </td>
                                        <td>৳{{ number_format($item->unit_price) }}</td>
                                        <td>×{{ $item->quantity }}</td>
                                        <td class="text-right font-bold">৳{{ number_format($item->line_total) }}</td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                    <div class="p-6 border-t border-line ml-auto max-w-sm space-y-2.5">
                        <p class="flex justify-between"><span class="text-ink/60">Subtotal</span><b>৳{{ number_format($order->subtotal) }}</b></p>
                        @if ($order->coupon_code)
                            <p class="flex justify-between"><span class="text-ink/60">Coupon ({{ $order->coupon_code }})</span><b class="text-success">{{ $order->discount > 0 ? '-৳'.number_format($order->discount) : 'Free delivery' }}</b></p>
                        @endif
                        <p class="flex justify-between"><span class="text-ink/60">Delivery ({{ $order->zone_label }})</span><b>{{ $order->delivery_fee > 0 ? '৳'.number_format($order->delivery_fee) : 'Free' }}</b></p>
                        <p class="flex justify-between text-lg pt-3 border-t border-line"><b>Total (COD)</b><b class="font-display font-extrabold text-primary">৳{{ number_format($order->total) }}</b></p>
                    </div>
                </div>
            </section>

            <section class="card">
                <div class="card-h"><h3>Order timeline</h3></div>
                <div class="card-b">
                    <ul class="timeline">
                        @foreach ($events as $event)
                            @php $isNow = $event->is($lastEvent) && ! in_array($event->status, ['delivered', 'cancelled', 'returned'], true); @endphp
                            <li class="{{ $isNow ? 'now' : 'done' }}">
                                <p class="font-bold">{{ $event->note ?: \App\Models\Order::STATUSES[$event->status] ?? ucfirst($event->status) }}</p>
                                <p class="text-xs text-ink/50">{{ $event->created_at->timezone(\App\Models\Setting::tz())->format('d M Y, h:i A') }}</p>
                            </li>
                        @endforeach
                    </ul>
                </div>
            </section>

            @if ($order->returns->isNotEmpty())
                <section class="card">
                    <div class="card-h"><h3>Return &amp; cancel requests</h3></div>
                    <div class="overflow-x-auto">
                        <table class="tbl">
                            <thead>
                                <tr>
                                    <th>Request</th>
                                    <th>Items</th>
                                    <th>Reason</th>
                                    <th>Status</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($order->returns as $return)
                                    <tr>
                                        <td><a href="{{ route('admin.return-requests.show', $return) }}" class="font-bold text-primary hover:underline">{{ $return->number }}</a><p class="text-xs text-ink/50">{{ $return->type_label }}</p></td>
                                        <td>{{ $return->items->map(fn ($line) => ($line->orderItem?->name ?? 'Item').' ×'.$line->quantity)->implode(', ') }}</td>
                                        <td>{{ $return->reason_label }}</td>
                                        <td><span class="badge {{ ['pending' => 'b-warn', 'approved' => 'b-success', 'rejected' => 'b-danger'][$return->status] ?? 'b-muted' }}">{{ $return->status_label }}</span></td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </section>
            @endif

            <section class="card">
                <div class="card-h"><h3>Internal note</h3></div>
                <div class="card-b">
                    <form method="POST" action="{{ route('admin.orders.note', $order) }}">
                        @csrf
                        @method('PATCH')
                        <textarea class="textarea" name="admin_note" rows="3" maxlength="1000" placeholder="Add a private note for your team…">{{ old('admin_note', $order->admin_note) }}</textarea>
                        @error('admin_note')<p class="text-danger text-xs mt-1">{{ $message }}</p>@enderror
                        <button class="btn btn-ghost btn-sm mt-3" type="submit">Save note</button>
                    </form>
                </div>
            </section>

        </div>

        <div class="space-y-6">

            <section class="card">
                <div class="card-h"><h3>Update order</h3></div>
                <div class="card-b">
                    @if ($order->isFinal())
                        <p class="text-sm text-ink/60">This order is {{ strtolower($order->status_label) }}, so it can no longer be changed.</p>
                    @else
                        <form method="POST" action="{{ route('admin.orders.update', $order) }}" class="space-y-5">
                            @csrf
                            @method('PATCH')
                            <div>
                                <label class="lbl" for="st">Order status</label>
                                <select class="select" id="st" name="status">
                                    @foreach (\Illuminate\Support\Arr::except(\App\Models\Order::STATUSES, ['returned']) as $key => $label)
                                        <option value="{{ $key }}" @selected(old('status', $order->status) === $key)>{{ $label }}</option>
                                    @endforeach
                                </select>
                                @error('status')<p class="text-danger text-xs mt-1">{{ $message }}</p>@enderror
                            </div>
                            <div>
                                <label class="lbl" for="courier">Courier</label>
                                <select class="select" id="courier" name="courier">
                                    <option value="">— Not assigned —</option>
                                    @foreach (\App\Models\Setting::COURIERS as $key => $label)
                                        <option value="{{ $key }}" @selected(old('courier', $order->courier) === $key)>{{ $label }}</option>
                                    @endforeach
                                </select>
                                @error('courier')<p class="text-danger text-xs mt-1">{{ $message }}</p>@enderror
                            </div>
                            <div>
                                <label class="lbl" for="trk">Consignment / tracking ID</label>
                                <input class="input" id="trk" name="tracking_id" type="text" maxlength="80" value="{{ old('tracking_id', $order->tracking_id) }}">
                                @error('tracking_id')<p class="text-danger text-xs mt-1">{{ $message }}</p>@enderror
                            </div>
                            <button class="btn btn-primary w-full" type="submit" id="update-order-btn">Update order</button>
                        </form>
                    @endif
                </div>
            </section>

            <section class="card">
                <div class="card-h"><h3>Customer</h3></div>
                <div class="card-b">
                    <div class="flex items-center gap-3 mb-4">
                        <span class="w-12 h-12 rounded-2xl bg-primary text-white grid place-items-center font-display font-extrabold">{{ strtoupper(mb_substr($order->name, 0, 1)) }}</span>
                        <div>
                            <p class="font-bold">{{ $order->name }}</p>
                            <p class="text-xs text-ink/50">{{ $customerOrders }} {{ $customerOrders === 1 ? 'order' : 'orders' }}</p>
                        </div>
                    </div>
                    <ul class="space-y-3 text-sm">
                        <li class="flex gap-3"><svg class="ic ic-sm text-ink/40"><use href="#i-mail" /></svg>{{ $order->email }}</li>
                        <li class="flex gap-3"><svg class="ic ic-sm text-ink/40"><use href="#i-phone" /></svg>{{ $order->phone }}</li>
                    </ul>
                </div>
            </section>

            <section class="card">
                <div class="card-h"><h3>Shipping address</h3></div>
                <div class="card-b">
                    <p class="flex gap-3"><svg class="ic text-primary"><use href="#i-pin" /></svg><span>{{ $order->address }}<br>{{ $order->area }}, {{ $order->district }}<br>{{ $order->division }}, Bangladesh</span></p>
                    @if ($order->note)
                        <p class="text-sm text-ink/60 mt-4"><b>Customer note:</b> {{ $order->note }}</p>
                    @endif
                </div>
            </section>

            <section class="card">
                <div class="card-h"><h3>Payment</h3></div>
                <div class="card-b">
                    <p class="flex items-center justify-between">
                        <span class="flex items-center gap-2 font-semibold"><svg class="ic text-primary"><use href="#i-wallet" /></svg>{{ $order->payment_label }}</span>
                        <span class="badge {{ $order->payment_status === 'paid' ? 'b-success' : 'b-warn' }}">{{ $order->payment_status === 'paid' ? 'Paid' : 'Unpaid' }}</span>
                    </p>
                    @if ($order->payment_status !== 'paid' && ! $order->isFinal())
                        <p class="text-xs text-ink/50 mt-3">Collect ৳{{ number_format($order->total) }} from the customer on delivery.</p>
                    @endif
                </div>
            </section>

        </div>
    </div>


@endsection
