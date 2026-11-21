@extends('frontend.dashboard.dashboard')

@php
    $heading = $brand?->name ?? $category?->name ?? 'Shop all products';
    $intro = $brand?->description ?? $category?->description ?? 'Filter by category, price, brand, color and size to find exactly what you need.';
    $pageTitle = ($brand?->meta_title ?: $category?->meta_title) ?: ($heading.' | '.$setting('store_name'));
    $pageDescription = ($brand?->meta_description ?: $category?->meta_description) ?: $intro;
    $baseUrl = $brand ? $brand->url : ($category ? $category->url : url('/shop'));
    $filterCount = count($filters['brands']) + count($filters['colors']) + count($filters['sizes']) + ($filters['min'] !== null ? 1 : 0) + ($filters['max'] !== null ? 1 : 0);
    $activeFilters = $filters['search'] !== '' || $filters['brands'] || $filters['colors'] || $filters['sizes'] || $filters['min'] !== null || $filters['max'] !== null;
@endphp

@section('frontend_title', $pageTitle)
@section('frontend_description', $pageDescription)
@section('page', 'catalog')

@section('frontend_contents')

<section class="bg-primary border-b-4 border-line relative overflow-hidden">
    <span class="absolute -right-10 -top-12 w-56 h-56 rounded-full bg-primary"></span>
    <span class="absolute right-40 -bottom-14 w-40 h-40 rounded-full bg-accent"></span>
    <span class="absolute right-24 top-8 w-8 h-8 rounded-full bg-accent"></span>
    <div class="max-w-7xl mx-auto px-4 lg:px-6 py-10 relative text-white">
        <nav aria-label="Breadcrumb" class="text-sm">
            <ol class="flex flex-wrap gap-2">
                <li><a href="{{ url('/') }}" class="hover:underline">Home</a></li>
                <li aria-hidden="true">/</li>
                @if ($brand || $category)
                    <li><a href="{{ url('/shop') }}" class="hover:underline">Shop</a></li>
                    <li aria-hidden="true">/</li>
                @endif
                @foreach ($trail as $node)
                    @if (! $loop->last)
                        <li><a href="{{ $node->url }}" class="hover:underline">{{ $node->name }}</a></li>
                        <li aria-hidden="true">/</li>
                    @endif
                @endforeach
                <li aria-current="page" class="font-bold">{{ $heading }}</li>
            </ol>
        </nav>
        <h1 class="font-display font-extrabold text-4xl lg:text-5xl mt-3">{{ $heading }}</h1>
        <p class="mt-2 max-w-lg opacity-90">{{ $intro }}</p>
    </div>
</section>

