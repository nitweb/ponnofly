@extends('frontend.account.layout')

@section('frontend_title', 'Return products | '.$setting('store_name'))
@section('account_title', $order->status === 'delivered' ? 'Return products' : 'Cancel / return products')
@section('account_sub', 'Order '.$order->number.' · choose what you want to send back or cancel.')

@section('account_body')
    @php
        $tk = fn (float $n) => '৳'.number_format($n, floor($n) == $n ? 0 : 2);
        $card = 'bg-white border border-line rounded-3xl p-6 lg:p-8';
        $selected = collect(old('items', []))->map(fn ($id) => (int) $id)->all();
    @endphp

    <form method="POST" action="{{ route('account.returns.store', $order->number) }}" class="grid gap-6">
        @csrf

        <section class="{{ $card }}" aria-labelledby="h-pick">
            <h2 id="h-pick" class="font-display font-extrabold text-2xl">Products</h2>
            @error('items')<p class="text-danger text-sm font-bold mt-2">{{ $message }}</p>@enderror
            <ul class="grid gap-4 mt-5">
                @foreach ($order->items as $item)
                    @php $available = $item->returnable_quantity; @endphp
                    <li class="flex items-center gap-4 {{ $available < 1 ? 'opacity-50' : '' }}">
                        <input type="checkbox" name="items[]" value="{{ $item->id }}" id="item-{{ $item->id }}" class="w-5 h-5 shrink-0" @checked(in_array($item->id, $selected, true)) @disabled($available < 1)>
                        <img src="{{ $item->image }}" alt="{{ $item->name }}" width="300" height="300" class="w-16 h-16 rounded-lg object-cover border border-line" loading="lazy">
                        <label for="item-{{ $item->id }}" class="flex-1 min-w-0">
                            <span class="block font-bold leading-snug">{{ $item->name }}</span>
                            <span class="block text-sm text-ink/60">{{ $item->variant_label ? $item->variant_label.' · ' : '' }}{{ $tk($item->unit_price) }} each @if ($available < 1) · already requested @endif</span>
                        </label>
                        @if ($available > 0)
                            <div class="shrink-0">
                                <label for="qty-{{ $item->id }}" class="block text-xs text-ink/60">Qty</label>
                                <select name="qty[{{ $item->id }}]" id="qty-{{ $item->id }}" class="border border-line rounded-lg px-2 py-1.5 font-bold bg-white">
                                    @for ($i = 1; $i <= $available; $i++)
                                        <option value="{{ $i }}" @selected((int) old("qty.{$item->id}", $available) === $i)>{{ $i }}</option>
                                    @endfor
                                </select>
                            </div>
                        @endif
                    </li>
                @endforeach
            </ul>
        </section>

        <section class="{{ $card }}" aria-labelledby="h-why">
            <h2 id="h-why" class="font-display font-extrabold text-2xl">Reason</h2>
            <div class="grid gap-4 mt-5">
                <div>
                    <label for="reason" class="block font-bold text-sm mb-1.5">Why are you sending it back?</label>
                    <select name="reason" id="reason" class="w-full border border-line rounded-xl px-4 py-3 bg-white" required>
                        <option value="">Select a reason</option>
                        @foreach (\App\Models\OrderReturn::REASONS as $key => $label)
                            <option value="{{ $key }}" @selected(old('reason') === $key)>{{ $label }}</option>
                        @endforeach
                    </select>
                    @error('reason')<p class="text-danger text-sm font-bold mt-1">{{ $message }}</p>@enderror
                </div>
                <div>
                    <label for="details" class="block font-bold text-sm mb-1.5">More details (optional)</label>
                    <textarea name="details" id="details" rows="3" maxlength="500" class="w-full border border-line rounded-xl px-4 py-3">{{ old('details') }}</textarea>
                    @error('details')<p class="text-danger text-sm font-bold mt-1">{{ $message }}</p>@enderror
                </div>
            </div>
            <p class="mt-4 text-sm text-ink/60">Payment is Cash on Delivery, so nothing is charged online. Our team reviews every request before it is approved.</p>
            <div class="flex flex-wrap gap-2 mt-5">
                <button type="submit" class="inline-flex items-center justify-center bg-primary hover:bg-primary-dark text-white font-extrabold rounded-xl px-6 py-3.5 transition-colors">Send request</button>
                <a href="{{ route('account.orders.show', $order->number) }}" class="px-6 py-3.5 rounded-xl border border-line font-bold hover:bg-primary-soft">Back</a>
            </div>
        </section>
    </form>
@endsection
