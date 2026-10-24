@extends('admin.dashboard.dashboard')
@section('admin_title', $return->number.' · Nebula Mart Admin')
@section('admin_contents')

    @php
        $badges = ['pending' => 'b-warn', 'approved' => 'b-success', 'rejected' => 'b-danger'];
        $order = $return->order;
    @endphp

    <div class="flex flex-wrap items-end justify-between gap-4">
        <div>
            <h1 class="font-display font-extrabold text-2xl sm:text-[28px] tracking-tight">{{ $return->type_label }} request {{ $return->number }}</h1>
            <p class="text-ink/55 mt-1">Sent on {{ $return->created_at->timezone(\App\Models\Setting::tz())->format('d M Y, h:i A') }} · <span class="badge {{ $badges[$return->status] ?? 'b-muted' }}">{{ $return->status_label }}</span></p>
        </div>
        <div class="flex flex-wrap gap-3">
            <a href="{{ route('admin.orders.show', $order) }}" class="btn btn-ghost">View order #{{ $order->number }}</a>
            <a href="{{ route('admin.return-requests.index') }}" class="btn btn-ghost"><svg class="ic ic-sm"><use href="#i-chev-left" /></svg>Back</a>
        </div>
    </div>

    <div class="grid xl:grid-cols-3 gap-6 items-start">
        <div class="xl:col-span-2 space-y-6">
            <section class="card">
                <div class="card-h"><h3>Requested items</h3></div>
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
                            @foreach ($return->items as $line)
                                @if ($line->orderItem)
                                    <tr>
                                        <td>
                                            <div class="flex items-center gap-3">
                                                <img class="thumb" src="{{ $line->orderItem->image ?: asset('assets/admin/img/no-image.svg') }}" alt="">
                                                <div>
                                                    <p class="font-bold">{{ $line->orderItem->name }}</p>
                                                    <p class="text-xs text-ink/50">{{ collect([$line->orderItem->variant_label, $line->orderItem->sku ? 'SKU '.$line->orderItem->sku : null])->filter()->implode(' · ') }}</p>
                                                </div>
                                            </div>
                                        </td>
                                        <td>৳{{ number_format($line->orderItem->unit_price) }}</td>
                                        <td>×{{ $line->quantity }} <span class="text-xs text-ink/50">of {{ $line->orderItem->quantity }}</span></td>
                                        <td class="text-right font-bold">৳{{ number_format($line->orderItem->unit_price * $line->quantity) }}</td>
                                    </tr>
                                @endif
                            @endforeach
                        </tbody>
                    </table>
                </div>
                <div class="p-6 border-t border-line ml-auto max-w-sm">
                    <p class="flex justify-between text-lg"><b>Request value</b><b class="font-display font-extrabold text-primary">৳{{ number_format($return->amount) }}</b></p>
                </div>
            </section>

            <section class="card">
                <div class="card-h"><h3>Customer's reason</h3></div>
                <div class="card-b">
                    <p class="font-bold">{{ $return->reason_label }}</p>
                    @if ($return->details)
                        <p class="text-ink/70 mt-2">{{ $return->details }}</p>
                    @endif
                </div>
            </section>
        </div>

        <div class="space-y-6">
            <section class="card">
                <div class="card-h"><h3>Decision</h3></div>
                <div class="card-b">
                    @if ($return->isPending())
                        @if ($order->isFinal())
                            <p class="text-sm text-ink/60">This order is {{ strtolower($order->status_label) }}, so the request can no longer be approved.</p>
                        @else
                            <form method="POST" action="{{ route('admin.return-requests.approve', $return) }}">
                                @csrf
                                @method('PATCH')
                                <button class="btn btn-primary w-full" type="submit">Approve &amp; restock</button>
                            </form>
                        @endif
                        <form method="POST" action="{{ route('admin.return-requests.reject', $return) }}" class="mt-5 pt-5 border-t border-line">
                            @csrf
                            @method('PATCH')
                            <label class="lbl" for="admin_note">Reason for rejecting (shown to the customer)</label>
                            <textarea class="textarea" id="admin_note" name="admin_note" rows="3" maxlength="500">{{ old('admin_note') }}</textarea>
                            @error('admin_note')<p class="text-danger text-xs mt-1">{{ $message }}</p>@enderror
                            <button class="btn btn-ghost w-full mt-3" type="submit">Reject request</button>
                        </form>
                    @else
                        <p class="text-sm">This request was <b>{{ strtolower($return->status_label) }}</b> on {{ $return->resolved_at?->timezone(\App\Models\Setting::tz())->format('d M Y, h:i A') }}.</p>
                        @if ($return->admin_note)
                            <p class="text-sm text-ink/70 mt-3"><b>Note:</b> {{ $return->admin_note }}</p>
                        @endif
                    @endif
                </div>
            </section>

            <section class="card">
                <div class="card-h"><h3>Order</h3></div>
                <div class="card-b text-sm space-y-2">
                    <p class="flex justify-between"><span class="text-ink/60">Order</span><a href="{{ route('admin.orders.show', $order) }}" class="font-bold text-primary hover:underline">#{{ $order->number }}</a></p>
                    <p class="flex justify-between"><span class="text-ink/60">Status</span><b>{{ $order->status_label }}</b></p>
                    <p class="flex justify-between"><span class="text-ink/60">Total (COD)</span><b>৳{{ number_format($order->total) }}</b></p>
                </div>
            </section>

            <section class="card">
                <div class="card-h"><h3>Customer</h3></div>
                <div class="card-b text-sm space-y-2">
                    <p class="font-bold">{{ $order->name }}</p>
                    <p class="flex gap-3"><svg class="ic ic-sm text-ink/40"><use href="#i-mail" /></svg>{{ $order->email }}</p>
                    <p class="flex gap-3"><svg class="ic ic-sm text-ink/40"><use href="#i-phone" /></svg>{{ $order->phone }}</p>
                </div>
            </section>
        </div>
    </div>

@endsection
