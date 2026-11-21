@extends('admin.dashboard.dashboard')
@section('admin_title', 'Categories · Nebula Mart Admin')
@section('admin_contents')

    <div class="flex flex-wrap items-end justify-between gap-4">
        <div>
            <h1 class="font-display font-extrabold text-2xl sm:text-[28px] tracking-tight">Categories</h1>
            <p class="text-ink/55 mt-1">Organize products into categories and unlimited levels of sub-categories.</p>
        </div>
        <a href="{{ route('admin.categories.create') }}" class="btn btn-primary">
            <svg class="ic ic-sm"><use href="#i-plus" /></svg>Add category
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
                        <th>Category</th>
                        <th>Slug</th>
                        <th>Sub-categories</th>
                        <th>Products</th>
                        <th>Header menu</th>
                        <th>Active</th>
                        <th class="text-right">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($categories as $category)
                        <tr>
                            <td>
                                <div class="flex items-center gap-3" style="padding-left: {{ $category->depth * 1.75 }}rem">
                                    @if ($category->depth > 0)
                                        <span class="text-ink/30" aria-hidden="true">└</span>
                                    @endif
                                    <img class="thumb" src="{{ $category->image_url ?? asset('assets/admin/img/no-image.svg') }}" alt="">
                                    <div>
                                        <p class="font-bold">{{ $category->name }}</p>
                                        @if ($category->description)
                                            <p class="text-xs text-ink/50">{{ \Illuminate\Support\Str::limit($category->description, 60) }}</p>
                                        @endif
                                    </div>
                                </div>
                            </td>
                            <td class="text-ink/60">/{{ $category->slug }}</td>
                            <td>{{ $category->children_count }}</td>
                            <td>
                                <a href="{{ route('admin.products.index', ['category' => $category->id]) }}" class="font-bold hover:text-primary" title="View products">{{ $category->products_total }}</a>
                                @if ($category->children_count && $category->products_total !== $category->products_direct)
                                    <p class="text-xs text-ink/50">{{ $category->products_direct }} direct</p>
                                @endif
                            </td>
                            <td>
                                @if ($category->show_in_menu)
                                    <span class="badge b-success" title="Shown in the header menu">On</span>
                                @elseif ($category->depth === 0)
                                    <span class="text-ink/40">Off</span>
                                @else
                                    <span class="text-ink/30">—</span>
                                @endif
                            </td>
                            <td>
                                <form method="POST" action="{{ route('admin.categories.toggle', $category) }}">
                                    @csrf
                                    @method('PATCH')
                                    <label class="switch"><input type="checkbox" @checked($category->is_active) onchange="this.form.submit()"><i></i></label>
                                </form>
                            </td>
                            <td>
                                <div class="flex items-center justify-end gap-1">
                                    <a href="{{ route('admin.categories.create', ['parent' => $category->id]) }}" class="icon-btn" title="Add sub-category">
                                        <svg class="ic"><use href="#i-plus" /></svg>
                                    </a>
                                    <a href="{{ route('admin.categories.edit', $category) }}" class="icon-btn" title="Edit">
                                        <svg class="ic"><use href="#i-edit" /></svg>
                                    </a>
                                    <form method="POST" action="{{ route('admin.categories.destroy', $category) }}">
                                        @csrf
                                        @method('DELETE')
                                        <button type="button" class="icon-btn danger" title="Delete" data-delete="{{ $category->name }}">
                                            <svg class="ic"><use href="#i-trash" /></svg>
                                        </button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="text-center text-ink/55 py-10">No categories yet.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <div class="px-5 py-4 border-t border-line">
            <p class="text-sm text-ink/55">Total <b class="text-ink">{{ $categories->count() }}</b> categories · <b class="text-ink">{{ $categories->where('show_in_menu', true)->count() }}</b> of {{ \App\Models\Category::NAV_LIMIT }} in the header menu</p>
        </div>
    </section>

@endsection
