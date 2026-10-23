@extends('admin.dashboard.dashboard')
@section('admin_title', 'Expense categories · Nebula Mart Admin')
@section('admin_contents')

    <div class="flex flex-wrap items-end justify-between gap-4">
        <div>
            <h1 class="font-display font-extrabold text-2xl sm:text-[28px] tracking-tight">Expense categories</h1>
            <p class="text-ink/55 mt-1">Group expenses to see where the money goes.</p>
        </div>
        <a href="{{ route('admin.expenses.index') }}" class="btn btn-ghost"><svg class="ic ic-sm"><use href="#i-chev-left" /></svg>Back to expenses</a>
    </div>

    <section class="card">
        <div class="card-h"><h3>Add category</h3></div>
        <form method="POST" action="{{ route('admin.expense-categories.store') }}" class="card-b flex flex-wrap items-end gap-3">
            @csrf
            <div class="flex-1 min-w-[220px] max-w-sm">
                <label class="lbl" for="new-name">Name</label>
                <input class="input" id="new-name" name="name" value="{{ old('name') }}" maxlength="80" required>
                @error('name')<p class="text-danger text-xs mt-1">{{ $message }}</p>@enderror
            </div>
            <div>
                <label class="lbl" for="new-color">Colour</label>
                <input class="input" style="width:5rem;padding:.25rem" id="new-color" name="color" type="color" value="{{ old('color', '#0B3A8C') }}">
            </div>
            <button class="btn btn-primary" type="submit"><svg class="ic ic-sm"><use href="#i-plus" /></svg>Add</button>
        </form>
    </section>

    <section class="card">
        <div class="overflow-x-auto">
            <table class="tbl">
                <thead><tr><th>Name</th><th>Colour</th><th>Entries</th><th>Total spent</th><th>Active</th><th class="text-right">Actions</th></tr></thead>
                <tbody>
                    @foreach ($categories as $category)
                        <tr>
                            <td><input class="input" form="cat-{{ $category->id }}" name="name" value="{{ $category->name }}" maxlength="80" required></td>
                            <td><input class="input" style="width:5rem;padding:.25rem" form="cat-{{ $category->id }}" name="color" type="color" value="{{ $category->color }}"></td>
                            <td>{{ $category->expenses_count }}</td>
                            <td><b>৳{{ number_format((float) $category->expenses_sum_amount) }}</b></td>
                            <td><label class="switch"><input type="checkbox" form="cat-{{ $category->id }}" name="is_active" value="1" @checked($category->is_active)><i></i></label></td>
                            <td>
                                <div class="flex items-center justify-end gap-1">
                                    <button class="btn btn-ghost btn-sm" type="submit" form="cat-{{ $category->id }}">Save</button>
                                    <form method="POST" action="{{ route('admin.expense-categories.destroy', $category) }}">
                                        @csrf
                                        @method('DELETE')
                                        <button type="button" class="icon-btn danger" data-delete="{{ $category->name }}" title="Delete"><svg class="ic"><use href="#i-trash" /></svg></button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </section>
    @foreach ($categories as $category)
        <form method="POST" action="{{ route('admin.expense-categories.update', $category) }}" id="cat-{{ $category->id }}" class="hidden">
            @csrf
            @method('PUT')
        </form>
    @endforeach
    <p class="text-xs text-ink/50">Deleting a category keeps its expenses; they move to “Uncategorized”.</p>

@endsection
