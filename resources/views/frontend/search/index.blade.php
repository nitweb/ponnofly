@extends('frontend.dashboard.dashboard')

@php
    $pageTitle = 'Search results for “'.$term.'” | '.$setting('store_name');
@endphp

@section('frontend_title', $pageTitle)
@section('frontend_description', 'Products matching “'.$term.'”.')
@section('page', 'search')

@section('frontend_contents')

<section class="bg-primary border-b-4 border-line relative overflow-hidden">
    <span class="absolute -right-10 -top-12 w-56 h-56 rounded-full bg-primary"></span>
    <span class="absolute right-40 -bottom-14 w-40 h-40 rounded-full bg-accent"></span>
    <div class="max-w-7xl mx-auto px-4 lg:px-6 py-10 relative text-white">
        <nav aria-label="Breadcrumb" class="text-sm">
            <ol class="flex flex-wrap gap-2">
                <li><a href="{{ url('/') }}" class="hover:underline">Home</a></li>
                <li aria-hidden="true">/</li>
                <li aria-current="page" class="font-bold">Search</li>
            </ol>
        </nav>
        <h1 class="font-display font-extrabold text-3xl lg:text-4xl mt-3">Search results for “{{ $term }}”</h1>
        <p class="mt-2 opacity-90" aria-live="polite">
            @if ($products->total())
                {{ $products->total() }} {{ \Illuminate\Support\Str::plural('product', $products->total()) }} found
            @else
                No products found
            @endif
        </p>
    </div>
</section>

<section aria-label="Search results" class="max-w-7xl mx-auto px-4 lg:px-6 mt-8">
    @if ($products->isNotEmpty())
        <ul id="search-grid" class="grid grid-cols-2 lg:grid-cols-4 gap-3 sm:gap-5">
            @include('frontend.shop.partials.items', ['products' => $products])
        </ul>

        @if ($products->hasMorePages())
        <div data-more data-target="#search-grid" data-next="{{ $products->nextPageUrl() }}" class="flex justify-center mt-10">
            <button type="button" class="px-8 py-2.5 rounded-xl font-bold text-[15px] border-2 border-line bg-white hover:border-primary hover:bg-primary hover:text-white transition-colors">Read more</button>
        </div>
        @endif
    @else
        <div class="rounded-3xl border border-line bg-white p-10 text-center">
            <p class="font-display font-extrabold text-2xl">Nothing matches “{{ $term }}”</p>
            <p class="text-ink/65 mt-2">Try a different word, or browse everything we have.</p>
            <a href="{{ route('shop') }}" class="inline-block mt-5 bg-accent font-extrabold rounded-xl px-6 py-3 border border-line">Browse all products</a>
        </div>
    @endif
</section>

<div style="height:5rem"></div>

@endsection
