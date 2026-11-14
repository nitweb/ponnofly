@extends('frontend.dashboard.dashboard')
@section('frontend_contents')

    <h1 class="sr-only">{{ $setting('store_name') }}: online shopping in Bangladesh</h1>

    @include('frontend.home.01_banner')

    @include('frontend.home.02_shop_by_category')

    @include('frontend.home.03_flash_deals')

    @include('frontend.home._product_section', ['id' => 'featured', 'title' => 'Featured products', 'products' => $featuredProducts, 'link' => url('/shop')])

    @include('frontend.home._product_section', ['id' => 'new', 'title' => 'New arrivals', 'products' => $newProducts, 'link' => url('/shop?sort=new')])

    @foreach ($categorySections as $section)
        @include('frontend.home._product_section', ['id' => 'cat'.$loop->index, 'title' => $section['category']->name, 'products' => $section['products'], 'link' => $section['category']->url])
    @endforeach

    @include('frontend.home.04_top_brands')

    <div class="pb-14"></div>

@endsection
