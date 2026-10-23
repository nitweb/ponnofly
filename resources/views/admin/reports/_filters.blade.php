@php($ranges = \App\Services\ReportService::RANGES)
<style media="print">
    #sidebar, .topbar, footer, .no-print, #overlay { display: none !important; }
    main { padding: 0 !important; }
</style>

<div class="flex flex-wrap items-end justify-between gap-4">
    <div>
        <h1 class="font-display font-extrabold text-2xl sm:text-[28px] tracking-tight">{{ $title }}</h1>
        <p class="text-ink/55 mt-1">{{ $subtitle }} · <b class="text-ink">{{ $period['label'] }}</b></p>
    </div>
    <form method="GET" action="{{ url()->current() }}" class="flex flex-wrap items-end gap-3 no-print" id="period-form">
        @foreach (request()->except(['range', 'from', 'to', 'page']) as $name => $value)
            @if (is_scalar($value))
                <input type="hidden" name="{{ $name }}" value="{{ $value }}">
            @endif
        @endforeach
        <select class="select !w-48" name="range" onchange="document.getElementById('custom-dates').style.display = this.value === 'custom' ? 'flex' : 'none'; if (this.value !== 'custom') this.form.submit();" aria-label="Date range">
            @foreach ($ranges as $value => $label)
                <option value="{{ $value }}" @selected($period['key'] === $value)>{{ $label }}</option>
            @endforeach
        </select>
        <div id="custom-dates" class="items-end gap-3" style="display: {{ $period['key'] === 'custom' ? 'flex' : 'none' }}">
            <input class="input !w-48" type="date" name="from" value="{{ $period['from']->format('Y-m-d') }}" aria-label="From">
            <input class="input !w-48" type="date" name="to" value="{{ $period['to']->format('Y-m-d') }}" aria-label="To">
            <button class="btn btn-ghost" type="submit">Apply</button>
        </div>
        @if (! empty($exportKey) && auth('admin')->user()->hasPermission('reports:export'))
            <a href="{{ route('admin.reports.export', ['report' => $exportKey] + request()->only(['range', 'from', 'to'])) }}" class="btn btn-ghost"><svg class="ic ic-sm"><use href="#i-download" /></svg>CSV</a>
        @endif
        <button type="button" class="btn btn-primary" data-print><svg class="ic ic-sm"><use href="#i-printer" /></svg>Print / PDF</button>
    </form>
</div>

<div class="tabs no-print">
    @foreach ([
        'admin.reports.index' => 'Overview',
        'admin.reports.sales' => 'Sales',
        'admin.reports.profit' => 'Profit & loss',
        'admin.reports.products' => 'Products & stock',
        'admin.reports.customers' => 'Customers',
        'admin.reports.staff' => 'Staff',
    ] as $routeName => $label)
        <a href="{{ route($routeName, request()->only(['range', 'from', 'to'])) }}" class="tab {{ request()->routeIs($routeName === 'admin.reports.staff' ? 'admin.reports.staff*' : $routeName) ? 'active' : '' }}">{{ $label }}</a>
    @endforeach
    @if (auth('admin')->user()->hasPermission('expenses:view'))
        <a href="{{ route('admin.expenses.index', request()->only(['range', 'from', 'to'])) }}" class="tab">Expenses</a>
    @endif
</div>
