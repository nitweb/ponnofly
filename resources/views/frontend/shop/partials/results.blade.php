<p class="mt-4 font-bold text-ink/70" aria-live="polite">
    @if ($products->total())
        {{ $products->total() }} {{ \Illuminate\Support\Str::plural('product', $products->total()) }}
    @else
        No products found
    @endif
</p>

@if ($products->isNotEmpty())
    <ul id="shop-grid" class="grid grid-cols-2 lg:grid-cols-3 gap-3 sm:gap-5 mt-5">
        @include('frontend.shop.partials.items', ['products' => $products])
    </ul>

    <div id="shop-more" class="flex justify-center mt-10" @if ($products->hasMorePages()) data-next="{{ $products->nextPageUrl() }}" @else hidden @endif>
        <button type="button" class="px-8 py-2.5 rounded-xl font-bold text-[15px] border-2 border-primary text-primary bg-primary-soft hover:bg-primary hover:text-white transition-colors">Read more</button>
    </div>
@else
    <div class="mt-8 rounded-3xl border border-line bg-white p-10 text-center">
        <p class="font-display font-extrabold text-2xl">Nothing matches your filters</p>
        <p class="text-ink/65 mt-2">Try removing a filter or searching for something else.</p>
        <a href="{{ $baseUrl }}" class="inline-block mt-5 bg-accent font-extrabold rounded-xl px-6 py-3 border border-line">Clear filters</a>
    </div>
@endif
