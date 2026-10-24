@extends('frontend.dashboard.dashboard')

@php
    $fmt = fn ($n) => number_format($n, floor($n) == $n ? 0 : 2);
    $variants = $product->variants;
    $sizes = $variants->pluck('size')->filter()->unique()->values();
    $colors = $variants->pluck('color')->filter()->unique()->values();
    $default = $variants->first(fn ($v) => $v->stock > 0 || ! $product->track_inventory) ?? $variants->first();
    $images = $product->images;
    $variantData = $variants->map(fn ($v) => ['id' => $v->id, 'size' => $v->size, 'color' => $v->color, 'price' => $v->price, 'stock' => $v->stock])->values();
    $title = $product->meta_title ?: $product->name.' | '.$setting('store_name');
    $description = $product->meta_description ?: ($product->short_description ?: \Illuminate\Support\Str::limit(strip_tags((string) $product->description), 155));
    $inStock = $product->in_stock;
    $stockTotal = $product->stock_total;
    $flashItem = $product->flash_item;
    $flashPercent = $flashItem ? (float) $flashItem->discount_percent : 0;
    $flashEnd = $flashItem?->deal->windowAt()[1];
    $basePrice = (float) ($default?->price ?? $product->price);
    $shownPrice = $product->flashPrice($basePrice);
    $oldPrice = max((float) ($product->compare_price ?? 0), $flashItem ? $basePrice : 0);
    $hasOld = $oldPrice > $shownPrice;
    $badgePercent = $hasOld ? (int) round((1 - $shownPrice / $oldPrice) * 100) : 0;
@endphp

@section('frontend_title', $title)
@section('frontend_description', $description)
@if (filled($product->meta_keywords))
@section('frontend_keywords', $product->meta_keywords)
@endif
@section('frontend_image', $product->cover_url)
@section('page', 'product')

@push('jsonld')
    <script type="application/ld+json">{!! json_encode(array_filter([
        '@context' => 'https://schema.org',
        '@type' => 'Product',
        'name' => $product->name,
        'image' => $images->map->url->all() ?: null,
        'description' => $description,
        'sku' => $product->sku,
        'brand' => $product->brand ? ['@type' => 'Brand', 'name' => $product->brand->name] : null,
        'offers' => [
            '@type' => 'Offer',
            'url' => $product->url,
            'priceCurrency' => 'BDT',
            'price' => $product->display_price,
            'availability' => $inStock ? 'https://schema.org/InStock' : 'https://schema.org/OutOfStock',
        ],
    ]), JSON_HEX_TAG | JSON_HEX_AMP | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) !!}</script>
@endpush

