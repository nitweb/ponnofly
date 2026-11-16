@foreach ($products as $product)
    @include('frontend.partials.product_card', ['product' => $product])
@endforeach
