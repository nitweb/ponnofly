@if ($products->isNotEmpty())
<section class="max-w-7xl mx-auto px-4 lg:px-6 mt-14" aria-labelledby="h-{{ $id }}">
    <div class="flex items-end justify-between mb-6 gap-4">
        <h2 id="h-{{ $id }}" class="font-display font-extrabold text-3xl">{{ $title }}</h2>
        @isset($link)
            <a href="{{ $link }}" class="font-bold underline underline-offset-4 shrink-0">View all</a>
        @endisset
    </div>
    <ul id="{{ $id }}-grid" class="grid grid-cols-2 md:grid-cols-3 lg:grid-cols-4 gap-3 sm:gap-5">
        @foreach ($products as $product)
            @include('frontend.partials.product_card', ['product' => $product])
        @endforeach
    </ul>
    @if (! empty($next))
        <div data-more data-target="#{{ $id }}-grid" data-next="{{ $next }}" class="flex justify-center mt-10 min-h-10 font-bold text-ink/60">
        </div>
    @endif
</section>
@endif
