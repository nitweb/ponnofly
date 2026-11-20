@extends('admin.dashboard.dashboard')
@section('admin_title', 'Products · Nebula Mart Admin')
@section('admin_contents')

    @php
        $tabs = ['all' => 'All', 'active' => 'Active', 'draft' => 'Draft', 'out' => 'Out of stock'];
        $keep = fn (array $extra) => array_filter(array_merge(['q' => $filters['search'], 'category' => $filters['categoryId'] ?: null, 'sort' => $filters['sort'] !== 'new' ? $filters['sort'] : null, 'status' => $filters['status'] !== 'all' ? $filters['status'] : null], $extra), fn ($v) => $v !== null && $v !== '');
    @endphp

    <div class="flex flex-wrap items-end justify-between gap-4">
        <div>
            <h1 class="font-display font-extrabold text-2xl sm:text-[28px] tracking-tight">Products</h1>
            <p class="text-ink/55 mt-1">Manage your catalog, pricing and stock.</p>
        </div>
        <a href="{{ route('admin.products.create') }}" class="btn btn-primary">
            <svg class="ic ic-sm"><use href="#i-plus" /></svg>Add product
        </a>
    </div>

    <section class="card">
        <div class="tabs" data-live-tabs>
            @foreach ($tabs as $key => $label)
                <a href="{{ route('admin.products.index', $keep(['status' => $key === 'all' ? null : $key, 'page' => null])) }}" class="tab {{ $filters['status'] === $key ? 'active' : '' }}">
                    {{ $label }} @if (isset($counts[$key]))<small>{{ $counts[$key] }}</small>@endif
                </a>
            @endforeach
        </div>

        <form method="GET" action="{{ route('admin.products.index') }}" data-live-search class="flex flex-wrap items-center gap-3 p-5 border-b border-line">
            @if ($filters['status'] !== 'all')<input type="hidden" name="status" value="{{ $filters['status'] }}">@endif
            <div class="input-group flex-1 min-w-[220px] max-w-sm">
                <svg class="ic"><use href="#i-search" /></svg>
                <input class="input" name="q" value="{{ $filters['search'] }}" placeholder="Search name or SKU…">
            </div>
            <div class="w-56">
                <select class="select" name="category" onchange="this.form.submit()">
                    <option value="">All categories</option>
                    @foreach ($categories as $category)
                        <option value="{{ $category->id }}" @selected($filters['categoryId'] === $category->id)>{{ str_repeat('— ', $category->depth) }}{{ $category->name }}</option>
                    @endforeach
                </select>
            </div>
            <div class="w-56">
                <select class="select" name="supplier" onchange="this.form.submit()">
                    <option value="">All suppliers</option>
                    @foreach ($suppliers as $supplier)
                        <option value="{{ $supplier->id }}" @selected($filters['supplierId'] === $supplier->id)>{{ $supplier->name }}</option>
                    @endforeach
                </select>
            </div>
            <div class="w-48">
                <select class="select" name="sort" onchange="this.form.submit()">
                    <option value="new" @selected($filters['sort'] === 'new')>Newest first</option>
                    <option value="lo" @selected($filters['sort'] === 'lo')>Price: low to high</option>
                    <option value="hi" @selected($filters['sort'] === 'hi')>Price: high to low</option>
                    <option value="stock" @selected($filters['sort'] === 'stock')>Stock: low to high</option>
                </select>
            </div>
            <button class="btn btn-ghost btn-sm" type="submit">Search</button>
        </form>

        <div data-live-results>
        <div class="overflow-x-auto">
            <table class="tbl">
                <thead>
                    <tr>
                        <th>Product</th>
                        <th>Category</th>
                        <th>Brand</th>
                        <th>Supplier</th>
                        <th>Price</th>
                        <th>Stock</th>
                        <th>Active</th>
                        <th class="text-right">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($products as $product)
                        <tr>
                            <td>
                                <div class="flex items-center gap-3">
                                    <img class="thumb" src="{{ $product->cover_url }}" alt="">
                                    <div class="min-w-0">
                                        <p class="font-bold max-w-[280px] truncate">{{ $product->name }}</p>
                                        <p class="text-xs text-ink/50">{{ $product->sku ? 'SKU: '.$product->sku : 'No SKU' }}@if ($product->has_variants) · {{ $product->variants->count() }} variants @endif</p>
                                    </div>
                                </div>
                            </td>
                            <td>{{ $product->category?->name ?? '—' }}</td>
                            <td>{{ $product->brand?->name ?? '—' }}</td>
                            <td>{{ $product->supplier?->name ?? '—' }}</td>
                            <td>
                                <b>৳{{ number_format($product->price) }}</b>
                                @if ($product->discount_percent)<p class="text-xs text-ink/40 line-through">৳{{ number_format($product->compare_price) }}</p>@endif
                            </td>
                            <td>
                                <p class="font-semibold">{{ $product->track_inventory ? $product->stock_total : '∞' }}</p>
                                @if (! $product->in_stock)
                                    <span class="badge b-danger">Out of stock</span>
                                @elseif ($product->is_low_stock)
                                    <span class="badge b-warn">Low stock</span>
                                @else
                                    <span class="badge b-success">In stock</span>
                                @endif
                            </td>
                            <td>
                                <form method="POST" action="{{ route('admin.products.toggle', $product) }}">
                                    @csrf
                                    @method('PATCH')
                                    <label class="switch"><input type="checkbox" @checked($product->status === 'active') onchange="this.form.submit()"><i></i></label>
                                </form>
                            </td>
                            <td>
                                <div class="flex items-center justify-end gap-1">
                                    <a href="{{ $product->url }}" target="_blank" class="icon-btn" title="View"><svg class="ic"><use href="#i-eye" /></svg></a>
                                    <a href="{{ route('admin.products.edit', $product) }}" class="icon-btn" title="Edit"><svg class="ic"><use href="#i-edit" /></svg></a>
                                    <form method="POST" action="{{ route('admin.products.destroy', $product) }}">
                                        @csrf
                                        @method('DELETE')
                                        <button type="button" class="icon-btn danger" data-delete="{{ $product->name }}" title="Delete"><svg class="ic"><use href="#i-trash" /></svg></button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="8" class="text-ink/55">No products found.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if ($products->hasPages())
            <div class="flex flex-wrap items-center justify-between gap-3 px-5 py-4 border-t border-line">
                <p class="text-sm text-ink/55">Showing <b class="text-ink">{{ $products->firstItem() }}–{{ $products->lastItem() }}</b> of <b class="text-ink">{{ $products->total() }}</b> results</p>
                <div class="pg flex items-center gap-1">
                    @if ($products->onFirstPage())
                        <span class="px-1 text-ink/30" aria-hidden="true">‹</span>
                    @else
                        <a href="{{ $products->previousPageUrl() }}" aria-label="Previous">‹</a>
                    @endif
                    @foreach ($products->getUrlRange(max(1, $products->currentPage() - 3), min($products->lastPage(), $products->currentPage() + 3)) as $page => $url)
                        <a href="{{ $url }}" @class(['active' => $page === $products->currentPage()])>{{ $page }}</a>
                    @endforeach
                    @if ($products->hasMorePages())
                        <a href="{{ $products->nextPageUrl() }}" aria-label="Next">›</a>
                    @else
                        <span class="px-1 text-ink/30" aria-hidden="true">›</span>
                    @endif
                </div>
            </div>
        @endif
        </div>
    </section>

    @include('admin.partials.live-search')

@endsection
