@extends('admin.dashboard.dashboard')
@section('admin_title', 'Suppliers · Nebula Mart Admin')
@section('admin_contents')

    <div class="flex flex-wrap items-end justify-between gap-4">
        <div>
            <h1 class="font-display font-extrabold text-2xl sm:text-[28px] tracking-tight">Suppliers</h1>
            <p class="text-ink/55 mt-1">Internal list of who each product is sourced from. Visible in the admin panel only.</p>
        </div>
        <a href="{{ route('admin.suppliers.create') }}" class="btn btn-primary">
            <svg class="ic ic-sm"><use href="#i-plus" /></svg>Add supplier
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
                        <th>Supplier</th>
                        <th>Contact</th>
                        <th>Phone</th>
                        <th>Products</th>
                        <th>Active</th>
                        <th class="text-right">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($suppliers as $supplier)
                        <tr>
                            <td>
                                <div class="flex items-center gap-3">
                                    <span class="w-11 h-11 rounded-xl bg-primary/10 text-primary grid place-items-center font-display font-extrabold">{{ mb_strtoupper(mb_substr($supplier->name, 0, 1)) }}</span>
                                    <div>
                                        <b>{{ $supplier->name }}</b>
                                        @if ($supplier->email)<p class="text-xs text-ink/50">{{ $supplier->email }}</p>@endif
                                    </div>
                                </div>
                            </td>
                            <td class="text-ink/60">{{ $supplier->contact_person ?: '—' }}</td>
                            <td class="text-ink/60">{{ $supplier->phone ?: '—' }}</td>
                            <td>
                                @if ($supplier->products_count)
                                    <a href="{{ route('admin.products.index', ['supplier' => $supplier->id]) }}" class="font-bold text-primary">{{ $supplier->products_count }}</a>
                                @else
                                    0
                                @endif
                            </td>
                            <td>
                                <form method="POST" action="{{ route('admin.suppliers.toggle', $supplier) }}">
                                    @csrf
                                    @method('PATCH')
                                    <label class="switch"><input type="checkbox" @checked($supplier->is_active) onchange="this.form.submit()"><i></i></label>
                                </form>
                            </td>
                            <td>
                                <div class="flex items-center justify-end gap-1">
                                    <a href="{{ route('admin.suppliers.edit', $supplier) }}" class="icon-btn" title="Edit">
                                        <svg class="ic"><use href="#i-edit" /></svg>
                                    </a>
                                    <form method="POST" action="{{ route('admin.suppliers.destroy', $supplier) }}">
                                        @csrf
                                        @method('DELETE')
                                        <button type="button" class="icon-btn danger" data-delete="{{ $supplier->name }}" title="Delete">
                                            <svg class="ic"><use href="#i-trash" /></svg>
                                        </button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="6" class="text-ink/55">No suppliers yet.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if ($suppliers->hasPages())
            <div class="flex flex-wrap items-center justify-between gap-3 px-5 py-4 border-t border-line">
                <p class="text-sm text-ink/55">Showing <b class="text-ink">{{ $suppliers->firstItem() }}–{{ $suppliers->lastItem() }}</b> of <b class="text-ink">{{ $suppliers->total() }}</b> results</p>
                <div class="pg flex items-center gap-1">
                    @if ($suppliers->onFirstPage())
                        <span class="px-1 text-ink/30" aria-hidden="true">‹</span>
                    @else
                        <a href="{{ $suppliers->previousPageUrl() }}" aria-label="Previous">‹</a>
                    @endif
                    @foreach ($suppliers->getUrlRange(1, $suppliers->lastPage()) as $page => $url)
                        <a href="{{ $url }}" @class(['active' => $page === $suppliers->currentPage()])>{{ $page }}</a>
                    @endforeach
                    @if ($suppliers->hasMorePages())
                        <a href="{{ $suppliers->nextPageUrl() }}" aria-label="Next">›</a>
                    @else
                        <span class="px-1 text-ink/30" aria-hidden="true">›</span>
                    @endif
                </div>
            </div>
        @endif
    </section>

@endsection