<form method="GET" action="{{ $baseUrl }}" id="shop-form" data-live class="max-w-7xl mx-auto px-4 lg:px-6 mt-8 grid lg:grid-cols-[280px_1fr] gap-8">

    <div class="shop-filters-backdrop" data-filters-close aria-hidden="true"></div>

    <aside id="shop-filters" aria-label="Product filters" class="shop-filters bg-white border border-line rounded-3xl p-5 shadow-sm lg:self-start">
        <div class="flex items-center justify-between gap-3">
            <h2 class="font-display font-extrabold text-2xl">Filters</h2>
            <div class="flex items-center gap-3">
                @if ($activeFilters)
                    <a href="{{ $baseUrl }}" class="text-sm font-bold underline underline-offset-4 text-primary">Clear all</a>
                @endif
                <button type="button" class="shop-filters__close" data-filters-close aria-label="Close filters"><svg viewBox="0 0 24 24" width="20" height="20" fill="none" stroke="currentColor" stroke-width="2.4" stroke-linecap="round" aria-hidden="true"><path d="M6 6l12 12M18 6 6 18"/></svg></button>
            </div>
        </div>

        <div id="shop-drawer-search" class="shop-drawer-search"></div>

        @if ($category || $brand === null)
            <details class="border-b-2 border-line py-4" open>
                <summary class="flex justify-between items-center cursor-pointer font-display font-extrabold text-lg">Category</summary>
                <ul class="cat-list mt-3">
                    <li>
                        <a href="{{ url('/shop') }}" class="cat-link {{ ! $category ? 'is-active' : '' }}">
                            <span class="cat-ic"><svg viewBox="0 0 24 24" aria-hidden="true"><path d="M4 4h6v6H4zM14 4h6v6h-6zM4 14h6v6H4zM14 14h6v6h-6z" fill="none" stroke="currentColor" stroke-width="2" stroke-linejoin="round"/></svg></span>
                            <span class="cat-name">All products</span>
                        </a>
                    </li>
                    @foreach ($menuCategories as $top)
                        @php $topOpen = in_array($top->id, $trailIds, true); @endphp
                        <li>
                            <a href="{{ $top->url }}" class="cat-link {{ $topOpen ? 'is-active' : '' }}">
                                <span class="cat-ic">@include('frontend.shop.partials.cat_icon', ['name' => $top->name])</span>
                                <span class="cat-name">{{ $top->name }}</span>
                                @if ($top->children->isNotEmpty())
                                    <svg class="cat-chev {{ $topOpen ? 'is-open' : '' }}" viewBox="0 0 24 24" aria-hidden="true"><path d="m9 6 6 6-6 6" fill="none" stroke="currentColor" stroke-width="2.4" stroke-linecap="round" stroke-linejoin="round"/></svg>
                                @endif
                            </a>
                            @if ($topOpen && $top->children->isNotEmpty())
                                <ul class="cat-sub">
                                    @foreach ($top->children as $child)
                                        @php $childOpen = in_array($child->id, $trailIds, true); @endphp
                                        <li>
                                            <a href="{{ $child->url }}" class="cat-link cat-link--sm {{ $childOpen ? 'is-active' : '' }}"><span class="cat-name">{{ $child->name }}</span></a>
                                            @if ($childOpen && $child->children->isNotEmpty())
                                                <ul class="cat-sub">
                                                    @foreach ($child->children as $grand)
                                                        <li><a href="{{ $grand->url }}" class="cat-link cat-link--sm {{ in_array($grand->id, $trailIds, true) ? 'is-active' : '' }}"><span class="cat-name">{{ $grand->name }}</span></a></li>
                                                    @endforeach
                                                </ul>
                                            @endif
                                        </li>
                                    @endforeach
                                </ul>
                            @endif
                        </li>
                    @endforeach
                </ul>
            </details>
        @endif

        <details class="border-b-2 border-line py-4" open>
            <summary class="flex justify-between items-center cursor-pointer font-display font-extrabold text-lg">Price</summary>
            <div class="mt-3">
                <div class="flex items-center gap-2">
                    <label class="sr-only" for="pmin">Minimum price</label>
                    <input id="pmin" name="min" type="number" min="0" value="{{ $filters['min'] }}" placeholder="0" class="w-full rounded-xl border border-line px-3 py-2 font-bold focus:outline-none focus:shadow-md">
                    <span class="font-bold">to</span>
                    <label class="sr-only" for="pmax">Maximum price</label>
                    <input id="pmax" name="max" type="number" min="0" value="{{ $filters['max'] }}" placeholder="{{ $priceCeiling ?: '' }}" class="w-full rounded-xl border border-line px-3 py-2 font-bold focus:outline-none focus:shadow-md">
                </div>
                <p class="text-xs text-ink/60 mt-2">Prices in ৳ (BDT)</p>
            </div>
        </details>

        @if ($facetColors->isNotEmpty())
            <details class="border-b-2 border-line py-4" open>
                <summary class="flex justify-between items-center cursor-pointer font-display font-extrabold text-lg">Color</summary>
                <div class="mt-3 flex flex-wrap gap-2">
                    @foreach ($facetColors as $color)
                        <label>
                            <input type="checkbox" name="color[]" value="{{ $color }}" class="peer sr-only" @checked(in_array($color, $filters['colors'], true))>
                            <span class="block px-3 py-1.5 rounded-xl border border-line font-bold text-sm cursor-pointer peer-checked:bg-ink peer-checked:text-white">{{ $color }}</span>
                        </label>
                    @endforeach
                </div>
            </details>
        @endif

        @if ($facetSizes->isNotEmpty())
            <details class="py-4" open>
                <summary class="flex justify-between items-center cursor-pointer font-display font-extrabold text-lg">Size</summary>
                <div class="mt-3 flex flex-wrap gap-2">
                    @foreach ($facetSizes as $size)
                        <label>
                            <input type="checkbox" name="size[]" value="{{ $size }}" class="peer sr-only" @checked(in_array($size, $filters['sizes'], true))>
                            <span class="block px-3 py-1.5 rounded-xl border border-line font-bold text-sm cursor-pointer peer-checked:bg-ink peer-checked:text-white">{{ $size }}</span>
                        </label>
                    @endforeach
                </div>
            </details>
        @endif

        <div class="shop-apply-bar mt-4">
            <button type="submit" class="w-full bg-primary hover:bg-primary-dark text-white font-extrabold rounded-xl py-3 border border-line shadow-sm">Apply filters</button>
        </div>
    </aside>

    <section aria-label="Products">
        <div class="flex flex-wrap items-center gap-3">
            <button type="button" class="shop-filter-btn" data-filters-open aria-controls="shop-filters" aria-expanded="false">
                <svg viewBox="0 0 24 24" width="18" height="18" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" aria-hidden="true"><path d="M4 7h10M18 7h2M4 17h2M10 17h10"/><circle cx="16" cy="7" r="2"/><circle cx="8" cy="17" r="2"/></svg>
                <span>Filters</span>
                @if ($filterCount)
                    <span class="shop-filter-btn__count">{{ $filterCount }}</span>
                @endif
            </button>
            <span id="shop-q-home" hidden></span>
            <div id="shop-q-wrap" class="relative flex-1" style="min-width:14rem">
                <label for="shop-q" class="sr-only">Search products</label>
                <input type="text" id="shop-q" name="q" value="{{ $filters['search'] }}" placeholder="Search products by name" autocomplete="off" enterkeyhint="search" class="w-full rounded-xl border border-line bg-white px-4 py-3 font-semibold focus:outline-none focus:shadow-md">
            </div>
            <label for="sort" class="sr-only">Sort by</label>
            <select id="sort" name="sort" onchange="this.form.requestSubmit()" class="shop-sort rounded-xl border border-line bg-white px-4 py-3 font-bold">
                <option value="new" @selected($filters['sort'] === 'new')>Newest</option>
                <option value="pop" @selected($filters['sort'] === 'pop')>Most popular</option>
                <option value="lo" @selected($filters['sort'] === 'lo')>Price: low to high</option>
                <option value="hi" @selected($filters['sort'] === 'hi')>Price: high to low</option>
            </select>
        </div>

        <div id="shop-results">
            @include('frontend.shop.partials.results', ['products' => $products, 'baseUrl' => $baseUrl])
        </div>
    </section>

</form>

<div style="height:5rem"></div>

@endsection
