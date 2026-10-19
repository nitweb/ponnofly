<p class="mt-4 font-bold text-ink/70" aria-live="polite">
    @if ($products->total())
        Showing {{ $products->firstItem() }}–{{ $products->lastItem() }} of {{ $products->total() }} products
    @else
        No products found
    @endif
</p>

@if ($products->isNotEmpty())
    <ul class="grid grid-cols-2 lg:grid-cols-3 gap-3 sm:gap-5 mt-5">
        @foreach ($products as $product)
            @include('frontend.partials.product_card', ['product' => $product])
        @endforeach
    </ul>

    @if ($products->hasPages())
        <nav class="flex flex-wrap items-center justify-center gap-2 mt-10" aria-label="Pagination">
            @if (! $products->onFirstPage())
                <a href="{{ $products->previousPageUrl() }}" class="px-4 py-2 rounded-xl border border-line bg-white font-bold hover:bg-accent">‹ Prev</a>
            @endif
            @foreach ($products->getUrlRange(max(1, $products->currentPage() - 2), min($products->lastPage(), $products->currentPage() + 2)) as $page => $url)
                <a href="{{ $url }}" class="px-4 py-2 rounded-xl border border-line font-bold {{ $page === $products->currentPage() ? 'bg-primary text-white' : 'bg-white hover:bg-accent' }}" @if ($page === $products->currentPage()) aria-current="page" @endif>{{ $page }}</a>
            @endforeach
            @if ($products->hasMorePages())
                <a href="{{ $products->nextPageUrl() }}" class="px-4 py-2 rounded-xl border border-line bg-white font-bold hover:bg-accent">Next ›</a>
            @endif
        </nav>
    @endif
@else
    <div class="mt-8 rounded-3xl border border-line bg-white p-10 text-center">
        <p class="font-display font-extrabold text-2xl">Nothing matches your filters</p>
        <p class="text-ink/65 mt-2">Try removing a filter or searching for something else.</p>
        <a href="{{ $baseUrl }}" class="inline-block mt-5 bg-accent font-extrabold rounded-xl px-6 py-3 border border-line">Clear filters</a>
    </div>
@endif
