@extends('admin.dashboard.dashboard')
@section('admin_title', 'Expenses · Nebula Mart Admin')
@section('admin_contents')

    @php($actor = auth('admin')->user())

    <style media="print">
        #sidebar, .topbar, footer, .no-print, #overlay { display: none !important; }
        main { padding: 0 !important; }
    </style>

    <div class="flex flex-wrap items-end justify-between gap-4">
        <div>
            <h1 class="font-display font-extrabold text-2xl sm:text-[28px] tracking-tight">Expenses</h1>
            <p class="text-ink/55 mt-1">Running costs of the store · <b class="text-ink">{{ $period['label'] }}</b></p>
        </div>
        <div class="flex flex-wrap gap-3 no-print">
            <a href="{{ route('admin.expense-categories.index') }}" class="btn btn-ghost">Categories</a>
            @if ($actor->hasPermission('expenses:export'))
                <a href="{{ route('admin.expenses.export', request()->query()) }}" class="btn btn-ghost"><svg class="ic ic-sm"><use href="#i-download" /></svg>CSV</a>
            @endif
            <button type="button" class="btn btn-ghost" data-print><svg class="ic ic-sm"><use href="#i-printer" /></svg>Print / PDF</button>
            @if ($actor->hasPermission('expenses:create'))
                <a href="{{ route('admin.expenses.create') }}" class="btn btn-primary"><svg class="ic ic-sm"><use href="#i-plus" /></svg>Add expense</a>
            @endif
        </div>
    </div>

    <div class="grid sm:grid-cols-2 xl:grid-cols-3 gap-5">
        @include('admin.reports._stat', ['tone' => 'is-5', 'icon' => 'wallet', 'label' => 'Total expenses', 'value' => '৳'.number_format($summary['total']), 'hint' => $summary['count'].' entries'])
        @php($top = $summary['by_category']->first())
        @include('admin.reports._stat', ['tone' => 'is-11', 'icon' => 'chart', 'label' => 'Biggest category', 'value' => $top?->name ?? '—', 'hint' => $top ? '৳'.number_format($top->total) : ''])
        @include('admin.reports._stat', ['tone' => 'is-5', 'icon' => 'clock', 'label' => 'Daily average', 'value' => '৳'.number_format($summary['total'] / max($period['days'], 1)), 'hint' => $period['days'].' days'])
    </div>

    @if ($summary['by_category']->isNotEmpty())
        <section class="card">
            <div class="card-h"><h3>By category</h3></div>
            <div class="card-b">
                <div class="relative w-44 h-44 mx-auto" data-donut='@json($summary['by_category']->map(fn ($row) => ['l' => $row->name, 'v' => round((float) $row->total), 'c' => $row->color])->values())' data-center="Spent"></div>
                <ul class="mt-6 grid sm:grid-cols-2 xl:grid-cols-4 gap-3 text-sm">
                    @foreach ($summary['by_category'] as $row)
                        <li class="flex items-center justify-between gap-3"><span class="flex items-center gap-2"><i class="w-2.5 h-2.5 rounded-full" style="background: {{ $row->color }}"></i>{{ $row->name }}</span><b>৳{{ number_format($row->total) }}</b></li>
                    @endforeach
                </ul>
            </div>
        </section>
    @endif

    <section class="card">
        <form method="GET" action="{{ route('admin.expenses.index') }}" class="flex flex-wrap items-end gap-3 p-5 border-b border-line no-print">
            <div class="w-44">
                <select class="select" name="range" onchange="document.getElementById('exp-custom').style.display = this.value === 'custom' ? 'flex' : 'none'; if (this.value !== 'custom') this.form.submit();" aria-label="Date range">
                    @foreach (\App\Services\ReportService::RANGES as $key => $label)
                        <option value="{{ $key }}" @selected($period['key'] === $key)>{{ $label }}</option>
                    @endforeach
                </select>
            </div>
            <div id="exp-custom" class="items-end gap-3" style="display: {{ $period['key'] === 'custom' ? 'flex' : 'none' }}">
                <input class="input !w-48" type="date" name="from" value="{{ $period['from']->format('Y-m-d') }}" aria-label="From">
                <input class="input !w-48" type="date" name="to" value="{{ $period['to']->format('Y-m-d') }}" aria-label="To">
            </div>
            <div class="w-48">
                <select class="select" name="category" onchange="this.form.submit()" aria-label="Category">
                    <option value="">All categories</option>
                    <option value="none" @selected(($filters['category'] ?? '') === 'none')>Uncategorized</option>
                    @foreach ($categories as $category)
                        <option value="{{ $category->id }}" @selected((string) ($filters['category'] ?? '') === (string) $category->id)>{{ $category->name }}</option>
                    @endforeach
                </select>
            </div>
            <div class="input-group flex-1 min-w-[220px] max-w-sm">
                <svg class="ic"><use href="#i-search" /></svg>
                <input class="input" name="q" value="{{ $filters['q'] ?? '' }}" placeholder="Search title, reference, note…">
            </div>
            <button class="btn btn-ghost btn-sm" type="submit">Apply</button>
        </form>

        <div class="overflow-x-auto">
            <table class="tbl">
                <thead><tr><th>Date</th><th>Title</th><th>Category</th><th>Paid with</th><th>Added by</th><th>Amount</th><th class="text-right no-print">Actions</th></tr></thead>
                <tbody>
                    @forelse ($expenses as $expense)
                        <tr>
                            <td class="text-ink/60">{{ $expense->expense_date->format('d M Y') }}</td>
                            <td><b>{{ $expense->title }}</b>@if ($expense->reference)<p class="text-xs text-ink/50">Ref {{ $expense->reference }}</p>@endif</td>
                            <td>
                                @if ($expense->category)
                                    <span class="flex items-center gap-2"><i class="w-2.5 h-2.5 rounded-full" style="background: {{ $expense->category->color }}"></i>{{ $expense->category->name }}</span>
                                @else
                                    <span class="text-ink/45">—</span>
                                @endif
                            </td>
                            <td>{{ $expense->payment_label }}</td>
                            <td class="text-ink/60">{{ $expense->admin?->name ?? '—' }}</td>
                            <td><b>৳{{ number_format($expense->amount, 2) }}</b></td>
                            <td class="no-print">
                                <div class="flex items-center justify-end gap-1">
                                    @if ($actor->hasPermission('expenses:edit'))
                                        <a href="{{ route('admin.expenses.edit', $expense) }}" class="icon-btn" title="Edit"><svg class="ic"><use href="#i-edit" /></svg></a>
                                    @endif
                                    @if ($actor->hasPermission('expenses:delete'))
                                        <form method="POST" action="{{ route('admin.expenses.destroy', $expense) }}">
                                            @csrf
                                            @method('DELETE')
                                            <button type="button" class="icon-btn danger" data-delete="{{ $expense->title }}" title="Delete"><svg class="ic"><use href="#i-trash" /></svg></button>
                                        </form>
                                    @endif
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="7" class="text-ink/55">No expenses in this period.</td></tr>
                    @endforelse
                </tbody>
                @if ($expenses->total() > 0)
                    <tfoot><tr><td colspan="5" class="text-right font-bold">Total for filter</td><td colspan="2"><b>৳{{ number_format($total, 2) }}</b></td></tr></tfoot>
                @endif
            </table>
        </div>

        @if ($expenses->hasPages())
            <div class="flex flex-wrap items-center justify-between gap-3 px-5 py-4 border-t border-line no-print">
                <p class="text-sm text-ink/55">Showing <b class="text-ink">{{ $expenses->firstItem() }}–{{ $expenses->lastItem() }}</b> of <b class="text-ink">{{ $expenses->total() }}</b> results</p>
                <div class="pg flex items-center gap-1">
                    @foreach ($expenses->getUrlRange(max(1, $expenses->currentPage() - 3), min($expenses->lastPage(), $expenses->currentPage() + 3)) as $page => $url)
                        <a href="{{ $url }}" @class(['active' => $page === $expenses->currentPage()])>{{ $page }}</a>
                    @endforeach
                </div>
            </div>
        @endif
    </section>

@endsection
