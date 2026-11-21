@extends('admin.dashboard.dashboard')
@section('admin_title', 'Brands · Nebula Mart Admin')
@section('admin_contents')

    <div class="flex flex-wrap items-end justify-between gap-4">
        <div>
            <h1 class="font-display font-extrabold text-2xl sm:text-[28px] tracking-tight">Brands</h1>
            <p class="text-ink/55 mt-1">Manage product brands and manufacturers.</p>
        </div>
        <a href="{{ route('admin.brands.create') }}" class="btn btn-primary">
            <svg class="ic ic-sm"><use href="#i-plus" /></svg>Add brand
        </a>
    </div>

    <section class="card">
        <div class="flex flex-wrap items-center gap-3 p-5 border-b border-line">
            <div class="input-group flex-1 min-w-[220px] max-w-sm">
                <svg class="ic"><use href="#i-search" /></svg>
                <input class="input" placeholder="Search…" data-table-search="#tbl">
            </div>
        </div>

        <div class="overflow-x-auto">
            <table class="tbl" id="tbl">
                <thead>
                    <tr>
                        <th>Brand</th>
                        <th>Slug</th>
                        <th>Country</th>
                        <th>Products</th>
                        <th>Order</th>
                        <th>Featured</th>
                        <th>Active</th>
                        <th class="text-right">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($brands as $brand)
                        <tr>
                            <td>
                                <div class="flex items-center gap-3">
                                    @if ($brand->logo)
                                        <img src="{{ $brand->logo_url }}" alt="" class="w-11 h-11 rounded-xl object-contain bg-white border border-line">
                                    @else
                                        <span class="w-11 h-11 rounded-xl bg-primary/10 text-primary grid place-items-center font-display font-extrabold">{{ mb_strtoupper(mb_substr($brand->name, 0, 1)) }}</span>
                                    @endif
                                    <b>{{ $brand->name }}</b>
                                </div>
                            </td>
                            <td class="text-ink/60">/{{ $brand->slug }}</td>
                            <td class="text-ink/60">{{ $brand->country ?: '—' }}</td>
                            <td><a href="{{ route('admin.products.index', ['brand' => $brand->id]) }}" class="font-bold hover:text-primary" title="View products">{{ $brand->products_count }}</a></td>
                            <td>{{ $brand->sort_order }}</td>
                            <td><span class="badge {{ $brand->is_featured ? 'b-success' : 'b-muted' }}">{{ $brand->is_featured ? 'Yes' : 'No' }}</span></td>
                            <td>
                                <form method="POST" action="{{ route('admin.brands.toggle', $brand) }}">
                                    @csrf
                                    @method('PATCH')
                                    <label class="switch"><input type="checkbox" @checked($brand->is_active) onchange="this.form.submit()"><i></i></label>
                                </form>
                            </td>
                            <td>
                                <div class="flex items-center justify-end gap-1">
                                    <a href="{{ route('admin.brands.edit', $brand) }}" class="icon-btn" title="Edit">
                                        <svg class="ic"><use href="#i-edit" /></svg>
                                    </a>
                                    <form method="POST" action="{{ route('admin.brands.destroy', $brand) }}">
                                        @csrf
                                        @method('DELETE')
                                        <button type="button" class="icon-btn danger" data-delete="{{ $brand->name }}" title="Delete">
                                            <svg class="ic"><use href="#i-trash" /></svg>
                                        </button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="8" class="text-ink/55">No brands yet.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if ($brands->hasPages())
            <div class="flex flex-wrap items-center justify-between gap-3 px-5 py-4 border-t border-line">
                <p class="text-sm text-ink/55">Showing <b class="text-ink">{{ $brands->firstItem() }}–{{ $brands->lastItem() }}</b> of <b class="text-ink">{{ $brands->total() }}</b> results</p>
                <div class="pg flex items-center gap-1">
                    @if ($brands->onFirstPage())
                        <span class="px-1 text-ink/30" aria-hidden="true">‹</span>
                    @else
                        <a href="{{ $brands->previousPageUrl() }}" aria-label="Previous">‹</a>
                    @endif
                    @foreach ($brands->getUrlRange(1, $brands->lastPage()) as $page => $url)
                        <a href="{{ $url }}" @class(['active' => $page === $brands->currentPage()])>{{ $page }}</a>
                    @endforeach
                    @if ($brands->hasMorePages())
                        <a href="{{ $brands->nextPageUrl() }}" aria-label="Next">›</a>
                    @else
                        <span class="px-1 text-ink/30" aria-hidden="true">›</span>
                    @endif
                </div>
            </div>
        @endif
    </section>

@endsection