@section('frontend_contents')
<main id="main">
    <div class="max-w-7xl mx-auto px-4 lg:px-6 pt-6">
        <nav aria-label="Breadcrumb" class="text-sm text-ink/70">
            <ol class="flex flex-wrap gap-2">
                <li><a href="{{ url('/') }}" class="hover:text-primary">Home</a></li>
                <li aria-hidden="true">/</li>
                @foreach ($trail as $node)
                    <li><a href="{{ $node->url }}" class="hover:text-primary">{{ $node->name }}</a></li>
                    <li aria-hidden="true">/</li>
                @endforeach
                <li aria-current="page" class="font-bold text-ink">{{ $product->name }}</li>
            </ol>
        </nav>

        <article class="mt-6 grid lg:grid-cols-2 gap-8 lg:gap-12">
            <div>
                <figure id="zoom-fig" class="relative rounded-3xl overflow-hidden border border-line bg-primary-soft shadow-sm" data-zoom>
                    <img id="main-img" src="{{ $product->cover_url }}" alt="{{ $product->name }}" data-alt="{{ $product->name }}" width="900" height="900" fetchpriority="high" class="w-full aspect-square object-cover">
                    @if ($badgePercent)
                        <span id="p-badge" class="absolute top-4 left-4 {{ $flashItem ? 'bg-accent text-ink' : 'bg-primary text-white' }} font-extrabold px-3 py-1.5 rounded-xl">-{{ $badgePercent }}%</span>
                    @endif
                </figure>
                @if ($images->count() > 1)
                    <ul class="grid grid-cols-5 gap-3 mt-5">
                        @foreach ($images as $image)
                            <li>
                                <button type="button" class="thumb block w-full aspect-square rounded-xl overflow-hidden border {{ $loop->first ? 'border-primary' : 'border-line' }} hover:border-primary" data-full="{{ $image->url }}" aria-label="View image {{ $loop->iteration }}">
                                    <img src="{{ $image->url }}" alt="{{ $product->name }}, view {{ $loop->iteration }}" width="200" height="200" loading="lazy" class="w-full h-full object-cover">
                                </button>
                            </li>
                        @endforeach
                    </ul>
                @endif
            </div>

            <div>
                @if ($product->category)
                    <a href="{{ $product->category->url }}" class="font-bold text-primary">{{ $product->category->name }}</a>
                @endif
                <h1 class="font-display font-extrabold text-3xl lg:text-4xl leading-tight mt-1">{{ $product->name }}</h1>

                @if ($product->brand)
                    <a href="{{ $product->brand->url }}" class="inline-flex items-center gap-3 mt-4 pr-4 rounded-xl border border-line bg-white hover:bg-accent transition-colors" title="More from {{ $product->brand->name }}">
                        @if ($product->brand->logo)
                            <img src="{{ $product->brand->logo_url }}" alt="" style="width:2.5rem;height:2.5rem;object-fit:contain;border-right:1px solid rgb(0 0 0 / .08);padding:.25rem">
                        @else
                            <span class="grid place-items-center font-display font-extrabold text-primary" style="width:2.5rem;height:2.5rem;border-right:1px solid rgb(0 0 0 / .08)">{{ mb_strtoupper(mb_substr($product->brand->name, 0, 1)) }}</span>
                        @endif
                        <span class="font-bold text-sm">{{ $product->brand->name }}</span>
                        <svg class="w-4 h-4 -rotate-90" aria-hidden="true"><use href="#i-chev" /></svg>
                    </a>
                @endif

                <div class="flex flex-wrap items-center gap-x-3 gap-y-1 mt-4">
                    <span id="stock-chip" class="px-2.5 py-1 rounded-lg bg-accent text-ink text-sm font-bold">
                        @if (! $inStock) Out of stock @elseif ($product->is_low_stock) Only {{ $stockTotal }} left @else In stock @endif
                    </span>
                </div>

                <div class="mt-5 flex flex-wrap items-baseline gap-3">
                    <span id="p-price" class="font-display font-extrabold text-4xl text-primary">৳{{ $fmt($shownPrice) }}</span>
                    <s id="p-old" class="text-xl text-ink/50" @if (! $hasOld) hidden @endif>৳{{ $fmt($oldPrice) }}</s>
                    <span id="p-save" class="bg-accent px-2.5 py-1 rounded-lg font-extrabold text-sm" @if (! $hasOld) hidden @endif>Save ৳{{ $fmt(max(0, $oldPrice - $shownPrice)) }}</span>
                </div>

                @if ($flashItem)
                    <div class="fdeal">
                        <div class="fdeal__head">
                            <span class="fdeal__tag">
                                <svg viewBox="0 0 24 24" width="12" height="12" aria-hidden="true"><path fill="currentColor" d="M13 2 4 14h6l-1 8 9-12h-6z" /></svg>
                                Flash deal
                            </span>
                            <span class="fdeal__title">{{ $flashItem->deal->title }}</span>
                            @if ($badgePercent)
                                <span class="fdeal__off">{{ $badgePercent }}% off</span>
                            @endif
                        </div>
                        <div class="fdeal__body">
                            @if ($flashItem->deal->show_countdown)
                                <div class="fdeal__timer" data-end="{{ $flashEnd->getTimestamp() * 1000 }}" role="timer" aria-live="off" aria-label="Time left in this flash deal">
                                    <span class="fdeal__label">Ends in</span>
                                    <span class="fdeal__unit"><b data-fc="h">00</b><small>h</small></span>
                                    <i class="fdeal__colon" aria-hidden="true">:</i>
                                    <span class="fdeal__unit"><b data-fc="m">00</b><small>m</small></span>
                                    <i class="fdeal__colon" aria-hidden="true">:</i>
                                    <span class="fdeal__unit fdeal__unit--s"><b data-fc="s">00</b><small>s</small></span>
                                </div>
                            @endif
                            @if ($flashItem->deal_stock)
                                <span class="fdeal__stock">Only <b>{{ $flashItem->deal_stock }}</b> left at this price</span>
                            @endif
                        </div>
                    </div>
                @endif

                @if ($product->short_description)
                    <p class="mt-5 text-ink/80 leading-relaxed">{{ $product->short_description }}</p>
                @endif

                @if ($sizes->isNotEmpty())
                    <fieldset class="mt-6">
                        <legend class="font-bold mb-2">Size</legend>
                        <div class="flex flex-wrap gap-2">
                            @foreach ($sizes as $size)
                                <label>
                                    <input type="radio" name="size" value="{{ $size }}" class="peer sr-only" @checked($default && $default->size === $size)>
                                    <span class="block px-4 py-2 rounded-xl border border-line font-bold cursor-pointer peer-checked:bg-ink peer-checked:text-white peer-focus-visible:ring-2 peer-focus-visible:ring-primary">{{ $size }}</span>
                                </label>
                            @endforeach
                        </div>
                    </fieldset>
                @endif

                @if ($colors->isNotEmpty())
                    <fieldset class="mt-6">
                        <legend class="font-bold mb-2">Color</legend>
                        <div class="flex flex-wrap gap-2">
                            @foreach ($colors as $color)
                                <label>
                                    <input type="radio" name="color" value="{{ $color }}" class="peer sr-only" @checked($default && $default->color === $color)>
                                    <span class="block px-4 py-2 rounded-xl border border-line font-bold cursor-pointer peer-checked:bg-ink peer-checked:text-white peer-focus-visible:ring-2 peer-focus-visible:ring-primary">{{ $color }}</span>
                                </label>
                            @endforeach
                        </div>
                    </fieldset>
                @endif

                <div class="mt-6 flex flex-wrap items-center gap-3">
                    <div class="flex items-center border border-line rounded-xl font-bold">
                        <button type="button" id="q-" class="px-4 py-3" aria-label="Decrease quantity">−</button>
                        <input id="qty" type="number" value="1" min="1" step="1" inputmode="numeric" class="w-16 text-center bg-transparent focus:outline-none" aria-label="Quantity">
                        <button type="button" id="q+" class="px-4 py-3" aria-label="Increase quantity">+</button>
                    </div>
                    <button type="button" id="add-main" class="addcart flex-1 min-w-[150px] bg-primary hover:bg-primary-dark text-white font-extrabold rounded-xl py-3.5 border border-line shadow-sm"
                        data-product-id="{{ $product->id }}" data-variant-id="{{ $default?->id ?? 0 }}" @disabled(! $inStock)>
                        {{ $inStock ? 'Add to cart' : 'Out of stock' }}
                    </button>
                    <button type="button" id="buy-now" class="flex-1 min-w-[130px] text-center bg-accent font-extrabold rounded-xl py-3.5 border border-line shadow-sm" @disabled(! $inStock)>Buy now</button>
                    <button type="button" class="wish w-12 h-12 grid place-items-center rounded-xl border border-line hover:bg-primary hover:text-white {{ in_array($product->id, $wishlistIds, true) ? 'bg-primary text-white' : '' }}" data-product-id="{{ $product->id }}" aria-label="Add to wishlist" aria-pressed="{{ in_array($product->id, $wishlistIds, true) ? 'true' : 'false' }}">
                        <svg class="w-5 h-5"><use href="#i-heart" /></svg>
                    </button>
                </div>

                <div class="mt-7 grid gap-3 rounded-2xl border border-line bg-white p-5">
                    <div class="flex items-center gap-3">
                        <span class="w-10 h-10 rounded-xl bg-accent grid place-items-center shrink-0"><svg class="w-5 h-5"><use href="#i-truck" /></svg></span>
                        <p class="text-sm"><b>{{ $product->is_free_delivery ? 'Free delivery' : 'Delivery in 24 hours' }}</b> {{ $product->is_free_delivery ? 'all over Bangladesh.' : 'inside Dhaka, 2-4 days elsewhere.' }}@if ($product->is_cod) Cash on delivery available.@endif</p>
                    </div>
                    @if ($product->is_returnable)
                        <div class="flex items-center gap-3">
                            <span class="w-10 h-10 rounded-xl bg-accent grid place-items-center shrink-0"><svg class="w-5 h-5"><use href="#i-return" /></svg></span>
                            <p class="text-sm"><b>7-day returns</b> if it is not what you expected.</p>
                        </div>
                    @endif
                    <div class="flex items-center gap-3">
                        <span class="w-10 h-10 rounded-xl bg-primary-200 grid place-items-center shrink-0"><svg class="w-5 h-5"><use href="#i-shield" /></svg></span>
                        <p class="text-sm"><b>100% authentic</b> products with official warranty.</p>
                    </div>
                </div>

                <p class="mt-5 text-sm text-ink/65">
                    @if ($product->sku)SKU: {{ $product->sku }} · @endif
                    @if ($product->category)Category: {{ $product->category->name }} · @endif
                    @if ($product->brand)Brand: <a href="{{ $product->brand->url }}" class="hover:text-primary underline">{{ $product->brand->name }}</a>@endif
                </p>
            </div>
        </article>

        <section id="product-details" class="mt-14" aria-label="Product information">
            <div role="tablist" aria-label="Product information" class="flex gap-2 overflow-x-auto border-b-4 border-line">
                <button type="button" role="tab" id="tab-desc" aria-controls="panel-desc" aria-selected="true" class="tab px-6 py-3 font-bold rounded-t-xl border border-b-0 border-line whitespace-nowrap bg-primary text-white">Description</button>
                <button type="button" role="tab" id="tab-spec" aria-controls="panel-spec" aria-selected="false" tabindex="-1" class="tab px-6 py-3 font-bold rounded-t-xl border border-b-0 border-line whitespace-nowrap bg-white">Specifications</button>
            </div>
            <div id="panel-desc" role="tabpanel" aria-labelledby="tab-desc" class="pt-8">
                <h2 class="font-display font-extrabold text-2xl mb-4">Description</h2>
                @if (\App\Support\HtmlSanitizer::looksLikeHtml($product->description))
                    <style>
                        .product-rich h1 { font-size: 1.75rem; font-weight: 800; margin: 1rem 0 .5rem; }
                        .product-rich h2 { font-size: 1.4rem; font-weight: 800; margin: 1rem 0 .5rem; }
                        .product-rich h3 { font-size: 1.15rem; font-weight: 700; margin: .9rem 0 .4rem; }
                        .product-rich p { margin: 0 0 .75rem; }
                        .product-rich ul { list-style: disc; padding-left: 1.5rem; margin: 0 0 .75rem; }
                        .product-rich ol { list-style: decimal; padding-left: 1.5rem; margin: 0 0 .75rem; }
                        .product-rich blockquote { border-left: 4px solid rgb(0 0 0 / .15); padding-left: 1rem; margin: 0 0 .75rem; font-style: italic; }
                        .product-rich a { color: rgb(var(--color-primary, 11 58 140)); text-decoration: underline; }
                        .product-rich img { max-width: 100%; height: auto; border-radius: .75rem; margin: .5rem 0; }
                        .product-rich .ql-align-center { text-align: center; }
                        .product-rich .ql-align-right { text-align: right; }
                        .product-rich .ql-align-justify { text-align: justify; }
                    </style>
                    <div class="product-rich max-w-3xl text-ink/80 leading-relaxed">{!! \App\Support\HtmlSanitizer::clean($product->description) !!}</div>
                @else
                    <div class="max-w-3xl text-ink/80 leading-relaxed grid gap-3">
                        @forelse (preg_split('/\R{2,}/', trim((string) ($product->description ?: $product->short_description))) as $paragraph)
                            @if (trim($paragraph) !== '')<p>{!! nl2br(e(trim($paragraph))) !!}</p>@endif
                        @empty
                            <p>No description yet.</p>
                        @endforelse
                    </div>
                @endif
            </div>
            <div id="panel-spec" role="tabpanel" aria-labelledby="tab-spec" class="pt-8" hidden>
                <h2 class="font-display font-extrabold text-2xl mb-4">Specifications</h2>
                @php
                    $specs = array_filter([
                        'Brand' => $product->brand?->name,
                        'Model' => $product->model,
                        'SKU' => $product->sku,
                        'Category' => $product->category?->name,
                        'Weight' => $product->weight ? rtrim(rtrim(number_format($product->weight, 3), '0'), '.').' kg' : null,
                        'Sizes' => $sizes->implode(', ') ?: null,
                        'Colors' => $colors->implode(', ') ?: null,
                        'Tags' => implode(', ', $product->tag_list) ?: null,
                    ]);
                @endphp
                <table class="w-full max-w-3xl bg-white border border-line rounded-2xl overflow-hidden px-4">
                    <tbody>
                        @foreach ($specs as $label => $value)
                            <tr class="border-b border-line">
                                <th scope="row" class="text-left font-bold py-3 pr-4 pl-4 w-1/3">{{ $label }}</th>
                                <td class="py-3">{{ $value }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </section>


        @if ($related->isNotEmpty())
            <section class="mt-14 mb-4" aria-labelledby="h-rel" style="padding-bottom: 4.75rem;">
                <h2 id="h-rel" class="font-display font-extrabold text-2xl mb-6">Related products</h2>
                <ul class="grid grid-cols-2 lg:grid-cols-4 gap-3 sm:gap-5">
                    @foreach ($related as $item)
                        @include('frontend.partials.product_card', ['product' => $item])
                    @endforeach
                </ul>
            </section>
        @else
            <div style="height:4.75rem"></div>
        @endif
    </div>
</main>
@endsection

@push('scripts')
<script>
    (function () {
        var variants = @json($variantData);
        var base = { price: {{ (float) $product->price }}, old: {{ (float) ($product->compare_price ?? 0) }}, flash: {{ $flashPercent }}, stock: {{ $stockTotal }}, track: {{ $product->track_inventory ? 'true' : 'false' }}, low: {{ (int) $product->getRawOriginal('low_stock') }} };
        var add = document.getElementById('add-main');
        var buy = document.getElementById('buy-now');
        var chip = document.getElementById('stock-chip');
        var price = document.getElementById('p-price');
        var old = document.getElementById('p-old');
        var save = document.getElementById('p-save');
        var badge = document.getElementById('p-badge');

        function money(n) { return '৳' + Number(n).toLocaleString('en-US', { maximumFractionDigits: 2 }); }
        function picked(name) { var el = document.querySelector('input[name=' + name + ']:checked'); return el ? el.value : null; }

        function update() {
            var size = picked('size'), color = picked('color'), v = null, label = [];
            if (variants.length) {
                v = variants.find(function (x) { return (size === null || x.size === size) && (color === null || x.color === color); }) || null;
            }
            if (size) label.push('Size: ' + size);
            if (color) label.push('Color: ' + color);

            var orig = v && v.price !== null ? Number(v.price) : base.price;
            var p = base.flash ? Math.round(orig * (1 - base.flash / 100)) : orig;
            var ref = base.flash ? Math.max(base.old, orig) : base.old;
            var stock = v ? v.stock : base.stock;
            var available = !base.track || stock > 0;
            if (variants.length && !v) available = false;

            price.textContent = money(p);
            var hasOld = ref > p;
            old.hidden = save.hidden = !hasOld;
            if (hasOld) { old.textContent = money(ref); save.textContent = 'Save ' + money(ref - p); }
            if (badge) { badge.hidden = !hasOld; if (hasOld) badge.textContent = '-' + Math.round((1 - p / ref) * 100) + '%'; }

            chip.textContent = !available ? 'Out of stock' : (base.track && stock <= base.low ? 'Only ' + stock + ' left' : 'In stock');

            add.dataset.variantId = v ? v.id : 0;
            add.disabled = buy.disabled = !available;
            add.textContent = available ? 'Add to cart' : 'Out of stock';
        }

        document.querySelectorAll('input[name=size], input[name=color]').forEach(function (r) { r.addEventListener('change', update); });
        buy.addEventListener('click', function () {
            if (!window.NebulaCart) return;
            window.NebulaCart.add(add.dataset.productId, add.dataset.variantId, document.getElementById('qty').value).then(function (result) {
                if (result.ok) location.href = '{{ \Illuminate\Support\Facades\Route::has('checkout') ? route('checkout') : route('cart.index') }}';
            });
        });
        update();
    })();
</script>
@endpush
