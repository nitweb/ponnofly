@extends('frontend.dashboard.dashboard')

@php
    $heading = $brand?->name ?? $category?->name ?? 'Shop all products';
    $intro = $brand?->description ?? $category?->description ?? 'Filter by category, price, brand, color and size to find exactly what you need.';
    $pageTitle = ($brand?->meta_title ?: $category?->meta_title) ?: ($heading.' | '.$setting('store_name'));
    $pageDescription = ($brand?->meta_description ?: $category?->meta_description) ?: $intro;
    $baseUrl = $brand ? $brand->url : ($category ? $category->url : url('/shop'));
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

<form method="GET" action="{{ $baseUrl }}" id="shop-form" class="max-w-7xl mx-auto px-4 lg:px-6 mt-8 grid lg:grid-cols-[280px_1fr] gap-8">

    <aside aria-label="Product filters" class="bg-white border border-line rounded-3xl p-5 shadow-sm lg:self-start">
        <div class="flex items-center justify-between">
            <h2 class="font-display font-extrabold text-2xl">Filters</h2>
            @if ($activeFilters)
                <a href="{{ $baseUrl }}" class="text-sm font-bold underline underline-offset-4 text-primary">Clear all</a>
            @endif
        </div>

        @if ($category || $brand === null)
            <details class="border-b-2 border-line py-4" open>
                <summary class="flex justify-between items-center cursor-pointer font-display font-extrabold text-lg">Category</summary>
                <ul class="mt-3 text-[15px]">
                    <li><a href="{{ url('/shop') }}" class="block py-1.5 {{ ! $category ? 'font-bold text-primary' : 'hover:text-primary' }}">All products</a></li>
                    @foreach ($menuCategories as $top)
                        <li>
                            <a href="{{ $top->url }}" class="block py-1.5 {{ in_array($top->id, $trailIds, true) ? 'font-bold text-primary' : 'hover:text-primary' }}">{{ $top->name }}</a>
                            @if (in_array($top->id, $trailIds, true) && $top->children->isNotEmpty())
                                <ul class="ml-4 border-l-2 border-line pl-3">
                                    @foreach ($top->children as $child)
                                        <li>
                                            <a href="{{ $child->url }}" class="block py-1 {{ in_array($child->id, $trailIds, true) ? 'font-bold text-primary' : 'hover:text-primary' }}">{{ $child->name }}</a>
                                            @if (in_array($child->id, $trailIds, true) && $child->children->isNotEmpty())
                                                <ul class="ml-4 border-l-2 border-line pl-3">
                                                    @foreach ($child->children as $grand)
                                                        <li><a href="{{ $grand->url }}" class="block py-1 {{ in_array($grand->id, $trailIds, true) ? 'font-bold text-primary' : 'hover:text-primary' }}">{{ $grand->name }}</a></li>
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

        @if (! $brand && $facetBrands->isNotEmpty())
            <details class="border-b-2 border-line py-4" open>
                <summary class="flex justify-between items-center cursor-pointer font-display font-extrabold text-lg">Brand</summary>
                <div class="mt-3 max-h-52 overflow-y-auto pr-1">
                    @foreach ($facetBrands as $facet)
                        <label class="flex items-center gap-3 py-1.5 cursor-pointer text-[15px]">
                            <input type="checkbox" name="brand[]" value="{{ $facet->slug }}" class="w-5 h-5 accent-primary" @checked(in_array($facet->slug, $filters['brands'], true))>
                            <span class="flex-1">{{ $facet->name }}</span>
                            <span class="text-xs text-ink/50">{{ $facet->facet_count }}</span>
                        </label>
                    @endforeach
                </div>
            </details>
        @endif

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

        <button type="submit" class="mt-4 w-full bg-primary hover:bg-primary-dark text-white font-extrabold rounded-xl py-3 border border-line shadow-sm">Apply filters</button>
    </aside>

    <section aria-label="Products">
        <div class="flex flex-wrap items-center gap-3">
            <div class="relative flex-1" style="min-width:14rem">
                <label for="shop-q" class="sr-only">Search products</label>
                <input type="text" id="shop-q" name="q" value="{{ $filters['search'] }}" placeholder="Search products by name" autocomplete="off" enterkeyhint="search" class="w-full rounded-xl border border-line bg-white px-4 py-3 font-semibold focus:outline-none focus:shadow-md">
            </div>
            <label for="sort" class="sr-only">Sort by</label>
            <select id="sort" name="sort" onchange="this.form.submit()" class="rounded-xl border border-line bg-white px-4 py-3 font-bold">
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
