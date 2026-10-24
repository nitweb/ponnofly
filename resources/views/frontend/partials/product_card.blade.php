@php
    $fmt = fn ($n) => number_format($n, floor($n) == $n ? 0 : 2);
    $pName = $product->name;
    $shownPrice = $product->display_price;
    $oldPrice = $product->display_old_price;
    $percentOff = $product->display_discount_percent;
    $flashItem = $product->flash_item;
    $wished = in_array($product->id, $wishlistIds ?? [], true);
@endphp
<li>
    <article class="group relative bg-white border border-line rounded-2xl overflow-hidden hover:shadow-md transition-shadow flex flex-col h-full">
        <div class="relative overflow-hidden bg-primary-soft">
            <a href="{{ $product->url }}" class="block aspect-square" tabindex="-1" aria-hidden="true">
                <img src="{{ $product->cover_url }}" alt="{{ $pName }}" width="600" height="600" loading="lazy" class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-500">
            </a>
            @if ($percentOff)
                <span class="absolute top-3 left-3 {{ $flashItem ? 'bg-accent text-ink' : 'bg-primary text-white' }} text-xs font-extrabold px-2.5 py-1 rounded-lg">{{ $flashItem ? 'Flash ' : '' }}-{{ $percentOff }}%</span>
            @endif
            <div class="card-actions">
                <button type="button" class="wish w-9 h-9 grid place-items-center rounded-full border border-line hover:bg-primary hover:text-white {{ $wished ? 'bg-primary text-white' : 'bg-white' }}" data-product-id="{{ $product->id }}" aria-label="Add {{ $pName }} to wishlist" aria-pressed="{{ $wished ? 'true' : 'false' }}">
                    <svg class="w-5 h-5"><use href="#i-heart" /></svg>
                </button>
                <button type="button" class="qv" aria-label="Quick view {{ $pName }}" data-id="{{ $product->id }}" data-has-variants="{{ $product->has_variants ? 1 : 0 }}" data-name="{{ $pName }}" data-price="{{ $fmt($shownPrice) }}" data-old="{{ $oldPrice ? $fmt($oldPrice) : '' }}" data-img="{{ $product->cover_url }}" data-href="{{ $product->url }}" data-brand="{{ $product->brand?->name }}">
                    <svg class="w-5 h-5"><use href="#i-eye" /></svg>
                </button>
            </div>
        </div>
        <div class="p-4 flex flex-col flex-1">
            @if ($product->category || $product->brand)
                <div class="flex items-center justify-between gap-2 text-xs font-bold">
                    <span class="text-primary truncate">{{ $product->category?->name }}</span>
                    @if ($product->brand)
                        <a href="{{ $product->brand->url }}" class="text-ink/60 hover:text-primary truncate" style="max-width:50%">{{ $product->brand->name }}</a>
                    @endif
                </div>
            @endif
            <h3 class="font-bold leading-snug mt-1 line-clamp-2">
                <a href="{{ $product->url }}" class="hover:text-primary">{{ $pName }}</a>
            </h3>
            @if ($product->short_description)
                <p class="ldesc hidden text-sm text-ink/70 mt-2">{{ $product->short_description }}</p>
            @endif
            <div class="mt-auto pt-3 flex items-baseline gap-2">
                <span class="font-display font-extrabold text-xl">৳{{ $fmt($shownPrice) }}</span>
                @if ($oldPrice)
                    <s class="text-sm text-ink/50">৳{{ $fmt($oldPrice) }}</s>
                @endif
            </div>
            @if ($flashItem && $flashItem->deal_stock)
                <p class="text-xs font-bold text-primary mt-1">Only {{ $flashItem->deal_stock }} left at this price</p>
            @endif
        </div>
        @if (! $product->in_stock)
            <span class="card-cart" style="opacity:.55;cursor:not-allowed">Out of stock</span>
        @elseif ($product->has_variants)
            <a href="{{ $product->url }}" class="card-cart" style="text-decoration:none">Select options</a>
        @else
            <button type="button" class="addcart card-cart" data-product-id="{{ $product->id }}" aria-label="Add {{ $pName }} to cart">Add to cart</button>
        @endif
    </article>
</li>
