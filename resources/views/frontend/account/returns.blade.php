@extends('frontend.account.layout')

@section('frontend_title', 'Returns | '.$setting('store_name'))
@section('account_title', 'Returns')
@section('account_sub', 'Your return and cancellation requests.')

@section('account_body')
    @php $tk = fn (float $n) => '৳'.number_format($n, floor($n) == $n ? 0 : 2); @endphp

    <div class="grid gap-4">
        @forelse ($returns as $return)
            <article class="bg-white border border-line rounded-3xl p-5 lg:p-6">
                <div class="flex flex-wrap items-start justify-between gap-3">
                    <div>
                        <p class="font-display font-extrabold text-lg">{{ $return->number }} · {{ $return->type_label }}</p>
                        <p class="text-sm text-ink/60">
                            Order <a href="{{ route('account.orders.show', $return->order->number) }}" class="font-bold hover:text-primary">{{ $return->order->number }}</a>
                            · {{ $return->created_at->timezone(\App\Models\Setting::tz())->format('d M Y, h:i A') }}
                        </p>
                    </div>
                    <span class="acct-badge" data-status="{{ $return->status }}">{{ $return->status_label }}</span>
                </div>

                <ul class="grid gap-3 mt-4">
                    @foreach ($return->items as $line)
                        @if ($line->orderItem)
                            <li class="flex items-center gap-3">
                                <img src="{{ $line->orderItem->image }}" alt="{{ $line->orderItem->name }}" width="300" height="300" class="w-12 h-12 rounded-lg object-cover border border-line" loading="lazy">
                                <p class="flex-1 min-w-0 font-bold leading-snug">{{ $line->orderItem->name }}<span class="block text-sm font-normal text-ink/60">{{ $line->orderItem->variant_label ? $line->orderItem->variant_label.' · ' : '' }}Qty {{ $line->quantity }}</span></p>
                                <p class="font-bold">{{ $tk($line->orderItem->unit_price * $line->quantity) }}</p>
                            </li>
                        @endif
                    @endforeach
                </ul>

                <div class="mt-4 pt-4 border-t-2 border-dashed border-line text-sm text-ink/70">
                    <p>Reason: <b class="text-ink">{{ $return->reason_label }}</b>@if ($return->details) · {{ $return->details }}@endif</p>
                    @if ($return->admin_note)
                        <p class="mt-2 rounded-xl bg-accent/25 border border-line px-3 py-2">Store note: {{ $return->admin_note }}</p>
                    @endif
                </div>
            </article>
        @empty
            <div class="bg-white border border-line rounded-3xl p-6 lg:p-8 text-center">
                <p class="font-display text-2xl">No return requests</p>
                <p class="mt-2 text-ink/70">Open one of your orders to cancel or return products.</p>
                <a href="{{ route('account.orders') }}" class="inline-flex items-center justify-center bg-primary hover:bg-primary-dark text-white font-extrabold rounded-xl px-6 py-3.5 transition-colors mt-5">My orders</a>
            </div>
        @endforelse
    </div>

    @if ($returns->hasPages())
        <div class="mt-6 flex items-center justify-between gap-3 text-sm font-bold">
            @if ($returns->onFirstPage())
                <span class="px-4 py-2 rounded-xl border border-line opacity-50">Previous</span>
            @else
                <a href="{{ $returns->previousPageUrl() }}" class="px-4 py-2 rounded-xl border border-line bg-white hover:bg-primary-soft">Previous</a>
            @endif
            <span>Page {{ $returns->currentPage() }} of {{ $returns->lastPage() }}</span>
            @if ($returns->hasMorePages())
                <a href="{{ $returns->nextPageUrl() }}" class="px-4 py-2 rounded-xl border border-line bg-white hover:bg-primary-soft">Next</a>
            @else
                <span class="px-4 py-2 rounded-xl border border-line opacity-50">Next</span>
            @endif
        </div>
    @endif
@endsection
